<?php
/**
 * Caricamento di stili e script, sia lato frontend (solo sulle pagine
 * pubbliche gestite dal plugin: landing, condizioni di vendita, privacy,
 * grazie, i-miei-numeri) sia lato admin (solo sulla pagina impostazioni).
 *
 * v3.7: niente più Google Fonts (sostituiti da @font-face locali, vedi
 * assets/css/fonts.css), niente più Stripe.js caricato staticamente (vedi
 * assets/js/gaps-stripe-loader.js, richiamato solo al primo click su un
 * CTA), Meta Pixel opzionale e sempre ritardato (vedi
 * assets/js/gaps-meta-pixel-loader.js), dimensioni immagine dedicate
 * registrate per la copertina Hero e per l'anteprima responsive del libro.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GAPS_Assets {

	/**
	 * File WOFF2 critici (usati sopra la piega, nella barra in alto e nella
	 * Hero): sono gli unici precaricati in <head>, e solo se il file esiste
	 * già su disco in assets/fonts/ (nessun preload verso un file 404, vedi
	 * output_font_preloads()). Gli altri pesi/font dichiarati in
	 * assets/css/fonts.css (Lora, usato solo più in basso nella pagina) non
	 * vengono mai precaricati.
	 */
	const CRITICAL_FONT_FILES = array(
		'fredoka-600.woff2',
		'fredoka-700.woff2',
		'karla-400.woff2',
		'karla-600.woff2',
		'karla-700.woff2',
	);

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend' ) );
		add_action( 'wp_head', array( __CLASS__, 'output_font_preloads' ), 1 );
		add_action( 'wp_head', array( __CLASS__, 'output_og_tags' ), 5 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin' ) );
		add_action( 'after_setup_theme', array( __CLASS__, 'register_image_sizes' ) );
	}

	/**
	 * Registra le dimensioni immagine dedicate del plugin: copertina Hero
	 * (candidata LCP) e le due varianti responsive dell'anteprima libro
	 * (desktop/mobile). Nessun crop distruttivo (crop=false): l'immagine
	 * viene ridimensionata in proporzione, mai tagliata. Valgono solo per i
	 * nuovi caricamenti: gli allegati già in libreria usano la dimensione
	 * più vicina già disponibile (gestito automaticamente da
	 * wp_get_attachment_image(), che ricade su "full" se la dimensione
	 * dedicata non è mai stata generata).
	 */
	public static function register_image_sizes() {
		add_image_size( 'gaps-hero-cover', 900, 0, false );
		add_image_size( 'gaps-book-preview-desktop', 1400, 0, false );
		add_image_size( 'gaps-book-preview-mobile', 768, 0, false );
	}

	/**
	 * Restituisce la chiave del registro (vedi GAPS_Page_Manager::get_registry())
	 * corrispondente alla pagina gestita dal plugin correntemente richiesta,
	 * oppure stringa vuota se la richiesta corrente non è una nostra pagina.
	 *
	 * @return string
	 */
	private static function get_current_page_key() {
		if ( ! is_page() ) {
			return '';
		}
		$page_id = get_the_ID();
		foreach ( GAPS_Page_Manager::get_registry() as $key => $def ) {
			if ( get_post_meta( $page_id, $def['meta'], true ) ) {
				return $key;
			}
		}
		return '';
	}

	/**
	 * Verifica se la richiesta corrente è una qualsiasi pagina gestita dal
	 * plugin (landing, condizioni di vendita, privacy, grazie, i-miei-numeri).
	 *
	 * @return bool
	 */
	private static function is_plugin_page() {
		return '' !== self::get_current_page_key();
	}

	/**
	 * Stampa, solo sulle pagine del plugin e solo se il file esiste già su
	 * disco, il preload dei font WOFF2 usati sopra la piega (barra in alto
	 * + Hero). Deve girare prima di qualunque altro output in <head> (hook
	 * a priorità 1), altrimenti il preload arriverebbe troppo tardi per
	 * essere utile. Ogni file viene precaricato una sola volta: l'elenco in
	 * CRITICAL_FONT_FILES non contiene duplicati e questo metodo viene
	 * agganciato una sola volta a wp_head.
	 */
	public static function output_font_preloads() {
		if ( ! self::is_plugin_page() ) {
			return;
		}

		foreach ( self::CRITICAL_FONT_FILES as $font_file ) {
			$disk_path = GAPS_PLUGIN_DIR . 'assets/fonts/' . $font_file;
			if ( ! file_exists( $disk_path ) ) {
				// Nessun file ancora presente in assets/fonts/: niente
				// preload, per non generare una richiesta 404. Vedi
				// assets/fonts/README.txt per l'elenco dei file attesi.
				continue;
			}

			printf(
				'<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin>' . "\n",
				esc_url( GAPS_PLUGIN_URL . 'assets/fonts/' . $font_file )
			);
		}
	}

	/**
	 * Registra ed enqueue CSS/JS/font sulle pagine pubbliche del plugin.
	 * Font, variabili colore e componenti di base (pulsanti, tipografia)
	 * sono condivisi da tutte le pagine, per garantire lo stesso identico
	 * stile ovunque. Script e fogli di stile specifici restano invece per
	 * pagina: il popup di preordine (frontend.js) solo sulla landing, unica
	 * pagina che lo utilizza; le pagine legali caricano in aggiunta un
	 * foglio di stile dedicato alla tipografia dei testi lunghi; la pagina
	 * "Grazie" carica il proprio foglio di stile per il riepilogo ordine e
	 * il rimando al corso pratico.
	 */
	public static function enqueue_frontend() {
		$page_key = self::get_current_page_key();
		if ( '' === $page_key ) {
			return;
		}

		// Font locali (vedi assets/css/fonts.css): nessuna richiesta verso
		// fonts.googleapis.com / fonts.gstatic.com. Caricato come
		// dipendenza di gaps-frontend così le dichiarazioni @font-face sono
		// sempre disponibili prima del CSS che le usa.
		wp_enqueue_style(
			'gaps-fonts',
			GAPS_PLUGIN_URL . 'assets/css/fonts.css',
			array(),
			GAPS_VERSION
		);

		wp_enqueue_style(
			'gaps-frontend',
			GAPS_PLUGIN_URL . 'assets/css/frontend.css',
			array( 'gaps-fonts' ),
			GAPS_VERSION
		);

		// Variabili colore/sfondo dinamiche, generate dalle impostazioni salvate.
		wp_add_inline_style( 'gaps-frontend', self::build_dynamic_css() );

		if ( 'landing' === $page_key ) {
			$settings = gaps_get_settings();

			/*
			 * Stripe.js (https://js.stripe.com/v3/) NON viene più
			 * registrato/accodato qui: zero richieste verso js.stripe.com
			 * nel caricamento iniziale della pagina. Il loader condiviso
			 * (assets/js/gaps-stripe-loader.js) lo scarica — sempre e
			 * soltanto dal dominio ufficiale Stripe, mai auto-ospitato —
			 * solo al primo click su un CTA che apre il popup di
			 * preordine. "strategy: defer" (via wp_script_add_data, che
			 * non produce errori sulle versioni di WordPress meno recenti
			 * del 6.3: viene semplicemente ignorato) va bene per questo
			 * file perché è minuscolo e non scarica nulla da solo.
			 */
			wp_enqueue_script(
				'gaps-stripe-loader',
				GAPS_PLUGIN_URL . 'assets/js/gaps-stripe-loader.js',
				array(),
				GAPS_VERSION,
				true
			);
			wp_script_add_data( 'gaps-stripe-loader', 'strategy', 'defer' );

			wp_enqueue_script(
				'gaps-frontend',
				GAPS_PLUGIN_URL . 'assets/js/frontend.js',
				array( 'gaps-stripe-loader' ),
				GAPS_VERSION,
				true
			);
			wp_script_add_data( 'gaps-frontend', 'strategy', 'defer' );

			wp_localize_script(
				'gaps-frontend',
				'gapsFrontend',
				array(
					'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
					'action'             => GAPS_Preorder::AJAX_ACTION,
					'nonce'              => wp_create_nonce( GAPS_Preorder::NONCE_ACTION ),
					'stripePublishableKey' => $settings['stripe_publishable_key'],
					'unitPriceCents'     => gaps_get_price_cents(),
					'shippingCents'      => gaps_get_shipping_cents(),
					'thankYouUrl'        => gaps_get_thankyou_url(),
					'i18n'               => array(
						'sending'      => __( 'Invio in corso…', 'guida-antipanico-soffocamento' ),
						'genericError' => __( 'Qualcosa è andato storto. Riprova tra qualche istante.', 'guida-antipanico-soffocamento' ),
						'paying'       => __( 'Elaborazione del pagamento in corso…', 'guida-antipanico-soffocamento' ),
					),
				)
			);

			self::maybe_enqueue_meta_pixel();
		} elseif ( in_array( $page_key, array( 'terms', 'privacy' ), true ) ) {
			wp_enqueue_style(
				'gaps-legal',
				GAPS_PLUGIN_URL . 'assets/css/legal.css',
				array( 'gaps-frontend' ),
				GAPS_VERSION
			);
		} elseif ( 'thankyou' === $page_key ) {
			wp_enqueue_style(
				'gaps-thankyou',
				GAPS_PLUGIN_URL . 'assets/css/thankyou.css',
				array( 'gaps-frontend' ),
				GAPS_VERSION
			);
			wp_enqueue_script(
				'gaps-thankyou',
				GAPS_PLUGIN_URL . 'assets/js/thankyou.js',
				array(),
				GAPS_VERSION,
				true
			);
		} elseif ( 'numeri' === $page_key ) {
			wp_enqueue_style(
				'gaps-numeri',
				GAPS_PLUGIN_URL . 'assets/css/numeri.css',
				array( 'gaps-frontend' ),
				GAPS_VERSION
			);
		}
	}

	/**
	 * Accoda il loader condiviso di Meta Pixel, solo sulla landing e solo
	 * se un Pixel ID è stato configurato nel pannello impostazioni: se il
	 * campo è vuoto, questo metodo non fa nulla e nessuno script del Pixel
	 * viene mai caricato (nessuna richiesta verso connect.facebook.net).
	 * Il file è marcato "strategy: defer" e comunque il suo stesso codice
	 * attende window.load + tempo di inattività del browser prima di
	 * scaricare fbevents.js (vedi assets/js/gaps-meta-pixel-loader.js): non
	 * compete mai con HTML critico, CSS, font o immagine LCP.
	 */
	private static function maybe_enqueue_meta_pixel() {
		$pixel_id = gaps_get_meta_pixel_id();
		if ( '' === $pixel_id ) {
			return;
		}

		wp_enqueue_script(
			'gaps-meta-pixel-loader',
			GAPS_PLUGIN_URL . 'assets/js/gaps-meta-pixel-loader.js',
			array(),
			GAPS_VERSION,
			true
		);
		wp_script_add_data( 'gaps-meta-pixel-loader', 'strategy', 'defer' );

		wp_localize_script(
			'gaps-meta-pixel-loader',
			'gapsMetaPixel',
			array(
				'pixelId'        => $pixel_id,
				/**
				 * Filtro per i siti che vogliono richiedere sempre (o non
				 * richiedere mai) un consenso esplicito prima di caricare
				 * il Pixel. Di default true: il Pixel resta in attesa di
				 * window.gapsHasMarketingConsent() (vedi
				 * gaps-meta-pixel-loader.js per come integrarlo con il
				 * vero sistema di consenso del sito).
				 */
				'requireConsent' => (bool) apply_filters( 'gaps_meta_pixel_require_consent', true ),
			)
		);
	}

	/**
	 * Costruisce il blocco CSS che sovrascrive le custom properties di colore
	 * e lo sfondo dinamico della sezione "damaging admission", in base a
	 * quanto configurato nel pannello impostazioni.
	 *
	 * @return string
	 */
	private static function build_dynamic_css() {
		$settings = gaps_get_settings();

		$teal_primary = sanitize_hex_color( $settings['color_teal_primary'] );
		$teal_accent  = sanitize_hex_color( $settings['color_teal_accent'] );
		$cream_bg     = sanitize_hex_color( $settings['color_cream_bg'] );
		$peach_bg     = sanitize_hex_color( $settings['color_peach_bg'] );
		$terracotta   = sanitize_hex_color( $settings['color_terracotta'] );

		$teal_primary = $teal_primary ? $teal_primary : '#0B6560';
		$teal_accent  = $teal_accent ? $teal_accent : '#2CADA9';
		$cream_bg     = $cream_bg ? $cream_bg : '#FFFDF7';
		$peach_bg     = $peach_bg ? $peach_bg : '#F5D9A8';
		$terracotta   = $terracotta ? $terracotta : '#C24E3A';

		$teal_darker = self::darken_hex( $teal_primary, 0.15 );

		$css = ".gaps-landing{" .
			"--gaps-cream:{$cream_bg};" .
			"--gaps-cream-warm:{$peach_bg};" .
			"--gaps-teal-deep:{$teal_primary};" .
			"--gaps-teal-bright:{$teal_accent};" .
			"--gaps-teal-darker:{$teal_darker};" .
			"--gaps-terracotta:{$terracotta};" .
			'}';

		// Sfondo dinamico della sezione "damaging admission", se un'immagine è stata caricata.
		$bg_url = gaps_get_image_url( $settings['admission_bg_image_id'], 'full' );
		if ( $bg_url ) {
			$opacity = max( 0, min( 100, absint( $settings['admission_bg_opacity'] ) ) ) / 100;
			$blur    = max( 0, min( 20, absint( $settings['admission_bg_blur'] ) ) );
			$css    .= ".gaps-admission-section{--gaps-admission-bg-image:url('" . esc_url_raw( $bg_url ) . "');--gaps-admission-bg-opacity:{$opacity};--gaps-admission-bg-blur:{$blur}px;}";
		}

		return $css;
	}

	/**
	 * Scurisce un colore esadecimale di una percentuale data (0-1), per generare
	 * la variante "darker" usata negli stati hover, mantenendo un'unica fonte
	 * di verità (il colore primario impostato dall'admin).
	 *
	 * @param string $hex     Colore esadecimale, es. #0B6560.
	 * @param float  $percent Percentuale di scurimento (0-1).
	 * @return string
	 */
	private static function darken_hex( $hex, $percent ) {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( 6 !== strlen( $hex ) ) {
			return '#084F4B';
		}

		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		$r = max( 0, min( 255, (int) round( $r * ( 1 - $percent ) ) ) );
		$g = max( 0, min( 255, (int) round( $g * ( 1 - $percent ) ) ) );
		$b = max( 0, min( 255, (int) round( $b * ( 1 - $percent ) ) ) );

		return sprintf( '#%02X%02X%02X', $r, $g, $b );
	}

	/**
	 * Stampa i meta tag Open Graph / Twitter per la condivisione social,
	 * solo sulla landing page e solo se un'immagine social è stata impostata.
	 */
	public static function output_og_tags() {
		if ( ! self::is_plugin_page() ) {
			return;
		}

		$settings = gaps_get_settings();
		$og_url   = gaps_get_image_url( $settings['og_image_id'], 'full' );
		if ( ! $og_url ) {
			return;
		}

		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $og_url ) );
		printf( '<meta name="twitter:card" content="summary_large_image" />' . "\n" );
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $og_url ) );
	}

	/**
	 * Enqueue di script/stili solo sulla pagina impostazioni del plugin in admin.
	 *
	 * @param string $hook Hook della pagina admin corrente.
	 */
	public static function enqueue_admin( $hook ) {
		if ( 'toplevel_page_' . GAPS_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style(
			'gaps-admin',
			GAPS_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			GAPS_VERSION
		);

		wp_enqueue_script(
			'gaps-admin',
			GAPS_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			GAPS_VERSION,
			true
		);
	}
}
