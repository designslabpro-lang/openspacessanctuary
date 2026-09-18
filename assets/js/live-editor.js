/**
 * Live Page Builder — editor shell core.
 *
 * Owns the working document + history, the postMessage bridge to the canvas,
 * and the top bar. Phase 1: field edits and inline text edits update the
 * document, apply live to the canvas without reload, push (debounced) history
 * snapshots, mark dirty, and Save persists via the secured REST endpoint.
 */
( function () {
	'use strict';

	if ( typeof OSS_LPB === 'undefined' ) { return; }

	var UI = window.OSSLPBUI;
	var El = window.OSSLPBElements;
	var frame = document.getElementById( 'oss-lpb-frame' );
	var statusEl = document.getElementById( 'oss-lpb-status' );
	var saveBtn = document.getElementById( 'oss-lpb-save' );
	var undoBtn = document.getElementById( 'oss-lpb-undo' );
	var redoBtn = document.getElementById( 'oss-lpb-redo' );

	var doc = [];
	var selection = null;
	var dirty = false;
	var histTimer = null;
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
		} ).catch( function ( err ) { status( 'error', err.message ); } );
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
		} ).catch( function ( err ) { status( 'error', err.message ); }
		).finally( function () { saveBtn.disabled = false; } );
	}

	/* ---- history ---- */
	function syncHistoryButtons() {
		undoBtn.disabled = ! history.canUndo();
		redoBtn.disabled = ! history.canRedo();
	}
	function pushHistorySoon() {
		clearTimeout( histTimer );
		histTimer = setTimeout( function () { history.push( doc ); }, 500 );
	}
	function applySnapshot( snap ) {
		if ( ! snap ) { return; }
		doc = snap;
		dirty = true;
		markSavable();
		reloadCanvas(); // simplest faithful re-render of a historical state
	}
	function reloadCanvas() {
		// Persist current doc to an autosave draft, then reload the iframe so
		// the server renders the exact state (used for undo/redo + big changes).
		api( 'POST', { document: doc, autosave: true } ).then( function () {
			frame.contentWindow.location.reload();
		} );
	}

	function markSavable() { dirty = true; }

	/* ---- canvas bridge ---- */
	function toCanvas( msg ) {
		if ( frame && frame.contentWindow ) {
			frame.contentWindow.postMessage( Object.assign( { source: 'oss-lpb-shell' }, msg ), window.location.origin );
		}
	}

	function currentNode() {
		if ( ! selection ) { return null; }
		var hit = El.find( doc, selection.id );
		return hit ? hit.node : null;
	}

	function refreshInspector() {
		UI.showInspector( selection, currentNode(), { onChange: onFieldChange, onAction: onAction } );
	}

	window.addEventListener( 'message', function ( e ) {
		if ( e.origin !== window.location.origin || ! e.data || 'oss-lpb-canvas' !== e.data.source ) { return; }
		var m = e.data;
		if ( 'ready' === m.type ) {
			// Canvas (re)loaded — restore selection if any.
			if ( selection ) { toCanvas( { type: 'select-node', scope: selection.scope, id: selection.id } ); }
			return;
		}
		if ( 'select' === m.type ) {
			selection = { scope: m.scope, id: m.id, elType: m.elType };
			UI.setActiveTab( 'inspector' );
			refreshInspector();
		} else if ( 'deselect' === m.type ) {
			selection = null;
			UI.showInspector( null );
		} else if ( 'text-input' === m.type || 'text-commit' === m.type ) {
			var hit = El.find( doc, m.id );
			if ( hit ) {
				var key = ( 'button' === hit.node.type ) ? 'text' : 'text';
				El.writeSetting( hit.node, hit.node.type, key, m.text );
				markSavable();
				if ( 'text-commit' === m.type ) { pushHistorySoon(); refreshInspector(); }
			}
		} else if ( 'action' === m.type ) {
			onAction( m.act );
		}
	} );

	/* ---- field editing ---- */
	function onFieldChange( key, value, opts ) {
		var node = currentNode();
		if ( ! node ) { return; }
		El.writeSetting( node, node.type, key, value, opts );
		markSavable();

		// Send a live-apply to the canvas (attach a preview url for media).
		var settings = shallow( node.settings );
		if ( opts && opts.previewUrl ) {
			if ( 'section' === selection.scope ) { settings._bg_preview = opts.previewUrl; }
			else { settings._preview = opts.previewUrl; }
		}
		toCanvas( { type: 'apply', scope: selection.scope, id: selection.id, elType: selection.elType, settings: settings } );

		pushHistorySoon();
	}

	function shallow( o ) { var c = {}; for ( var k in o ) { c[ k ] = o[ k ]; } return c; }

	function onAction( act ) {
		// Section/element structural actions are wired in Phase 2.
		status( '', '' );
	}

	/* ---- top bar ---- */
	document.querySelectorAll( '.oss-lpb-device' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			document.querySelectorAll( '.oss-lpb-device' ).forEach( function ( b ) { b.classList.remove( 'is-active' ); } );
			btn.classList.add( 'is-active' );
			document.querySelector( '.oss-lpb-canvas' ).setAttribute( 'data-device', btn.getAttribute( 'data-device' ) );
			setTimeout( function () { toCanvas( { type: 'reposition' } ); }, 320 );
		} );
	} );

	document.querySelectorAll( '.oss-lpb-tab' ).forEach( function ( tab ) {
		tab.addEventListener( 'click', function () {
			var name = tab.getAttribute( 'data-tab' );
			UI.setActiveTab( name );
			if ( 'inspector' === name ) { refreshInspector(); }
			else if ( 'sections' === name ) { UI.hint( 'Section management (add, reorder, duplicate) arrives in a later phase.' ); }
			else { UI.hint( 'Global colors and typography arrive in a later phase.' ); }
		} );
	} );

	saveBtn.addEventListener( 'click', save );
	undoBtn.addEventListener( 'click', function () { applySnapshot( history.undo() ); } );
	redoBtn.addEventListener( 'click', function () { applySnapshot( history.redo() ); } );

	document.addEventListener( 'keydown', function ( e ) {
		var mod = e.ctrlKey || e.metaKey;
		if ( ! mod ) { return; }
		var k = e.key.toLowerCase();
		if ( 's' === k ) { e.preventDefault(); save(); }
		else if ( 'z' === k && ! e.shiftKey ) { e.preventDefault(); applySnapshot( history.undo() ); }
		else if ( 'y' === k || ( 'z' === k && e.shiftKey ) ) { e.preventDefault(); applySnapshot( history.redo() ); }
	} );

	window.addEventListener( 'beforeunload', function ( e ) {
		if ( dirty ) { e.preventDefault(); e.returnValue = ''; }
	} );

	syncHistoryButtons();
	load();
} )();
