( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var header = document.querySelector( '.oss-header' );
		var toggle = document.querySelector( '.oss-menu-toggle' );
		var panel = document.querySelector( '.oss-mobile-panel' );
		var overlay = document.querySelector( '.oss-mobile-overlay' );
		var closeBtn = document.querySelector( '.oss-mobile-panel__close' );

		function onScroll() {
			if ( ! header ) return;
			if ( window.scrollY > 40 ) {
				header.classList.add( 'is-scrolled' );
			} else {
				header.classList.remove( 'is-scrolled' );
			}
		}
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();

		function openMenu() {
			if ( ! panel ) return;
			panel.classList.add( 'is-open' );
			overlay.classList.add( 'is-open' );
			toggle.setAttribute( 'aria-expanded', 'true' );
			document.body.style.overflow = 'hidden';
		}
		function closeMenu() {
			if ( ! panel ) return;
			panel.classList.remove( 'is-open' );
			overlay.classList.remove( 'is-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
			document.body.style.overflow = '';
		}

		if ( toggle ) {
			toggle.addEventListener( 'click', function () {
				var isOpen = panel && panel.classList.contains( 'is-open' );
				isOpen ? closeMenu() : openMenu();
			} );
		}
		if ( closeBtn ) closeBtn.addEventListener( 'click', closeMenu );
		if ( overlay ) overlay.addEventListener( 'click', closeMenu );
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) closeMenu();
		} );
	} );
} )();
