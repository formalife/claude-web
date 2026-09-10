<?php
/**
 * Eseguito solo alla disinstallazione esplicita del plugin (Plugin → Elimina),
 * che in WordPress richiede già una conferma da parte dell'utente.
 * Alla semplice disattivazione, invece, impostazioni e pagine restano intatte
 * (vedi GAPS_Page_Manager::on_deactivation()).
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$gaps_option_key       = 'gaps_settings';
$gaps_page_id_option   = 'gaps_page_id';
$gaps_conflict_option  = 'gaps_page_conflict';
$gaps_page_meta        = '_gaps_landing_page';

// Rimuovi la pagina generata dal plugin, solo se porta ancora il nostro marcatore
// (per non cancellare per errore una pagina che l'admin ha nel frattempo riassegnato).
$page_id = (int) get_option( $gaps_page_id_option );
if ( $page_id ) {
	$marker = get_post_meta( $page_id, $gaps_page_meta, true );
	if ( $marker ) {
		wp_delete_post( $page_id, true );
	}
}

delete_option( $gaps_option_key );
delete_option( $gaps_page_id_option );
delete_option( $gaps_conflict_option );

// Pagina "Grazie — Ordine confermato" (v3.5), stesso meccanismo di
// riconoscimento della landing: rimossa solo se porta ancora il nostro
// marcatore.
$gaps_thankyou_page_id_option = 'gaps_thankyou_page_id';
$gaps_thankyou_conflict_option = 'gaps_page_conflict_thankyou';
$gaps_thankyou_page_meta       = '_gaps_thankyou_page';

$thankyou_page_id = (int) get_option( $gaps_thankyou_page_id_option );
if ( $thankyou_page_id ) {
	$thankyou_marker = get_post_meta( $thankyou_page_id, $gaps_thankyou_page_meta, true );
	if ( $thankyou_marker ) {
		wp_delete_post( $thankyou_page_id, true );
	}
}

delete_option( $gaps_thankyou_page_id_option );
delete_option( $gaps_thankyou_conflict_option );

// Pagina "I tuoi numeri importanti" (v3.5.3), stesso meccanismo di
// riconoscimento delle altre: rimossa solo se porta ancora il nostro
// marcatore.
$gaps_numeri_page_id_option  = 'gaps_numeri_page_id';
$gaps_numeri_conflict_option = 'gaps_page_conflict_numeri';
$gaps_numeri_page_meta       = '_gaps_numeri_page';

$numeri_page_id = (int) get_option( $gaps_numeri_page_id_option );
if ( $numeri_page_id ) {
	$numeri_marker = get_post_meta( $numeri_page_id, $gaps_numeri_page_meta, true );
	if ( $numeri_marker ) {
		wp_delete_post( $numeri_page_id, true );
	}
}

delete_option( $gaps_numeri_page_id_option );
delete_option( $gaps_numeri_conflict_option );

// Ripulisci anche eventuali impostazioni multisite.
if ( is_multisite() ) {
	delete_site_option( $gaps_option_key );
}
