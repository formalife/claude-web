<?php
/**
 * Gestisce i preordini raccolti dal modulo popup: registra un custom post
 * type privato per archiviarli in bacheca e gestisce l'invio via AJAX,
 * con reindirizzamento finale alla pagina di pagamento Stripe.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GAPS_Preorder {

	const CPT             = 'gaps_preorder';
	const NONCE_ACTION    = 'gaps_preorder_nonce';
	const AJAX_ACTION     = 'gaps_submit_preorder';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'handle_submit' ) );
		add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, array( __CLASS__, 'handle_submit' ) );

		add_filter( 'manage_' . self::CPT . '_posts_columns', array( __CLASS__, 'add_columns' ) );
		add_action( 'manage_' . self::CPT . '_posts_custom_column', array( __CLASS__, 'render_column' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );

		// Filtro "Pagamento" nell'elenco Preordini ricevuti, per trovare a
		// colpo d'occhio quali preordini sono stati davvero pagati (vedi
		// class-gaps-stripe-webhook.php) prima di spedire.
		add_action( 'restrict_manage_posts', array( __CLASS__, 'render_payment_filter' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'filter_by_payment_status' ) );
	}

	/**
	 * Aggiunge un riquadro di sola lettura con tutti i dettagli del preordine
	 * nella schermata di modifica del singolo post.
	 */
	public static function add_meta_box() {
		add_meta_box(
			'gaps_preorder_details',
			__( 'Dettagli preordine', 'guida-antipanico-soffocamento' ),
			array( __CLASS__, 'render_meta_box' ),
			self::CPT,
			'normal',
			'high'
		);
	}

	/**
	 * Renderizza il riquadro di sola lettura con i dettagli del preordine.
	 *
	 * @param WP_Post $post Post corrente.
	 */
	public static function render_meta_box( $post ) {
		echo '<table class="widefat"><tbody>';

		printf(
			'<tr><th style="width:180px;text-align:left;">%1$s</th><td>%2$s</td></tr>',
			esc_html__( 'Stato pagamento', 'guida-antipanico-soffocamento' ),
			self::render_payment_badge( get_post_meta( $post->ID, '_gaps_payment_status', true ) )
		);

		$paid_at = get_post_meta( $post->ID, '_gaps_paid_at', true );
		if ( $paid_at ) {
			printf(
				'<tr><th style="width:180px;text-align:left;">%1$s</th><td>%2$s</td></tr>',
				esc_html__( 'Pagato il', 'guida-antipanico-soffocamento' ),
				esc_html( $paid_at )
			);
		}

		$book_cents     = get_post_meta( $post->ID, '_gaps_book_amount_cents', true );
		$shipping_cents = get_post_meta( $post->ID, '_gaps_shipping_amount_cents', true );
		if ( '' !== $book_cents || '' !== $shipping_cents ) {
			printf(
				'<tr><th style="width:180px;text-align:left;">%1$s</th><td>%2$s</td></tr>',
				esc_html__( 'Importo', 'guida-antipanico-soffocamento' ),
				esc_html(
					sprintf(
						/* translators: 1: importo libro, 2: importo spedizione */
						__( '%1$s libro + %2$s spedizione', 'guida-antipanico-soffocamento' ),
						number_format_i18n( absint( $book_cents ) / 100, 2 ) . ' €',
						number_format_i18n( absint( $shipping_cents ) / 100, 2 ) . ' €'
					)
				)
			);
		}

		$session_id = get_post_meta( $post->ID, '_gaps_stripe_session_id', true );
		if ( $session_id ) {
			printf(
				'<tr><th style="width:180px;text-align:left;">%1$s</th><td>%2$s</td></tr>',
				esc_html__( 'ID sessione Stripe', 'guida-antipanico-soffocamento' ),
				esc_html( $session_id )
			);
		}

		$fields = array(
			'_gaps_nome'      => __( 'Nome', 'guida-antipanico-soffocamento' ),
			'_gaps_cognome'   => __( 'Cognome', 'guida-antipanico-soffocamento' ),
			'_gaps_telefono'  => __( 'Telefono', 'guida-antipanico-soffocamento' ),
			'_gaps_email'     => __( 'Email', 'guida-antipanico-soffocamento' ),
			'_gaps_indirizzo' => __( 'Indirizzo di spedizione', 'guida-antipanico-soffocamento' ),
			'_gaps_citta'     => __( 'Città', 'guida-antipanico-soffocamento' ),
			'_gaps_provincia' => __( 'Provincia', 'guida-antipanico-soffocamento' ),
			'_gaps_cap'       => __( 'CAP', 'guida-antipanico-soffocamento' ),
		);
		foreach ( $fields as $meta_key => $label ) {
			printf(
				'<tr><th style="width:180px;text-align:left;">%1$s</th><td>%2$s</td></tr>',
				esc_html( $label ),
				esc_html( get_post_meta( $post->ID, $meta_key, true ) )
			);
		}

		$invoice_requested = (bool) get_post_meta( $post->ID, '_gaps_invoice_requested', true );
		printf(
			'<tr><th style="width:180px;text-align:left;">%1$s</th><td>%2$s</td></tr>',
			esc_html__( 'Fattura richiesta', 'guida-antipanico-soffocamento' ),
			$invoice_requested ? esc_html__( 'Sì', 'guida-antipanico-soffocamento' ) : esc_html__( 'No', 'guida-antipanico-soffocamento' )
		);

		if ( $invoice_requested ) {
			$invoice_fields = array(
				'_gaps_invoice_holder'        => __( 'Intestatario della fattura', 'guida-antipanico-soffocamento' ),
				'_gaps_billing_address'       => __( 'Indirizzo di fatturazione', 'guida-antipanico-soffocamento' ),
				'_gaps_vat_number'            => __( 'P.IVA', 'guida-antipanico-soffocamento' ),
				'_gaps_recipient_code_or_pec' => __( 'Codice univoco o PEC', 'guida-antipanico-soffocamento' ),
			);
			foreach ( $invoice_fields as $meta_key => $label ) {
				printf(
					'<tr><th style="width:180px;text-align:left;">%1$s</th><td>%2$s</td></tr>',
					esc_html( $label ),
					esc_html( get_post_meta( $post->ID, $meta_key, true ) )
				);
			}
		}

		// Mantiene consultabile il codice fiscale solo nei preordini storici
		// creati prima della sua rimozione dal modulo.
		$legacy_tax_code = get_post_meta( $post->ID, '_gaps_codice_fiscale', true );
		if ( $legacy_tax_code ) {
			printf(
				'<tr><th style="width:180px;text-align:left;">%1$s</th><td>%2$s</td></tr>',
				esc_html__( 'Codice fiscale (dato storico)', 'guida-antipanico-soffocamento' ),
				esc_html( $legacy_tax_code )
			);
		}
		echo '</tbody></table>';
	}

	/**
	 * Registra il custom post type "Preordini", visibile solo in bacheca
	 * come sottomenu della voce "Guida Anti-Panico" (non pubblico sul sito).
	 */
	public static function register_post_type() {
		register_post_type(
			self::CPT,
			array(
				'labels'          => array(
					'name'          => __( 'Preordini', 'guida-antipanico-soffocamento' ),
					'singular_name' => __( 'Preordine', 'guida-antipanico-soffocamento' ),
					'add_new_item'  => __( 'Aggiungi preordine', 'guida-antipanico-soffocamento' ),
					'edit_item'     => __( 'Modifica preordine', 'guida-antipanico-soffocamento' ),
					'all_items'     => __( 'Preordini ricevuti', 'guida-antipanico-soffocamento' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => GAPS_SLUG,
				'capability_type' => 'post',
				'map_meta_cap'    => true,
				'supports'        => array( 'title' ),
				'has_archive'     => false,
				'rewrite'         => false,
				'show_in_rest'    => false,
			)
		);
	}

	/**
	 * Colonne personalizzate nell'elenco preordini, per vedere i dati principali a colpo d'occhio.
	 *
	 * @param array $columns Colonne esistenti.
	 * @return array
	 */
	public static function add_columns( $columns ) {
		$new = array(
			'cb'             => $columns['cb'],
			'title'          => __( 'Nome e cognome', 'guida-antipanico-soffocamento' ),
			'gaps_payment'   => __( 'Pagamento', 'guida-antipanico-soffocamento' ),
			'gaps_telefono'  => __( 'Telefono', 'guida-antipanico-soffocamento' ),
			'gaps_email'     => __( 'Email', 'guida-antipanico-soffocamento' ),
			'gaps_indirizzo' => __( 'Indirizzo', 'guida-antipanico-soffocamento' ),
			'date'           => $columns['date'],
		);
		return $new;
	}

	/**
	 * Stampa il contenuto delle colonne personalizzate.
	 *
	 * @param string $column  Nome colonna.
	 * @param int    $post_id ID del post.
	 */
	public static function render_column( $column, $post_id ) {
		switch ( $column ) {
			case 'gaps_payment':
				echo self::render_payment_badge( get_post_meta( $post_id, '_gaps_payment_status', true ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- già escapato in render_payment_badge().
				break;
			case 'gaps_telefono':
				echo esc_html( get_post_meta( $post_id, '_gaps_telefono', true ) );
				break;
			case 'gaps_email':
				echo esc_html( get_post_meta( $post_id, '_gaps_email', true ) );
				break;
			case 'gaps_indirizzo':
				echo esc_html( get_post_meta( $post_id, '_gaps_indirizzo', true ) );
				break;
		}
	}

	/**
	 * Restituisce le etichette leggibili di ogni possibile stato di
	 * pagamento, usate sia dal badge sia dal filtro a tendina.
	 *
	 * @return array<string, array{label: string, color: string, bg: string}>
	 */
	private static function payment_statuses() {
		return array(
			'pending'           => array(
				'label' => __( '⏳ In attesa di pagamento', 'guida-antipanico-soffocamento' ),
				'color' => '#8a6d3b',
				'bg'    => '#fdf3d7',
			),
			'awaiting_payment'  => array(
				'label' => __( '🕓 Pagamento in verifica', 'guida-antipanico-soffocamento' ),
				'color' => '#8a6d3b',
				'bg'    => '#fdf3d7',
			),
			'paid'              => array(
				'label' => __( '✅ Pagato', 'guida-antipanico-soffocamento' ),
				'color' => '#1a7a3e',
				'bg'    => '#dcf5e3',
			),
			'failed'            => array(
				'label' => __( '❌ Pagamento fallito', 'guida-antipanico-soffocamento' ),
				'color' => '#9b1c1c',
				'bg'    => '#fbdcdc',
			),
			'expired'           => array(
				'label' => __( '⌛ Scaduto (non pagato)', 'guida-antipanico-soffocamento' ),
				'color' => '#9b1c1c',
				'bg'    => '#fbdcdc',
			),
		);
	}

	/**
	 * Restituisce il markup HTML (già escapato) del badge colorato per uno
	 * stato di pagamento. Uno stato vuoto/sconosciuto è trattato come
	 * "in attesa di pagamento", lo stato di partenza di ogni preordine.
	 *
	 * @param string $status Valore del meta "_gaps_payment_status".
	 * @return string
	 */
	private static function render_payment_badge( $status ) {
		$statuses = self::payment_statuses();
		$status   = isset( $statuses[ $status ] ) ? $status : 'pending';
		$info     = $statuses[ $status ];

		return sprintf(
			'<span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:600;white-space:nowrap;color:%1$s;background:%2$s;">%3$s</span>',
			esc_attr( $info['color'] ),
			esc_attr( $info['bg'] ),
			esc_html( $info['label'] )
		);
	}

	/**
	 * Stampa il menu a tendina "Pagamento" sopra l'elenco Preordini ricevuti.
	 */
	public static function render_payment_filter() {
		global $typenow;
		if ( self::CPT !== $typenow ) {
			return;
		}

		$current = isset( $_GET['gaps_payment_status'] ) ? sanitize_text_field( wp_unslash( $_GET['gaps_payment_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- filtro di sola lettura in bacheca.

		echo '<select name="gaps_payment_status">';
		printf( '<option value="">%s</option>', esc_html__( 'Tutti gli stati di pagamento', 'guida-antipanico-soffocamento' ) );
		foreach ( self::payment_statuses() as $key => $info ) {
			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $key ),
				selected( $current, $key, false ),
				esc_html( wp_strip_all_tags( $info['label'] ) )
			);
		}
		echo '</select>';
	}

	/**
	 * Applica il filtro "Pagamento" selezionato alla query dell'elenco
	 * Preordini ricevuti in bacheca.
	 *
	 * @param WP_Query $query Query corrente.
	 */
	public static function filter_by_payment_status( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( self::CPT !== $query->get( 'post_type' ) ) {
			return;
		}
		if ( empty( $_GET['gaps_payment_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- filtro di sola lettura in bacheca.
			return;
		}

		$status = sanitize_text_field( wp_unslash( $_GET['gaps_payment_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'pending' === $status ) {
			// "In attesa" include sia i preordini con meta esplicito "pending"
			// sia quelli non ancora toccati dal webhook (meta assente).
			$query->set(
				'meta_query', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- elenco a basso volume, filtro amministrativo occasionale.
				array(
					'relation' => 'OR',
					array(
						'key'     => '_gaps_payment_status',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'   => '_gaps_payment_status',
						'value' => 'pending',
					),
				)
			);
		} else {
			$query->set(
				'meta_query', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					array(
						'key'   => '_gaps_payment_status',
						'value' => $status,
					),
				)
			);
		}
	}

	/**
	 * Gestisce l'invio AJAX del modulo di preordine dal popup frontend.
	 * Salva il lead con stato di pagamento "pending" e crea il PaymentIntent
	 * Stripe per lo step 3. Nessuna email parte da qui: né al cliente né
	 * all'admin, perché a questo punto il modulo è stato solo compilato, non
	 * pagato. Entrambe le email (cliente e admin) partono solo da
	 * GAPS_Stripe_Webhook, quando Stripe conferma che il pagamento è
	 * realmente andato a buon fine.
	 */
	public static function handle_submit() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$nome                  = isset( $_POST['nome'] ) ? sanitize_text_field( wp_unslash( $_POST['nome'] ) ) : '';
		$cognome               = isset( $_POST['cognome'] ) ? sanitize_text_field( wp_unslash( $_POST['cognome'] ) ) : '';
		$telefono              = isset( $_POST['telefono'] ) ? sanitize_text_field( wp_unslash( $_POST['telefono'] ) ) : '';
		$email                 = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$indirizzo             = isset( $_POST['indirizzo'] ) ? sanitize_text_field( wp_unslash( $_POST['indirizzo'] ) ) : '';
		$citta                 = isset( $_POST['citta'] ) ? sanitize_text_field( wp_unslash( $_POST['citta'] ) ) : '';
		$provincia             = isset( $_POST['provincia'] ) ? sanitize_text_field( wp_unslash( $_POST['provincia'] ) ) : '';
		$cap                   = isset( $_POST['cap'] ) ? sanitize_text_field( wp_unslash( $_POST['cap'] ) ) : '';
		$invoice_requested     = isset( $_POST['invoice_requested'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['invoice_requested'] ) );
		$invoice_holder        = isset( $_POST['invoice_holder'] ) ? sanitize_text_field( wp_unslash( $_POST['invoice_holder'] ) ) : '';
		$billing_address       = isset( $_POST['billing_address'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_address'] ) ) : '';
		$vat_number            = isset( $_POST['vat_number'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['vat_number'] ) ) ) : '';
		$recipient_code_or_pec = isset( $_POST['recipient_code_or_pec'] ) ? sanitize_text_field( wp_unslash( $_POST['recipient_code_or_pec'] ) ) : '';
		$quantita              = isset( $_POST['quantita'] ) ? max( 1, absint( $_POST['quantita'] ) ) : 1;

		if ( '' === $nome || '' === $cognome || '' === $telefono || '' === $indirizzo || '' === $citta || '' === $provincia || '' === $cap ) {
			wp_send_json_error( array( 'message' => __( 'Compila tutti i campi obbligatori.', 'guida-antipanico-soffocamento' ) ) );
		}

		if ( ! preg_match( '/^[0-9]{5}$/', $cap ) ) {
			wp_send_json_error( array( 'message' => __( 'Inserisci un CAP valido (5 cifre).', 'guida-antipanico-soffocamento' ) ) );
		}

		if ( '' === $email || ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Inserisci un indirizzo email valido.', 'guida-antipanico-soffocamento' ) ) );
		}

		if ( $invoice_requested && ( '' === $invoice_holder || '' === $billing_address || '' === $vat_number || '' === $recipient_code_or_pec ) ) {
			wp_send_json_error( array( 'message' => __( 'Per richiedere la fattura, compila tutti i dati di fatturazione.', 'guida-antipanico-soffocamento' ) ) );
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => self::CPT,
				'post_status' => 'publish',
				'post_title'  => $nome . ' ' . $cognome,
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			wp_send_json_error( array( 'message' => __( 'Si è verificato un errore nel salvataggio. Riprova tra qualche istante.', 'guida-antipanico-soffocamento' ) ) );
		}

		update_post_meta( $post_id, '_gaps_nome', $nome );
		update_post_meta( $post_id, '_gaps_cognome', $cognome );
		update_post_meta( $post_id, '_gaps_telefono', $telefono );
		update_post_meta( $post_id, '_gaps_email', $email );
		update_post_meta( $post_id, '_gaps_indirizzo', $indirizzo );
		update_post_meta( $post_id, '_gaps_citta', $citta );
		update_post_meta( $post_id, '_gaps_provincia', $provincia );
		update_post_meta( $post_id, '_gaps_cap', $cap );
		update_post_meta( $post_id, '_gaps_quantita', $quantita );
		update_post_meta( $post_id, '_gaps_invoice_requested', $invoice_requested ? '1' : '0' );
		if ( $invoice_requested ) {
			update_post_meta( $post_id, '_gaps_invoice_holder', $invoice_holder );
			update_post_meta( $post_id, '_gaps_billing_address', $billing_address );
			update_post_meta( $post_id, '_gaps_vat_number', $vat_number );
			update_post_meta( $post_id, '_gaps_recipient_code_or_pec', $recipient_code_or_pec );
		}
		// Stato di partenza: il modulo è stato compilato, ma il pagamento
		// non è ancora confermato. Solo il webhook Stripe (vedi
		// class-gaps-stripe-webhook.php) può portarlo a "paid".
		update_post_meta( $post_id, '_gaps_payment_status', 'pending' );

		// Il pagamento avviene ora dentro il popup (Stripe Payment Element),
		// non più con un reindirizzamento esterno. Creiamo il PaymentIntent
		// e restituiamo il client_secret al frontend, che monta il modulo di
		// pagamento nello step 3. Il collegamento preordine <-> pagamento
		// passa dai metadata del PaymentIntent (vedi
		// class-gaps-stripe-webhook.php), non più da client_reference_id.
		$intent = self::create_payment_intent( $post_id, $quantita, $email );

		if ( is_wp_error( $intent ) ) {
			wp_send_json_error( array( 'message' => $intent->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'client_secret' => $intent['client_secret'],
				'quantita'      => $quantita,
				'importo_cents' => $intent['amount'],
			)
		);
	}

	/**
	 * Crea un PaymentIntent Stripe per il preordine indicato, chiamando
	 * direttamente le REST API di Stripe (nessuna libreria/SDK necessaria).
	 * L'importo è SEMPRE ricalcolato qui lato server a partire dal prezzo
	 * configurato nel pannello impostazioni: non ci si fida mai di un
	 * importo passato dal frontend.
	 *
	 * @param int    $post_id  ID del preordine (CPT gaps_preorder).
	 * @param int    $quantita Quantità di copie richieste (minimo 1).
	 * @param string $email    Email del cliente, usata per la ricevuta Stripe.
	 * @return array|WP_Error Array con 'client_secret' e 'amount' (centesimi), oppure WP_Error.
	 */
	private static function create_payment_intent( $post_id, $quantita, $email ) {
		$settings   = gaps_get_settings();
		$secret_key = trim( $settings['stripe_secret_key'] );

		if ( '' === $secret_key ) {
			return new WP_Error( 'stripe_not_configured', __( 'Pagamento non disponibile al momento: contatta l\'assistenza.', 'guida-antipanico-soffocamento' ) );
		}

		$book_cents     = gaps_get_price_cents() * $quantita;
		$shipping_cents = gaps_get_shipping_cents();
		$amount_cents   = $book_cents + $shipping_cents;

		update_post_meta( $post_id, '_gaps_book_amount_cents', $book_cents );
		update_post_meta( $post_id, '_gaps_shipping_amount_cents', $shipping_cents );

		$response = wp_remote_post(
			'https://api.stripe.com/v1/payment_intents',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $secret_key,
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body'    => self::build_payment_intent_body( $amount_cents, $shipping_cents, $email, $post_id, $quantita, $settings['stripe_payment_methods'] ),
				'timeout' => 20,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'stripe_unreachable', __( 'Impossibile contattare Stripe in questo momento. Riprova tra poco.', 'guida-antipanico-soffocamento' ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $data['client_secret'] ) ) {
			$message = isset( $data['error']['message'] ) ? $data['error']['message'] : __( 'Errore Stripe sconosciuto.', 'guida-antipanico-soffocamento' );
			return new WP_Error( 'stripe_error', $message );
		}

		update_post_meta( $post_id, '_gaps_stripe_payment_intent_id', sanitize_text_field( $data['id'] ) );

		return array(
			'client_secret' => $data['client_secret'],
			'amount'        => $amount_cents,
		);
	}

	/**
	 * Costruisce il corpo della richiesta HTTP per la creazione del
	 * PaymentIntent, in formato compatibile con l'API di Stripe.
	 *
	 * @param int    $amount_cents    Importo totale in centesimi (libro + spedizione).
	 * @param int    $shipping_cents  Quota di spedizione inclusa nell'importo totale, solo a scopo di riconciliazione (non influisce sull'addebito).
	 * @param string $email           Email cliente.
	 * @param int    $post_id         ID del preordine.
	 * @param int    $quantita        Quantità.
	 * @param array  $payment_methods Elenco dei payment_method_types abilitati (es. card, link, paypal).
	 * @return array
	 */
	private static function build_payment_intent_body( $amount_cents, $shipping_cents, $email, $post_id, $quantita, $payment_methods ) {
		$body = array(
			'amount'                     => $amount_cents,
			'currency'                   => 'eur',
			'receipt_email'              => $email,
			'metadata[wp_preorder_id]'   => $post_id,
			'metadata[quantita]'         => $quantita,
			'metadata[shipping_cents]'   => $shipping_cents,
		);

		foreach ( array_values( (array) $payment_methods ) as $index => $method ) {
			$body[ "payment_method_types[{$index}]" ] = sanitize_key( $method );
		}

		return $body;
	}

}
