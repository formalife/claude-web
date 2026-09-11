<?php
/**
 * Pannello impostazioni del plugin: immagini (Libreria Media), dati
 * operativi del libro (prezzo/date), statistiche, contatti, dati legali e
 * link legali per il footer. Il copy testuale lungo delle sezioni non è
 * qui: vive nel template (vedi fmh-settings-helpers.php per la nota
 * completa). Nessun campo colore: la palette è fissa via CSS per
 * garantire che sul sito venga usata sempre e solo quella approvata.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FMH_Settings {

	const SETTINGS_GROUP = 'fmh_settings_group';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	public static function add_menu() {
		add_menu_page(
			__( 'Formalife Homepage — Impostazioni', 'formalife-homepage' ),
			__( 'Formalife Home', 'formalife-homepage' ),
			'manage_options',
			FMH_SLUG,
			array( __CLASS__, 'render_page' ),
			'dashicons-admin-home',
			59
		);
	}

	public static function register_settings() {
		register_setting(
			self::SETTINGS_GROUP,
			FMH_OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => fmh_default_settings(),
			)
		);
	}

	/**
	 * Sanitizza tutti i campi del pannello impostazioni prima del salvataggio.
	 *
	 * @param array $input Dati grezzi ricevuti dal form.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$defaults = fmh_default_settings();
		$input    = is_array( $input ) ? $input : array();
		$output   = array();

		// Immagini: solo ID interi validi.
		foreach ( array(
			'book_cover_image_id', 'camposarcone_image_id', 'raffaele_image_id', 'sara_image_id', 'formalife_logo_id', 'og_image_id',
			'hero_bg_image_id',
			'carousel_image_1_id', 'carousel_image_2_id', 'carousel_image_3_id', 'carousel_image_4_id', 'carousel_image_5_id',
			'moment_1_bg_image_id', 'moment_2_bg_image_id', 'moment_3_bg_image_id', 'moment_4_bg_image_id', 'moment_5_bg_image_id', 'moment_6_bg_image_id',
			'triad_bg_image_id', 'next_book_image_id', 'next_course_image_id', 'book_secondary_image_id',
			'waitlist_bg_image_id', 'b2b_image_id', 'team_bg_image_id',
		) as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? absint( $input[ $key ] ) : 0;
		}

		// Menu superiore — URL opzionali (fallback automatico se vuoti).
		foreach ( array( 'nav_home_url', 'nav_book_url', 'nav_course_url' ) as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? esc_url_raw( trim( wp_unslash( $input[ $key ] ) ) ) : '';
		}

		// Opacità immagini di sfondo (0-100).
		foreach ( array( 'triad_bg_opacity', 'team_bg_opacity' ) as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? max( 0, min( 100, absint( $input[ $key ] ) ) ) : $defaults[ $key ];
		}

		// Hero — sfondo: opacità (0-100), sfocatura in px (0-20), colore overlay (hex).
		$output['hero_bg_opacity'] = isset( $input['hero_bg_opacity'] ) ? max( 0, min( 100, absint( $input['hero_bg_opacity'] ) ) ) : $defaults['hero_bg_opacity'];
		$output['hero_bg_blur']    = isset( $input['hero_bg_blur'] ) ? max( 0, min( 20, absint( $input['hero_bg_blur'] ) ) ) : $defaults['hero_bg_blur'];
		$output['hero_overlay_color'] = isset( $input['hero_overlay_color'] ) ? sanitize_hex_color( wp_unslash( $input['hero_overlay_color'] ) ) : $defaults['hero_overlay_color'];
		if ( ! $output['hero_overlay_color'] ) {
			$output['hero_overlay_color'] = $defaults['hero_overlay_color'];
		}

		// Team — ruolo e bio personalizzabili per membro.
		foreach ( array( 'team_mafalda_role', 'team_raffaele_role', 'team_sara_role' ) as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : $defaults[ $key ];
		}
		foreach ( array( 'team_mafalda_bio', 'team_raffaele_bio', 'team_sara_bio' ) as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( wp_unslash( $input[ $key ] ) ) : $defaults[ $key ];
		}

		// Messaggio precompilato CTA WhatsApp del box "Il corso".
		$output['course_whatsapp_message'] = isset( $input['course_whatsapp_message'] ) ? sanitize_text_field( wp_unslash( $input['course_whatsapp_message'] ) ) : $defaults['course_whatsapp_message'];

		// SEO.
		$output['meta_title']       = isset( $input['meta_title'] ) ? sanitize_text_field( wp_unslash( $input['meta_title'] ) ) : $defaults['meta_title'];
		$output['meta_description'] = isset( $input['meta_description'] ) ? sanitize_textarea_field( wp_unslash( $input['meta_description'] ) ) : $defaults['meta_description'];

		// Il libro — dati operativi.
		$output['book_price']         = isset( $input['book_price'] ) ? sanitize_text_field( wp_unslash( $input['book_price'] ) ) : $defaults['book_price'];
		$output['book_date_closing']  = isset( $input['book_date_closing'] ) ? sanitize_text_field( wp_unslash( $input['book_date_closing'] ) ) : $defaults['book_date_closing'];
		$output['book_date_delivery'] = isset( $input['book_date_delivery'] ) ? sanitize_text_field( wp_unslash( $input['book_date_delivery'] ) ) : $defaults['book_date_delivery'];
		$output['book_url']           = isset( $input['book_url'] ) ? esc_url_raw( trim( wp_unslash( $input['book_url'] ) ) ) : $defaults['book_url'];
		$output['book_preview_anchor'] = isset( $input['book_preview_anchor'] ) ? sanitize_title( wp_unslash( $input['book_preview_anchor'] ) ) : $defaults['book_preview_anchor'];

		// Statistiche.
		foreach ( array( 'stat1_number', 'stat1_label', 'stat2_number', 'stat2_label', 'stat3_number', 'stat3_label', 'stat_source' ) as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : $defaults[ $key ];
		}

		// Contatti.
		$output['contact_email']     = isset( $input['contact_email'] ) ? sanitize_email( wp_unslash( $input['contact_email'] ) ) : '';
		$output['contact_whatsapp']  = isset( $input['contact_whatsapp'] ) ? sanitize_text_field( wp_unslash( $input['contact_whatsapp'] ) ) : '';
		$output['contact_instagram'] = isset( $input['contact_instagram'] ) ? sanitize_text_field( wp_unslash( $input['contact_instagram'] ) ) : '';

		// Link legali (le pagine sono generate dal plugin del libro: qui solo l'URL).
		$output['terms_url']   = isset( $input['terms_url'] ) ? esc_url_raw( trim( wp_unslash( $input['terms_url'] ) ) ) : $defaults['terms_url'];
		$output['privacy_url'] = isset( $input['privacy_url'] ) ? esc_url_raw( trim( wp_unslash( $input['privacy_url'] ) ) ) : $defaults['privacy_url'];

		// Dati legali azienda.
		$output['legal_company_name'] = isset( $input['legal_company_name'] ) ? sanitize_text_field( wp_unslash( $input['legal_company_name'] ) ) : $defaults['legal_company_name'];
		$output['legal_vat_number']   = isset( $input['legal_vat_number'] ) ? sanitize_text_field( wp_unslash( $input['legal_vat_number'] ) ) : $defaults['legal_vat_number'];

		// Notifiche lista d'attesa corso.
		$output['notify_email'] = isset( $input['notify_email'] ) ? sanitize_email( wp_unslash( $input['notify_email'] ) ) : $defaults['notify_email'];

		// Sicurezza — vedi uninstall.php: la casella deve essere spuntata di
		// proposito ad ogni salvataggio, non basta averla spuntata una volta
		// in passato. Sempre falso se il checkbox non è presente nel POST.
		$output['allow_uninstall_wipe'] = ! empty( $input['allow_uninstall_wipe'] );

		return $output;
	}

	/**
	 * Renderizza la pagina impostazioni.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = fmh_get_settings();
		$home_url = fmh_get_home_url();
		?>
		<div class="wrap fmh-settings-wrap">
			<h1><?php esc_html_e( 'Formalife Homepage — Impostazioni', 'formalife-homepage' ); ?></h1>

			<p>
				<?php if ( $home_url ) : ?>
					<?php esc_html_e( 'Homepage:', 'formalife-homepage' ); ?> <a href="<?php echo esc_url( $home_url ); ?>" target="_blank"><?php echo esc_url( $home_url ); ?></a>
				<?php else : ?>
					<span class="notice notice-warning inline" style="display:inline-block;margin:2px 0;">
						<?php esc_html_e( 'Pagina "Home" non ancora creata: disattiva e riattiva il plugin, oppure controlla gli avvisi qui sopra per un eventuale conflitto di slug.', 'formalife-homepage' ); ?>
					</span>
				<?php endif; ?>
			</p>

			<div class="notice notice-info inline">
				<p>
					<?php esc_html_e( 'Questo plugin crea e gestisce la pagina con slug "home", ma non la imposta automaticamente come pagina iniziale del sito. Per farlo, vai su Bacheca → Impostazioni → Lettura, seleziona "Una pagina statica" e scegli "Home" come pagina iniziale.', 'formalife-homepage' ); ?>
				</p>
			</div>

			<div class="notice notice-warning inline">
				<p>
					<strong><?php esc_html_e( 'Punti ancora aperti (vedi riepilogo consegnato):', 'formalife-homepage' ); ?></strong>
					<?php esc_html_e( 'foto di Raffaele La Torre e Sara Bertoli non ancora caricate (compaiono con placeholder), nome/prezzo/date del corso pratico non ancora confermati (sezione "in arrivo" con lista d\'attesa), ancora "#anteprima" sulla landing del libro da verificare.', 'formalife-homepage' ); ?>
				</p>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( self::SETTINGS_GROUP ); ?>

				<h2 class="title"><?php esc_html_e( 'SEO', 'formalife-homepage' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="fmh_meta_title"><?php esc_html_e( 'Title tag', 'formalife-homepage' ); ?></label></th>
						<td><input type="text" id="fmh_meta_title" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[meta_title]" value="<?php echo esc_attr( $settings['meta_title'] ); ?>" class="large-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_meta_description"><?php esc_html_e( 'Meta description', 'formalife-homepage' ); ?></label></th>
						<td><textarea id="fmh_meta_description" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[meta_description]" rows="3" class="large-text"><?php echo esc_textarea( $settings['meta_description'] ); ?></textarea></td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Immagini', 'formalife-homepage' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::render_image_field(
						'book_cover_image_id',
						$settings['book_cover_image_id'],
						__( 'Copertina del libro', 'formalife-homepage' ),
						__( 'Usata nell\'hero e nella sezione "Il libro". Stessa immagine caricata nel plugin del libro. Formato consigliato: 800×1132 px.', 'formalife-homepage' )
					);
					self::render_image_field(
						'camposarcone_image_id',
						$settings['camposarcone_image_id'],
						__( 'Foto Dott.ssa Mafalda Camposarcone', 'formalife-homepage' ),
						__( 'Sezione "Chi c\'è dietro Formalife". Formato consigliato: ritratto quadrato o 4:5.', 'formalife-homepage' )
					);
					self::render_image_field(
						'raffaele_image_id',
						$settings['raffaele_image_id'],
						__( 'Foto Raffaele La Torre (opzionale)', 'formalife-homepage' ),
						__( 'Se non caricata, compare un placeholder con le iniziali. Formato consigliato: ritratto quadrato o 4:5.', 'formalife-homepage' )
					);
					self::render_image_field(
						'sara_image_id',
						$settings['sara_image_id'],
						__( 'Foto Sara Bertoli (opzionale)', 'formalife-homepage' ),
						__( 'Se non caricata, compare un placeholder con le iniziali. Formato consigliato: ritratto quadrato o 4:5.', 'formalife-homepage' )
					);
					self::render_image_field(
						'formalife_logo_id',
						$settings['formalife_logo_id'],
						__( 'Logo Formalife (sezione contatti finale, opzionale)', 'formalife-homepage' ),
						__( 'Formato consigliato: PNG trasparente, larghezza indicativa 300 px.', 'formalife-homepage' )
					);
					self::render_image_field(
						'og_image_id',
						$settings['og_image_id'],
						__( 'Immagine social / OG (opzionale)', 'formalife-homepage' ),
						__( 'Usata quando la homepage viene condivisa su social o messaggistica. Formato consigliato: 1200×630 px.', 'formalife-homepage' )
					);
					?>
				</table>

				<h2 class="title"><?php esc_html_e( 'Menu superiore', 'formalife-homepage' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Il logo si imposta più sopra ("Logo Formalife"). URL opzionali: se lasciati vuoti, "Home" punta alla pagina Home, "Il libro" alla URL del libro, "I corsi" all\'ancora del box corso in questa pagina.', 'formalife-homepage' ); ?></p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="fmh_nav_home_url"><?php esc_html_e( 'Link "Home" (opzionale)', 'formalife-homepage' ); ?></label></th>
						<td><input type="url" id="fmh_nav_home_url" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[nav_home_url]" value="<?php echo esc_attr( $settings['nav_home_url'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_nav_book_url"><?php esc_html_e( 'Link "Il libro" (opzionale)', 'formalife-homepage' ); ?></label></th>
						<td><input type="url" id="fmh_nav_book_url" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[nav_book_url]" value="<?php echo esc_attr( $settings['nav_book_url'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_nav_course_url"><?php esc_html_e( 'Link "I corsi" (opzionale)', 'formalife-homepage' ); ?></label></th>
						<td><input type="url" id="fmh_nav_course_url" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[nav_course_url]" value="<?php echo esc_attr( $settings['nav_course_url'] ); ?>" class="regular-text" /></td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Hero — immagine di sfondo', 'formalife-homepage' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Immagine opzionale dietro al testo della hero, con trasparenza, sfocatura e colore di sovrapposizione regolabili (di base il cream chiaro del sito).', 'formalife-homepage' ); ?></p>
				<table class="form-table" role="presentation">
					<?php
					self::render_image_field(
						'hero_bg_image_id',
						$settings['hero_bg_image_id'],
						__( 'Immagine di sfondo hero (opzionale)', 'formalife-homepage' ),
						__( 'Se non caricata, la hero resta con il solo sfondo colorato attuale. Formato consigliato: 1920×1080 px o più largo.', 'formalife-homepage' )
					);
					?>
					<tr>
						<th scope="row"><label for="fmh_hero_bg_opacity"><?php esc_html_e( 'Trasparenza immagine (%)', 'formalife-homepage' ); ?></label></th>
						<td>
							<input type="number" min="0" max="100" id="fmh_hero_bg_opacity" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[hero_bg_opacity]" value="<?php echo esc_attr( $settings['hero_bg_opacity'] ); ?>" class="small-text" />
							<p class="description"><?php esc_html_e( 'Percentuale di visibilità dell\'immagine (0 = invisibile, 100 = piena visibilità). Consigliato un valore basso, 10-25, per non disturbare la lettura.', 'formalife-homepage' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_hero_bg_blur"><?php esc_html_e( 'Sfocatura immagine (px)', 'formalife-homepage' ); ?></label></th>
						<td><input type="number" min="0" max="20" id="fmh_hero_bg_blur" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[hero_bg_blur]" value="<?php echo esc_attr( $settings['hero_bg_blur'] ); ?>" class="small-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_hero_overlay_color"><?php esc_html_e( 'Colore di sovrapposizione', 'formalife-homepage' ); ?></label></th>
						<td>
							<input type="text" id="fmh_hero_overlay_color" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[hero_overlay_color]" value="<?php echo esc_attr( $settings['hero_overlay_color'] ); ?>" class="fmh-color-field" data-default-color="#FBF8F4" />
							<p class="description"><?php esc_html_e( 'Di base il cream chiaro del sito (#FBF8F4).', 'formalife-homepage' ); ?></p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Il libro — dati operativi', 'formalife-homepage' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="fmh_book_price"><?php esc_html_e( 'Prezzo', 'formalife-homepage' ); ?></label></th>
						<td><input type="text" id="fmh_book_price" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[book_price]" value="<?php echo esc_attr( $settings['book_price'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_book_date_closing"><?php esc_html_e( 'Prenotazioni entro il', 'formalife-homepage' ); ?></label></th>
						<td><input type="text" id="fmh_book_date_closing" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[book_date_closing]" value="<?php echo esc_attr( $settings['book_date_closing'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_book_date_delivery"><?php esc_html_e( 'Spedizione', 'formalife-homepage' ); ?></label></th>
						<td><input type="text" id="fmh_book_date_delivery" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[book_date_delivery]" value="<?php echo esc_attr( $settings['book_date_delivery'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_book_url"><?php esc_html_e( 'URL pagina del libro', 'formalife-homepage' ); ?></label></th>
						<td><input type="url" id="fmh_book_url" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[book_url]" value="<?php echo esc_attr( $settings['book_url'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_book_preview_anchor"><?php esc_html_e( 'Ancora anteprima libro', 'formalife-homepage' ); ?></label></th>
						<td>
							<input type="text" id="fmh_book_preview_anchor" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[book_preview_anchor]" value="<?php echo esc_attr( $settings['book_preview_anchor'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'ID HTML della sezione "anteprima" sulla landing del libro (senza #). Il link "Sfoglia un\'anteprima" punta a URL-libro#ancora. Richiede che quell\'id esista davvero sulla pagina del libro — verifica prima di considerare il link definitivo.', 'formalife-homepage' ); ?></p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Statistiche — sezione "Trasparenza sulle fonti"', 'formalife-homepage' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Stessi valori reali già pubblicati sulla landing del libro (fonte: Ministero della Salute).', 'formalife-homepage' ); ?></p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Statistica 1', 'formalife-homepage' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[stat1_number]" value="<?php echo esc_attr( $settings['stat1_number'] ); ?>" class="small-text" />
							<input type="text" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[stat1_label]" value="<?php echo esc_attr( $settings['stat1_label'] ); ?>" class="regular-text" style="width:60%;" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Statistica 2', 'formalife-homepage' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[stat2_number]" value="<?php echo esc_attr( $settings['stat2_number'] ); ?>" class="small-text" />
							<input type="text" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[stat2_label]" value="<?php echo esc_attr( $settings['stat2_label'] ); ?>" class="regular-text" style="width:60%;" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Statistica 3', 'formalife-homepage' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[stat3_number]" value="<?php echo esc_attr( $settings['stat3_number'] ); ?>" class="small-text" />
							<input type="text" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[stat3_label]" value="<?php echo esc_attr( $settings['stat3_label'] ); ?>" class="regular-text" style="width:60%;" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_stat_source"><?php esc_html_e( 'Fonte', 'formalife-homepage' ); ?></label></th>
						<td><input type="text" id="fmh_stat_source" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[stat_source]" value="<?php echo esc_attr( $settings['stat_source'] ); ?>" class="large-text" /></td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Contatti', 'formalife-homepage' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="fmh_contact_email"><?php esc_html_e( 'Email', 'formalife-homepage' ); ?></label></th>
						<td><input type="email" id="fmh_contact_email" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[contact_email]" value="<?php echo esc_attr( $settings['contact_email'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_contact_whatsapp"><?php esc_html_e( 'WhatsApp', 'formalife-homepage' ); ?></label></th>
						<td><input type="text" id="fmh_contact_whatsapp" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[contact_whatsapp]" value="<?php echo esc_attr( $settings['contact_whatsapp'] ); ?>" class="regular-text" placeholder="3517940823" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_contact_instagram"><?php esc_html_e( 'Instagram', 'formalife-homepage' ); ?></label></th>
						<td><input type="text" id="fmh_contact_instagram" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[contact_instagram]" value="<?php echo esc_attr( $settings['contact_instagram'] ); ?>" class="regular-text" placeholder="@formalife.it" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_notify_email"><?php esc_html_e( 'Email notifiche lista d\'attesa corso', 'formalife-homepage' ); ?></label></th>
						<td>
							<input type="email" id="fmh_notify_email" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[notify_email]" value="<?php echo esc_attr( $settings['notify_email'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Riceve una notifica ad ogni nuova iscrizione alla lista d\'attesa del corso pratico.', 'formalife-homepage' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_course_whatsapp_message"><?php esc_html_e( 'Messaggio precompilato WhatsApp corso', 'formalife-homepage' ); ?></label></th>
						<td>
							<input type="text" id="fmh_course_whatsapp_message" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[course_whatsapp_message]" value="<?php echo esc_attr( $settings['course_whatsapp_message'] ); ?>" class="large-text" />
							<p class="description"><?php esc_html_e( 'Testo già scritto nel messaggio WhatsApp quando si clicca il pulsante "Voglio conoscere le prossime date" nella sezione "Qual è il prossimo passo?".', 'formalife-homepage' ); ?></p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Chi c\'è dietro Formalife — ruoli e bio', 'formalife-homepage' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Ruolo e biografia di ciascun membro del team, mostrati nella sezione "Chi c\'è dietro Formalife".', 'formalife-homepage' ); ?></p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Dott.ssa Mafalda Camposarcone', 'formalife-homepage' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[team_mafalda_role]" value="<?php echo esc_attr( $settings['team_mafalda_role'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Ruolo, es. Direttrice Scientifica', 'formalife-homepage' ); ?>" /><br />
							<textarea name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[team_mafalda_bio]" rows="3" class="large-text"><?php echo esc_textarea( $settings['team_mafalda_bio'] ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Raffaele La Torre', 'formalife-homepage' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[team_raffaele_role]" value="<?php echo esc_attr( $settings['team_raffaele_role'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Ruolo, es. CEO e Istruttore', 'formalife-homepage' ); ?>" /><br />
							<textarea name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[team_raffaele_bio]" rows="3" class="large-text"><?php echo esc_textarea( $settings['team_raffaele_bio'] ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Sara Bertoli', 'formalife-homepage' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[team_sara_role]" value="<?php echo esc_attr( $settings['team_sara_role'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Ruolo, es. Istruttrice', 'formalife-homepage' ); ?>" /><br />
							<textarea name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[team_sara_bio]" rows="3" class="large-text"><?php echo esc_textarea( $settings['team_sara_bio'] ); ?></textarea>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Osserva. Valuta. Agisci. — sfondo', 'formalife-homepage' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::render_image_field(
						'triad_bg_image_id',
						$settings['triad_bg_image_id'],
						__( 'Immagine di sfondo (opzionale)', 'formalife-homepage' ),
						__( 'Mostrata in sovrapposizione, in trasparenza, dietro il testo.', 'formalife-homepage' )
					);
					?>
					<tr>
						<th scope="row"><label for="fmh_triad_bg_opacity"><?php esc_html_e( 'Trasparenza immagine (%)', 'formalife-homepage' ); ?></label></th>
						<td><input type="number" min="0" max="100" id="fmh_triad_bg_opacity" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[triad_bg_opacity]" value="<?php echo esc_attr( $settings['triad_bg_opacity'] ); ?>" class="small-text" /></td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Qual è il prossimo passo? — immagini box', 'formalife-homepage' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::render_image_field(
						'next_book_image_id',
						$settings['next_book_image_id'],
						__( 'Immagine box "Il libro" (16:9)', 'formalife-homepage' ),
						__( 'Formato consigliato 16:9, es. 800×450 px.', 'formalife-homepage' )
					);
					self::render_image_field(
						'next_course_image_id',
						$settings['next_course_image_id'],
						__( 'Immagine box "Il corso pratico" (16:9)', 'formalife-homepage' ),
						__( 'Formato consigliato 16:9, es. 800×450 px.', 'formalife-homepage' )
					);
					?>
				</table>

				<h2 class="title"><?php esc_html_e( 'Il libro — seconda immagine', 'formalife-homepage' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::render_image_field(
						'book_secondary_image_id',
						$settings['book_secondary_image_id'],
						__( 'Seconda immagine (opzionale)', 'formalife-homepage' ),
						__( 'Mostrata impilata sotto la copertina, es. una pagina interna o il retro del libro.', 'formalife-homepage' )
					);
					?>
				</table>

				<h2 class="title"><?php esc_html_e( 'Lista d\'attesa corso — sfondo sezione', 'formalife-homepage' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::render_image_field(
						'waitlist_bg_image_id',
						$settings['waitlist_bg_image_id'],
						__( 'Immagine di sfondo (opzionale, 10% di opacità)', 'formalife-homepage' ),
						''
					);
					?>
				</table>

				<h2 class="title"><?php esc_html_e( 'B2B — immagine', 'formalife-homepage' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::render_image_field(
						'b2b_image_id',
						$settings['b2b_image_id'],
						__( 'Immagine quadrata (opzionale)', 'formalife-homepage' ),
						__( 'Sostituisce l\'icona. Formato consigliato: quadrato, es. 400×400 px.', 'formalife-homepage' )
					);
					?>
				</table>

				<h2 class="title"><?php esc_html_e( 'Chi c\'è dietro Formalife — sfondo sezione', 'formalife-homepage' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::render_image_field(
						'team_bg_image_id',
						$settings['team_bg_image_id'],
						__( 'Immagine di sfondo (opzionale)', 'formalife-homepage' ),
						''
					);
					?>
					<tr>
						<th scope="row"><label for="fmh_team_bg_opacity"><?php esc_html_e( 'Trasparenza immagine (%)', 'formalife-homepage' ); ?></label></th>
						<td><input type="number" min="0" max="100" id="fmh_team_bg_opacity" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[team_bg_opacity]" value="<?php echo esc_attr( $settings['team_bg_opacity'] ); ?>" class="small-text" /></td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Carosello corsi dal vivo', 'formalife-homepage' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Fino a 5 immagini reali dei corsi: diventano lo sfondo (a rotazione automatica) della sezione dopo il team. Se ne carichi meno di 5, il carosello usa solo quelle presenti.', 'formalife-homepage' ); ?></p>
				<table class="form-table" role="presentation">
					<?php
					for ( $i = 1; $i <= 5; $i++ ) {
						self::render_image_field(
							'carousel_image_' . $i . '_id',
							$settings[ 'carousel_image_' . $i . '_id' ],
							sprintf(
								/* translators: %d: numero immagine carosello */
								__( 'Immagine carosello %d', 'formalife-homepage' ),
								$i
							),
							__( 'Formato consigliato: paesaggio largo (16:9 o simile), stessa proporzione per tutte le immagini del carosello.', 'formalife-homepage' )
						);
					}
					?>
				</table>

				<h2 class="title"><?php esc_html_e( 'Sfondi box "momenti della famiglia"', 'formalife-homepage' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Immagine di sfondo opzionale per ciascuno dei sei box (mostrata al 15% di opacità su sfondo bianco). Se non caricata, il box resta bianco.', 'formalife-homepage' ); ?></p>
				<table class="form-table" role="presentation">
					<?php
					$moment_labels = array(
						1 => __( 'Box 1 — Aspetti un bambino o l\'hai appena avuto', 'formalife-homepage' ),
						2 => __( 'Box 2 — Hai iniziato lo svezzamento', 'formalife-homepage' ),
						3 => __( 'Box 3 — Tuo figlio è entrato al nido', 'formalife-homepage' ),
						4 => __( 'Box 4 — Sei tornato/a al lavoro', 'formalife-homepage' ),
						5 => __( 'Box 5 — Avete già vissuto un brutto evento', 'formalife-homepage' ),
						6 => __( 'Box 6 — È passato più di un anno', 'formalife-homepage' ),
					);
					foreach ( $moment_labels as $i => $label ) {
						self::render_image_field(
							'moment_' . $i . '_bg_image_id',
							$settings[ 'moment_' . $i . '_bg_image_id' ],
							$label,
							''
						);
					}
					?>
				</table>

				<h2 class="title"><?php esc_html_e( 'Dati legali e link (footer)', 'formalife-homepage' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="fmh_legal_company_name"><?php esc_html_e( 'Ragione sociale', 'formalife-homepage' ); ?></label></th>
						<td><input type="text" id="fmh_legal_company_name" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[legal_company_name]" value="<?php echo esc_attr( $settings['legal_company_name'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_legal_vat_number"><?php esc_html_e( 'Partita IVA', 'formalife-homepage' ); ?></label></th>
						<td><input type="text" id="fmh_legal_vat_number" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[legal_vat_number]" value="<?php echo esc_attr( $settings['legal_vat_number'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_terms_url"><?php esc_html_e( 'Condizioni di vendita (URL)', 'formalife-homepage' ); ?></label></th>
						<td><input type="url" id="fmh_terms_url" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[terms_url]" value="<?php echo esc_attr( $settings['terms_url'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="fmh_privacy_url"><?php esc_html_e( 'Privacy (URL)', 'formalife-homepage' ); ?></label></th>
						<td><input type="url" id="fmh_privacy_url" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[privacy_url]" value="<?php echo esc_attr( $settings['privacy_url'] ); ?>" class="regular-text" /></td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Sicurezza — disinstallazione', 'formalife-homepage' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Cancella i dati alla disinstallazione', 'formalife-homepage' ); ?></th>
						<td>
							<label for="fmh_allow_uninstall_wipe">
								<input type="checkbox" id="fmh_allow_uninstall_wipe" name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[allow_uninstall_wipe]" value="1" <?php checked( $settings['allow_uninstall_wipe'], true ); ?> />
								<?php esc_html_e( 'Sì, alla disinstallazione (Bacheca → Plugin → Elimina) cancella impostazioni, iscrizioni al corso e pagine generate.', 'formalife-homepage' ); ?>
							</label>
							<p class="description" style="color:#b32d2e;">
								<?php esc_html_e( 'Lascia questa casella deselezionata se vuoi solo aggiornare i file del plugin (es. caricando uno zip più recente): con la casella deselezionata, eliminare il plugin da Bacheca → Plugin non tocca nessun dato — impostazioni, chiavi Stripe, date dei corsi e iscrizioni restano intatte. Spuntala solo se vuoi davvero ripulire tutto.', 'formalife-homepage' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Salva impostazioni', 'formalife-homepage' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renderizza una riga di form-table per un campo immagine con uploader wp.media.
	 *
	 * @param string $key   Chiave del campo nell'array impostazioni.
	 * @param int    $value ID allegato corrente.
	 * @param string $label Etichetta del campo.
	 * @param string $help  Testo di aiuto (specifiche formato).
	 */
	private static function render_image_field( $key, $value, $label, $help ) {
		$value    = absint( $value );
		$field_id = 'fmh_' . $key;
		$url      = $value ? fmh_get_image_url( $value, 'medium' ) : '';
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<div class="fmh-image-field" data-field="<?php echo esc_attr( $field_id ); ?>">
					<div class="fmh-image-preview" id="<?php echo esc_attr( $field_id ); ?>_preview">
						<?php if ( $url ) : ?>
							<img src="<?php echo esc_url( $url ); ?>" alt="" />
						<?php endif; ?>
					</div>
					<input
						type="hidden"
						id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( FMH_OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]"
						value="<?php echo esc_attr( $value ); ?>"
						class="fmh-image-id-input"
					/>
					<p>
						<button type="button" class="button fmh-choose-image"><?php esc_html_e( 'Scegli immagine', 'formalife-homepage' ); ?></button>
						<button type="button" class="button fmh-remove-image" <?php echo $value ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Rimuovi immagine', 'formalife-homepage' ); ?></button>
					</p>
					<p class="description"><?php echo esc_html( $help ); ?></p>
				</div>
			</td>
		</tr>
		<?php
	}
}
