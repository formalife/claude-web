<?php
/**
 * Template condiviso per le pagine legali pubbliche (v3.2): Condizioni di
 * vendita e Privacy. Caricato via template_include (vedi
 * GAPS_Page_Manager::load_template()), bypassa volutamente header/footer
 * del tema per restare coerente con la landing, mantenendo comunque
 * wp_head()/wp_footer() per compatibilità con plugin di analytics/SEO.
 *
 * Il "motore" (intestazione, indice, richiami, footer) è unico: il
 * contenuto vero e proprio delle due pagine vive in
 * includes/gaps-legal-content.php (gaps_render_terms_content() e
 * gaps_render_privacy_content()), così da poter aggiungere in futuro altre
 * pagine legali riusando lo stesso template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = gaps_get_settings();
$page_id  = get_the_ID();
$doc_type = get_post_meta( $page_id, GAPS_TERMS_PAGE_META, true ) ? 'terms' : 'privacy';

if ( 'terms' === $doc_type ) {
	$doc_title = __( 'Condizioni di vendita', 'guida-antipanico-soffocamento' );
	$doc_toc   = gaps_get_terms_toc();
} else {
	$doc_title = __( 'Privacy Policy', 'guida-antipanico-soffocamento' );
	$doc_toc   = gaps_get_privacy_toc();
}

$landing_url = GAPS_Page_Manager::get_page_url( 'landing' );
$home_url    = $landing_url ? $landing_url : home_url( '/' );
$terms_url   = gaps_get_terms_url();
$privacy_url = gaps_get_privacy_url();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc_html( $doc_title . ' — La Guida Anti-Panico al Soffocamento Pediatrico' ); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'gaps-legal-body' ); ?>>

<div class="gaps-topbar">
	<a class="gaps-legal-topbar-link" href="<?php echo esc_url( $home_url ); ?>">
		&larr; <?php esc_html_e( 'Torna alla pagina del libro', 'guida-antipanico-soffocamento' ); ?>
	</a>
</div>

<div class="gaps-landing gaps-legal-page">

<header class="gaps-legal-header">
	<div class="gaps-wrap">
		<span class="gaps-eyebrow">Formalife</span>
		<h1><?php echo esc_html( $doc_title ); ?></h1>
		<p class="gaps-legal-updated">
			<?php
			printf(
				/* translators: %s: data di ultimo aggiornamento del testo legale */
				esc_html__( 'Ultimo aggiornamento: %s', 'guida-antipanico-soffocamento' ),
				esc_html( $settings['legal_last_updated'] )
			);
			?>
		</p>
	</div>
</header>

<div class="gaps-wrap gaps-legal-body-wrap">

	<?php if ( ! empty( $doc_toc ) ) : ?>
	<nav class="gaps-legal-toc" aria-label="<?php esc_attr_e( 'Indice della pagina', 'guida-antipanico-soffocamento' ); ?>">
		<h2><?php esc_html_e( 'Indice', 'guida-antipanico-soffocamento' ); ?></h2>
		<ol>
			<?php foreach ( $doc_toc as $anchor => $label ) : ?>
				<li><a href="#<?php echo esc_attr( $anchor ); ?>"><?php echo esc_html( $label ); ?></a></li>
			<?php endforeach; ?>
		</ol>
	</nav>
	<?php endif; ?>

	<?php
	if ( 'terms' === $doc_type ) {
		gaps_render_terms_content( $settings );
	} else {
		gaps_render_privacy_content( $settings );
	}
	?>

</div>

<section class="gaps-legal-cta">
	<div class="gaps-wrap">
		<p><?php esc_html_e( 'Hai altre domande prima di completare la prenotazione?', 'guida-antipanico-soffocamento' ); ?></p>
		<a class="gaps-btn-primary" href="<?php echo esc_url( $home_url . '#offerta' ); ?>">
			<?php esc_html_e( 'Torna alla pagina del libro', 'guida-antipanico-soffocamento' ); ?> &rarr;
		</a>
	</div>
</section>

<footer class="gaps-footer">
	<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Formalife. <?php esc_html_e( 'Tutti i diritti riservati.', 'guida-antipanico-soffocamento' ); ?></span>
	<?php if ( $terms_url ) : ?>
		<span class="gaps-footer-sep">·</span>
		<a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Condizioni di vendita', 'guida-antipanico-soffocamento' ); ?></a>
	<?php endif; ?>
	<?php if ( $privacy_url ) : ?>
		<span class="gaps-footer-sep">·</span>
		<a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy', 'guida-antipanico-soffocamento' ); ?></a>
	<?php endif; ?>
</footer>

</div>
<?php wp_footer(); ?>
</body>
</html>
