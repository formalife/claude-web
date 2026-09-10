<?php
/**
 * Purchase server-side del corso verso Meta Conversions API.
 *
 * La fonte di verità resta il pagamento Stripe confermato. L'evento viene
 * inviato solo quando il browser aveva già un cookie Meta _fbp, così il CAPI
 * non introduce tracking Meta per chi non aveva il pixel marketing attivo.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FMH_Meta_CAPI {
	const API_VERSION = 'v25.0';

	private static function set_status( $post_id, $status, $detail = '' ) {
		update_post_meta( $post_id, '_fmh_meta_purchase_status', sanitize_key( $status ) );
		update_post_meta( $post_id, '_fmh_meta_purchase_status_at', current_time( 'mysql' ) );
		if ( '' !== $detail ) {
			update_post_meta( $post_id, '_fmh_meta_purchase_detail', substr( sanitize_text_field( $detail ), 0, 1000 ) );
		} else {
			delete_post_meta( $post_id, '_fmh_meta_purchase_detail' );
		}
	}

	/**
	 * Invia Purchase una sola volta per ordine pagato. In caso di errore non
	 * marca l'ordine come inviato: un webhook/reconcile successivo può ritentare
	 * con lo stesso event_id, che resta stabile per la deduplicazione Meta.
	 *
	 * @param int   $post_id ID ordine corso.
	 * @param array $object  PaymentIntent Stripe.
	 * @return bool True se già inviato o inviato con successo.
	 */
	public static function maybe_send_purchase( $post_id, $object ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || FMH_Course_Orders::CPT !== get_post_type( $post_id ) ) {
			return false;
		}
		if ( 'paid' !== get_post_meta( $post_id, '_fmh_payment_status', true ) ) {
			self::set_status( $post_id, 'skipped_not_paid' );
			return false;
		}
		if ( '1' === get_post_meta( $post_id, '_fmh_meta_purchase_sent', true ) ) {
			self::set_status( $post_id, 'sent' );
			return true;
		}

		$settings  = fmh_course_get_settings();
		$dataset_id = preg_replace( '/\D+/', '', (string) $settings['meta_dataset_id'] );
		$token      = trim( (string) $settings['meta_access_token'] );
		$fbp        = trim( (string) get_post_meta( $post_id, '_fmh_meta_fbp', true ) );
		$fbc        = trim( (string) get_post_meta( $post_id, '_fmh_meta_fbc', true ) );

		// Senza configurazione o senza segnale browser Meta non inviamo CAPI.
		if ( '' === $dataset_id ) {
			self::set_status( $post_id, 'skipped_no_dataset' );
			return false;
		}
		if ( '' === $token ) {
			self::set_status( $post_id, 'skipped_no_token' );
			return false;
		}
		if ( '' === $fbp ) {
			self::set_status( $post_id, 'skipped_no_fbp' );
			return false;
		}

		$amount_cents = isset( $object['amount_received'] ) ? absint( $object['amount_received'] ) : 0;
		if ( $amount_cents <= 0 ) {
			$amount_cents = absint( get_post_meta( $post_id, '_fmh_amount_cents', true ) );
		}
		if ( $amount_cents <= 0 ) {
			self::set_status( $post_id, 'skipped_no_amount' );
			return false;
		}

		$event_time = absint( get_post_meta( $post_id, '_fmh_meta_purchase_event_time', true ) );
		if ( ! $event_time ) {
			$event_time = time();
			update_post_meta( $post_id, '_fmh_meta_purchase_event_time', $event_time );
		}

		$user_data = array( 'fbp' => $fbp );
		if ( '' !== $fbc ) {
			$user_data['fbc'] = $fbc;
		}

		$seats    = max( 1, absint( get_post_meta( $post_id, '_fmh_course_seats', true ) ) );
		$event_id = 'fmh_course_order_' . $post_id;
		$payload  = array(
			'data' => array(
				array(
					'event_name'       => 'Purchase',
					'event_time'       => $event_time,
					'event_id'         => $event_id,
					'action_source'    => 'website',
					'event_source_url' => fmh_get_course_thankyou_url(),
					'user_data'        => $user_data,
					'custom_data'      => array(
						'value'        => round( $amount_cents / 100, 2 ),
						'currency'     => 'EUR',
						'content_name' => 'Corso Formalife',
						'content_type' => 'product',
						'content_ids'  => array( 'formalife-course' ),
						'order_id'     => (string) $post_id,
						'num_items'    => $seats,
					),
				),
			),
		);

		$url = add_query_arg(
			'access_token',
			$token,
			'https://graph.facebook.com/' . self::API_VERSION . '/' . rawurlencode( $dataset_id ) . '/events'
		);
		self::set_status( $post_id, 'attempting' );
		$response = wp_remote_post(
			$url,
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( $payload ),
				'timeout' => 5,
			)
		);
		if ( is_wp_error( $response ) ) {
			self::set_status( $post_id, 'error_wp', $response->get_error_code() . ': ' . $response->get_error_message() );
			return false;
		}

		$status = wp_remote_retrieve_response_code( $response );
		$body   = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( $status < 200 || $status >= 300 || ! is_array( $body ) || empty( $body['events_received'] ) ) {
			self::set_status( $post_id, 'error_http_' . absint( $status ), wp_remote_retrieve_body( $response ) );
			return false;
		}

		update_post_meta( $post_id, '_fmh_meta_purchase_sent', '1' );
		update_post_meta( $post_id, '_fmh_meta_purchase_sent_at', current_time( 'mysql' ) );
		update_post_meta( $post_id, '_fmh_meta_purchase_event_id', $event_id );
		self::set_status( $post_id, 'sent', 'events_received=' . absint( $body['events_received'] ) . ( ! empty( $body['fbtrace_id'] ) ? '; fbtrace_id=' . $body['fbtrace_id'] : '' ) );
		return true;
	}
}
