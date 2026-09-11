<?php
/**
 * Plugin Name:       Formalife Core — Stile e componenti condivisi
 * Plugin URI:        https://formalife.it
 * Description:       Fondamenta condivise per i plugin Formalife: token di design (colori, font, spaziature), componenti CSS di base (pulsanti, guscio del popup, primitive di form) e un piccolo helper per i pannelli impostazioni. Non genera nessuna pagina pubblica da solo: altri plugin (es. guida-antipanico-soffocamento) lo richiamano per non riscrivere lo stesso stile e lo stesso layout ogni volta. Va attivato PRIMA dei plugin che ne dipendono.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Formalife
 * Text Domain:       formalife-core
 * License:           GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Nessun accesso diretto.
}

define( 'FMLS_CORE_VERSION', '1.1.0' );
define( 'FMLS_CORE_PLUGIN_FILE', __FILE__ );
define( 'FMLS_CORE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FMLS_CORE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once FMLS_CORE_PLUGIN_DIR . 'includes/formalife-core-functions.php';
require_once FMLS_CORE_PLUGIN_DIR . 'includes/class-formalife-settings-field.php';
