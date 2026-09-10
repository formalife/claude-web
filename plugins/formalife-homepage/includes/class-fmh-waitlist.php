<?php
/**
 * Gestisce la lista d'attesa per l'apertura delle iscrizioni al corso
 * pratico (Sezione 6 della homepage): un modulo inline, senza popup, che
 * salva il contatto in un custom post type privato e notifica l'admin via
 * email. Nessuna integrazione di pagamento: qui non si vende nulla, si
 * raccoglie solo un consenso esplicito ad essere avvisati in futuro.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FMH_Waitlist {

	const CPT          = 'fmh_waitlist_lead';
	const NONCE_ACTION  = 'fmh_waitlist_nonce';
	const AJAX_ACTION   = 'fmh_join_waitlist';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'handle_submit' ) );
		add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, array( __CLASS__, 'handle_submit' ) );

		add_filter( 'manage_' . self::CPT . '_posts_columns', array( __CLASS__, 'add_columns' ) );
		add_action( 'manage_' . self::CPT . '_posts_custom_column', array( __CLASS__, 'render_column' ), 10, 2 );
	}

	/**
	 * Registra il custom post type "Lista d'attesa corso", visibile solo in
	 * bacheca come sottomenu della voce "Formalife Home" (non pubblico sul sito).
	 */
	public static function register_post_type() {
		register_post_type(
			self::CPT,
			array(
				'labels'          => array(
					'name'          => __( 'Lista d\'attesa corso', 'formalife-homepage' ),
					'singular_name' => __( 'Iscritto lista d\'attesa', 'formalife-homepage' ),
					'all_items'     => __( 'Lista d\'attesa corso', 'formalife-homepage' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => FMH_SLUG,
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
	 * Colonne personalizzate nell'elenco, per vedere i dati principali a colpo d'occhio.
	 *
	 * @param array $columns Colonne esistenti.
	 * @return array
	 */
	public static function add_columns( $columns ) {
		return array(
			'cb'          => $columns['cb'],
			'title'       => __( 'Nome', 'formalife-homepage' ),
			'fmh_email'   => __( 'Email', 'formalife-homepage' ),
			'date'        => $columns['date'],
		);
	}

	/**
	 * Stampa il contenuto della colonna email.
	 *
	 * @param string $column  Nome colonna.
	 * @param int    $post_id ID del post.
	 */
	public static function render_column( $column, $post_id ) {
		if ( 'fmh_email' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_fmh_email', true ) );
		}
	}

	/**
	 * Gestisce l'invio AJAX del modulo "Voglio essere avvisato/a" (Sezione 6).
	 * Salva il contatto, invia una notifica email all'admin e restituisce un
	 * messaggio di conferma testuale (mostrato inline sotto il modulo,
	 * nessun popup, nessun reindirizzamento).
	 */
	public static function handle_submit() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$nome  = isset( $_POST['nome'] ) ? sanitize_text_field( wp_unslash( $_POST['nome'] ) ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$consenso = isset( $_POST['consenso'] ) && '1' === $_POST['consenso'];

		if ( '' === $nome ) {
			wp_send_json_error( array( 'message' => __( 'Inserisci il tuo nome e cognome.', 'formalife-homepage' ) ) );
		}

		if ( '' === $email || ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Inserisci un indirizzo email valido.', 'formalife-homepage' ) ) );
		}

		if ( ! $consenso ) {
			wp_send_json_error( array( 'message' => __( 'Per essere avvisato/a devi acconsentire a essere ricontattato/a.', 'formalife-homepage' ) ) );
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => self::CPT,
				'post_status' => 'publish',
				'post_title'  => '' !== $nome ? $nome : $email,
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			wp_send_json_error( array( 'message' => __( 'Si è verificato un errore nel salvataggio. Riprova tra qualche istante.', 'formalife-homepage' ) ) );
		}

		update_post_meta( $post_id, '_fmh_nome', $nome );
		update_post_meta( $post_id, '_fmh_email', $email );

		self::notify_admin( $post_id, $nome, $email );

		wp_send_json_success(
			array(
				'message' => __( 'Fatto! Ti scriveremo non appena apriamo le iscrizioni al corso.', 'formalife-homepage' ),
			)
		);
	}

	/**
	 * Invia una email di notifica per ogni nuova iscrizione alla lista d'attesa.
	 *
	 * @param int    $post_id ID del contatto appena salvato.
	 * @param string $nome    Nome (facoltativo).
	 * @param string $email   Email.
	 */
	private static function notify_admin( $post_id, $nome, $email ) {
		$settings = fmh_get_settings();
		$to       = '' !== trim( $settings['notify_email'] ) ? $settings['notify_email'] : get_option( 'admin_email' );

		$subject = __( 'Nuova iscrizione alla lista d\'attesa del corso pratico', 'formalife-homepage' );
		$body    = __( 'Qualcuno si è iscritto per essere avvisato dell\'apertura delle iscrizioni al corso pratico Formalife.', 'formalife-homepage' ) . "\n\n";
		$body   .= __( 'Nome:', 'formalife-homepage' ) . ' ' . ( '' !== $nome ? $nome : '(non fornito)' ) . "\n";
		$body   .= __( 'Email:', 'formalife-homepage' ) . ' ' . $email . "\n";
		$body   .= "\n" . __( 'Elenco completo in bacheca:', 'formalife-homepage' ) . ' ' . admin_url( 'edit.php?post_type=' . self::CPT ) . "\n";

		wp_mail( $to, $subject, $body );
	}
}
