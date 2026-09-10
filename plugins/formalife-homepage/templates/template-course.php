<?php
/** Landing del Corso Anti-Panico al Soffocamento Pediatrico. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$home     = fmh_get_settings();
$course   = fmh_course_get_settings();
$sessions = fmh_course_get_sessions( true );
$ready    = fmh_course_checkout_ready();
$nav      = fmh_get_nav_urls();
$privacy  = fmh_course_get_privacy_url();
$terms    = fmh_course_get_terms_url();
$hero     = $course['hero_image_id'] ?: $home['carousel_image_1_id'];
$book     = $course['book_image_id'] ?: $home['book_cover_image_id'];
$session_list = array_values( $sessions );

// Stessa immagine dell'hero riusata come sfondo della sezione (opacità 10%).
$hero_bg_url = $hero ? fmh_get_image_url( $hero, 'fmh-course-hero' ) : '';

// Prezzo senza decimali ("80", "120", "160"): usato dove il copy richiede la
// cifra tonda. fmh_course_format_price() resta invariata per tutto il resto.
$eur0 = static function ( $cents ) {
	return number_format_i18n( max( 0, absint( $cents ) ) / 100, 0 );
};
$single_0 = $eur0( $course['single_price_cents'] );
$couple_0 = $eur0( $course['couple_price_cents'] );
$double_0 = $eur0( $course['single_price_cents'] * 2 );

$timeline = array(
	array( 'ANTI-PANICO', 'Prepara la mente', 'Capisci cosa succede al tuo cervello sotto stress e costruisci una sequenza mentale semplice da recuperare anche quando la paura arriva prima del pensiero.' ),
	array( 'PREVIENI', 'Riduci i rischi prima dell’emergenza', 'Alimenti, tagli, comportamenti, piccoli oggetti e ambiente domestico: molte situazioni pericolose possono essere affrontate prima che diventino un’emergenza.' ),
	array( 'RICONOSCI', 'Capisci la situazione che hai davanti', 'Tosse efficace, ostruzione parziale, ostruzione totale: impari a riconoscere ciò che cambia completamente la decisione successiva.' ),
	array( 'VALUTA', 'Decidi se intervenire', 'Impari a fermare per un istante il panico, leggere i segnali importanti e prendere la decisione corretta.' ),
	array( 'AGISCI', 'Esegui le manovre', 'Impari e provi le manovre specifiche per lattante e bambino, con un istruttore accanto che ti guida e ti corregge.' ),
);
$program = array(
	array( 'ANTI-PANICO', 'Preparazione mentale contro le emergenze', 'Come reagisce il cervello sotto stress, perché il panico può prendere il controllo e come costruire una sequenza mentale semplice prima che serva davvero.' ),
	array( 'PRIMA CHE SUCCEDA', 'Prevenzione alimentare e ambientale', 'Alimenti e tagli a rischio, comportamenti durante i pasti, oggetti domestici e altre situazioni che possono aumentare il rischio di soffocamento.' ),
	array( 'QUANDO ACCADE', 'Riconoscere una tosse efficace da un’ostruzione totale', 'Impari a osservare i segnali davvero importanti e a distinguere una situazione in cui la tosse sta ancora funzionando da una in cui serve intervenire.' ),
	array( 'QUANDO INTERVENIRE', 'Impara le manovre specifiche per bambini e lattanti', 'Vedi, provi e ripeti le tecniche appropriate per le diverse età, con correzione diretta dell’istruttore.' ),
	array( 'OLTRE L’OSTRUZIONE', 'Cosa fare se la situazione non si risolve', 'Cosa cambia quando il bambino perde coscienza e quali sono i passaggi successivi da conoscere.' ),
	array( 'LA VITA REALE', 'Cosa fare in casi veri e situazioni particolari', 'Essere soli, essere in due, trovarsi al ristorante, in auto, con nonni o babysitter: l’emergenza reale raramente assomiglia alla dimostrazione perfetta.' ),
);
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo esc_html( $course['meta_title'] ); ?></title>
<meta name="description" content="<?php echo esc_attr( $course['meta_description'] ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'fmh-course-body' ); ?>>
<?php wp_body_open(); ?>

<nav class="fmh-nav"><div class="fmh-wrap fmh-nav-inner">
	<a class="fmh-nav-logo" href="<?php echo esc_url( $nav['home'] ); ?>"><?php if ( $home['formalife_logo_id'] ) { fmh_render_image( $home['formalife_logo_id'], 'Formalife', '', '', 'medium', false ); } else { echo '<span class="fmh-nav-logo-text">Formalife</span>'; } ?></a>
	<ul class="fmh-nav-menu"><li><a href="<?php echo esc_url( $nav['home'] ); ?>">Home</a></li><li><a href="#sessioni">Date</a></li><li><a href="#faq">FAQ</a></li></ul>
</div></nav>

<main class="fmh-course-page">

<!-- ============================== 1. HERO ============================== -->
<section class="fmh-course-hero" id="hero">
	<?php if ( $hero_bg_url ) : ?>
		<div class="fmh-course-hero-bg" style="background-image:url('<?php echo esc_url( $hero_bg_url ); ?>');" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="fmh-wrap fmh-course-hero-grid">
	<div>
		<span class="fmh-eyebrow"><?php echo esc_html( $course['hero_eyebrow'] ); ?></span>
		<h1><?php echo esc_html( $course['hero_headline'] ); ?></h1>
		<p class="fmh-course-lead"><?php echo esc_html( $course['hero_subheadline'] ); ?></p>

		<div class="fmh-hero-schedule">
			<?php foreach ( $session_list as $session ) : ?>
				<span class="fmh-date-badge">📅 <?php echo esc_html( fmh_course_format_date( $session['date'] ) . ' · ' . $session['city'] . ' · ' . $session['time'] ); ?></span>
			<?php endforeach; ?>
		</div>

		<div class="fmh-price"><strong><?php echo esc_html( fmh_course_format_price( $course['single_price_cents'] ) ); ?></strong><b>Nuovo Libro sul Soffocamento incluso!</b></div>

		<div class="fmh-couple">
			<span>Venite in due?</span>
			<strong><?php echo esc_html( $couple_0 ); ?> € in coppia invece di <?php echo esc_html( $double_0 ); ?> €</strong>
			<small>Non sapete a chi lasciare il bambino? Potete portarlo al corso!</small>
		</div>

		<a class="fmh-btn-primary" href="#sessioni"><?php echo esc_html( $course['hero_cta_label'] ); ?></a>
		<small class="fmh-microcopy">Pagamento sicuro online · Cambio data gratuito · Retraining pratico incluso</small>
	</div>
	<div class="fmh-course-hero-media">
		<?php if ( $course['hero_mobile_image_id'] && $hero ) : ?>
			<picture>
				<source media="(max-width:680px)" srcset="<?php echo esc_url( wp_get_attachment_image_url( $course['hero_mobile_image_id'], 'large' ) ); ?>">
				<?php echo wp_get_attachment_image( $hero, 'fmh-course-hero', false, array( 'alt' => 'Corso pratico Formalife', 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
			</picture>
		<?php else : ?>
			<?php fmh_render_image( $hero, 'Corso pratico Formalife', '', 'Foto del corso pratico', 'fmh-course-hero', false ); ?>
		<?php endif; ?>
	</div>
</div></section>

<!-- ==================== 2. TEST INTERATTIVO (Scenario A/B) ==================== -->
<section class="fmh-section fmh-test" id="test"><div class="fmh-wrap fmh-narrow">
	<span class="fmh-eyebrow fmh-eyebrow-center">Il test interattivo</span>
	<h2>Sapere che esiste una manovra non significa sapere come usarla.</h2>
	<p>Immagina che stia succedendo adesso. Tuo figlio sta mangiando e improvvisamente qualcosa va storto. Davanti a te possono esserci due situazioni che sembrano simili. Ma richiedono decisioni completamente diverse.</p>

	<div class="fmh-scenarios">
		<details>
			<summary><span>Scenario A</span><strong>Tuo figlio tossisce forte. È spaventato, ma emette suoni e riesce ancora a respirare.</strong><b>Cosa faresti?</b></summary>
			<div>
				<h3>Non iniziare subito le manovre!</h3>
				<p>Una tosse efficace significa che l’aria sta ancora passando e il corpo sta già cercando di espellere l’ostruzione. Intervenire nel momento sbagliato può peggiorare la situazione.</p>
				<strong>Ed è proprio qui che riconoscere viene prima di agire.</strong>
			</div>
		</details>
		<details>
			<summary><span>Scenario B</span><strong>Tuo figlio non riesce più a parlare o piangere normalmente. La tosse diventa inefficace e non riesce a respirare bene.</strong><b>Cosa faresti?</b></summary>
			<div>
				<h3>Adesso la situazione è diversa.</h3>
				<p>Quando la tosse non è più efficace e l’aria non passa adeguatamente, devi riconoscere rapidamente cosa sta accadendo e sapere come intervenire.</p>
				<strong>E sapere che esiste una manovra non basta: devi aver imparato come eseguirla.</strong>
			</div>
		</details>
	</div>
	<a href="#metodo" class="fmh-text-link">Scopri cosa imparerai</a>
</div></section>

<!-- ============================== 3. LA LETTERA ============================== -->
<section class="fmh-section fmh-letter"><div class="fmh-wrap"><div class="fmh-letter-paper">
	<span class="fmh-eyebrow fmh-eyebrow-center">Una storia vera</span>
	<h2>«Ma stava tossendo…»</h2>
	<div class="fmh-letter-copy">
		<?php if ( trim( $course['letter_text'] ) ) : ?>
			<?php echo wp_kses_post( wpautop( $course['letter_text'] ) ); ?>
		<?php else : ?>
			<p><em>Caro genitore,</em></p>
			<p>eravamo in vacanza al mare. Era l’ora della merenda e mia figlia stava mangiando un grissino quando ha cominciato a tossire. Tossiva forte.</p>
			<p>Io ho visto mia figlia in difficoltà e ho pensato una cosa soltanto:&nbsp;<strong>sta soffocando. Devo fare qualcosa. Subito.</strong></p>
			<p>Presa dal panico, ho iniziato a scuoterla e a darle colpi sulla schiena. Non sapevo che quella tosse così violenta significava che l’aria stava ancora passando. Poi qualcosa è cambiato. Ha provato a respirare. E non usciva più alcun suono.</p>
			<p>Fortunatamente mio marito era in casa e conosceva le manovre di disostruzione. È intervenuto. Mia figlia si è salvata.</p>
			<p>Dopo continuavo a ripetere:&nbsp;<strong>«Ma stava tossendo…»</strong></p>
			<p>Non avevo bisogno semplicemente di conoscere una manovra. Avevo bisogno di sapere&nbsp;<strong>quando non farla</strong>&nbsp;e quando, invece, non c’era più tempo da perdere.</p>
			<p>Formalife insegna il soffocamento partendo da ciò che viene prima delle mani:&nbsp;<strong>la tua capacità di capire cosa sta realmente accadendo.</strong></p>
		<?php endif; ?>
	</div>
</div></div></section>

<!-- ============================== 4. IL METODO ============================== -->
<section class="fmh-section fmh-method" id="metodo"><div class="fmh-wrap">
	<span class="fmh-eyebrow">Il metodo</span>
	<h2>Imparare le manovre è solo l’ultimo passaggio.</h2>
	<p>Una buona preparazione non comincia quando tuo figlio sta già soffocando. Comincia molto prima.</p>

	<ol class="fmh-timeline">
		<?php foreach ( $timeline as $i => $step ) : ?>
			<li>
				<?php fmh_render_image( $course['timeline_image_ids'][ $i ], $step[0], '', $step[0], 'fmh-course-card' ); ?>
				<div>
					<span><?php echo ( $i + 1 ) . ' · ' . esc_html( $step[0] ); ?></span>
					<h3><?php echo esc_html( $step[1] ); ?></h3>
					<p><?php echo esc_html( $step[2] ); ?></p>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
	<p class="fmh-mantra"><strong>Prevenire → Riconoscere → Decidere → Agire.</strong>Le manovre sono fondamentali. Ma arrivano alla fine di una sequenza che devi conoscere per intero.</p>
</div></section>

<!-- ==================== 5. QUELLO CHE NON TI PROMETTIAMO ==================== -->
<section class="fmh-section fmh-admission"><div class="fmh-wrap fmh-narrow">
	<h2>Ti diciamo anche ciò che il nostro corso non può fare.</h2>
	<?php if ( trim( $course['admission_text'] ) ) : ?>
		<?php echo wp_kses_post( wpautop( $course['admission_text'] ) ); ?>
	<?php else : ?>
		<p>Non può garantirti che non avrai mai paura. Non può prometterti che un episodio di soffocamento non accadrà mai. Non può trasformarti in un professionista sanitario in tre ore e mezza. E nessun corso serio dovrebbe promettertelo.</p>
		<h3>Può però fare qualcosa di molto concreto.</h3>
		<div class="fmh-admission-yes">
			<p>Può aiutarti a ridurre molti dei rischi evitabili di soffocamento grazie a semplici interventi quotidiani. Può prepararti mentalmente ad agire in caso di emergenza. Può insegnarti a riconoscere la situazione in cui ti trovi. E può permetterti di costruire quella memoria muscolare che nasce quando le manovre non le hai soltanto viste:&nbsp;<strong>le hai provate davvero.</strong></p>
		</div>
	<?php endif; ?>
	<a href="#sessioni" class="fmh-btn-primary">Voglio prepararmi</a>
</div></section>

<!-- ============================== 6. LA PRATICA ============================== -->
<section class="fmh-section fmh-practice"><div class="fmh-wrap">
	<span class="fmh-eyebrow fmh-eyebrow-center">La pratica</span>
	<h2>Le tue mani non possono improvvisare.</h2>
	<p>Un video può mostrarti un movimento. Un libro può spiegartelo. Ma c’è una differenza tra capire una manovra e averla già eseguita.</p>

	<?php if ( $course['practice_image_id'] || $course['practice_secondary_image_id'] ) : ?>
		<div class="fmh-practice-media">
			<?php fmh_render_image( $course['practice_image_id'], 'Esercitazione pratica sulle manovre', '', 'Esercitazione pratica' ); ?>
			<?php fmh_render_image( $course['practice_secondary_image_id'], 'Correzione guidata durante il corso', '', 'Correzione guidata' ); ?>
		</div>
	<?php endif; ?>

	<div class="fmh-practice-grid">
		<article><b>1</b><h3>Guarda</h3><p>L’istruttore ti mostra il gesto, la posizione e la sequenza.</p></article>
		<article><b>2</b><h3>Prova</h3><p>Sei tu a eseguire le manovre sul manichino. Non osservi soltanto gli altri.</p></article>
		<article><b>3</b><h3>Correggi e ripeti</h3><p>L’istruttore ti osserva, ti corregge e ti guida finché il movimento non diventa molto più naturale.</p></article>
	</div>

	<div class="fmh-practice-note"><h3>Massimo 12 partecipanti</h3><p>perché oltre un certo numero smette di essere pratica guidata e diventa una conferenza poco utile.</p></div>
</div></section>

<!-- ==================== 7. COSA IMPARERAI (6 card) ==================== -->
<section class="fmh-section fmh-program"><div class="fmh-wrap">
	<h2>Non un elenco di manovre.</h2>
	<h3>Un sistema completo per affrontare il soffocamento pediatrico.</h3>
	<div class="fmh-program-grid">
		<?php foreach ( $program as $i => $item ) : ?>
			<details>
				<summary>
					<?php fmh_render_image( $course['program_image_ids'][ $i ], $item[0], '', $item[0], 'fmh-course-card' ); ?>
					<span><?php echo esc_html( $item[0] ); ?></span>
					<strong><?php echo esc_html( $item[1] ); ?></strong>
				</summary>
				<p><?php echo esc_html( $item[2] ); ?></p>
			</details>
		<?php endforeach; ?>
	</div>
</div></section>

<!-- ============================== 8. CHI SIAMO ============================== -->
<section class="fmh-section fmh-authority"><div class="fmh-wrap">
	<h2>Non ci conosci?</h2>
	<h3>Prima di chiederti di fidarti di noi, ti diciamo chi siamo e da dove arrivano le informazioni.</h3>

	<div class="fmh-bios">
		<article>
			<?php fmh_render_avatar( $course['mafalda_image_id'], 'Mafalda Camposarcone' ); ?>
			<div>
				<h3>Dott.ssa Mafalda Camposarcone</h3>
				<strong>Pediatra · Istruttrice · Direttrice Scientifica Formalife</strong>
				<p><?php echo esc_html( $course['mafalda_bio'] ); ?></p>
			</div>
		</article>
		<article>
			<?php fmh_render_avatar( $course['raffaele_image_id'] ?: $home['raffaele_image_id'], 'Raffaele La Torre' ); ?>
			<div>
				<h3>Raffaele La Torre</h3>
				<strong>Istruttore · Divulgatore scientifico Formalife</strong>
				<p><?php echo esc_html( $course['raffaele_bio'] ); ?></p>
			</div>
		</article>
		<?php
		/* Terza persona: foto e bio vengono dai campi già esistenti del
		   pannello "Formalife Home" (sara_image_id / team_sara_bio), così non
		   serve aggiungere nuovi campi alla dashboard del corso. */
		?>
		<article>
			<?php fmh_render_avatar( $home['sara_image_id'], 'Sara Bertoli' ); ?>
			<div>
				<h3>Sara Bertoli</h3>
				<strong>Istruttrice</strong>
				<p><?php echo esc_html( $home['team_sara_bio'] ); ?></p>
			</div>
		</article>
	</div>

	<div class="fmh-sources">
		<h3>Origine delle informazioni</h3>
		<?php foreach ( preg_split( '/\r?\n/', $course['source_labels'] ) as $source ) : if ( ! trim( $source ) ) continue; ?>
			<span><?php echo esc_html( $source ); ?></span>
		<?php endforeach; ?>
		<p><strong>La scienza cambia. Anche ciò che insegniamo deve poter cambiare con lei.</strong></p>
	</div>
</div></section>

<!-- ==================== 9. IL LIBRO — SUPERBONUS ==================== -->
<section class="fmh-section fmh-book"><div class="fmh-wrap"><div class="fmh-course-book-card">
	<div><?php fmh_render_image( $book, 'La Guida Anti-Panico al Soffocamento Pediatrico', '', 'Copertina del libro' ); ?></div>
	<div>
		<span class="fmh-eyebrow">Superbonus incluso</span>
		<h2>Tre ore e mezza servono per allenarti. Il libro serve per approfondire la teoria e mantenerti pronto.</h2>
		<?php if ( trim( $course['book_copy'] ) ) : ?>
			<?php echo wp_kses_post( wpautop( $course['book_copy'] ) ); ?>
		<?php else : ?>
			<p>Il corso finisce alle 19:00. La tua preparazione no. Per questo non torni a casa soltanto con ciò che ricordi della giornata: ricevi anche il nuovo libro Formalife sul soffocamento pediatrico, un vero manuale da tenere a casa e riprendere nel tempo.</p>
		<?php endif; ?>
		<ul class="fmh-book-list"><li>Preparazione mentale</li><li>Prevenzione</li><li>Riconoscimento</li><li>Illustrazioni delle manovre</li><li>Checklist</li><li>Situazioni reali</li></ul>
		<div class="fmh-course-book-price-row">
			<strong>Prezzo di vendita <?php echo esc_html( $course['book_value'] ); ?></strong>
			<span>Per chi partecipa al corso è incluso: una copia per ogni partecipante. Scoprirai fin da subito quanto è prezioso, dalla prima all'ultima pagina!</span>
		</div>
	</div>
</div></div></section>

<!-- ==================== 10. OFFERTA, DATE E CHECKOUT ==================== -->
<section class="fmh-section fmh-sessions" id="sessioni"><div class="fmh-wrap">
	<h2>Scegli quando vuoi diventare più preparato.</h2>

	<div class="fmh-session-grid">
		<?php foreach ( $sessions as $session ) :
			$available = FMH_Course_Orders::get_available_seats( $session['id'] );
			$single    = fmh_course_get_price_cents( $session, 1 );
			?>
			<article class="fmh-session-card<?php echo ! $available ? ' is-sold-out' : ''; ?>">
				<h3><?php echo esc_html( fmh_course_format_date( $session['date'] ) ); ?></h3>
				<p><?php echo esc_html( $session['city'] . ' · ' . $session['time'] . ' · massimo ' . $session['capacity'] . ' partecipanti' ); ?></p>
				<?php
				// La sede si mostra solo se è stata davvero scelta in bacheca:
				// "Sede da confermare" è il valore segnaposto predefinito.
				$venue = trim( $session['venue'] );
				if ( '' !== $venue && 'Sede da confermare' !== $venue ) :
					?>
					<p><?php echo esc_html( $venue ); ?></p>
				<?php endif; ?>
				<?php if ( $session['note'] ) : ?><p><?php echo esc_html( $session['note'] ); ?></p><?php endif; ?>
				<div class="fmh-session-prices">
					<span class="fmh-session-price-couple"><?php echo esc_html( $eur0( fmh_course_get_price_cents( $session, 2 ) ) ); ?>€ coppia</span>
					<span class="fmh-session-price-single"><?php echo esc_html( $eur0( $single ) ); ?>€ singolo</span>
				</div>
				<p class="fmh-session-bonus">Nuovo Libro sul Soffocamento incluso!</p>
				<span class="fmh-availability"><?php echo $available ? esc_html( $available ) . ' posti disponibili' : 'Data al completo'; ?></span>
				<button class="fmh-btn-primary fmh-course-open-checkout" data-session="<?php echo esc_attr( $session['id'] ); ?>" <?php disabled( ! $ready || ! $session['sales_open'] || ! $available ); ?>><?php echo $available ? 'Scelgo questa data' : 'Data al completo'; ?></button>
			</article>
		<?php endforeach; ?>
	</div>

	<div class="fmh-couple-offer">
		<span><?php echo esc_html( $course['couple_badge'] ); ?></span>
		<h3><?php echo esc_html( $course['couple_headline'] ); ?></h3>

		<div class="fmh-couple-compare">
			<div class="fmh-couple-tile">
				<span class="fmh-couple-tile-label">Un partecipante</span>
				<span class="fmh-couple-tile-price"><?php echo esc_html( $single_0 ); ?>€</span>
			</div>
			<span class="fmh-couple-vs" aria-hidden="true">→</span>
			<div class="fmh-couple-tile fmh-couple-tile--highlight">
				<span class="fmh-couple-tile-label">Due insieme</span>
				<span class="fmh-couple-tile-price"><?php echo esc_html( $couple_0 ); ?>€ <s><?php echo esc_html( $double_0 ); ?>€</s></span>
				<span class="fmh-couple-tile-save">Risparmi <?php echo esc_html( $eur0( ( $course['single_price_cents'] * 2 ) - $course['couple_price_cents'] ) ); ?>€</span>
			</div>
		</div>

		<p><?php echo esc_html( $course['couple_copy'] ); ?></p>
	</div>

	<?php if ( trim( $course['venue_note'] ) ) : ?><p class="fmh-venue-note"><?php echo esc_html( $course['venue_note'] ); ?></p><?php endif; ?>
	<?php if ( ! $ready ) : ?><p class="fmh-readiness-note">Le iscrizioni online non sono ancora aperte.</p><?php endif; ?>
</div></section>

<!-- ============================== 12. GARANZIA SERENITÀ ============================== -->
<?php if ( $course['guarantee_enabled'] ) : ?>
<section class="fmh-section fmh-guarantee"><div class="fmh-wrap fmh-narrow">
	<span>Garanzia commerciale Formalife</span>
	<h2>Prenotare oggi non deve significare perdere libertà domani.</h2>
	<p><?php echo esc_html( $course['guarantee_text'] ); ?></p>
	<div class="fmh-guarantee-grid">
		<article><h3>Cambio data gratuito</h3><p>Fino a 24 ore prima dell’inizio del corso, senza penale.</p></article>
		<article><h3>Rimborso integrale</h3><p>Fino a 7 giorni prima della data prenotata.</p></article>
		<article><h3>Meno di una settimana</h3><p>Non perdi il valore pagato: diventa credito Formalife per una successiva edizione, valido 12 mesi.</p></article>
		<article><h3>Corso annullato da Formalife</h3><p>Scegli tu: trasferimento gratuito a un’altra data, oppure rimborso integrale.</p></article>
	</div>
	<p>La Garanzia Serenità è una garanzia commerciale configurabile e non sostituisce né limita i diritti legali inderogabili applicabili.</p>
</div></section>
<?php endif; ?>

<!-- ============================== 13. FAQ ============================== -->
<section class="fmh-section fmh-faq" id="faq"><div class="fmh-wrap fmh-narrow">
	<h2>Domande frequenti</h2>
	<div class="fmh-course-faq-list">
		<?php foreach ( $course['faqs'] as $i => $faq ) : ?>
			<details class="fmh-course-faq-item"><summary><span class="fmh-course-faq-number"><?php echo esc_html( $i + 1 ); ?></span><span class="fmh-course-faq-q"><?php echo esc_html( $faq['question'] ); ?></span></summary><p><?php echo esc_html( $faq['answer'] ); ?></p></details>
		<?php endforeach; ?>
	</div>
</div></section>

<!-- ============================== 14. CHIUSURA ============================== -->
<section class="fmh-section fmh-final"><div class="fmh-wrap fmh-narrow">
	<h2><?php echo esc_html( $course['final_headline'] ); ?></h2>
	<p><?php echo esc_html( $course['final_text'] ); ?></p>
	<h3><?php echo esc_html( $course['final_kicker'] ); ?></h3>
	<div class="fmh-final-list"><p><?php echo esc_html( $course['final_details'] ); ?></p></div>
	<p class="fmh-final-offer"><strong><?php echo esc_html( $couple_0 ); ?> € in coppia, <?php echo esc_html( $single_0 ); ?>€ singolo</strong>Nuovo Libro sul Soffocamento incluso!</p>
	<a class="fmh-btn-primary" href="#sessioni"><?php echo esc_html( $course['final_cta_label'] ); ?></a>
	<small>Pagamento sicuro · Cambio data gratuito · Garanzia Serenità Formalife</small>
</div></section>

</main>

<?php /* Nessun attributo "hidden": la barra resta fuori schermo via CSS
     (transform) e scorre dentro quando course.js aggiunge .is-visible.
     Con "hidden" il display:none vinceva sempre e la CTA non compariva mai. */ ?>
<div class="fmh-mobile-cta"><a href="#sessioni">Scegli la data</a></div>

<div class="fmh-course-modal" id="fmh-course-checkout-overlay" hidden>
	<div class="fmh-course-modal-card" role="dialog" aria-modal="true" aria-labelledby="fmh-checkout-title">
		<button type="button" class="fmh-modal-close" data-fmh-course-close aria-label="Chiudi">×</button>
		<div id="fmh-course-checkout-details">
			<h2 id="fmh-checkout-title">Prenota il tuo posto</h2>
			<form id="fmh-course-order-form">
				<label>Sessione
					<select id="fmh-course-session" name="session" required>
						<?php foreach ( $sessions as $session ) : if ( ! $session['sales_open'] ) continue; ?>
							<option value="<?php echo esc_attr( $session['id'] ); ?>"><?php echo esc_html( fmh_course_format_date( $session['date'] ) . ' · ' . $session['city'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<div class="fmh-form-grid">
					<label>Nome<input name="nome" required autocomplete="given-name"></label>
					<label>Cognome<input name="cognome" required autocomplete="family-name"></label>
					<label>Email<input type="email" name="email" required autocomplete="email"></label>
					<label>Telefono<input type="tel" name="telefono" required autocomplete="tel"></label>
				</div>
				<?php /* Dati necessari all'emissione della fattura. */ ?>
				<label>Codice fiscale<input name="codice_fiscale" required autocomplete="off" spellcheck="false" maxlength="16" placeholder="RSSMRA80A01H501U"></label>
				<div class="fmh-form-grid">
					<label>Indirizzo di residenza<input name="indirizzo" required autocomplete="street-address" placeholder="Via e numero civico"></label>
					<label>Città<input name="citta" required autocomplete="address-level2"></label>
				</div>
				<label class="fmh-pair-option"><input type="checkbox" id="fmh-course-couple"> <?php echo esc_html( $course['couple_label'] ); ?></label>
				<p id="fmh-course-one-seat" hidden>Resta un solo posto per questa data: l’opzione coppia non è disponibile.</p>
				<div id="fmh-course-second-person" hidden>
					<div class="fmh-form-grid">
						<label>Nome seconda persona<input id="fmh-second-nome" name="second_nome"></label>
						<label>Cognome seconda persona<input id="fmh-second-cognome" name="second_cognome"></label>
					</div>
				</div>
				<input type="hidden" id="fmh-party-size" name="party_size" value="1">
				<?php if ( ! empty( $course['discount_enabled'] ) ) : ?>
					<label><input type="checkbox" id="fmh-course-has-code"> <?php echo esc_html( $course['discount_label'] ); ?></label>
					<div id="fmh-course-code-field" hidden>
						<label>Codice<input id="fmh-course-discount-code" name="discount_code" autocomplete="off" spellcheck="false" placeholder="Inserisci il codice ricevuto"></label>
						<p id="fmh-course-code-message" role="status" aria-live="polite"></p>
					</div>
				<?php endif; ?>
				<label><input type="checkbox" id="fmh-course-invoice"> Richiedo fattura</label>
				<div id="fmh-course-invoice-fields" hidden>
					<label>Intestatario<input name="invoice_holder"></label>
					<label>Indirizzo<input name="billing_address"></label>
					<label>Codice fiscale / P.IVA<input name="vat_number"></label>
					<label>Codice destinatario / PEC<input name="recipient_code_or_pec"></label>
				</div>
				<label><input type="checkbox" id="fmh-course-privacy" required> Accetto la <a href="<?php echo esc_url( $privacy ); ?>" target="_blank" rel="noopener">Privacy Policy</a></label>
				<label><input type="checkbox" id="fmh-course-terms" required> Accetto le <a href="<?php echo esc_url( $terms ); ?>" target="_blank" rel="noopener">Condizioni di vendita</a></label>
				<div class="fmh-checkout-summary">
					<span class="fmh-summary-label">Riepilogo</span>
					<span id="fmh-summary-session"></span>
					<span>Partecipanti:&nbsp;<b id="fmh-summary-party">1</b></span>
					<strong>Totale:&nbsp;<span id="fmh-course-total"><?php echo esc_html( fmh_course_format_price( $course['single_price_cents'] ) ); ?></span></strong>
				</div>
				<p id="fmh-course-form-message" role="alert"></p>
				<button type="submit" class="fmh-btn-primary">Procedi al pagamento sicuro</button>
			</form>
		</div>
		<div id="fmh-course-checkout-payment" hidden>
			<h2>Pagamento sicuro</h2>
			<div id="fmh-course-payment-element"></div>
			<p id="fmh-course-payment-message" role="alert"></p>
			<button id="fmh-course-pay-button" class="fmh-btn-primary" disabled>Paga ora</button>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
