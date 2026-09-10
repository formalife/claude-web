/* global fmhFrontend */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		initScrollReveal();
		initWaitlistForm();
	} );

	/* ==========================================================
	 * Animazioni "reveal" on-scroll (fade + slide-up leggero).
	 * ========================================================== */
	function initScrollReveal() {
		var items = document.querySelectorAll( '.fmh-reveal' );
		if ( ! items.length ) {
			return;
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			items.forEach( function ( el ) { el.classList.add( 'is-visible' ); } );
			return;
		}

		var observer = new IntersectionObserver( function ( entries, obs ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					obs.unobserve( entry.target );
				}
			} );
		}, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' } );

		items.forEach( function ( el ) { observer.observe( el ); } );
	}

	/* ==========================================================
	 * Modulo "Voglio essere avvisato/a" (Sezione 6): invio via
	 * fetch/AJAX, nessun popup — il messaggio di esito compare
	 * inline, sotto al bottone.
	 * ========================================================== */
	function initWaitlistForm() {
		var form = document.getElementById( 'fmh-waitlist-form' );
		if ( ! form || ! window.fmhFrontend ) {
			return;
		}

		var messageBox = form.querySelector( '.fmh-waitlist-message' );
		var submitBtn  = form.querySelector( '[type="submit"]' );

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			setMessage( '', '' );

			var formData = new FormData( form );
			formData.append( 'action', fmhFrontend.action );
			formData.append( 'nonce', fmhFrontend.nonce );
			formData.set( 'consenso', form.querySelector( '#fmh-w-consenso' ).checked ? '1' : '0' );

			if ( submitBtn ) {
				submitBtn.disabled = true;
				submitBtn.dataset.originalText = submitBtn.textContent;
				submitBtn.textContent = fmhFrontend.i18n.sending;
			}

			fetch( fmhFrontend.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData
			} )
				.then( function ( response ) { return response.json(); } )
				.then( function ( data ) {
					if ( data && data.success ) {
						setMessage( data.data.message || '', 'success' );
						form.reset();
					} else {
						var msg = ( data && data.data && data.data.message ) ? data.data.message : fmhFrontend.i18n.genericError;
						setMessage( msg, 'error' );
					}
				} )
				.catch( function () {
					setMessage( fmhFrontend.i18n.genericError, 'error' );
				} )
				.finally( function () {
					if ( submitBtn ) {
						submitBtn.disabled = false;
						submitBtn.textContent = submitBtn.dataset.originalText;
					}
				} );
		} );

		function setMessage( text, type ) {
			if ( ! messageBox ) {
				return;
			}
			messageBox.textContent = text;
			messageBox.className = 'fmh-waitlist-message';
			if ( type ) {
				messageBox.classList.add( 'fmh-waitlist-message--' + type );
			}
		}
	}
} )();
