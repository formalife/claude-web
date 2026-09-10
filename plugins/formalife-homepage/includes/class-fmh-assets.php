<?php
/**
 * Asset frontend/admin per homepage e landing corso.
 *
 * v4.6.4: font e token di base non vengono più dal CSS/assets/fonts locali
 * di questo plugin, ma da formalife-core (stesso modello adottato da
 * guida-antipanico-soffocamento in v3.7.6) — vedi
 * docs/architettura-formalife-core.md. Ogni punto di integrazione resta
 * difensivo (function_exists()): se formalife-core non fosse attivo, le
 * pagine continuano a rendere con i valori di fallback dichiarati nel CSS
 * invece di un foglio di stile mancante.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FMH_Assets {
	const CRITICAL_FONT_FILES = array( 'fredoka-600.woff2', 'fredoka-700.woff2', 'karla-400.woff2', 'karla-600.woff2', 'karla-700.woff2' );
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend' ) );
		add_action( 'wp_head', array( __CLASS__, 'output_font_preloads' ), 1 );
		add_action( 'wp_head', array( __CLASS__, 'output_og_tags' ), 5 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin' ) );
		add_action( 'after_setup_theme', array( __CLASS__, 'register_image_sizes' ) );
	}

	private static function has_page_meta( $meta ) {
		return is_page() && (bool) get_post_meta( get_the_ID(), $meta, true );
	}

	private static function is_home_page() {
		return self::has_page_meta( FMH_HOME_PAGE_META );
	}

	private static function is_course_page() {
		return self::has_page_meta( FMH_COURSE_PAGE_META );
	}

	private static function is_course_thankyou_page() {
		return self::has_page_meta( FMH_COURSE_THANKYOU_PAGE_META );
	}

	private static function enqueue_shared_fonts_and_style() {
		// Token/font/componenti condivisi (v4.6.4): vedi formalife-core.
		// Guardia difensiva — "Requires Plugins" dovrebbe già impedire
		// l'attivazione di questo plugin senza formalife-core (WP 6.5+),
		// ma su versioni precedenti non è imposto: se manca, il CSS di
		// questo plugin ricade sui valori di fallback dichiarati in ogni
		// var(--fmls-..., <fallback>) invece di un foglio di stile mancante.
		$core_deps = array();
		if ( function_exists( 'formalife_core_enqueue' ) ) {
			$core_handles = formalife_core_enqueue();
			$core_deps    = array( $core_handles['components'] );
		}

		wp_enqueue_style(
			'fmh-frontend',
			FMH_PLUGIN_URL . 'assets/css/frontend.css',
			$core_deps,
			FMH_VERSION
		);
	}

	public static function enqueue_frontend() {
		if ( self::is_home_page() ) {
			self::enqueue_shared_fonts_and_style();
			wp_enqueue_script( 'fmh-frontend', FMH_PLUGIN_URL . 'assets/js/frontend.js', array(), FMH_VERSION, true );
			wp_localize_script(
				'fmh-frontend',
				'fmhFrontend',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'action'  => FMH_Waitlist::AJAX_ACTION,
					'nonce'   => wp_create_nonce( FMH_Waitlist::NONCE_ACTION ),
					'i18n'    => array(
						'sending'      => __( 'Invio in corso…', 'formalife-homepage' ),
						'genericError' => __( 'Qualcosa è andato storto. Riprova tra qualche istante.', 'formalife-homepage' ),
					),
				)
			);
			return;
		}

			if ( self::is_course_page() ) {
				self::enqueue_shared_fonts_and_style();
				wp_enqueue_style( 'fmh-course', FMH_PLUGIN_URL . 'assets/css/course.css', array( 'fmh-frontend' ), FMH_VERSION );
				$settings = fmh_course_get_settings();
				$letter_url = fmh_get_image_url( $settings['letter_bg_image_id'], 'large' );
				$final_url = fmh_get_image_url( $settings['final_bg_image_id'], 'fmh-course-hero' );
				$final_mobile_url = fmh_get_image_url( $settings['final_mobile_bg_image_id'], 'large' );
				$hero_hex = ltrim( sanitize_hex_color( $settings['hero_overlay_color'] ) ?: '#123f4a', '#' ); $final_hex = ltrim( sanitize_hex_color( $settings['final_overlay_color'] ) ?: '#0c3440', '#' );
				$hero_rgba = sprintf( 'rgba(%d,%d,%d,%.2F)', hexdec( substr( $hero_hex, 0, 2 ) ), hexdec( substr( $hero_hex, 2, 2 ) ), hexdec( substr( $hero_hex, 4, 2 ) ), absint( $settings['hero_overlay_opacity'] ) / 100 );
				$final_rgba = sprintf( 'rgba(%d,%d,%d,%.2F)', hexdec( substr( $final_hex, 0, 2 ) ), hexdec( substr( $final_hex, 2, 2 ) ), hexdec( substr( $final_hex, 4, 2 ) ), absint( $settings['final_overlay_opacity'] ) / 100 );
					$dynamic_css = '.fmh-course-page{--fmh-hero-focal-x:' . absint( $settings['hero_focal_x'] ) . '%;--fmh-hero-focal-y:' . absint( $settings['hero_focal_y'] ) . '%;--fmh-hero-blur:' . absint( $settings['hero_blur'] ) . 'px;--fmh-hero-overlay:' . $hero_rgba . ';--fmh-final-focal-x:' . absint( $settings['final_focal_x'] ) . '%;--fmh-final-focal-y:' . absint( $settings['final_focal_y'] ) . '%;--fmh-final-blur:' . absint( $settings['final_blur'] ) . 'px;--fmh-final-overlay:' . $final_rgba . ';--fmh-final-image:none;}';
			if ( $letter_url ) { $dynamic_css .= '.fmh-letter{background-image:linear-gradient(rgba(255,250,240,.92),rgba(255,250,240,.92)),url(' . esc_url_raw( $letter_url ) . ');}'; }
					if ( $final_url ) { $dynamic_css .= '.fmh-final{--fmh-final-image:url(' . esc_url_raw( $final_url ) . ');}'; }
					if ( $final_mobile_url ) { $dynamic_css .= '@media(max-width:680px){.fmh-final{--fmh-final-image:url(' . esc_url_raw( $final_mobile_url ) . ');}}'; }
			wp_add_inline_style( 'fmh-course', $dynamic_css );
			wp_enqueue_script( 'fmh-stripe-loader', FMH_PLUGIN_URL . 'assets/js/fmh-stripe-loader.js', array(), FMH_VERSION, true );
			wp_enqueue_script( 'fmh-course', FMH_PLUGIN_URL . 'assets/js/course.js', array( 'fmh-stripe-loader' ), FMH_VERSION, true );
			wp_script_add_data( 'fmh-stripe-loader', 'strategy', 'defer' );
			wp_script_add_data( 'fmh-course', 'strategy', 'defer' );

				$sessions = array();
			foreach ( fmh_course_get_sessions( true ) as $session ) {
				$stats = FMH_Course_Orders::get_session_stats( $session['id'] );
				$sessions[ $session['id'] ] = array(
					'id'        => $session['id'],
					'date'      => $session['date'],
					'label'     => fmh_course_format_date( $session['date'] ),
					'time'      => $session['time'],
					'city'      => $session['city'],
					'capacity'  => $session['capacity'],
					'paid'      => $stats['paid'],
					'available' => FMH_Course_Orders::get_available_seats( $session['id'] ),
					'enabled'   => $session['enabled'],
					'salesOpen' => $session['sales_open'],
					'singlePriceCents' => fmh_course_get_price_cents( $session, 1 ),
					'couplePriceCents' => fmh_course_get_price_cents( $session, 2 ),
				);
			}
			wp_localize_script(
				'fmh-course',
				'fmhCourseFrontend',
				array(
					'ajaxUrl'              => admin_url( 'admin-ajax.php' ),
					'action'               => FMH_Course_Orders::AJAX_ACTION,
					'checkCodeAction'      => FMH_Course_Orders::CHECK_CODE_ACTION,
					'nonce'                => wp_create_nonce( FMH_Course_Orders::NONCE_ACTION ),
					'stripePublishableKey' => $settings['stripe_publishable_key'],
					'singlePriceCents'     => absint( $settings['single_price_cents'] ),
					'couplePriceCents'     => absint( $settings['couple_price_cents'] ),
					'thankYouUrl'          => fmh_get_course_thankyou_url(),
					'checkoutReady'        => fmh_course_checkout_ready(),
					'sessions'             => $sessions,
					'i18n'                 => array(
						'genericError' => __( 'Qualcosa è andato storto. Riprova tra poco.', 'formalife-homepage' ),
						'loading'      => __( 'Preparazione pagamento sicuro…', 'formalife-homepage' ),
						'paying'       => __( 'Pagamento in corso…', 'formalife-homepage' ),
					),
				)
			);
			return;
		}

		if ( self::is_course_thankyou_page() ) {
			self::enqueue_shared_fonts_and_style();
			wp_enqueue_style( 'fmh-course', FMH_PLUGIN_URL . 'assets/css/course.css', array( 'fmh-frontend' ), FMH_VERSION );
		}
	}

	public static function register_image_sizes() {
		add_image_size( 'fmh-course-hero', 1200, 0, false );
		add_image_size( 'fmh-course-card', 720, 540, true );
	}

	public static function output_font_preloads() {
		if ( ! self::is_home_page() && ! self::is_course_page() && ! self::is_course_thankyou_page() ) { return; }
		if ( ! function_exists( 'formalife_core_output_font_preloads' ) ) {
			// formalife-core non attivo: nessun preload, il CSS ricade sui
			// font di sistema (font-display: optional già lo gestisce senza
			// errori).
			return;
		}
		formalife_core_output_font_preloads( self::CRITICAL_FONT_FILES );
	}

	public static function output_og_tags() {
		if ( self::is_home_page() ) {
			$settings = fmh_get_settings();
			$og_url = fmh_get_image_url( $settings['og_image_id'], 'full' );
		} elseif ( self::is_course_page() ) {
			$course = fmh_course_get_settings();
			$home   = fmh_get_settings();
			$og_id  = $course['og_image_id'] ? $course['og_image_id'] : $home['og_image_id'];
			$og_url = fmh_get_image_url( $og_id, 'full' );
		} else {
			return;
		}
		if ( ! $og_url ) {
			return;
		}
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $og_url ) );
		printf( '<meta name="twitter:card" content="summary_large_image" />' . "\n" );
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $og_url ) );
	}

	public static function enqueue_admin( $hook ) {
		$is_home_admin = ( 'toplevel_page_' . FMH_SLUG === $hook );
		$is_course_admin = ( false !== strpos( $hook, '_page_' . FMH_Course_Settings::MENU_SLUG ) );
		if ( ! $is_home_admin && ! $is_course_admin ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'fmh-admin', FMH_PLUGIN_URL . 'assets/css/admin.css', array(), FMH_VERSION );
		wp_enqueue_script( 'fmh-admin', FMH_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), FMH_VERSION, true );
	}
}
