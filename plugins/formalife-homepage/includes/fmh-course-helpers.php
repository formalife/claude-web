<?php
/**
 * Helper del Corso Anti-Panico al Soffocamento Pediatrico.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function fmh_course_default_faqs() {
	return array(
		array( 'question' => 'Posso partecipare anche se non so nulla di primo soccorso?', 'answer' => 'Sì. Il corso è pensato per genitori e caregiver e non richiede conoscenze sanitarie precedenti. Partiamo dalle basi e costruiamo il percorso passo dopo passo.' ),
		array( 'question' => 'Possono partecipare entrambi i genitori?', 'answer' => 'Assolutamente sì. Imparare insieme permette di condividere lo stesso modo di riconoscere e affrontare una situazione. Per questo l’offerta coppia è 120 € in due invece di 160 €.' ),
		array( 'question' => 'Il libro è incluso per ogni partecipante?', 'answer' => 'Sì. Ogni partecipante riceve la propria copia della Guida Anti-Panico al Soffocamento Pediatrico, anche nell’iscrizione di coppia.' ),
		array( 'question' => 'Fate provare davvero le manovre?', 'answer' => 'Sì. Ogni partecipante prova le tecniche e riceve indicazioni dall’istruttore. È anche per questo che ogni sessione è limitata a massimo 12 persone.' ),
		array( 'question' => 'È un corso BLSD?', 'answer' => 'No. Questo corso è dedicato specificamente al soffocamento pediatrico: prevenzione, riconoscimento, gestione dell’ostruzione e pratica delle manovre.' ),
		array( 'question' => 'Quanto dura?', 'answer' => 'Tre ore e mezza. Per ogni sessione fa fede l’orario mostrato nella relativa scheda.' ),
		array( 'question' => 'Devo portare qualcosa?', 'answer' => 'No. Formalife fornisce il materiale necessario per le esercitazioni e il libro incluso. Vestiti in modo comodo per partecipare alle prove pratiche.' ),
		array( 'question' => 'Cosa succede se non posso più partecipare?', 'answer' => 'Puoi utilizzare la Garanzia Serenità Formalife e le condizioni di vendita applicabili.' ),
		array( 'question' => 'Posso chiedere il rimborso?', 'answer' => 'Sì, secondo la Garanzia Serenità Formalife e le condizioni di vendita applicabili. La policy commerciale prevede rimborso integrale fino a 7 giorni prima; dopo tale soglia è previsto il mantenimento del valore come credito per una successiva edizione.' ),
		array( 'question' => 'Posso venire con un bambino piccolo?', 'answer' => 'Sì, ma quando possibile lo sconsigliamo: il corso richiede attenzione e comprende esercitazioni pratiche.' ),
		array( 'question' => 'È adatto anche a nonni e babysitter?', 'answer' => 'Sì. È pensato per chiunque abbia realmente la responsabilità di un bambino: genitori, nonni, babysitter e caregiver.' ),
		array( 'question' => 'Fate corsi solo a Brescia?', 'answer' => 'Al momento organizziamo i corsi principalmente nelle province di Brescia e Verona. Raccogliamo richieste anche da altre città per pianificare le prossime edizioni.' ),
		array( 'question' => 'Qual è l’età dei bambini a cui si riferisce?', 'answer' => 'Il percorso distingue in particolare tecniche e situazioni relative a lattante e bambino.' ),
	);
}

function fmh_course_default_sessions() {
	return array(
		array( 'id' => 'a', 'enabled' => 1, 'sales_open' => 0, 'date' => '2026-09-06', 'time' => '15:30–19:00', 'city' => 'Brescia', 'venue' => 'Sede da confermare', 'fallback' => 'Via Lamberti 4, Brescia', 'capacity' => 12, 'note' => '', 'single_price_cents' => 0, 'couple_price_cents' => 0, 'sort_order' => 10, 'admin_status' => 'programmata' ),
		array( 'id' => 'b', 'enabled' => 1, 'sales_open' => 0, 'date' => '2026-09-13', 'time' => '15:30–19:00', 'city' => 'Brescia', 'venue' => 'Sede da confermare', 'fallback' => 'Via Lamberti 4, Brescia', 'capacity' => 12, 'note' => '', 'single_price_cents' => 0, 'couple_price_cents' => 0, 'sort_order' => 20, 'admin_status' => 'programmata' ),
	);
}

function fmh_course_default_settings() {
	return array(
		'schema_version' => 4,
		'sales_enabled' => 0,
		'policy_verified_live' => 0,
		'course_name' => 'Corso Anti-Panico al Soffocamento Pediatrico',
		'hero_eyebrow' => 'Corso pratico a Brescia · Massimo 12 partecipanti',
		'hero_headline' => 'Quando tuo figlio tossisce, sapresti capire se devi intervenire — o se intervenire sarebbe proprio la cosa sbagliata?',
		'hero_subheadline' => 'Il Corso Anti-Panico al Soffocamento Pediatrico ti insegna a prevenire, riconoscere e gestire un’ostruzione delle vie aeree — e soprattutto ti permette di provare realmente le manovre su un manichino, con un istruttore che ti corregge e ti guida.',
		'hero_cta_label' => 'Scegli la data e prenota il tuo posto',
		'single_price_cents' => 8000,
		'couple_price_cents' => 12000,
		// Codici riservati per chi ha ricevuto un prezzo concordato prima del
		// lancio: uno per persona, monouso. I codici non vengono mai inviati al
		// browser, la verifica è solo lato server (FMH_Course_Orders::handle_check_code()).
		'discount_enabled' => 1,
		'discount_codes' => "pediastudio129\npediastudio266\npediastudio425\npediastudio618\npediastudio738\npediastudio764\npediastudio788\npediastudio838\npediastudio934\npediastudio947",
		'discount_single_use' => 1,
		'discount_single_price_cents' => 6500,
		'discount_couple_price_cents' => 11000,
		'discount_label' => 'Ho un codice riservato',
		'couple_label' => 'Sì, veniamo in due — aggiungi la seconda persona a soli 40 €',
		'couple_badge' => 'Super-offerta coppia',
		'couple_headline' => 'Se questa preparazione serve a entrambi, non vogliamo costringervi a scegliere chi deve imparare.',
		'couple_copy' => 'La seconda persona ti costa soltanto 40 €. Risparmi 40 € e potete affrontare insieme la stessa preparazione.',
		'book_included' => 1,
		'book_per_participant' => 1,
		'book_value' => '19,90 €',
		'duration_text' => '3 ore e mezza',
		'max_people_per_order' => 2,
		'sessions' => fmh_course_default_sessions(),
		'venue_note' => 'La sede definitiva a Brescia verrà comunicata ai partecipanti prima del corso.',
		'guarantee_enabled' => 1,
		'guarantee_text' => 'Cambio data gratuito fino a 24 ore prima. Rimborso integrale fino a 7 giorni prima; successivamente credito Formalife valido 12 mesi. Se Formalife annulla il corso puoi scegliere trasferimento o rimborso integrale.',
		'periodic_updates' => 1,
		'free_refresh' => 1,
		'free_refresh_text' => 'Refresh gratuito secondo modalità comunicate da Formalife.',
		'certificate_enabled' => 0,
		'certificate_text' => '',
		'letter_text' => '',
		'admission_text' => '',
		'book_copy' => '',
		'final_headline' => 'Il momento peggiore per imparare è quello in cui ne hai bisogno.',
		'final_text' => 'Quel momento non ti concederà il tempo di tornare indietro, cercare un video e imparare per la prima volta.',
		'final_kicker' => 'Quello che puoi fare è arrivarci avendo già provato.',
		'final_details' => 'Aver già riconosciuto la differenza. Aver già eseguito il movimento. Aver già ricevuto una correzione. Aver già costruito un piano mentale.',
		'final_cta_label' => 'Scegli la data e prenota il tuo posto',
		'mafalda_bio' => 'Pediatra di famiglia con oltre trent’anni di esperienza, Mafalda Camposarcone è la figura responsabile della direzione scientifica di Formalife.',
		'raffaele_bio' => 'Raffaele trasforma contenuti tecnici e linee guida in strumenti comprensibili e utilizzabili da genitori e caregiver.',
		'source_labels' => "ERC — European Resuscitation Council\nIRC — Italian Resuscitation Council\nILCOR — International Liaison Committee on Resuscitation",
		'faqs' => fmh_course_default_faqs(),
		'meta_title' => 'Corso Anti-Panico al Soffocamento Pediatrico a Brescia | Formalife',
		'meta_description' => 'Corso pratico a Brescia per prevenire, riconoscere e gestire il soffocamento pediatrico. Massimo 12 partecipanti, libro incluso.',
		'terms_url' => '', 'privacy_url' => '', 'notify_email' => '',
		'stripe_publishable_key' => '', 'stripe_secret_key' => '', 'stripe_webhook_secret' => '',
		'meta_dataset_id' => '', 'meta_access_token' => '',
		'hero_image_id' => 0, 'hero_mobile_image_id' => 0, 'letter_bg_image_id' => 0,
		'practice_image_id' => 0, 'practice_secondary_image_id' => 0,
		'mafalda_image_id' => 0, 'raffaele_image_id' => 0, 'book_image_id' => 0,
		'final_bg_image_id' => 0, 'final_mobile_bg_image_id' => 0, 'og_image_id' => 0,
		'timeline_image_ids' => array( 0, 0, 0, 0, 0 ), 'program_image_ids' => array( 0, 0, 0, 0, 0, 0 ),
		'hero_overlay_color' => '#123f4a', 'hero_overlay_opacity' => 35, 'hero_blur' => 0, 'hero_focal_x' => 50, 'hero_focal_y' => 50,
		'final_overlay_color' => '#0c3440', 'final_overlay_opacity' => 70, 'final_blur' => 0, 'final_focal_x' => 50, 'final_focal_y' => 50,
	);
}

function fmh_course_get_settings() {
	$saved = get_option( FMH_COURSE_OPTION_KEY, array() );
	$saved = is_array( $saved ) ? $saved : array();
	$out = array_replace( fmh_course_default_settings(), $saved );
	foreach ( array( 'sessions', 'faqs', 'timeline_image_ids', 'program_image_ids' ) as $collection ) {
		if ( isset( $saved[ $collection ] ) && is_array( $saved[ $collection ] ) ) { $out[ $collection ] = $saved[ $collection ]; }
	}
	return $out;
}

function fmh_course_maybe_migrate_settings() {
	$saved = get_option( FMH_COURSE_OPTION_KEY, array() );
	if ( ! is_array( $saved ) || isset( $saved['sessions'] ) ) { return; }
	$sessions = array();
	foreach ( array( 'a', 'b' ) as $i => $legacy_id ) {
		$sessions[] = array(
			'id' => $legacy_id,
			'enabled' => ! empty( $saved[ 'session_' . $legacy_id . '_enabled' ] ) ? 1 : 0,
			'sales_open' => 0,
			'date' => isset( $saved[ 'session_' . $legacy_id . '_date' ] ) ? $saved[ 'session_' . $legacy_id . '_date' ] : fmh_course_default_sessions()[ $i ]['date'],
			'time' => isset( $saved[ 'session_' . $legacy_id . '_time' ] ) ? $saved[ 'session_' . $legacy_id . '_time' ] : '15:30–19:00',
			'city' => isset( $saved[ 'session_' . $legacy_id . '_city' ] ) ? $saved[ 'session_' . $legacy_id . '_city' ] : 'Brescia',
			'venue' => isset( $saved[ 'session_' . $legacy_id . '_venue' ] ) ? $saved[ 'session_' . $legacy_id . '_venue' ] : 'Sede da confermare',
			'fallback' => isset( $saved[ 'session_' . $legacy_id . '_fallback' ] ) ? $saved[ 'session_' . $legacy_id . '_fallback' ] : 'Via Lamberti 4, Brescia',
			'capacity' => isset( $saved[ 'session_' . $legacy_id . '_capacity' ] ) ? absint( $saved[ 'session_' . $legacy_id . '_capacity' ] ) : 12,
			'note' => '', 'single_price_cents' => 0, 'couple_price_cents' => 0, 'sort_order' => ( $i + 1 ) * 10, 'admin_status' => 'migrata-v3',
		);
	}
	$saved['sessions'] = $sessions;
	$saved['schema_version'] = 4;
	$saved['single_price_cents'] = isset( $saved['price_cents'] ) ? absint( $saved['price_cents'] ) : 8000;
	$saved['couple_price_cents'] = 12000;
	$saved['sales_enabled'] = 0;
	update_option( FMH_COURSE_OPTION_KEY, $saved, false );
}
add_action( 'admin_init', 'fmh_course_maybe_migrate_settings', 5 );

function fmh_course_normalize_session( $session ) {
	$d = fmh_course_default_sessions()[0];
	$s = wp_parse_args( is_array( $session ) ? $session : array(), $d );
	$s['id'] = sanitize_key( $s['id'] );
	$s['enabled'] = ! empty( $s['enabled'] );
	$s['sales_open'] = ! empty( $s['sales_open'] );
	$s['capacity'] = max( 1, absint( $s['capacity'] ) );
	$s['sort_order'] = intval( $s['sort_order'] );
	$s['single_price_cents'] = absint( $s['single_price_cents'] );
	$s['couple_price_cents'] = absint( $s['couple_price_cents'] );
	return $s;
}

function fmh_course_get_sessions( $enabled_only = false ) {
	$s = fmh_course_get_settings();
	$out = array();
	foreach ( (array) $s['sessions'] as $session ) {
		$session = fmh_course_normalize_session( $session );
		if ( ! $session['id'] || ( $enabled_only && ! $session['enabled'] ) ) { continue; }
		$out[ $session['id'] ] = $session;
	}
	uasort( $out, function( $a, $b ) { return $a['sort_order'] <=> $b['sort_order']; } );
	return $out;
}

function fmh_course_get_session( $session_id ) {
	$sessions = fmh_course_get_sessions();
	$id = sanitize_key( $session_id );
	return isset( $sessions[ $id ] ) ? $sessions[ $id ] : null;
}

function fmh_course_get_price_cents( $session, $party_size ) {
	$s = fmh_course_get_settings();
	$party_size = 2 === absint( $party_size ) ? 2 : 1;
	$key = 2 === $party_size ? 'couple_price_cents' : 'single_price_cents';
	return ! empty( $session[ $key ] ) ? absint( $session[ $key ] ) : absint( $s[ $key ] );
}

/**
 * Normalizza un codice riservato per il confronto: spazi via, tutto minuscolo.
 *
 * @param string $code Codice inserito.
 * @return string
 */
function fmh_course_normalize_code( $code ) {
	return strtolower( trim( (string) $code ) );
}

/**
 * Elenco dei codici riservati autorizzati, normalizzati.
 *
 * @return string[]
 */
function fmh_course_get_discount_codes() {
	$s = fmh_course_get_settings();
	$raw = isset( $s['discount_codes'] ) ? (string) $s['discount_codes'] : '';

	// Compatibilità con la versione a codice singolo.
	if ( '' === trim( $raw ) && ! empty( $s['discount_code'] ) ) {
		$raw = (string) $s['discount_code'];
	}

	$codes = array();
	foreach ( preg_split( '/[\r\n,]+/', $raw ) as $line ) {
		$code = fmh_course_normalize_code( $line );
		if ( '' !== $code ) {
			$codes[ $code ] = true;
		}
	}
	return array_keys( $codes );
}

/**
 * Registro dei codici già utilizzati: codice => ID dell'ordine che l'ha usato.
 *
 * @return array<string,int>
 */
function fmh_course_get_used_codes() {
	$used = get_option( 'fmh_course_used_codes', array() );
	return is_array( $used ) ? $used : array();
}

/**
 * Segna un codice come utilizzato da un ordine. Idempotente: se il codice
 * risulta già speso da un altro ordine, non viene sovrascritto.
 *
 * @param string $code     Codice usato.
 * @param int    $order_id Ordine che l'ha consumato.
 */
function fmh_course_mark_code_used( $code, $order_id ) {
	$code = fmh_course_normalize_code( $code );
	if ( '' === $code ) {
		return;
	}
	$used = fmh_course_get_used_codes();
	if ( isset( $used[ $code ] ) && absint( $used[ $code ] ) !== absint( $order_id ) ) {
		return;
	}
	$used[ $code ] = absint( $order_id );
	update_option( 'fmh_course_used_codes', $used, false );
}

/**
 * Verifica se un codice riservato è valido e ancora spendibile.
 *
 * I codici sono monouso: vengono consumati solo a pagamento confermato, così
 * un tentativo fallito non brucia il codice della persona.
 *
 * @param string $code     Codice inserito dall'utente.
 * @param int    $order_id Ordine corrente, se già creato (per non invalidare
 *                         il codice che quell'ordine sta già usando).
 * @return bool
 */
function fmh_course_code_is_valid( $code, $order_id = 0 ) {
	$s = fmh_course_get_settings();
	if ( empty( $s['discount_enabled'] ) ) {
		return false;
	}

	$code = fmh_course_normalize_code( $code );
	if ( '' === $code ) {
		return false;
	}

	$authorized = false;
	foreach ( fmh_course_get_discount_codes() as $known ) {
		if ( hash_equals( $known, $code ) ) {
			$authorized = true;
			break;
		}
	}
	if ( ! $authorized ) {
		return false;
	}

	if ( empty( $s['discount_single_use'] ) ) {
		return true;
	}

	$used = fmh_course_get_used_codes();
	if ( ! isset( $used[ $code ] ) ) {
		return true;
	}
	return absint( $used[ $code ] ) === absint( $order_id );
}

/**
 * Prezzo effettivo in centesimi, tenendo conto dell'eventuale codice riservato.
 *
 * È l'unico punto in cui si decide quanto pagare: viene usato sia per il
 * riepilogo mostrato nel popup sia per l'importo del PaymentIntent, così il
 * prezzo non dipende mai da quello che arriva dal browser.
 *
 * @param array  $session    Sessione.
 * @param int    $party_size 1 o 2.
 * @param string $code       Codice riservato eventualmente inserito.
 * @return int
 */
function fmh_course_resolve_price_cents( $session, $party_size, $code = '', $order_id = 0 ) {
	$party_size = 2 === absint( $party_size ) ? 2 : 1;
	if ( '' !== trim( (string) $code ) && fmh_course_code_is_valid( $code, $order_id ) ) {
		$s   = fmh_course_get_settings();
		$key = 2 === $party_size ? 'discount_couple_price_cents' : 'discount_single_price_cents';
		$cents = absint( $s[ $key ] );
		if ( $cents > 0 ) {
			return $cents;
		}
	}
	return fmh_course_get_price_cents( $session, $party_size );
}

function fmh_get_course_page_url() { return class_exists( 'FMH_Page_Manager' ) ? FMH_Page_Manager::get_page_url( 'course' ) : ''; }
function fmh_get_course_thankyou_url() { return class_exists( 'FMH_Page_Manager' ) ? FMH_Page_Manager::get_page_url( 'course_thankyou' ) : ''; }
function fmh_course_format_price( $cents ) { return number_format_i18n( max( 0, absint( $cents ) ) / 100, 2 ) . ' €'; }
function fmh_course_format_date( $date ) { $ts = strtotime( $date . ' 12:00:00' ); return $ts ? wp_date( 'l j F Y', $ts ) : $date; }

function fmh_course_get_privacy_url() {
	$c = fmh_course_get_settings(); if ( ! empty( $c['privacy_url'] ) ) { return $c['privacy_url']; }
	$h = fmh_get_settings(); return isset( $h['privacy_url'] ) ? $h['privacy_url'] : '';
}
function fmh_course_get_terms_url() {
	$c = fmh_course_get_settings(); if ( ! empty( $c['terms_url'] ) ) { return $c['terms_url']; }
	$h = fmh_get_settings(); return isset( $h['terms_url'] ) ? $h['terms_url'] : '';
}
function fmh_course_get_notify_email() {
	$c = fmh_course_get_settings(); if ( ! empty( $c['notify_email'] ) && is_email( $c['notify_email'] ) ) { return $c['notify_email']; }
	$h = fmh_get_settings(); return ! empty( $h['notify_email'] ) && is_email( $h['notify_email'] ) ? $h['notify_email'] : get_option( 'admin_email' );
}
function fmh_course_stripe_mode() {
	$s = fmh_course_get_settings(); $pk = trim( $s['stripe_publishable_key'] ); $sk = trim( $s['stripe_secret_key'] );
	if ( ! $pk || ! $sk ) { return 'empty'; }
	if ( 0 === strpos( $pk, 'pk_test_' ) && 0 === strpos( $sk, 'sk_test_' ) ) { return 'test'; }
	if ( 0 === strpos( $pk, 'pk_live_' ) && 0 === strpos( $sk, 'sk_live_' ) ) { return 'live'; }
	return 'mismatch';
}
function fmh_course_readiness_checks() {
	$s = fmh_course_get_settings(); $mode = fmh_course_stripe_mode(); $has_open = false;
	foreach ( fmh_course_get_sessions() as $session ) { if ( $session['enabled'] && $session['sales_open'] ) { $has_open = true; break; } }
	return array(
		'Sessione attiva con vendite aperte' => $has_open,
		'Prezzi singolo e coppia' => absint( $s['single_price_cents'] ) > 0 && absint( $s['couple_price_cents'] ) > 0,
		'Privacy' => (bool) fmh_course_get_privacy_url(), 'Termini' => (bool) fmh_course_get_terms_url(),
		'Garanzia commerciale configurata' => ! empty( $s['guarantee_enabled'] ) && '' !== trim( $s['guarantee_text'] ),
		'Termini/policy verificati per vendite live' => ! empty( $s['policy_verified_live'] ),
		'Chiavi Stripe' => (bool) ( trim( $s['stripe_publishable_key'] ) && trim( $s['stripe_secret_key'] ) ),
		'Modalità Stripe coerente' => in_array( $mode, array( 'test', 'live' ), true ), 'Webhook Stripe' => '' !== trim( $s['stripe_webhook_secret'] ),
		'Vendite abilitate' => ! empty( $s['sales_enabled'] ),
	);
}
function fmh_course_checkout_ready() {
	$s = fmh_course_get_settings(); $checks = fmh_course_readiness_checks(); $mode = fmh_course_stripe_mode();
	$required = array( 'Sessione attiva con vendite aperte', 'Prezzi singolo e coppia', 'Privacy', 'Termini', 'Garanzia commerciale configurata', 'Chiavi Stripe', 'Modalità Stripe coerente', 'Webhook Stripe', 'Vendite abilitate' );
	foreach ( $required as $key ) { if ( empty( $checks[ $key ] ) ) { return false; } }
	return 'live' !== $mode || ! empty( $s['policy_verified_live'] );
}
