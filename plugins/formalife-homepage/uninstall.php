<?php
/**
 * Pulizia eseguita solo alla disinstallazione esplicita del plugin.
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

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
