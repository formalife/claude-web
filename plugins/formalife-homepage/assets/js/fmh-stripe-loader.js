( function ( window, document ) {
	'use strict';
	var stripeScriptPromise = null;
	var stripeInstancePromise = null;
	var stripeInstanceKey = null;

	function loadStripeScript() {
		if ( window.Stripe ) { return Promise.resolve( window.Stripe ); }
		if ( stripeScriptPromise ) { return stripeScriptPromise; }
		stripeScriptPromise = new Promise( function ( resolve, reject ) {
			var existing = document.querySelector( 'script[src^="https://js.stripe.com/v3"]' );
			if ( existing ) {
				existing.addEventListener( 'load', function () { resolve( window.Stripe ); }, { once: true } );
				existing.addEventListener( 'error', function () { stripeScriptPromise = null; reject( new Error( 'Impossibile caricare Stripe.' ) ); }, { once: true } );
				return;
			}
			var script = document.createElement( 'script' );
			script.src = 'https://js.stripe.com/v3/';
			script.async = true;
			script.dataset.fmhStripe = 'true';
			script.addEventListener( 'load', function () {
				if ( typeof window.Stripe !== 'function' ) { stripeScriptPromise = null; reject( new Error( 'Stripe non inizializzato.' ) ); return; }
				resolve( window.Stripe );
			}, { once: true } );
			script.addEventListener( 'error', function () { stripeScriptPromise = null; script.remove(); reject( new Error( 'Impossibile caricare Stripe.' ) ); }, { once: true } );
			document.head.appendChild( script );
		} );
		return stripeScriptPromise;
	}

	function getStripeInstance( key ) {
		if ( ! key ) { return Promise.reject( new Error( 'Chiave Stripe mancante.' ) ); }
		if ( stripeInstancePromise && stripeInstanceKey === key ) { return stripeInstancePromise; }
		stripeInstanceKey = key;
		stripeInstancePromise = loadStripeScript().then( function ( StripeConstructor ) { return StripeConstructor( key ); } ).catch( function ( error ) {
			stripeInstancePromise = null; stripeInstanceKey = null; throw error;
		} );
		return stripeInstancePromise;
	}

	window.FMHStripeLoader = { getStripeInstance: getStripeInstance };
} )( window, document );
