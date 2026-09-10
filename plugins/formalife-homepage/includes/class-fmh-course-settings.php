<?php
/** Dashboard del Corso Anti-Panico. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class FMH_Course_Settings {
	const SETTINGS_GROUP = 'fmh_course_settings_group';
	const MENU_SLUG = 'formalife-course';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ), 8 );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}
	public static function add_menu() {
		add_submenu_page( FMH_SLUG, 'Corso Anti-Panico', 'Corso Anti-Panico', 'manage_options', self::MENU_SLUG, array( __CLASS__, 'render_page' ) );
	}
	public static function register_settings() {
		register_setting( self::SETTINGS_GROUP, FMH_COURSE_OPTION_KEY, array( 'type' => 'array', 'sanitize_callback' => array( __CLASS__, 'sanitize' ), 'default' => fmh_course_default_settings() ) );
	}
	private static function money_to_cents( $value, $fallback ) {
		$value = str_replace( ',', '.', sanitize_text_field( wp_unslash( $value ) ) );
		return '' === $value ? absint( $fallback ) : max( 50, (int) round( (float) $value * 100 ) );
	}
	public static function sanitize( $input ) {
		$d = fmh_course_default_settings(); $current = fmh_course_get_settings(); $input = is_array( $input ) ? $input : array(); $out = $current;
		foreach ( array( 'sales_enabled', 'policy_verified_live', 'book_included', 'book_per_participant', 'guarantee_enabled', 'periodic_updates', 'free_refresh', 'certificate_enabled', 'discount_enabled', 'discount_single_use' ) as $key ) { $out[ $key ] = ! empty( $input[ $key ] ) ? 1 : 0; }
		foreach ( array( 'course_name', 'hero_eyebrow', 'hero_cta_label', 'couple_label', 'couple_badge', 'couple_headline', 'book_value', 'duration_text', 'free_refresh_text', 'certificate_text', 'final_headline', 'final_kicker', 'final_cta_label', 'meta_title', 'discount_label' ) as $key ) { $out[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : $d[ $key ]; }
		foreach ( array( 'hero_headline', 'hero_subheadline', 'couple_copy', 'venue_note', 'guarantee_text', 'letter_text', 'admission_text', 'book_copy', 'final_text', 'final_details', 'mafalda_bio', 'raffaele_bio', 'source_labels', 'meta_description', 'discount_codes' ) as $key ) { $out[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( wp_unslash( $input[ $key ] ) ) : $d[ $key ]; }
		$out['single_price_cents'] = self::money_to_cents( isset( $input['single_price_eur'] ) ? $input['single_price_eur'] : '', $d['single_price_cents'] );
		$out['couple_price_cents'] = self::money_to_cents( isset( $input['couple_price_eur'] ) ? $input['couple_price_eur'] : '', $d['couple_price_cents'] );
		$out['discount_single_price_cents'] = self::money_to_cents( isset( $input['discount_single_price_eur'] ) ? $input['discount_single_price_eur'] : '', $d['discount_single_price_cents'] );
		$out['discount_couple_price_cents'] = self::money_to_cents( isset( $input['discount_couple_price_eur'] ) ? $input['discount_couple_price_eur'] : '', $d['discount_couple_price_cents'] );
		$out['max_people_per_order'] = 2;
		$out['schema_version'] = 4;

		$out['sessions'] = array();
		foreach ( isset( $input['sessions'] ) && is_array( $input['sessions'] ) ? $input['sessions'] : array() as $row ) {
			if ( ! is_array( $row ) ) { continue; }
			$id = sanitize_key( isset( $row['id'] ) ? wp_unslash( $row['id'] ) : '' );
			if ( ! $id ) { $id = 'session-' . wp_generate_uuid4(); }
			$date = isset( $row['date'] ) ? sanitize_text_field( wp_unslash( $row['date'] ) ) : '';
			if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) { continue; }
			$out['sessions'][] = array(
				'id' => $id, 'enabled' => ! empty( $row['enabled'] ) ? 1 : 0, 'sales_open' => ! empty( $row['sales_open'] ) ? 1 : 0,
				'date' => $date, 'time' => sanitize_text_field( wp_unslash( isset( $row['time'] ) ? $row['time'] : '' ) ),
				'city' => sanitize_text_field( wp_unslash( isset( $row['city'] ) ? $row['city'] : '' ) ), 'venue' => sanitize_text_field( wp_unslash( isset( $row['venue'] ) ? $row['venue'] : '' ) ),
				'fallback' => sanitize_text_field( wp_unslash( isset( $row['fallback'] ) ? $row['fallback'] : '' ) ), 'capacity' => max( 1, min( 100, absint( isset( $row['capacity'] ) ? $row['capacity'] : 12 ) ) ),
				'note' => sanitize_textarea_field( wp_unslash( isset( $row['note'] ) ? $row['note'] : '' ) ),
				'single_price_cents' => self::money_to_cents( isset( $row['single_price_eur'] ) ? $row['single_price_eur'] : '', 0 ),
				'couple_price_cents' => self::money_to_cents( isset( $row['couple_price_eur'] ) ? $row['couple_price_eur'] : '', 0 ),
				'sort_order' => intval( isset( $row['sort_order'] ) ? $row['sort_order'] : 0 ), 'admin_status' => sanitize_text_field( wp_unslash( isset( $row['admin_status'] ) ? $row['admin_status'] : '' ) ),
			);
		}
		if ( ! $out['sessions'] ) { $out['sessions'] = $current['sessions']; }

		$media = array( 'hero_image_id', 'hero_mobile_image_id', 'letter_bg_image_id', 'practice_image_id', 'practice_secondary_image_id', 'mafalda_image_id', 'raffaele_image_id', 'book_image_id', 'final_bg_image_id', 'final_mobile_bg_image_id', 'og_image_id' );
		foreach ( $media as $key ) { $out[ $key ] = isset( $input[ $key ] ) ? absint( $input[ $key ] ) : 0; }
		foreach ( array( 'timeline_image_ids' => 5, 'program_image_ids' => 6 ) as $key => $count ) { $out[ $key ] = array(); for ( $i = 0; $i < $count; $i++ ) { $out[ $key ][] = isset( $input[ $key ][ $i ] ) ? absint( $input[ $key ][ $i ] ) : 0; } }
		foreach ( array( 'hero_overlay_opacity', 'final_overlay_opacity', 'hero_focal_x', 'hero_focal_y', 'final_focal_x', 'final_focal_y' ) as $key ) { $out[ $key ] = min( 100, absint( isset( $input[ $key ] ) ? $input[ $key ] : $d[ $key ] ) ); }
		foreach ( array( 'hero_blur', 'final_blur' ) as $key ) { $out[ $key ] = min( 8, absint( isset( $input[ $key ] ) ? $input[ $key ] : 0 ) ); }
		foreach ( array( 'hero_overlay_color', 'final_overlay_color' ) as $key ) { $out[ $key ] = sanitize_hex_color( isset( $input[ $key ] ) ? $input[ $key ] : '' ) ?: $d[ $key ]; }

		$out['faqs'] = array();
		$faq_text = isset( $input['faqs_text'] ) ? wp_unslash( $input['faqs_text'] ) : '';
		foreach ( preg_split( '/\r?\n/', $faq_text ) as $line ) { $parts = array_map( 'trim', explode( '|', $line, 2 ) ); if ( 2 === count( $parts ) && $parts[0] && $parts[1] ) { $out['faqs'][] = array( 'question' => sanitize_text_field( $parts[0] ), 'answer' => sanitize_textarea_field( $parts[1] ) ); } }
		if ( ! $out['faqs'] ) { $out['faqs'] = $current['faqs']; }
		foreach ( array( 'terms_url', 'privacy_url' ) as $key ) { $out[ $key ] = isset( $input[ $key ] ) ? esc_url_raw( trim( wp_unslash( $input[ $key ] ) ) ) : ''; }
		$out['notify_email'] = isset( $input['notify_email'] ) ? sanitize_email( wp_unslash( $input['notify_email'] ) ) : '';
		$out['stripe_publishable_key'] = isset( $input['stripe_publishable_key'] ) ? sanitize_text_field( wp_unslash( $input['stripe_publishable_key'] ) ) : $current['stripe_publishable_key'];
		foreach ( array( 'stripe_secret_key', 'stripe_webhook_secret' ) as $key ) { $out[ $key ] = isset( $input[ $key ] ) && '' !== trim( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : $current[ $key ]; }
		$out['meta_dataset_id'] = isset( $input['meta_dataset_id'] ) ? preg_replace( '/\D+/', '', sanitize_text_field( wp_unslash( $input['meta_dataset_id'] ) ) ) : $current['meta_dataset_id'];
		$out['meta_access_token'] = isset( $input['meta_access_token'] ) && '' !== trim( $input['meta_access_token'] ) ? sanitize_text_field( wp_unslash( $input['meta_access_token'] ) ) : $current['meta_access_token'];
		if ( 0 === strpos( $out['stripe_publishable_key'], 'pk_live_' ) && empty( $out['policy_verified_live'] ) ) { $out['sales_enabled'] = 0; add_settings_error( FMH_COURSE_OPTION_KEY, 'fmh_live_blocked', 'Vendite LIVE non abilitate: verifica Termini/policy mancante.', 'error' ); }
		return $out;
	}

	private static function image_field( $key, $value, $label ) {
		$id = 'fmh_course_' . $key; $url = $value ? fmh_get_image_url( $value, 'medium' ) : '';
		?><div class="fmh-image-field"><label><strong><?php echo esc_html( $label ); ?></strong></label><div class="fmh-image-preview" id="<?php echo esc_attr( $id ); ?>_preview"><?php if ( $url ) : ?><img src="<?php echo esc_url( $url ); ?>" alt=""><?php endif; ?></div><input class="fmh-image-id-input" type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( FMH_COURSE_OPTION_KEY . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $value ); ?>"><button type="button" class="button fmh-choose-image">Scegli</button> <button type="button" class="button fmh-remove-image">Rimuovi</button></div><?php
	}
	private static function render_control() {
		?><h2>Course Control</h2><div class="fmh-course-control"><?php foreach ( fmh_course_get_sessions() as $session ) : $stats = FMH_Course_Orders::get_session_stats( $session['id'] ); ?>
		<div class="fmh-course-control-card"><h3><?php echo esc_html( fmh_course_format_date( $session['date'] ) ); ?></h3><p><?php echo esc_html( $session['city'] . ' · ' . $session['time'] ); ?></p><p><strong><?php echo esc_html( $stats['paid'] ); ?></strong> pagati · <strong><?php echo esc_html( $stats['pending'] ); ?></strong> pending · <strong><?php echo esc_html( max( 0, $session['capacity'] - $stats['paid'] - $stats['pending'] ) ); ?></strong> disponibili</p><p><?php echo esc_html( $stats['paid_orders'] ); ?> ordini · <?php echo esc_html( $stats['single_orders'] ); ?> singoli · <?php echo esc_html( $stats['couple_orders'] ); ?> coppie · <?php echo esc_html( fmh_course_format_price( $stats['gross_cents'] ) ); ?></p></div>
		<?php endforeach; ?></div><?php
	}
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) { return; } $s = fmh_course_get_settings(); $checks = fmh_course_readiness_checks();
		?><div class="wrap fmh-settings-wrap"><h1>Corso Anti-Panico — Dashboard</h1><p>Gestisci offerta, sessioni, contenuti e media senza modificare il template.</p><div class="notice <?php echo fmh_course_checkout_ready() ? 'notice-success' : 'notice-warning'; ?> inline"><p><strong><?php echo fmh_course_checkout_ready() ? 'Checkout pronto' : 'Checkout non pronto'; ?></strong> · modalità Stripe: <?php echo esc_html( strtoupper( fmh_course_stripe_mode() ) ); ?></p><ul><?php foreach ( $checks as $label => $ok ) : ?><li><?php echo $ok ? '✓' : '•'; ?> <?php echo esc_html( $label ); ?></li><?php endforeach; ?></ul></div>
		<?php self::render_control(); ?><form method="post" action="options.php"><?php settings_fields( self::SETTINGS_GROUP ); ?>
		<h2>Offerta globale</h2><table class="form-table"><tr><th>Vendite</th><td><label><input type="checkbox" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[sales_enabled]" value="1" <?php checked( $s['sales_enabled'] ); ?>> Abilita checkout</label><br><label><input type="checkbox" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[policy_verified_live]" value="1" <?php checked( $s['policy_verified_live'] ); ?>> Termini/policy verificati per vendite live</label></td></tr>
		<?php foreach ( array( 'course_name' => 'Nome corso', 'hero_eyebrow' => 'Eyebrow', 'hero_headline' => 'Headline', 'hero_subheadline' => 'Subheadline', 'hero_cta_label' => 'CTA hero', 'couple_label' => 'Checkbox coppia', 'couple_badge' => 'Badge coppia', 'couple_headline' => 'Headline coppia', 'couple_copy' => 'Copy coppia', 'duration_text' => 'Durata', 'book_value' => 'Valore libro', 'final_headline' => 'Headline finale', 'final_text' => 'Copy finale', 'final_kicker' => 'Enfasi finale', 'final_details' => 'Dettagli finali', 'final_cta_label' => 'CTA finale' ) as $key => $label ) : ?><tr><th><?php echo esc_html( $label ); ?></th><td><?php if ( in_array( $key, array( 'hero_headline', 'hero_subheadline', 'couple_copy', 'final_text', 'final_details' ), true ) ) : ?><textarea class="large-text" rows="3" name="<?php echo FMH_COURSE_OPTION_KEY . '[' . esc_attr( $key ) . ']'; ?>"><?php echo esc_textarea( $s[ $key ] ); ?></textarea><?php else : ?><input class="large-text" name="<?php echo FMH_COURSE_OPTION_KEY . '[' . esc_attr( $key ) . ']'; ?>" value="<?php echo esc_attr( $s[ $key ] ); ?>"><?php endif; ?></td></tr><?php endforeach; ?>
		<tr><th>Prezzi</th><td>Singolo € <input type="number" step=".01" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[single_price_eur]" value="<?php echo esc_attr( number_format( $s['single_price_cents'] / 100, 2, '.', '' ) ); ?>"> Coppia € <input type="number" step=".01" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[couple_price_eur]" value="<?php echo esc_attr( number_format( $s['couple_price_cents'] / 100, 2, '.', '' ) ); ?>"></td></tr>
		<tr><th>Codici riservati</th><td>
			<label><input type="checkbox" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[discount_enabled]" value="1" <?php checked( $s['discount_enabled'] ); ?>> Attiva il campo codice nel checkout</label><br>
			<label><input type="checkbox" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[discount_single_use]" value="1" <?php checked( $s['discount_single_use'] ); ?>> Ogni codice vale per una sola iscrizione</label><br>
			<p><strong>Codici autorizzati</strong> — uno per riga:</p>
			<textarea rows="10" class="large-text code" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[discount_codes]"><?php echo esc_textarea( $s['discount_codes'] ); ?></textarea>
			<p>Prezzi con codice: singolo € <input type="number" step=".01" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[discount_single_price_eur]" value="<?php echo esc_attr( number_format( $s['discount_single_price_cents'] / 100, 2, '.', '' ) ); ?>">
			coppia € <input type="number" step=".01" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[discount_couple_price_eur]" value="<?php echo esc_attr( number_format( $s['discount_couple_price_cents'] / 100, 2, '.', '' ) ); ?>"></p>
			<p>Etichetta della spunta <input type="text" class="large-text" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[discount_label]" value="<?php echo esc_attr( $s['discount_label'] ); ?>"></p>
			<?php
			$codes = fmh_course_get_discount_codes();
			$used  = fmh_course_get_used_codes();
			if ( $codes ) :
				?>
				<p><strong>Stato dei codici</strong></p>
				<table class="widefat striped" style="max-width:640px"><thead><tr><th>Codice</th><th>Stato</th><th></th></tr></thead><tbody>
				<?php foreach ( $codes as $code ) :
					$order_id = isset( $used[ $code ] ) ? absint( $used[ $code ] ) : 0;
					?>
					<tr>
						<td><code><?php echo esc_html( $code ); ?></code></td>
						<td>
							<?php if ( $order_id ) : ?>
								Usato — <a href="<?php echo esc_url( get_edit_post_link( $order_id ) ); ?>">iscrizione #<?php echo esc_html( $order_id ); ?></a>
							<?php else : ?>
								Libero
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $order_id ) : ?>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=' . FMH_Course_Orders::FREE_CODE_ACTION . '&code=' . rawurlencode( $code ) ), FMH_Course_Orders::FREE_CODE_ACTION ) ); ?>">Libera</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endif; ?>
			<p class="description">I codici non compaiono mai nel sorgente della pagina: vengono verificati solo lato server e il prezzo finale è sempre ricalcolato al momento del pagamento. Un codice viene marcato come usato solo a pagamento confermato, così un tentativo fallito non lo brucia.</p>
		</td></tr>
		<tr><th>Inclusioni</th><td><label><input type="checkbox" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[book_included]" value="1" <?php checked( $s['book_included'] ); ?>> Libro incluso</label> <label><input type="checkbox" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[book_per_participant]" value="1" <?php checked( $s['book_per_participant'] ); ?>> Una copia per partecipante</label></td></tr></table>
		<h2>Sessioni dinamiche</h2><p>Gli ID <code>a</code>/<code>b</code> restano validi per gli ordini legacy. Per nuove sessioni usa un ID stabile.</p><div id="fmh-course-sessions"><?php foreach ( $s['sessions'] as $i => $session ) : self::render_session_row( $i, $session ); endforeach; ?></div><button type="button" class="button" id="fmh-add-session">Aggiungi sessione</button>
		<h2>Contenuti e readiness</h2><table class="form-table"><tr><th>Nota sede</th><td><textarea class="large-text" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[venue_note]"><?php echo esc_textarea( $s['venue_note'] ); ?></textarea></td></tr><tr><th>Garanzia Serenità</th><td><label><input type="checkbox" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[guarantee_enabled]" value="1" <?php checked( $s['guarantee_enabled'] ); ?>> Mostra garanzia commerciale</label><textarea rows="4" class="large-text" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[guarantee_text]"><?php echo esc_textarea( $s['guarantee_text'] ); ?></textarea></td></tr>
		<?php foreach ( array( 'letter_text' => 'Lettera (override opzionale)', 'admission_text' => 'Quello che non promettiamo (override)', 'book_copy' => 'Copy libro (override)', 'mafalda_bio' => 'Bio Mafalda', 'raffaele_bio' => 'Bio Raffaele', 'source_labels' => 'Fonti scientifiche (una per riga)' ) as $key => $label ) : ?><tr><th><?php echo esc_html( $label ); ?></th><td><textarea rows="4" class="large-text" name="<?php echo FMH_COURSE_OPTION_KEY . '[' . $key . ']'; ?>"><?php echo esc_textarea( $s[ $key ] ); ?></textarea></td></tr><?php endforeach; ?>
		<tr><th>Dopo il corso</th><td><label><input type="checkbox" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[periodic_updates]" value="1" <?php checked( $s['periodic_updates'] ); ?>> Aggiornamenti periodici</label><br><label><input type="checkbox" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[free_refresh]" value="1" <?php checked( $s['free_refresh'] ); ?>> Refresh gratuito</label> <input class="regular-text" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[free_refresh_text]" value="<?php echo esc_attr( $s['free_refresh_text'] ); ?>"><br><label><input type="checkbox" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[certificate_enabled]" value="1" <?php checked( $s['certificate_enabled'] ); ?>> Attestato</label> <input class="regular-text" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[certificate_text]" value="<?php echo esc_attr( $s['certificate_text'] ); ?>"></td></tr>
		<tr><th>FAQ</th><td><textarea rows="12" class="large-text code" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[faqs_text]"><?php foreach ( $s['faqs'] as $faq ) { echo esc_textarea( $faq['question'] . ' | ' . $faq['answer'] ) . "\n"; } ?></textarea><p>Una FAQ per riga: domanda | risposta. La FAQ attestato viene aggiunta solo se configurata.</p></td></tr><tr><th>Privacy / Termini</th><td><input type="url" class="large-text" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[privacy_url]" value="<?php echo esc_attr( $s['privacy_url'] ); ?>" placeholder="Privacy URL"><br><input type="url" class="large-text" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[terms_url]" value="<?php echo esc_attr( $s['terms_url'] ); ?>" placeholder="Termini URL"></td></tr></table>
		<h2>Media</h2><div class="fmh-media-grid"><?php foreach ( array( 'hero_image_id'=>'Hero desktop','hero_mobile_image_id'=>'Hero mobile','letter_bg_image_id'=>'Lettera/background','practice_image_id'=>'Pratica 1','practice_secondary_image_id'=>'Pratica 2','mafalda_image_id'=>'Mafalda','raffaele_image_id'=>'Raffaele','book_image_id'=>'Libro','final_bg_image_id'=>'Finale desktop','final_mobile_bg_image_id'=>'Finale mobile','og_image_id'=>'OG image' ) as $key=>$label ) { self::image_field( $key, $s[$key], $label ); } foreach ( $s['timeline_image_ids'] as $i=>$id ) { self::image_field( 'timeline_image_ids]['.$i, $id, 'Timeline '.($i+1) ); } foreach ( $s['program_image_ids'] as $i=>$id ) { self::image_field( 'program_image_ids]['.$i, $id, 'Programma '.($i+1) ); } ?></div><table class="form-table"><tr><th>Hero immagine</th><td>Overlay <input class="fmh-color-field" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[hero_overlay_color]" value="<?php echo esc_attr($s['hero_overlay_color']); ?>"> Opacità <input type="number" min="0" max="100" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[hero_overlay_opacity]" value="<?php echo esc_attr($s['hero_overlay_opacity']); ?>"> Blur px <input type="number" min="0" max="8" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[hero_blur]" value="<?php echo esc_attr($s['hero_blur']); ?>"> Focale X/Y <input type="number" min="0" max="100" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[hero_focal_x]" value="<?php echo esc_attr($s['hero_focal_x']); ?>"> <input type="number" min="0" max="100" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[hero_focal_y]" value="<?php echo esc_attr($s['hero_focal_y']); ?>"></td></tr><tr><th>Background finale</th><td>Overlay <input class="fmh-color-field" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[final_overlay_color]" value="<?php echo esc_attr($s['final_overlay_color']); ?>"> Opacità <input type="number" min="0" max="100" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[final_overlay_opacity]" value="<?php echo esc_attr($s['final_overlay_opacity']); ?>"> Blur px <input type="number" min="0" max="8" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[final_blur]" value="<?php echo esc_attr($s['final_blur']); ?>"> Focale X/Y <input type="number" min="0" max="100" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[final_focal_x]" value="<?php echo esc_attr($s['final_focal_x']); ?>"> <input type="number" min="0" max="100" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[final_focal_y]" value="<?php echo esc_attr($s['final_focal_y']); ?>"></td></tr></table>
		<h2>Stripe</h2><table class="form-table"><tr><th>Publishable key</th><td><input class="large-text code" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[stripe_publishable_key]" value="<?php echo esc_attr( $s['stripe_publishable_key'] ); ?>"></td></tr><tr><th>Secret key</th><td><input type="password" class="large-text code" autocomplete="new-password" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[stripe_secret_key]" placeholder="Lascia vuoto per mantenere"></td></tr><tr><th>Webhook secret</th><td><input type="password" class="large-text code" autocomplete="new-password" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[stripe_webhook_secret]" placeholder="Lascia vuoto per mantenere"><p><code><?php echo esc_html( FMH_Course_Stripe_Webhook::get_endpoint_url() ); ?></code></p></td></tr><tr><th>Email notifiche</th><td><input type="email" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[notify_email]" value="<?php echo esc_attr( $s['notify_email'] ); ?>"></td></tr></table>
		<h2>Meta Conversions API</h2><table class="form-table"><tr><th>Dataset / Pixel ID</th><td><input class="large-text code" inputmode="numeric" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[meta_dataset_id]" value="<?php echo esc_attr( $s['meta_dataset_id'] ); ?>"><p class="description">Usato solo per il Purchase server-side del corso dopo pagamento Stripe confermato.</p></td></tr><tr><th>Access token CAPI</th><td><input type="password" class="large-text code" autocomplete="new-password" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[meta_access_token]" placeholder="Lascia vuoto per mantenere"><p class="description">Il token non viene mai stampato nella pagina pubblica. Se manca il cookie Meta _fbp, il Purchase server-side non viene inviato.</p></td></tr></table>
		<h2>SEO</h2><table class="form-table"><tr><th>Title</th><td><input class="large-text" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[meta_title]" value="<?php echo esc_attr( $s['meta_title'] ); ?>"></td></tr><tr><th>Description</th><td><textarea class="large-text" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[meta_description]"><?php echo esc_textarea( $s['meta_description'] ); ?></textarea></td></tr></table><?php submit_button( 'Salva impostazioni corso' ); ?></form></div><?php
	}
	private static function render_session_row( $i, $session ) { ?>
		<fieldset class="fmh-session-row"><legend>Sessione</legend><input type="hidden" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[sessions][<?php echo $i; ?>][id]" value="<?php echo esc_attr( $session['id'] ); ?>"><label><input type="checkbox" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[sessions][<?php echo $i; ?>][enabled]" value="1" <?php checked( $session['enabled'] ); ?>> Attiva</label> <label><input type="checkbox" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[sessions][<?php echo $i; ?>][sales_open]" value="1" <?php checked( $session['sales_open'] ); ?>> Vendite aperte</label><br>
		<?php foreach ( array( 'date'=>'Data','time'=>'Orario','city'=>'Città','venue'=>'Sede pubblica','fallback'=>'Fallback interno','capacity'=>'Capienza','sort_order'=>'Ordine','admin_status'=>'Stato admin' ) as $key=>$label ) : ?><label><?php echo esc_html( $label ); ?> <input type="<?php echo 'date'===$key?'date':(in_array($key,array('capacity','sort_order'),true)?'number':'text'); ?>" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[sessions][<?php echo $i; ?>][<?php echo $key; ?>]" value="<?php echo esc_attr( $session[$key] ); ?>"></label> <?php endforeach; ?><br><label>Nota <textarea name="<?php echo FMH_COURSE_OPTION_KEY; ?>[sessions][<?php echo $i; ?>][note]"><?php echo esc_textarea( $session['note'] ); ?></textarea></label> <label>Override singolo € <input type="number" step=".01" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[sessions][<?php echo $i; ?>][single_price_eur]" value="<?php echo $session['single_price_cents'] ? esc_attr(number_format($session['single_price_cents']/100,2,'.','')) : ''; ?>"></label> <label>Override coppia € <input type="number" step=".01" name="<?php echo FMH_COURSE_OPTION_KEY; ?>[sessions][<?php echo $i; ?>][couple_price_eur]" value="<?php echo $session['couple_price_cents'] ? esc_attr(number_format($session['couple_price_cents']/100,2,'.','')) : ''; ?>"></label><button type="button" class="button-link-delete fmh-remove-session">Rimuovi</button></fieldset>
	<?php }
}
