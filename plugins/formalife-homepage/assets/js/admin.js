/* global jQuery, wp */
( function ( $ ) {
	'use strict';

	$( function () {
		// Color picker per gli overlay configurabili; la palette del sito resta fissa nel CSS.
		if ( $.fn.wpColorPicker ) {
			$( '.fmh-color-field' ).wpColorPicker();
		}

		// Uploader Libreria Media per ogni campo immagine.
		$( '.fmh-image-field' ).each( function () {
			var $field    = $( this );
			var $input    = $field.find( '.fmh-image-id-input' );
			var $preview  = $field.find( '.fmh-image-preview' );
			var $choose   = $field.find( '.fmh-choose-image' );
			var $remove   = $field.find( '.fmh-remove-image' );
			var frame;

			$choose.on( 'click', function ( e ) {
				e.preventDefault();

				if ( frame ) {
					frame.open();
					return;
				}

				frame = wp.media( {
					title: 'Scegli immagine',
					button: { text: 'Usa questa immagine' },
					multiple: false
				} );

				frame.on( 'select', function () {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					$input.val( attachment.id );

					var imgUrl = attachment.sizes && attachment.sizes.medium
						? attachment.sizes.medium.url
						: attachment.url;

					$preview.html( '<img src="' + imgUrl + '" alt="" />' );
					$remove.show();
				} );

				frame.open();
			} );

			$remove.on( 'click', function ( e ) {
				e.preventDefault();
				$input.val( '' );
				$preview.html( '' );
				$remove.hide();
			} );
		} );

		var $sessions = $( '#fmh-course-sessions' );
		$( '#fmh-add-session' ).on( 'click', function () {
			var index = $sessions.children( '.fmh-session-row' ).length;
			var id = 'session-' + Date.now().toString( 36 );
			var base = 'fmh_course_settings[sessions][' + index + ']';
			var html = '<fieldset class="fmh-session-row"><legend>Nuova sessione</legend>' +
				'<input type="hidden" name="' + base + '[id]" value="' + id + '">' +
				'<label><input type="checkbox" name="' + base + '[enabled]" value="1"> Attiva</label> ' +
				'<label><input type="checkbox" name="' + base + '[sales_open]" value="1"> Vendite aperte</label><br>' +
				'<label>Data <input required type="date" name="' + base + '[date]"></label> ' +
				'<label>Orario <input type="text" name="' + base + '[time]"></label> ' +
				'<label>Città <input type="text" name="' + base + '[city]"></label> ' +
				'<label>Sede pubblica <input type="text" name="' + base + '[venue]"></label> ' +
				'<label>Fallback interno <input type="text" name="' + base + '[fallback]"></label> ' +
				'<label>Capienza <input type="number" min="1" value="12" name="' + base + '[capacity]"></label> ' +
				'<label>Ordine <input type="number" value="' + ( ( index + 1 ) * 10 ) + '" name="' + base + '[sort_order]"></label> ' +
				'<label>Stato admin <input type="text" name="' + base + '[admin_status]"></label><br>' +
				'<label>Nota <textarea name="' + base + '[note]"></textarea></label> ' +
				'<label>Override singolo € <input type="number" step=".01" name="' + base + '[single_price_eur]"></label> ' +
				'<label>Override coppia € <input type="number" step=".01" name="' + base + '[couple_price_eur]"></label> ' +
				'<button type="button" class="button-link-delete fmh-remove-session">Rimuovi</button></fieldset>';
			$sessions.append( html );
		} );
		$sessions.on( 'click', '.fmh-remove-session', function () { $( this ).closest( '.fmh-session-row' ).remove(); } );
	} );
} )( jQuery );
