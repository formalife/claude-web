<?php
/**
 * Helper per il markup ripetitivo dei pannelli impostazioni in bacheca (una
 * riga <tr><th>...</th><td>...</td></tr> per campo). Puramente additivo:
 * guida-antipanico-soffocamento continua a usare il proprio markup scritto
 * a mano finché non viene migrato di proposito — nessuna modifica
 * retroattiva imposta da questo file.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Formalife_Settings_Field' ) ) :

	class Formalife_Settings_Field {

		/**
		 * Riga di impostazioni con un campo di testo.
		 *
		 * @param string $id          ID/for del campo.
		 * @param string $name        Attributo name (es. "plugin_opts[campo]").
		 * @param string $value       Valore corrente.
		 * @param string $label       Etichetta.
		 * @param string $description Testo d'aiuto sotto al campo (opzionale).
		 * @param string $placeholder Placeholder (opzionale).
		 */
		public static function text( $id, $name, $value, $label, $description = '', $placeholder = '' ) {
			printf(
				'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="text" id="%1$s" name="%3$s" value="%4$s" class="regular-text" placeholder="%5$s" />%6$s</td></tr>',
				esc_attr( $id ),
				esc_html( $label ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( $placeholder ),
				$description ? '<p class="description">' . esc_html( $description ) . '</p>' : ''
			);
		}

		/**
		 * Riga di impostazioni con una casella di spunta.
		 *
		 * @param string $id          ID/for del campo.
		 * @param string $name        Attributo name.
		 * @param bool   $checked     Stato corrente.
		 * @param string $label       Etichetta (mostrata sia nella colonna sinistra che accanto alla casella).
		 * @param string $description Testo d'aiuto (opzionale).
		 */
		public static function checkbox( $id, $name, $checked, $label, $description = '' ) {
			printf(
				'<tr><th scope="row">%2$s</th><td><label for="%1$s"><input type="checkbox" id="%1$s" name="%3$s" value="1" %4$s /> %2$s</label>%5$s</td></tr>',
				esc_attr( $id ),
				esc_html( $label ),
				esc_attr( $name ),
				checked( $checked, true, false ),
				$description ? '<p class="description">' . esc_html( $description ) . '</p>' : ''
			);
		}

		/**
		 * Riga di impostazioni con un'area di testo multi-riga.
		 *
		 * @param string $id          ID/for del campo.
		 * @param string $name        Attributo name.
		 * @param string $value       Valore corrente.
		 * @param string $label       Etichetta.
		 * @param string $description Testo d'aiuto (opzionale).
		 * @param int    $rows        Numero di righe (default 4).
		 */
		public static function textarea( $id, $name, $value, $label, $description = '', $rows = 4 ) {
			printf(
				'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><textarea id="%1$s" name="%3$s" rows="%5$d" class="large-text">%4$s</textarea>%6$s</td></tr>',
				esc_attr( $id ),
				esc_html( $label ),
				esc_attr( $name ),
				esc_textarea( $value ),
				absint( $rows ),
				$description ? '<p class="description">' . esc_html( $description ) . '</p>' : ''
			);
		}
	}

endif;
