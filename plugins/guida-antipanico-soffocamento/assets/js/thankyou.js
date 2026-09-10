( function () {
	'use strict';
	document.documentElement.classList.add( 'gaps-ty-js' );

	document.addEventListener( 'DOMContentLoaded', function () {
		initReveal();
		initChecklist();
	} );

	function initReveal() {
		var items = document.querySelectorAll( '.gaps-ty-reveal' );
		if ( ! items.length ) {
			return;
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			items.forEach( function ( item ) {
				item.classList.add( 'is-visible' );
			} );
			return;
		}

		var observer = new IntersectionObserver( function ( entries, currentObserver ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					currentObserver.unobserve( entry.target );
				}
			} );
		}, { threshold: 0.12, rootMargin: '0px 0px -36px 0px' } );

		items.forEach( function ( item ) {
			observer.observe( item );
		} );
	}

	function initChecklist() {
		var checklist = document.querySelector( '[data-gaps-ty-checklist]' );
		if ( ! checklist ) {
			return;
		}

		var fields = Array.prototype.slice.call( checklist.querySelectorAll( 'input[type="checkbox"]' ) );
		var progressBar = document.querySelector( '[data-gaps-ty-progress-bar]' );
		var progressLabel = document.querySelector( '[data-gaps-ty-progress-label]' );
		var storageKey = 'gapsThankyouChecklistV1';
		var saved = [];

		try {
			saved = JSON.parse( window.localStorage.getItem( storageKey ) || '[]' );
		} catch ( error ) {
			saved = [];
		}

		fields.forEach( function ( field ) {
			field.checked = saved.indexOf( field.value ) !== -1;
			field.addEventListener( 'change', update );
		} );

		update();

		function update() {
			var completed = fields.filter( function ( field ) {
				return field.checked;
			} );
			var percentage = fields.length ? Math.round( ( completed.length / fields.length ) * 100 ) : 0;

			if ( progressBar ) {
				progressBar.style.width = percentage + '%';
			}
			if ( progressLabel ) {
				progressLabel.textContent = completed.length === fields.length
					? 'Fatto: hai preparato i prossimi passi.'
					: completed.length + ' di ' + fields.length + ' completate';
			}

			try {
				window.localStorage.setItem( storageKey, JSON.stringify( completed.map( function ( field ) {
					return field.value;
				} ) ) );
			} catch ( error ) {
				// La checklist continua a funzionare anche se lo storage è bloccato.
			}
		}
	}
}() );
