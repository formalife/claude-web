<?php
/**
 * Ordini/iscrizioni del corso e creazione PaymentIntent Stripe.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FMH_Course_Orders {
	const CPT          = 'fmh_course_order';
	const AJAX_ACTION  = 'fmh_submit_course_order';
	const NONCE_ACTION = 'fmh_course_order_nonce';
	const PENDING_HOLD_MINUTES = 30;

	/** Destinatario della notifica inviata alla compilazione del modulo. */
	const SUBMISSION_NOTIFY_EMAIL = 'formalife.it@gmail.com';

	/** Azione admin per la verifica manuale del pagamento su Stripe. */
	const RECONCILE_ACTION = 'fmh_course_reconcile_order';

	/** Verifica AJAX del codice riservato. */
	const CHECK_CODE_ACTION = 'fmh_course_check_code';

	/** Azione admin per rendere di nuovo spendibile un codice già usato. */
	const FREE_CODE_ACTION = 'fmh_course_free_code';

	/** Retry manuale del Purchase Meta CAPI per un ordine già pagato. */
	const META_RETRY_ACTION = 'fmh_course_meta_retry';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'handle_submit' ) );
		add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, array( __CLASS__, 'handle_submit' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_filter( 'manage_' . self::CPT . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::CPT . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_filter( 'post_row_actions', array( __CLASS__, 'row_actions' ), 10, 2 );
		add_action( 'admin_post_' . self::RECONCILE_ACTION, array( __CLASS__, 'handle_reconcile_request' ) );
		add_action( 'wp_ajax_' . self::CHECK_CODE_ACTION, array( __CLASS__, 'handle_check_code' ) );
		add_action( 'wp_ajax_nopriv_' . self::CHECK_CODE_ACTION, array( __CLASS__, 'handle_check_code' ) );
		add_action( 'admin_post_' . self::FREE_CODE_ACTION, array( __CLASS__, 'handle_free_code' ) );
		add_action( 'admin_post_' . self::META_RETRY_ACTION, array( __CLASS__, 'handle_meta_retry_request' ) );
	}

	/**
	 * Rende di nuovo spendibile un codice già consumato (link "Libera" nella
	 * tabella dei codici in bacheca).
	 */
	public static function handle_free_code() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Non hai i permessi per questa operazione.', 'formalife-homepage' ) );
		}
		check_admin_referer( self::FREE_CODE_ACTION );

		$code = isset( $_GET['code'] ) ? fmh_course_normalize_code( wp_unslash( $_GET['code'] ) ) : '';
		if ( '' !== $code ) {
			$used = fmh_course_get_used_codes();
			unset( $used[ $code ] );
			update_option( 'fmh_course_used_codes', $used, false );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . FMH_Course_Settings::MENU_SLUG ) );
		exit;
	}

	/**
	 * Verifica un codice riservato e restituisce i prezzi da mostrare.
	 *
	 * Il codice viaggia solo in questa direzione (browser -> server): non viene
	 * mai stampato nella pagina né incluso negli script, così non è ricavabile
	 * leggendo il sorgente. Resta comunque un controllo di comodo per il
	 * riepilogo: l'importo realmente addebitato viene ricalcolato lato server
	 * al momento del checkout.
	 */
	public static function handle_check_code() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$code       = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
		$session_id = isset( $_POST['session'] ) ? sanitize_key( wp_unslash( $_POST['session'] ) ) : '';
		$session    = fmh_course_get_session( $session_id );

		if ( ! $session || ! fmh_course_code_is_valid( $code ) ) {
			// Distinguere "già usato" da "inesistente" evita che la persona
			// pensi di aver sbagliato a digitare.
			$normalized = fmh_course_normalize_code( $code );
			$used       = fmh_course_get_used_codes();
			$message    = isset( $used[ $normalized ] )
				? __( 'Questo codice risulta già utilizzato.', 'formalife-homepage' )
				: __( 'Codice non riconosciuto.', 'formalife-homepage' );
			wp_send_json_error( array( 'message' => $message ) );
		}

		wp_send_json_success(
			array(
				'singlePriceCents' => fmh_course_resolve_price_cents( $session, 1, $code ),
				'couplePriceCents' => fmh_course_resolve_price_cents( $session, 2, $code ),
				'message'          => __( 'Codice applicato.', 'formalife-homepage' ),
			)
		);
	}

	/**
	 * Chiede a Stripe lo stato reale del PaymentIntent di un ordine e, se
	 * risulta incassato, lo conferma.
	 *
	 * Serve perché lo stato dell'ordine dipendeva unicamente dalla consegna del
	 * webhook: se il webhook non arriva (endpoint non raggiungibile, firma non
	 * valida per orologio del server sfasato, evento non sottoscritto) l'ordine
	 * resta "pending" anche a pagamento avvenuto. Qui la fonte di verità è
	 * Stripe, interrogata direttamente.
	 *
	 * @param int $post_id ID dell'ordine.
	 * @return string Stato del pagamento dopo la verifica.
	 */
	public static function reconcile_order( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || self::CPT !== get_post_type( $post_id ) ) {
			return '';
		}

		$status = (string) get_post_meta( $post_id, '_fmh_payment_status', true );
		if ( 'paid' === $status ) {
			return 'paid';
		}

		$intent_id = trim( (string) get_post_meta( $post_id, '_fmh_stripe_payment_intent_id', true ) );
		if ( '' === $intent_id ) {
			return $status;
		}

		$settings = fmh_course_get_settings();
		$secret   = trim( $settings['stripe_secret_key'] );
		if ( '' === $secret ) {
			return $status;
		}

		$response = wp_remote_get(
			'https://api.stripe.com/v1/payment_intents/' . rawurlencode( $intent_id ),
			array(
				'headers' => array( 'Authorization' => 'Bearer ' . $secret ),
				'timeout' => 15,
			)
		);
		if ( is_wp_error( $response ) ) {
			return $status;
		}

		$intent = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $intent ) || empty( $intent['id'] ) || empty( $intent['status'] ) ) {
			return $status;
		}

		// Il metadata dell'ordine potrebbe mancare su intent creati da versioni
		// precedenti: lo reintegriamo, perché process_event() lo usa per
		// risalire all'ordine.
		if ( empty( $intent['metadata']['wp_course_order_id'] ) ) {
			$intent['metadata']['wp_course_order_id'] = $post_id;
		}

		if ( 'succeeded' === $intent['status'] ) {
			FMH_Course_Stripe_Webhook::process_event( 'payment_intent.succeeded', $intent );
			return 'paid';
		}

		// "requires_payment_method" è anche lo stato iniziale: viene trattato
		// come fallito solo se l'intent riporta un errore di pagamento.
		if ( 'canceled' === $intent['status'] || ! empty( $intent['last_payment_error'] ) ) {
			FMH_Course_Stripe_Webhook::process_event( 'payment_intent.payment_failed', $intent );
			return 'failed';
		}

		return $status;
	}

	/**
	 * Pulsante "Verifica pagamento su Stripe" nel dettaglio iscrizione.
	 */
	public static function handle_reconcile_request() {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Non hai i permessi per questa operazione.', 'formalife-homepage' ) );
		}
		check_admin_referer( self::RECONCILE_ACTION . '_' . $post_id );

		self::reconcile_order( $post_id );
		wp_safe_redirect( add_query_arg( 'fmh_reconciled', '1', get_edit_post_link( $post_id, 'url' ) ) );
		exit;
	}

	/**
	 * Riprova il Purchase Meta per un ordine gia pagato senza creare un nuovo
	 * pagamento. Recupera il PaymentIntent reale da Stripe e riusa event_id.
	 */
	public static function handle_meta_retry_request() {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Non hai i permessi per questa operazione.', 'formalife-homepage' ) );
		}
		check_admin_referer( self::META_RETRY_ACTION . '_' . $post_id );

		if ( 'paid' === get_post_meta( $post_id, '_fmh_payment_status', true ) ) {
			$intent_id = trim( (string) get_post_meta( $post_id, '_fmh_stripe_payment_intent_id', true ) );
			$settings  = fmh_course_get_settings();
			$secret    = trim( (string) $settings['stripe_secret_key'] );
			if ( '' !== $intent_id && '' !== $secret ) {
				$response = wp_remote_get(
					'https://api.stripe.com/v1/payment_intents/' . rawurlencode( $intent_id ),
					array( 'headers' => array( 'Authorization' => 'Bearer ' . $secret ), 'timeout' => 15 )
				);
				if ( ! is_wp_error( $response ) ) {
					$intent = json_decode( wp_remote_retrieve_body( $response ), true );
					if ( is_array( $intent ) && ! empty( $intent['id'] ) && 'succeeded' === ( $intent['status'] ?? '' ) ) {
						FMH_Meta_CAPI::maybe_send_purchase( $post_id, $intent );
					}
				}
			}
		}

		wp_safe_redirect( add_query_arg( 'fmh_meta_retried', '1', get_edit_post_link( $post_id, 'url' ) ) );
		exit;
	}

	/**
	 * Email con tutti i dati raccolti, inviata appena il modulo viene compilato
	 * (quindi prima dell'esito del pagamento). Le notifiche di pagamento
	 * confermato restano quelle gestite dal webhook.
	 *
	 * @param int $post_id ID dell'ordine appena creato.
	 */
	private static function notify_submission( $post_id ) {
		$session = fmh_course_get_session( get_post_meta( $post_id, '_fmh_course_session', true ) );
		$seats   = max( 1, absint( get_post_meta( $post_id, '_fmh_course_seats', true ) ) );
		$get     = static function ( $key ) use ( $post_id ) {
			return (string) get_post_meta( $post_id, $key, true );
		};

		$lines = array(
			'Nuova iscrizione compilata (pagamento non ancora confermato).',
			'',
			'ORDINE',
			'Numero: #' . $post_id,
			'Ricevuta il: ' . current_time( 'd/m/Y H:i' ),
			'Importo previsto: ' . fmh_course_format_price( absint( $get( '_fmh_amount_cents' ) ) ),
			'Partecipanti: ' . $seats,
			'Tariffa: ' . ( 'couple' === $get( '_fmh_price_tier' ) ? 'coppia' : 'singolo' ),
			'Copie libro incluse: ' . absint( $get( '_fmh_book_copies' ) ),
		);

		$used_code = $get( '_fmh_discount_code' );
		if ( '' !== $used_code ) {
			$lines[] = 'Codice riservato: ' . $used_code;
			$lines[] = 'Prezzo di listino: ' . fmh_course_format_price( absint( $get( '_fmh_list_price_cents' ) ) );
		}

		if ( $session ) {
			$lines[] = '';
			$lines[] = 'CORSO';
			$lines[] = 'Data: ' . fmh_course_format_date( $session['date'] ) . ' · ' . $session['time'];
			$lines[] = 'Città: ' . $session['city'];
			$lines[] = 'Sede: ' . $session['venue'];
		}

		$lines[] = '';
		$lines[] = 'PARTECIPANTE';
		$lines[] = 'Nome e cognome: ' . trim( $get( '_fmh_nome' ) . ' ' . $get( '_fmh_cognome' ) );
		$lines[] = 'Email: ' . $get( '_fmh_email' );
		$lines[] = 'Telefono: ' . $get( '_fmh_telefono' );
		$lines[] = 'Codice fiscale: ' . $get( '_fmh_codice_fiscale' );
		$lines[] = 'Indirizzo di residenza: ' . $get( '_fmh_indirizzo' );
		$lines[] = 'Città di residenza: ' . $get( '_fmh_citta' );

		$second = trim( $get( '_fmh_second_nome' ) . ' ' . $get( '_fmh_second_cognome' ) );
		if ( '' !== $second ) {
			$lines[] = '';
			$lines[] = 'SECONDA PERSONA';
			$lines[] = 'Nome e cognome: ' . $second;
		}

		if ( $get( '_fmh_invoice_requested' ) ) {
			$lines[] = '';
			$lines[] = 'FATTURA RICHIESTA';
			$lines[] = 'Intestatario: ' . $get( '_fmh_invoice_holder' );
			$lines[] = 'Indirizzo di fatturazione: ' . $get( '_fmh_billing_address' );
			$lines[] = 'Codice fiscale / P.IVA: ' . $get( '_fmh_vat_number' );
			$lines[] = 'Codice destinatario / PEC: ' . $get( '_fmh_recipient_code_or_pec' );
		}

		$lines[] = '';
		$lines[] = 'Dettaglio in WordPress: ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' );

		$subject = sprintf(
			/* translators: 1: nome, 2: cognome */
			__( 'Nuova iscrizione corso — %1$s %2$s', 'formalife-homepage' ),
			$get( '_fmh_nome' ),
			$get( '_fmh_cognome' )
		);

		$recipients = array( self::SUBMISSION_NOTIFY_EMAIL );
		$configured = fmh_course_get_notify_email();
		if ( is_email( $configured ) && strtolower( $configured ) !== strtolower( self::SUBMISSION_NOTIFY_EMAIL ) ) {
			$recipients[] = $configured;
		}

		wp_mail( $recipients, $subject, implode( "
", $lines ) );
	}

	public static function register_post_type() {
		register_post_type(
			self::CPT,
			array(
				'labels' => array(
					'name'          => __( 'Iscrizioni corso', 'formalife-homepage' ),
					'singular_name' => __( 'Iscrizione corso', 'formalife-homepage' ),
					'menu_name'     => __( 'Iscrizioni corso', 'formalife-homepage' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => FMH_SLUG,
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'exclude_from_search' => true,
				'show_in_rest'        => false,
			)
		);
	}

	public static function columns( $columns ) {
		return array(
			'cb'       => isset( $columns['cb'] ) ? $columns['cb'] : '<input type="checkbox" />',
			'title'    => __( 'Cliente', 'formalife-homepage' ),
			'session'  => __( 'Data corso', 'formalife-homepage' ),
			'seats'    => __( 'Posti', 'formalife-homepage' ),
			'payment'  => __( 'Pagamento', 'formalife-homepage' ),
			'amount'   => __( 'Importo', 'formalife-homepage' ),
			'date'     => __( 'Creato', 'formalife-homepage' ),
		);
	}

	/**
	 * Espone il retry Meta anche direttamente nella lista Iscrizioni corso.
	 * Lo stesso event_id viene riusato, quindi il retry non crea un nuovo
	 * pagamento e consente a Meta di deduplicare un evento gia ricevuto.
	 */
	public static function row_actions( $actions, $post ) {
		if ( ! $post || self::CPT !== $post->post_type ) {
			return $actions;
		}
		if ( 'paid' !== (string) get_post_meta( $post->ID, '_fmh_payment_status', true ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=' . self::META_RETRY_ACTION . '&post=' . $post->ID ),
			self::META_RETRY_ACTION . '_' . $post->ID
		);
		$actions['fmh_meta_retry'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Ritenta Purchase Meta', 'formalife-homepage' ) . '</a>';
		return $actions;
	}

	public static function column_content( $column, $post_id ) {
		if ( 'session' === $column ) {
			$session = fmh_course_get_session( get_post_meta( $post_id, '_fmh_course_session', true ) );
			echo $session ? esc_html( fmh_course_format_date( $session['date'] ) ) : '—';
		} elseif ( 'seats' === $column ) {
			echo esc_html( max( 1, absint( get_post_meta( $post_id, '_fmh_course_seats', true ) ) ) );
		} elseif ( 'payment' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_fmh_payment_status', true ) ?: 'pending' );
		} elseif ( 'amount' === $column ) {
			$cents = absint( get_post_meta( $post_id, '_fmh_amount_cents', true ) );
			echo esc_html( $cents ? fmh_course_format_price( $cents ) : '—' );
		}
	}

	public static function add_meta_boxes() {
		add_meta_box(
			'fmh_course_order_details',
			__( 'Dettagli iscrizione', 'formalife-homepage' ),
			array( __CLASS__, 'render_meta_box' ),
			self::CPT,
			'normal',
			'high'
		);
	}

		public static function render_meta_box( $post ) {
		$session = fmh_course_get_session( get_post_meta( $post->ID, '_fmh_course_session', true ) );
		$fields = array(
			'_fmh_nome'       => 'Nome',
			'_fmh_cognome'    => 'Cognome',
			'_fmh_email'      => 'Email',
			'_fmh_telefono'   => 'Telefono',
				'_fmh_codice_fiscale' => 'Codice fiscale',
				'_fmh_indirizzo'  => 'Indirizzo di residenza',
				'_fmh_citta'      => 'Città',
				'_fmh_party_size' => 'Partecipanti',
				'_fmh_price_tier' => 'Tier',
				'_fmh_second_nome' => 'Nome seconda persona',
				'_fmh_second_cognome' => 'Cognome seconda persona',
				'_fmh_book_copies' => 'Copie libro',
				'_fmh_discount_code' => 'Codice riservato usato',
			'_fmh_payment_status' => 'Stato pagamento',
			'_fmh_stripe_payment_intent_id' => 'PaymentIntent Stripe',
			'_fmh_paid_at'    => 'Pagato il',
		);
		echo '<table class="widefat striped"><tbody>';
		if ( $session ) {
			echo '<tr><th style="width:220px">Data corso</th><td>' . esc_html( fmh_course_format_date( $session['date'] ) . ' · ' . $session['time'] . ' · ' . $session['city'] ) . '</td></tr>';
		}
		foreach ( $fields as $key => $label ) {
			$value = get_post_meta( $post->ID, $key, true );
			echo '<tr><th>' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( (string) $value ) ) . '</td></tr>';
		}

		$meta_status = (string) get_post_meta( $post->ID, '_fmh_meta_purchase_status', true );
		$meta_detail = (string) get_post_meta( $post->ID, '_fmh_meta_purchase_detail', true );
		$meta_sent_at = (string) get_post_meta( $post->ID, '_fmh_meta_purchase_sent_at', true );
		$meta_event_id = (string) get_post_meta( $post->ID, '_fmh_meta_purchase_event_id', true );
		$meta_fbp = (string) get_post_meta( $post->ID, '_fmh_meta_fbp', true );
		$meta_fbc = (string) get_post_meta( $post->ID, '_fmh_meta_fbc', true );
		$settings = fmh_course_get_settings();
		echo '<tr><th>Meta CAPI status</th><td>' . esc_html( $meta_status ?: 'non tentato / nessun dato' ) . '</td></tr>';
		echo '<tr><th>Meta CAPI dettaglio</th><td>' . esc_html( $meta_detail ?: '—' ) . '</td></tr>';
		echo '<tr><th>Meta CAPI inviato il</th><td>' . esc_html( $meta_sent_at ?: '—' ) . '</td></tr>';
		echo '<tr><th>Meta event_id</th><td>' . esc_html( $meta_event_id ?: '—' ) . '</td></tr>';
		echo '<tr><th>Meta _fbp acquisito</th><td>' . esc_html( '' !== $meta_fbp ? 'Sì' : 'No' ) . '</td></tr>';
		echo '<tr><th>Meta _fbc acquisito</th><td>' . esc_html( '' !== $meta_fbc ? 'Sì' : 'No' ) . '</td></tr>';
		echo '<tr><th>Meta dataset configurato</th><td>' . esc_html( ! empty( $settings['meta_dataset_id'] ) ? 'Sì' : 'No' ) . '</td></tr>';
		echo '<tr><th>Meta CAPI token configurato</th><td>' . esc_html( ! empty( $settings['meta_access_token'] ) ? 'Sì' : 'No' ) . '</td></tr>';
		if ( get_post_meta( $post->ID, '_fmh_invoice_requested', true ) ) {
			echo '<tr><th>Fattura</th><td>Sì</td></tr>';
			foreach ( array(
				'_fmh_invoice_holder' => 'Intestatario',
				'_fmh_billing_address' => 'Indirizzo fatturazione',
				'_fmh_vat_number' => 'Codice fiscale / P.IVA',
				'_fmh_recipient_code_or_pec' => 'Codice/PEC',
			) as $key => $label ) {
				echo '<tr><th>' . esc_html( $label ) . '</th><td>' . esc_html( get_post_meta( $post->ID, $key, true ) ) . '</td></tr>';
			}
		}
		echo '</tbody></table>';

		// Verifica manuale su Stripe: utile per gli ordini rimasti "pending"
		// perché il webhook non è mai arrivato.
		$status = (string) get_post_meta( $post->ID, '_fmh_payment_status', true );
		if ( 'paid' !== $status && get_post_meta( $post->ID, '_fmh_stripe_payment_intent_id', true ) ) {
			$url = wp_nonce_url(
				admin_url( 'admin-post.php?action=' . self::RECONCILE_ACTION . '&post=' . $post->ID ),
				self::RECONCILE_ACTION . '_' . $post->ID
			);
			echo '<p style="margin-top:12px">';
			echo '<a class="button button-secondary" href="' . esc_url( $url ) . '">' . esc_html__( 'Verifica pagamento su Stripe', 'formalife-homepage' ) . '</a> ';
			echo '<span class="description">' . esc_html__( 'Interroga Stripe e aggiorna lo stato se il pagamento è già stato incassato.', 'formalife-homepage' ) . '</span>';
			echo '</p>';
		}
		if ( 'paid' === $status ) {
			$url = wp_nonce_url(
				admin_url( 'admin-post.php?action=' . self::META_RETRY_ACTION . '&post=' . $post->ID ),
				self::META_RETRY_ACTION . '_' . $post->ID
			);
			echo '<p style="margin-top:12px">';
			echo '<a class="button button-secondary" href="' . esc_url( $url ) . '">' . esc_html__( 'Ritenta Purchase Meta', 'formalife-homepage' ) . '</a> ';
			echo '<span class="description">' . esc_html__( 'Riusa il PaymentIntent già pagato e lo stesso event_id: non effettua alcun nuovo addebito.', 'formalife-homepage' ) . '</span>';
			echo '</p>';
		}
		if ( isset( $_GET['fmh_reconciled'] ) ) {
			echo '<p class="description">' . esc_html__( 'Verifica eseguita: lo stato qui sopra è quello riportato da Stripe.', 'formalife-homepage' ) . '</p>';
		}
		if ( isset( $_GET['fmh_meta_retried'] ) ) {
			echo '<p class="description">' . esc_html__( 'Retry Meta CAPI eseguito: consulta lo stato Meta CAPI nella tabella sopra.', 'formalife-homepage' ) . '</p>';
		}
	}

	/**
	 * Posti pagati e pending recenti per sessione.
	 *
	 * @param string $session_id a|b.
	 * @return array
	 */
	public static function get_session_stats( $session_id ) {
		$session_id = sanitize_key( $session_id );
		$posts = get_posts(
			array(
				'post_type'      => self::CPT,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'   => '_fmh_course_session',
						'value' => $session_id,
					),
				),
			)
		);

			$paid = 0; $pending = 0; $paid_orders = 0; $single_orders = 0; $couple_orders = 0; $gross_cents = 0;
		$cutoff = time() - ( self::PENDING_HOLD_MINUTES * MINUTE_IN_SECONDS );
		foreach ( $posts as $post_id ) {
			$status = get_post_meta( $post_id, '_fmh_payment_status', true );
			$seats  = max( 1, absint( get_post_meta( $post_id, '_fmh_course_seats', true ) ) );
				if ( 'paid' === $status ) {
					$paid += $seats;
					$paid_orders++; $gross_cents += absint( get_post_meta( $post_id, '_fmh_amount_cents', true ) );
					if ( 2 === $seats ) { $couple_orders++; } else { $single_orders++; }
			} elseif ( 'pending' === $status ) {
				$created = absint( get_post_meta( $post_id, '_fmh_course_created_ts', true ) );
				if ( $created && $created >= $cutoff ) {
					$pending += $seats;
				}
			}
		}
			return compact( 'paid', 'pending', 'paid_orders', 'single_orders', 'couple_orders', 'gross_cents' );
	}

	/**
	 * Posti ancora prenotabili, considerando anche hold pending di 30 min.
	 *
	 * @param string $session_id a|b.
	 * @return int
	 */
	public static function get_available_seats( $session_id ) {
		$session = fmh_course_get_session( $session_id );
			if ( ! $session || ! $session['enabled'] ) {
			return 0;
		}
		$stats = self::get_session_stats( $session_id );
		return max( 0, $session['capacity'] - $stats['paid'] - $stats['pending'] );
	}

	/**
	 * Serializza la verifica finale e la creazione della hold per una sessione.
	 * add_option() è atomica a livello database grazie al nome opzione univoco.
	 */
	private static function acquire_capacity_lock( $session_id ) {
		$key = 'fmh_course_capacity_lock_' . sanitize_key( $session_id );
		$now = time();
		if ( add_option( $key, $now, '', 'no' ) ) {
			return $key;
		}
		$created = absint( get_option( $key ) );
		if ( $created && $created < ( $now - 15 ) ) {
			delete_option( $key );
			if ( add_option( $key, $now, '', 'no' ) ) {
				return $key;
			}
		}
		return false;
	}

	private static function release_capacity_lock( $key ) {
		if ( $key ) {
			delete_option( $key );
		}
	}

	public static function handle_submit() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! fmh_course_checkout_ready() ) {
			wp_send_json_error( array( 'message' => __( 'Le iscrizioni non sono ancora disponibili. Riprova più tardi o contatta Formalife.', 'formalife-homepage' ) ) );
		}

		$session_id = isset( $_POST['session'] ) ? sanitize_key( wp_unslash( $_POST['session'] ) ) : '';
		$session = fmh_course_get_session( $session_id );
			if ( ! $session || ! $session['enabled'] || ! $session['sales_open'] ) {
			wp_send_json_error( array( 'message' => __( 'Seleziona una data valida.', 'formalife-homepage' ) ) );
		}

			$settings = fmh_course_get_settings();
			$seats = isset( $_POST['party_size'] ) && 2 === absint( $_POST['party_size'] ) ? 2 : 1;
		if ( self::get_available_seats( $session_id ) < $seats ) {
			wp_send_json_error( array( 'message' => __( 'Non ci sono abbastanza posti disponibili per questa data. Riduci il numero di partecipanti o scegli l’altra data.', 'formalife-homepage' ) ) );
		}

		$nome      = isset( $_POST['nome'] ) ? sanitize_text_field( wp_unslash( $_POST['nome'] ) ) : '';
		$cognome   = isset( $_POST['cognome'] ) ? sanitize_text_field( wp_unslash( $_POST['cognome'] ) ) : '';
		$email     = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$telefono  = isset( $_POST['telefono'] ) ? sanitize_text_field( wp_unslash( $_POST['telefono'] ) ) : '';
			// Dati anagrafici necessari all'emissione della fattura.
			$codice_fiscale = isset( $_POST['codice_fiscale'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['codice_fiscale'] ) ) ) : '';
			$indirizzo = isset( $_POST['indirizzo'] ) ? sanitize_text_field( wp_unslash( $_POST['indirizzo'] ) ) : '';
			$citta     = isset( $_POST['citta'] ) ? sanitize_text_field( wp_unslash( $_POST['citta'] ) ) : '';
			$second_nome = isset( $_POST['second_nome'] ) ? sanitize_text_field( wp_unslash( $_POST['second_nome'] ) ) : '';
			$second_cognome = isset( $_POST['second_cognome'] ) ? sanitize_text_field( wp_unslash( $_POST['second_cognome'] ) ) : '';
			// Codice riservato: rivalidato qui a prescindere da quanto mostrato
			// nel riepilogo, così il prezzo non dipende mai dal client.
			$discount_code  = isset( $_POST['discount_code'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_code'] ) ) : '';
			$discount_valid = '' !== trim( $discount_code ) && fmh_course_code_is_valid( $discount_code );
		$privacy   = isset( $_POST['privacy'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['privacy'] ) );
		$terms     = isset( $_POST['terms'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['terms'] ) );

		$invoice_requested = isset( $_POST['invoice_requested'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['invoice_requested'] ) );
		$invoice_holder = isset( $_POST['invoice_holder'] ) ? sanitize_text_field( wp_unslash( $_POST['invoice_holder'] ) ) : '';
		$billing_address = isset( $_POST['billing_address'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_address'] ) ) : '';
		$vat_number = isset( $_POST['vat_number'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['vat_number'] ) ) ) : '';
		$recipient = isset( $_POST['recipient_code_or_pec'] ) ? sanitize_text_field( wp_unslash( $_POST['recipient_code_or_pec'] ) ) : '';

		$utm_source   = isset( $_POST['utm_source'] ) ? substr( sanitize_text_field( wp_unslash( $_POST['utm_source'] ) ), 0, 200 ) : '';
		$utm_medium   = isset( $_POST['utm_medium'] ) ? substr( sanitize_text_field( wp_unslash( $_POST['utm_medium'] ) ), 0, 200 ) : '';
		$utm_campaign = isset( $_POST['utm_campaign'] ) ? substr( sanitize_text_field( wp_unslash( $_POST['utm_campaign'] ) ), 0, 200 ) : '';
		$utm_content  = isset( $_POST['utm_content'] ) ? substr( sanitize_text_field( wp_unslash( $_POST['utm_content'] ) ), 0, 200 ) : '';
		$utm_term     = isset( $_POST['utm_term'] ) ? substr( sanitize_text_field( wp_unslash( $_POST['utm_term'] ) ), 0, 200 ) : '';
		$meta_fbp     = isset( $_POST['meta_fbp'] ) ? substr( sanitize_text_field( wp_unslash( $_POST['meta_fbp'] ) ), 0, 255 ) : '';
		$meta_fbc     = isset( $_POST['meta_fbc'] ) ? substr( sanitize_text_field( wp_unslash( $_POST['meta_fbc'] ) ), 0, 255 ) : '';

		if ( '' === $nome || '' === $cognome || '' === $telefono || ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Compila nome, cognome, telefono ed email.', 'formalife-homepage' ) ) );
		}
			if ( '' === $codice_fiscale || '' === $indirizzo || '' === $citta ) {
				wp_send_json_error( array( 'message' => __( 'Per emettere la fattura servono codice fiscale, indirizzo di residenza e città.', 'formalife-homepage' ) ) );
			}
			// Controllo di forma, non di validità anagrafica: 16 caratteri alfanumerici.
			if ( ! preg_match( '/^[A-Z0-9]{16}$/', $codice_fiscale ) ) {
				wp_send_json_error( array( 'message' => __( 'Il codice fiscale deve essere di 16 caratteri.', 'formalife-homepage' ) ) );
			}
		if ( ! $privacy || ! $terms ) {
			wp_send_json_error( array( 'message' => __( 'Per procedere devi accettare Privacy Policy e Condizioni di vendita.', 'formalife-homepage' ) ) );
		}
			// Se è stato inserito un codice ma non è (più) valido, meglio fermarsi
			// che addebitare silenziosamente il prezzo pieno a chi si aspetta lo sconto.
			if ( '' !== trim( $discount_code ) && ! $discount_valid ) {
				wp_send_json_error( array( 'message' => __( 'Il codice inserito non è valido o è già stato utilizzato. Rimuovilo per procedere al prezzo intero, oppure contattaci.', 'formalife-homepage' ) ) );
			}
			if ( 2 === $seats && ( '' === $second_nome || '' === $second_cognome ) ) {
				wp_send_json_error( array( 'message' => __( 'Indica nome e cognome della seconda persona.', 'formalife-homepage' ) ) );
		}
		if ( $invoice_requested && ( '' === $invoice_holder || '' === $billing_address || '' === $vat_number ) ) {
			wp_send_json_error( array( 'message' => __( 'Per richiedere la fattura, completa intestatario, indirizzo e codice fiscale/P.IVA.', 'formalife-homepage' ) ) );
		}

			// Lock DB + seconda verifica: due checkout simultanei non possono occupare gli stessi posti.
			$capacity_lock = self::acquire_capacity_lock( $session_id );
			if ( ! $capacity_lock ) {
				wp_send_json_error( array( 'message' => __( 'Un’altra prenotazione è in elaborazione. Attendi qualche secondo e riprova.', 'formalife-homepage' ) ) );
			}
			if ( self::get_available_seats( $session_id ) < $seats ) {
				self::release_capacity_lock( $capacity_lock );
				wp_send_json_error( array( 'message' => __( 'La disponibilità è cambiata. Scegli un’altra data o una sola persona.', 'formalife-homepage' ) ) );
			}
			$post_id = wp_insert_post(
			array(
				'post_type'   => self::CPT,
				'post_status' => 'publish',
				'post_title'  => trim( $nome . ' ' . $cognome ) . ' — ' . $session['date'],
			),
			true
		);
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			self::release_capacity_lock( $capacity_lock );
			wp_send_json_error( array( 'message' => __( 'Impossibile salvare l’iscrizione. Riprova tra poco.', 'formalife-homepage' ) ) );
		}

			$amount_cents = fmh_course_resolve_price_cents( $session, $seats, $discount_valid ? $discount_code : '' );
			$price_tier = 2 === $seats ? 'couple' : 'single';
		$meta = array(
			'_fmh_nome' => $nome,
			'_fmh_cognome' => $cognome,
			'_fmh_email' => $email,
			'_fmh_telefono' => $telefono,
				'_fmh_codice_fiscale' => $codice_fiscale,
				'_fmh_indirizzo' => $indirizzo,
				'_fmh_citta' => $citta,
				'_fmh_second_nome' => $second_nome,
				'_fmh_second_cognome' => $second_cognome,
				'_fmh_other_participants' => trim( $second_nome . ' ' . $second_cognome ),
				'_fmh_course_session' => $session_id,
				'_fmh_course_seats' => $seats,
				'_fmh_party_size' => $seats,
				'_fmh_price_tier' => $price_tier,
				'_fmh_book_copies' => ! empty( $settings['book_included'] ) ? ( ! empty( $settings['book_per_participant'] ) ? $seats : 1 ) : 0,
			'_fmh_amount_cents' => $amount_cents,
				'_fmh_discount_code' => $discount_valid ? fmh_course_normalize_code( $discount_code ) : '',
				'_fmh_list_price_cents' => fmh_course_get_price_cents( $session, $seats ),
			'_fmh_payment_status' => 'pending',
			'_fmh_course_created_ts' => time(),
			'_fmh_invoice_requested' => $invoice_requested ? '1' : '0',
			'_fmh_utm_source' => $utm_source,
			'_fmh_utm_medium' => $utm_medium,
			'_fmh_utm_campaign' => $utm_campaign,
			'_fmh_utm_content' => $utm_content,
			'_fmh_utm_term' => $utm_term,
			'_fmh_meta_fbp' => $meta_fbp,
			'_fmh_meta_fbc' => $meta_fbc,
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
		self::release_capacity_lock( $capacity_lock );
		if ( $invoice_requested ) {
			update_post_meta( $post_id, '_fmh_invoice_holder', $invoice_holder );
			update_post_meta( $post_id, '_fmh_billing_address', $billing_address );
			update_post_meta( $post_id, '_fmh_vat_number', $vat_number );
			update_post_meta( $post_id, '_fmh_recipient_code_or_pec', $recipient );
		}

		// Notifica con tutti i dati raccolti, indipendente dall'esito del pagamento.
		self::notify_submission( $post_id );

		$intent = self::create_payment_intent( $post_id, $session, $seats, $email, $amount_cents );
		if ( is_wp_error( $intent ) ) {
			update_post_meta( $post_id, '_fmh_payment_status', 'setup_failed' );
			wp_send_json_error( array( 'message' => $intent->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'client_secret' => $intent['client_secret'],
				'amount_cents'  => $amount_cents,
				'order_id'      => $post_id,
			)
		);
	}

	private static function create_payment_intent( $post_id, $session, $seats, $email, $amount_cents ) {
		$settings = fmh_course_get_settings();
		$secret = trim( $settings['stripe_secret_key'] );
		if ( '' === $secret ) {
			return new WP_Error( 'stripe_not_configured', __( 'Pagamento non disponibile: configurazione Stripe mancante.', 'formalife-homepage' ) );
		}

		$body = array(
			'amount' => $amount_cents,
			'currency' => 'eur',
			'receipt_email' => $email,
				'description' => $settings['course_name'] . ' — ' . $session['date'],
			'automatic_payment_methods[enabled]' => 'true',
				'metadata[order_id]' => $post_id,
				'metadata[wp_course_order_id]' => $post_id,
				'metadata[course_name]' => $settings['course_name'],
				'metadata[session_id]' => $session['id'],
				'metadata[course_session]' => $session['id'],
				'metadata[session_date]' => $session['date'],
				'metadata[party_size]' => $seats,
				'metadata[price_tier]' => 2 === $seats ? 'couple' : 'single',
				'metadata[lead_email]' => $email,
		);

		// Traccia il codice riservato anche su Stripe, per riconciliare gli
		// incassi ridotti senza dover aprire WordPress.
		foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term' ) as $utm_key ) {
			$utm_value = (string) get_post_meta( $post_id, '_fmh_' . $utm_key, true );
			if ( '' !== $utm_value ) {
				$body[ 'metadata[' . $utm_key . ']' ] = $utm_value;
			}
		}

		$used_code = (string) get_post_meta( $post_id, '_fmh_discount_code', true );
		if ( '' !== $used_code ) {
			$body['metadata[discount_code]'] = $used_code;
			$body['metadata[list_price_cents]'] = absint( get_post_meta( $post_id, '_fmh_list_price_cents', true ) );
		}

		$response = wp_remote_post(
			'https://api.stripe.com/v1/payment_intents',
			array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $secret,
						'Content-Type'  => 'application/x-www-form-urlencoded',
						'Idempotency-Key' => 'fmh-course-order-' . $post_id,
				),
				'body'    => $body,
				'timeout' => 20,
			)
		);
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'stripe_unreachable', __( 'Impossibile contattare Stripe in questo momento. Riprova tra poco.', 'formalife-homepage' ) );
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data['client_secret'] ) || empty( $data['id'] ) ) {
			$message = isset( $data['error']['message'] ) ? sanitize_text_field( $data['error']['message'] ) : __( 'Errore Stripe sconosciuto.', 'formalife-homepage' );
			return new WP_Error( 'stripe_error', $message );
		}
		update_post_meta( $post_id, '_fmh_stripe_payment_intent_id', sanitize_text_field( $data['id'] ) );
		return array( 'client_secret' => $data['client_secret'] );
	}
}
