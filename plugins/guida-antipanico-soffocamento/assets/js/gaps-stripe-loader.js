/**
 * Guida Anti-Panico al Soffocamento Pediatrico — loader condiviso di Stripe.js.
 *
 * Stripe.js (https://js.stripe.com/v3/) non viene più registrato/accodato da
 * WordPress: nessuno script Stripe è presente nella pagina finché l'utente
 * non preme un pulsante che apre il popup di preordine. Questo modulo viene
 * caricato SEMPRE con gli altri asset della landing (è minuscolo e non
 * scarica nulla da solo), ma scarica lo script ufficiale di Stripe — sempre
 * e soltanto da js.stripe.com, mai auto-ospitato — solo alla prima
 * invocazione di GAPSStripeLoader.getStripeInstance().
 *
 * Le due Promise sono condivise a livello di pagina: comunque venga
 * invocato (da CTA diversi, click ripetuti, riapertura del popup), lo
 * script Stripe viene scaricato una sola volta e l'istanza Stripe viene
 * creata una sola volta.
 */
( function ( window, document ) {
	'use strict';

	var stripeScriptPromise = null;
	var stripeInstancePromise = null;
	var stripeInstanceKey = null;

	/**
	 * Carica lo script ufficiale Stripe.js (v3) una sola volta, riusando uno
	 * script già presente in pagina se un'altra integrazione lo avesse già
	 * aggiunto nel frattempo.
	 *
	 * @return {Promise<Function>} Promise risolta con il costruttore window.Stripe.
	 */
	function loadStripeScript() {
		if ( window.Stripe ) {
			return Promise.resolve( window.Stripe );
		}

		if ( stripeScriptPromise ) {
			return stripeScriptPromise;
		}

		stripeScriptPromise = new Promise( function ( resolve, reject ) {
			var existingScript = document.querySelector( 'script[src^="https://js.stripe.com/v3"]' );

			if ( existingScript ) {
				existingScript.addEventListener(
					'load',
					function () { resolve( window.Stripe ); },
					{ once: true }
				);
				existingScript.addEventListener(
					'error',
					function () {
						stripeScriptPromise = null;
						reject( new Error( 'Impossibile caricare Stripe.' ) );
					},
					{ once: true }
				);
				return;
			}

			var script = document.createElement( 'script' );
			script.src = 'https://js.stripe.com/v3/';
			script.async = true;
			script.dataset.gapsStripe = 'true';

			script.addEventListener(
				'load',
				function () {
					if ( typeof window.Stripe !== 'function' ) {
						stripeScriptPromise = null;
						reject( new Error( 'Stripe non è stato inizializzato.' ) );
						return;
					}
					resolve( window.Stripe );
				},
				{ once: true }
			);

			script.addEventListener(
				'error',
				function () {
					stripeScriptPromise = null;
					script.remove();
					reject( new Error( 'Impossibile caricare Stripe.' ) );
				},
				{ once: true }
			);

			document.head.appendChild( script );
		} );

		return stripeScriptPromise;
	}

	/**
	 * Restituisce (creandola se necessario) l'istanza Stripe legata alla
	 * chiave pubblicabile indicata. Se la Promise precedente è fallita, un
	 * nuovo tentativo la ricrea da zero (nessun tentativo bloccato per
	 * sempre da un errore di rete transitorio).
	 *
	 * @param {string} publishableKey Chiave pubblicabile Stripe (pk_...).
	 * @return {Promise<Object>} Promise risolta con l'istanza Stripe pronta all'uso.
	 */
	function getStripeInstance( publishableKey ) {
		if ( ! publishableKey ) {
			return Promise.reject( new Error( 'Chiave pubblicabile Stripe mancante.' ) );
		}

		if ( stripeInstancePromise && stripeInstanceKey === publishableKey ) {
			return stripeInstancePromise;
		}

		stripeInstanceKey = publishableKey;
		stripeInstancePromise = loadStripeScript()
			.then( function ( StripeConstructor ) {
				return StripeConstructor( publishableKey );
			} )
			.catch( function ( error ) {
				stripeInstancePromise = null;
				stripeInstanceKey = null;
				throw error;
			} );

		return stripeInstancePromise;
	}

	/**
	 * Espone il loader in un unico oggetto globale namespaced (prefisso
	 * "GAPS", coerente con il resto del plugin), riusabile da qualunque
	 * landing/popup futuro senza duplicare questa logica.
	 */
	window.GAPSStripeLoader = {
		getStripeInstance: getStripeInstance
	};
} )( window, document );
