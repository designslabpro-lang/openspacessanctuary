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

		/* Scroll reveal — tag known component selectors at runtime so no
		   Elementor content edits are needed to opt elements in. */
		var revealSelectors = [
			'.oss-card', '.oss-who-card', '.oss-feature',
			'.oss-power__media', '.oss-power__eyebrow-row', '.oss-power__quote',
			'.oss-split__media', '.oss-split__content',
			'.oss-testimonial-carousel', '.oss-value'
		];
		document.querySelectorAll( revealSelectors.join( ',' ) ).forEach( function ( el ) {
			el.classList.add( 'oss-reveal' );
		} );

		var revealTargets = document.querySelectorAll( '.oss-reveal' );
		if ( revealTargets.length && 'IntersectionObserver' in window ) {
			var revealObserver = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-visible' );
						revealObserver.unobserve( entry.target );
					}
				} );
			}, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' } );
			revealTargets.forEach( function ( el ) { revealObserver.observe( el ); } );
		} else {
			revealTargets.forEach( function ( el ) { el.classList.add( 'is-visible' ); } );
		}

		/* Testimonial carousel */
		document.querySelectorAll( '[data-oss-carousel]' ).forEach( function ( carousel ) {
			var track = carousel.querySelector( '.oss-testimonial-carousel__track' );
			var slides = carousel.querySelectorAll( '.oss-testimonial-carousel__slide' );
			var dotsWrap = carousel.querySelector( '[data-oss-carousel-dots]' );
			var prevBtn = carousel.querySelector( '[data-oss-carousel-prev]' );
			var nextBtn = carousel.querySelector( '[data-oss-carousel-next]' );
			if ( ! track || slides.length < 2 ) return;

			var index = 0;
			var dots = [];

			slides.forEach( function ( _, i ) {
				var dot = document.createElement( 'button' );
				dot.type = 'button';
				dot.className = 'oss-testimonial-carousel__dot';
				dot.setAttribute( 'aria-label', 'Go to story ' + ( i + 1 ) );
				dot.addEventListener( 'click', function () { goTo( i ); } );
				dotsWrap.appendChild( dot );
				dots.push( dot );
			} );

			function render() {
				track.style.transform = 'translateX(-' + ( index * 100 ) + '%)';
				dots.forEach( function ( d, i ) { d.classList.toggle( 'is-active', i === index ); } );
			}
			function goTo( i ) {
				index = ( i + slides.length ) % slides.length;
				render();
			}

			if ( prevBtn ) prevBtn.addEventListener( 'click', function () { goTo( index - 1 ); } );
			if ( nextBtn ) nextBtn.addEventListener( 'click', function () { goTo( index + 1 ); } );

			var autoplay = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? null : setInterval( function () { goTo( index + 1 ); }, 7000 );
			carousel.addEventListener( 'mouseenter', function () { if ( autoplay ) clearInterval( autoplay ); } );

			render();
		} );
	} );
} )();
