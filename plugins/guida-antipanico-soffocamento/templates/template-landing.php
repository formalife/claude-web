<?php
/**
 * Template della landing page pubblica del libro (v3.1).
 * Caricato via template_include (vedi GAPS_Page_Manager), bypassa
 * volutamente header/footer del tema per restare un funnel isolato,
 * mantenendo comunque wp_head()/wp_footer() per compatibilità con
 * plugin di analytics/SEO.
 *
 * Nota su "&nbsp;": ovunque il copy richieda uno spazio subito prima o dopo
 * un tratto in <strong>/<em>, viene usato uno spazio non discendente
 * (&nbsp;) incorporato direttamente nella stringa tradotta, invece di
 * affidarsi a uno spazio letterale tra tag PHP separati.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = gaps_get_settings();

$price         = $settings['price'];
$shipping_price = $settings['shipping_price'];
$date_closing  = $settings['date_closing'];
$date_delivery = $settings['date_delivery'];

// Anteprima libro (sezione "Prova"): fino a 3 immagini per la gallery del
// lightbox, come prima. L'attachment ID del primo slot valorizzato resta
// disponibile come fallback "legacy" per le nuove varianti desktop/mobile
// responsive (vedi gaps_resolve_book_preview_ids()), per compatibilità con
// le landing pubblicate prima dell'introduzione di questi due campi.
$preview_ids  = array();
$preview_urls = array();
foreach ( array( 'preview_image_1_id', 'preview_image_2_id', 'preview_image_3_id' ) as $preview_key ) {
	$attachment_id = absint( $settings[ $preview_key ] );
	if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
		continue;
	}
	$url = gaps_get_image_url( $attachment_id, 'full' );
	if ( $url ) {
		$preview_ids[]  = $attachment_id;
		$preview_urls[] = $url;
	}
}

$legacy_preview_id = ! empty( $preview_ids ) ? $preview_ids[0] : 0;
$book_preview_ids  = gaps_resolve_book_preview_ids( $settings, $legacy_preview_id );

// Se non sono stati caricati i 3 slot "legacy" ma esiste almeno una delle
// nuove varianti desktop/mobile, usala anche come unica immagine della
// gallery del lightbox: il pulsante "Sfoglia un'anteprima" resta comunque
// utile, invece di sparire del tutto.
if ( empty( $preview_urls ) && $book_preview_ids['desktop'] ) {
	$fallback_preview_url = gaps_get_image_url( $book_preview_ids['desktop'], 'full' );
	if ( $fallback_preview_url ) {
		$preview_urls[] = $fallback_preview_url;
	}
}

$has_preview = ! empty( $preview_urls );

$contact_email     = $settings['contact_email'];
$contact_whatsapp  = gaps_build_whatsapp_url( $settings['contact_whatsapp'] );
$contact_instagram = gaps_build_instagram_url( $settings['contact_instagram'] );
$has_contacts      = $contact_email || $contact_whatsapp || $contact_instagram || $settings['formalife_logo_id'];

$key_image_id = absint( $settings['mechanism_key_image_id'] );

// Condizioni di vendita e Privacy: usano di default le pagine generate da
// questo stesso plugin (vedi GAPS_Page_Manager), con possibilità di
// sovrascrittura manuale dal pannello impostazioni.
$terms_url   = gaps_get_terms_url();
$privacy_url = gaps_get_privacy_url();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc_html( get_the_title() ? get_the_title() : 'La Guida Anti-Panico al Soffocamento Pediatrico' ); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'gaps-landing-body' ); ?>>

<div class="gaps-topbar">
	<?php
	echo wp_kses(
		__( 'PER&nbsp;<strong>GENITORI</strong>&nbsp;DI BAMBINI DA 0 A 6 ANNI', 'guida-antipanico-soffocamento' ),
		array( 'strong' => array() )
	);
	?>
</div>

<div class="gaps-landing">

<!-- ===== 1. HERO ===== -->
<section class="gaps-hero">
	<div class="gaps-wrap gaps-hero-grid">
		<div>
			<h1>
				<?php esc_html_e( "C'è una differenza tra una tosse che salva e un silenzio che uccide.", 'guida-antipanico-soffocamento' ); ?>
				<span class="gaps-accent-line"><?php esc_html_e( 'E quasi nessun genitore sa qual è.', 'guida-antipanico-soffocamento' ); ?></span>
			</h1>
			<p class="gaps-hero-sub"><?php esc_html_e( "Una pediatra l'ha scritta in un libro, verificato riga per riga in base alle linee guida internazionali più recenti (ERC 2025) — perché la prossima volta che un genitore si troverà davanti a quella differenza, la riconosca subito, non un attimo dopo.", 'guida-antipanico-soffocamento' ); ?></p>
			<div class="gaps-hero-cta-wrap">
				<?php
				gaps_render_cta_button(
					sprintf(
						/* translators: %s: prezzo del libro */
						esc_html__( 'Acquista ora — %s', 'guida-antipanico-soffocamento' ),
						esc_html( $price )
					) . ' <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>'
				);
				?>
			</div>
			<span class="gaps-hero-badge">
				✅
				<?php esc_html_e( 'Disponibile ora', 'guida-antipanico-soffocamento' ); ?>
			</span>
		</div>
		<div class="gaps-hero-cover">
			<?php
			// Unica immagine con priorità di caricamento esplicitamente alta
			// (candidata LCP): niente lazy loading, presente nell'HTML
			// iniziale, mai altre immagini della pagina con fetchpriority
			// "high" (vedi gaps_render_hero_cover_image()).
			gaps_render_hero_cover_image(
				$settings['cover_image_id'],
				__( 'Copertina del libro La Guida Anti-Panico al Soffocamento Pediatrico', 'guida-antipanico-soffocamento' )
			);
			?>
		</div>
	</div>
</section>

<div class="gaps-wave-divider" aria-hidden="true">
	<svg viewBox="0 0 1200 60" preserveAspectRatio="none"><path d="M0,30 C150,70 350,-10 600,30 C850,70 1050,-10 1200,30 L1200,60 L0,60 Z" fill="#FFFFFF"></path></svg>
</div>

<!-- ===== 2. LA STORIA ===== -->
<section class="gaps-story-section gaps-paper-texture gaps-paper-edges">
	<div class="gaps-story-wrap">
		<span class="gaps-eyebrow"><?php esc_html_e( 'Una storia vera', 'guida-antipanico-soffocamento' ); ?></span>
		<p><?php esc_html_e( "Eravamo in vacanza al mare. Era l'ora della merenda.", 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Mia figlia stava mangiando un pezzo di grissino. A un certo punto ha iniziato a tossire.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( "Tossiva forte. Era spaventata, ma l'aria passava ancora.", 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Presa dal panico, ho pensato che stesse soffocando. Non sapevo che quella tosse — per quanto violenta, per quanto spaventosa da guardare — era il modo in cui il corpo di mia figlia stava già facendo il proprio lavoro: espellere da solo il corpo estraneo.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'Ho iniziato a scuoterla e a darle colpi tra le scapole.', 'guida-antipanico-soffocamento' ); ?></p>
		<strong class="gaps-story-emphasis"><?php esc_html_e( 'Poi ha cercato di respirare... Ma non usciva più alcun suono.', 'guida-antipanico-soffocamento' ); ?></strong>
		<p><?php esc_html_e( 'Fortunatamente mio marito era in casa, sotto la doccia. Conosceva bene le manovre di disostruzione avendole imparate anni prima, come volontario della Croce Rossa. Ho urlato ed è subito intervenuto, così nostra figlia si è salvata.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php echo wp_kses( __( 'Dopo questo episodio continuavo a ripetere la stessa frase:&nbsp;<em>"Ma stava tossendo..."</em>', 'guida-antipanico-soffocamento' ), array( 'em' => array() ) ); ?></p>

		<div class="gaps-story-callout">
			<?php
			echo wp_kses(
				__( 'Questa mamma non ha sbagliato per disattenzione, né per mancanza d\'amore. Ha sbagliato perché nessuno le aveva mai insegnato una cosa che sembra poco importante, e che invece è tutto:&nbsp;<strong>una tosse efficace è già una difesa.</strong>', 'guida-antipanico-soffocamento' ),
				array( 'strong' => array() )
			);
			echo wp_kses(
				__( '<strong class="gaps-callout-emphasis">Interromperla con un intervento sbagliato può trasformare un\'ostruzione parziale in una totale.</strong>', 'guida-antipanico-soffocamento' ),
				array( 'strong' => array( 'class' => array() ) )
			);
			?>
		</div>

		<div class="gaps-story-footer-wrap">
			<span class="gaps-story-footer">📖 <?php esc_html_e( 'Storia vera, raccontata per intero, con le parole stesse di quella madre, all\'inizio del libro.', 'guida-antipanico-soffocamento' ); ?></span>
		</div>
	</div>
</section>

<!-- ===== 3. IL PROBLEMA + AGITAZIONE ===== -->
<section class="gaps-problem-section">
	<div class="gaps-wrap">
		<div class="gaps-problem-icons" aria-hidden="true">🤔 💬 ❓</div>
		<h2 class="gaps-reveal"><?php esc_html_e( 'Il problema non è il soffocamento. È non saperlo riconoscere.', 'guida-antipanico-soffocamento' ); ?></h2>
		<div class="gaps-problem-body">
			<p><?php esc_html_e( "Quasi nessun genitore sa distinguere un'ostruzione parziale da una totale.", 'guida-antipanico-soffocamento' ); ?></p>
			<p><?php esc_html_e( 'In quel momento, è la differenza tra aiutare e peggiorare la situazione.', 'guida-antipanico-soffocamento' ); ?></p>
			<p><?php echo wp_kses( __( '<strong>Ma non è colpa tua.</strong>', 'guida-antipanico-soffocamento' ), array( 'strong' => array() ) ); ?></p>
			<p><?php esc_html_e( 'Le informazioni sul soffocamento pediatrico esistono ovunque — video, articoli, corsi — ma sono sparse, spesso contraddittorie, a volte pericolosamente imprecise.', 'guida-antipanico-soffocamento' ); ?></p>
		</div>

		<div class="gaps-contradiction-grid">
			<div class="gaps-contradiction-card gaps-reveal gaps-reveal-1">
				<span class="gaps-contradiction-icon" aria-hidden="true">✗</span>
				<span class="gaps-contradiction-tag"><?php esc_html_e( 'Consiglio online n.1', 'guida-antipanico-soffocamento' ); ?></span>
				<blockquote><?php esc_html_e( '"Colpisci forte tra le scapole"', 'guida-antipanico-soffocamento' ); ?></blockquote>
			</div>
			<div class="gaps-contradiction-card gaps-reveal gaps-reveal-2">
				<span class="gaps-contradiction-icon" aria-hidden="true">✗</span>
				<span class="gaps-contradiction-tag"><?php esc_html_e( 'Consiglio online n.2', 'guida-antipanico-soffocamento' ); ?></span>
				<blockquote><?php esc_html_e( '"Fai la manovra di Heimlich"', 'guida-antipanico-soffocamento' ); ?></blockquote>
			</div>
			<div class="gaps-contradiction-card gaps-reveal gaps-reveal-3">
				<span class="gaps-contradiction-icon" aria-hidden="true">✗</span>
				<span class="gaps-contradiction-tag"><?php esc_html_e( 'Consiglio online n.3', 'guida-antipanico-soffocamento' ); ?></span>
				<blockquote><?php esc_html_e( '"Devi intervenire subito', 'guida-antipanico-soffocamento' ); ?></blockquote>
			</div>
		</div>
		<p class="gaps-contradiction-caption"><?php esc_html_e( 'Tre fonti diverse trovate online. Tre risposte diverse. Nessuna ti dice quando si applica.', 'guida-antipanico-soffocamento' ); ?></p>

		<p class="gaps-problem-close gaps-reveal"><?php esc_html_e( 'Su un tema dove un\'informazione sbagliata può trasformarsi in un errore fatale, non basta "informarsi". Serve una fonte sola, verificata.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>
</section>

<!-- ===== 4. CHI HA SCRITTO IL LIBRO ===== -->
<section class="gaps-author-section">
	<div class="gaps-wrap gaps-author-grid">
		<div class="gaps-author-photo-wrap gaps-reveal">
			<?php
			gaps_render_image(
				$settings['camposarcone_image_id'],
				__( 'Dott.ssa Mafalda Camposarcone, Pediatra e Direttrice Scientifica', 'guida-antipanico-soffocamento' ),
				'',
				__( 'Foto Dott.ssa Camposarcone', 'guida-antipanico-soffocamento' )
			);
			?>
			<div class="gaps-author-quote">
				<?php esc_html_e( '"Ho scritto e verificato ogni contenuto con la stessa serietà, professionalità e cura con cui visito un mio piccolo paziente."', 'guida-antipanico-soffocamento' ); ?>
				<cite>&mdash; <?php esc_html_e( 'Dott.ssa Mafalda Camposarcone, Pediatra e Direttrice Scientifica', 'guida-antipanico-soffocamento' ); ?></cite>
			</div>
		</div>
		<div class="gaps-author-text gaps-reveal gaps-reveal-2">
			<h2><?php esc_html_e( 'La domanda che ti stai facendo, una pediatra l\'ha già risolta', 'guida-antipanico-soffocamento' ); ?></h2>
			<p><?php echo wp_kses( __( 'Probabilmente ti stai chiedendo:&nbsp;<em>e io, lo saprei riconoscere quel momento?</em>', 'guida-antipanico-soffocamento' ), array( 'em' => array() ) ); ?></p>
			<p><?php echo wp_kses( __( 'È la domanda giusta. Ed è quella a cui Mafalda Camposarcone ha deciso di rispondere in un libro, dopo trent\'anni come pediatra di famiglia — la persona che, sul territorio, riceve ogni giorno le paure e le domande dei genitori. In trent\'anni ha visto ripetersi sempre lo stesso schema: non genitori distratti, ma genitori che semplicemente non avevano mai avuto occasione di imparare&nbsp;<em>prima</em>&nbsp;la differenza tra un\'ostruzione che si risolve da sola e una che richiede un intervento immediato.', 'guida-antipanico-soffocamento' ), array( 'em' => array() ) ); ?></p>
			<p>
				<?php
				echo wp_kses(
					__( 'Così ha scritto, insieme a Raffaele La Torre — CEO e istruttore di Formalife —&nbsp;<span class="gaps-book-title">La Guida Anti-Panico al Soffocamento Pediatrico</span>.', 'guida-antipanico-soffocamento' ),
					array( 'span' => array( 'class' => array() ) )
				);
				?>
			</p>

			<div class="gaps-trust-row">
				<span><?php esc_html_e( '✓ Verificato su linee guida ERC 2025', 'guida-antipanico-soffocamento' ); ?></span>
				<span><?php esc_html_e( '✓ Verificato su linee guida IRC', 'guida-antipanico-soffocamento' ); ?></span>
				<span><?php esc_html_e( '✓ Bibliografia scientifica completa', 'guida-antipanico-soffocamento' ); ?></span>
			</div>
		</div>
	</div>
</section>

<!-- ===== 5. IL SISTEMA ===== -->
<section class="gaps-mechanism-section">
	<div class="gaps-wrap">
		<h2 class="gaps-reveal"><?php esc_html_e( 'Il libro parte da dove serve davvero: dalla tua testa, non dalle manovre', 'guida-antipanico-soffocamento' ); ?></h2>
		<p class="gaps-mechanism-intro"><?php esc_html_e( 'Il primo capitolo spiega cosa fa il tuo cervello sotto stress — perché ti blocchi, perché il panico prende il controllo prima che tu possa pensare, e come si costruisce la capacità di restare lucido anche durante un\'emergenza.', 'guida-antipanico-soffocamento' ); ?></p>

		<div class="gaps-key-box gaps-reveal <?php echo $key_image_id ? '' : 'gaps-key-box--no-media'; ?>">
			<?php if ( $key_image_id ) : ?>
				<div class="gaps-key-media">
					<?php echo gaps_render_attachment_image( $key_image_id, 'medium_large', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- già escapato da wp_get_attachment_image(). ?>
				</div>
			<?php endif; ?>
			<div class="gaps-key-body">
				<h3><?php echo wp_kses( __( 'Riconoscere un\'ostruzione parziale — e sapere che, in quel caso, la cosa più giusta e più difficile è&nbsp;<em>non</em>&nbsp;intervenire.', 'guida-antipanico-soffocamento' ), array( 'em' => array() ) ); ?></h3>
				<p><?php esc_html_e( 'È la differenza esatta che a quella madre nessuno aveva mai spiegato. Il libro te la presenta con chiarezza, prima ancora di insegnarti le manovre.', 'guida-antipanico-soffocamento' ); ?></p>
			</div>
		</div>

		<div class="gaps-mech-grid">
			<div class="gaps-mech-card gaps-reveal gaps-reveal-1">
				<span class="gaps-icon-badge" aria-hidden="true">🍽️</span>
				<h4><?php esc_html_e( 'Prevenzione', 'guida-antipanico-soffocamento' ); ?></h4>
				<p><?php esc_html_e( 'Sicurezza a tavola, tagli sicuri per età, alimenti a rischio, autosvezzamento, falsi miti.', 'guida-antipanico-soffocamento' ); ?></p>
			</div>
			<div class="gaps-mech-card gaps-reveal gaps-reveal-2">
				<span class="gaps-icon-badge" aria-hidden="true">🏠</span>
				<h4><?php esc_html_e( 'Ambiente domestico', 'guida-antipanico-soffocamento' ); ?></h4>
				<p><?php esc_html_e( 'Pile a bottone, giocattoli, palloncini, il test dei 30 secondi per ogni oggetto.', 'guida-antipanico-soffocamento' ); ?></p>
			</div>
			<div class="gaps-mech-card gaps-reveal gaps-reveal-3">
				<span class="gaps-icon-badge" aria-hidden="true">🤲</span>
				<h4><?php esc_html_e( 'Manovre corrette', 'guida-antipanico-soffocamento' ); ?></h4>
				<p><?php esc_html_e( 'Per ostruzione totale — distinte per lattante e bambino, con i 10 errori più comuni.', 'guida-antipanico-soffocamento' ); ?></p>
			</div>
			<div class="gaps-mech-card gaps-reveal gaps-reveal-4">
				<span class="gaps-icon-badge" aria-hidden="true">⚠️</span>
				<h4><?php esc_html_e( 'Scenario peggiore', 'guida-antipanico-soffocamento' ); ?></h4>
				<p><?php esc_html_e( 'Perdita di coscienza, quando serve la rianimazione, quando serve anche un corso BLSD.', 'guida-antipanico-soffocamento' ); ?></p>
			</div>
			<div class="gaps-mech-card gaps-reveal gaps-reveal-5">
				<span class="gaps-icon-badge" aria-hidden="true">🌍</span>
				<h4><?php esc_html_e( 'Vita reale', 'guida-antipanico-soffocamento' ); ?></h4>
				<p><?php esc_html_e( 'Casi veri: da solo, in due, al ristorante, in auto, se è solo con nonni o la babysitter.', 'guida-antipanico-soffocamento' ); ?></p>
			</div>
		</div>

		<p class="gaps-mechanism-close">
			<?php esc_html_e( 'La prevenzione viene prima dell\'emergenza.', 'guida-antipanico-soffocamento' ); ?><br>
			<?php esc_html_e( 'Il riconoscimento viene prima dell\'intervento.', 'guida-antipanico-soffocamento' ); ?><br>
			<?php esc_html_e( 'La teoria viene prima della tecnica.', 'guida-antipanico-soffocamento' ); ?><br>
			<?php esc_html_e( 'Non è un caso: è così che il cervello impara davvero.', 'guida-antipanico-soffocamento' ); ?>
		</p>
	</div>
</section>

<!-- ===== 6. PROVA ===== -->
<section class="gaps-proof-section">
	<div class="gaps-wrap">
		<h2><?php esc_html_e( 'Non ti chiediamo di crederci sulla parola', 'guida-antipanico-soffocamento' ); ?></h2>

		<div class="gaps-book-page gaps-reveal">
			<p><?php esc_html_e( '"In un\'emergenza ogni istinto ti dice di fare qualcosa, subito. Agitarsi, correre, urlare, toccare. Ma c\'è una differenza cruciale tra l\'azione orientata e il movimento caotico: la prima salva, il secondo consuma tempo prezioso.', 'guida-antipanico-soffocamento' ); ?></p>
			<p><?php esc_html_e( 'Devi prenderti due o tre secondi consapevoli prima di agire: respira — un respiro profondo inizia a controbilanciare l\'adrenalina. Stabilisci il contatto con la realtà — dove sei, chi c\'è con te, cosa vedi davanti a te in questo momento. Verifica che tu stesso sia al sicuro.', 'guida-antipanico-soffocamento' ); ?></p>
			<p><?php esc_html_e( 'Non è un dettaglio secondario. È il primo controllo di ogni intervento di soccorso professionale, e ora deve esserlo anche per te."', 'guida-antipanico-soffocamento' ); ?></p>
		</div>
		<p class="gaps-book-caption">
			<?php
			echo wp_kses(
				__( 'Tre parole —&nbsp;<span class="gaps-word-ferma">Ferma</span>,&nbsp;<span class="gaps-word-valuta">Valuta</span>,&nbsp;<span class="gaps-word-agisci">Agisci</span>&nbsp;— pensate per essere l\'unica cosa che il cervello riesce a recuperare quando tutto il resto si spegne per il panico.', 'guida-antipanico-soffocamento' ),
				array( 'span' => array( 'class' => array() ) )
			);
			?>
		</p>

		<?php if ( $has_preview ) : ?>
			<div class="gaps-preview-trigger-wrap">
				<button type="button" id="gaps-preview-main-trigger" class="gaps-preview-visual" data-images="<?php echo esc_attr( wp_json_encode( $preview_urls ) ); ?>">
					<span class="gaps-preview-visual-img"<?php echo gaps_get_book_preview_ratio_style( $book_preview_ids['desktop'], $book_preview_ids['mobile'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- già escapato in gaps_get_book_preview_ratio_style(). ?>>
						<?php if ( $book_preview_ids['desktop'] ) : ?>
							<?php
							gaps_render_book_preview_picture(
								$book_preview_ids['desktop'],
								$book_preview_ids['mobile'],
								__( "Anteprima del libro", 'guida-antipanico-soffocamento' )
							);
							?>
						<?php else : ?>
							<img src="<?php echo esc_url( $preview_urls[0] ); ?>" alt="<?php esc_attr_e( "Anteprima del libro", 'guida-antipanico-soffocamento' ); ?>" />
						<?php endif; ?>
					</span>
					<span class="gaps-preview-visual-label"><?php esc_html_e( "Sfoglia un'anteprima del libro", 'guida-antipanico-soffocamento' ); ?></span>
				</button>
			</div>
		<?php endif; ?>

		<div class="gaps-proof-grid">
			<div class="gaps-proof-card gaps-reveal gaps-reveal-1">
				<span class="gaps-icon-badge" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3v6a5 5 0 0 0 10 0V3"/><path d="M11 12v2a5 5 0 0 0 5 5 5 5 0 0 0 5-5v-1"/><circle cx="20" cy="10" r="2"/></svg>
				</span>
				<h4><?php esc_html_e( 'La voce della pediatra', 'guida-antipanico-soffocamento' ); ?></h4>
				<p><?php esc_html_e( 'La Dott.ssa Camposarcone interviene nel testo con osservazioni cliniche, nei punti più delicati.', 'guida-antipanico-soffocamento' ); ?></p>
			</div>
			<div class="gaps-proof-card gaps-reveal gaps-reveal-2">
				<span class="gaps-icon-badge" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
				</span>
				<h4><?php esc_html_e( 'Numerose illustrazioni', 'guida-antipanico-soffocamento' ); ?></h4>
				<p><?php esc_html_e( 'Disegni chiari, passo dopo passo, pensati per essere capiti al volo — anche quando l\'argomento è più tecnico.', 'guida-antipanico-soffocamento' ); ?></p>
			</div>
			<div class="gaps-proof-card gaps-reveal gaps-reveal-3">
				<span class="gaps-icon-badge" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
				</span>
				<h4><?php esc_html_e( 'Checklist pratiche', 'guida-antipanico-soffocamento' ); ?></h4>
				<p><?php esc_html_e( 'Pensate per essere consultate in trenta secondi: apri solo la sezione che ti serve.', 'guida-antipanico-soffocamento' ); ?></p>
			</div>
		</div>
	</div>
</section>

<!-- ===== 7. DAMAGING ADMISSION ===== -->
<section class="gaps-admission-section">
	<div class="gaps-wrap gaps-admission-wrap">
		<p class="gaps-admission-sub"><?php esc_html_e( 'Un libro non può allenare le tue mani.', 'guida-antipanico-soffocamento' ); ?></p>
		<p><?php esc_html_e( 'E te lo diciamo apertamente: subito, nelle prime pagine, nell\'introduzione. Le manovre di disostruzione si imparano davvero solo provandole, con un istruttore accanto che corregge ogni movimento. Nessun libro — questo compreso — sostituisce quell\'esperienza.', 'guida-antipanico-soffocamento' ); ?></p>
		<p>
			<?php
			echo wp_kses(
				__( '<span class="gaps-book-title">La Guida Anti-Panico al Soffocamento Pediatrico</span>&nbsp;non promette di trasformarti in un soccorritore. Promette di darti ciò che a quella madre, nella storia che hai letto, è mancato: capire cosa succede, riconoscere una tosse efficace, sapere qual è il gesto giusto e quale evitare —&nbsp;<strong>prima</strong>&nbsp;di averne bisogno.', 'guida-antipanico-soffocamento' ),
				array(
					'span'   => array( 'class' => array() ),
					'strong' => array(),
				)
			);
			?>
		</p>
		<p><?php esc_html_e( 'Per questo, alla fine del libro, trovi anche come accedere ai corsi pratici Formalife: un percorso a numero chiuso (massimo 12 partecipanti per sessione), pensato perché ognuno pratichi davvero. Il libro è il primo passo. Il corso è quello che trasforma la comprensione in un gesto che le tue mani ricordano da sole.', 'guida-antipanico-soffocamento' ); ?></p>
		<p class="gaps-admission-final"><?php esc_html_e( 'Non è la lettura più veloce che troverai sull\'argomento. È probabilmente la più curata e basata su fonti scientifiche.', 'guida-antipanico-soffocamento' ); ?></p>
	</div>
</section>

<!-- ===== 8. COSTO DELL'INAZIONE ===== -->
<section class="gaps-cost-section">
	<div class="gaps-wrap">
		<span class="gaps-eyebrow gaps-eyebrow-center"><?php esc_html_e( 'Dati ufficiali', 'guida-antipanico-soffocamento' ); ?></span>
		<h2 class="gaps-reveal"><?php esc_html_e( 'Qual è il costo di non agire?', 'guida-antipanico-soffocamento' ); ?></h2>
		<p class="gaps-cost-intro"><?php esc_html_e( 'Prima di continuare, guarda i numeri con cui ci confrontiamo ogni giorno per capire la portata del problema.', 'guida-antipanico-soffocamento' ); ?></p>
		<div class="gaps-stat-grid">
			<div class="gaps-stat-card gaps-reveal gaps-reveal-1">
				<span class="gaps-stat-icon" aria-hidden="true">💔</span>
				<span class="gaps-stat-number"><?php echo esc_html( $settings['stat1_number'] ); ?></span>
				<span class="gaps-stat-label"><?php echo esc_html( $settings['stat1_label'] ); ?></span>
			</div>
			<div class="gaps-stat-card gaps-reveal gaps-reveal-2">
				<span class="gaps-stat-icon" aria-hidden="true">🏥</span>
				<span class="gaps-stat-number"><?php echo esc_html( $settings['stat2_number'] ); ?></span>
				<span class="gaps-stat-label"><?php echo esc_html( $settings['stat2_label'] ); ?></span>
			</div>
			<div class="gaps-stat-card gaps-reveal gaps-reveal-3">
				<span class="gaps-stat-icon" aria-hidden="true">🍽️</span>
				<span class="gaps-stat-number"><?php echo esc_html( $settings['stat3_number'] ); ?></span>
				<span class="gaps-stat-label"><?php echo esc_html( $settings['stat3_label'] ); ?></span>
			</div>
		</div>
		<p class="gaps-stat-source"><?php echo esc_html( $settings['stat_source'] ); ?></p>

		<div class="gaps-cost-body">
			<p><?php esc_html_e( 'Il soffocamento è probabilmente una di quelle paure che tieni a distanza — qualcosa che succede, certo, ma ad altri. I numeri raccontano un\'altra storia: può succedere durante un pasto qualunque, in una casa normale.', 'guida-antipanico-soffocamento' ); ?></p>
			<p><?php esc_html_e( 'Puoi continuare a sperare che non capiti mai, oppure puoi diventare la persona che, se dovesse capitare, saprebbe cosa fare.', 'guida-antipanico-soffocamento' ); ?></p>
			<p class="gaps-cost-final">
				<?php esc_html_e( 'La preparazione non elimina il rischio.', 'guida-antipanico-soffocamento' ); ?><br>
				<?php esc_html_e( 'Niente può farlo.', 'guida-antipanico-soffocamento' ); ?><br>
				<?php echo wp_kses( __( '<strong>Ma cambia chi sei nel momento che più conta.</strong>', 'guida-antipanico-soffocamento' ), array( 'strong' => array() ) ); ?>
			</p>
		</div>
	</div>
</section>

<!-- ===== 9. L'OFFERTA ===== -->
<section class="gaps-offer-section" id="offerta">
	<div class="gaps-wrap">
		<div class="gaps-offer-card gaps-reveal">
			<span class="gaps-offer-ribbon">🔥 <?php esc_html_e( 'Offerta di lancio', 'guida-antipanico-soffocamento' ); ?></span>

			<div class="gaps-offer-visual">
				<?php
				gaps_render_image(
					$settings['cover_image_id'],
					__( 'Copertina del libro La Guida Anti-Panico al Soffocamento Pediatrico', 'guida-antipanico-soffocamento' ),
					'',
					__( 'Copertina del libro', 'guida-antipanico-soffocamento' )
				);
				?>
			</div>

			<h3><?php esc_html_e( 'La Guida Anti-Panico al Soffocamento Pediatrico', 'guida-antipanico-soffocamento' ); ?></h3>

			<div class="gaps-offer-columns">
				<div class="gaps-offer-details">
					<ul class="gaps-offer-list">
						<li><span class="gaps-offer-icon" aria-hidden="true">📘</span><span><?php echo wp_kses( __( '<strong>Il libro</strong>&nbsp;— 160 pagine, copertina rigida, verificato su linee guida ERC 2025 / IRC.', 'guida-antipanico-soffocamento' ), array( 'strong' => array() ) ); ?></span></li>
						<li><span class="gaps-offer-icon" aria-hidden="true">🗂️</span><span><?php echo wp_kses( __( '<strong>La scheda "I tuoi numeri importanti"</strong>&nbsp;stampabile in omaggio, da dare anche ai nonni o alla babysitter.', 'guida-antipanico-soffocamento' ), array( 'strong' => array() ) ); ?></span></li>
						<li><span class="gaps-offer-icon" aria-hidden="true">🎨</span><span><?php echo wp_kses( __( '<strong>Illustrazioni e checklist</strong>&nbsp;pensate per essere consultate anche quando non hai voglia di leggere, in trenta secondi.', 'guida-antipanico-soffocamento' ), array( 'strong' => array() ) ); ?></span></li>
						<li><span class="gaps-offer-icon" aria-hidden="true">🎟️</span><span><?php echo wp_kses( __( '<strong>Accesso prioritario</strong>&nbsp;alle informazioni sui corsi pratici Formalife, a numero chiuso.', 'guida-antipanico-soffocamento' ), array( 'strong' => array() ) ); ?></span></li>
					</ul>


					<div class="gaps-offer-centered">
						<div class="gaps-price-block">
							<span class="gaps-price-number"><?php echo esc_html( $price ); ?></span>
							<p class="gaps-price-note"><?php esc_html_e( 'Prezzo di lancio — disponibile ora', 'guida-antipanico-soffocamento' ); ?></p>
						</div>

						<div class="gaps-date-badges">
							<span class="gaps-date-badge">✅ <?php esc_html_e( 'Disponibile ora', 'guida-antipanico-soffocamento' ); ?></span>
							<span class="gaps-date-badge">🚚 <?php printf( esc_html__( 'Spedizione %s', 'guida-antipanico-soffocamento' ), esc_html( $date_delivery ) ); ?></span>
						</div>

						<p class="gaps-offer-explain">
							<?php esc_html_e( 'Il libro è disponibile da oggi, al prezzo di lancio.', 'guida-antipanico-soffocamento' ); ?>
						</p>

						<?php gaps_render_cta_button( esc_html__( 'Acquista ora la tua copia →', 'guida-antipanico-soffocamento' ) ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ===== 10. FAQ ===== -->
<section class="gaps-faq-section">
	<div class="gaps-wrap">
		<h2><?php esc_html_e( 'Le domande che probabilmente ti stai facendo', 'guida-antipanico-soffocamento' ); ?></h2>
		<div class="gaps-faq-list">
			<details class="gaps-faq-item gaps-reveal gaps-reveal-1">
				<summary><span class="gaps-faq-number">1</span><span class="gaps-faq-q"><?php esc_html_e( 'E se non sono il tipo che legge libri sulla genitorialità?', 'guida-antipanico-soffocamento' ); ?></span></summary>
				<p><?php esc_html_e( 'Non devi leggerlo come un romanzo, tutto in una sera. È pensato anche come strumento di consultazione: la prima volta lo leggi per intero, per avere una mappa generale. Dopo, lo apri solo quando ti serve — prima dello svezzamento, con un giocattolo nuovo, quando cambi babysitter o affidi tuo figlio ai nonni.', 'guida-antipanico-soffocamento' ); ?></p>
			</details>
			<details class="gaps-faq-item gaps-reveal gaps-reveal-2">
				<summary><span class="gaps-faq-number">2</span><span class="gaps-faq-q"><?php esc_html_e( 'Non si trova già tutto gratis online?', 'guida-antipanico-soffocamento' ); ?></span></summary>
				<p><?php esc_html_e( 'È vero che le informazioni esistono. Il problema è che sono sparse, spesso contraddittorie e — su questo argomento specifico — a volte pericolosamente imprecise. Questo libro è l\'esatto opposto: una fonte sola, verificata riga per riga, firmata da una pediatra che ci mette la faccia.', 'guida-antipanico-soffocamento' ); ?></p>
			</details>
			<details class="gaps-faq-item gaps-reveal gaps-reveal-3">
				<summary><span class="gaps-faq-number">3</span><span class="gaps-faq-q"><?php esc_html_e( 'Un libro può davvero prepararmi per un\'emergenza reale?', 'guida-antipanico-soffocamento' ); ?></span></summary>
				<p><?php esc_html_e( 'Hai ragione a chiedertelo, e te lo abbiamo detto apertamente più sopra in questa pagina. Il libro ti dà la comprensione. Il corso pratico Formalife ti dà l\'allenamento delle mani. Sono due passi distinti e complementari, servono entrambi, e chi ben comincia è a metà dell\'opera.', 'guida-antipanico-soffocamento' ); ?></p>
			</details>
			<details class="gaps-faq-item gaps-reveal gaps-reveal-4">
				<summary><span class="gaps-faq-number">4</span><span class="gaps-faq-q"><?php esc_html_e( 'Non conosco Formalife, chi siete?', 'guida-antipanico-soffocamento' ); ?></span></summary>
				<p><?php esc_html_e( 'Giusto chiederlo: siamo un\'azienda giovane, nata sulla convinzione che sia essenziale diffondere la cultura della sicurezza, soprattutto quella che riguarda i più piccoli. Non abbiamo una lunga storia alle spalle, ma la direzione scientifica di una pediatra che ci mette nome e cognome, fonti verificabili in bibliografia, e una garanzia che ti fa rischiare zero.', 'guida-antipanico-soffocamento' ); ?></p>
			</details>
			<details class="gaps-faq-item gaps-reveal gaps-reveal-5">
				<summary><span class="gaps-faq-number">5</span><span class="gaps-faq-q"><?php esc_html_e( '19,90 € per un libro non sono tanti?', 'guida-antipanico-soffocamento' ); ?></span></summary>
				<p><?php esc_html_e( 'Considera che è il prezzo di una cena in pizzeria, per una persona sola — per un contenuto verificato riga per riga da una pediatra su tutto ciò che riguarda la sicurezza di tuo figlio nei primi anni. Include la scheda numeri d\'emergenza. E se questo non ti basta, hai anche la garanzia qui sotto: tieni il libro, ti rimborsiamo comunque. Noi crediamo che la sicurezza di un bambino valga molto di più di una cena in pizzeria.', 'guida-antipanico-soffocamento' ); ?></p>
			</details>
		</div>
	</div>
</section>

<!-- ===== 11. GARANZIA ===== -->
<section class="gaps-guarantee-section">
	<div class="gaps-wrap">
		<div class="gaps-guarantee-card gaps-reveal">
			<div class="gaps-guarantee-badge">
				<?php if ( $settings['guarantee_image_id'] ) : ?>
					<?php gaps_render_image( $settings['guarantee_image_id'], __( 'Garanzia soddisfatti o rimborsati', 'guida-antipanico-soffocamento' ) ); ?>
				<?php else : ?>
					<span class="gaps-guarantee-fallback-icon" aria-hidden="true">🛡️</span>
				<?php endif; ?>
			</div>
			<h2><?php esc_html_e( 'Una garanzia migliore di un semplice rimborso', 'guida-antipanico-soffocamento' ); ?></h2>
			<p><?php esc_html_e( 'Se ricevi il libro e, per qualsiasi motivo, non ti convince — non ti chiediamo di spiegarci perché, non ti chiediamo di rispedirlo indietro:', 'guida-antipanico-soffocamento' ); ?></p>
			<span class="gaps-guarantee-highlight">
				<?php
				printf(
					/* translators: %s: prezzo del libro */
					esc_html__( 'Tieni comunque il libro, e ti rimborsiamo interamente i %s.', 'guida-antipanico-soffocamento' ),
					esc_html( $price )
				);
				?>
			</span>

			<div class="gaps-guarantee-steps">
				<div class="gaps-guarantee-step">
					<span class="gaps-guarantee-step-number">1</span>
					<p><?php esc_html_e( 'Ricevi il libro a casa, con la scheda numeri d\'emergenza in omaggio.', 'guida-antipanico-soffocamento' ); ?></p>
				</div>
				<div class="gaps-guarantee-step">
					<span class="gaps-guarantee-step-number">2</span>
					<p><?php esc_html_e( 'Non ti convince? Scrivici, senza bisogno di spiegazioni o di rispedirlo.', 'guida-antipanico-soffocamento' ); ?></p>
				</div>
				<div class="gaps-guarantee-step">
					<span class="gaps-guarantee-step-number">3</span>
					<p><?php esc_html_e( 'Rimborso pieno e 20% di sconto su un corso pratico Formalife.', 'guida-antipanico-soffocamento' ); ?></p>
				</div>
			</div>

			<p class="gaps-guarantee-final">
				<?php
				echo wp_kses(
					__( 'Nella peggiore delle ipotesi, avresti comunque il libro gratis e uno sconto reale su un corso che ti serve davvero. Non rischi nulla ad acquistare —&nbsp;<strong>siamo noi a rischiare, non tu.</strong>', 'guida-antipanico-soffocamento' ),
					array( 'strong' => array() )
				);
				?>
			</p>
		</div>
	</div>
</section>

<!-- ===== 12. URGENZA + CTA FINALE + P.S. ===== -->
<section class="gaps-final-section" id="final-cta">
	<?php
	gaps_render_image( $settings['final_floating_image_1_id'], '', 'gaps-final-floating gaps-final-floating-1' );
	gaps_render_image( $settings['final_floating_image_2_id'], '', 'gaps-final-floating gaps-final-floating-2' );
	gaps_render_image( $settings['final_floating_image_3_id'], '', 'gaps-final-floating gaps-final-floating-3' );
	?>
	<div class="gaps-wrap">
		<h2><?php esc_html_e( 'Tra sei mesi, a un compleanno o a una grigliata tra amici: chi vuoi essere?', 'guida-antipanico-soffocamento' ); ?></h2>
		<p class="gaps-final-body"><?php esc_html_e( 'Il soffocamento non manda un preavviso. Arriva durante un pranzo qualunque, con le persone che ami. In quel momento non conterà quanto ci hai pensato prima — conterà solo se, in quei pochi secondi, saprai riconoscere cosa sta succedendo e cosa fare. Puoi essere la persona che resta bloccata, oppure quella a cui tutti, dopo, diranno:', 'guida-antipanico-soffocamento' ); ?></p>
		<strong class="gaps-final-quote"><?php esc_html_e( '"Meno male che c\'eri tu."', 'guida-antipanico-soffocamento' ); ?></strong>
		<p class="gaps-urgency-line">
			<?php esc_html_e( 'Il libro è disponibile ora. Ordina la tua copia quando vuoi.', 'guida-antipanico-soffocamento' ); ?>
		</p>
		<?php gaps_render_cta_button( esc_html__( 'Acquista ora la tua copia →', 'guida-antipanico-soffocamento' ), 'gaps-btn-onlight gaps-btn-lg', 'gaps-final-cta-btn' ); ?>

		<div class="gaps-ps-box">
			<p>
				<?php
				echo wp_kses(
					__( '<strong class="gaps-ps-label">P.S.</strong>&nbsp;— Se hai letto fin qui, probabilmente hai già capito la cosa più importante: la differenza tra un genitore che spera che non succeda nulla, e un genitore che — se dovesse succedere — saprebbe cosa fare, si costruisce&nbsp;<strong>prima</strong>, con calma, non nel momento in cui l\'emergenza è arrivata.', 'guida-antipanico-soffocamento' ),
					array( 'strong' => array( 'class' => array() ) )
				);
				?>
			</p>
			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %s: prezzo */
						__( 'Il libro costa %s, e include la scheda numeri d\'emergenza in omaggio.', 'guida-antipanico-soffocamento' ),
						esc_html( $price )
					),
					array( 'strong' => array() )
				);
				?>
			</p>
			<div class="gaps-ps-cta-wrap">
				<?php gaps_render_cta_button( esc_html__( 'Acquista la tua copia →', 'guida-antipanico-soffocamento' ) ); ?>
			</div>
		</div>
	</div>
</section>

<?php if ( $has_contacts ) : ?>
<!-- ===== CONTATTI ===== -->
<section class="gaps-contact-section">
	<div class="gaps-wrap">
		<h2><?php esc_html_e( 'Hai domande prima di prenotare? Scrivici e ti rispondiamo subito!', 'guida-antipanico-soffocamento' ); ?></h2>
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
						<svg viewBox="0 0 48 48"><defs><linearGradient id="gapsIgGrad" x1="0" y1="48" x2="48" y2="0"><stop offset="0" stop-color="#FFDC80"/><stop offset="0.25" stop-color="#FCAF45"/><stop offset="0.5" stop-color="#E1306C"/><stop offset="0.75" stop-color="#C13584"/><stop offset="1" stop-color="#833AB4"/></linearGradient></defs><rect width="48" height="48" rx="12" fill="url(#gapsIgGrad)"/><rect x="12" y="12" width="24" height="24" rx="7" fill="none" stroke="#fff" stroke-width="2.4"/><circle cx="24" cy="24" r="6.2" fill="none" stroke="#fff" stroke-width="2.4"/><circle cx="32.2" cy="15.8" r="1.6" fill="#fff"/></svg>
					</span>
					<?php esc_html_e( 'Instagram', 'guida-antipanico-soffocamento' ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php if ( $settings['formalife_logo_id'] ) : ?>
			<div class="gaps-contact-logo">
				<?php gaps_render_image( $settings['formalife_logo_id'], __( 'Formalife', 'guida-antipanico-soffocamento' ) ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>

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

<!-- ===== POPUP: MODULO DI PREORDINE ===== -->
<div class="gaps-modal-overlay" id="gaps-preorder-overlay">
	<div class="gaps-modal" role="dialog" aria-modal="true" aria-labelledby="gaps-preorder-title">
		<button type="button" class="gaps-modal-close" aria-label="<?php esc_attr_e( 'Chiudi', 'guida-antipanico-soffocamento' ); ?>">&times;</button>
		<h3 id="gaps-preorder-title"><?php esc_html_e( 'Acquista la tua copia', 'guida-antipanico-soffocamento' ); ?></h3>
		<p class="gaps-modal-intro gaps-step-intro" data-step-intro="1"><?php esc_html_e( 'Compila i tuoi dati: al passo successivo completerai il pagamento in sicurezza.', 'guida-antipanico-soffocamento' ); ?></p>
		<p class="gaps-modal-intro gaps-step-intro" data-step-intro="2" hidden><?php esc_html_e( 'Dati di consegna e, se ti serve, di fatturazione.', 'guida-antipanico-soffocamento' ); ?></p>
		<p class="gaps-modal-intro gaps-step-intro" data-step-intro="3" hidden><?php esc_html_e( 'Ultimo passo: quantità e pagamento sicuro.', 'guida-antipanico-soffocamento' ); ?></p>

		<form id="gaps-preorder-form" novalidate>

			<!-- STEP 1: dati anagrafici -->
			<div class="gaps-form-step" data-step="1">
				<div class="gaps-form-grid-2">
					<div class="gaps-form-row">
						<label for="gaps-f-nome"><?php esc_html_e( 'Nome *', 'guida-antipanico-soffocamento' ); ?></label>
						<input type="text" id="gaps-f-nome" name="nome" required />
					</div>
					<div class="gaps-form-row">
						<label for="gaps-f-cognome"><?php esc_html_e( 'Cognome *', 'guida-antipanico-soffocamento' ); ?></label>
						<input type="text" id="gaps-f-cognome" name="cognome" required />
					</div>
				</div>
				<div class="gaps-form-grid-2">
					<div class="gaps-form-row">
						<label for="gaps-f-telefono"><?php esc_html_e( 'Telefono *', 'guida-antipanico-soffocamento' ); ?></label>
						<input type="tel" id="gaps-f-telefono" name="telefono" required />
					</div>
					<div class="gaps-form-row">
						<label for="gaps-f-email"><?php esc_html_e( 'Email *', 'guida-antipanico-soffocamento' ); ?></label>
						<input type="email" id="gaps-f-email" name="email" required />
					</div>
				</div>
				<p class="gaps-form-message" data-step-message="1" aria-live="polite"></p>
				<button type="button" class="gaps-btn-primary gaps-step-next" data-goto="2"><?php esc_html_e( 'Avanti →', 'guida-antipanico-soffocamento' ); ?></button>
			</div>

			<!-- STEP 2: consegna e fatturazione -->
			<div class="gaps-form-step" data-step="2" hidden>
				<div class="gaps-form-row">
					<label for="gaps-f-indirizzo"><?php esc_html_e( 'Indirizzo di spedizione *', 'guida-antipanico-soffocamento' ); ?></label>
					<input type="text" id="gaps-f-indirizzo" name="indirizzo" />
				</div>
				<div class="gaps-form-grid-3">
					<div class="gaps-form-row">
						<label for="gaps-f-citta"><?php esc_html_e( 'Città *', 'guida-antipanico-soffocamento' ); ?></label>
						<input type="text" id="gaps-f-citta" name="citta" />
					</div>
					<div class="gaps-form-row">
						<label for="gaps-f-provincia"><?php esc_html_e( 'Provincia *', 'guida-antipanico-soffocamento' ); ?></label>
						<input type="text" id="gaps-f-provincia" name="provincia" maxlength="2" style="text-transform:uppercase;" />
					</div>
					<div class="gaps-form-row">
						<label for="gaps-f-cap"><?php esc_html_e( 'CAP *', 'guida-antipanico-soffocamento' ); ?></label>
						<input type="text" id="gaps-f-cap" name="cap" inputmode="numeric" maxlength="5" />
					</div>
				</div>
				<div class="gaps-form-row gaps-form-checkbox gaps-invoice-choice">
					<input type="checkbox" id="gaps-f-invoice" name="invoice_requested" value="1" aria-controls="gaps-invoice-fields" aria-expanded="false" />
					<label for="gaps-f-invoice">
						<?php esc_html_e( 'Voglio la fattura', 'guida-antipanico-soffocamento' ); ?>
					</label>
				</div>
				<div class="gaps-invoice-fields" id="gaps-invoice-fields" hidden>
					<div class="gaps-invoice-heading">
						<strong><?php esc_html_e( 'Dati di fatturazione', 'guida-antipanico-soffocamento' ); ?></strong>
						<span><?php esc_html_e( 'Compila tutti i campi per richiedere la fattura.', 'guida-antipanico-soffocamento' ); ?></span>
					</div>
					<div class="gaps-form-row">
						<label for="gaps-f-invoice-holder"><?php esc_html_e( 'Intestatario della fattura *', 'guida-antipanico-soffocamento' ); ?></label>
						<input type="text" id="gaps-f-invoice-holder" name="invoice_holder" autocomplete="organization" disabled />
					</div>
					<div class="gaps-form-row">
						<label for="gaps-f-billing-address"><?php esc_html_e( 'Indirizzo di fatturazione *', 'guida-antipanico-soffocamento' ); ?></label>
						<input type="text" id="gaps-f-billing-address" name="billing_address" autocomplete="billing street-address" disabled />
					</div>
					<div class="gaps-form-row">
						<label for="gaps-f-vat-number"><?php esc_html_e( 'P.IVA *', 'guida-antipanico-soffocamento' ); ?></label>
						<input type="text" id="gaps-f-vat-number" name="vat_number" inputmode="text" maxlength="20" autocomplete="off" disabled />
					</div>
					<div class="gaps-form-row">
						<label for="gaps-f-recipient"><?php esc_html_e( 'Codice univoco o PEC *', 'guida-antipanico-soffocamento' ); ?></label>
						<input type="text" id="gaps-f-recipient" name="recipient_code_or_pec" maxlength="100" autocomplete="off" disabled />
					</div>
				</div>
				<div class="gaps-form-row gaps-form-checkbox">
					<input type="checkbox" id="gaps-f-privacy" />
					<label for="gaps-f-privacy" style="font-weight:400;">
						<?php if ( $privacy_url ) : ?>
							<?php
							printf(
								/* translators: %s: link privacy policy */
								esc_html__( 'Ho letto e accetto la %s. *', 'guida-antipanico-soffocamento' ),
								'<a href="' . esc_url( $privacy_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Privacy Policy', 'guida-antipanico-soffocamento' ) . '</a>'
							);
							?>
						<?php else : ?>
							<?php esc_html_e( 'Acconsento al trattamento dei miei dati personali per gestire la richiesta di preordine. *', 'guida-antipanico-soffocamento' ); ?>
						<?php endif; ?>
					</label>
				</div>
				<p class="gaps-form-message" data-step-message="2" aria-live="polite"></p>
				<div class="gaps-step-actions">
					<button type="button" class="gaps-btn-secondary gaps-step-back" data-goto="1"><?php esc_html_e( '← Indietro', 'guida-antipanico-soffocamento' ); ?></button>
					<button type="button" class="gaps-btn-primary gaps-step-next" data-goto="3"><?php esc_html_e( 'Continua al pagamento →', 'guida-antipanico-soffocamento' ); ?></button>
				</div>
			</div>

			<!-- STEP 3: quantità e pagamento -->
			<div class="gaps-form-step" data-step="3" hidden>
				<div class="gaps-form-row gaps-quantity-row">
					<label for="gaps-f-quantita"><?php esc_html_e( 'Quante copie?', 'guida-antipanico-soffocamento' ); ?></label>
					<input type="number" id="gaps-f-quantita" name="quantita" min="1" step="1" value="1" />
					<span class="gaps-order-total" id="gaps-order-total" aria-live="polite"></span>
				</div>
				<p class="gaps-micro" id="gaps-shipping-note">
					<?php
					printf(
						/* translators: 1: costo di spedizione, 2: tempi di consegna */
						esc_html__( 'Totale comprensivo di %1$s di spedizione — consegna %2$s.', 'guida-antipanico-soffocamento' ),
						esc_html( $shipping_price ),
						esc_html( $date_delivery )
					);
					?>
				</p>
				<div id="gaps-payment-element" aria-busy="false"><!-- Stripe monta qui il modulo di pagamento --></div>
				<div id="gaps-payment-loading" class="gaps-payment-loading" hidden aria-live="polite">
					<span class="gaps-payment-spinner" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Preparazione del pagamento…', 'guida-antipanico-soffocamento' ); ?></span>
				</div>
				<p class="gaps-form-message" data-step-message="3" id="gaps-payment-message" aria-live="polite"></p>
				<div class="gaps-step-actions">
					<button type="button" class="gaps-btn-secondary gaps-step-back" data-goto="2"><?php esc_html_e( '← Indietro', 'guida-antipanico-soffocamento' ); ?></button>
					<button type="button" class="gaps-btn-primary" id="gaps-pay-button"><?php esc_html_e( 'Paga ora', 'guida-antipanico-soffocamento' ); ?></button>
				</div>
			</div>

		</form>
	</div>
</div>

<?php if ( $has_preview ) : ?>
<!-- ===== LIGHTBOX: ANTEPRIMA LIBRO ===== -->
<div class="gaps-lightbox-overlay" id="gaps-lightbox-overlay">
	<button type="button" class="gaps-lightbox-close" aria-label="<?php esc_attr_e( 'Chiudi', 'guida-antipanico-soffocamento' ); ?>">&times;</button>
	<button type="button" class="gaps-lightbox-prev" aria-label="<?php esc_attr_e( 'Precedente', 'guida-antipanico-soffocamento' ); ?>">&#8249;</button>
	<img class="gaps-lightbox-img" src="" alt="" />
	<button type="button" class="gaps-lightbox-next" aria-label="<?php esc_attr_e( 'Successiva', 'guida-antipanico-soffocamento' ); ?>">&#8250;</button>
</div>
<?php endif; ?>

<!-- ===== BARRA CTA FISSA (solo mobile, vedi frontend.css/frontend.js) ===== -->
<div class="gaps-sticky-cta" id="gaps-sticky-cta">
	<?php
	gaps_render_cta_button(
		sprintf(
			/* translators: %s: prezzo del libro */
			esc_html__( 'Acquista ora — %s', 'guida-antipanico-soffocamento' ),
			esc_html( $price )
		) . ' <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>'
	);
	?>
</div>

</div>
<?php wp_footer(); ?>
</body>
</html>
