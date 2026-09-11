<?php
/**
 * Plugin Name:       Formalife — Homepage
 * Plugin URI:         https://formalife.it
 * Description:        Homepage Formalife + Corso Anti-Panico al Soffocamento Pediatrico, con sessioni dinamiche e checkout Stripe embedded.
 * Version:             4.6.6
 * Requires at least:   6.0
 * Requires PHP:        7.4
 * Author:              Formalife
 * Text Domain:         formalife-homepage
 * License:             GPL v2 or later
 * Requires Plugins:    formalife-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Nessun accesso diretto.
}

/* -----------------------------------------------------------------------
 * Costanti del plugin.
 * Prefisso "FMH_" (Formalife HoMepage) per non entrare mai in conflitto
 * con le costanti "GAPS_*" del plugin "Guida Anti-Panico al Soffocamento
 * Pediatrico", pensato per restare attivo in parallelo sullo stesso sito.
 * ---------------------------------------------------------------------*/
define( 'FMH_VERSION', '4.6.6' );
define( 'FMH_PLUGIN_FILE', __FILE__ );
define( 'FMH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FMH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FMH_SLUG', 'formalife-homepage' );
define( 'FMH_OPTION_KEY', 'fmh_settings' );

/* -----------------------------------------------------------------------
 * Costanti — pagina Home. Stesso meccanismo delle pagine gestite dal
 * plugin del libro: slug fisso, meta di riconoscimento, opzione per l'ID
 * pagina, opzione per eventuali conflitti di slug con una pagina
 * preesistente non gestita da questo plugin.
 * ---------------------------------------------------------------------*/
define( 'FMH_HOME_SLUG', 'home' );
define( 'FMH_HOME_PAGE_META', '_fmh_home_page' );
define( 'FMH_HOME_PAGE_ID_OPTION', 'fmh_home_page_id' );
define( 'FMH_CONFLICT_OPTION_HOME', 'fmh_page_conflict_home' );

/* -----------------------------------------------------------------------
 * Costanti — landing corso e pagina di conferma. Lo slug legacy resta invariato.
 * La landing viene gestita dallo stesso plugin della homepage per
 * condividere stile, dati aziendali e componenti, mantenendo però
 * impostazioni e ordini del corso separati da quelli della homepage.
 * ---------------------------------------------------------------------*/
define( 'FMH_COURSE_SLUG', 'genitori-pronti' );
define( 'FMH_COURSE_PAGE_META', '_fmh_course_page' );
define( 'FMH_COURSE_PAGE_ID_OPTION', 'fmh_course_page_id' );
define( 'FMH_CONFLICT_OPTION_COURSE', 'fmh_page_conflict_course' );
define( 'FMH_COURSE_THANKYOU_SLUG', 'corso-confermato' );
define( 'FMH_COURSE_THANKYOU_PAGE_META', '_fmh_course_thankyou_page' );
define( 'FMH_COURSE_THANKYOU_PAGE_ID_OPTION', 'fmh_course_thankyou_page_id' );
define( 'FMH_CONFLICT_OPTION_COURSE_THANKYOU', 'fmh_page_conflict_course_thankyou' );
define( 'FMH_COURSE_OPTION_KEY', 'fmh_course_settings' );

/* -----------------------------------------------------------------------
 * Include dei file della logica del plugin.
 * ---------------------------------------------------------------------*/
require_once FMH_PLUGIN_DIR . 'includes/fmh-settings-helpers.php';
require_once FMH_PLUGIN_DIR . 'includes/fmh-course-helpers.php';
require_once FMH_PLUGIN_DIR . 'includes/class-fmh-course-settings.php';
require_once FMH_PLUGIN_DIR . 'includes/class-fmh-course-orders.php';
require_once FMH_PLUGIN_DIR . 'includes/class-fmh-meta-capi.php';
require_once FMH_PLUGIN_DIR . 'includes/class-fmh-course-stripe-webhook.php';
require_once FMH_PLUGIN_DIR . 'includes/class-fmh-settings.php';
require_once FMH_PLUGIN_DIR . 'includes/class-fmh-page-manager.php';
require_once FMH_PLUGIN_DIR . 'includes/class-fmh-waitlist.php';
require_once FMH_PLUGIN_DIR . 'includes/class-fmh-assets.php';
require_once FMH_PLUGIN_DIR . 'includes/class-fmh-admin-notices.php';

/* -----------------------------------------------------------------------
 * Hook di attivazione / disattivazione / init.
 * ---------------------------------------------------------------------*/
register_activation_hook( FMH_PLUGIN_FILE, array( 'FMH_Page_Manager', 'on_activation' ) );
register_deactivation_hook( FMH_PLUGIN_FILE, array( 'FMH_Page_Manager', 'on_deactivation' ) );

add_action( 'plugins_loaded', 'fmh_bootstrap' );

/**
 * Inizializza tutte le classi del plugin.
 */
function fmh_bootstrap() {
	FMH_Page_Manager::init();
	FMH_Settings::init();
	FMH_Course_Settings::init();
	FMH_Course_Orders::init();
	FMH_Course_Stripe_Webhook::init();
	FMH_Waitlist::init();
	FMH_Assets::init();
	FMH_Admin_Notices::init();
}
