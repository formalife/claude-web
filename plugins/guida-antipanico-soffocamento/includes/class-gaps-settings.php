<?php
/**
 * Pannello impostazioni del plugin: immagini (Libreria Media), colori
 * (color picker nativo), contenuti variabili, dato statistico configurabile,
 * pagina Stripe di destinazione, pagina del corso pratico (cross-sell post
 * acquisto), contatti finali e link legali per il footer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GAPS_Settings {

	const SETTINGS_GROUP = 'gaps_settings_group';

	public static function init() {
		// Priorità 5 (anziché la predefinita 10): la voce di menu principale e
		// il suo sottomenu "Impostazioni" devono registrarsi PRIMA che WordPress
		// agganci il sottomenu automatico del custom post type "Preordini"
		// (che usa lo stesso slug del plugin come parent, vedi class-gaps-preorder.php).
		// Senza questa precedenza esplicita, in alcune installazioni il CPT
		// finisce per occupare la voce di primo livello al posto della pagina
		// impostazioni, che resta registrata ma senza alcun link visibile nel
		// menu laterale — esattamente il sintomo "vedo solo Preordini".
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	public static function add_menu() {
		add_menu_page(
			__( 'Guida Anti-Panico — Impostazioni', 'guida-antipanico-soffocamento' ),
			__( 'Guida Anti-Panico', 'guida-antipanico-soffocamento' ),
			'manage_options',
			GAPS_SLUG,
			array( __CLASS__, 'render_page' ),
			'dashicons-shield',
			58
		);

		// Sottomenu esplicito per la pagina impostazioni, con etichetta chiara
		// "Impostazioni". Senza questa riga, WordPress duplicherebbe da solo la
		// voce di primo livello come primo sottomenu; registrarla a mano
		// garantisce sia l'etichetta corretta sia che questo slot non venga
		// mai nascosto o sovrascritto dal sottomenu del CPT "Preordini".
		add_submenu_page(
			GAPS_SLUG,
			__( 'Guida Anti-Panico — Impostazioni', 'guida-antipanico-soffocamento' ),
			__( 'Impostazioni', 'guida-antipanico-soffocamento' ),
			'manage_options',
			GAPS_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	public static function register_settings() {
		register_setting(
			self::SETTINGS_GROUP,
			GAPS_OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => gaps_default_settings(),
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
		$defaults = gaps_default_settings();
		$input    = is_array( $input ) ? $input : array();
		$output   = array();

		// Immagini: solo ID interi validi.
		foreach ( array( 'cover_image_id', 'camposarcone_image_id', 'admission_bg_image_id', 'guarantee_image_id', 'preview_image_1_id', 'preview_image_2_id', 'preview_image_3_id', 'preview_image_desktop_id', 'preview_image_mobile_id', 'formalife_logo_id', 'og_image_id', 'mechanism_key_image_id', 'final_floating_image_1_id', 'final_floating_image_2_id', 'final_floating_image_3_id', 'numeri_preview_image_id' ) as $key ) {
			$candidate      = isset( $input[ $key ] ) ? absint( $input[ $key ] ) : 0;
			// L'ID deve appartenere a un allegato immagine valido: un ID
			// che punta a un post cancellato, a un file non-immagine o a
			// un tipo di post diverso viene scartato silenziosamente
			// invece di essere salvato "alla cieca".
			$output[ $key ] = ( $candidate && wp_attachment_is_image( $candidate ) ) ? $candidate : 0;
		}

		// File generico (non immagine): sovrascrittura opzionale del PDF
		// "I tuoi numeri importanti". Stesso trattamento delle immagini (solo
		// ID intero valido), ma tramite uploader dedicato ai file nella
		// Libreria Media (vedi assets/js/admin.js, campo ".gaps-file-field").
		$output['numeri_pdf_id'] = isset( $input['numeri_pdf_id'] ) ? absint( $input['numeri_pdf_id'] ) : 0;

		// Opacità e sfocatura sfondo admission.
		$opacity                        = isset( $input['admission_bg_opacity'] ) ? absint( $input['admission_bg_opacity'] ) : $defaults['admission_bg_opacity'];
		$output['admission_bg_opacity'] = max( 0, min( 100, $opacity ) );
		$blur                           = isset( $input['admission_bg_blur'] ) ? absint( $input['admission_bg_blur'] ) : $defaults['admission_bg_blur'];
		$output['admission_bg_blur']    = max( 0, min( 20, $blur ) );

		// Colori: hex validi, altrimenti si mantiene il default.
		foreach ( array( 'color_teal_primary', 'color_teal_accent', 'color_cream_bg', 'color_peach_bg', 'color_terracotta' ) as $key ) {
			$color          = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';
			$output[ $key ] = $color ? $color : $defaults[ $key ];
		}

		// Contenuti testuali semplici.
		foreach ( array( 'price', 'shipping_price', 'date_closing', 'date_delivery' ) as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : $defaults[ $key ];
		}

		// Chiavi Stripe per il pagamento embedded (Payment Element).
		$output['stripe_publishable_key'] = isset( $input['stripe_publishable_key'] ) ? sanitize_text_field( wp_unslash( $input['stripe_publishable_key'] ) ) : '';
		$output['stripe_secret_key']      = isset( $input['stripe_secret_key'] ) ? sanitize_text_field( wp_unslash( $input['stripe_secret_key'] ) ) : '';
		$output['stripe_payment_methods'] = $defaults['stripe_payment_methods']; // fisso in questa versione: card, link, paypal.

		// Meta Pixel (opzionale): solo l'ID numerico/testuale del Pixel,
		// nessuna chiave segreta coinvolta. Campo vuoto = nessuno script
		// del Pixel viene mai caricato (vedi GAPS_Assets::enqueue_frontend()).
		$output['meta_pixel_id'] = isset( $input['meta_pixel_id'] ) ? sanitize_text_field( wp_unslash( $input['meta_pixel_id'] ) ) : '';

		// Pagina del corso pratico (cross-sell nella pagina "Grazie").
		$output['course_url'] = isset( $input['course_url'] ) ? esc_url_raw( trim( wp_unslash( $input['course_url'] ) ) ) : '';

		// Link recensione Google (pagina "I tuoi numeri importanti").
		$output['review_url'] = isset( $input['review_url'] ) ? esc_url_raw( trim( wp_unslash( $input['review_url'] ) ) ) : '';

		// Contatti.
		$output['contact_email']     = isset( $input['contact_email'] ) ? sanitize_email( wp_unslash( $input['contact_email'] ) ) : '';
		$output['contact_whatsapp']  = isset( $input['contact_whatsapp'] ) ? sanitize_text_field( wp_unslash( $input['contact_whatsapp'] ) ) : '';
		$output['contact_instagram'] = isset( $input['contact_instagram'] ) ? sanitize_text_field( wp_unslash( $input['contact_instagram'] ) ) : '';

		// Link legali.
		$output['terms_url']   = isset( $input['terms_url'] ) ? esc_url_raw( trim( wp_unslash( $input['terms_url'] ) ) ) : '';
		$output['privacy_url'] = isset( $input['privacy_url'] ) ? esc_url_raw( trim( wp_unslash( $input['privacy_url'] ) ) ) : '';

		// Dato statistico.
		foreach ( array( 'stat1_number', 'stat1_label', 'stat2_number', 'stat2_label', 'stat3_number', 'stat3_label', 'stat_source' ) as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : $defaults[ $key ];
		}

		// Dati legali e aziendali (Condizioni di vendita, Privacy).
		// Nota: usiamo sanitize_text_field() anche per PEC e referente privacy
		// (non sanitize_email()) perché quest'ultima, applicata al segnaposto
		// di default "[indirizzo]@pec.it", ne rimuoverebbe silenziosamente le
		// parentesi quadre: il campo risulterebbe "compilato" a tutti gli
		// effetti e perderebbe l'evidenziazione di gaps_legal_field(), pur
		// non contenendo un indirizzo reale.
		foreach ( array( 'legal_company_name', 'legal_vat_number', 'legal_address', 'legal_pec', 'legal_rea', 'legal_share_capital', 'legal_court', 'legal_dpo_email', 'legal_last_updated', 'shipping_carrier' ) as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : $defaults[ $key ];
		}
		$guarantee_days                  = isset( $input['guarantee_period_days'] ) ? absint( $input['guarantee_period_days'] ) : absint( $defaults['guarantee_period_days'] );
		$output['guarantee_period_days'] = $guarantee_days > 0 ? (string) $guarantee_days : $defaults['guarantee_period_days'];

		// Notifiche e conferma di pagamento.
		$output['notify_email']          = isset( $input['notify_email'] ) ? sanitize_email( wp_unslash( $input['notify_email'] ) ) : $defaults['notify_email'];
		$output['stripe_webhook_secret'] = isset( $input['stripe_webhook_secret'] ) ? sanitize_text_field( wp_unslash( $input['stripe_webhook_secret'] ) ) : '';

		return $output;
	}

	/**
	 * Renderizza la pagina impostazioni.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings         = gaps_get_settings();
		$stripe_configured = ( '' !== trim( $settings['stripe_publishable_key'] ) && '' !== trim( $settings['stripe_secret_key'] ) );
		// Le chiavi API bastano perché il pagamento vada a buon fine su
		// Stripe, ma NON bastano perché il plugin lo venga a sapere: solo
		// il webhook (vedi class-gaps-stripe-webhook.php) porta un preordine
		// a "Pagato". Questo secondo controllo, distinto dal primo, esiste
		// apposta per intercettare il sintomo "il cliente ha pagato ma in
		// bacheca risulta ancora In attesa di pagamento".
		$webhook_configured = $stripe_configured && ( '' !== trim( $settings['stripe_webhook_secret'] ) );
		?>
		<div class="wrap gaps-settings-wrap">
			<h1><?php esc_html_e( 'Guida Anti-Panico al Soffocamento Pediatrico — Impostazioni', 'guida-antipanico-soffocamento' ); ?></h1>

			<p>
			<?php
			foreach ( GAPS_Page_Manager::get_registry() as $page_key => $page_def ) {
				$page_url = GAPS_Page_Manager::get_page_url( $page_key );
				if ( $page_url ) {
					printf(
						'%1$s <a href="%2$s" target="_blank">%2$s</a><br />',
						esc_html( $page_def['admin_label'] . ':' ),
						esc_url( $page_url )
					);
				} else {
					printf(
						'<span class="notice notice-warning inline" style="display:inline-block;margin:2px 0;">%s</span><br />',
						sprintf(
							/* translators: %s: nome della pagina (es. "Privacy Policy") */
							esc_html__( 'Pagina "%s" non ancora creata: disattiva e riattiva il plugin, oppure controlla gli avvisi qui sopra per un eventuale conflitto di slug.', 'guida-antipanico-soffocamento' ),
							esc_html( $page_def['admin_label'] )
						)
					);
				}
			}
			?>
			</p>

			<div class="notice <?php echo $webhook_configured ? 'notice-success' : 'notice-warning'; ?> inline">
				<p>
					<?php if ( $webhook_configured ) : ?>
						<?php esc_html_e( 'Chiavi Stripe e webhook configurati: il pagamento (carta, Link, PayPal) avviene direttamente nello step 3 del popup, senza uscire dal sito, e ogni pagamento confermato aggiorna automaticamente lo stato in "Preordini ricevuti".', 'guida-antipanico-soffocamento' ); ?>
					<?php elseif ( $stripe_configured ) : ?>
						<strong><?php esc_html_e( 'Attenzione:', 'guida-antipanico-soffocamento' ); ?></strong>
						<?php esc_html_e( 'le chiavi Stripe sono impostate e i clienti possono già pagare, ma la chiave segreta del webhook qui sotto è ancora vuota. Senza di essa il plugin non può sapere quando un pagamento va davvero a buon fine: i preordini pagati resteranno bloccati su "In attesa di pagamento" in "Preordini ricevuti" e nessuna email di conferma partirà, anche se il cliente ha pagato regolarmente. Completa la sezione "Notifiche e conferma di pagamento" qui sotto per risolvere.', 'guida-antipanico-soffocamento' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Non hai ancora impostato le chiavi Stripe qui sotto. Il modulo di preordine funziona comunque e salva i lead in "Preordini ricevuti", ma lo step di pagamento non potrà completarsi finché non imposti la chiave pubblicabile e quella segreta.', 'guida-antipanico-soffocamento' ); ?>
					<?php endif; ?>
				</p>
			</div>

			<?php
			$thankyou_url = gaps_get_thankyou_url();
			if ( $thankyou_url ) :
				?>
				<div class="notice notice-info inline">
					<p>
						<?php esc_html_e( 'Dopo il pagamento, il cliente viene reindirizzato automaticamente a questa pagina: non serve impostare nulla su Stripe per questo passaggio (non esiste più un Payment Link o una pagina di successo del Checkout da configurare — il pagamento avviene dentro il popup del sito).', 'guida-antipanico-soffocamento' ); ?> <code><?php echo esc_html( $thankyou_url ); ?></code>
					</p>
					<p>
						<?php esc_html_e( 'Il passaggio da fare davvero su Stripe è un altro, ed è nella sezione "Notifiche e conferma di pagamento" qui sotto: registrare l\'URL del webhook e incollarne la chiave segreta. Senza quello, i pagamenti riusciti non verranno mai segnati come "Pagato".', 'guida-antipanico-soffocamento' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( self::SETTINGS_GROUP ); ?>

				<h2 class="title"><?php esc_html_e( 'Immagini', 'guida-antipanico-soffocamento' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::render_image_field(
						'cover_image_id',
						$settings['cover_image_id'],
						__( 'Copertina del libro', 'guida-antipanico-soffocamento' ),
						__( 'Usata nell\'Hero, nell\'Offerta e nella pagina "Grazie". Formato consigliato: 800×1132 px (proporzione ~1:1.414), PNG o JPG.', 'guida-antipanico-soffocamento' )
					);
					self::render_image_field(
						'camposarcone_image_id',
						$settings['camposarcone_image_id'],
						__( 'Foto Dott.ssa Camposarcone', 'guida-antipanico-soffocamento' ),
						__( 'Sezione "Chi ha scritto il libro". Formato consigliato: 800×1000 px (verticale, ritratto 4:5), JPG.', 'guida-antipanico-soffocamento' )
					);
					self::render_image_field(
						'admission_bg_image_id',
						$settings['admission_bg_image_id'],
						__( 'Sfondo sezione "Un libro non può allenare le tue mani" (opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'Immagine mostrata in sovrapposizione, con trasparenza regolabile qui sotto. Formato consigliato: 1600×900 px o più larga.', 'guida-antipanico-soffocamento' )
					);
					self::render_image_field(
						'guarantee_image_id',
						$settings['guarantee_image_id'],
						__( 'Immagine garanzia (opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'Sostituisce l\'icona scudo nella sezione Garanzia. Consigliata un\'immagine PNG con sfondo trasparente, dimensioni contenute (es. 300×300 px).', 'guida-antipanico-soffocamento' )
					);
					self::render_image_field(
						'preview_image_1_id',
						$settings['preview_image_1_id'],
						__( 'Anteprima libro — pagina 1 (opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'Mostrata nel popup "Sfoglia un\'anteprima" della sezione Prova. Formato consigliato: 1000×1400 px.', 'guida-antipanico-soffocamento' )
					);
					self::render_image_field(
						'preview_image_2_id',
						$settings['preview_image_2_id'],
						__( 'Anteprima libro — pagina 2 (opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'Formato consigliato: 1000×1400 px.', 'guida-antipanico-soffocamento' )
					);
					self::render_image_field(
						'preview_image_3_id',
						$settings['preview_image_3_id'],
						__( 'Anteprima libro — pagina 3 (opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'Formato consigliato: 1000×1400 px.', 'guida-antipanico-soffocamento' )
					);
					?>
					<tr>
						<th colspan="2">
							<h3 style="margin:18px 0 4px;"><?php esc_html_e( 'Anteprima libro responsive — pulsante "Sfoglia un\'anteprima" (opzionale)', 'guida-antipanico-soffocamento' ); ?></h3>
							<p class="description"><?php esc_html_e( 'Due varianti dedicate per l\'immagine del pulsante che apre la gallery della sezione "Prova": una per desktop, una per mobile. Se lasciate vuote, viene usata automaticamente l\'immagine "Anteprima libro — pagina 1" qui sopra (nessuna landing esistente si rompe).', 'guida-antipanico-soffocamento' ); ?></p>
						</th>
					</tr>
					<?php
					self::render_image_field(
						'preview_image_desktop_id',
						$settings['preview_image_desktop_id'],
						__( 'Anteprima libro — desktop (opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'Mostrata sopra il breakpoint di 767px. Formato consigliato: 1000×1400 px, stesso rapporto d\'aspetto della variante mobile per evitare scatti visivi al ridimensionamento.', 'guida-antipanico-soffocamento' )
					);
					self::render_image_field(
						'preview_image_mobile_id',
						$settings['preview_image_mobile_id'],
						__( 'Anteprima libro — mobile (opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'Mostrata sotto il breakpoint di 767px. Se lasciata vuota, viene usata la variante desktop qui sopra. Formato consigliato: 1000×1400 px.', 'guida-antipanico-soffocamento' )
					);
					self::render_image_field(
						'formalife_logo_id',
						$settings['formalife_logo_id'],
						__( 'Logo Formalife (sezione contatti finale)', 'guida-antipanico-soffocamento' ),
						__( 'Formato consigliato: PNG trasparente, larghezza indicativa 300 px.', 'guida-antipanico-soffocamento' )
					);
					self::render_image_field(
						'og_image_id',
						$settings['og_image_id'],
						__( 'Immagine social / OG (opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'Usata quando la pagina viene condivisa su social o messaggistica. Formato consigliato: 1200×630 px.', 'guida-antipanico-soffocamento' )
					);
					?>
					<tr>
						<th scope="row"><label for="gaps_admission_bg_opacity"><?php esc_html_e( 'Trasparenza sfondo (0-100)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="number" min="0" max="100" id="gaps_admission_bg_opacity" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[admission_bg_opacity]" value="<?php echo esc_attr( $settings['admission_bg_opacity'] ); ?>" class="small-text" />
							<p class="description"><?php esc_html_e( '0 = immagine invisibile, 100 = immagine a piena opacità. Consigliato tra 10 e 25 per non compromettere la leggibilità del testo.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_admission_bg_blur"><?php esc_html_e( 'Sfocatura sfondo in px (0-20)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="number" min="0" max="20" id="gaps_admission_bg_blur" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[admission_bg_blur]" value="<?php echo esc_attr( $settings['admission_bg_blur'] ); ?>" class="small-text" />
							<p class="description"><?php esc_html_e( '0 = nitida, valori più alti sfocano progressivamente l\'immagine di sfondo.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<?php
					self::render_image_field(
						'mechanism_key_image_id',
						$settings['mechanism_key_image_id'],
						__( 'Immagine box "Il sistema" (opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'Mostrata in sovrapposizione nel box teal della sezione "Il sistema" (circa 1/3 del box). Se non impostata, resta l\'icona 🧠. Formato consigliato: 500×700 px (verticale).', 'guida-antipanico-soffocamento' )
					);
					self::render_image_field(
						'final_floating_image_1_id',
						$settings['final_floating_image_1_id'],
						__( 'Immagine fluttuante 1 — sezione "Tra sei mesi..." (opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'PNG trasparente, formato quadrato consigliato (es. 300×300 px). Se non impostata, semplicemente non compare.', 'guida-antipanico-soffocamento' )
					);
					self::render_image_field(
						'final_floating_image_2_id',
						$settings['final_floating_image_2_id'],
						__( 'Immagine fluttuante 2 — sezione "Tra sei mesi..." (opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'PNG trasparente, formato quadrato consigliato (es. 300×300 px).', 'guida-antipanico-soffocamento' )
					);
					self::render_image_field(
						'final_floating_image_3_id',
						$settings['final_floating_image_3_id'],
						__( 'Immagine fluttuante 3 — sezione "Tra sei mesi..." (opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'PNG trasparente, formato quadrato consigliato (es. 300×300 px).', 'guida-antipanico-soffocamento' )
					);
					?>
				</table>

				<h2 class="title"><?php esc_html_e( 'Colori', 'guida-antipanico-soffocamento' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::render_color_field( 'color_teal_primary', $settings['color_teal_primary'], __( 'Teal primario', 'guida-antipanico-soffocamento' ) );
					self::render_color_field( 'color_teal_accent', $settings['color_teal_accent'], __( 'Teal accento', 'guida-antipanico-soffocamento' ) );
					self::render_color_field( 'color_cream_bg', $settings['color_cream_bg'], __( 'Crema sfondo', 'guida-antipanico-soffocamento' ) );
					self::render_color_field( 'color_peach_bg', $settings['color_peach_bg'], __( 'Pesca sfondo alternato', 'guida-antipanico-soffocamento' ) );
					self::render_color_field( 'color_terracotta', $settings['color_terracotta'], __( 'Terracotta accento statistiche', 'guida-antipanico-soffocamento' ) );
					?>
				</table>

				<h2 class="title"><?php esc_html_e( 'Contenuti', 'guida-antipanico-soffocamento' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="gaps_price"><?php esc_html_e( 'Prezzo', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td><input type="text" id="gaps_price" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[price]" value="<?php echo esc_attr( $settings['price'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_shipping_price"><?php esc_html_e( 'Costo di spedizione', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_shipping_price" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[shipping_price]" value="<?php echo esc_attr( $settings['shipping_price'] ); ?>" class="regular-text" placeholder="2,90 €" />
							<p class="description"><?php esc_html_e( 'Si aggiunge al prezzo del libro (una sola volta per ordine, non per copia). Addebitato insieme al libro nello stesso pagamento Stripe; mostrato con discrezione nello step di pagamento del popup, non nella landing.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_date_closing"><?php esc_html_e( 'Data chiusura prevendita (non più in uso)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_date_closing" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[date_closing]" value="<?php echo esc_attr( $settings['date_closing'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Campo storico della fase di prevendita: da quando il libro è in vendita normale, non compare più da nessuna parte sul sito. Puoi lasciarlo vuoto.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_date_delivery"><?php esc_html_e( 'Data spedizione', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_date_delivery" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[date_delivery]" value="<?php echo esc_attr( $settings['date_delivery'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Es. "in 4-5 giorni lavorativi" — viene mostrato preceduto da "Spedizione", nel popup di pagamento, nella pagina "Grazie", nell\'email di conferma e nelle Condizioni di vendita.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_stripe_publishable_key"><?php esc_html_e( 'Chiave pubblicabile Stripe (pk_...)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_stripe_publishable_key" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[stripe_publishable_key]" value="<?php echo esc_attr( $settings['stripe_publishable_key'] ); ?>" class="regular-text code" autocomplete="off" placeholder="pk_test_... oppure pk_live_..." />
							<p class="description"><?php esc_html_e( 'Dashboard Stripe → Sviluppatori → Chiavi API. Usata dal frontend per mostrare il modulo di pagamento nel popup: non è segreta, può stare nel codice della pagina.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_stripe_secret_key"><?php esc_html_e( 'Chiave segreta Stripe (sk_...)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="password" id="gaps_stripe_secret_key" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[stripe_secret_key]" value="<?php echo esc_attr( $settings['stripe_secret_key'] ); ?>" class="regular-text code" autocomplete="off" placeholder="sk_test_... oppure sk_live_..." />
							<p class="description"><?php esc_html_e( 'Dashboard Stripe → Sviluppatori → Chiavi API. Non condividerla mai: resta solo lato server, usata per creare i pagamenti. Usa le chiavi "test" finché non sei pronto ad andare in produzione, poi sostituiscile con le chiavi "live".', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_course_url"><?php esc_html_e( 'URL corso pratico (cross-sell nella pagina "Grazie")', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="url" id="gaps_course_url" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[course_url]" value="<?php echo esc_attr( $settings['course_url'] ); ?>" class="regular-text" placeholder="https://www.formalife.it/genitori-pronti/" />
							<p class="description"><?php esc_html_e( 'Mostrato come suggerimento facoltativo nella pagina "Grazie — Ordine confermato", dopo il pagamento. Lascia vuoto per non mostrare questa sezione.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Scheda "I tuoi numeri importanti" (pagina raggiunta dal QR code nel libro)', 'guida-antipanico-soffocamento' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Il plugin include già di serie il PDF e l\'anteprima corretti: compila i campi qui sotto solo se vuoi sostituirli con una versione aggiornata caricata dalla Libreria Media.', 'guida-antipanico-soffocamento' ); ?>
				</p>
				<table class="form-table" role="presentation">
					<?php
					self::render_file_field(
						'numeri_pdf_id',
						$settings['numeri_pdf_id'],
						__( 'PDF scaricabile (sovrascrittura opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'Se non impostato, viene usato il PDF incluso nel plugin. Carica qui un file solo per sostituirlo con una versione aggiornata.', 'guida-antipanico-soffocamento' )
					);
					self::render_image_field(
						'numeri_preview_image_id',
						$settings['numeri_preview_image_id'],
						__( 'Immagine di anteprima (sovrascrittura opzionale)', 'guida-antipanico-soffocamento' ),
						__( 'Se non impostata, viene usata l\'anteprima inclusa nel plugin (generata dal PDF). Formato consigliato: verticale, es. 900×1272 px.', 'guida-antipanico-soffocamento' )
					);
					?>
					<tr>
						<th scope="row"><label for="gaps_review_url"><?php esc_html_e( 'Link recensione Google', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="url" id="gaps_review_url" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[review_url]" value="<?php echo esc_attr( $settings['review_url'] ); ?>" class="regular-text" placeholder="https://g.page/r/.../review" />
							<p class="description"><?php esc_html_e( 'Mostrato nella sezione "richiesta recensione" della pagina, dopo il download del PDF. Lascia vuoto per non mostrare questa sezione.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Notifiche e conferma di pagamento', 'guida-antipanico-soffocamento' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'La compilazione del modulo di preordine non invia nessuna email: il lead viene solo salvato in "Preordini ricevuti" con stato "In attesa di pagamento". Solo quando Stripe conferma — via webhook — che il pagamento è realmente andato a buon fine partono due email distinte, nello stesso momento: una al cliente (ringraziamento e riepilogo dell\'ordine) e una all\'indirizzo qui sotto (segnale per procedere con la spedizione).', 'guida-antipanico-soffocamento' ); ?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="gaps_notify_email"><?php esc_html_e( 'Email di notifica interna', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="email" id="gaps_notify_email" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[notify_email]" value="<?php echo esc_attr( $settings['notify_email'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Riceve la notifica interna quando un pagamento viene confermato da Stripe. Non è l\'email inviata al cliente: quella parte automaticamente all\'indirizzo inserito nel modulo di preordine.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'URL endpoint webhook Stripe', 'guida-antipanico-soffocamento' ); ?></th>
						<td>
							<input type="text" readonly="readonly" onclick="this.select();" value="<?php echo esc_attr( GAPS_Stripe_Webhook::get_endpoint_url() ); ?>" class="regular-text code" />
							<p class="description"><?php esc_html_e( 'Incolla questo URL in Stripe → Sviluppatori → Webhook → Aggiungi endpoint, selezionando gli eventi: payment_intent.succeeded, payment_intent.payment_failed.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_stripe_webhook_secret"><?php esc_html_e( 'Chiave segreta webhook Stripe (whsec_...)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="password" id="gaps_stripe_webhook_secret" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[stripe_webhook_secret]" value="<?php echo esc_attr( $settings['stripe_webhook_secret'] ); ?>" class="regular-text" autocomplete="off" />
							<p class="description"><?php esc_html_e( 'La trovi nella pagina di dettaglio del webhook appena creato su Stripe, sezione "Signing secret". Senza questa chiave, il plugin ignora ogni notifica di pagamento per sicurezza (non può verificarne l\'autenticità).', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Meta Pixel (opzionale)', 'guida-antipanico-soffocamento' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Lascia vuoto per non caricare alcuno script di tracciamento: senza un Pixel ID qui sotto, nessuna richiesta parte mai verso connect.facebook.net. Se compilato, il Pixel viene comunque caricato solo dopo le risorse critiche della pagina (font, immagine di copertina, popup) e solo se risulta un consenso ai cookie di marketing valido, quando il sito ne richiede uno.', 'guida-antipanico-soffocamento' ); ?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="gaps_meta_pixel_id"><?php esc_html_e( 'Meta Pixel ID', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_meta_pixel_id" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[meta_pixel_id]" value="<?php echo esc_attr( $settings['meta_pixel_id'] ); ?>" class="regular-text code" autocomplete="off" placeholder="1234567890123456" />
							<p class="description"><?php esc_html_e( 'Dashboard Meta Business → Gestione eventi → la trovi in cima alla pagina del tuo Pixel.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Contatti (sezione finale)', 'guida-antipanico-soffocamento' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="gaps_contact_email"><?php esc_html_e( 'Email', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td><input type="email" id="gaps_contact_email" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[contact_email]" value="<?php echo esc_attr( $settings['contact_email'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_contact_whatsapp"><?php esc_html_e( 'WhatsApp', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_contact_whatsapp" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[contact_whatsapp]" value="<?php echo esc_attr( $settings['contact_whatsapp'] ); ?>" class="regular-text" placeholder="+39 333 1234567" />
							<p class="description"><?php esc_html_e( 'Numero con prefisso internazionale, oppure un link wa.me completo.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_contact_instagram"><?php esc_html_e( 'Instagram', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_contact_instagram" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[contact_instagram]" value="<?php echo esc_attr( $settings['contact_instagram'] ); ?>" class="regular-text" placeholder="@formalife" />
							<p class="description"><?php esc_html_e( 'Handle (es. @formalife) oppure un link completo.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Link legali (footer) — sovrascrittura opzionale', 'guida-antipanico-soffocamento' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Il plugin genera automaticamente le pagine "Condizioni di vendita" e "Privacy" (vedi URL in alto) e le collega da sole nel footer e nel popup di preordine. Compila questi campi solo se vuoi sostituirle con un URL esterno diverso.', 'guida-antipanico-soffocamento' ); ?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="gaps_terms_url"><?php esc_html_e( 'Condizioni di vendita (URL esterno)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td><input type="url" id="gaps_terms_url" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[terms_url]" value="<?php echo esc_attr( $settings['terms_url'] ); ?>" class="regular-text" placeholder="https://" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_privacy_url"><?php esc_html_e( 'Privacy (URL esterno)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td><input type="url" id="gaps_privacy_url" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[privacy_url]" value="<?php echo esc_attr( $settings['privacy_url'] ); ?>" class="regular-text" placeholder="https://" /></td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Dati legali e azienda — Condizioni di vendita e Privacy', 'guida-antipanico-soffocamento' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Questi dati compaiono nelle pagine "Condizioni di vendita" e "Privacy" generate dal plugin. Finché un campo obbligatorio non viene compilato, sulla pagina pubblica appare evidenziato in giallo come promemoria.', 'guida-antipanico-soffocamento' ); ?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="gaps_legal_company_name"><?php esc_html_e( 'Ragione sociale', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_legal_company_name" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[legal_company_name]" value="<?php echo esc_attr( $settings['legal_company_name'] ); ?>" class="regular-text" placeholder="Formalife S.r.l." />
							<p class="description"><?php esc_html_e( 'Denominazione legale completa del venditore, comprensiva della forma giuridica (es. S.r.l., S.r.l.s., ditta individuale).', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_legal_vat_number"><?php esc_html_e( 'Partita IVA / Codice Fiscale', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td><input type="text" id="gaps_legal_vat_number" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[legal_vat_number]" value="<?php echo esc_attr( $settings['legal_vat_number'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_legal_address"><?php esc_html_e( 'Sede legale', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_legal_address" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[legal_address]" value="<?php echo esc_attr( $settings['legal_address'] ); ?>" class="large-text" />
							<p class="description"><?php esc_html_e( 'Indirizzo completo: via/piazza e numero civico, CAP, città, provincia.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_legal_pec"><?php esc_html_e( 'PEC (posta elettronica certificata)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td><input type="text" id="gaps_legal_pec" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[legal_pec]" value="<?php echo esc_attr( $settings['legal_pec'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_legal_court"><?php esc_html_e( 'Città della sede legale (per il foro competente)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_legal_court" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[legal_court]" value="<?php echo esc_attr( $settings['legal_court'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Usata solo per le controversie tra imprese: per i consumatori la legge impone comunque il foro di residenza del consumatore, indipendentemente da questo campo (vedi testo delle Condizioni di vendita).', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_legal_rea"><?php esc_html_e( 'Numero REA (opzionale)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td><input type="text" id="gaps_legal_rea" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[legal_rea]" value="<?php echo esc_attr( $settings['legal_rea'] ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_legal_share_capital"><?php esc_html_e( 'Capitale sociale (opzionale)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_legal_share_capital" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[legal_share_capital]" value="<?php echo esc_attr( $settings['legal_share_capital'] ); ?>" class="regular-text" placeholder="es. 10.000 € i.v." />
							<p class="description"><?php esc_html_e( 'Rilevante solo per società di capitali (S.r.l. e simili). Lascia vuoto se non applicabile.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_legal_dpo_email"><?php esc_html_e( 'Referente/DPO privacy (opzionale)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_legal_dpo_email" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[legal_dpo_email]" value="<?php echo esc_attr( $settings['legal_dpo_email'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Compila solo se è stato nominato un Responsabile della Protezione dei Dati o un referente privacy diverso dall\'email di contatto generale.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_shipping_carrier"><?php esc_html_e( 'Corriere di spedizione (opzionale)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_shipping_carrier" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[shipping_carrier]" value="<?php echo esc_attr( $settings['shipping_carrier'] ); ?>" class="regular-text" placeholder="es. BRT, GLS, Poste Italiane" />
							<p class="description"><?php esc_html_e( 'Se lasciato vuoto, le Condizioni di vendita useranno una dicitura generica ("corriere espresso incaricato").', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_guarantee_period_days"><?php esc_html_e( 'Durata garanzia commerciale "soddisfatti o rimborsati" (giorni)', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td><input type="number" min="1" id="gaps_guarantee_period_days" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[guarantee_period_days]" value="<?php echo esc_attr( $settings['guarantee_period_days'] ); ?>" class="small-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_legal_last_updated"><?php esc_html_e( 'Data "ultimo aggiornamento" mostrata su Condizioni/Privacy', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td>
							<input type="text" id="gaps_legal_last_updated" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[legal_last_updated]" value="<?php echo esc_attr( $settings['legal_last_updated'] ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Aggiorna manualmente questa data ogni volta che modifichi il testo legale.', 'guida-antipanico-soffocamento' ); ?></p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Dato statistico — sezione "Costo dell\'inazione"', 'guida-antipanico-soffocamento' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Statistica 1', 'guida-antipanico-soffocamento' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[stat1_number]" value="<?php echo esc_attr( $settings['stat1_number'] ); ?>" class="small-text" placeholder="500" />
							<input type="text" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[stat1_label]" value="<?php echo esc_attr( $settings['stat1_label'] ); ?>" class="regular-text" style="width:60%;" placeholder="<?php esc_attr_e( 'etichetta', 'guida-antipanico-soffocamento' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Statistica 2', 'guida-antipanico-soffocamento' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[stat2_number]" value="<?php echo esc_attr( $settings['stat2_number'] ); ?>" class="small-text" placeholder="1.000" />
							<input type="text" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[stat2_label]" value="<?php echo esc_attr( $settings['stat2_label'] ); ?>" class="regular-text" style="width:60%;" placeholder="<?php esc_attr_e( 'etichetta', 'guida-antipanico-soffocamento' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Statistica 3', 'guida-antipanico-soffocamento' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[stat3_number]" value="<?php echo esc_attr( $settings['stat3_number'] ); ?>" class="small-text" placeholder="60-80%" />
							<input type="text" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[stat3_label]" value="<?php echo esc_attr( $settings['stat3_label'] ); ?>" class="regular-text" style="width:60%;" placeholder="<?php esc_attr_e( 'etichetta', 'guida-antipanico-soffocamento' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gaps_stat_source"><?php esc_html_e( 'Fonte', 'guida-antipanico-soffocamento' ); ?></label></th>
						<td><input type="text" id="gaps_stat_source" name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[stat_source]" value="<?php echo esc_attr( $settings['stat_source'] ); ?>" class="large-text" /></td>
					</tr>
				</table>

				<?php submit_button( __( 'Salva impostazioni', 'guida-antipanico-soffocamento' ) ); ?>
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
		$field_id = 'gaps_' . $key;
		$url      = $value ? gaps_get_image_url( $value, 'medium' ) : '';
		$mime     = $value ? get_post_mime_type( $value ) : '';
		$is_webp  = ( 'image/webp' === $mime );
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<div class="gaps-image-field" data-field="<?php echo esc_attr( $field_id ); ?>">
					<div class="gaps-image-preview" id="<?php echo esc_attr( $field_id ); ?>_preview">
						<?php if ( $url ) : ?>
							<img src="<?php echo esc_url( $url ); ?>" alt="" />
						<?php endif; ?>
					</div>
					<input
						type="hidden"
						id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]"
						value="<?php echo esc_attr( $value ); ?>"
						class="gaps-image-id-input"
					/>
					<p>
						<button type="button" class="button gaps-choose-image"><?php echo $value ? esc_html__( 'Sostituisci immagine', 'guida-antipanico-soffocamento' ) : esc_html__( 'Seleziona immagine', 'guida-antipanico-soffocamento' ); ?></button>
						<button type="button" class="button gaps-remove-image" <?php echo $value ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Rimuovi', 'guida-antipanico-soffocamento' ); ?></button>
					</p>
					<p class="gaps-webp-hint" <?php echo ( $value && ! $is_webp ) ? '' : 'style="display:none;"'; ?>>
						<?php esc_html_e( 'Consiglio: per prestazioni migliori, converti questa immagine in formato WebP prima di caricarla (la conversione va fatta manualmente, il plugin non la esegue automaticamente).', 'guida-antipanico-soffocamento' ); ?>
					</p>
					<p class="description"><?php echo esc_html( $help ); ?></p>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Renderizza una riga di form-table per un campo file generico (non
	 * immagine, es. PDF) con uploader wp.media. A differenza di
	 * render_image_field(), non mostra un'anteprima grafica ma il nome del
	 * file e un link "Apri file corrente".
	 *
	 * @param string $key   Chiave del campo nell'array impostazioni.
	 * @param int    $value ID allegato corrente.
	 * @param string $label Etichetta del campo.
	 * @param string $help  Testo di aiuto.
	 */
	private static function render_file_field( $key, $value, $label, $help ) {
		$value    = absint( $value );
		$field_id = 'gaps_' . $key;
		$url      = $value ? gaps_get_attachment_file_url( $value ) : '';
		$filename = $url ? wp_basename( $url ) : '';
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<div class="gaps-file-field" data-field="<?php echo esc_attr( $field_id ); ?>">
					<div class="gaps-file-preview" id="<?php echo esc_attr( $field_id ); ?>_preview">
						<?php if ( $url ) : ?>
							<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $filename ); ?></a>
						<?php else : ?>
							<em><?php esc_html_e( 'Nessun file caricato: verrà usato quello incluso nel plugin.', 'guida-antipanico-soffocamento' ); ?></em>
						<?php endif; ?>
					</div>
					<input
						type="hidden"
						id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]"
						value="<?php echo esc_attr( $value ); ?>"
						class="gaps-file-id-input"
					/>
					<p>
						<button type="button" class="button gaps-choose-file"><?php esc_html_e( 'Scegli file', 'guida-antipanico-soffocamento' ); ?></button>
						<button type="button" class="button gaps-remove-file" <?php echo $value ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Rimuovi (torna al file incluso)', 'guida-antipanico-soffocamento' ); ?></button>
					</p>
					<p class="description"><?php echo esc_html( $help ); ?></p>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Renderizza una riga di form-table per un campo colore con wp-color-picker.
	 *
	 * @param string $key   Chiave del campo nell'array impostazioni.
	 * @param string $value Colore esadecimale corrente.
	 * @param string $label Etichetta del campo.
	 */
	private static function render_color_field( $key, $value, $label ) {
		$field_id = 'gaps_' . $key;
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input
					type="text"
					id="<?php echo esc_attr( $field_id ); ?>"
					name="<?php echo esc_attr( GAPS_OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]"
					value="<?php echo esc_attr( $value ); ?>"
					class="gaps-color-field"
					data-default-color="<?php echo esc_attr( $value ); ?>"
				/>
			</td>
		</tr>
		<?php
	}
}
