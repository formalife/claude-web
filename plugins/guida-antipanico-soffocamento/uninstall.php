<?php
/**
 * Eseguito solo alla disinstallazione esplicita del plugin (Plugin → Elimina),
 * che in WordPress richiede già una conferma da parte dell'utente.
 * Alla semplice disattivazione, invece, impostazioni e pagine restano intatte
 * (vedi GAPS_Page_Manager::on_deactivation()).
 *
 * v3.7.7: questa pulizia è distruttiva (cancella impostazioni — comprese le
 * chiavi Stripe — e le pagine generate) e prima d'ora partiva in automatico
 * al primo "Elimina", incluso quando l'intento era solo sostituire i file
 * del plugin con una versione più recente (che in WordPress richiede di
 * eliminare il plugin esistente prima di poterne caricare uno nuovo con lo
 * stesso slug). Ora richiede un consenso esplicito salvato in anticipo
 * dall'admin (casella "Cancella i dati alla disinstallazione" nel pannello
 * impostazioni, falsa di default): se non è stata spuntata, questo file
 * esce subito senza cancellare nulla.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$gaps_settings = get_option( 'gaps_settings' );
if ( ! is_array( $gaps_settings ) || empty( $gaps_settings['allow_uninstall_wipe'] ) ) {
	return;
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
