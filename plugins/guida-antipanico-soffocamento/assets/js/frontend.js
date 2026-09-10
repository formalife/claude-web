/* global gapsFrontend, GAPSStripeLoader */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		initPreorderModal();
		initPreviewLightbox();
		initScrollReveal();
		initStickyCta();
	} );

	/* ==========================================================
	 * Popup di preordine: apertura, navigazione a step, pagamento Stripe
	 * ========================================================== */
	function initPreorderModal() {
		var overlay = document.getElementById( 'gaps-preorder-overlay' );
		if ( ! overlay ) {
			return;
		}

		var closeBtn       = overlay.querySelector( '.gaps-modal-close' );
		var form           = overlay.querySelector( '#gaps-preorder-form' );
		var invoiceToggle  = overlay.querySelector( '#gaps-f-invoice' );
		var invoiceFields  = overlay.querySelector( '#gaps-invoice-fields' );
		var privacyCheck   = overlay.querySelector( '#gaps-f-privacy' );
		var quantityInput  = overlay.querySelector( '#gaps-f-quantita' );
		var totalDisplay   = overlay.querySelector( '#gaps-order-total' );
		var payButton      = overlay.querySelector( '#gaps-pay-button' );
		var paymentDiv     = overlay.querySelector( '#gaps-payment-element' );
		var paymentLoading = overlay.querySelector( '#gaps-payment-loading' );
		var lastFocused    = null;

		/*
		 * Stripe.js non viene più caricato staticamente da WordPress: lo
		 * script ufficiale (js.stripe.com) e l'istanza Stripe vengono
		 * scaricati/creati solo al primo click su un CTA che apre questo
		 * popup, tramite il loader condiviso GAPSStripeLoader (vedi
		 * assets/js/gaps-stripe-loader.js). Le Promise sono memorizzate qui
		 * per essere riusate ad ogni riapertura del popup nella stessa
		 * pagina, senza mai riscaricare lo script.
		 */
		var stripeInstancePromise = null;
		var stripe                = null;

		function ensureStripeInstance() {
			if ( ! window.GAPSStripeLoader || ! window.gapsFrontend || ! gapsFrontend.stripePublishableKey ) {
				return Promise.reject( new Error( 'Stripe non configurato.' ) );
			}
			if ( ! stripeInstancePromise ) {
				stripeInstancePromise = window.GAPSStripeLoader.getStripeInstance( gapsFrontend.stripePublishableKey )
					.then( function ( instance ) {
						stripe = instance;
						return instance;
					} )
					.catch( function ( error ) {
						// Un tentativo fallito (es. rete lenta/bloccata) non deve
						// restare bloccato per sempre: il prossimo tentativo (nuovo
						// click, o retry dopo errore) riparte da zero.
						stripeInstancePromise = null;
						throw error;
					} );
			}
			return stripeInstancePromise;
		}

		/* ---------- Fattura: mostra/nasconde i campi ---------- */
		function updateInvoiceFields() {
			var enabled = Boolean( invoiceToggle && invoiceToggle.checked );
			if ( invoiceFields ) {
				invoiceFields.hidden = ! enabled;
				invoiceFields.querySelectorAll( 'input' ).forEach( function ( field ) {
					field.disabled = ! enabled;
				} );
			}
			if ( invoiceToggle ) {
				invoiceToggle.setAttribute( 'aria-expanded', enabled ? 'true' : 'false' );
			}
		}
		if ( invoiceToggle ) {
			invoiceToggle.addEventListener( 'change', updateInvoiceFields );
			updateInvoiceFields();
		}

		/* ---------- Apertura / chiusura popup ---------- */
		var checkoutEventTracked = false;

		function openModal() {
			lastFocused = document.activeElement;
			overlay.classList.add( 'is-open' );
			document.body.classList.add( 'gaps-modal-open' );
			goToStep( 1 );
			var firstField = overlay.querySelector( 'input, textarea' );
			if ( firstField ) {
				firstField.focus();
			}

			// Comincia subito, in parallelo, a scaricare Stripe: nella
			// maggior parte dei casi sarà già pronto molto prima che
			// l'utente raggiunga lo step 3 (pagamento), senza che l'utente
			// se ne accorga. Fallimenti qui non bloccano l'apertura del
			// popup: verranno rigestiti (con possibilità di retry) solo se
			// e quando l'utente arriva davvero al pagamento.
			ensureStripeInstance().catch( function () {} );

			if ( ! checkoutEventTracked && 'function' === typeof window.gapsTrackEvent ) {
				checkoutEventTracked = true;
				window.gapsTrackEvent( 'InitiateCheckout' );
			}
		}

		function closeModal() {
			overlay.classList.remove( 'is-open' );
			document.body.classList.remove( 'gaps-modal-open' );
			if ( lastFocused && typeof lastFocused.focus === 'function' ) {
				lastFocused.focus();
			}
		}

		/*
		 * Event delegation su document per tutti i CTA che aprono il popup
		 * (classe condivisa ".gaps-open-preorder", usata da ogni pulsante
		 * "Prenota ora" della pagina, inclusa la barra CTA fissa mobile):
		 * funziona anche per CTA aggiunti dinamicamente o presenti in
		 * sezioni/landing future, senza dover ricollegare un listener per
		 * ciascuno.
		 */
		document.addEventListener( 'click', function ( e ) {
			var trigger = e.target.closest ? e.target.closest( '.gaps-open-preorder' ) : null;
			if ( ! trigger ) {
				return;
			}
			e.preventDefault();
			openModal();
		} );

		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', closeModal );
		}
		overlay.addEventListener( 'click', function ( e ) {
			if ( e.target === overlay ) {
				closeModal();
			}
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && overlay.classList.contains( 'is-open' ) ) {
				closeModal();
			}
		} );

		/* ---------- Navigazione fra step ---------- */
		function goToStep( step ) {
			overlay.querySelectorAll( '.gaps-form-step' ).forEach( function ( el ) {
				el.hidden = ( parseInt( el.getAttribute( 'data-step' ), 10 ) !== step );
			} );
			overlay.querySelectorAll( '.gaps-step-intro' ).forEach( function ( el ) {
				el.hidden = ( parseInt( el.getAttribute( 'data-step-intro' ), 10 ) !== step );
			} );
		}

		function setStepMessage( step, text, type ) {
			var box = overlay.querySelector( '[data-step-message="' + step + '"]' );
			if ( ! box ) {
				return;
			}
			box.textContent = text || '';
			box.className = 'gaps-form-message';
			if ( type ) {
				box.classList.add( 'gaps-form-message--' + type );
			}
		}

		function val( id ) {
			var el = overlay.querySelector( '#' + id );
			return el ? el.value.trim() : '';
		}

		function validateStep1() {
			if ( ! val( 'gaps-f-nome' ) || ! val( 'gaps-f-cognome' ) || ! val( 'gaps-f-telefono' ) || ! val( 'gaps-f-email' ) ) {
				setStepMessage( 1, gapsFrontend.i18n.genericError, 'error' );
				return false;
			}
			return true;
		}

		function validateStep2() {
			if ( ! val( 'gaps-f-indirizzo' ) || ! val( 'gaps-f-citta' ) || ! val( 'gaps-f-provincia' ) || ! val( 'gaps-f-cap' ) ) {
				setStepMessage( 2, gapsFrontend.i18n.genericError, 'error' );
				return false;
			}
			if ( invoiceToggle && invoiceToggle.checked ) {
				if ( ! val( 'gaps-f-invoice-holder' ) || ! val( 'gaps-f-billing-address' ) || ! val( 'gaps-f-vat-number' ) || ! val( 'gaps-f-recipient' ) ) {
					setStepMessage( 2, gapsFrontend.i18n.genericError, 'error' );
					return false;
				}
			}
			if ( ! privacyCheck || ! privacyCheck.checked ) {
				setStepMessage( 2, gapsFrontend.i18n.genericError, 'error' );
				return false;
			}
			return true;
		}

		overlay.querySelectorAll( '.gaps-step-back' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				goToStep( parseInt( btn.getAttribute( 'data-goto' ), 10 ) );
			} );
		} );

		overlay.querySelectorAll( '.gaps-step-next' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var goto = parseInt( btn.getAttribute( 'data-goto' ), 10 );

				if ( 2 === goto && ! validateStep1() ) {
					return;
				}
				if ( 3 === goto ) {
					if ( ! validateStep2() ) {
						return;
					}
					goToStep( 3 );
					createPaymentIntentAndMount();
					return;
				}
				goToStep( goto );
			} );
		} );

		/* ---------- Step 3: quantità, totale, Stripe Payment Element ---------- */
		function formatEuro( cents ) {
			return ( cents / 100 ).toLocaleString( 'it-IT', { style: 'currency', currency: 'EUR' } );
		}

		function updateTotal() {
			if ( ! totalDisplay || ! window.gapsFrontend ) {
				return;
			}
			var qty = Math.max( 1, parseInt( quantityInput.value, 10 ) || 1 );
			var shipping = gapsFrontend.shippingCents || 0;
			totalDisplay.textContent = formatEuro( gapsFrontend.unitPriceCents * qty + shipping );
		}

		if ( quantityInput ) {
			quantityInput.addEventListener( 'input', updateTotal );
		}

		/* ---------- Stato di caricamento del pagamento ---------- */
		function setPaymentLoading( isLoading ) {
			if ( paymentDiv ) {
				paymentDiv.setAttribute( 'aria-busy', isLoading ? 'true' : 'false' );
			}
			if ( paymentLoading ) {
				paymentLoading.hidden = ! isLoading;
			}
			if ( payButton ) {
				payButton.disabled = isLoading;
			}
		}

		/*
		 * Richiesta di Payment Intent al server: memorizzata per evitare
		 * duplicati se l'utente preme più volte "Continua al pagamento"
		 * (doppio click, rete lenta). La chiave include i dati che
		 * determinano l'importo/il contenuto dell'ordine: se cambiano,
		 * una nuova richiesta viene creata; se sono identici a quelli
		 * dell'ultima richiesta in corso o riuscita, quella viene riusata.
		 */
		var paymentIntentPromise = null;
		var paymentIntentKey     = null;

		function buildRequestKey() {
			return JSON.stringify( {
				nome: val( 'gaps-f-nome' ),
				cognome: val( 'gaps-f-cognome' ),
				email: val( 'gaps-f-email' ),
				telefono: val( 'gaps-f-telefono' ),
				indirizzo: val( 'gaps-f-indirizzo' ),
				citta: val( 'gaps-f-citta' ),
				provincia: val( 'gaps-f-provincia' ),
				cap: val( 'gaps-f-cap' ),
				fattura: Boolean( invoiceToggle && invoiceToggle.checked )
			} );
		}

		function requestPaymentIntent() {
			var key = buildRequestKey();

			if ( paymentIntentPromise && paymentIntentKey === key ) {
				return paymentIntentPromise;
			}

			paymentIntentKey = key;

			var formData = new FormData( form );
			formData.append( 'action', gapsFrontend.action );
			formData.append( 'nonce', gapsFrontend.nonce );

			paymentIntentPromise = fetch( gapsFrontend.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData
			} )
				.then( function ( response ) { return response.json(); } )
				.then( function ( data ) {
					if ( data && data.success && data.data.client_secret ) {
						return data.data.client_secret;
					}
					var msg = ( data && data.data && data.data.message ) ? data.data.message : gapsFrontend.i18n.genericError;
					throw new Error( msg );
				} )
				.catch( function ( error ) {
					// Richiesta fallita: azzeriamo la cache per permettere un
					// nuovo tentativo pulito al prossimo click.
					paymentIntentPromise = null;
					paymentIntentKey = null;
					throw error;
				} );

			return paymentIntentPromise;
		}

		var paymentElementMounted = false;

		function createPaymentIntentAndMount() {
			updateTotal();
			setStepMessage( 3, '', '' );

			if ( paymentElementMounted ) {
				// Il Payment Element è già montato per questi stessi dati
				// (es. l'utente è tornato indietro e poi di nuovo avanti
				// senza modificare nulla): non rifare tutto da capo.
				return;
			}

			setPaymentLoading( true );

			// Avvia in parallelo il caricamento di Stripe (se non è già in
			// corso dal momento dell'apertura del popup) e la richiesta al
			// server per il Payment Intent: il popup è già visibile con lo
			// stato di caricamento, nessuna delle due attese lo lascia
			// invisibile o bloccato.
			Promise.all( [ ensureStripeInstance(), requestPaymentIntent() ] )
				.then( function ( results ) {
					var stripeInstance = results[0];
					var clientSecret    = results[1];

					var stripeElements = stripeInstance.elements( { clientSecret: clientSecret } );
					var paymentElement = stripeElements.create( 'payment' );
					paymentElement.mount( paymentDiv );
					paymentElementMounted = true;

					setPaymentLoading( false );

					if ( payButton ) {
						payButton.onclick = function () {
							if ( payButton.disabled ) {
								return;
							}
							payButton.disabled = true;
							setStepMessage( 3, gapsFrontend.i18n.paying, '' );

							stripeInstance.confirmPayment( {
								elements: stripeElements,
								confirmParams: {
									return_url: gapsFrontend.thankYouUrl
								}
							} ).then( function ( result ) {
								// In caso di redirect (3D Secure, PayPal...) il
								// browser lascia la pagina prima di arrivare
								// qui. Si arriva a questo punto solo per errori
								// immediati (es. carta rifiutata).
								if ( result.error ) {
									setStepMessage( 3, result.error.message, 'error' );
									payButton.disabled = false;
								}
							} );
						};
					}
				} )
				.catch( function () {
					setPaymentLoading( false );
					paymentElementMounted = false;
					setStepMessage( 3, gapsFrontend.i18n.genericError, 'error' );
				} );
		}

		// Se l'utente torna allo step 2 e cambia i dati, un nuovo Payment
		// Element dovrà essere ricreato la prossima volta che raggiunge lo
		// step 3.
		overlay.querySelectorAll( '.gaps-step-back' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				if ( '3' === btn.closest( '.gaps-form-step' ).getAttribute( 'data-step' ) ) {
					paymentElementMounted = false;
				}
			} );
		} );
	}

	/* ==========================================================
	 * Lightbox anteprima libro
	 * ========================================================== */
	function initPreviewLightbox() {
		var overlay = document.getElementById( 'gaps-lightbox-overlay' );
		if ( ! overlay ) {
			return;
		}

		var img         = overlay.querySelector( '.gaps-lightbox-img' );
		var closeBtn    = overlay.querySelector( '.gaps-lightbox-close' );
		var prevBtn     = overlay.querySelector( '.gaps-lightbox-prev' );
		var nextBtn     = overlay.querySelector( '.gaps-lightbox-next' );
		var mainTrigger = document.getElementById( 'gaps-preview-main-trigger' );
		var current     = 0;
		var images      = [];

		if ( mainTrigger ) {
			try {
				images = JSON.parse( mainTrigger.getAttribute( 'data-images' ) || '[]' );
			} catch ( err ) {
				images = [];
			}
		}

		if ( ! images.length ) {
			return;
		}

		function show( index ) {
			if ( index < 0 ) {
				index = images.length - 1;
			}
			if ( index >= images.length ) {
				index = 0;
			}
			current = index;
			if ( img ) {
				img.src = images[ current ];
				img.alt = '';
			}
		}

		function openAt( index ) {
			show( index );
			overlay.classList.add( 'is-open' );
			document.body.classList.add( 'gaps-modal-open' );
		}

		if ( mainTrigger ) {
			mainTrigger.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				openAt( 0 );
			} );
		}

		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', function () {
				overlay.classList.remove( 'is-open' );
				document.body.classList.remove( 'gaps-modal-open' );
			} );
		}

		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function () { show( current - 1 ); } );
		}
		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () { show( current + 1 ); } );
		}

		overlay.addEventListener( 'click', function ( e ) {
			if ( e.target === overlay ) {
				overlay.classList.remove( 'is-open' );
				document.body.classList.remove( 'gaps-modal-open' );
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( ! overlay.classList.contains( 'is-open' ) ) {
				return;
			}
			if ( 'Escape' === e.key ) {
				overlay.classList.remove( 'is-open' );
				document.body.classList.remove( 'gaps-modal-open' );
			} else if ( 'ArrowLeft' === e.key ) {
				show( current - 1 );
			} else if ( 'ArrowRight' === e.key ) {
				show( current + 1 );
			}
		} );

		/* ------------------------------------------------------------
		 * Scorrimento a trascinamento (touch): su mobile le frecce
		 * prev/next sono nascoste via CSS (sotto gli 860px), sostituite
		 * da uno swipe orizzontale sull'immagine. Le frecce restano
		 * funzionanti su desktop, dove restano visibili.
		 * ------------------------------------------------------------ */
		var touchStartX = null;
		var touchStartY = null;

		overlay.addEventListener( 'touchstart', function ( e ) {
			if ( e.touches && 1 === e.touches.length ) {
				touchStartX = e.touches[0].clientX;
				touchStartY = e.touches[0].clientY;
			}
		}, { passive: true } );

		overlay.addEventListener( 'touchend', function ( e ) {
			if ( null === touchStartX ) {
				return;
			}
			var touch = e.changedTouches && e.changedTouches[0];
			if ( ! touch ) {
				touchStartX = null;
				touchStartY = null;
				return;
			}
			var deltaX = touch.clientX - touchStartX;
			var deltaY = touch.clientY - touchStartY;
			touchStartX = null;
			touchStartY = null;

			// Ignora gesti troppo brevi o prevalentemente verticali.
			if ( Math.abs( deltaX ) < 40 || Math.abs( deltaX ) < Math.abs( deltaY ) ) {
				return;
			}
			show( deltaX > 0 ? current - 1 : current + 1 );
		}, { passive: true } );
	}

	/* ==========================================================
	 * Animazioni "reveal" on-scroll (fade + slide-up leggero)
	 * ========================================================== */
	function initScrollReveal() {
		var items = document.querySelectorAll( '.gaps-reveal' );
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
	 * Barra CTA fissa in basso (solo mobile, vedi frontend.css):
	 * compare dopo aver superato la hero, scompare mentre è visibile
	 * il box P.S. della sezione finale, ricompare se si risale sopra
	 * quel box. Su desktop resta sempre nascosta via CSS: qui il
	 * lavoro è puramente logico, innocuo se il markup non c'è.
	 * ========================================================== */
	function initStickyCta() {
		var bar = document.getElementById( 'gaps-sticky-cta' );
		var hero = document.querySelector( '.gaps-hero' );

		if ( ! bar || ! hero || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		var pastHero = false;
		var inPsZone = false;

		function update() {
			bar.classList.toggle( 'is-visible', pastHero && ! inPsZone );
		}

		var heroObserver = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				pastHero = ! entry.isIntersecting;
				update();
			} );
		}, { threshold: 0 } );
		heroObserver.observe( hero );

		var psBox = document.querySelector( '.gaps-ps-box' );
		if ( psBox ) {
			var psObserver = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					inPsZone = entry.isIntersecting;
					update();
				} );
			}, { threshold: 0.1 } );
			psObserver.observe( psBox );
		}
	}
} )();
