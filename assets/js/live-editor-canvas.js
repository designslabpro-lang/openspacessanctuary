/**
 * Live Page Builder — canvas agent (runs inside the iframe).
 *
 * Adds hover outlines, click-to-select, a floating type tag + element toolbar,
 * and talks to the editor shell over postMessage. Phase 0 wires selection and
 * the toolbar surface; the shell decides what each toolbar action does.
 * Vanilla JS, no dependencies.
 */
( function () {
	'use strict';

	var selectedEl = null;
	var tag = null;
	var toolbar = null;

	function parentPost( msg ) {
		try { window.parent.postMessage( Object.assign( { source: 'oss-lpb-canvas' }, msg ), window.location.origin ); } catch ( e ) {}
	}

	function nodeInfo( el ) {
		if ( el.hasAttribute( 'data-live-element' ) ) {
			return { scope: 'element', id: el.getAttribute( 'data-live-element' ), elType: el.getAttribute( 'data-live-type' ) || 'element' };
		}
		return { scope: 'section', id: el.getAttribute( 'data-live-section' ), elType: 'section' };
	}

	function ensureChrome() {
		if ( ! tag ) {
			tag = document.createElement( 'div' );
			tag.className = 'oss-lpb-tag';
			document.body.appendChild( tag );
		}
		if ( ! toolbar ) {
			toolbar = document.createElement( 'div' );
			toolbar.className = 'oss-lpb-toolbar';
			toolbar.innerHTML =
				'<button data-act="edit">Edit</button>' +
				'<button data-act="duplicate">Duplicate</button>' +
				'<button data-act="move-up">↑</button>' +
				'<button data-act="move-down">↓</button>' +
				'<button data-act="delete">Delete</button>';
			toolbar.addEventListener( 'click', function ( e ) {
				var b = e.target.closest( 'button' );
				if ( ! b || ! selectedEl ) { return; }
				e.preventDefault();
				var info = nodeInfo( selectedEl );
				parentPost( { type: 'action', act: b.getAttribute( 'data-act' ), scope: info.scope, id: info.id, elType: info.elType } );
			} );
			document.body.appendChild( toolbar );
		}
	}

	function positionChrome() {
		if ( ! selectedEl ) { return; }
		var r = selectedEl.getBoundingClientRect();
		var top = r.top + window.scrollY;
		var left = r.left + window.scrollX;
		var info = nodeInfo( selectedEl );
		tag.textContent = info.elType;
		tag.className = 'oss-lpb-tag' + ( 'section' === info.scope ? ' oss-lpb-tag--section' : '' );
		tag.style.top = top + 'px';
		tag.style.left = left + 'px';
		tag.style.display = 'block';
		toolbar.style.top = top + 'px';
		toolbar.style.left = ( left + Math.max( 0, r.width - 220 ) ) + 'px';
		toolbar.style.display = 'flex';
	}

	function select( el ) {
		if ( selectedEl ) { selectedEl.classList.remove( 'oss-lpb-selected' ); }
		selectedEl = el;
		if ( ! el ) { if ( tag ) tag.style.display = 'none'; if ( toolbar ) toolbar.style.display = 'none'; parentPost( { type: 'deselect' } ); return; }
		ensureChrome();
		el.classList.add( 'oss-lpb-selected' );
		positionChrome();
		var info = nodeInfo( el );
		parentPost( { type: 'select', scope: info.scope, id: info.id, elType: info.elType } );
	}

	function findEditable( target ) {
		return target.closest( '[data-live-element]' ) || target.closest( '[data-live-section]' );
	}

	document.addEventListener( 'mouseover', function ( e ) {
		var el = findEditable( e.target );
		if ( el ) { el.classList.add( 'oss-lpb-hover' ); }
	} );
	document.addEventListener( 'mouseout', function ( e ) {
		var el = findEditable( e.target );
		if ( el ) { el.classList.remove( 'oss-lpb-hover' ); }
	} );

	document.addEventListener( 'click', function ( e ) {
		var el = findEditable( e.target );
		if ( el ) {
			e.preventDefault();
			e.stopPropagation();
			// Prefer the element over its wrapping section when both match.
			var pick = e.target.closest( '[data-live-element]' ) || el;
			select( pick );
		} else {
			select( null );
		}
	}, true );

	window.addEventListener( 'scroll', positionChrome, { passive: true } );
	window.addEventListener( 'resize', positionChrome );

	// Messages from the editor shell.
	window.addEventListener( 'message', function ( e ) {
		if ( e.origin !== window.location.origin || ! e.data || 'oss-lpb-shell' !== e.data.source ) { return; }
		var msg = e.data;
		if ( 'reposition' === msg.type ) { positionChrome(); }
		if ( 'deselect' === msg.type ) { select( null ); }
	} );

	parentPost( { type: 'ready' } );
} )();
