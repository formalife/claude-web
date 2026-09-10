<?php
/**
 * Template della pagina pubblica "Grazie — Ordine confermato".
 *
 * La pagina non riceve dati personali del singolo ordine: conferma il flusso,
 * valorizza la scelta, offre passi utili e rimanda ai canali di assistenza
 * senza fingere di conoscere nome, indirizzo o contenuto dell'email ricevuta.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = gaps_get_settings();

$price         = $settings['price'];
$shipping_price = $settings['shipping_price'];
$date_delivery = $settings['date_delivery'];

$contact_email     = $settings['contact_email'];
$contact_whatsapp  = gaps_build_whatsapp_url( $settings['contact_whatsapp'] );
$contact_instagram = gaps_build_instagram_url( $settings['contact_instagram'] );
$has_contacts      = $contact_email || $contact_whatsapp || $contact_instagram || $settings['formalife_logo_id'];

$terms_url   = gaps_get_terms_url();
$privacy_url = gaps_get_privacy_url();
$landing_url = GAPS_Page_Manager::get_page_url( 'landing' );
$home_url    = $landing_url ? $landing_url : home_url( '/' );
$course_url  = gaps_get_course_url();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?php esc_html_e( 'Ordine confermato — Grazie! — La Guida Anti-Panico al Soffocamento Pediatrico', 'guida-antipanico-soffocamento' ); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'gaps-thankyou-body' ); ?>>

<div class="gaps-topbar gaps-ty-topbar">
	<span aria-hidden="true">✓</span>
	<?php esc_html_e( 'PAGAMENTO COMPLETATO · ORDINE AL SICURO', 'guida-antipanico-soffocamento' ); ?>
</div>

<div class="gaps-landing gaps-thankyou-page">

<!-- ===== 1. CONFERMA E VALORE ===== -->
<section class="gaps-ty-hero">
	<div class="gaps-wrap gaps-ty-hero-grid">
		<div class="gaps-ty-hero-copy">
			<span class="gaps-eyebrow"><?php esc_html_e( 'Grazie per la fiducia', 'guida-antipanico-soffocamento' ); ?></span>
			<h1><?php esc_html_e( 'Hai scelto di prepararti prima che serva.', 'guida-antipanico-soffocamento' ); ?></h1>
			<p class="gaps-ty-hero-sub"><?php esc_html_e( 'Il tuo ordine è confermato. La Guida Anti-Panico nasce per trasformare informazioni sparse in un percorso chiaro, consultabile e condivisibile con le persone che si prendono cura dei bambini insieme a te.', 'guida-antipanico-soffocamento' ); ?></p>
			<div class="gaps-ty-trust-row" aria-label="Riepilogo conferma">
				<span><strong>✓</strong> <?php esc_html_e( 'Pagamento confermato', 'guida-antipanico-soffocamento' ); ?></span>
				<span><strong>🚚</strong> <?php printf( esc_html__( 'Spedizione %s', 'guida-antipanico-soffocamento' ), esc_html( $date_delivery ) ); ?></span>
				<span><strong>📦</strong> <?php esc_html_e( 'Confezione con omaggio incluso', 'guida-antipanico-soffocamento' ); ?></span>
			</div>
			<nav class="gaps-ty-jump-nav" aria-label="Vai a una sezione della pagina">
				<a href="#gaps-ty-order"><?php esc_html_e( 'Il mio ordine', 'guida-antipanico-soffocamento' ); ?></a>
				<a href="#gaps-ty-checklist"><?php esc_html_e( 'Cosa fare ora', 'guida-antipanico-soffocamento' ); ?></a>
				<a href="#gaps-ty-help"><?php esc_html_e( 'Serve aiuto?', 'guida-antipanico-soffocamento' ); ?></a>
			</nav>
		</div>
		<div class="gaps-ty-confirm-card" aria-label="Conferma ordine">
			<span class="gaps-ty-check" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
			</span>
			<strong><?php esc_html_e( 'È tutto a posto.', 'guida-antipanico-soffocamento' ); ?></strong>
			<p><?php esc_html_e( 'Non devi ripetere l’ordine né inviarci altri dati. Conserva l’email di conferma e usa questa pagina come guida per i prossimi passi.', 'guida-antipanico-soffocamento' ); ?></p>
		</div>
	</div>
</section>

<!-- ===== 2. RIEPILOGO ORDINE ===== -->
<section class="gaps-ty-summary-section" id="gaps-ty-order">
	<div class="gaps-wrap">
		<div class="gaps-ty-summary-card gaps-ty-reveal">
			<div class="gaps-ty-summary-visual">
				<?php
				gaps_render_image(
					$settings['cover_image_id'],
					__( 'Copertina del libro La Guida Anti-Panico al Soffocamento Pediatrico', 'guida-antipanico-soffocamento' ),
					'',
					__( 'Copertina del libro', 'guida-antipanico-soffocamento' )
				);
				?>
			</div>
			<div class="gaps-ty-summary-body">
				<span class="gaps-eyebrow"><?php esc_html_e( 'Dentro il tuo ordine', 'guida-antipanico-soffocamento' ); ?></span>
				<h2><?php esc_html_e( 'La Guida Anti-Panico al Soffocamento Pediatrico', 'guida-antipanico-soffocamento' ); ?></h2>
				<p class="gaps-ty-summary-price"><?php echo esc_html( $price ); ?></p>
				<p class="gaps-micro">
					<?php
					printf(
						/* translators: %s: costo di spedizione */
						esc_html__( '+ %s di spedizione, già inclusi nel totale pagato.', 'guida-antipanico-soffocamento' ),
						esc_html( $shipping_price )
					);
					?>
				</p>
				<ul class="gaps-ty-summary-list">
					<li><span aria-hidden="true">📘</span><span><?php esc_html_e( 'Un percorso ordinato per capire, ricordare e ritrovare rapidamente le informazioni.', 'guida-antipanico-soffocamento' ); ?></span></li>
					<li><span aria-hidden="true">🗂️</span><span><?php echo wp_kses( __( 'La scheda <strong>“I tuoi numeri importanti”</strong> in omaggio.', 'guida-antipanico-soffocamento' ), array( 'strong' => array() ) ); ?></span></li>
					<li><span aria-hidden="true">🛡️</span><span><?php echo wp_kses( __( 'La protezione della <strong>garanzia soddisfatti o rimborsati</strong>.', 'guida-antipanico-soffocamento' ), array( 'strong' => array() ) ); ?></span></li>
				</ul>
				<div class="gaps-ty-delivery-note">
					<span aria-hidden="true">🚚</span>
					<div><strong><?php esc_html_e( 'Prossimo aggiornamento: la spedizione', 'guida-antipanico-soffocamento' ); ?></strong><small><?php printf( esc_html__( 'Prevista %s. Se ci fossero variazioni, riceverai una comunicazione via email.', 'guida-antipanico-soffocamento' ), esc_html( $date_delivery ) ); ?></small></div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ===== 3. CHECKLIST INTERATTIVA ===== -->
<section class="gaps-ty-action-section" id="gaps-ty-checklist">
	<div class="gaps-wrap gaps-ty-action-grid">
		<div class="gaps-ty-action-intro gaps-ty-reveal">
			<span class="gaps-eyebrow"><?php esc_html_e( 'Prima di chiudere questa pagina', 'guida-antipanico-soffocamento' ); ?></span>
			<h2><?php esc_html_e( 'Tre piccole azioni rendono l’acquisto più utile fin da ora.', 'guida-antipanico-soffocamento' ); ?></h2>
			<p><?php esc_html_e( 'Spuntale quando vuoi: il progresso resta salvato soltanto su questo dispositivo e non viene inviato a Formalife.', 'guida-antipanico-soffocamento' ); ?></p>
			<div class="gaps-ty-progress" aria-hidden="true"><span data-gaps-ty-progress-bar></span></div>
			<p class="gaps-ty-progress-label" data-gaps-ty-progress-label aria-live="polite"><?php esc_html_e( '0 di 3 completate', 'guida-antipanico-soffocamento' ); ?></p>
		</div>
		<div class="gaps-ty-checklist gaps-ty-reveal" data-gaps-ty-checklist>
			<label class="gaps-ty-check-item">
				<input type="checkbox" value="email" />
				<span class="gaps-ty-check-control" aria-hidden="true"></span>
				<span><strong><?php esc_html_e( 'Controlla l’email usata per l’ordine', 'guida-antipanico-soffocamento' ); ?></strong><small><?php esc_html_e( 'Conservala: è il riferimento più rapido per eventuali comunicazioni sulla spedizione.', 'guida-antipanico-soffocamento' ); ?></small></span>
			</label>
			<label class="gaps-ty-check-item">
				<input type="checkbox" value="contacts" />
				<span class="gaps-ty-check-control" aria-hidden="true"></span>
				<span><strong><?php esc_html_e( 'Salva un contatto Formalife', 'guida-antipanico-soffocamento' ); ?></strong><small><?php esc_html_e( 'Così saprai subito dove scrivere per indirizzo, fattura, consegna o garanzia.', 'guida-antipanico-soffocamento' ); ?></small></span>
			</label>
			<label class="gaps-ty-check-item">
				<input type="checkbox" value="caregiver" />
				<span class="gaps-ty-check-control" aria-hidden="true"></span>
				<span><strong><?php esc_html_e( 'Scegli con chi condividerai la guida', 'guida-antipanico-soffocamento' ); ?></strong><small><?php esc_html_e( 'Partner, nonni, babysitter: la preparazione funziona meglio quando non resta sulle spalle di una sola persona.', 'guida-antipanico-soffocamento' ); ?></small></span>
			</label>
		</div>
	</div>
</section>

<!-- ===== 4. VALORE NELL'ATTESA ===== -->
<section class="gaps-ty-value-section">
	<div class="gaps-wrap">
		<div class="gaps-ty-section-heading gaps-ty-reveal">
			<span class="gaps-eyebrow"><?php esc_html_e( 'Mentre aspetti il libro', 'guida-antipanico-soffocamento' ); ?></span>
			<h2><?php esc_html_e( 'Inizia a costruire un sistema semplice, non altra ansia.', 'guida-antipanico-soffocamento' ); ?></h2>
			<p><?php esc_html_e( 'Non serve studiare tutto oggi. Puoi già preparare il contesto in cui userai la guida.', 'guida-antipanico-soffocamento' ); ?></p>
		</div>
		<div class="gaps-ty-value-grid">
			<article class="gaps-ty-value-card gaps-ty-reveal">
				<span aria-hidden="true">01</span>
				<h3><?php esc_html_e( 'Un posto riconoscibile', 'guida-antipanico-soffocamento' ); ?></h3>
				<p><?php esc_html_e( 'Decidi dove terrai la guida e la scheda dei numeri importanti, in un punto noto agli adulti di casa.', 'guida-antipanico-soffocamento' ); ?></p>
			</article>
			<article class="gaps-ty-value-card gaps-ty-reveal">
				<span aria-hidden="true">02</span>
				<h3><?php esc_html_e( 'Una lista di domande vere', 'guida-antipanico-soffocamento' ); ?></h3>
				<p><?php esc_html_e( 'Annota i dubbi che emergono nella quotidianità: ti aiuteranno a leggere con uno scopo e a capire cosa vorrai esercitare dal vivo.', 'guida-antipanico-soffocamento' ); ?></p>
			</article>
			<article class="gaps-ty-value-card gaps-ty-reveal">
				<span aria-hidden="true">03</span>
				<h3><?php esc_html_e( 'Una responsabilità condivisa', 'guida-antipanico-soffocamento' ); ?></h3>
				<p><?php esc_html_e( 'Coinvolgi gli altri caregiver: sapere dove trovare le informazioni è già un primo passo verso una risposta più coordinata.', 'guida-antipanico-soffocamento' ); ?></p>
			</article>
		</div>
		<p class="gaps-ty-safety-note gaps-ty-reveal"><strong><?php esc_html_e( 'Una precisazione importante:', 'guida-antipanico-soffocamento' ); ?></strong> <?php esc_html_e( 'il libro aiuta a comprendere e ricordare, ma non sostituisce l’addestramento pratico né il parere dei professionisti sanitari.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>
</section>

<!-- ===== 5. COSA SUCCEDE ADESSO ===== -->
<section class="gaps-ty-next-section">
	<div class="gaps-wrap">
		<div class="gaps-ty-section-heading gaps-ty-reveal">
			<span class="gaps-eyebrow"><?php esc_html_e( 'Da qui alla consegna', 'guida-antipanico-soffocamento' ); ?></span>
			<h2><?php esc_html_e( 'Sai sempre qual è il prossimo passo.', 'guida-antipanico-soffocamento' ); ?></h2>
		</div>
		<div class="gaps-ty-timeline">
			<article class="gaps-ty-timeline-item gaps-ty-reveal"><span>1</span><div><strong><?php esc_html_e( 'Ordine registrato', 'guida-antipanico-soffocamento' ); ?></strong><p><?php esc_html_e( 'Il pagamento è completato: non devi effettuare un secondo ordine.', 'guida-antipanico-soffocamento' ); ?></p></div></article>
			<article class="gaps-ty-timeline-item gaps-ty-reveal"><span>2</span><div><strong><?php esc_html_e( 'Preparazione e spedizione', 'guida-antipanico-soffocamento' ); ?></strong><p><?php printf( esc_html__( 'La partenza è prevista %s, insieme alla scheda in omaggio.', 'guida-antipanico-soffocamento' ), esc_html( $date_delivery ) ); ?></p></div></article>
			<article class="gaps-ty-timeline-item gaps-ty-reveal"><span>3</span><div><strong><?php esc_html_e( 'Consegna e assistenza', 'guida-antipanico-soffocamento' ); ?></strong><p><?php esc_html_e( 'Per dubbi sull’ordine o per attivare la garanzia, i contatti Formalife restano a tua disposizione.', 'guida-antipanico-soffocamento' ); ?></p></div></article>
		</div>
	</div>
</section>

<!-- ===== 6. RISPOSTE RAPIDE ===== -->
<section class="gaps-ty-faq-section" id="gaps-ty-help">
	<div class="gaps-wrap gaps-ty-faq-layout">
		<div class="gaps-ty-faq-intro gaps-ty-reveal">
			<span class="gaps-eyebrow"><?php esc_html_e( 'Risposte rapide', 'guida-antipanico-soffocamento' ); ?></span>
			<h2><?php esc_html_e( 'Le domande che arrivano subito dopo l’acquisto.', 'guida-antipanico-soffocamento' ); ?></h2>
			<p><?php esc_html_e( 'Apri una domanda per leggere la risposta. Se il tuo caso è diverso, scrivici direttamente.', 'guida-antipanico-soffocamento' ); ?></p>
		</div>
		<div class="gaps-ty-faq-list gaps-ty-reveal">
			<details>
				<summary><?php esc_html_e( 'Quando verrà spedito il libro?', 'guida-antipanico-soffocamento' ); ?><span aria-hidden="true">+</span></summary>
				<p><?php printf( esc_html__( 'La spedizione è prevista %s. Se il calendario dovesse cambiare, useremo l’email dell’ordine per aggiornarti.', 'guida-antipanico-soffocamento' ), esc_html( $date_delivery ) ); ?></p>
			</details>
			<details>
				<summary><?php esc_html_e( 'Ho inserito un indirizzo errato: cosa faccio?', 'guida-antipanico-soffocamento' ); ?><span aria-hidden="true">+</span></summary>
				<p><?php esc_html_e( 'Contattaci appena possibile, prima della preparazione della spedizione, indicando nome, cognome e indirizzo corretto.', 'guida-antipanico-soffocamento' ); ?></p>
			</details>
			<details>
				<summary><?php esc_html_e( 'Ho richiesto la fattura: devo fare altro?', 'guida-antipanico-soffocamento' ); ?><span aria-hidden="true">+</span></summary>
				<p><?php esc_html_e( 'No. Useremo i dati di fatturazione inseriti nel modulo. Se noti un errore, scrivici prima dell’emissione.', 'guida-antipanico-soffocamento' ); ?></p>
			</details>
			<details>
				<summary><?php esc_html_e( 'Non trovo l’email di conferma.', 'guida-antipanico-soffocamento' ); ?><span aria-hidden="true">+</span></summary>
				<p><?php esc_html_e( 'Controlla spam e promozioni. Se non compare, contattaci indicando il nome usato nell’ordine: verificheremo insieme.', 'guida-antipanico-soffocamento' ); ?></p>
			</details>
			<details>
				<summary><?php esc_html_e( 'E se il libro non fosse adatto a me?', 'guida-antipanico-soffocamento' ); ?><span aria-hidden="true">+</span></summary>
				<p><?php esc_html_e( 'Resta valida la garanzia soddisfatti o rimborsati indicata nell’offerta e nelle condizioni di vendita. Scrivici con il riferimento dell’ordine e ti guideremo senza passaggi inutili.', 'guida-antipanico-soffocamento' ); ?></p>
			</details>
		</div>
	</div>
</section>

<?php if ( $course_url ) : ?>
<!-- ===== 7. PASSO PRATICO FACOLTATIVO ===== -->
<section class="gaps-ty-course-section">
	<div class="gaps-wrap">
		<div class="gaps-ty-course-wrap gaps-ty-reveal">
			<div class="gaps-ty-course-icon" aria-hidden="true">✋</div>
			<div>
				<span class="gaps-eyebrow"><?php esc_html_e( 'Il passo successivo, quando vorrai', 'guida-antipanico-soffocamento' ); ?></span>
				<h2><?php esc_html_e( 'Il libro costruisce comprensione. La pratica costruisce gesti.', 'guida-antipanico-soffocamento' ); ?></h2>
				<p><?php esc_html_e( '“Genitori Pronti” è il percorso pratico Formalife in piccoli gruppi: puoi scoprirlo ora e decidere con calma se e quando sarà il momento giusto.', 'guida-antipanico-soffocamento' ); ?></p>
				<a class="gaps-btn-primary" href="<?php echo esc_url( $course_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Scopri il corso pratico', 'guida-antipanico-soffocamento' ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</a>
				<p class="gaps-micro gaps-ty-course-micro"><?php esc_html_e( 'Nessun obbligo e nessun conto alla rovescia: è un’informazione utile da conservare.', 'guida-antipanico-soffocamento' ); ?></p>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( $has_contacts ) : ?>
<!-- ===== CONTATTI ===== -->
<section class="gaps-contact-section gaps-ty-contact-section">
	<div class="gaps-wrap">
		<span class="gaps-eyebrow"><?php esc_html_e( 'Ci siamo davvero', 'guida-antipanico-soffocamento' ); ?></span>
		<h2><?php esc_html_e( 'Per indirizzo, fattura, consegna o garanzia, scrivici qui.', 'guida-antipanico-soffocamento' ); ?></h2>
		<div class="gaps-contact-row">
			<?php if ( $contact_email ) : ?>
				<a class="gaps-contact-pill" href="mailto:<?php echo esc_attr( $contact_email ); ?>"><span aria-hidden="true">✉️</span><?php echo esc_html( $contact_email ); ?></a>
			<?php endif; ?>
			<?php if ( $contact_whatsapp ) : ?>
				<a class="gaps-contact-pill" href="<?php echo esc_url( $contact_whatsapp ); ?>" target="_blank" rel="noopener noreferrer"><span aria-hidden="true">💬</span><?php esc_html_e( 'WhatsApp', 'guida-antipanico-soffocamento' ); ?></a>
			<?php endif; ?>
			<?php if ( $contact_instagram ) : ?>
				<a class="gaps-contact-pill" href="<?php echo esc_url( $contact_instagram ); ?>" target="_blank" rel="noopener noreferrer"><span aria-hidden="true">◎</span><?php esc_html_e( 'Instagram', 'guida-antipanico-soffocamento' ); ?></a>
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
