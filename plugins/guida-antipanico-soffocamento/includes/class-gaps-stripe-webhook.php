<?php
/**
 * Riceve le notifiche webhook di Stripe per confermare l'avvenuto pagamento
 * di un preordine. È un endpoint REST pubblico (Stripe non può autenticarsi
 * come utente WordPress): la sicurezza non si basa sulla segretezza
 * dell'URL, ma sulla verifica crittografica della firma della richiesta,
 * fatta con la chiave segreta del webhook impostata nel pannello
 * "Notifiche e conferma di pagamento". Senza quella chiave configurata,
 * ogni richiesta in arrivo viene ignorata.
 *
 * Perché serve: compilare il modulo di preordine (vedi class-gaps-preorder.php)
 * non significa aver pagato. Questo file è l'unico punto in cui il plugin
 * viene a sapere, con certezza, che un pagamento è realmente andato a buon
 * fine — ed è quel momento, non l'invio del modulo, a far scattare sia
 * l'email di ringraziamento al cliente sia la notifica interna che
 * autorizza la spedizione del libro. Nessuna email parte alla semplice
 * compilazione del modulo.
 *
 * Collegamento preordine <-> pagamento: GAPS_Preorder::create_payment_intent()
 * imposta "metadata[wp_preorder_id]" sul PaymentIntent al momento della sua
 * creazione. Quando il pagamento va a buon fine (o fallisce), Stripe include
 * gli stessi metadata nell'evento webhook: è così che questo file individua
 * a quale preordine WordPress si riferisce l'evento.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GAPS_Stripe_Webhook {

	const ROUTE_NAMESPACE = 'gaps/v1';
	const ROUTE           = '/stripe-webhook';

	/** Tolleranza in secondi sull'età dell'evento, per limitare i replay (come da raccomandazione Stripe). */
	const TOLERANCE = 300;

	/**
	 * Tipi di evento PaymentIntent gestiti (pagamento embedded via Payment
	 * Element, popup interno al sito). Prima della v3.6.0 qui si gestivano
	 * eventi Checkout Session, legati al vecchio reindirizzamento esterno a
	 * un Payment Link Stripe: quel meccanismo non è più in uso.
	 */
	const HANDLED_EVENTS = array(
		'payment_intent.succeeded',
		'payment_intent.payment_failed',
	);

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_route' ) );
	}

	/**
	 * Registra la rotta REST pubblica su cui Stripe invia gli eventi.
	 */
	public static function register_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			self::ROUTE,
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_request' ),
				// Nessuna autenticazione WordPress possibile per Stripe: la
				// richiesta viene invece verificata crittograficamente in
				// handle_request() tramite la firma nell'header Stripe-Signature.
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Restituisce l'URL completo dell'endpoint webhook, da incollare nella
	 * Dashboard Stripe (Sviluppatori → Webhook → Aggiungi endpoint).
	 *
	 * @return string
	 */
	public static function get_endpoint_url() {
		return rest_url( self::ROUTE_NAMESPACE . self::ROUTE );
	}

	/**
	 * Callback della rotta REST: verifica la firma, interpreta l'evento e
	 * aggiorna il preordine corrispondente.
	 *
	 * @param WP_REST_Request $request Richiesta REST in arrivo.
	 * @return WP_REST_Response
	 */
	public static function handle_request( WP_REST_Request $request ) {
		$settings = gaps_get_settings();
		$secret   = trim( $settings['stripe_webhook_secret'] );

		if ( '' === $secret ) {
			return new WP_REST_Response( array( 'error' => 'webhook_secret_not_configured' ), 400 );
		}

		$payload = (string) $request->get_body();

		// Leggiamo l'header direttamente da $_SERVER (invece che tramite
		// $request->get_header()) per evitare qualunque ambiguità sulla
		// normalizzazione trattino/underscore del nome dell'header nella
		// REST API di WordPress: è lo stesso approccio usato nella
		// documentazione ufficiale Stripe per PHP.
		$sig_header = isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ) : '';

		if ( '' === $payload || '' === $sig_header || ! self::verify_signature( $payload, $sig_header, $secret ) ) {
			return new WP_REST_Response( array( 'error' => 'invalid_signature' ), 400 );
		}

		$event = json_decode( $payload, true );
		if ( ! is_array( $event ) || empty( $event['type'] ) || empty( $event['data']['object'] ) || ! is_array( $event['data']['object'] ) ) {
			return new WP_REST_Response( array( 'error' => 'invalid_payload' ), 400 );
		}

		self::process_event( $event['type'], $event['data']['object'] );

		// Stripe interrompe i tentativi di reinvio solo davanti a una risposta 2xx.
		return new WP_REST_Response( array( 'received' => true ), 200 );
	}

	/**
	 * Verifica la firma della richiesta secondo l'algoritmo documentato da
	 * Stripe: header "Stripe-Signature" nella forma
	 * "t=<timestamp>,v1=<firma>[,v1=<firma precedente in caso di rotazione>]".
	 * La firma attesa è HMAC-SHA256("{timestamp}.{payload}", chiave segreta).
	 *
	 * @param string $payload    Corpo grezzo della richiesta, non modificato.
	 * @param string $sig_header Contenuto dell'header Stripe-Signature.
	 * @param string $secret     Chiave segreta del webhook (whsec_...).
	 * @return bool
	 */
	private static function verify_signature( $payload, $sig_header, $secret ) {
		$timestamp  = null;
		$signatures = array();

		foreach ( explode( ',', $sig_header ) as $part ) {
			$pair = explode( '=', trim( $part ), 2 );
			if ( 2 !== count( $pair ) ) {
				continue;
			}
			list( $key, $value ) = $pair;
			if ( 't' === $key ) {
				$timestamp = $value;
			} elseif ( 'v1' === $key ) {
				$signatures[] = $value;
			}
		}

		if ( null === $timestamp || ! ctype_digit( (string) $timestamp ) || empty( $signatures ) ) {
			return false;
		}

		if ( abs( time() - (int) $timestamp ) > self::TOLERANCE ) {
			return false;
		}

		$expected = hash_hmac( 'sha256', $timestamp . '.' . $payload, $secret );

		foreach ( $signatures as $signature ) {
			if ( hash_equals( $expected, $signature ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Interpreta un evento Stripe già verificato e aggiorna lo stato di
	 * pagamento del preordine corrispondente, individuato tramite i
	 * metadata del PaymentIntent (vedi GAPS_Preorder::create_payment_intent()).
	 *
	 * @param string $type   Tipo di evento (es. "payment_intent.succeeded").
	 * @param array  $object Oggetto PaymentIntent incluso nell'evento.
	 */
	private static function process_event( $type, $object ) {
		if ( ! in_array( $type, self::HANDLED_EVENTS, true ) ) {
			return;
		}

		$post_id = isset( $object['metadata']['wp_preorder_id'] ) ? absint( $object['metadata']['wp_preorder_id'] ) : 0;
		if ( ! $post_id || GAPS_Preorder::CPT !== get_post_type( $post_id ) ) {
			return;
		}

		$already_paid = ( 'paid' === get_post_meta( $post_id, '_gaps_payment_status', true ) );

		if ( 'payment_intent.payment_failed' === $type ) {
			if ( ! $already_paid ) {
				update_post_meta( $post_id, '_gaps_payment_status', 'failed' );
			}
			return;
		}

		// payment_intent.succeeded.
		if ( $already_paid ) {
			return; // Già registrato: Stripe può reinviare lo stesso evento più volte.
		}

		$payment_intent_id = isset( $object['id'] ) ? sanitize_text_field( $object['id'] ) : '';

		update_post_meta( $post_id, '_gaps_payment_status', 'paid' );
		update_post_meta( $post_id, '_gaps_stripe_payment_intent_id', $payment_intent_id );
		update_post_meta( $post_id, '_gaps_paid_at', current_time( 'mysql' ) );

		// Il controllo "$already_paid" più sopra garantisce che questo blocco
		// (e quindi entrambe le email) parta una sola volta per preordine,
		// anche se Stripe reinvia lo stesso evento più volte.
		self::notify_payment_confirmed( $post_id, $object );
		self::notify_customer_payment_confirmed( $post_id, $object );
	}

	/**
	 * Invia l'email di conferma pagamento all'indirizzo di notifica interno
	 * configurato: è il segnale che autorizza a spedire il libro.
	 *
	 * @param int   $post_id ID del preordine.
	 * @param array $object  Oggetto PaymentIntent ricevuto da Stripe.
	 */
	private static function notify_payment_confirmed( $post_id, $object ) {
		$settings = gaps_get_settings();
		$to       = '' !== trim( $settings['notify_email'] ) ? $settings['notify_email'] : get_option( 'admin_email' );

		$nome     = get_post_meta( $post_id, '_gaps_nome', true );
		$cognome  = get_post_meta( $post_id, '_gaps_cognome', true );
		$email    = get_post_meta( $post_id, '_gaps_email', true );
		$indirizzo = get_post_meta( $post_id, '_gaps_indirizzo', true );
		$citta    = get_post_meta( $post_id, '_gaps_citta', true );
		$provincia = get_post_meta( $post_id, '_gaps_provincia', true );
		$cap      = get_post_meta( $post_id, '_gaps_cap', true );

		$amount   = isset( $object['amount'] ) ? number_format_i18n( absint( $object['amount'] ) / 100, 2 ) : '';
		$currency = isset( $object['currency'] ) ? strtoupper( sanitize_text_field( $object['currency'] ) ) : '';

		$subject = sprintf(
			/* translators: %s: nome e cognome del cliente */
			__( 'Pagamento confermato — %s', 'guida-antipanico-soffocamento' ),
			trim( $nome . ' ' . $cognome )
		);

		$body  = __( 'Stripe ha confermato il pagamento del seguente preordine: puoi procedere con la spedizione.', 'guida-antipanico-soffocamento' ) . "\n\n";
		$body .= __( 'Nome:', 'guida-antipanico-soffocamento' ) . ' ' . $nome . ' ' . $cognome . "\n";
		$body .= __( 'Email:', 'guida-antipanico-soffocamento' ) . ' ' . $email . "\n";
		$body .= __( 'Indirizzo:', 'guida-antipanico-soffocamento' ) . ' ' . $indirizzo . ', ' . $cap . ' ' . $citta . ' (' . $provincia . ')' . "\n";
		if ( '' !== $amount ) {
			$body .= __( 'Importo pagato:', 'guida-antipanico-soffocamento' ) . ' ' . $amount . ' ' . $currency . "\n";
		}
		$body .= "\n" . __( 'Dettaglio completo del preordine nella bacheca WordPress:', 'guida-antipanico-soffocamento' ) . ' ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . "\n";

		wp_mail( $to, $subject, $body );
	}

	/**
	 * Invia al cliente l'email di ringraziamento con il riepilogo
	 * dell'ordine, allo stesso momento (conferma di pagamento reale) in cui
	 * parte la notifica interna. I tempi di consegna riprendono lo stesso
	 * campo "Data spedizione" già mostrato in landing e nella pagina
	 * "Grazie", per non promettere al cliente una data diversa da quella che
	 * ha già letto sul sito.
	 *
	 * @param int   $post_id ID del preordine.
	 * @param array $object  Oggetto PaymentIntent ricevuto da Stripe.
	 */
	private static function notify_customer_payment_confirmed( $post_id, $object ) {
		$email = get_post_meta( $post_id, '_gaps_email', true );
		if ( '' === $email || ! is_email( $email ) ) {
			// Non dovrebbe accadere (l'email è obbligatoria e validata in
			// fase di invio del modulo), ma non deve mai bloccare la
			// notifica admin già inviata sopra.
			return;
		}

		$settings = gaps_get_settings();

		$nome      = get_post_meta( $post_id, '_gaps_nome', true );
		$indirizzo = get_post_meta( $post_id, '_gaps_indirizzo', true );
		$citta     = get_post_meta( $post_id, '_gaps_citta', true );
		$provincia = get_post_meta( $post_id, '_gaps_provincia', true );
		$cap       = get_post_meta( $post_id, '_gaps_cap', true );
		$quantita  = max( 1, absint( get_post_meta( $post_id, '_gaps_quantita', true ) ) );

		$invoice_requested = (bool) get_post_meta( $post_id, '_gaps_invoice_requested', true );

		$amount   = isset( $object['amount'] ) ? number_format_i18n( absint( $object['amount'] ) / 100, 2 ) : '';
		$currency = isset( $object['currency'] ) ? strtoupper( sanitize_text_field( $object['currency'] ) ) : 'EUR';

		$subject = __( 'Grazie! Il tuo ordine è confermato — La Guida Anti-Panico al Soffocamento Pediatrico', 'guida-antipanico-soffocamento' );

		$body  = sprintf(
			/* translators: %s: nome del cliente */
			__( 'Ciao %s,', 'guida-antipanico-soffocamento' ),
			'' !== $nome ? $nome : __( 'grazie', 'guida-antipanico-soffocamento' )
		) . "\n\n";
		$body .= __( 'il tuo pagamento è andato a buon fine: l\'ordine de "La Guida Anti-Panico al Soffocamento Pediatrico" è confermato. Non devi fare nient\'altro.', 'guida-antipanico-soffocamento' ) . "\n\n";

		$body .= __( 'RIEPILOGO ORDINE', 'guida-antipanico-soffocamento' ) . "\n";
		$body .= sprintf(
			/* translators: %d: quantità di copie ordinate */
			_n( '%d copia — La Guida Anti-Panico al Soffocamento Pediatrico', '%d copie — La Guida Anti-Panico al Soffocamento Pediatrico', $quantita, 'guida-antipanico-soffocamento' ),
			$quantita
		) . "\n";
		if ( '' !== $amount ) {
			$body .= __( 'Totale pagato:', 'guida-antipanico-soffocamento' ) . ' ' . $amount . ' ' . $currency . "\n";
			$body .= __( 'di cui spese di spedizione:', 'guida-antipanico-soffocamento' ) . ' ' . $settings['shipping_price'] . "\n";
		}
		$body .= __( 'Indirizzo di spedizione:', 'guida-antipanico-soffocamento' ) . ' ' . $indirizzo . ', ' . $cap . ' ' . $citta . ' (' . $provincia . ')' . "\n";
		if ( $invoice_requested ) {
			$body .= __( 'Fattura: la riceverai agli estremi indicati in fase d\'ordine.', 'guida-antipanico-soffocamento' ) . "\n";
		}

		$body .= "\n" . __( 'TEMPI DI CONSEGNA', 'guida-antipanico-soffocamento' ) . "\n";
		$body .= sprintf(
			/* translators: %s: testo libero impostato nel pannello del plugin (es. "in 4-5 giorni lavorativi") */
			__( 'Spedizione %s in tutta Italia. Se ci fossero variazioni, te lo scriveremo a questa email.', 'guida-antipanico-soffocamento' ),
			$settings['date_delivery']
		) . "\n";

		$body .= "\n" . __( 'Nella confezione trovi anche la scheda "I tuoi numeri importanti" in omaggio.', 'guida-antipanico-soffocamento' ) . "\n";

		$contact_lines = array();
		$contact_email = trim( $settings['contact_email'] );
		if ( '' !== $contact_email && is_email( $contact_email ) ) {
			$contact_lines[] = $contact_email;
		}
		$contact_whatsapp = trim( $settings['contact_whatsapp'] );
		if ( '' !== $contact_whatsapp ) {
			// gaps_build_whatsapp_url() applica esc_url() per il contesto
			// HTML (attributo href): in un'email testuale serve invece
			// riportare l'ampersand semplice, non l'entità &#038;.
			$contact_lines[] = __( 'WhatsApp:', 'guida-antipanico-soffocamento' ) . ' ' . wp_specialchars_decode( gaps_build_whatsapp_url( $contact_whatsapp ) );
		}
		if ( ! empty( $contact_lines ) ) {
			$body .= "\n" . __( 'Per domande su ordine, indirizzo, fattura o garanzia, scrivici:', 'guida-antipanico-soffocamento' ) . "\n";
			$body .= implode( "\n", $contact_lines ) . "\n";
		}

		$headers    = array();
		$from_email = trim( $settings['notify_email'] );
		if ( '' !== $from_email && is_email( $from_email ) ) {
			$headers[] = 'From: Formalife <' . $from_email . '>';
		}

		wp_mail( $email, $subject, $body, $headers );
	}
}
