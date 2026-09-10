<?php
/**
 * Gestisce la creazione della pagina pubblica "Home" e il caricamento del
 * template dedicato che la renderizza. Stesso schema del plugin "Guida
 * Anti-Panico al Soffocamento Pediatrico": slug fisso, meta di
 * riconoscimento, opzione con l'ID pagina salvato, opzione per segnalare
 * un eventuale conflitto di slug con una pagina preesistente non gestita
 * dal plugin. Il registro è volutamente strutturato come un array di
 * pagine (anche se oggi ne contiene una sola), pronto per ospitare in
 * futuro altre pagine istituzionali con lo stesso meccanismo.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FMH_Page_Manager {

	/**
	 * Hook di inizializzazione (chiamato su plugins_loaded).
	 */
	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'load_template' ) );
		add_filter( 'display_post_states', array( __CLASS__, 'add_page_state' ), 10, 2 );
		add_action( 'admin_init', array( __CLASS__, 'maybe_upgrade_pages' ) );
	}

	/**
	 * Registro delle pagine gestite dal plugin.
	 *
	 * @return array<string, array>
	 */
	public static function get_registry() {
		return array(
			'home' => array(
				'slug'            => FMH_HOME_SLUG,
				'title'           => __( 'Home', 'formalife-homepage' ),
				'meta'            => FMH_HOME_PAGE_META,
				'option'          => FMH_HOME_PAGE_ID_OPTION,
				'conflict_option' => FMH_CONFLICT_OPTION_HOME,
				'template'        => 'template-home.php',
				'state_label'     => __( 'Home — Formalife', 'formalife-homepage' ),
				'admin_label'     => __( 'Homepage', 'formalife-homepage' ),
				'adopt_existing'  => false,
			),
			'course' => array(
				'slug'            => FMH_COURSE_SLUG,
				'title'           => __( 'Corso Anti-Panico al Soffocamento Pediatrico', 'formalife-homepage' ),
				'meta'            => FMH_COURSE_PAGE_META,
				'option'          => FMH_COURSE_PAGE_ID_OPTION,
				'conflict_option' => FMH_CONFLICT_OPTION_COURSE,
				'template'        => 'template-course.php',
				'state_label'     => __( 'Corso — Formalife', 'formalife-homepage' ),
				'admin_label'     => __( 'Corso Anti-Panico', 'formalife-homepage' ),
				// Lo slug esiste già nel sito: questa versione lo adotta senza
				// cancellare o sovrascrivere il contenuto WordPress esistente.
				'adopt_existing'  => true,
			),
			'course_thankyou' => array(
				'slug'            => FMH_COURSE_THANKYOU_SLUG,
				'title'           => __( 'Iscrizione corso confermata', 'formalife-homepage' ),
				'meta'            => FMH_COURSE_THANKYOU_PAGE_META,
				'option'          => FMH_COURSE_THANKYOU_PAGE_ID_OPTION,
				'conflict_option' => FMH_CONFLICT_OPTION_COURSE_THANKYOU,
				'template'        => 'template-course-thankyou.php',
				'state_label'     => __( 'Conferma corso — Formalife', 'formalife-homepage' ),
				'admin_label'     => __( 'Conferma corso', 'formalife-homepage' ),
				'adopt_existing'  => false,
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
		$course_page_id = self::get_page_id( 'course' );
		if ( $course_page_id && get_post( $course_page_id ) && get_post_meta( $course_page_id, FMH_COURSE_PAGE_META, true ) ) {
			wp_update_post( array( 'ID' => $course_page_id, 'post_title' => __( 'Corso Anti-Panico al Soffocamento Pediatrico', 'formalife-homepage' ) ) );
		}
		update_option( 'fmh_pages_version', FMH_VERSION );
	}

	/**
	 * Gli hook di attivazione non vengono eseguiti durante un normale update
	 * del plugin. Questa migrazione leggera assicura che le nuove pagine
	 * introdotte dalla v3 vengano create/adottate alla prima visita admin.
	 */
	public static function maybe_upgrade_pages() {
		if ( get_option( 'fmh_pages_version' ) === FMH_VERSION ) {
			return;
		}
		foreach ( self::get_registry() as $def ) {
			self::activate_single_page( $def );
		}
		$course_page_id = self::get_page_id( 'course' );
		if ( $course_page_id && get_post( $course_page_id ) && get_post_meta( $course_page_id, FMH_COURSE_PAGE_META, true ) ) {
			wp_update_post( array( 'ID' => $course_page_id, 'post_title' => __( 'Corso Anti-Panico al Soffocamento Pediatrico', 'formalife-homepage' ) ) );
		}
		update_option( 'fmh_pages_version', FMH_VERSION );
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

		// Verifica se esiste già una pagina pubblica con lo stesso slug
		// (molto probabile per "home": è lo slug che WordPress crea di
		// default). Se esiste e non è nostra, segnaliamo un conflitto
		// invece di modificarla: meglio un avviso in bacheca che una
		// sovrascrittura silenziosa di una pagina che l'admin potrebbe
		// già usare come home page nel sito.
		$page_by_path = get_page_by_path( $def['slug'], OBJECT, 'page' );

		if ( $page_by_path instanceof WP_Post ) {
			$is_ours = (bool) get_post_meta( $page_by_path->ID, $def['meta'], true );

			if ( $is_ours ) {
				update_option( $def['option'], $page_by_path->ID );
				return;
			}

			if ( ! empty( $def['adopt_existing'] ) ) {
				// Adozione esplicita prevista dal registro: conserva titolo e
				// contenuto della pagina esistente, aggiungendo soltanto il marker
				// che fa usare il template del plugin.
				update_post_meta( $page_by_path->ID, $def['meta'], 1 );
				update_post_meta( $page_by_path->ID, '_fmh_page_adopted', 1 );
				update_option( $def['option'], $page_by_path->ID );
				delete_option( $def['conflict_option'] );
				return;
			}

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
			delete_post_meta( $page_id, '_fmh_page_adopted' );
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
	 * ('home'), oppure 0 se non ancora creata.
	 *
	 * @param string $key Chiave della pagina nel registro.
	 * @return int
	 */
	public static function get_page_id( $key = 'home' ) {
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
	public static function get_page_url( $key = 'home' ) {
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
	 * header/footer del tema attivo.
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
				$custom_template = FMH_PLUGIN_DIR . 'templates/' . $def['template'];
				if ( file_exists( $custom_template ) ) {
					return $custom_template;
				}
			}
		}

		return $template;
	}

	/**
	 * Aggiunge un'etichetta accanto al titolo della pagina nell'elenco
	 * Pagine della bacheca, per riconoscere facilmente quella gestita dal
	 * plugin.
	 *
	 * @param array   $states Stati esistenti.
	 * @param WP_Post $post   Oggetto post.
	 * @return array
	 */
	public static function add_page_state( $states, $post ) {
		foreach ( self::get_registry() as $def ) {
			if ( get_post_meta( $post->ID, $def['meta'], true ) ) {
				$states['fmh_page'] = $def['state_label'];
				break;
			}
		}
		return $states;
	}
}
