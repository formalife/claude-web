<?php
/**
 * Plugin Name:       Guida Anti-Panico al Soffocamento Pediatrico — Vendita libro
 * Plugin URI:         https://formalife.it
 * Description:        Landing page di vendita per "La Guida Anti-Panico al Soffocamento Pediatrico" (Formalife), con pagine "Condizioni di vendita", "Privacy", "Grazie — Ordine confermato" e "I tuoi numeri importanti" (scheda in omaggio, raggiungibile dal QR code stampato nel libro) generate automaticamente nello stesso stile. Popup d'acquisto con fatturazione facoltativa e pagamento Stripe integrato (Payment Element); nessuna email alla compilazione del modulo, email di ringraziamento al cliente e notifica interna separata solo a pagamento realmente confermato via webhook Stripe. Pannello impostazioni per immagini, colori, prezzo, date, dati statistici, dati legali/aziendali e link al corso pratico.
 * Version:             3.7.5
 * Requires at least:   6.0
 * Requires PHP:        7.4
 * Author:              Formalife
 * Text Domain:         guida-antipanico-soffocamento
 * License:             GPL v2 or later
 * Update URI:          https://github.com/formalife/plugin-guida-al-soffocamento/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Nessun accesso diretto.
}

/* -----------------------------------------------------------------------
 * Costanti del plugin
 * ---------------------------------------------------------------------*/
define( 'GAPS_VERSION', '3.7.5' );
define( 'GAPS_PLUGIN_FILE', __FILE__ );
define( 'GAPS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GAPS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GAPS_SLUG', 'guida-antipanico-soffocamento' );
define( 'GAPS_UPDATE_REPOSITORY', 'https://github.com/formalife/plugin-guida-al-soffocamento/' );
define( 'GAPS_OPTION_KEY', 'gaps_settings' );
define( 'GAPS_PAGE_ID_OPTION', 'gaps_page_id' );
define( 'GAPS_CONFLICT_OPTION', 'gaps_page_conflict' );
define( 'GAPS_PAGE_META', '_gaps_landing_page' );

/* -----------------------------------------------------------------------
 * Costanti — pagine legali (Condizioni di vendita, Privacy).
 * Stesso meccanismo della landing: slug fisso, meta di riconoscimento,
 * opzione per l'ID pagina, opzione per eventuali conflitti di slug.
 * ---------------------------------------------------------------------*/
define( 'GAPS_TERMS_SLUG', 'condizioni-di-vendita' );
define( 'GAPS_TERMS_PAGE_META', '_gaps_terms_page' );
define( 'GAPS_TERMS_PAGE_ID_OPTION', 'gaps_terms_page_id' );
define( 'GAPS_CONFLICT_OPTION_TERMS', 'gaps_page_conflict_terms' );

define( 'GAPS_PRIVACY_SLUG', 'privacy' );
define( 'GAPS_PRIVACY_PAGE_META', '_gaps_privacy_page' );
define( 'GAPS_PRIVACY_PAGE_ID_OPTION', 'gaps_privacy_page_id' );
define( 'GAPS_CONFLICT_OPTION_PRIVACY', 'gaps_page_conflict_privacy' );

/* -----------------------------------------------------------------------
 * Costanti — pagina "Grazie — Ordine confermato" (thank you page
 * post-acquisto). Stesso meccanismo di landing/condizioni/privacy: slug
 * fisso, meta di riconoscimento, opzione per l'ID pagina, opzione per
 * eventuali conflitti di slug. Questa è la pagina da impostare come
 * destinazione "dopo il pagamento" nel Payment Link Stripe.
 * ---------------------------------------------------------------------*/
define( 'GAPS_THANKYOU_SLUG', 'grazie-ordine-confermato' );
define( 'GAPS_THANKYOU_PAGE_META', '_gaps_thankyou_page' );
define( 'GAPS_THANKYOU_PAGE_ID_OPTION', 'gaps_thankyou_page_id' );
define( 'GAPS_CONFLICT_OPTION_THANKYOU', 'gaps_page_conflict_thankyou' );

/* -----------------------------------------------------------------------
 * Costanti — pagina "I tuoi numeri importanti" (v3.5.3). Scheda stampabile
 * in omaggio, raggiungibile dal QR code stampato nelle ultime pagine del
 * libro: permette il download gratuito del PDF (un click, nessun modulo) e
 * chiude con una richiesta, non invasiva, di recensione su Google. Stesso
 * meccanismo di slug fisso/meta/opzione/conflitto delle altre pagine.
 * ---------------------------------------------------------------------*/
define( 'GAPS_NUMERI_SLUG', 'i-miei-numeri' );
define( 'GAPS_NUMERI_PAGE_META', '_gaps_numeri_page' );
define( 'GAPS_NUMERI_PAGE_ID_OPTION', 'gaps_numeri_page_id' );
define( 'GAPS_CONFLICT_OPTION_NUMERI', 'gaps_page_conflict_numeri' );

/* -----------------------------------------------------------------------
 * Aggiornamenti dal repository GitHub ufficiale.
 *
 * Plugin Update Checker e' incluso nel pacchetto: sul sito WordPress non
 * servono Composer, token GitHub o altri plugin. Le release devono essere
 * pubbliche e contenere lo ZIP generato dal workflow del repository.
 * ---------------------------------------------------------------------*/
require_once GAPS_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';

$gaps_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	GAPS_UPDATE_REPOSITORY,
	GAPS_PLUGIN_FILE,
	GAPS_SLUG
);

$gaps_update_checker->setBranch( 'main' );
$gaps_update_checker->getVcsApi()->enableReleaseAssets(
	'/guida-antipanico-soffocamento(?:-v?[0-9.]+)?\.zip($|[?&#])/i',
	\YahnisElsts\PluginUpdateChecker\v5p7\Vcs\Api::REQUIRE_RELEASE_ASSETS
);

/* -----------------------------------------------------------------------
 * Include dei file della logica del plugin
 * ---------------------------------------------------------------------*/
require_once GAPS_PLUGIN_DIR . 'includes/gaps-settings-helpers.php';
require_once GAPS_PLUGIN_DIR . 'includes/gaps-legal-content.php';
require_once GAPS_PLUGIN_DIR . 'includes/class-gaps-settings.php';
require_once GAPS_PLUGIN_DIR . 'includes/class-gaps-page-manager.php';
require_once GAPS_PLUGIN_DIR . 'includes/class-gaps-preorder.php';
require_once GAPS_PLUGIN_DIR . 'includes/class-gaps-stripe-webhook.php';
require_once GAPS_PLUGIN_DIR . 'includes/class-gaps-assets.php';
require_once GAPS_PLUGIN_DIR . 'includes/class-gaps-admin-notices.php';

/* -----------------------------------------------------------------------
 * Hook di attivazione / disattivazione / init
 * ---------------------------------------------------------------------*/
register_activation_hook( GAPS_PLUGIN_FILE, array( 'GAPS_Page_Manager', 'on_activation' ) );
register_deactivation_hook( GAPS_PLUGIN_FILE, array( 'GAPS_Page_Manager', 'on_deactivation' ) );

add_action( 'plugins_loaded', 'gaps_bootstrap' );

/**
 * Inizializza tutte le classi del plugin.
 */
function gaps_bootstrap() {
	GAPS_Page_Manager::init();
	GAPS_Settings::init();
	GAPS_Preorder::init();
	GAPS_Stripe_Webhook::init();
	GAPS_Assets::init();
	GAPS_Admin_Notices::init();
}
