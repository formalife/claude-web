<?php
/**
 * Avvisi nella bacheca di amministrazione: conflitto di slug pagina
 * rilevato all'attivazione (vedi FMH_Page_Manager::activate_single_page()).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FMH_Admin_Notices {

	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'render_notices' ) );
	}

	public static function render_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::render_conflict_notice();
	}

	/**
	 * Avviso: esiste già una pagina con lo slug "home" (o di un'altra
	 * pagina gestita in futuro dal plugin), non creata da questo plugin.
	 * Molto probabile per "home", spesso creata di default da WordPress o
	 * dal tema: per sicurezza il plugin non la tocca mai.
	 */
	private static function render_conflict_notice() {
		foreach ( FMH_Page_Manager::get_registry() as $def ) {
			$conflict_page_id = (int) get_option( $def['conflict_option'] );
			if ( ! $conflict_page_id ) {
				continue;
			}

			$edit_link = get_edit_post_link( $conflict_page_id );
			?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Formalife Homepage:', 'formalife-homepage' ); ?></strong>
					<?php
					printf(
						/* translators: 1: nome della pagina gestita dal plugin (es. "Homepage"), 2: slug della pagina in conflitto */
						esc_html__( 'esiste già una pagina con lo slug "%2$s", destinato alla pagina "%1$s" di questo plugin, ma quella pagina non è stata creata da noi. Per sicurezza non è stata modificata. Rinomina lo slug della pagina esistente (o svuotane il contenuto e riusala) oppure elimina/rinomina quella pagina e poi disattiva/riattiva il plugin per generare la pagina corretta.', 'formalife-homepage' ),
						esc_html( $def['admin_label'] ),
						esc_html( $def['slug'] )
					);
					?>
					<?php if ( $edit_link ) : ?>
						<a href="<?php echo esc_url( $edit_link ); ?>"><?php esc_html_e( 'Apri la pagina in conflitto →', 'formalife-homepage' ); ?></a>
					<?php endif; ?>
				</p>
			</div>
			<?php
		}
	}
}
