<?php
/**
 * Template della pagina pubblica "I tuoi numeri importanti".
 *
 * Raggiunta dal QR code stampato nelle ultime pagine del libro: permette il
 * download gratuito della scheda PDF (un click, nessun modulo da compilare)
 * e chiude, in una sezione separata e non invasiva, con una richiesta di
 * recensione Google — mai rivolta a chi ha appena espresso un dubbio, vedi
 * il rimando ai contatti nella nota in piccolo sotto il pulsante.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = gaps_get_settings();

$pdf_url    = gaps_get_numeri_pdf_url();
$review_url = gaps_get_review_url();

$contact_email     = $settings['contact_email'];
$contact_whatsapp  = gaps_build_whatsapp_url( $settings['contact_whatsapp'] );
$contact_instagram = gaps_build_instagram_url( $settings['contact_instagram'] );
$has_contacts      = $contact_email || $contact_whatsapp || $contact_instagram || $settings['formalife_logo_id'];

$terms_url   = gaps_get_terms_url();
$privacy_url = gaps_get_privacy_url();
$landing_url = GAPS_Page_Manager::get_page_url( 'landing' );
$home_url    = $landing_url ? $landing_url : home_url( '/' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, follow">
<title><?php esc_html_e( 'I tuoi numeri importanti — Formalife', 'guida-antipanico-soffocamento' ); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'gaps-numeri-body' ); ?>>

<div class="gaps-landing gaps-numeri-page">

<!-- ===== 1. HERO — DOWNLOAD ===== -->
<section class="gaps-num-hero">
	<div class="gaps-wrap gaps-num-hero-grid">
		<div class="gaps-num-hero-copy">
			<span class="gaps-eyebrow"><?php esc_html_e( 'Scheda in omaggio per chi ha il libro', 'guida-antipanico-soffocamento' ); ?></span>
			<h1><?php esc_html_e( 'I tuoi numeri importanti', 'guida-antipanico-soffocamento' ); ?></h1>
			<p class="gaps-num-hero-sub"><?php esc_html_e( 'Se sei arrivato qui dal QR code nelle ultime pagine del libro, questa è la scheda che ti avevamo promesso: una pagina sola, da compilare in due minuti e tenere in un punto della casa facile da raggiungere.', 'guida-antipanico-soffocamento' ); ?></p>
			<a class="gaps-btn-primary gaps-btn-lg" href="<?php echo esc_url( $pdf_url ); ?>" download="<?php esc_attr_e( 'I tuoi numeri importanti - Formalife.pdf', 'guida-antipanico-soffocamento' ); ?>" target="_blank" rel="noopener noreferrer">
				<span aria-hidden="true">⬇</span> <?php esc_html_e( 'Scarica gratis il PDF', 'guida-antipanico-soffocamento' ); ?>
			</a>
			<p class="gaps-micro"><?php esc_html_e( 'Un file PDF, una pagina, pronta da stampare. Nessuna registrazione richiesta.', 'guida-antipanico-soffocamento' ); ?></p>
		</div>
		<div class="gaps-num-hero-visual">
			<?php gaps_render_numeri_preview_image(); ?>
		</div>
	</div>
</section>

<!-- ===== 2. COSA TROVI NELLA SCHEDA ===== -->
<section class="gaps-num-content-section">
	<div class="gaps-wrap">
		<div class="gaps-num-content-card">
			<h2><?php esc_html_e( 'Cosa trovi nella scheda', 'guida-antipanico-soffocamento' ); ?></h2>
			<p><?php esc_html_e( 'Uno spazio unico per i contatti e le informazioni che, in un\'emergenza, altrimenti passeresti a cercare — tempo che conta.', 'guida-antipanico-soffocamento' ); ?></p>
			<ul class="gaps-num-list">
				<li><?php esc_html_e( 'Il numero unico di emergenza 112, con quando chiamarlo', 'guida-antipanico-soffocamento' ); ?></li>
				<li><?php esc_html_e( 'Pediatra, guardia medica, farmacia e centro antiveleni', 'guida-antipanico-soffocamento' ); ?></li>
				<li><?php esc_html_e( 'Due persone da contattare in emergenza', 'guida-antipanico-soffocamento' ); ?></li>
				<li><?php echo wp_kses( __( 'Allergie, patologie e farmaci in corso di tuo figlio/a', 'guida-antipanico-soffocamento' ), array() ); ?></li>
			</ul>
			<p class="gaps-num-share-note"><?php esc_html_e( 'Stampane una copia anche per i nonni, la babysitter o chiunque passi del tempo con tuo figlio: in un\'emergenza, chi lo accudisce deve poterli trovare subito quanto te.', 'guida-antipanico-soffocamento' ); ?></p>
		</div>
	</div>
</section>

<!-- ===== 3. PERCHÉ ===== -->
<section class="gaps-num-why-section">
	<div class="gaps-wrap gaps-num-why-grid">
		<div class="gaps-num-why-item">
			<strong><?php esc_html_e( 'Perché su carta', 'guida-antipanico-soffocamento' ); ?></strong>
			<p><?php esc_html_e( 'In un momento di panico, un foglio attaccato al frigo si trova più in fretta di un file sul telefono.', 'guida-antipanico-soffocamento' ); ?></p>
		</div>
		<div class="gaps-num-why-item">
			<strong><?php esc_html_e( 'Perché compilarla subito', 'guida-antipanico-soffocamento' ); ?></strong>
			<p><?php esc_html_e( 'Non aspettare l\'emergenza per accorgerti che un numero non ce l\'hai segnato da nessuna parte.', 'guida-antipanico-soffocamento' ); ?></p>
		</div>
		<div class="gaps-num-why-item">
			<strong><?php esc_html_e( 'Perché condividerla', 'guida-antipanico-soffocamento' ); ?></strong>
			<p><?php esc_html_e( 'Chi si prende cura di tuo figlio anche solo per un pomeriggio dovrebbe avere questi contatti a portata di mano.', 'guida-antipanico-soffocamento' ); ?></p>
		</div>
	</div>
</section>

<?php if ( $review_url ) : ?>
<!-- ===== 4. RICHIESTA RECENSIONE (separata dal download, mai invasiva) ===== -->
<section class="gaps-num-review-section">
	<div class="gaps-wrap gaps-num-review-wrap">
		<span class="gaps-eyebrow"><?php esc_html_e( 'Solo se ti va', 'guida-antipanico-soffocamento' ); ?></span>
		<h2><?php esc_html_e( 'Il libro ti sta dando la chiarezza che cercavi?', 'guida-antipanico-soffocamento' ); ?></h2>
		<p><?php esc_html_e( 'Se è così, una tua recensione aiuta un altro genitore — che oggi si sente incerto quanto ti sentivi tu prima di leggerlo — a trovare questo libro. Bastano trenta secondi.', 'guida-antipanico-soffocamento' ); ?></p>
		<a class="gaps-btn-primary gaps-btn-onlight" href="<?php echo esc_url( $review_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Lascia una recensione', 'guida-antipanico-soffocamento' ); ?></a>
		<?php if ( $contact_email ) : ?>
			<p class="gaps-num-fine-print"><?php printf( wp_kses( __( 'Se qualcosa non ti ha convinto, scrivici prima a&nbsp;<a href="mailto:%1$s">%1$s</a>: preferiamo risolvere che ricevere una recensione a metà.', 'guida-antipanico-soffocamento' ), array( 'a' => array( 'href' => array() ) ) ), esc_attr( $contact_email ) ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>

<?php if ( $has_contacts ) : ?>
<!-- ===== CONTATTI ===== -->
<section class="gaps-contact-section gaps-num-contact-section">
	<div class="gaps-wrap">
		<span class="gaps-eyebrow"><?php esc_html_e( 'Ci siamo davvero', 'guida-antipanico-soffocamento' ); ?></span>
		<h2><?php esc_html_e( 'Domande sulla scheda o sull\'ordine? Scrivici qui.', 'guida-antipanico-soffocamento' ); ?></h2>
		<div class="gaps-contact-row">
			<?php if ( $contact_email ) : ?>
				<a class="gaps-contact-pill" href="mailto:<?php echo esc_attr( $contact_email ); ?>">
					<span class="gaps-contact-icon" aria-hidden="true">
						<svg viewBox="0 0 48 48"><rect width="48" height="48" rx="10" fill="#fff"/><path fill="#4285F4" d="M6 14v20a4 4 0 0 0 4 4h4V19.4L24 28l10-8.6V38h4a4 4 0 0 0 4-4V14a4 4 0 0 0-1.6-3.2L24 22 7.6 10.8A4 4 0 0 0 6 14Z"/><path fill="#EA4335" d="M6 14a4 4 0 0 1 1.6-3.2L24 22 6.4 11a4 4 0 0 0-.4.6Z"/><path fill="#34A853" d="M14 38v-14l-8-6.2V34a4 4 0 0 0 4 4Z"/><path fill="#FBBC05" d="M34 38v-14l8-6.2V34a4 4 0 0 1-4 4Z"/></svg>
					</span>
					<?php echo esc_html( $contact_email ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $contact_whatsapp ) : ?>
				<a class="gaps-contact-pill" href="<?php echo esc_url( $contact_whatsapp ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="gaps-contact-icon" aria-hidden="true">
						<svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="24" fill="#25D366"/><path fill="#fff" d="M24 11c-7.2 0-13 5.8-13 13 0 2.4.6 4.6 1.8 6.6L11 37l6.6-1.7c1.9 1 4.1 1.6 6.4 1.6 7.2 0 13-5.8 13-13s-5.8-13-13-13Zm0 2.2c6 0 10.8 4.8 10.8 10.8S30 34.8 24 34.8c-2 0-3.9-.5-5.6-1.5l-.4-.2-3.9 1 1-3.8-.3-.4A10.7 10.7 0 0 1 13.2 24c0-6 4.8-10.8 10.8-10.8Z"/><path fill="#fff" d="M19.8 18.4c-.2-.5-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.1-1.2 2.8s1.2 3.3 1.4 3.5c.2.3 2.3 3.6 5.7 4.9 2.8 1.1 3.4.9 4 .8.6-.1 1.9-.8 2.2-1.5.3-.7.3-1.3.2-1.5-.1-.2-.4-.3-.7-.5-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.3-.7.1-.3-.2-1.3-.5-2.5-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.7.1-.1.3-.4.5-.5.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5-.1-.2-.7-1.8-1-2.5Z"/></svg>
					</span>
					<?php esc_html_e( 'WhatsApp', 'guida-antipanico-soffocamento' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $contact_instagram ) : ?>
				<a class="gaps-contact-pill" href="<?php echo esc_url( $contact_instagram ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="gaps-contact-icon" aria-hidden="true">
						<svg viewBox="0 0 48 48"><defs><linearGradient id="gapsIgGradNumeri" x1="0" y1="48" x2="48" y2="0"><stop offset="0" stop-color="#FFDC80"/><stop offset="0.25" stop-color="#FCAF45"/><stop offset="0.5" stop-color="#E1306C"/><stop offset="0.75" stop-color="#C13584"/><stop offset="1" stop-color="#833AB4"/></linearGradient></defs><rect width="48" height="48" rx="12" fill="url(#gapsIgGradNumeri)"/><rect x="12" y="12" width="24" height="24" rx="7" fill="none" stroke="#fff" stroke-width="2.4"/><circle cx="24" cy="24" r="6.2" fill="none" stroke="#fff" stroke-width="2.4"/><circle cx="32.2" cy="15.8" r="1.6" fill="#fff"/></svg>
					</span>
					<?php esc_html_e( 'Instagram', 'guida-antipanico-soffocamento' ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php if ( $settings['formalife_logo_id'] ) : ?>
			<div class="gaps-contact-logo"><?php gaps_render_image( $settings['formalife_logo_id'], __( 'Formalife', 'guida-antipanico-soffocamento' ) ); ?></div>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>

<footer class="gaps-footer">
	<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Formalife. <?php esc_html_e( 'Tutti i diritti riservati.', 'guida-antipanico-soffocamento' ); ?></span>
	<?php if ( $terms_url ) : ?><span class="gaps-footer-sep">·</span><a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Condizioni di vendita', 'guida-antipanico-soffocamento' ); ?></a><?php endif; ?>
	<?php if ( $privacy_url ) : ?><span class="gaps-footer-sep">·</span><a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy', 'guida-antipanico-soffocamento' ); ?></a><?php endif; ?>
	<span class="gaps-footer-sep">·</span><a href="<?php echo esc_url( $home_url ); ?>"><?php esc_html_e( 'Torna alla pagina del libro', 'guida-antipanico-soffocamento' ); ?></a>
</footer>

</div>
<?php wp_footer(); ?>
</body>
</html>
