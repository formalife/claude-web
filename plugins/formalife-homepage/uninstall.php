<?php
/**
 * Pulizia eseguita solo alla disinstallazione esplicita del plugin
 * (Bacheca → Plugin → Elimina).
 *
 * v4.6.5: questa pulizia è distruttiva (cancella impostazioni — comprese
 * le chiavi Stripe — e ogni iscrizione al corso) e prima d'ora partiva in
 * automatico al primo "Elimina", incluso quando l'intento era solo
 * sostituire i file del plugin con una versione più recente (che in
 * WordPress richiede di eliminare il plugin esistente prima di poterne
 * caricare uno nuovo con lo stesso slug). Ora richiede un consenso
 * esplicito salvato in anticipo dall'admin (casella "Cancella i dati alla
 * disinstallazione" nel pannello impostazioni, falsa di default): se non è
 * stata spuntata, questo file esce subito senza cancellare nulla.
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

$fmh_settings = get_option( 'fmh_settings' );
if ( ! is_array( $fmh_settings ) || empty( $fmh_settings['allow_uninstall_wipe'] ) ) {
	return;
}

$pages = array(
	array( 'option' => 'fmh_home_page_id', 'meta' => '_fmh_home_page', 'conflict' => 'fmh_page_conflict_home' ),
	array( 'option' => 'fmh_course_page_id', 'meta' => '_fmh_course_page', 'conflict' => 'fmh_page_conflict_course' ),
	array( 'option' => 'fmh_course_thankyou_page_id', 'meta' => '_fmh_course_thankyou_page', 'conflict' => 'fmh_page_conflict_course_thankyou' ),
);
foreach ( $pages as $page ) {
	$page_id = (int) get_option( $page['option'] );
	if ( $page_id && get_post_meta( $page_id, $page['meta'], true ) ) {
		$adopted = (bool) get_post_meta( $page_id, '_fmh_page_adopted', true );
		if ( $adopted ) {
			delete_post_meta( $page_id, $page['meta'] );
			delete_post_meta( $page_id, '_fmh_page_adopted' );
		} else {
			wp_delete_post( $page_id, true );
		}
	}
	delete_option( $page['option'] );
	delete_option( $page['conflict'] );
}

// Le iscrizioni corso sono dati creati dal plugin: su uninstall esplicito vengono rimosse.
$orders = get_posts( array( 'post_type' => 'fmh_course_order', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) );
foreach ( $orders as $order_id ) { wp_delete_post( $order_id, true ); }

delete_option( 'fmh_settings' );
delete_option( 'fmh_course_settings' );
delete_option( 'fmh_pages_version' );

if ( is_multisite() ) {
	delete_site_option( 'fmh_settings' );
	delete_site_option( 'fmh_course_settings' );
}
