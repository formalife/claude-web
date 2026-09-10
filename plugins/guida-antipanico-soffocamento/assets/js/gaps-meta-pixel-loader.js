/* global gapsMetaPixel */
/**
 * Guida Anti-Panico al Soffocamento Pediatrico — loader condiviso e ritardato
 * di Meta Pixel.
 *
 * Questo script viene accodato SOLO se nel pannello impostazioni è stato
 * configurato un Pixel ID (vedi GAPS_Assets::enqueue_frontend()): se il
 * campo è vuoto, questo file non viene nemmeno caricato e non esiste
 * nessuna chiamata a connect.facebook.net. Non crea un secondo Pixel se un
 * altro sistema del sito (tema, Tag Manager, altro plugin) sta già
 * caricando fbevents.js: si limita ad esporre gapsTrackEvent(), che se un
 * fbq globale è già presente lo riusa direttamente.
 *
 * Consenso: per default il Pixel NON viene programmato finché
 * window.gapsHasMarketingConsent() non restituisce true. L'implementazione
 * di default qui sotto riconosce alcuni cookie di consenso comuni (Complianz,
 * Cookiebot, Borlabs, iubenda) SOLO come euristica di comodo: se il sito usa
 * un meccanismo di consenso diverso, definire window.gapsHasMarketingConsent
 * PRIMA che questo script venga eseguito (viene caricato con strategia
 * "defer", quindi molto tardi) per integrarlo con il vero banner cookie del
 * sito, oppure agganciare l'evento personalizzato "gaps:consent-granted" su
 * document quando il consenso viene concesso interattivamente.
 */
( function ( window, document ) {
	'use strict';

	if ( 'undefined' === typeof gapsMetaPixel || ! gapsMetaPixel || ! gapsMetaPixel.pixelId ) {
		return;
	}

	var metaPixelPromise = null;
	var eventQueue = [];
	var scheduled = false;

	/* ----------------------------------------------------------------
	 * Consenso: euristica di default, sovrascrivibile dal sito.
	 * ---------------------------------------------------------------- */
	if ( 'function' !== typeof window.gapsHasMarketingConsent ) {
		window.gapsHasMarketingConsent = function () {
			if ( ! gapsMetaPixel.requireConsent ) {
				return true;
			}
			var cookie = document.cookie || '';
			// Complianz (categoria marketing accettata).
			if ( /cmplz_marketing=allow/.test( cookie ) ) {
				return true;
			}
			// Cookiebot.
			if ( /CookieConsent=.*marketing%3Atrue/.test( cookie ) ) {
				return true;
			}
			// Borlabs Cookie.
			if ( /borlabs-cookie=.*"marketing":true/.test( cookie ) ) {
				return true;
			}
			// iubenda (presenza del cookie di consenso registrato).
			if ( /_iub_cs-\d+=/.test( cookie ) ) {
				return true;
			}
			return false;
		};
	}

	/**
	 * Inizializza fbevents.js una sola volta, evitando duplicati se un
	 * fbq globale esiste già (caricato da un'altra integrazione del sito).
	 *
	 * @return {Promise<Function>} Promise risolta con la funzione fbq pronta.
	 */
	function loadMetaPixel() {
		if ( metaPixelPromise ) {
			return metaPixelPromise;
		}

		metaPixelPromise = new Promise( function ( resolve, reject ) {
			if ( 'function' === typeof window.fbq && window.fbq.__gapsAlreadyInitialized !== false && window._fbq ) {
				// fbq esiste già in pagina (altra integrazione): lo riusiamo,
				// senza inizializzare un secondo Pixel duplicato.
				resolve( window.fbq );
				return;
			}

			try {
				/* eslint-disable */
				!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
				n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
				n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
				t.src=v;t.addEventListener('load',function(){resolve(f.fbq);});
				t.addEventListener('error',function(){metaPixelPromise=null;reject(new Error('Impossibile caricare Meta Pixel.'));});
				s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
				'https://connect.facebook.net/en_US/fbevents.js');
				/* eslint-enable */

				window.fbq( 'init', gapsMetaPixel.pixelId );
			} catch ( err ) {
				metaPixelPromise = null;
				reject( err );
			}
		} ).catch( function ( error ) {
			// Un ad blocker o un errore di rete non deve rompere la pagina:
			// la Promise fallisce silenziosamente, il popup e il checkout
			// restano pienamente funzionanti.
			metaPixelPromise = null;
			return null;
		} );

		return metaPixelPromise;
	}

	/**
	 * Pianifica il caricamento del Pixel dopo le risorse critiche: attende
	 * window.load, poi un momento di inattività del browser (o un timeout
	 * di sicurezza sui browser senza requestIdleCallback).
	 */
	function scheduleMetaPixel() {
		if ( scheduled ) {
			return;
		}
		scheduled = true;

		var run = function () {
			if ( ! window.gapsHasMarketingConsent() ) {
				return;
			}
			if ( 'requestIdleCallback' in window ) {
				window.requestIdleCallback( function () { loadMetaPixel().then( flushQueue ); }, { timeout: 5000 } );
			} else {
				window.setTimeout( function () { loadMetaPixel().then( flushQueue ); }, 2500 );
			}
		};

		if ( 'complete' === document.readyState ) {
			run();
		} else {
			window.addEventListener( 'load', run, { once: true } );
		}
	}

	/**
	 * Invia gli eventi accodati prima che fbq fosse disponibile, una sola
	 * volta ciascuno.
	 *
	 * @param {Function|null} fbq Funzione fbq pronta, o null se il caricamento è fallito.
	 */
	function flushQueue( fbq ) {
		if ( ! fbq || 'function' !== typeof fbq ) {
			eventQueue = [];
			return;
		}
		eventQueue.forEach( function ( evt ) {
			fbq( 'track', evt.name, evt.params || {} );
		} );
		eventQueue = [];
	}

	/**
	 * API pubblica per tracciare un evento dai vari punti della landing
	 * (es. apertura del popup di preordine). Se il Pixel non è ancora
	 * pronto (consenso non ancora dato, script non ancora scaricato),
	 * l'evento resta in coda e viene inviato non appena possibile — non
	 * blocca mai popup, Stripe o checkout, e non duplica un evento già
	 * accodato con lo stesso nome nella stessa sessione di navigazione.
	 *
	 * @param {string} name   Nome evento standard Meta (es. "InitiateCheckout").
	 * @param {Object} [params] Parametri opzionali dell'evento.
	 */
	window.gapsTrackEvent = function ( name, params ) {
		if ( ! name ) {
			return;
		}

		if ( 'function' === typeof window.fbq && metaPixelPromise ) {
			metaPixelPromise.then( function ( fbq ) {
				if ( fbq ) {
					fbq( 'track', name, params || {} );
				}
			} );
			return;
		}

		// Evita di accodare due volte lo stesso evento prima che sia partito.
		var alreadyQueued = eventQueue.some( function ( evt ) { return evt.name === name; } );
		if ( ! alreadyQueued ) {
			eventQueue.push( { name: name, params: params || {} } );
		}

		scheduleMetaPixel();
	};

	// Se al caricamento della pagina il consenso risulta già valido (cookie
	// già salvato in precedenza), pianifica subito il Pixel, sempre dopo le
	// risorse critiche — senza aspettare una nuova interazione dell'utente.
	if ( window.gapsHasMarketingConsent() ) {
		scheduleMetaPixel();
	}

	// Se il sito notifica il consenso a runtime tramite un evento custom,
	// intercettalo per avviare lo scheduler nello stesso istante.
	document.addEventListener( 'gaps:consent-granted', scheduleMetaPixel );
} )( window, document );
