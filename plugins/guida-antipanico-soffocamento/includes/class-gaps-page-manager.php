<?php
/**
 * Gestisce la creazione delle pagine pubbliche del plugin (landing di
 * prevendita, Condizioni di vendita, Privacy, Grazie — Ordine confermato) e
 * il caricamento dei template dedicati che le renderizzano. Tutte le pagine
 * seguono lo stesso schema: slug fisso, meta di riconoscimento, opzione con
 * l'ID pagina salvato, opzione per segnalare un eventuale conflitto di slug
 * con una pagina preesistente non gestita dal plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GAPS_Page_Manager {

	/**
	 * Hook di inizializzazione (chiamato su plugins_loaded).
	 */
	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'load_template' ) );
		add_filter( 'display_post_states', array( __CLASS__, 'add_page_state' ), 10, 2 );
	}

	/**
	 * Registro delle pagine gestite dal plugin: landing di prevendita,
	 * Condizioni di vendita, Privacy, Grazie — Ordine confermato. Aggiungere
	 * qui una nuova voce è sufficiente per far gestire al plugin
	 * un'ulteriore pagina pubblica con lo stesso meccanismo di
	 * creazione/template/conflitti.
	 *
	 * @return array<string, array>
	 */
	public static function get_registry() {
		return array(
			'landing'  => array(
				'slug'            => GAPS_SLUG,
				'title'           => __( 'La Guida Anti-Panico al Soffocamento Pediatrico', 'guida-antipanico-soffocamento' ),
				'meta'            => GAPS_PAGE_META,
				'option'          => GAPS_PAGE_ID_OPTION,
				'conflict_option' => GAPS_CONFLICT_OPTION,
				'template'        => 'template-landing.php',
				'state_label'     => __( 'Landing Guida Anti-Panico', 'guida-antipanico-soffocamento' ),
				'admin_label'     => __( 'Landing del libro', 'guida-antipanico-soffocamento' ),
			),
			'terms'    => array(
				'slug'            => GAPS_TERMS_SLUG,
				'title'           => __( 'Condizioni di vendita', 'guida-antipanico-soffocamento' ),
				'meta'            => GAPS_TERMS_PAGE_META,
				'option'          => GAPS_TERMS_PAGE_ID_OPTION,
				'conflict_option' => GAPS_CONFLICT_OPTION_TERMS,
				'template'        => 'template-legal.php',
				'state_label'     => __( 'Condizioni di vendita — Guida Anti-Panico', 'guida-antipanico-soffocamento' ),
				'admin_label'     => __( 'Condizioni di vendita', 'guida-antipanico-soffocamento' ),
			),
			'privacy'  => array(
				'slug'            => GAPS_PRIVACY_SLUG,
				'title'           => __( 'Privacy Policy', 'guida-antipanico-soffocamento' ),
				'meta'            => GAPS_PRIVACY_PAGE_META,
				'option'          => GAPS_PRIVACY_PAGE_ID_OPTION,
				'conflict_option' => GAPS_CONFLICT_OPTION_PRIVACY,
				'template'        => 'template-legal.php',
				'state_label'     => __( 'Privacy Policy — Guida Anti-Panico', 'guida-antipanico-soffocamento' ),
				'admin_label'     => __( 'Privacy Policy', 'guida-antipanico-soffocamento' ),
			),
			'thankyou' => array(
				'slug'            => GAPS_THANKYOU_SLUG,
				'title'           => __( 'Grazie — Ordine confermato', 'guida-antipanico-soffocamento' ),
				'meta'            => GAPS_THANKYOU_PAGE_META,
				'option'          => GAPS_THANKYOU_PAGE_ID_OPTION,
				'conflict_option' => GAPS_CONFLICT_OPTION_THANKYOU,
				'template'        => 'template-thankyou.php',
				'state_label'     => __( 'Grazie (thank you) — Guida Anti-Panico', 'guida-antipanico-soffocamento' ),
				'admin_label'     => __( 'Grazie — Ordine confermato', 'guida-antipanico-soffocamento' ),
			),
			'numeri'   => array(
				'slug'            => GAPS_NUMERI_SLUG,
				'title'           => __( 'I tuoi numeri importanti', 'guida-antipanico-soffocamento' ),
				'meta'            => GAPS_NUMERI_PAGE_META,
				'option'          => GAPS_NUMERI_PAGE_ID_OPTION,
				'conflict_option' => GAPS_CONFLICT_OPTION_NUMERI,
				'template'        => 'template-numeri.php',
				'state_label'     => __( 'I tuoi numeri importanti — Guida Anti-Panico', 'guida-antipanico-soffocamento' ),
				'admin_label'     => __( 'I tuoi numeri importanti (scheda QR)', 'guida-antipanico-soffocamento' ),
			),
		);
	}

	/**
	 * Eseguito all'attivazione del plugin: crea (o ricollega) tutte le
	 * pagine pubbliche registrate, senza mai sovrascrivere una pagina
	 * esistente non creata da questo plugin.
	 */
	public static function on_activation() {
		foreach ( self::get_registry() as $def ) {
			self::activate_single_page( $def );
		}
	}

	/**
	 * Crea o ricollega una singola pagina gestita, secondo la sua definizione
	 * nel registro.
	 *
	 * @param array $def Definizione della pagina (vedi get_registry()).
	 */
	private static function activate_single_page( $def ) {
		delete_option( $def['conflict_option'] );

		$existing_page_id = (int) get_option( $def['option'] );

		// Se abbiamo già una pagina registrata e ancora esistente, non fare nulla.
		if ( $existing_page_id && get_post( $existing_page_id ) ) {
			$post = get_post( $existing_page_id );
			if ( 'trash' !== $post->post_status ) {
				return;
			}
		}

		// Verifica se esiste già una pagina pubblica con lo stesso slug.
		$page_by_path = get_page_by_path( $def['slug'], OBJECT, 'page' );

		if ( $page_by_path instanceof WP_Post ) {
			$is_ours = (bool) get_post_meta( $page_by_path->ID, $def['meta'], true );

			if ( $is_ours ) {
				// È la nostra pagina (magari ricreata da noi in passato): la ricolleghiamo.
				update_option( $def['option'], $page_by_path->ID );
				return;
			}

			// Conflitto: esiste già una pagina con questo slug non gestita da noi.
			update_option( $def['conflict_option'], $page_by_path->ID );
			return;
		}

		// Nessun conflitto: creiamo la pagina.
		$page_id = wp_insert_post(
			array(
				'post_title'     => $def['title'],
				'post_name'      => $def['slug'],
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_content'   => '',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			true
		);

		if ( ! is_wp_error( $page_id ) && $page_id ) {
			update_post_meta( $page_id, $def['meta'], 1 );
			update_option( $def['option'], $page_id );
		}
	}

	/**
	 * Eseguito alla disattivazione del plugin.
	 * Non elimina le pagine né le impostazioni: solo l'uninstall esplicito lo fa.
	 */
	public static function on_deactivation() {
		// Intenzionalmente vuoto: nessuna eliminazione di dati alla disattivazione.
	}

	/**
	 * Restituisce l'ID della pagina gestita corrispondente alla chiave data
	 * ('landing', 'terms', 'privacy', 'thankyou'), oppure 0 se non ancora creata.
	 *
	 * @param string $key Chiave della pagina nel registro.
	 * @return int
	 */
	public static function get_page_id( $key = 'landing' ) {
		$registry = self::get_registry();
		if ( ! isset( $registry[ $key ] ) ) {
			return 0;
		}
		return (int) get_option( $registry[ $key ]['option'] );
	}

	/**
	 * Restituisce il permalink della pagina gestita corrispondente alla
	 * chiave data, oppure stringa vuota se non ancora creata.
	 *
	 * @param string $key Chiave della pagina nel registro.
	 * @return string
	 */
	public static function get_page_url( $key = 'landing' ) {
		$page_id = self::get_page_id( $key );
		if ( ! $page_id ) {
			return '';
		}
		$url = get_permalink( $page_id );
		return $url ? $url : '';
	}

	/**
	 * Se la richiesta corrente corrisponde a una delle nostre pagine
	 * pubbliche, sostituisce il template con quello dedicato, bypassando
	 * header/footer del tema attivo: le pagine del plugin restano un
	 * funnel isolato, coerente su landing, condizioni, privacy e grazie.
	 *
	 * @param string $template Percorso del template scelto da WordPress.
	 * @return string
	 */
	public static function load_template( $template ) {
		if ( ! is_page() ) {
			return $template;
		}

		$page_id = get_the_ID();

		foreach ( self::get_registry() as $def ) {
			if ( get_post_meta( $page_id, $def['meta'], true ) ) {
				$custom_template = GAPS_PLUGIN_DIR . 'templates/' . $def['template'];
				if ( file_exists( $custom_template ) ) {
					return $custom_template;
				}
			}
		}

		return $template;
	}

	/**
	 * Aggiunge un'etichetta accanto al titolo della pagina nell'elenco
	 * Pagine della bacheca, per riconoscere facilmente quelle gestite dal
	 * plugin (landing, condizioni di vendita, privacy, grazie).
	 *
	 * @param array   $states Stati esistenti.
	 * @param WP_Post $post   Oggetto post.
	 * @return array
	 */
	public static function add_page_state( $states, $post ) {
		foreach ( self::get_registry() as $def ) {
			if ( get_post_meta( $post->ID, $def['meta'], true ) ) {
				$states['gaps_page'] = $def['state_label'];
				break;
			}
		}
		return $states;
	}
}
