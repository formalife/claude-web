<?php
/**
 * Helper condivisi per leggere le impostazioni del plugin con i valori di default.
 * Questo file non definisce classi: sono funzioni globali con prefisso gaps_
 * per essere utilizzabili facilmente da template e altre classi.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valori di default di tutte le impostazioni del plugin.
 *
 * @return array
 */
function gaps_default_settings() {
	return array(
		// Immagini (ID allegato Libreria Media).
		'cover_image_id'         => 0,
		'camposarcone_image_id'  => 0,
		'admission_bg_image_id'  => 0,
		'guarantee_image_id'     => 0,
		'preview_image_1_id'     => 0,
		'preview_image_2_id'     => 0,
		'preview_image_3_id'     => 0,
		// Anteprima libro responsive (v3.7): due varianti dedicate
		// desktop/mobile, con <picture> lato frontend. Se lasciate a 0, il
		// rendering ricade sul vecchio campo singolo "preview_image_1_id"
		// (vedi gaps_resolve_book_preview_ids()), per compatibilità con le
		// landing già pubblicate prima dell'introduzione di questi campi.
		'preview_image_desktop_id' => 0,
		'preview_image_mobile_id'  => 0,
		'formalife_logo_id'      => 0,
		'og_image_id'            => 0,
		'mechanism_key_image_id' => 0,
		'final_floating_image_1_id' => 0,
		'final_floating_image_2_id' => 0,
		'final_floating_image_3_id' => 0,

		// Opacità e sfocatura sfondo sezione "damaging admission" (0-100 / px).
		'admission_bg_opacity' => 15,
		'admission_bg_blur'    => 4,

		// Colori.
		'color_teal_primary' => '#0B6560',
		'color_teal_accent'  => '#2CADA9',
		'color_cream_bg'     => '#FFFDF7',
		'color_peach_bg'     => '#F5D9A8',
		'color_terracotta'   => '#C24E3A',

		// Contenuti variabili.
		'price'         => '19,90 €',
		'shipping_price' => '2,90 €',
		'date_closing'  => '14 agosto',
		'date_delivery' => 'in 4-5 giorni lavorativi',

		// Destinazione dopo l'invio del modulo di preordine.
		'stripe_url' => '',

		// Pagina del corso pratico Formalife, proposta come passo successivo
		// (cross-sell soft, non obbligatorio) nella pagina "Grazie — Ordine
		// confermato" mostrata dopo il pagamento.
		'course_url' => 'https://www.formalife.it/genitori-pronti/',

		/*
		 * Pagina "I tuoi numeri importanti" (v3.5.3) — scheda PDF in omaggio,
		 * raggiungibile dal QR code stampato nelle ultime pagine del libro.
		 * 'numeri_pdf_id' e 'numeri_preview_image_id' sono sovrascritture
		 * facoltative dalla Libreria Media: se lasciate a 0, il plugin usa il
		 * PDF e l'anteprima inclusi di serie nel pacchetto (sempre
		 * disponibili, anche subito dopo l'installazione, senza bisogno di
		 * caricare nulla). 'review_url' è il link diretto alla recensione
		 * Google, mostrato in una sezione separata e non invasiva dopo il
		 * download.
		 */
		'numeri_pdf_id'           => 0,
		'numeri_preview_image_id' => 0,
		'review_url'              => 'https://g.page/r/CcCAtN00-Md4EBM/review',

		// Contatti (nuova sezione finale).
		'contact_email'     => '',
		'contact_whatsapp'  => '',
		'contact_instagram' => '',

		// Link legali (footer).
		'terms_url'   => '',
		'privacy_url' => '',

		// Dato statistico — sezione "Costo dell'inazione".
		'stat1_number' => '500',
		'stat1_label'  => 'bambini muoiono soffocati ogni anno in Europa',
		'stat2_number' => '1.000',
		'stat2_label'  => "ospedalizzazioni l'anno in Italia per soffocamento, dato stabile da 10 anni",
		'stat3_number' => '60–80%',
		'stat3_label'  => 'degli episodi è legato al cibo — un pasto qualunque',
		'stat_source'  => 'Fonte: Linee di indirizzo del Ministero della Salute per la prevenzione del soffocamento in età pediatrica',

		/*
		 * Dati legali e aziendali — usati nelle pagine "Condizioni di
		 * vendita" e "Privacy" generate automaticamente dal plugin. I
		 * valori di default sono segnaposto tra parentesi quadre: finché
		 * non vengono sostituiti con i dati reali dal pannello
		 * impostazioni, compaiono evidenziati sulla pagina pubblica (vedi
		 * gaps_legal_field()) per ricordare di completarli prima della
		 * pubblicazione definitiva.
		 */
		'legal_company_name'    => '[Ragione sociale] S.r.l.',
		'legal_vat_number'      => '[Partita IVA / Codice Fiscale]',
		'legal_address'         => '[Indirizzo sede legale], [CAP] [Città] ([Provincia]), Italia',
		'legal_pec'             => '[indirizzo]@pec.it',
		'legal_rea'             => '',
		'legal_share_capital'   => '',
		'legal_court'           => '[Città sede legale]',
		'legal_dpo_email'       => '',
		'legal_last_updated'    => function_exists( 'date_i18n' ) ? date_i18n( 'j F Y' ) : gmdate( 'd/m/Y' ),
		'shipping_carrier'      => '',
		'guarantee_period_days' => '30',

		/*
		 * Notifiche e conferma di pagamento. Nessuna email parte alla
		 * semplice compilazione del modulo di preordine. "notify_email" è
		 * l'indirizzo interno che riceve la notifica solo quando Stripe
		 * conferma che il pagamento è realmente andato a buon fine; nello
		 * stesso momento parte anche, separatamente, l'email di
		 * ringraziamento al cliente (vedi class-gaps-stripe-webhook.php).
		 * "stripe_webhook_secret" è la chiave segreta (whsec_...) mostrata
		 * da Stripe quando si registra l'endpoint webhook in Dashboard.
		 */
		'notify_email'          => 'info@formalife.it',
		'stripe_webhook_secret' => '',

		/*
		 * Integrazione Stripe embedded (Payment Element), v3.6.0. Sostituisce
		 * il vecchio reindirizzamento a un Payment Link esterno: il pagamento
		 * avviene ora dentro il popup, nello step 3. "stripe_secret_key" e
		 * "stripe_publishable_key" si trovano nel Dashboard Stripe in
		 * Sviluppatori → Chiavi API. Usare le chiavi che iniziano con
		 * pk_test_/sk_test_ finché si è in fase di test, poi passare a
		 * pk_live_/sk_live_ per andare in produzione.
		 */
		'stripe_publishable_key'   => '',
		'stripe_secret_key'        => '',
		'stripe_payment_methods'   => array( 'card', 'link', 'paypal' ),

		/*
		 * Meta Pixel (v3.7), opzionale e disattivato di default: se questo
		 * campo resta vuoto, nessuno script del Pixel viene mai accodato
		 * (vedi GAPS_Assets::enqueue_frontend()) e non parte alcuna
		 * richiesta verso connect.facebook.net. Se compilato, il Pixel
		 * viene comunque caricato solo dopo le risorse critiche della
		 * pagina e solo se risulta un consenso valido (vedi
		 * assets/js/gaps-meta-pixel-loader.js).
		 */
		'meta_pixel_id' => '',
	);
}

/**
 * Restituisce il prezzo unitario del libro in centesimi, ricavandolo dal
 * campo "price" delle impostazioni (es. "19,90 €") in modo da avere un'unica
 * fonte di verità: chi cambia il prezzo mostrato in pagina aggiorna anche
 * automaticamente l'importo richiesto a Stripe. Non validare qui un formato
 * troppo rigido: si limita a estrarre cifre, virgola o punto.
 *
 * @return int
 */
function gaps_get_price_cents() {
	$settings = gaps_get_settings();
	$raw      = (string) $settings['price'];

	// Tiene solo cifre, virgola e punto (es. "19,90 €" -> "19,90").
	$numeric = preg_replace( '/[^0-9,\.]/', '', $raw );
	$numeric = str_replace( ',', '.', $numeric );

	$value = (float) $numeric;
	if ( $value <= 0 ) {
		return 1990; // fallback di sicurezza: 19,90 €.
	}

	return (int) round( $value * 100 );
}

/**
 * Restituisce il costo di spedizione in centesimi, ricavandolo dal campo
 * "shipping_price" delle impostazioni (es. "2,90 €"), con la stessa logica
 * di gaps_get_price_cents(): un'unica fonte di verità, così chi cambia il
 * costo mostrato in pagina aggiorna automaticamente anche l'importo
 * richiesto a Stripe. È una spedizione fissa per ordine (non moltiplicata
 * per la quantità di copie): un ordine di più copie viaggia in un unico
 * pacco.
 *
 * @return int
 */
function gaps_get_shipping_cents() {
	$settings = gaps_get_settings();
	$raw      = (string) $settings['shipping_price'];

	$numeric = preg_replace( '/[^0-9,\.]/', '', $raw );
	$numeric = str_replace( ',', '.', $numeric );

	$value = (float) $numeric;
	if ( $value < 0 ) {
		return 290; // fallback di sicurezza: 2,90 €.
	}

	return (int) round( $value * 100 );
}

/**
 * Restituisce le impostazioni salvate, unite ai valori di default per i campi mancanti.
 *
 * @return array
 */
function gaps_get_settings() {
	$saved = get_option( GAPS_OPTION_KEY, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return wp_parse_args( $saved, gaps_default_settings() );
}

/**
 * Restituisce l'URL di un'immagine impostata, oppure stringa vuota se non configurata
 * o se l'allegato non esiste più nella libreria media.
 *
 * @param int    $attachment_id ID dell'allegato.
 * @param string $size          Dimensione immagine WordPress.
 * @return string
 */
function gaps_get_image_url( $attachment_id, $size = 'large' ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id ) {
		return '';
	}
	$url = wp_get_attachment_image_url( $attachment_id, $size );
	return $url ? $url : '';
}

/**
 * Renderer condiviso per qualunque immagine della Libreria Media stampata
 * dal plugin: unico punto in cui viene chiamato wp_get_attachment_image(),
 * così ogni <img> generata ha sempre width/height reali, srcset, sizes,
 * loading/decoding coerenti, senza dover ripetere questa logica in ogni
 * template o sezione. Restituisce una stringa (da fare echo dal chiamante),
 * mai un warning: se l'ID non è valido o non è più un'immagine in libreria,
 * restituisce semplicemente una stringa vuota.
 *
 * @param int          $attachment_id ID dell'allegato.
 * @param string|array $size          Dimensione registrata WordPress (stringa) o array [w,h].
 * @param array        $attributes    Attributi HTML aggiuntivi/di override (class, alt, loading,
 *                                    decoding, fetchpriority, sizes, ...). Vedi wp_get_attachment_image().
 * @return string
 */
function gaps_render_attachment_image( $attachment_id, $size = 'large', $attributes = array() ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
		return '';
	}

	// Ordine dell'alt text: 1) valore esplicito passato dal chiamante (anche
	// stringa vuota voluta, per immagini dichiaratamente decorative); 2) se
	// la chiave "alt" non viene passata affatto, wp_get_attachment_image()
	// usa da sola il campo alt dell'allegato salvato in Libreria Media; 3)
	// altrimenti resta una stringa vuota. Non usiamo mai il titolo
	// dell'allegato come alt: spesso coincide con il nome del file e
	// produrrebbe testo alternativo ridondante o poco utile.
	$attributes = wp_parse_args(
		$attributes,
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);

	$html = wp_get_attachment_image( $attachment_id, $size, false, $attributes );

	return $html ? $html : '';
}

/**
 * Stampa la copertina del libro nella Hero: l'unica immagine della pagina
 * con priorità di caricamento esplicitamente alta (candidata LCP). A
 * differenza di gaps_render_attachment_image(), forza sempre
 * loading="eager" e fetchpriority="high" e non va mai usata per altre
 * immagini della pagina.
 *
 * @param int    $attachment_id ID allegato della copertina.
 * @param string $alt           Testo alternativo.
 * @param string $css_class     Classe CSS aggiuntiva.
 */
function gaps_render_hero_cover_image( $attachment_id, $alt, $css_class = '' ) {
	$attachment_id = absint( $attachment_id );

	if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
		printf( '<div class="gaps-placeholder">%s</div>', esc_html__( 'Copertina del libro', 'guida-antipanico-soffocamento' ) );
		return;
	}

	echo gaps_render_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- già escapato da wp_get_attachment_image().
		$attachment_id,
		'gaps-hero-cover',
		array(
			'class'         => trim( 'gaps-hero-cover-img ' . $css_class ),
			'alt'           => $alt,
			'loading'       => 'eager',
			'decoding'      => 'async',
			'fetchpriority' => 'high',
			'sizes'         => '(max-width: 860px) 82vw, 402px',
		)
	);
}

/**
 * Risolve gli ID allegato da usare per l'anteprima responsive del libro
 * (sezione "Prova"), applicando i fallback richiesti: se manca la variante
 * desktop dedicata, usa il vecchio campo singolo (compatibilità con landing
 * pubblicate prima dell'introduzione dei campi desktop/mobile); se manca la
 * variante mobile, usa la stessa immagine desktop già risolta.
 *
 * @param array $settings  Impostazioni del plugin (gaps_get_settings()).
 * @param int   $legacy_id ID allegato del vecchio campo singolo, da usare come fallback finale.
 * @return array{desktop: int, mobile: int}
 */
function gaps_resolve_book_preview_ids( $settings, $legacy_id = 0 ) {
	$desktop = absint( $settings['preview_image_desktop_id'] );
	$mobile  = absint( $settings['preview_image_mobile_id'] );
	$legacy  = absint( $legacy_id );

	if ( ! $desktop || ! wp_attachment_is_image( $desktop ) ) {
		$desktop = ( $legacy && wp_attachment_is_image( $legacy ) ) ? $legacy : 0;
	}
	if ( ! $mobile || ! wp_attachment_is_image( $mobile ) ) {
		$mobile = $desktop;
	}

	return array(
		'desktop' => $desktop,
		'mobile'  => $mobile,
	);
}

/**
 * Stampa il markup <picture> dell'anteprima responsive del libro, con
 * sorgente dedicata per il breakpoint mobile: il browser scarica una sola
 * delle due varianti (mai entrambe). Se le due immagini hanno un rapporto
 * d'aspetto diverso, il chiamante deve accompagnare questo markup con lo
 * style prodotto da gaps_get_book_preview_ratio_style() sul contenitore,
 * per evitare layout shift al cambio di breakpoint.
 *
 * @param int    $desktop_id ID allegato variante desktop (già risolto, vedi gaps_resolve_book_preview_ids()).
 * @param int    $mobile_id  ID allegato variante mobile (già risolto).
 * @param string $alt        Testo alternativo.
 * @param int    $breakpoint Larghezza (px) sotto la quale si usa la variante mobile.
 */
function gaps_render_book_preview_picture( $desktop_id, $mobile_id, $alt, $breakpoint = 767 ) {
	$desktop_id = absint( $desktop_id );
	$mobile_id  = absint( $mobile_id );

	if ( ! $desktop_id || ! wp_attachment_is_image( $desktop_id ) ) {
		return;
	}

	// Il contenitore ".gaps-preview-visual-img" e' largo 190px, tranne sotto
	// gli 860px dove il CSS lo ingrandisce a 240px (vedi frontend.css):
	// riflettiamo la stessa soglia nell'attributo "sizes", cosi' il browser
	// sceglie la variante piu' vicina alla dimensione realmente renderizzata.
	$sizes = '(max-width: 860px) 240px, 190px';

	$desktop_html = gaps_render_attachment_image(
		$desktop_id,
		'gaps-book-preview-desktop',
		array(
			'class'    => 'gaps-preview-visual-img-tag',
			'alt'      => $alt,
			'loading'  => 'lazy',
			'decoding' => 'async',
			'sizes'    => $sizes,
		)
	);

	if ( ! $desktop_html ) {
		return;
	}

	echo '<picture class="gaps-book-preview-picture">';

	if ( $mobile_id && $mobile_id !== $desktop_id && wp_attachment_is_image( $mobile_id ) ) {
		$mobile_srcset = wp_get_attachment_image_srcset( $mobile_id, 'gaps-book-preview-mobile' );
		$mobile_src    = wp_get_attachment_image_url( $mobile_id, 'gaps-book-preview-mobile' );

		if ( $mobile_src ) {
			printf(
				'<source media="(max-width: %1$dpx)" srcset="%2$s" sizes="%3$s" />',
				absint( $breakpoint ),
				esc_attr( $mobile_srcset ? $mobile_srcset : $mobile_src ),
				esc_attr( $sizes )
			);
		}
	}

	echo $desktop_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- già escapato da wp_get_attachment_image().
	echo '</picture>';
}

/**
 * Calcola l'attributo style="" (rapporto d'aspetto via custom property CSS)
 * da applicare al contenitore ".gaps-preview-visual-img" quando le varianti
 * desktop e mobile dell'anteprima libro hanno proporzioni diverse: evita
 * layout shift quando il browser passa dall'una all'altra al variare del
 * breakpoint. Restituisce stringa vuota se non c'è nulla da sovrascrivere
 * (il CSS di base già riserva uno spazio coerente con l'aspect-ratio letta
 * dagli attributi width/height dell'<img>).
 *
 * @param int $desktop_id ID allegato variante desktop.
 * @param int $mobile_id  ID allegato variante mobile.
 * @return string
 */
function gaps_get_book_preview_ratio_style( $desktop_id, $mobile_id ) {
	$desktop_meta = $desktop_id ? wp_get_attachment_metadata( $desktop_id ) : false;
	if ( empty( $desktop_meta['width'] ) || empty( $desktop_meta['height'] ) ) {
		return '';
	}

	$declarations = sprintf(
		'--gaps-preview-ratio-desktop:%d/%d;',
		absint( $desktop_meta['width'] ),
		absint( $desktop_meta['height'] )
	);

	if ( $mobile_id && $mobile_id !== $desktop_id ) {
		$mobile_meta = wp_get_attachment_metadata( $mobile_id );
		if ( ! empty( $mobile_meta['width'] ) && ! empty( $mobile_meta['height'] ) ) {
			$declarations .= sprintf(
				'--gaps-preview-ratio-mobile:%d/%d;',
				absint( $mobile_meta['width'] ),
				absint( $mobile_meta['height'] )
			);
		}
	}

	return ' style="' . esc_attr( $declarations ) . '"';
}

/**
 * Restituisce il Meta Pixel ID configurato nel pannello impostazioni, o
 * stringa vuota se il campo non è stato compilato — in quel caso nessuno
 * script del Pixel viene mai accodato (vedi GAPS_Assets::enqueue_frontend()).
 *
 * @return string
 */
function gaps_get_meta_pixel_id() {
	$settings = gaps_get_settings();
	return trim( (string) $settings['meta_pixel_id'] );
}

/**
 * Ritorna true se entrambe le chiavi Stripe (pubblicabile e segreta) sono
 * state configurate: senza di esse lo step di pagamento del popup non può
 * completarsi.
 *
 * @return bool
 */
function gaps_has_cta_destination() {
	$settings = gaps_get_settings();
	return '' !== trim( $settings['stripe_publishable_key'] ) && '' !== trim( $settings['stripe_secret_key'] );
}

/**
 * Stampa un'immagine impostata, o un placeholder grazioso se non è stata
 * caricata alcuna immagine — la sezione non si rompe mai visivamente.
 *
 * @param int    $attachment_id    ID allegato.
 * @param string $alt              Testo alternativo.
 * @param string $css_class        Classe CSS aggiuntiva per il tag img.
 * @param string $placeholder_text Testo mostrato nel placeholder. Se vuoto, non viene stampato nulla.
 * @param string $size             Dimensione immagine WordPress.
 */
function gaps_render_image( $attachment_id, $alt, $css_class = '', $placeholder_text = '', $size = 'large' ) {
	$attachment_id = absint( $attachment_id );

	if ( $attachment_id && wp_attachment_is_image( $attachment_id ) ) {
		// Delega al renderer condiviso (vedi gaps_render_attachment_image()):
		// ogni chiamata esistente a gaps_render_image() nei template guadagna
		// automaticamente width/height reali, srcset, sizes, loading="lazy" e
		// decoding="async", senza dover toccare i singoli template.
		$html = gaps_render_attachment_image(
			$attachment_id,
			$size,
			array(
				'class' => $css_class,
				'alt'   => $alt,
			)
		);
		if ( $html ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- già escapato da wp_get_attachment_image().
			return;
		}
	}

	if ( '' !== $placeholder_text ) {
		printf(
			'<div class="gaps-placeholder">%s</div>',
			esc_html( $placeholder_text )
		);
	}
}

/**
 * Stampa il pulsante CTA principale: apre sempre il popup di preordine
 * (mai un link diretto esterno). La destinazione finale (Stripe) viene
 * risolta lato JS/server dopo l'invio del modulo.
 *
 * @param string $label_html  Markup HTML dell'etichetta del pulsante (di fiducia, non da input utente).
 * @param string $extra_class Classe CSS aggiuntiva.
 * @param string $anchor_id   ID HTML opzionale per l'elemento.
 */
function gaps_render_cta_button( $label_html, $extra_class = '', $anchor_id = '' ) {
	$classes = trim( 'gaps-btn-primary gaps-open-preorder ' . $extra_class );
	$id_attr = $anchor_id ? ' id="' . esc_attr( $anchor_id ) . '"' : '';

	printf(
		'<button type="button" class="%1$s"%2$s>%3$s</button>',
		esc_attr( $classes ),
		$id_attr,
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
function gaps_build_whatsapp_url( $value ) {
	$value = trim( $value );
	if ( '' === $value ) {
		return '';
	}
	if ( 0 === strpos( $value, 'http' ) ) {
		return esc_url( $value );
	}
	$digits = preg_replace( '/[^0-9]/', '', $value );
	if ( '' === $digits ) {
		return '';
	}
	return esc_url( 'https://wa.me/' . $digits );
}

/**
 * Costruisce un URL Instagram valido a partire da un handle o da un URL completo.
 *
 * @param string $value Handle (con o senza @) o URL completo.
 * @return string
 */
function gaps_build_instagram_url( $value ) {
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
 * Restituisce l'URL della pagina "Condizioni di vendita": per default quella
 * generata automaticamente da questo plugin, oppure l'URL esterno impostato
 * manualmente nel pannello impostazioni (campo "Link legali") se quest'ultimo
 * è stato compilato, per compatibilità con configurazioni preesistenti.
 *
 * @return string
 */
function gaps_get_terms_url() {
	$internal = GAPS_Page_Manager::get_page_url( 'terms' );
	if ( $internal ) {
		return $internal;
	}
	$settings = gaps_get_settings();
	return $settings['terms_url'];
}

/**
 * Restituisce l'URL della pagina "Privacy": per default quella generata
 * automaticamente da questo plugin, oppure l'URL esterno impostato
 * manualmente nel pannello se quest'ultimo è stato compilato.
 *
 * @return string
 */
function gaps_get_privacy_url() {
	$internal = GAPS_Page_Manager::get_page_url( 'privacy' );
	if ( $internal ) {
		return $internal;
	}
	$settings = gaps_get_settings();
	return $settings['privacy_url'];
}

/**
 * Restituisce l'URL della pagina "Grazie — Ordine confermato": per default
 * quella generata automaticamente da questo plugin, oppure stringa vuota se
 * non ancora creata (es. subito dopo l'aggiornamento del plugin, prima di
 * disattivare/riattivare per generarla).
 *
 * @return string
 */
function gaps_get_thankyou_url() {
	return GAPS_Page_Manager::get_page_url( 'thankyou' );
}

/**
 * Restituisce l'URL della pagina del corso pratico Formalife ("Genitori
 * Pronti"), proposta come passo successivo facoltativo nella pagina "Grazie
 * — Ordine confermato". Stringa vuota se il campo è stato svuotato di
 * proposito nel pannello impostazioni (in quel caso la sezione di cross-sell
 * semplicemente non viene mostrata).
 *
 * @return string
 */
function gaps_get_course_url() {
	$settings = gaps_get_settings();
	$url      = trim( $settings['course_url'] );
	return $url ? esc_url( $url ) : '';
}

/**
 * Restituisce l'URL di un allegato della Libreria Media a prescindere dal
 * tipo di file (PDF incluso): a differenza di gaps_get_image_url(), non
 * richiede una "size" immagine e funziona quindi anche per i documenti.
 *
 * @param int $attachment_id ID dell'allegato.
 * @return string
 */
function gaps_get_attachment_file_url( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id ) {
		return '';
	}
	$url = wp_get_attachment_url( $attachment_id );
	return $url ? $url : '';
}

/**
 * Restituisce l'URL del PDF "I tuoi numeri importanti": quello caricato
 * nella Libreria Media dal pannello impostazioni se presente, altrimenti il
 * file incluso di serie nel plugin (sempre disponibile, anche subito dopo
 * l'installazione).
 *
 * @return string
 */
function gaps_get_numeri_pdf_url() {
	$settings = gaps_get_settings();
	$custom   = gaps_get_attachment_file_url( $settings['numeri_pdf_id'] );
	if ( $custom ) {
		return $custom;
	}
	return GAPS_PLUGIN_URL . 'assets/downloads/formalife-i-tuoi-numeri-importanti.pdf';
}

/**
 * Restituisce l'URL dell'immagine di anteprima della scheda "I tuoi numeri
 * importanti": quella caricata nella Libreria Media se presente, altrimenti
 * l'anteprima inclusa di serie nel plugin.
 *
 * @return string
 */
function gaps_get_numeri_preview_image_url() {
	$settings = gaps_get_settings();
	$custom   = gaps_get_image_url( $settings['numeri_preview_image_id'], 'large' );
	if ( $custom ) {
		return $custom;
	}
	return GAPS_PLUGIN_URL . 'assets/img/scheda-numeri-preview.jpg';
}

/**
 * Stampa l'immagine di anteprima della scheda "I tuoi numeri importanti":
 * se in Libreria Media è stata caricata una sovrascrittura
 * (numeri_preview_image_id), usa il renderer condiviso (width/height/
 * srcset reali). Altrimenti stampa il file incluso di serie nel plugin
 * (assets/img/scheda-numeri-preview.jpg, non è un allegato della Libreria
 * Media: le dimensioni sono note e fisse, dichiarate qui esplicitamente
 * per evitare comunque il layout shift). È l'immagine principale della
 * pagina "I tuoi numeri importanti", quindi sempre eager (mai lazy).
 */
function gaps_render_numeri_preview_image() {
	$settings      = gaps_get_settings();
	$attachment_id = absint( $settings['numeri_preview_image_id'] );
	$alt           = __( 'Anteprima della scheda I tuoi numeri importanti', 'guida-antipanico-soffocamento' );

	if ( $attachment_id && wp_attachment_is_image( $attachment_id ) ) {
		$html = gaps_render_attachment_image(
			$attachment_id,
			'large',
			array(
				'alt'      => $alt,
				'loading'  => 'eager',
				'decoding' => 'async',
			)
		);
		if ( $html ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- già escapato da wp_get_attachment_image().
			return;
		}
	}

	printf(
		'<img src="%1$s" width="900" height="1272" alt="%2$s" loading="eager" decoding="async" />',
		esc_url( GAPS_PLUGIN_URL . 'assets/img/scheda-numeri-preview.jpg' ),
		esc_attr( $alt )
	);
}

/**
 * Restituisce il link alla recensione Google impostato nel pannello, oppure
 * stringa vuota se il campo è stato svuotato di proposito (in quel caso la
 * sezione "richiesta recensione" della pagina "I tuoi numeri importanti"
 * semplicemente non viene mostrata).
 *
 * @return string
 */
function gaps_get_review_url() {
	$settings = gaps_get_settings();
	$url      = trim( $settings['review_url'] );
	return $url ? esc_url( $url ) : '';
}

/**
 * Stampa un valore testuale delle impostazioni "legali" evidenziandolo
 * visivamente se è ancora un segnaposto non compilato (vuoto, oppure
 * contenente una parentesi quadra "["). Usata nelle pagine Condizioni di
 * vendita e Privacy per rendere immediatamente visibili, direttamente sulla
 * pagina pubblicata, i dati aziendali ancora da completare nel pannello
 * impostazioni.
 *
 * @param string $value Valore già sanitizzato da stampare.
 */
function gaps_legal_field( $value ) {
	$value = (string) $value;

	if ( '' === $value || false !== strpos( $value, '[' ) ) {
		printf(
			'<mark class="gaps-legal-todo" title="%1$s">%2$s</mark>',
			esc_attr__( 'Dato da completare nel pannello impostazioni del plugin.', 'guida-antipanico-soffocamento' ),
			'' === $value ? esc_html__( '[da completare]', 'guida-antipanico-soffocamento' ) : esc_html( $value )
		);
		return;
	}

	echo esc_html( $value );
}
