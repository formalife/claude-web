/* global jQuery, wp */
( function ( $ ) {
	'use strict';

	$( function () {
		// Color picker nativo di WordPress.
		if ( $.fn.wpColorPicker ) {
			$( '.gaps-color-field' ).wpColorPicker();
		}

		// Uploader Libreria Media per ogni campo immagine.
		$( '.gaps-image-field' ).each( function () {
			var $field    = $( this );
			var $input    = $field.find( '.gaps-image-id-input' );
			var $preview  = $field.find( '.gaps-image-preview' );
			var $choose   = $field.find( '.gaps-choose-image' );
			var $remove   = $field.find( '.gaps-remove-image' );
			var $webpHint = $field.find( '.gaps-webp-hint' );
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
					multiple: false,
					library: { type: 'image' }
				} );

				frame.on( 'select', function () {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					$input.val( attachment.id );

					var imgUrl = attachment.sizes && attachment.sizes.medium
						? attachment.sizes.medium.url
						: attachment.url;

					$preview.html( '<img src="' + imgUrl + '" alt="" />' );
					$remove.show();
					$choose.text( 'Sostituisci immagine' );

					// Avviso WebP: mostrato solo se il file selezionato NON è
					// già in formato WebP (la conversione resta manuale, il
					// plugin non blocca né converte automaticamente altri
					// formati, si limita a segnalarlo all'admin).
					if ( $webpHint.length ) {
						$webpHint.toggle( 'image/webp' !== attachment.mime );
					}
				} );

				frame.open();
			} );

			$remove.on( 'click', function ( e ) {
				e.preventDefault();
				$input.val( '' );
				$preview.html( '' );
				$remove.hide();
				$choose.text( 'Seleziona immagine' );
				if ( $webpHint.length ) {
					$webpHint.hide();
				}
			} );
		} );

		// Uploader Libreria Media per campi file generici (non immagine, es.
		// il PDF "I tuoi numeri importanti"): stessa logica dei campi
		// immagine, ma senza anteprima grafica e senza restrizione di tipo,
		// così l'admin può scegliere anche un PDF già presente in libreria.
		$( '.gaps-file-field' ).each( function () {
			var $field   = $( this );
			var $input   = $field.find( '.gaps-file-id-input' );
			var $preview = $field.find( '.gaps-file-preview' );
			var $choose  = $field.find( '.gaps-choose-file' );
			var $remove  = $field.find( '.gaps-remove-file' );
			var frame;

			$choose.on( 'click', function ( e ) {
				e.preventDefault();

				if ( frame ) {
					frame.open();
					return;
				}

				frame = wp.media( {
					title: 'Scegli file',
					button: { text: 'Usa questo file' },
					multiple: false
				} );

				frame.on( 'select', function () {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					$input.val( attachment.id );

					var filename = attachment.filename || attachment.url;
					$preview.html( '<a href="' + attachment.url + '" target="_blank" rel="noopener noreferrer">' + filename + '</a>' );
					$remove.show();
				} );

				frame.open();
			} );

			$remove.on( 'click', function ( e ) {
				e.preventDefault();
				$input.val( '' );
				$preview.html( '<em>Nessun file caricato: verrà usato quello incluso nel plugin.</em>' );
				$remove.hide();
			} );
		} );
	} );
} )( jQuery );
