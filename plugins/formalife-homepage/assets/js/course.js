/* global fmhCourseFrontend, FMHStripeLoader */
( function () {
	'use strict';
	function ready( fn ) { if ( document.readyState !== 'loading' ) { fn(); } else { document.addEventListener( 'DOMContentLoaded', fn ); } }
	ready( function () {
		var cfg = window.fmhCourseFrontend;
		var overlay = document.getElementById( 'fmh-course-checkout-overlay' );
		if ( ! cfg || ! overlay ) { return; }
		var form = document.getElementById( 'fmh-course-order-form' );
		var sessionSelect = document.getElementById( 'fmh-course-session' );
		var couple = document.getElementById( 'fmh-course-couple' );
		var partyInput = document.getElementById( 'fmh-party-size' );
		var secondWrap = document.getElementById( 'fmh-course-second-person' );
		var secondNome = document.getElementById( 'fmh-second-nome' );
		var secondCognome = document.getElementById( 'fmh-second-cognome' );
		var oneSeat = document.getElementById( 'fmh-course-one-seat' );
		var hasCodeToggle = document.getElementById( 'fmh-course-has-code' );
		var codeField = document.getElementById( 'fmh-course-code-field' );
		var codeInput = document.getElementById( 'fmh-course-discount-code' );
		var codeMessage = document.getElementById( 'fmh-course-code-message' );
		var invoiceToggle = document.getElementById( 'fmh-course-invoice' );
		var invoiceFields = document.getElementById( 'fmh-course-invoice-fields' );
		var total = document.getElementById( 'fmh-course-total' );
		var summarySession = document.getElementById( 'fmh-summary-session' );
		var summaryParty = document.getElementById( 'fmh-summary-party' );
		var message = document.getElementById( 'fmh-course-form-message' );
		var detailsPane = document.getElementById( 'fmh-course-checkout-details' );
		var paymentPane = document.getElementById( 'fmh-course-checkout-payment' );
		var paymentDiv = document.getElementById( 'fmh-course-payment-element' );
		var payButton = document.getElementById( 'fmh-course-pay-button' );
		var paymentMessage = document.getElementById( 'fmh-course-payment-message' );
		var lastTrigger = null, stripe = null, elements = null, orderId = null;
		function cookieValue( name ) { var prefix=name+'='; var parts=document.cookie?document.cookie.split(';'):[]; for(var i=0;i<parts.length;i++){var part=parts[i].trim();if(part.indexOf(prefix)===0){return decodeURIComponent(part.substring(prefix.length));}} return ''; }
		function appendAttribution( data ) {
			var params=new URLSearchParams(window.location.search);
			['utm_source','utm_medium','utm_campaign','utm_content','utm_term'].forEach(function(key){var value=params.get(key);if(value){data.set(key,value);}});
			if(typeof window.fbq==='function'){var fbp=cookieValue('_fbp'),fbc=cookieValue('_fbc');if(fbp){data.set('meta_fbp',fbp);}if(fbc){data.set('meta_fbc',fbc);}}
		}
		// Prezzi ridotti restituiti dal server quando il codice è valido.
		// Sono solo per il riepilogo: l'importo addebitato lo ricalcola il server.
		var discountPrices = null, codeTimer = null;
		function euro( cents ) { return ( cents / 100 ).toLocaleString( 'it-IT', { style: 'currency', currency: 'EUR' } ); }
		function selected() { return cfg.sessions[ sessionSelect.value ] || null; }
		function priceFor( s, size ) { var src = discountPrices || s; return size === 2 ? src.couplePriceCents : src.singlePriceCents; }
		function update() {
			var s = selected(); if ( ! s ) { return; }
			var pairAllowed = parseInt( s.available, 10 ) >= 2;
			couple.disabled = ! pairAllowed; oneSeat.hidden = pairAllowed;
			if ( ! pairAllowed ) { couple.checked = false; }
			var size = couple.checked ? 2 : 1; partyInput.value = String( size ); secondWrap.hidden = size !== 2;
			secondNome.required = size === 2; secondCognome.required = size === 2;
			total.textContent = euro( priceFor( s, size ) );
			summarySession.textContent = s.label + ' · ' + s.city + ' · ' + s.time; summaryParty.textContent = String( size );
		}
		function setInvoice() { var on = invoiceToggle.checked; invoiceFields.hidden = ! on; invoiceFields.querySelectorAll( 'input' ).forEach( function( input ){ input.disabled = ! on; input.required = on && input.name !== 'recipient_code_or_pec'; } ); }
		function openModal( sessionId, trigger ) {
			if ( ! cfg.checkoutReady ) { return; } lastTrigger = trigger || document.activeElement;
			if ( sessionId && cfg.sessions[ sessionId ] ) { sessionSelect.value = sessionId; }
			overlay.hidden = false; document.body.classList.add( 'fmh-modal-open' ); update();
			window.FMHStripeLoader.getStripeInstance( cfg.stripePublishableKey ).catch( function( error ){ message.textContent = error.message; } );
			setTimeout( function(){ sessionSelect.focus(); }, 30 );
		}
		function closeModal() { overlay.hidden = true; document.body.classList.remove( 'fmh-modal-open' ); if ( lastTrigger && lastTrigger.focus ) { lastTrigger.focus(); } }
		document.addEventListener( 'click', function( event ){ var trigger = event.target.closest && event.target.closest( '.fmh-course-open-checkout' ); if ( trigger ) { event.preventDefault(); openModal( trigger.dataset.session, trigger ); } } );
		overlay.querySelectorAll( '[data-fmh-course-close]' ).forEach( function( button ){ button.addEventListener( 'click', closeModal ); } );
		overlay.addEventListener( 'click', function( event ){ if ( event.target === overlay ) { closeModal(); } } );
		document.addEventListener( 'keydown', function( event ){
			if ( overlay.hidden ) { return; } if ( event.key === 'Escape' ) { closeModal(); return; }
			if ( event.key === 'Tab' ) { var focusable = overlay.querySelectorAll( 'button:not([disabled]),input:not([disabled]),select:not([disabled]),a[href]' ); if ( ! focusable.length ) { return; } var first=focusable[0],last=focusable[focusable.length-1]; if(event.shiftKey&&document.activeElement===first){event.preventDefault();last.focus();}else if(!event.shiftKey&&document.activeElement===last){event.preventDefault();first.focus();} }
		} );
		function setCodeMessage( text, ok ) {
			if ( ! codeMessage ) { return; }
			codeMessage.textContent = text || '';
			codeMessage.classList.toggle( 'is-valid', !! ok );
			codeMessage.classList.toggle( 'is-invalid', !! text && ! ok );
		}
		function checkCode() {
			if ( ! codeInput ) { return; }
			var value = codeInput.value.trim();
			if ( ! value ) { discountPrices = null; setCodeMessage( '', false ); update(); return; }
			var data = new FormData();
			data.append( 'action', cfg.checkCodeAction );
			data.append( 'nonce', cfg.nonce );
			data.append( 'code', value );
			data.append( 'session', sessionSelect.value );
			fetch( cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( r ) {
					if ( r && r.success && r.data ) {
						discountPrices = { singlePriceCents: r.data.singlePriceCents, couplePriceCents: r.data.couplePriceCents };
						setCodeMessage( r.data.message, true );
					} else {
						discountPrices = null;
						setCodeMessage( ( r && r.data && r.data.message ) || cfg.i18n.genericError, false );
					}
					update();
				} )
				.catch( function () { discountPrices = null; setCodeMessage( cfg.i18n.genericError, false ); update(); } );
		}
		if ( hasCodeToggle && codeField ) {
			hasCodeToggle.addEventListener( 'change', function () {
				codeField.hidden = ! hasCodeToggle.checked;
				if ( ! hasCodeToggle.checked ) {
					if ( codeInput ) { codeInput.value = ''; }
					discountPrices = null; setCodeMessage( '', false ); update();
				} else if ( codeInput ) { codeInput.focus(); }
			} );
		}
		if ( codeInput ) {
			codeInput.addEventListener( 'input', function () {
				if ( codeTimer ) { clearTimeout( codeTimer ); }
				codeTimer = setTimeout( checkCode, 400 );
			} );
		}
		sessionSelect.addEventListener( 'change', function () { update(); if ( codeInput && codeInput.value.trim() ) { checkCode(); } } );
		couple.addEventListener( 'change', update ); invoiceToggle.addEventListener( 'change', setInvoice ); setInvoice(); update();
		form.addEventListener( 'submit', function( event ){
			event.preventDefault(); message.textContent=''; if ( ! form.reportValidity() ) { return; }
			var button=form.querySelector('button[type="submit"]'); var clientSecret=''; button.disabled=true; button.textContent=cfg.i18n.loading;
			var data=new FormData(form); data.append('action',cfg.action); data.append('nonce',cfg.nonce); data.set('privacy',document.getElementById('fmh-course-privacy').checked?'1':'0'); data.set('terms',document.getElementById('fmh-course-terms').checked?'1':'0'); data.set('invoice_requested',invoiceToggle.checked?'1':'0'); appendAttribution(data);
			fetch(cfg.ajaxUrl,{method:'POST',credentials:'same-origin',body:data})
				.then(function(r){return r.json();})
				.then(function(r){if(!r||!r.success||!r.data.client_secret){throw new Error(r&&r.data&&r.data.message?r.data.message:cfg.i18n.genericError);} orderId=r.data.order_id;clientSecret=r.data.client_secret;return window.FMHStripeLoader.getStripeInstance(cfg.stripePublishableKey);})
				.then(function(instance){stripe=instance;elements=stripe.elements({clientSecret:clientSecret});elements.create('payment').mount(paymentDiv);detailsPane.hidden=true;paymentPane.hidden=false;payButton.disabled=false;})
				.catch(function(error){message.textContent=error.message||cfg.i18n.genericError;button.disabled=false;button.textContent='Procedi al pagamento sicuro';});
		} );
		payButton.addEventListener('click',function(){if(!stripe||!elements)return;payButton.disabled=true;payButton.textContent=cfg.i18n.paying;var url=cfg.thankYouUrl+(cfg.thankYouUrl.indexOf('?')<0?'?':'&')+'order_id='+encodeURIComponent(orderId);stripe.confirmPayment({elements:elements,confirmParams:{return_url:url},redirect:'if_required'}).then(function(result){if(result.error){throw result.error;}window.location.href=url;}).catch(function(error){paymentMessage.textContent=error.message||cfg.i18n.genericError;payButton.disabled=false;payButton.textContent='Paga ora';});});
		var sticky=document.querySelector('.fmh-mobile-cta'),hero=document.getElementById('hero'); if(sticky&&hero&&'IntersectionObserver'in window){new IntersectionObserver(function(entries){sticky.classList.toggle('is-visible',!entries[0].isIntersecting);},{threshold:0}).observe(hero);}
	} );
} )();
