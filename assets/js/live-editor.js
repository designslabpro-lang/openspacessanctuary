/**
 * Live Page Builder — editor shell core.
 *
 * Loads the document, owns the postMessage bridge to the canvas iframe, wires
 * the top bar (device switch, Save, Undo/Redo, tabs), and holds the working
 * document + history. Phase 0 proves the full pipeline: load → select → save.
 * Depends on OSS_LPB (localized), OSSLPBHistory, OSSLPBElements, OSSLPBUI.
 */
( function () {
	'use strict';

	if ( typeof OSS_LPB === 'undefined' ) { return; }

	var UI = window.OSSLPBUI;
	var frame = document.getElementById( 'oss-lpb-frame' );
	var statusEl = document.getElementById( 'oss-lpb-status' );
	var saveBtn = document.getElementById( 'oss-lpb-save' );
	var undoBtn = document.getElementById( 'oss-lpb-undo' );
	var redoBtn = document.getElementById( 'oss-lpb-redo' );

	var doc = [];
	var selection = null;
	var dirty = false;
	var history = new window.OSSLPBHistory();
	history.onChange = syncHistoryButtons;

	/* ---- REST ---- */
	function api( method, body ) {
		return fetch( OSS_LPB.rest, {
			method: method,
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': OSS_LPB.nonce },
			credentials: 'same-origin',
			body: body ? JSON.stringify( body ) : undefined
		} ).then( function ( r ) {
			if ( ! r.ok ) { return r.json().then( function ( j ) { throw new Error( ( j && j.message ) || ( 'HTTP ' + r.status ) ); } ); }
			return r.json();
		} );
	}

	function load() {
		api( 'GET' ).then( function ( data ) {
			doc = data.document || [];
			history.reset( doc );
		} ).catch( function ( err ) {
			status( 'error', err.message );
		} );
	}

	function status( kind, text ) {
		statusEl.className = 'oss-lpb-status' + ( kind ? ' is-' + kind : '' );
		statusEl.textContent = text || '';
	}

	function save() {
		if ( saveBtn.disabled ) { return; }
		status( 'saving', 'Saving…' );
		saveBtn.disabled = true;
		api( 'POST', { document: doc, autosave: false } ).then( function ( res ) {
			doc = res.document || doc;
			dirty = false;
			status( 'saved', 'Saved ✓' );
			setTimeout( function () { if ( ! dirty ) { status( '', '' ); } }, 2500 );
		} ).catch( function ( err ) {
			status( 'error', err.message );
		} ).finally( function () { saveBtn.disabled = false; } );
	}

	/* ---- History buttons ---- */
	function syncHistoryButtons() {
		undoBtn.disabled = ! history.canUndo();
		redoBtn.disabled = ! history.canRedo();
	}
	function applySnapshot( snap ) {
		if ( ! snap ) { return; }
		doc = snap;
		dirty = true;
		// Phase 1+ will re-render the canvas from the snapshot.
	}

	/* ---- Canvas bridge ---- */
	function toCanvas( msg ) {
		if ( frame && frame.contentWindow ) {
			frame.contentWindow.postMessage( Object.assign( { source: 'oss-lpb-shell' }, msg ), window.location.origin );
		}
	}

	window.addEventListener( 'message', function ( e ) {
		if ( e.origin !== window.location.origin || ! e.data || 'oss-lpb-canvas' !== e.data.source ) { return; }
		var m = e.data;
		if ( 'ready' === m.type ) { return; }
		if ( 'select' === m.type ) {
			selection = { scope: m.scope, id: m.id, elType: m.elType };
			UI.setActiveTab( 'inspector' );
			UI.showInspector( selection, { onAction: onAction } );
		} else if ( 'deselect' === m.type ) {
			selection = null;
			UI.showInspector( null );
		} else if ( 'action' === m.type ) {
			onAction( m.act );
		}
	} );

	function onAction( act ) {
		// Phase 0: selection round-trips and the surface exists; mutations
		// (duplicate/move/hide/delete/edit) are wired in later phases.
		status( '', '' );
	}

	/* ---- Top bar wiring ---- */
	document.querySelectorAll( '.oss-lpb-device' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			document.querySelectorAll( '.oss-lpb-device' ).forEach( function ( b ) { b.classList.remove( 'is-active' ); } );
			btn.classList.add( 'is-active' );
			var device = btn.getAttribute( 'data-device' );
			document.querySelector( '.oss-lpb-canvas' ).setAttribute( 'data-device', device );
			// Let the canvas reposition its chrome after the width transition.
			setTimeout( function () { toCanvas( { type: 'reposition' } ); }, 320 );
		} );
	} );

	document.querySelectorAll( '.oss-lpb-tab' ).forEach( function ( tab ) {
		tab.addEventListener( 'click', function () {
			UI.setActiveTab( tab.getAttribute( 'data-tab' ) );
			var name = tab.getAttribute( 'data-tab' );
			if ( 'inspector' === name ) { UI.showInspector( selection, { onAction: onAction } ); }
			else if ( 'sections' === name ) { UI.hint( 'Section management (add, reorder, duplicate) arrives in a later phase.' ); }
			else { UI.hint( 'Global colors and typography arrive in a later phase.' ); }
		} );
	} );

	saveBtn.addEventListener( 'click', save );
	undoBtn.addEventListener( 'click', function () { applySnapshot( history.undo() ); } );
	redoBtn.addEventListener( 'click', function () { applySnapshot( history.redo() ); } );

	document.addEventListener( 'keydown', function ( e ) {
		var mod = e.ctrlKey || e.metaKey;
		if ( mod && 's' === e.key.toLowerCase() ) { e.preventDefault(); save(); }
		else if ( mod && 'z' === e.key.toLowerCase() && ! e.shiftKey ) { e.preventDefault(); applySnapshot( history.undo() ); }
		else if ( mod && ( 'y' === e.key.toLowerCase() || ( 'z' === e.key.toLowerCase() && e.shiftKey ) ) ) { e.preventDefault(); applySnapshot( history.redo() ); }
	} );

	window.addEventListener( 'beforeunload', function ( e ) {
		if ( dirty ) { e.preventDefault(); e.returnValue = ''; }
	} );

	syncHistoryButtons();
	load();
} )();
