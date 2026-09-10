<?php
/**
 * Template della pagina pubblica "Home" (v2.1.0).
 * Caricato via template_include (vedi FMH_Page_Manager), bypassa
 * volutamente header/footer del tema per restare coerente con lo stile
 * dell'ecosistema Formalife, mantenendo comunque wp_head()/wp_footer() per
 * compatibilità con plugin di analytics/SEO.
 *
 * Nota su "&nbsp;": come nel plugin del libro, ovunque un testo in
 * grassetto/corsivo termini e riprenda un testo normale nella stessa frase,
 * si usa uno spazio non discendente (&nbsp;) invece di uno spazio letterale,
 * per evitare che l'accento visivo si spezzi su due righe diverse.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = fmh_get_settings();

$book_url         = $settings['book_url'];
$book_preview_url = fmh_get_book_preview_url();

$contact_email     = $settings['contact_email'];
$contact_whatsapp  = fmh_build_whatsapp_url( $settings['contact_whatsapp'] );
$contact_instagram = fmh_build_instagram_url( $settings['contact_instagram'] );

$course_whatsapp_url = fmh_get_course_whatsapp_url();
$course_settings     = fmh_course_get_settings();
$course_page_url     = fmh_get_course_page_url();
$course_sessions     = fmh_course_get_sessions();
$course_checkout_ready = fmh_course_checkout_ready();

$terms_url   = $settings['terms_url'];
$privacy_url = $settings['privacy_url'];

$hero_bg_url = fmh_get_image_url( $settings['hero_bg_image_id'], 'full' );

$carousel_urls = fmh_get_carousel_image_urls();

$nav_urls = fmh_get_nav_urls();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc_html( $settings['meta_title'] ); ?></title>
<meta name="description" content="<?php echo esc_attr( $settings['meta_description'] ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'fmh-home-body' ); ?>>

<nav class="fmh-nav" id="fmh-nav">
	<div class="fmh-wrap fmh-nav-inner">
		<a class="fmh-nav-logo" href="<?php echo esc_url( $nav_urls['home'] ); ?>">
			<?php if ( $settings['formalife_logo_id'] ) : ?>
				<?php fmh_render_image( $settings['formalife_logo_id'], __( 'Formalife', 'formalife-homepage' ), '', '', 'medium', false ); ?>
			<?php else : ?>
				<span class="fmh-nav-logo-text">Formalife</span>
			<?php endif; ?>
		</a>
		<ul class="fmh-nav-menu">
			<li><a href="<?php echo esc_url( $nav_urls['home'] ); ?>"><?php esc_html_e( 'Home', 'formalife-homepage' ); ?></a></li>
			<li><a href="<?php echo esc_url( $nav_urls['book'] ); ?>"><?php esc_html_e( 'Il libro', 'formalife-homepage' ); ?></a></li>
			<li><a href="<?php echo esc_url( $nav_urls['course'] ); ?>"><?php esc_html_e( 'I corsi', 'formalife-homepage' ); ?></a></li>
		</ul>
	</div>
</nav>

<div class="fmh-home">

<!-- =====================================================================
     SEZIONE 1 — HERO
     Layout a due colonne + immagine di sfondo opzionale (trasparenza,
     sfocatura e colore di sovrapposizione regolabili dal pannello). Il
     colore di sovrapposizione è il livello di base (opaco), l'immagine
     sta sopra con la propria opacità regolabile: così l'immagine resta
     sempre visibile, invece di sparire sotto un overlay troppo coprente.
     ===================================================================== -->
<section class="fmh-hero">
	<div class="fmh-hero-overlay" style="background-color:<?php echo esc_attr( $settings['hero_overlay_color'] ); ?>;" aria-hidden="true"></div>
	<?php if ( $hero_bg_url ) : ?>
		<div class="fmh-hero-bg" style="background-image:url('<?php echo esc_url( $hero_bg_url ); ?>'); opacity:<?php echo esc_attr( round( absint( $settings['hero_bg_opacity'] ) / 100, 2 ) ); ?>; filter:blur(<?php echo esc_attr( absint( $settings['hero_bg_blur'] ) ); ?>px);" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="fmh-wrap fmh-hero-grid">
		<div class="fmh-hero-copy">
			<span class="fmh-eyebrow"><?php esc_html_e( 'Preparazione familiare alle emergenze pediatriche', 'formalife-homepage' ); ?></span>
			<h1>
				<span class="fmh-hero-title-blue"><?php esc_html_e( "Durante un'emergenza pediatrica", 'formalife-homepage' ); ?></span>&nbsp;<span class="fmh-hero-title-red"><?php esc_html_e( 'non permettere al panico di bloccarti.', 'formalife-homepage' ); ?></span>
			</h1>
			<p class="fmh-hero-sub">
				<?php
				echo wp_kses(
					__( '<strong>Formalife</strong>&nbsp;aiuta genitori e caregiver a prevenire, riconoscere e affrontare con lucidità le emergenze pediatriche — a partire dal soffocamento, una delle più temute e in cui le persone sono meno preparate.', 'formalife-homepage' ),
					array( 'strong' => array() )
				);
				?>
			</p>
			<p class="fmh-hero-body"><?php esc_html_e( 'Non proponiamo un video che si guarda una volta. Nemmeno un incontro che si dimentica dopo un mese. Abbiamo costruito un percorso fatto di indicazioni affidabili, pratica guidata e richiami che mantengono viva la preparazione nel tempo.', 'formalife-homepage' ); ?></p>
			<span class="fmh-hero-badge">✓ <?php esc_html_e( 'Verificato sulle linee guida ERC 2025 e IRC — seguito dalla Dott.ssa Mafalda Camposarcone, pediatra di famiglia da quasi trent\'anni', 'formalife-homepage' ); ?></span>

			<div class="fmh-hero-cta-block">
				<?php
				fmh_render_cta_button(
					esc_html__( 'Scopri il nuovo libro sul Soffocamento', 'formalife-homepage' ) . ' <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
					$book_url,
					'fmh-btn-lg'
				);
				?>
				<span class="fmh-hero-micro">
					<?php
					printf(
						/* translators: %s: prezzo del libro */
						esc_html__( 'A %s, spedizione gratuita in Italia.', 'formalife-homepage' ),
						esc_html( $settings['book_price'] )
					);
					?>
				</span>
			</div>
		</div>
		<div class="fmh-hero-visual">
			<?php
			fmh_render_image(
				$settings['book_cover_image_id'],
				__( 'Copertina del libro La Guida Anti-Panico al Soffocamento Pediatrico', 'formalife-homepage' ),
				'',
				__( 'Copertina del libro', 'formalife-homepage' ),
				'large',
				false
			);
			?>
		</div>
	</div>
	<div class="fmh-hero-more">
		<a href="#differenza" class="fmh-scroll-link">
			<span><?php esc_html_e( 'Vuoi prima capire come funziona tutto il sistema Formalife? Continua a leggere', 'formalife-homepage' ); ?></span>
			<span class="fmh-scroll-link-arrow" aria-hidden="true">↓</span>
		</a>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 2 — PERCHÉ ESISTE FORMALIFE
     Funzione: motivare la ragion d'essere prima di mostrare il "come".
     Layout semplice, testo centrale su fondo bianco: qui non serve altro
     che leggibilità, la sezione successiva porta già il contrasto visivo.
     ===================================================================== -->
<section class="fmh-why-section">
	<div class="fmh-wrap fmh-why-wrap">
		<h2 class="fmh-reveal"><?php esc_html_e( 'Perché esiste Formalife', 'formalife-homepage' ); ?></h2>
		<p class="fmh-reveal"><?php esc_html_e( 'La paura non si può impedire — e in fondo non dovrebbe nemmeno sparire del tutto: è lei, spesso, a tenerci vigili e a non bloccarci. Quello che si può cambiare è cosa succede subito dopo, nel momento in cui serve lucidità.', 'formalife-homepage' ); ?></p>
		<p class="fmh-reveal"><?php esc_html_e( "Troppi genitori e caregiver vivono la cura di un bambino governati dall'ansia, senza essersi mai potuti allenare davvero a riconoscere un'emergenza pediatrica e a sapere cosa fare.", 'formalife-homepage' ); ?></p>
		<p class="fmh-why-close fmh-reveal"><?php esc_html_e( 'Per questo Formalife riunisce libro, corso pratico, contenuti e una direzione scientifica pediatrica in un unico sistema, con un solo obiettivo:', 'formalife-homepage' ); ?></p>
		<p class="fmh-why-goal fmh-reveal"><?php esc_html_e( 'che tu sappia sempre cosa fare, prima che il panico decida per te.', 'formalife-homepage' ); ?></p>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 3 — C'È UNA DIFFERENZA TRA OSSERVARE E SAPER AGIRE
     ===================================================================== -->
<section class="fmh-diff-section" id="differenza">
	<div class="fmh-wrap fmh-diff-wrap">
		<h2 class="fmh-reveal"><?php esc_html_e( "C'è una differenza tra osservare e saper agire", 'formalife-homepage' ); ?></h2>
		<p class="fmh-diff-intro fmh-reveal"><?php esc_html_e( 'Molte risorse — video, una dimostrazione dal vivo, una serata informativa — mostrano le manovre poche volte, davanti a un gruppo che guarda. È meglio di niente, ma lascia una sensazione di competenza che non è mai stata verificata: nessuno controlla se il gesto, rifatto da te, con tuo figlio in braccio e pochi secondi a disposizione, funziona davvero.', 'formalife-homepage' ); ?></p>

		<div class="fmh-diff-panels">
			<div class="fmh-diff-panel fmh-reveal fmh-reveal-1">
				<span class="fmh-diff-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
				</span>
				<span class="fmh-diff-label"><?php esc_html_e( 'Osservare', 'formalife-homepage' ); ?></span>
				<p><?php esc_html_e( 'Guardare una manovra spiegata bene, una o due volte, in un video o davanti a un gruppo.', 'formalife-homepage' ); ?></p>
			</div>
			<div class="fmh-diff-arrow" aria-hidden="true">→</div>
			<div class="fmh-diff-panel fmh-diff-panel--accent fmh-reveal fmh-reveal-2">
				<span class="fmh-diff-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
				</span>
				<span class="fmh-diff-label"><?php esc_html_e( 'Saper agire', 'formalife-homepage' ); ?></span>
				<p><?php esc_html_e( 'Rifare tu il gesto, con tuo figlio in braccio, sotto pressione — e sapere per certo che funziona.', 'formalife-homepage' ); ?></p>
			</div>
		</div>

		<p class="fmh-diff-close fmh-reveal">
			<?php esc_html_e( 'Restano impressi, ma non diventano automatici e', 'formalife-homepage' ); ?><br>
			<strong><?php esc_html_e( "in un'emergenza reale è l'automatismo che conta.", 'formalife-homepage' ); ?></strong>
		</p>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 4 — OSSERVA. VALUTA. AGISCI.
     Funzione: frase-mnemonica che riassume il metodo, con evidenziazione
     ciclica continua sulle tre parole (nessuna interazione richiesta
     all'utente, animazione discreta e continua).
     ===================================================================== -->
<?php $triad_bg_url = fmh_get_image_url( $settings['triad_bg_image_id'], 'full' ); ?>
<section class="fmh-triad-section">
	<?php if ( $triad_bg_url ) : ?>
		<div class="fmh-triad-bg" style="background-image:url('<?php echo esc_url( $triad_bg_url ); ?>'); opacity:<?php echo esc_attr( round( absint( $settings['triad_bg_opacity'] ) / 100, 2 ) ); ?>;" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="fmh-wrap">
		<span class="fmh-eyebrow fmh-eyebrow-center"><?php esc_html_e( 'Il metodo, in tre parole', 'formalife-homepage' ); ?></span>
		<p class="fmh-triad-words" aria-label="<?php esc_attr_e( 'Osserva. Valuta. Agisci.', 'formalife-homepage' ); ?>">
			<span class="fmh-triad-word fmh-triad-word-1"><?php esc_html_e( 'Osserva.', 'formalife-homepage' ); ?></span>&nbsp;<span class="fmh-triad-word fmh-triad-word-2"><?php esc_html_e( 'Valuta.', 'formalife-homepage' ); ?></span>&nbsp;<span class="fmh-triad-word fmh-triad-word-3"><?php esc_html_e( 'Agisci.', 'formalife-homepage' ); ?></span>
		</p>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 5 — QUAL È IL PROSSIMO PASSO?
     Funzione: router rapido a due opzioni (libro disponibile ora / corso
     in arrivo), prima ancora del dettaglio completo di ciascuna più in
     basso nella pagina. Due card brevi, simmetriche, ciascuna con
     un'unica CTA.
     ===================================================================== -->
<section class="fmh-next-section">
	<div class="fmh-wrap">
		<h2 class="fmh-reveal"><?php esc_html_e( 'Qual è il prossimo passo?', 'formalife-homepage' ); ?></h2>

		<div class="fmh-next-grid">
			<div class="fmh-next-card fmh-reveal fmh-reveal-1">
				<?php if ( $settings['next_book_image_id'] ) : ?>
					<div class="fmh-next-card-image"><?php fmh_render_image( $settings['next_book_image_id'], '', '', '', 'large' ); ?></div>
				<?php endif; ?>
				<span class="fmh-next-tag"><?php esc_html_e( 'Disponibile ora', 'formalife-homepage' ); ?></span>
				<h3><?php esc_html_e( 'Il libro', 'formalife-homepage' ); ?></h3>
				<p><?php esc_html_e( 'Il libro scritto da una pediatra per prevenire, riconoscere e agire nei vari casi di soffocamento, con esempi reali e immagini a colori.', 'formalife-homepage' ); ?></p>
				<?php fmh_render_cta_button( esc_html__( 'Scopri il libro', 'formalife-homepage' ), $book_url ); ?>
			</div>
			<div class="fmh-next-card fmh-next-card--course fmh-reveal fmh-reveal-2" id="il-corso">
				<?php if ( $settings['next_course_image_id'] ) : ?>
					<div class="fmh-next-card-image"><?php fmh_render_image( $settings['next_course_image_id'], '', '', '', 'large' ); ?></div>
				<?php endif; ?>
				<span class="fmh-next-tag fmh-next-tag--course"><?php echo esc_html( $course_page_url ? 'Date di settembre' : 'In arrivo le prossime date' ); ?></span>
				<h3><?php esc_html_e( 'Il corso pratico', 'formalife-homepage' ); ?></h3>
				<p><?php esc_html_e( 'Il corso pratico a numero chiuso, con istruttori qualificati e manichini professionali, per allenare le mani a fare ciò che la teoria da sola non può insegnare.', 'formalife-homepage' ); ?></p>
				<?php if ( $course_page_url ) : ?>
					<a class="fmh-btn-navy" href="<?php echo esc_url( $course_page_url ); ?>"><?php esc_html_e( 'Scopri date e iscrizioni', 'formalife-homepage' ); ?></a>
				<?php elseif ( $course_whatsapp_url ) : ?>
					<a class="fmh-btn-navy" href="<?php echo esc_url( $course_whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Voglio conoscere le prossime date', 'formalife-homepage' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 6 — IL LIBRO (offerta live, dettaglio completo)
     Layout "product card" ricco — unico punto della homepage dove si
     usano i colori del plugin del libro (teal/terracotta), perché qui si
     parla solo del libro.
     ===================================================================== -->
<section class="fmh-book-section" id="il-libro">
	<div class="fmh-wrap">
		<div class="fmh-book-card fmh-reveal">
			<div class="fmh-book-visual">
				<?php
				fmh_render_image(
					$settings['book_cover_image_id'],
					__( 'Copertina del libro La Guida Anti-Panico al Soffocamento Pediatrico', 'formalife-homepage' ),
					'',
					__( 'Copertina del libro', 'formalife-homepage' )
				);
				?>
				<?php if ( $settings['book_secondary_image_id'] ) : ?>
					<?php fmh_render_image( $settings['book_secondary_image_id'], '', 'fmh-book-visual-secondary' ); ?>
				<?php endif; ?>
			</div>
			<div class="fmh-book-content">
				<h2><?php esc_html_e( 'La Guida Anti-Panico al Soffocamento Pediatrico', 'formalife-homepage' ); ?></h2>

				<ul class="fmh-book-list">
					<li>
						<span class="fmh-book-list-label"><?php esc_html_e( 'Per chi è', 'formalife-homepage' ); ?></span>
						<span><?php esc_html_e( 'Ogni genitore o caregiver che vuole essere pronto prima che serva, senza aspettare un corso in aula.', 'formalife-homepage' ); ?></span>
					</li>
					<li>
						<span class="fmh-book-list-label"><?php esc_html_e( 'Cosa contiene', 'formalife-homepage' ); ?></span>
						<span><?php esc_html_e( '160 pagine a colori — prevenzione, riconoscimento dei segnali, sequenze corrette spiegate passo passo, distinzione tra tosse efficace e ostruzione completa, manovre di disostruzione caso per caso, cosa fare in caso di arresto cardiaco. Il tutto verificato sulle linee guida ERC 2025 e IRC.', 'formalife-homepage' ); ?></span>
					</li>
					<li>
						<span class="fmh-book-list-label"><?php esc_html_e( 'In omaggio', 'formalife-homepage' ); ?></span>
						<span><?php esc_html_e( 'Scheda stampabile "I tuoi numeri importanti" e anteprima sfogliabile online.', 'formalife-homepage' ); ?></span>
					</li>
				</ul>

				<div class="fmh-book-price-row">
					<div class="fmh-book-price-block">
						<span class="fmh-book-price"><?php echo esc_html( $settings['book_price'] ); ?></span>
						<span class="fmh-book-price-note"><?php esc_html_e( 'Spedizione gratuita in Italia. Prezzo di lancio della prevendita, non permanente.', 'formalife-homepage' ); ?></span>
					</div>
					<div class="fmh-book-dates">
						<span class="fmh-date-badge">📅 <?php printf( esc_html__( 'Prenotazioni entro il %s', 'formalife-homepage' ), esc_html( $settings['book_date_closing'] ) ); ?></span>
						<span class="fmh-date-badge">🚚 <?php printf( esc_html__( 'Spedizione %s', 'formalife-homepage' ), esc_html( $settings['book_date_delivery'] ) ); ?></span>
					</div>
				</div>

				<div class="fmh-book-guarantee">
					<span class="fmh-book-guarantee-icon" aria-hidden="true">🛡️</span>
					<p><?php esc_html_e( 'Se il libro non ti convince, tieni comunque la tua copia, ricevi il rimborso completo e in più uno sconto del 20% su un corso pratico Formalife — senza dover spiegare nulla e senza dover restituire niente.', 'formalife-homepage' ); ?></p>
				</div>

				<?php fmh_render_cta_button( esc_html__( 'Prenota ora la tua copia', 'formalife-homepage' ) . ' <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>', $book_url ); ?>
			</div>
		</div>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 7 — IL CORSO PRATICO
     ===================================================================== -->
<?php $waitlist_bg_url = fmh_get_image_url( $settings['waitlist_bg_image_id'], 'full' ); ?>
<section class="fmh-waitlist-section" id="corso-avviso">
	<?php if ( $waitlist_bg_url ) : ?><div class="fmh-waitlist-bg" style="background-image:url('<?php echo esc_url( $waitlist_bg_url ); ?>');" aria-hidden="true"></div><?php endif; ?>
	<div class="fmh-wrap">
		<div class="fmh-waitlist-card fmh-reveal">
			<?php if ( $course_page_url ) : ?>
				<span class="fmh-waitlist-tag"><?php echo $course_checkout_ready ? esc_html__( 'Iscrizioni aperte', 'formalife-homepage' ) : esc_html__( 'Prossime date', 'formalife-homepage' ); ?></span>
				<h2><?php esc_html_e( 'Corso Anti-Panico al Soffocamento Pediatrico', 'formalife-homepage' ); ?></h2>
				<ul class="fmh-waitlist-features">
					<?php foreach ( $course_sessions as $session ) : if ( ! $session['enabled'] ) continue; ?>
						<li><span aria-hidden="true">📅</span><?php echo esc_html( fmh_course_format_date( $session['date'] ) . ' · ' . $session['time'] ); ?></li>
					<?php endforeach; ?>
					<li><span aria-hidden="true">👥</span><?php esc_html_e( 'Massimo 12 partecipanti per sessione', 'formalife-homepage' ); ?></li>
					<li><span aria-hidden="true">📘</span><?php echo esc_html( fmh_course_format_price( $course_settings['single_price_cents'] ) . ' · libro incluso' ); ?></li>
				</ul>
				<p class="fmh-waitlist-note"><?php esc_html_e( 'La pagina del corso contiene date, dettagli, disponibilità e checkout sicuro.', 'formalife-homepage' ); ?></p>
				<a class="fmh-btn-primary" href="<?php echo esc_url( $course_page_url ); ?>"><?php esc_html_e( 'Vai al corso e scegli la data →', 'formalife-homepage' ); ?></a>
			<?php else : ?>
				<span class="fmh-waitlist-tag"><?php esc_html_e( 'In arrivo', 'formalife-homepage' ); ?></span>
				<h2><?php esc_html_e( 'Il nuovo corso pratico Formalife sta per aprire le iscrizioni', 'formalife-homepage' ); ?></h2>
				<ul class="fmh-waitlist-features">
					<li><span aria-hidden="true">👥</span><?php esc_html_e( 'Gruppi a numero chiuso, fino a 12 partecipanti per sessione', 'formalife-homepage' ); ?></li>
					<li><span aria-hidden="true">🤲</span><?php esc_html_e( 'Pratica su manichino, con correzione individuale', 'formalife-homepage' ); ?></li>
				</ul>
				<p class="fmh-waitlist-note"><?php esc_html_e( 'Le date non sono ancora pubblicate. Lasciaci un contatto e ti avviseremo.', 'formalife-homepage' ); ?></p>
				<form id="fmh-waitlist-form" class="fmh-waitlist-form" novalidate><div class="fmh-waitlist-form-row"><label for="fmh-w-nome" class="fmh-visually-hidden">Nome e cognome</label><input type="text" id="fmh-w-nome" name="nome" placeholder="Nome e cognome *" required autocomplete="name" /><label for="fmh-w-email" class="fmh-visually-hidden">Email</label><input type="email" id="fmh-w-email" name="email" placeholder="La tua email *" required autocomplete="email" /></div><div class="fmh-waitlist-consent"><input type="checkbox" id="fmh-w-consenso" name="consenso" value="1" required /><label for="fmh-w-consenso">Ti scriveremo solo per comunicarti l'apertura delle iscrizioni al corso. *</label></div><button type="submit" class="fmh-btn-primary">Voglio essere avvisato/a</button><p class="fmh-waitlist-message" aria-live="polite"></p></form>
			<?php endif; ?>
		</div>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 8 — FORMALIFE ACCOMPAGNA LA FAMIGLIA DURANTE LE TAPPE PIÙ
     IMPORTANTI DELLA CRESCITA DEL BAMBINO
     Box ridisegnati: nessuna icona, testo più grande e centrato
     verticalmente, box più alti, immagine di sfondo al 15% di opacità,
     hover con sfondo blu/verde scuro alternato + testo bianco + scale.
     ===================================================================== -->
<section class="fmh-moments-section">
	<div class="fmh-wrap">
		<h2 class="fmh-reveal"><?php esc_html_e( 'Formalife accompagna la famiglia durante le tappe più importanti della crescita del bambino', 'formalife-homepage' ); ?></h2>

		<div class="fmh-moments-grid">
			<?php
			$moments = array(
				array(
					'title'   => __( "Aspetti un bambino o l'hai appena avuto", 'formalife-homepage' ),
					'body'    => __( 'Prepararti prima che serva, senza ancora sapere esattamente a cosa.', 'formalife-homepage' ),
					'variant' => 'blue',
					'bg_key'  => 'moment_1_bg_image_id',
				),
				array(
					'title'   => __( 'Hai iniziato lo svezzamento', 'formalife-homepage' ),
					'body'    => __( 'Il rischio quotidiano è in ogni pasto.', 'formalife-homepage' ),
					'variant' => 'green',
					'bg_key'  => 'moment_2_bg_image_id',
				),
				array(
					'title'   => __( "Tuo figlio è entrato al nido o alla scuola dell'infanzia", 'formalife-homepage' ),
					'body'    => __( 'La cura si condivide con altre persone.', 'formalife-homepage' ),
					'variant' => 'blue',
					'bg_key'  => 'moment_3_bg_image_id',
				),
				array(
					'title'   => __( "Sei tornato/a al lavoro e nonni o babysitter si occupano di lui", 'formalife-homepage' ),
					'body'    => __( 'Tutti i caregiver dovrebbero sapere cosa fare e cosa non fare.', 'formalife-homepage' ),
					'variant' => 'green',
					'bg_key'  => 'moment_4_bg_image_id',
				),
				array(
					'title'   => __( 'Avete già vissuto un brutto evento', 'formalife-homepage' ),
					'body'    => __( "Capita, e deve essere un'occasione per prepararsi meglio, non per sentirsi in colpa.", 'formalife-homepage' ),
					'variant' => 'blue',
					'bg_key'  => 'moment_5_bg_image_id',
				),
				array(
					'title'   => __( "È passato più di un anno dall'ultimo aggiornamento", 'formalife-homepage' ),
					'body'    => __( 'La pratica si dimentica, e tuo figlio nel frattempo è cambiato.', 'formalife-homepage' ),
					'variant' => 'green',
					'bg_key'  => 'moment_6_bg_image_id',
				),
			);
			foreach ( $moments as $i => $moment ) :
				$bg_url = fmh_get_image_url( $settings[ $moment['bg_key'] ], 'large' );
				?>
				<div class="fmh-moment-card fmh-moment-card--<?php echo esc_attr( $moment['variant'] ); ?> fmh-reveal fmh-reveal-<?php echo esc_attr( ( $i % 5 ) + 1 ); ?>">
					<?php if ( $bg_url ) : ?>
						<div class="fmh-moment-bg" style="background-image:url('<?php echo esc_url( $bg_url ); ?>');" aria-hidden="true"></div>
					<?php endif; ?>
					<div class="fmh-moment-content">
						<h3><?php echo esc_html( $moment['title'] ); ?></h3>
						<p><?php echo esc_html( $moment['body'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<p class="fmh-moments-close"><?php esc_html_e( 'Qualunque sia il momento di crescita del tuo bambino, il punto di partenza resta lo stesso: il libro oggi, il corso pratico appena possibile.', 'formalife-homepage' ); ?></p>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 9 — PER ASILI, AZIENDE E PROFESSIONISTI (B2B)
     ===================================================================== -->
<section class="fmh-b2b-section">
	<div class="fmh-wrap fmh-b2b-wrap fmh-reveal">
		<?php if ( $settings['b2b_image_id'] ) : ?>
			<div class="fmh-b2b-image"><?php fmh_render_image( $settings['b2b_image_id'], '', '', '', 'medium' ); ?></div>
		<?php else : ?>
			<span class="fmh-b2b-icon" aria-hidden="true">🏢</span>
		<?php endif; ?>
		<div class="fmh-b2b-text">
			<h2><?php esc_html_e( 'Anche la tua organizzazione può portare questa preparazione alle famiglie che segue', 'formalife-homepage' ); ?></h2>
			<p><?php esc_html_e( 'Formalife lavora anche con asili, scuole, aziende e professionisti sanitari che vogliono offrire questa preparazione al proprio staff, alle famiglie che seguono, o come iniziativa di welfare.', 'formalife-homepage' ); ?></p>
			<p><?php esc_html_e( 'Hai in mente delle iniziative per la tua organizzazione? Scrivici direttamente e troviamo insieme la configurazione giusta.', 'formalife-homepage' ); ?></p>
		</div>
		<div class="fmh-b2b-cta">
			<?php if ( $contact_whatsapp ) : ?>
				<a class="fmh-btn-whatsapp" href="<?php echo esc_url( $contact_whatsapp ); ?>" target="_blank" rel="noopener noreferrer">
					<svg viewBox="0 0 48 48" aria-hidden="true"><circle cx="24" cy="24" r="24" fill="#fff"/><path fill="#25D366" d="M24 11c-7.2 0-13 5.8-13 13 0 2.4.6 4.6 1.8 6.6L11 37l6.6-1.7c1.9 1 4.1 1.6 6.4 1.6 7.2 0 13-5.8 13-13s-5.8-13-13-13Zm0 2.2c6 0 10.8 4.8 10.8 10.8S30 34.8 24 34.8c-2 0-3.9-.5-5.6-1.5l-.4-.2-3.9 1 1-3.8-.3-.4A10.7 10.7 0 0 1 13.2 24c0-6 4.8-10.8 10.8-10.8Z"/><path fill="#25D366" d="M19.8 18.4c-.2-.5-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.1-1.2 2.8s1.2 3.3 1.4 3.5c.2.3 2.3 3.6 5.7 4.9 2.8 1.1 3.4.9 4 .8.6-.1 1.9-.8 2.2-1.5.3-.7.3-1.3.2-1.5-.1-.2-.4-.3-.7-.5-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.3-.7.1-.3-.2-1.3-.5-2.5-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.7.1-.1.3-.4.5-.5.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5-.1-.2-.7-1.8-1-2.5Z"/></svg>
					<?php esc_html_e( 'Scrivici su WhatsApp', 'formalife-homepage' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $contact_email ) : ?>
				<a class="fmh-btn-gmail" href="mailto:<?php echo esc_attr( $contact_email ); ?>">
					<svg viewBox="0 0 48 48" aria-hidden="true"><rect width="48" height="48" rx="10" fill="#fff"/><path fill="#4285F4" d="M6 14v20a4 4 0 0 0 4 4h4V19.4L24 28l10-8.6V38h4a4 4 0 0 0 4-4V14a4 4 0 0 0-1.6-3.2L24 22 7.6 10.8A4 4 0 0 0 6 14Z"/><path fill="#EA4335" d="M6 14a4 4 0 0 1 1.6-3.2L24 22 6.4 11a4 4 0 0 0-.4.6Z"/><path fill="#34A853" d="M14 38v-14l-8-6.2V34a4 4 0 0 0 4 4Z"/><path fill="#FBBC05" d="M34 38v-14l8-6.2V34a4 4 0 0 1-4 4Z"/></svg>
					<?php esc_html_e( 'Manda un\'email', 'formalife-homepage' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 10 — LA GARANZIA
     ===================================================================== -->
<section class="fmh-guarantee-section">
	<div class="fmh-wrap">
		<span class="fmh-eyebrow fmh-eyebrow-center"><?php esc_html_e( 'Onestà prima di tutto', 'formalife-homepage' ); ?></span>
		<h2 class="fmh-reveal"><?php esc_html_e( 'Che cosa garantiamo, e che cosa no', 'formalife-homepage' ); ?></h2>

		<div class="fmh-guarantee-columns">
			<div class="fmh-guarantee-col fmh-guarantee-col--yes fmh-reveal fmh-reveal-1">
				<span class="fmh-guarantee-col-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.4 9 8 11 4.6-2 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
				</span>
				<span class="fmh-guarantee-col-tag"><?php esc_html_e( 'Garantiamo', 'formalife-homepage' ); ?></span>
				<ul class="fmh-guarantee-list">
					<li><span aria-hidden="true">📘</span><?php esc_html_e( 'Il contenuto del libro', 'formalife-homepage' ); ?></li>
					<li><span aria-hidden="true">🎨</span><?php esc_html_e( "La qualità dell'edizione", 'formalife-homepage' ); ?></li>
					<li><span aria-hidden="true">🤲</span><?php esc_html_e( 'La pratica supervisionata durante il corso', 'formalife-homepage' ); ?></li>
					<li><span aria-hidden="true">💬</span><?php esc_html_e( "L'assistenza dopo l'acquisto", 'formalife-homepage' ); ?></li>
				</ul>
				<p class="fmh-guarantee-note"><?php esc_html_e( 'Se qualcosa non viene erogato come promesso, la soluzione è semplice e proporzionata: correzione, recupero, ripetizione della parte interessata o rimborso.', 'formalife-homepage' ); ?></p>
			</div>
			<div class="fmh-guarantee-col fmh-guarantee-col--no fmh-reveal fmh-reveal-2">
				<span class="fmh-guarantee-col-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v5"/><circle cx="12" cy="16.3" r="0.6" fill="currentColor" stroke="none"/></svg>
				</span>
				<span class="fmh-guarantee-col-tag"><?php esc_html_e( 'Non possiamo garantire', 'formalife-homepage' ); ?></span>
				<p class="fmh-guarantee-quote"><?php esc_html_e( "Come reagirà una persona in un'emergenza reale, o che la memoria di una manovra resti intatta per sempre senza mai più fare pratica.", 'formalife-homepage' ); ?></p>
				<p class="fmh-guarantee-note"><?php esc_html_e( 'Nessuno può prometterlo onestamente, e diffidare di chi lo fa è già una forma di consapevolezza.', 'formalife-homepage' ); ?></p>
			</div>
		</div>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 11 — CHI C'È DIETRO FORMALIFE
     Layout basato su "gaps-author-section" (plugin del libro): foto più
     piccola, testo più ampio, nessun badge di fiducia (sostituito dal
     carosello nella sezione successiva). Ripetuto una volta per membro.
     Ruolo e bio personalizzabili dal pannello impostazioni.
     ===================================================================== -->
<?php $team_bg_url = fmh_get_image_url( $settings['team_bg_image_id'], 'full' ); ?>
<section class="fmh-team-section">
	<?php if ( $team_bg_url ) : ?>
		<div class="fmh-team-bg" style="background-image:url('<?php echo esc_url( $team_bg_url ); ?>'); opacity:<?php echo esc_attr( round( absint( $settings['team_bg_opacity'] ) / 100, 2 ) ); ?>;" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="fmh-wrap">
		<span class="fmh-eyebrow fmh-eyebrow-center"><?php esc_html_e( "Chi c'è dietro Formalife", 'formalife-homepage' ); ?></span>
		<h2 class="fmh-team-title"><?php esc_html_e( 'Persone reali, non un brand anonimo', 'formalife-homepage' ); ?></h2>

		<div class="fmh-team-rows">
			<div class="fmh-team-row fmh-reveal fmh-reveal-1">
				<div class="fmh-team-row-photo">
					<?php fmh_render_avatar( $settings['camposarcone_image_id'], 'Mafalda Camposarcone' ); ?>
				</div>
				<div class="fmh-team-row-text">
					<h3><?php esc_html_e( 'Dott.ssa Mafalda Camposarcone', 'formalife-homepage' ); ?></h3>
					<span class="fmh-team-role"><?php echo esc_html( $settings['team_mafalda_role'] ); ?></span>
					<p><?php echo esc_html( $settings['team_mafalda_bio'] ); ?></p>
				</div>
			</div>
			<div class="fmh-team-row fmh-reveal fmh-reveal-2">
				<div class="fmh-team-row-photo">
					<?php fmh_render_avatar( $settings['raffaele_image_id'], 'Raffaele La Torre' ); ?>
				</div>
				<div class="fmh-team-row-text">
					<h3><?php esc_html_e( 'Raffaele La Torre', 'formalife-homepage' ); ?></h3>
					<span class="fmh-team-role"><?php echo esc_html( $settings['team_raffaele_role'] ); ?></span>
					<p><?php echo esc_html( $settings['team_raffaele_bio'] ); ?></p>
				</div>
			</div>
			<div class="fmh-team-row fmh-reveal fmh-reveal-3">
				<div class="fmh-team-row-photo">
					<?php fmh_render_avatar( $settings['sara_image_id'], 'Sara Bertoli' ); ?>
				</div>
				<div class="fmh-team-row-text">
					<h3><?php esc_html_e( 'Sara Bertoli', 'formalife-homepage' ); ?></h3>
					<span class="fmh-team-role"><?php echo esc_html( $settings['team_sara_role'] ); ?></span>
					<p><?php echo esc_html( $settings['team_sara_bio'] ); ?></p>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 12 — CAROSELLO CORSI DAL VIVO
     Sostituisce la precedente fascia con i badge di fiducia: immagini
     reali (fino a 5, dal pannello) come sfondo a rotazione automatica
     della sezione stessa, nessun pulsante/freccia/controllo.
     ===================================================================== -->
<?php
if ( ! empty( $carousel_urls ) ) :
	$carousel_count = count( $carousel_urls );
	// Due copie identiche in sequenza: animando la track di -50% (della sua
	// stessa larghezza) si scorre esattamente attraverso il primo set,
	// rientrando senza soluzione di continuità nel secondo — loop perfetto,
	// qualunque sia il numero di immagini caricate. Sempre 2 visibili alla
	// volta: ogni slide occupa il 50% della sezione.
	$slide_basis_pct = round( 100 / ( $carousel_count * 2 ), 4 );
	$track_width_pct = $carousel_count * 100; // = ( carousel_count * 2 ) * 50%
	$loop_images      = array_merge( $carousel_urls, $carousel_urls );
	?>
<section class="fmh-carousel-section" aria-label="<?php esc_attr_e( 'Immagini dai corsi pratici Formalife', 'formalife-homepage' ); ?>">
	<div class="fmh-carousel-track" style="width:<?php echo esc_attr( $track_width_pct ); ?>%;">
		<?php foreach ( $loop_images as $url ) : ?>
			<div class="fmh-carousel-slide" style="flex-basis:<?php echo esc_attr( $slide_basis_pct ); ?>%; background-image:url('<?php echo esc_url( $url ); ?>');"></div>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<!-- =====================================================================
     SEZIONE 13 — QUAL È IL COSTO DI NON AGIRE?
     Portata dalla landing del libro (sezione "Costo dell'inazione"),
     ricolorata in navy invece del verde originale: stessi dati/copy.
     ===================================================================== -->
<section class="fmh-cost-section">
	<div class="fmh-wrap">
		<span class="fmh-eyebrow fmh-eyebrow-center"><?php esc_html_e( 'Dati ufficiali', 'formalife-homepage' ); ?></span>
		<h2 class="fmh-reveal"><?php esc_html_e( 'Qual è il costo di non agire?', 'formalife-homepage' ); ?></h2>
		<p class="fmh-cost-intro"><?php esc_html_e( 'Prima di continuare, guarda i numeri con cui ci confrontiamo ogni giorno per capire la portata del problema.', 'formalife-homepage' ); ?></p>

		<div class="fmh-stat-row">
			<div class="fmh-stat-card fmh-reveal fmh-reveal-1">
				<span class="fmh-stat-icon" aria-hidden="true">💔</span>
				<span class="fmh-stat-number"><?php echo esc_html( $settings['stat1_number'] ); ?></span>
				<span class="fmh-stat-label"><?php echo esc_html( $settings['stat1_label'] ); ?></span>
			</div>
			<div class="fmh-stat-card fmh-reveal fmh-reveal-2">
				<span class="fmh-stat-icon" aria-hidden="true">🏥</span>
				<span class="fmh-stat-number"><?php echo esc_html( $settings['stat2_number'] ); ?></span>
				<span class="fmh-stat-label"><?php echo esc_html( $settings['stat2_label'] ); ?></span>
			</div>
			<div class="fmh-stat-card fmh-reveal fmh-reveal-3">
				<span class="fmh-stat-icon" aria-hidden="true">🍽️</span>
				<span class="fmh-stat-number"><?php echo esc_html( $settings['stat3_number'] ); ?></span>
				<span class="fmh-stat-label"><?php echo esc_html( $settings['stat3_label'] ); ?></span>
			</div>
		</div>
		<p class="fmh-stat-source"><?php echo esc_html( $settings['stat_source'] ); ?></p>

		<div class="fmh-cost-body">
			<p><?php esc_html_e( 'Il soffocamento è probabilmente una di quelle paure che tieni a distanza — qualcosa che succede, certo, ma ad altri. I numeri raccontano un\'altra storia: può succedere durante un pasto qualunque, in una casa normale.', 'formalife-homepage' ); ?></p>
			<p><?php esc_html_e( 'Puoi continuare a sperare che non capiti mai, oppure puoi diventare la persona che, se dovesse capitare, saprebbe cosa fare.', 'formalife-homepage' ); ?></p>
			<p class="fmh-cost-final">
				<?php esc_html_e( 'La preparazione non elimina il rischio.', 'formalife-homepage' ); ?><br>
				<?php esc_html_e( 'Niente può farlo.', 'formalife-homepage' ); ?><br>
				<strong><?php esc_html_e( 'Ma cambia chi sei nel momento che più conta.', 'formalife-homepage' ); ?></strong>
			</p>
		</div>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 14 — FAQ
     ===================================================================== -->
<section class="fmh-faq-section">
	<div class="fmh-wrap">
		<h2><?php esc_html_e( 'FAQ: le domande che ci fanno più spesso', 'formalife-homepage' ); ?></h2>
		<div class="fmh-faq-list">
			<details class="fmh-faq-item fmh-reveal fmh-reveal-1">
				<summary><span class="fmh-faq-number">1</span><span class="fmh-faq-q"><?php esc_html_e( 'Perché pagare, se in rete ci sono video gratuiti?', 'formalife-homepage' ); ?></span></summary>
				<p><?php esc_html_e( "Un video mostra un gesto, una volta, in una situazione generica. Formalife ti fa esercitare, corregge l'errore e ti fa riprovare più volte finché ti senti sicuro. Il libro comunque non sostituisce la pratica: è pensato come punto di partenza, non come alternativa al corso.", 'formalife-homepage' ); ?></p>
			</details>
			<details class="fmh-faq-item fmh-reveal fmh-reveal-2">
				<summary><span class="fmh-faq-number">2</span><span class="fmh-faq-q"><?php esc_html_e( 'Se ho già letto il libro, mi serve comunque il corso?', 'formalife-homepage' ); ?></span></summary>
				<p><?php esc_html_e( 'Sì, se vuoi arrivare ad essere pronto ad agire anche sotto pressione: sono due strumenti diversi, complementari, non uno la versione ridotta dell\'altro.', 'formalife-homepage' ); ?></p>
			</details>
			<details class="fmh-faq-item fmh-reveal fmh-reveal-3">
				<summary><span class="fmh-faq-number">3</span><span class="fmh-faq-q"><?php esc_html_e( 'Che cosa succede se non riesco a partecipare alla data del corso?', 'formalife-homepage' ); ?></span></summary>
				<p><?php esc_html_e( 'Le condizioni di recupero e trasferimento saranno pubblicate insieme alle date del corso, non ti preoccupare.', 'formalife-homepage' ); ?></p>
			</details>
			<details class="fmh-faq-item fmh-reveal fmh-reveal-4">
				<summary><span class="fmh-faq-number">4</span><span class="fmh-faq-q"><?php esc_html_e( 'Come vengono usati i miei dati?', 'formalife-homepage' ); ?></span></summary>
				<p>
					<?php
					echo wp_kses(
						sprintf(
							/* translators: %s: link alla pagina privacy */
							__( "Solo per lo scopo che hai autorizzato — inviarti il libro, avvisarti dell'apertura del corso, o rispondere alla tua richiesta. Puoi consultare %s in ogni momento e revocare il consenso quando vuoi.", 'formalife-homepage' ),
							$privacy_url ? '<a href="' . esc_url( $privacy_url ) . '">' . esc_html__( "l'informativa privacy", 'formalife-homepage' ) . '</a>' : esc_html__( "l'informativa privacy", 'formalife-homepage' )
						),
						array( 'a' => array( 'href' => array() ) )
					);
					?>
				</p>
			</details>
		</div>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 15 — IL PROSSIMO PASSO PIÙ SEMPLICE: INIZIARE DAL LIBRO
     CTA primaria in stile "cream" (non rossa: qui non è un'azione urgente
     ma un invito pacato), CTA secondaria verso il corso, box P.S. con CTA
     rossa (testo bianco) verso l'acquisto del libro.
     ===================================================================== -->
<section class="fmh-closing-section">
	<div class="fmh-wrap">
		<h2><?php esc_html_e( 'Il prossimo passo più semplice: iniziare dal libro', 'formalife-homepage' ); ?></h2>
		<div class="fmh-closing-cta-row">
			<a class="fmh-btn-cream fmh-btn-lg" href="<?php echo esc_url( $book_url ); ?>"><?php esc_html_e( 'Scopri la guida anti-panico al soffocamento pediatrico', 'formalife-homepage' ); ?></a>
			<a class="fmh-btn-outline fmh-btn-lg" href="#il-corso"><?php esc_html_e( 'Voglio essere avvisato/a quando aprono le iscrizioni al corso', 'formalife-homepage' ); ?></a>
		</div>

		<div class="fmh-ps-box">
			<span class="fmh-ps-badge">P.S.</span>
			<p>
				<?php
				echo wp_kses(
					__( 'Se hai letto fin qui, probabilmente hai già capito la cosa più importante:&nbsp;<strong>la preparazione si costruisce prima</strong>, con calma, non nel momento in cui l\'emergenza è arrivata.', 'formalife-homepage' ),
					array( 'strong' => array() )
				);
				?>
			</p>
			<p><?php esc_html_e( 'Il libro è disponibile subito. Il corso pratico apre le iscrizioni a breve: lascia un contatto e ti scriveremo quando sarai pronto a prenotare il tuo posto.', 'formalife-homepage' ); ?></p>
			<div class="fmh-ps-cta-wrap">
				<?php fmh_render_cta_button( esc_html__( 'Prenota la tua copia →', 'formalife-homepage' ), $book_url ); ?>
			</div>
		</div>
	</div>
</section>

<!-- =====================================================================
     SEZIONE 16 — CONTATTI FINALI
     ===================================================================== -->
<section class="fmh-contact-section">
	<div class="fmh-wrap">
		<h2><?php esc_html_e( 'Hai qualche domanda da farci? Scrivici dove preferisci!', 'formalife-homepage' ); ?></h2>
		<div class="fmh-contact-row">
			<?php if ( $contact_email ) : ?>
				<a class="fmh-contact-pill" href="mailto:<?php echo esc_attr( $contact_email ); ?>">
					<span class="fmh-contact-icon" aria-hidden="true">
						<svg viewBox="0 0 48 48"><rect width="48" height="48" rx="10" fill="#fff"/><path fill="#4285F4" d="M6 14v20a4 4 0 0 0 4 4h4V19.4L24 28l10-8.6V38h4a4 4 0 0 0 4-4V14a4 4 0 0 0-1.6-3.2L24 22 7.6 10.8A4 4 0 0 0 6 14Z"/><path fill="#EA4335" d="M6 14a4 4 0 0 1 1.6-3.2L24 22 6.4 11a4 4 0 0 0-.4.6Z"/><path fill="#34A853" d="M14 38v-14l-8-6.2V34a4 4 0 0 0 4 4Z"/><path fill="#FBBC05" d="M34 38v-14l8-6.2V34a4 4 0 0 1-4 4Z"/></svg>
					</span>
					<?php echo esc_html( $contact_email ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $contact_whatsapp ) : ?>
				<a class="fmh-contact-pill" href="<?php echo esc_url( $contact_whatsapp ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="fmh-contact-icon" aria-hidden="true">
						<svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="24" fill="#25D366"/><path fill="#fff" d="M24 11c-7.2 0-13 5.8-13 13 0 2.4.6 4.6 1.8 6.6L11 37l6.6-1.7c1.9 1 4.1 1.6 6.4 1.6 7.2 0 13-5.8 13-13s-5.8-13-13-13Zm0 2.2c6 0 10.8 4.8 10.8 10.8S30 34.8 24 34.8c-2 0-3.9-.5-5.6-1.5l-.4-.2-3.9 1 1-3.8-.3-.4A10.7 10.7 0 0 1 13.2 24c0-6 4.8-10.8 10.8-10.8Z"/><path fill="#fff" d="M19.8 18.4c-.2-.5-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.1-1.2 2.8s1.2 3.3 1.4 3.5c.2.3 2.3 3.6 5.7 4.9 2.8 1.1 3.4.9 4 .8.6-.1 1.9-.8 2.2-1.5.3-.7.3-1.3.2-1.5-.1-.2-.4-.3-.7-.5-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.3-.7.1-.3-.2-1.3-.5-2.5-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.7.1-.1.3-.4.5-.5.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5-.1-.2-.7-1.8-1-2.5Z"/></svg>
					</span>
					<?php esc_html_e( 'WhatsApp', 'formalife-homepage' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $contact_instagram ) : ?>
				<a class="fmh-contact-pill" href="<?php echo esc_url( $contact_instagram ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="fmh-contact-icon" aria-hidden="true">
						<svg viewBox="0 0 48 48"><defs><linearGradient id="fmhIgGrad" x1="0" y1="48" x2="48" y2="0"><stop offset="0" stop-color="#FFDC80"/><stop offset="0.25" stop-color="#FCAF45"/><stop offset="0.5" stop-color="#E1306C"/><stop offset="0.75" stop-color="#C13584"/><stop offset="1" stop-color="#833AB4"/></linearGradient></defs><rect width="48" height="48" rx="12" fill="url(#fmhIgGrad)"/><rect x="12" y="12" width="24" height="24" rx="7" fill="none" stroke="#fff" stroke-width="2.4"/><circle cx="24" cy="24" r="6.2" fill="none" stroke="#fff" stroke-width="2.4"/><circle cx="32.2" cy="15.8" r="1.6" fill="#fff"/></svg>
					</span>
					<?php esc_html_e( 'Instagram', 'formalife-homepage' ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php if ( $settings['formalife_logo_id'] ) : ?>
			<div class="fmh-contact-logo">
				<?php fmh_render_image( $settings['formalife_logo_id'], __( 'Formalife', 'formalife-homepage' ) ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<footer class="fmh-footer">
	<?php if ( $terms_url ) : ?>
		<a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Condizioni di vendita', 'formalife-homepage' ); ?></a>
		<span class="fmh-footer-sep">·</span>
	<?php endif; ?>
	<?php if ( $privacy_url ) : ?>
		<a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy', 'formalife-homepage' ); ?></a>
		<span class="fmh-footer-sep">·</span>
	<?php endif; ?>
	<span>
		<?php echo esc_html( $settings['legal_company_name'] ); ?>
		<?php if ( $settings['legal_vat_number'] ) : ?>
			· <?php esc_html_e( 'P.IVA', 'formalife-homepage' ); ?> <?php echo esc_html( $settings['legal_vat_number'] ); ?>
		<?php endif; ?>
	</span>
</footer>

</div>
<?php wp_footer(); ?>
</body>
</html>
