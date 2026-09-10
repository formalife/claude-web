<?php
/**
 * Funzioni condivise: enqueue di token/componenti/font, preload dei font
 * critici. Ogni funzione è avvolta in function_exists() per sicurezza (in
 * caso di doppio caricamento accidentale), e ogni plugin consumatore
 * dovrebbe a sua volta verificare function_exists('formalife_core_enqueue')
 * prima di chiamarla, così se formalife-core non è attivo il sito degrada
 * ai font/colori di fallback dichiarati nel proprio CSS invece di un errore
 * fatale — vedi il header "Requires Plugins" del plugin consumatore, che
 * dovrebbe comunque impedire l'attivazione senza questo plugin (WP 6.5+).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'formalife_core_enqueue' ) ) :
	/**
	 * Registra ed esegue l'enqueue dei fogli di stile condivisi (token,
	 * font, componenti), nell'ordine di dipendenza corretto. Il plugin
	 * chiamante enqueua il proprio CSS con questi handle come dipendenza,
	 * così i token e le dichiarazioni @font-face sono sempre disponibili
	 * prima del CSS che li usa.
	 *
	 * @return array{tokens:string,fonts:string,components:string} Handle registrati.
	 */
	function formalife_core_enqueue() {
		wp_enqueue_style(
			'formalife-core-tokens',
			FMLS_CORE_PLUGIN_URL . 'assets/css/tokens.css',
			array(),
			FMLS_CORE_VERSION
		);

		wp_enqueue_style(
			'formalife-core-fonts',
			FMLS_CORE_PLUGIN_URL . 'assets/css/fonts.css',
			array( 'formalife-core-tokens' ),
			FMLS_CORE_VERSION
		);

		wp_enqueue_style(
			'formalife-core-components',
			FMLS_CORE_PLUGIN_URL . 'assets/css/components.css',
			array( 'formalife-core-fonts' ),
			FMLS_CORE_VERSION
		);

		return array(
			'tokens'     => 'formalife-core-tokens',
			'fonts'      => 'formalife-core-fonts',
			'components' => 'formalife-core-components',
		);
	}
endif;

if ( ! function_exists( 'formalife_core_output_font_preloads' ) ) :
	/**
	 * Stampa il preload di ciascun file WOFF2 indicato, solo se esiste già
	 * su disco (nessuna richiesta verso un file 404). Il chiamante passa la
	 * propria lista di pesi "sopra la piega": formalife-core non decide da
	 * solo quali pesi contano per ciascuna pagina di ciascun plugin.
	 *
	 * @param string[] $font_files Nomi file in assets/fonts/, es. 'fredoka-600.woff2'.
	 */
	function formalife_core_output_font_preloads( $font_files ) {
		foreach ( (array) $font_files as $font_file ) {
			$disk_path = FMLS_CORE_PLUGIN_DIR . 'assets/fonts/' . $font_file;
			if ( ! file_exists( $disk_path ) ) {
				continue;
			}

			printf(
				'<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin>' . "\n",
				esc_url( FMLS_CORE_PLUGIN_URL . 'assets/fonts/' . $font_file )
			);
		}
	}
endif;
