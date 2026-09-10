<?php
/**
 * Helper condivisi per leggere le impostazioni del plugin con i valori di
 * default. Questo file non definisce classi: sono funzioni globali con
 * prefisso fmh_ per essere utilizzabili facilmente da template e altre
 * classi (mirror di gaps-settings-helpers.php nel plugin del libro).
 *
 * v2.0.0 — Il copy testuale lungo (paragrafi, titoli, elenchi) delle
 * sezioni non è più gestito da campo impostazioni: vive direttamente nel
 * template (templates/template-home.php), tradotto con __()/esc_html_e()
 * come nel resto dell'ecosistema Formalife, per garantire che il testo
 * pubblicato corrisponda sempre, parola per parola, al copy approvato.
 * Restano impostazioni solo i dati operativi che cambiano nel tempo:
 * immagini, URL, prezzo/date del libro, statistiche, contatti, dati legali.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valori di default di tutte le impostazioni del plugin.
 *
 * @return array
 */
function fmh_default_settings() {
	return array(
		// Immagini (ID allegato Libreria Media).
		'book_cover_image_id'   => 0,
		'camposarcone_image_id' => 0,
		'raffaele_image_id'     => 0,
		'sara_image_id'         => 0,
		'formalife_logo_id'     => 0,
		'og_image_id'           => 0,

		// Hero — immagine di sfondo con trasparenza/sfocatura/colore di
		// sovrapposizione regolabili dal pannello (sezione 1).
		'hero_bg_image_id' => 0,
		'hero_bg_opacity'  => 18, // percentuale (0-100) di visibilità dell'immagine.
		'hero_bg_blur'     => 3,  // sfocatura in px.
		'hero_overlay_color' => '#FBF8F4', // cream chiaro, stesso valore di --fmh-cream.

		// Team — ruolo e bio personalizzabili per membro (sezione "Chi c'è
		// dietro Formalife"), layout basato su gaps-author-section.
		'team_mafalda_role' => 'Direttrice Scientifica',
		'team_mafalda_bio'  => "Pediatra di famiglia da quasi trent'anni. Ha verificato personalmente i contenuti del libro e garantisce la qualità e l'accuratezza di tutti i contenuti clinici.",
		'team_raffaele_role' => 'CEO e Istruttore',
		'team_raffaele_bio'  => "Autore del libro e instancabile autodidatta.",
		'team_sara_role' => 'Istruttrice',
		'team_sara_bio'  => "Social media manager e occhio vigile dell'azienda.",

		// Carosello immagini reali dei corsi (sostituisce la vecchia
		// sezione con i tre badge di fiducia).
		'carousel_image_1_id' => 0,
		'carousel_image_2_id' => 0,
		'carousel_image_3_id' => 0,
		'carousel_image_4_id' => 0,
		'carousel_image_5_id' => 0,

		// Immagini di sfondo per i sei box "momenti della famiglia".
		'moment_1_bg_image_id' => 0,
		'moment_2_bg_image_id' => 0,
		'moment_3_bg_image_id' => 0,
		'moment_4_bg_image_id' => 0,
		'moment_5_bg_image_id' => 0,
		'moment_6_bg_image_id' => 0,

		// Messaggio precompilato per il CTA WhatsApp del box "Il corso".
		'course_whatsapp_message' => 'Sono interessato al corso, quando ci sono le prossime date?',

		// Menu superiore sticky.
		'nav_home_url'   => '',
		'nav_book_url'   => '',
		'nav_course_url' => '',

		// Osserva.Valuta.Agisci — immagine di sfondo in sovrapposizione.
		'triad_bg_image_id' => 0,
		'triad_bg_opacity'  => 12,

		// "Qual è il prossimo passo?" — immagine 16:9 per ciascun box.
		'next_book_image_id'   => 0,
		'next_course_image_id' => 0,

		// "Il libro" — seconda immagine (impilata sotto la copertina).
		'book_secondary_image_id' => 0,

		// Lista d'attesa corso — immagine di sfondo sezione (10% opacità).
		'waitlist_bg_image_id' => 0,

		// B2B — immagine quadrata al posto dell'icona.
		'b2b_image_id' => 0,

		// Team — immagine di sfondo sezione (in trasparenza).
		'team_bg_image_id' => 0,
		'team_bg_opacity'  => 10,

		// SEO.
		'meta_title'       => 'Formalife — Preparazione familiare alle emergenze pediatriche',
		'meta_description' => "Formalife aiuta genitori e caregiver a riconoscere e affrontare con lucidità le emergenze pediatriche. Il libro sul soffocamento e il corso pratico a numero chiuso, seguiti dalla Dott.ssa Mafalda Camposarcone.",

		// Il libro — dati operativi (sezioni 1 e 5).
		'book_price'        => '19,90 €',
		'book_date_closing' => '31 agosto',
		'book_date_delivery' => 'dal 14 agosto',
		'book_url'          => 'https://www.formalife.it/guida-antipanico-soffocamento/',
		// Ancora verso la sezione anteprima sfogliabile sulla landing del
		// libro: richiede che quella pagina esponga un elemento con questo
		// id (vedi nota nel riepilogo consegnato — piccola patch prevista
		// sul plugin "Guida Anti-Panico al Soffocamento Pediatrico").
		'book_preview_anchor' => 'anteprima',

		// Statistiche — stessi valori reali già pubblicati sulla landing
		// del libro (fonte: Ministero della Salute), riusati qui invece di
		// duplicarli con numeri diversi.
		'stat1_number' => '500',
		'stat1_label'  => 'bambini muoiono soffocati ogni anno in Europa',
		'stat2_number' => '1.000',
		'stat2_label'  => "ospedalizzazioni l'anno in Italia per soffocamento, dato stabile da 10 anni",
		'stat3_number' => '60–80%',
		'stat3_label'  => 'degli episodi è legato al cibo — un pasto qualunque',
		'stat_source'  => 'Fonte: Linee di indirizzo del Ministero della Salute per la prevenzione del soffocamento in età pediatrica',

		// Contatti (sezioni 8 e 12).
		'contact_email'     => 'info@formalife.it',
		'contact_whatsapp'  => '3517940823',
		'contact_instagram' => '@formalife.it',

		// Link legali (footer) — pagine generate e possedute dal plugin
		// "Guida Anti-Panico al Soffocamento Pediatrico".
		'terms_url'   => 'https://www.formalife.it/condizioni-di-vendita/',
		'privacy_url' => 'https://www.formalife.it/privacy/',

		// Dati legali azienda (footer).
		'legal_company_name' => 'Formalife Srls',
		'legal_vat_number'   => '04726850987',

		// Notifiche lista d'attesa corso (sezione 6).
		'notify_email' => 'info@formalife.it',
	);
}

/**
 * Restituisce le impostazioni salvate, unite ai valori di default per i campi mancanti.
 *
 * @return array
 */
function fmh_get_settings() {
	$saved = get_option( FMH_OPTION_KEY, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return wp_parse_args( $saved, fmh_default_settings() );
}

/**
 * Restituisce l'URL di un'immagine impostata, oppure stringa vuota se non
 * configurata o se l'allegato non esiste più nella libreria media.
 *
 * @param int    $attachment_id ID dell'allegato.
 * @param string $size          Dimensione immagine WordPress.
 * @return string
 */
function fmh_get_image_url( $attachment_id, $size = 'large' ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id ) {
		return '';
	}
	$url = wp_get_attachment_image_url( $attachment_id, $size );
	return $url ? $url : '';
}

/**
 * Stampa un'immagine impostata, o un placeholder grazioso se non è stata
 * caricata alcuna immagine — la sezione non si rompe mai visivamente.
 * Lazy-loading attivo di default (tranne dove esplicitamente disattivato,
 * es. l'immagine più in alto nella pagina, per non penalizzare il LCP).
 *
 * @param int    $attachment_id    ID allegato.
 * @param string $alt              Testo alternativo.
 * @param string $css_class        Classe CSS aggiuntiva per il tag img.
 * @param string $placeholder_text Testo mostrato nel placeholder. Se vuoto, non viene stampato nulla.
 * @param string $size             Dimensione immagine WordPress.
 * @param bool   $lazy             Se false, l'immagine viene caricata eager (usare solo above-the-fold).
 */
function fmh_render_image( $attachment_id, $alt, $css_class = '', $placeholder_text = '', $size = 'large', $lazy = true ) {
	$url = fmh_get_image_url( $attachment_id, $size );
	if ( $url ) {
		printf(
			'<img src="%1$s" alt="%2$s"%3$s%4$s decoding="async" />',
			esc_url( $url ),
			esc_attr( $alt ),
			$css_class ? ' class="' . esc_attr( $css_class ) . '"' : '',
			$lazy ? ' loading="lazy"' : ' loading="eager" fetchpriority="high"'
		);
	} elseif ( '' !== $placeholder_text ) {
		printf(
			'<div class="fmh-placeholder%1$s">%2$s</div>',
			$css_class ? ' ' . esc_attr( $css_class ) : '',
			esc_html( $placeholder_text )
		);
	}
}

/**
 * Stampa l'avatar di una persona (sezione "Chi c'è dietro Formalife"): la
 * foto se caricata, altrimenti un cerchio con le iniziali del nome — un
 * placeholder più adatto a un volto rispetto al riquadro tratteggiato
 * generico di fmh_render_image(), usato invece per copertine/foto grandi.
 *
 * @param int    $attachment_id ID allegato (0 se non caricato).
 * @param string $name          Nome completo, usato per alt e iniziali.
 * @param string $css_class     Classe CSS aggiuntiva per il wrapper.
 */
function fmh_render_avatar( $attachment_id, $name, $css_class = '' ) {
	$url = fmh_get_image_url( $attachment_id, 'medium' );
	if ( $url ) {
		printf(
			'<img src="%1$s" alt="%2$s" class="fmh-avatar-img%3$s" loading="lazy" decoding="async" />',
			esc_url( $url ),
			esc_attr( $name ),
			$css_class ? ' ' . esc_attr( $css_class ) : ''
		);
		return;
	}

	$words    = preg_split( '/\s+/', trim( $name ) );
	$initials = '';
	foreach ( array_slice( $words, 0, 2 ) as $word ) {
		$initials .= mb_strtoupper( mb_substr( $word, 0, 1 ) );
	}

	printf(
		'<div class="fmh-avatar-fallback%3$s" role="img" aria-label="%2$s"><span>%1$s</span></div>',
		esc_html( $initials ),
		esc_attr( $name ),
		$css_class ? ' ' . esc_attr( $css_class ) : ''
	);
}

/**
 * Stampa un pulsante/link CTA (sempre in stile "accento rosso", vedi
 * frontend.css). Nessun popup: è sempre un link diretto — verso una pagina
 * esterna, un'ancora interna, o innescato via JS solo per lo scroll
 * fluido (mai per aprire overlay).
 *
 * @param string $label_html  Markup HTML dell'etichetta (di fiducia, non da input utente).
 * @param string $url         URL di destinazione.
 * @param string $extra_class Classe CSS aggiuntiva.
 * @param bool   $new_tab     Se true, apre in una nuova scheda (link esterni).
 */
function fmh_render_cta_button( $label_html, $url, $extra_class = '', $new_tab = false ) {
	$classes = trim( 'fmh-btn-primary ' . $extra_class );
	$target  = $new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';

	printf(
		'<a class="%1$s" href="%2$s"%3$s>%4$s</a>',
		esc_attr( $classes ),
		esc_url( $url ),
		$target,
		$label_html
	);
}

/**
 * Costruisce un URL WhatsApp valido a partire da un numero di telefono
 * o da un URL già completo inserito nelle impostazioni.
 *
 * @param string $value Numero di telefono o URL.
 * @return string
 */
function fmh_build_whatsapp_url( $value, $message = '' ) {
	$value = trim( $value );
	if ( '' === $value ) {
		return '';
	}
	if ( 0 === strpos( $value, 'http' ) ) {
		$url = $value;
	} else {
		$digits = preg_replace( '/[^0-9]/', '', $value );
		if ( '' === $digits ) {
			return '';
		}
		// Numeri italiani senza prefisso internazionale: assumiamo +39.
		if ( '39' !== substr( $digits, 0, 2 ) && strlen( $digits ) <= 10 ) {
			$digits = '39' . $digits;
		}
		$url = 'https://wa.me/' . $digits;
	}

	$message = trim( $message );
	if ( '' !== $message ) {
		$url .= ( false === strpos( $url, '?' ) ? '?' : '&' ) . 'text=' . rawurlencode( $message );
	}

	return esc_url( $url );
}

/**
 * URL WhatsApp precompilato per il CTA del box "Il corso" (sezione "Qual è
 * il prossimo passo?"): stesso numero dei contatti, messaggio configurabile
 * dal pannello impostazioni.
 *
 * @return string
 */
function fmh_get_course_whatsapp_url() {
	$settings = fmh_get_settings();
	return fmh_build_whatsapp_url( $settings['contact_whatsapp'], $settings['course_whatsapp_message'] );
}

/**
 * Costruisce un URL Instagram valido a partire da un handle o da un URL completo.
 *
 * @param string $value Handle (con o senza @) o URL completo.
 * @return string
 */
function fmh_build_instagram_url( $value ) {
	$value = trim( $value );
	if ( '' === $value ) {
		return '';
	}
	if ( 0 === strpos( $value, 'http' ) ) {
		return esc_url( $value );
	}
	$handle = ltrim( $value, '@' );
	return esc_url( 'https://instagram.com/' . rawurlencode( $handle ) );
}

/**
 * Restituisce l'URL della pagina "Home" gestita da questo plugin, oppure
 * stringa vuota se non ancora creata.
 *
 * @return string
 */
function fmh_get_home_url() {
	return FMH_Page_Manager::get_page_url( 'home' );
}

/**
 * Restituisce l'URL dell'anteprima sfogliabile del libro: la pagina del
 * libro con l'ancora verso la sua sezione "Prova" (vedi
 * gaps_get_settings()/template-landing.php nel plugin del libro). Il
 * frammento è configurabile (impostazioni → "book_preview_anchor") per
 * poterlo allineare in un secondo momento all'id realmente pubblicato su
 * quella pagina.
 *
 * @return string
 */
function fmh_get_book_preview_url() {
	$settings = fmh_get_settings();
	$base     = trim( $settings['book_url'] );
	if ( '' === $base ) {
		return '';
	}
	$anchor = trim( $settings['book_preview_anchor'] );
	return $anchor ? $base . '#' . $anchor : $base;
}

/**
 * Restituisce gli URL per il menu superiore sticky (Home / Il libro / I
 * corsi), con fallback sensati se non sono stati impostati esplicitamente.
 *
 * @return array{home:string,book:string,course:string}
 */
function fmh_get_nav_urls() {
	$settings = fmh_get_settings();
	$home_url = fmh_get_home_url();
	$course_url = function_exists( 'fmh_get_course_page_url' ) ? fmh_get_course_page_url() : '';

	return array(
		'home'   => $settings['nav_home_url'] ? $settings['nav_home_url'] : $home_url,
		'book'   => $settings['nav_book_url'] ? $settings['nav_book_url'] : $settings['book_url'],
		'course' => $settings['nav_course_url'] ? $settings['nav_course_url'] : ( $course_url ? $course_url : ( $home_url ? $home_url . '#il-corso' : '#il-corso' ) ),
	);
}

/**
 * Restituisce l'elenco (non vuoto) degli URL delle immagini caricate per il
 * carosello "corsi dal vivo" (sostituisce la vecchia fascia con i tre badge
 * di fiducia). Solo le immagini realmente caricate vengono incluse.
 *
 * @return string[]
 */
function fmh_get_carousel_image_urls() {
	$settings = fmh_get_settings();
	$urls     = array();
	foreach ( array( 'carousel_image_1_id', 'carousel_image_2_id', 'carousel_image_3_id', 'carousel_image_4_id', 'carousel_image_5_id' ) as $key ) {
		$url = fmh_get_image_url( $settings[ $key ], 'full' );
		if ( $url ) {
			$urls[] = $url;
		}
	}
	return $urls;
}
