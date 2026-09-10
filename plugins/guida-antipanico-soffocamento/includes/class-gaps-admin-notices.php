<?php
/**
 * Avvisi nella bacheca di amministrazione:
 * 1. Conflitto di slug pagina rilevato all'attivazione.
 * 2. Nessuna pagina Stripe configurata come destinazione dopo il preordine.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GAPS_Admin_Notices {

	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'render_notices' ) );
	}

	public static function render_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::render_conflict_notice();
		self::render_missing_cta_notice();
		self::render_missing_webhook_notice();
	}

	/**
	 * Avviso: esiste già una pagina con lo slug di una delle pagine gestite
	 * dal plugin (landing, condizioni di vendita, privacy), non creata da noi.
	 * Un avviso separato per ciascuna pagina in conflitto.
	 */
	private static function render_conflict_notice() {
		foreach ( GAPS_Page_Manager::get_registry() as $def ) {
			$conflict_page_id = (int) get_option( $def['conflict_option'] );
			if ( ! $conflict_page_id ) {
				continue;
			}

			$edit_link = get_edit_post_link( $conflict_page_id );
			?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Guida Anti-Panico al Soffocamento Pediatrico:', 'guida-antipanico-soffocamento' ); ?></strong>
					<?php
					printf(
						/* translators: 1: nome della pagina gestita dal plugin (es. "Privacy Policy"), 2: slug della pagina in conflitto */
						esc_html__( 'esiste già una pagina con lo slug "%2$s", destinato alla pagina "%1$s" di questo plugin, ma quella pagina non è stata creata da noi. Per sicurezza non è stata modificata. Rinomina lo slug della pagina esistente oppure elimina/rinomina quella pagina e poi disattiva/riattiva il plugin per generare la pagina corretta.', 'guida-antipanico-soffocamento' ),
						esc_html( $def['admin_label'] ),
						esc_html( $def['slug'] )
					);
					?>
					<?php if ( $edit_link ) : ?>
						<a href="<?php echo esc_url( $edit_link ); ?>"><?php esc_html_e( 'Apri la pagina in conflitto →', 'guida-antipanico-soffocamento' ); ?></a>
					<?php endif; ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Promemoria: il modulo di preordine funziona comunque (salva i lead),
	 * ma senza URL Stripe configurato i visitatori non vengono reindirizzati
	 * al pagamento dopo l'invio.
	 */
	private static function render_missing_cta_notice() {
		if ( gaps_has_cta_destination() ) {
			return;
		}

		$settings_url = admin_url( 'admin.php?page=' . GAPS_SLUG );
		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'Guida Anti-Panico al Soffocamento Pediatrico:', 'guida-antipanico-soffocamento' ); ?></strong>
				<?php esc_html_e( 'non hai ancora impostato le chiavi Stripe. Il popup di preordine funziona comunque e salva i lead in "Preordini ricevuti", ma lo step di pagamento non potrà completarsi finché non imposti la chiave pubblicabile e quella segreta nel pannello impostazioni.', 'guida-antipanico-soffocamento' ); ?>
				<a href="<?php echo esc_url( $settings_url ); ?>"><?php esc_html_e( 'Vai alle impostazioni →', 'guida-antipanico-soffocamento' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Avviso critico, visibile in tutta la bacheca (non solo nella pagina
	 * impostazioni): le chiavi Stripe sono configurate — quindi i clienti
	 * possono già pagare — ma la chiave segreta del webhook no. In questo
	 * stato ogni pagamento va a buon fine su Stripe ma il plugin non lo
	 * saprà mai: i preordini restano bloccati su "In attesa di pagamento" e
	 * nessuna email (né al cliente né alla notifica interna) parte. È lo
	 * scenario più subdolo di tutti perché non produce nessun errore
	 * visibile: sembra tutto a posto finché non si controllano a mano i
	 * preordini pagati che non risultano tali.
	 */
	private static function render_missing_webhook_notice() {
		if ( ! gaps_has_cta_destination() ) {
			// Le chiavi Stripe non sono nemmeno impostate: si applica già
			// l'avviso precedente, più generale. Evitiamo di mostrare due
			// avvisi Stripe sovrapposti.
			return;
		}

		$settings = gaps_get_settings();
		if ( '' !== trim( $settings['stripe_webhook_secret'] ) ) {
			return;
		}

		$settings_url = admin_url( 'admin.php?page=' . GAPS_SLUG );
		?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Guida Anti-Panico al Soffocamento Pediatrico — pagamenti non registrati:', 'guida-antipanico-soffocamento' ); ?></strong>
				<?php esc_html_e( 'le chiavi Stripe sono configurate e i clienti possono già pagare, ma la chiave segreta del webhook non è ancora stata impostata. Senza di essa il plugin non può verificare i pagamenti confermati da Stripe: restano su "In attesa di pagamento" in "Preordini ricevuti" e le email di conferma (cliente + notifica interna) non partono, anche per ordini già pagati regolarmente.', 'guida-antipanico-soffocamento' ); ?>
				<a href="<?php echo esc_url( $settings_url . '#gaps_stripe_webhook_secret' ); ?>"><?php esc_html_e( 'Completa la configurazione del webhook →', 'guida-antipanico-soffocamento' ); ?></a>
			</p>
		</div>
		<?php
	}
}
