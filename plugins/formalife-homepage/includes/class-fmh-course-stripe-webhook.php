<?php
/**
 * Webhook Stripe per confermare i pagamenti delle iscrizioni al corso.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FMH_Course_Stripe_Webhook {
	const ROUTE_NAMESPACE = 'fmh/v1';
	const ROUTE           = '/course-stripe-webhook';
	const TOLERANCE       = 300;
	const HANDLED_EVENTS  = array(
		'payment_intent.succeeded',
		'payment_intent.payment_failed',
	);

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_route' ) );
	}

	public static function register_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			self::ROUTE,
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_request' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function get_endpoint_url() {
		return rest_url( self::ROUTE_NAMESPACE . self::ROUTE );
	}

	public static function handle_request( WP_REST_Request $request ) {
		$settings = fmh_course_get_settings();
		$secret   = trim( $settings['stripe_webhook_secret'] );
		if ( '' === $secret ) {
			return new WP_REST_Response( array( 'error' => 'webhook_secret_not_configured' ), 400 );
		}

		$payload = (string) $request->get_body();
		$sig_header = isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ) : '';
		if ( '' === $payload || '' === $sig_header || ! self::verify_signature( $payload, $sig_header, $secret ) ) {
			return new WP_REST_Response( array( 'error' => 'invalid_signature' ), 400 );
		}

		$event = json_decode( $payload, true );
		if ( ! is_array( $event ) || empty( $event['type'] ) || empty( $event['data']['object'] ) || ! is_array( $event['data']['object'] ) ) {
			return new WP_REST_Response( array( 'error' => 'invalid_payload' ), 400 );
		}

		self::process_event( $event['type'], $event['data']['object'] );
		return new WP_REST_Response( array( 'received' => true ), 200 );
	}

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
	 * Applica a un ordine l'esito di un PaymentIntent. È volutamente pubblica e
	 * idempotente: oltre al webhook la usa anche la riconciliazione diretta con
	 * Stripe (vedi FMH_Course_Orders::reconcile_order()), così un ordine pagato
	 * viene confermato anche quando il webhook non arriva. Chiamarla più volte
	 * sullo stesso ordine non duplica né stato né email.
	 *
	 * @param string $type   Tipo evento Stripe.
	 * @param array  $object Oggetto PaymentIntent.
	 */
	public static function process_event( $type, $object ) {
		if ( ! in_array( $type, self::HANDLED_EVENTS, true ) ) {
			return;
		}
		$post_id = isset( $object['metadata']['wp_course_order_id'] ) ? absint( $object['metadata']['wp_course_order_id'] ) : 0;
		if ( ! $post_id || FMH_Course_Orders::CPT !== get_post_type( $post_id ) ) {
			return;
		}

		$already_paid = ( 'paid' === get_post_meta( $post_id, '_fmh_payment_status', true ) );
		if ( 'payment_intent.payment_failed' === $type ) {
			if ( ! $already_paid ) {
				update_post_meta( $post_id, '_fmh_payment_status', 'failed' );
			}
			return;
		}
		if ( $already_paid ) {
			FMH_Meta_CAPI::maybe_send_purchase( $post_id, $object );
			return;
		}

		$intent_id = isset( $object['id'] ) ? sanitize_text_field( $object['id'] ) : '';

		// Il codice riservato viene consumato solo ora, a incasso avvenuto: un
		// tentativo di pagamento fallito non deve bruciare il codice.
		$used_code = (string) get_post_meta( $post_id, '_fmh_discount_code', true );
		if ( '' !== $used_code ) {
			fmh_course_mark_code_used( $used_code, $post_id );
		}

		update_post_meta( $post_id, '_fmh_payment_status', 'paid' );
		update_post_meta( $post_id, '_fmh_stripe_payment_intent_id', $intent_id );
		update_post_meta( $post_id, '_fmh_paid_at', current_time( 'mysql' ) );
		if ( isset( $object['amount_received'] ) ) {
			update_post_meta( $post_id, '_fmh_amount_cents', absint( $object['amount_received'] ) );
		}

		self::notify_admin( $post_id, $object );
		self::notify_customer( $post_id, $object );

		// Il tracking marketing non deve precedere né bloccare la logica
		// business-critical dell'ordine; se fallisce, un retry può ritentare.
		FMH_Meta_CAPI::maybe_send_purchase( $post_id, $object );
	}

	private static function notify_admin( $post_id, $object ) {
		$to = fmh_course_get_notify_email();
		$nome = get_post_meta( $post_id, '_fmh_nome', true );
		$cognome = get_post_meta( $post_id, '_fmh_cognome', true );
		$email = get_post_meta( $post_id, '_fmh_email', true );
		$telefono = get_post_meta( $post_id, '_fmh_telefono', true );
		$seats = max( 1, absint( get_post_meta( $post_id, '_fmh_course_seats', true ) ) );
		$session = fmh_course_get_session( get_post_meta( $post_id, '_fmh_course_session', true ) );
		$amount = isset( $object['amount_received'] ) ? fmh_course_format_price( absint( $object['amount_received'] ) ) : fmh_course_format_price( absint( get_post_meta( $post_id, '_fmh_amount_cents', true ) ) );

		$subject = sprintf( __( 'Nuova iscrizione pagata — %s %s', 'formalife-homepage' ), $nome, $cognome );
		$body  = "Pagamento Stripe confermato.\n\n";
		$body .= 'Cliente: ' . $nome . ' ' . $cognome . "\n";
		$body .= 'Email: ' . $email . "\n";
		$body .= 'Telefono: ' . $telefono . "\n";
		$body .= 'Posti: ' . $seats . "\n";
		if ( $session ) {
			$body .= 'Corso: ' . fmh_course_format_date( $session['date'] ) . ' · ' . $session['time'] . ' · ' . $session['city'] . "\n";
		}
		$body .= 'Importo: ' . $amount . "\n\n";
		$body .= 'Dettaglio WordPress: ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . "\n";
		wp_mail( $to, $subject, $body );
	}

	private static function notify_customer( $post_id, $object ) {
		$email = get_post_meta( $post_id, '_fmh_email', true );
		if ( ! is_email( $email ) ) {
			return;
		}
		$nome = get_post_meta( $post_id, '_fmh_nome', true );
		$seats = max( 1, absint( get_post_meta( $post_id, '_fmh_course_seats', true ) ) );
		$session = fmh_course_get_session( get_post_meta( $post_id, '_fmh_course_session', true ) );
		$amount = isset( $object['amount_received'] ) ? fmh_course_format_price( absint( $object['amount_received'] ) ) : fmh_course_format_price( absint( get_post_meta( $post_id, '_fmh_amount_cents', true ) ) );
		$home = fmh_get_settings();

		$settings = fmh_course_get_settings();
		$subject = __( 'Iscrizione confermata — Corso Anti-Panico | Formalife', 'formalife-homepage' );
		$body  = sprintf( __( 'Ciao %s,', 'formalife-homepage' ), $nome ) . "\n\n";
		$body .= sprintf( __( 'il pagamento è andato a buon fine e la tua iscrizione al %s è confermata.', 'formalife-homepage' ), $settings['course_name'] ) . "\n\n";
		if ( $session ) {
			$body .= strtoupper( __( 'DATA E ORARIO', 'formalife-homepage' ) ) . "\n";
			$body .= fmh_course_format_date( $session['date'] ) . ' · ' . $session['time'] . "\n";
			$body .= $session['city'] . ' · ' . $session['venue'] . "\n\n";
		}
		$body .= __( 'Posti acquistati:', 'formalife-homepage' ) . ' ' . $seats . "\n";
		$body .= __( 'Totale pagato:', 'formalife-homepage' ) . ' ' . $amount . "\n";
		$body .= __( 'Il libro è incluso per ogni partecipante.', 'formalife-homepage' ) . "\n\n";
		$body .= __( 'Ordine:', 'formalife-homepage' ) . ' #' . $post_id . "\n";
		$body .= __( 'Garanzia Serenità Formalife: consulta le condizioni di vendita applicabili per cambio data, rimborso o credito.', 'formalife-homepage' ) . "\n\n";
		$body .= __( 'Se la sede definitiva è ancora da confermare, riceverai i dettagli prima del corso.', 'formalife-homepage' ) . "\n\n";
		if ( ! empty( $home['contact_email'] ) ) {
			$body .= __( 'Per qualsiasi necessità:', 'formalife-homepage' ) . ' ' . $home['contact_email'] . "\n";
		}
		$headers = array();
		$from = fmh_course_get_notify_email();
		if ( is_email( $from ) ) {
			$headers[] = 'From: Formalife <' . $from . '>';
		}
		wp_mail( $email, $subject, $body, $headers );
	}
}
