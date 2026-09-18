/**
 * Live Page Builder — editor shell core.
 *
 * Owns the working document + history, the postMessage bridge to the canvas,
 * and the top bar. Phase 1: field/inline edits apply live. Phase 2: structural
 * actions (add/duplicate/move/delete/hide, reorder) mutate the document and
 * re-render the canvas at the in-progress state (persisted to an autosave
 * draft), with undo/redo across the whole session. Save commits via REST.
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
	var activeTab = 'inspector';
	var device = 'desktop';
	var dirty = false;
	var histTimer = null;
	var pendingSelect = null;
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
		api( 'GET' ).then( function ( data ) { doc = data.document || []; history.reset( doc ); } )
			.catch( function ( err ) { status( 'error', err.message ); } );
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
		} ).catch( function ( err ) { status( 'error', err.message ); } )
			.finally( function () { saveBtn.disabled = false; } );
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
		rerender( selection );
	}

	/* Persist the working doc to the autosave draft, then reload the canvas so
	   the server renders the exact in-progress structure. selectAfter is
	   restored once the canvas signals ready. */
	function rerender( selectAfter ) {
		markSavable();
		status( 'saving', 'Updating…' );
		api( 'POST', { document: doc, autosave: true } ).then( function () {
			pendingSelect = selectAfter || null;
			frame.contentWindow.location.reload();
			status( '', '' );
		} ).catch( function ( err ) { status( 'error', err.message ); } );
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

	function refreshInspector() { UI.showInspector( selection, currentNode(), inspectorHandlers(), device ); }
	function refreshActiveTab() {
		if ( 'inspector' === activeTab ) { refreshInspector(); }
		else if ( 'sections' === activeTab ) { UI.showSections( doc, sectionHandlers() ); }
		else { UI.hint( 'Global colors and typography arrive in a later phase.' ); }
	}

	window.addEventListener( 'message', function ( e ) {
		if ( e.origin !== window.location.origin || ! e.data || 'oss-lpb-canvas' !== e.data.source ) { return; }
		var m = e.data;
		if ( 'ready' === m.type ) {
			if ( pendingSelect ) {
				selection = { scope: pendingSelect.scope, id: pendingSelect.id, elType: pendingSelect.elType || elTypeOf( pendingSelect.id ) };
				toCanvas( { type: 'select-node', scope: selection.scope, id: selection.id } );
			} else if ( selection ) {
				toCanvas( { type: 'select-node', scope: selection.scope, id: selection.id } );
			}
			pendingSelect = null;
			refreshActiveTab();
			return;
		}
		if ( 'select' === m.type ) {
			selection = { scope: m.scope, id: m.id, elType: m.elType };
			activeTab = 'inspector';
			UI.setActiveTab( 'inspector' );
			refreshInspector();
		} else if ( 'deselect' === m.type ) {
			selection = null;
			if ( 'inspector' === activeTab ) { UI.showInspector( null ); }
		} else if ( 'text-input' === m.type || 'text-commit' === m.type ) {
			var hit = El.find( doc, m.id );
			if ( hit ) {
				El.writeSetting( hit.node, hit.node.type, 'text', m.text );
				markSavable();
				if ( 'text-commit' === m.type ) { pushHistorySoon(); refreshInspector(); }
			}
		} else if ( 'action' === m.type ) {
			onAction( m.act );
		}
	} );

	function elTypeOf( id ) {
		var hit = El.find( doc, id );
		return hit ? hit.node.type : 'element';
	}

	/* ---- field editing (Phase 1) ---- */
	function inspectorHandlers() {
		return { onChange: onFieldChange, onAction: onAction, onAddElement: onAddElement };
	}
	function onFieldChange( key, value, opts ) {
		var node = currentNode();
		if ( ! node ) { return; }
		El.writeSetting( node, node.type, key, value, opts );
		markSavable();
		var settings = shallow( node.settings );
		if ( opts && opts.previewUrl ) {
			if ( 'section' === selection.scope ) { settings._bg_preview = opts.previewUrl; }
			else { settings._preview = opts.previewUrl; }
		}
		toCanvas( { type: 'apply', scope: selection.scope, id: selection.id, elType: selection.elType, settings: settings } );
		// Hiding a section can't be un-clicked in the canvas; refresh sections list too.
		if ( 'hidden' === key && 'sections' === activeTab ) { refreshActiveTab(); }
		pushHistorySoon();
	}
	function shallow( o ) { var c = {}; for ( var k in o ) { c[ k ] = o[ k ]; } return c; }

	/* ---- structural actions (Phase 2) ---- */
	function onAction( act ) {
		if ( ! selection ) { return; }
		doActionOn( selection.id, act );
	}
	function doActionOn( id, act ) {
		var hit = El.find( doc, id );
		if ( ! hit ) { return; }

		if ( 'toggle-hide' === act ) {
			var s = hit.node.settings = hit.node.settings || {};
			if ( s.hidden ) { delete s.hidden; } else { s.hidden = 1; }
			pushHistorySoon();
			rerender( { scope: hit.scope, id: id, elType: hit.node.type } );
			return;
		}
		if ( 'duplicate' === act ) {
			var clone = El.reId( El.deepClone( hit.node ) );
			hit.parent.splice( hit.index + 1, 0, clone );
			pushHistorySoon();
			rerender( { scope: hit.scope, id: clone.id, elType: clone.type } );
			return;
		}
		if ( 'delete' === act ) {
			hit.parent.splice( hit.index, 1 );
			selection = null;
			pushHistorySoon();
			rerender( null );
			return;
		}
		if ( 'move-up' === act || 'move-down' === act ) {
			var to = hit.index + ( 'move-up' === act ? -1 : 1 );
			if ( to < 0 || to >= hit.parent.length ) { return; }
			var moved = hit.parent.splice( hit.index, 1 )[ 0 ];
			hit.parent.splice( to, 0, moved );
			pushHistorySoon();
			rerender( { scope: hit.scope, id: id, elType: hit.node.type } );
			return;
		}
	}

	function onAddElement( type ) {
		if ( ! selection ) { return; }
		var hit = El.find( doc, selection.id );
		if ( ! hit ) { return; }
		var section = ( 'section' === hit.scope ) ? hit.node : hit.section;
		if ( ! section ) { return; }
		section.elements = section.elements || [];
		var el = El.starterElement( type );
		// Insert after the selected element, or at the end of the section.
		if ( 'element' === hit.scope ) {
			var idx = section.elements.indexOf( hit.node );
			section.elements.splice( idx + 1, 0, el );
		} else {
			section.elements.push( el );
		}
		pushHistorySoon();
		rerender( { scope: 'element', id: el.id, elType: type } );
	}

	/* ---- sections tab ---- */
	function sectionHandlers() {
		return {
			onSelectNode: function ( scope, id ) { toCanvas( { type: 'select-node', scope: scope, id: id } ); },
			onSectionAction: function ( id, act ) { doActionOn( id, act ); },
			onAddSection: function () {
				var sec = El.starterSection();
				doc.push( sec );
				pushHistorySoon();
				rerender( { scope: 'section', id: sec.id, elType: 'section' } );
			},
			onReorder: function ( ids ) {
				var map = {};
				doc.forEach( function ( s ) { map[ s.id ] = s; } );
				var next = [];
				ids.forEach( function ( id ) { if ( map[ id ] ) { next.push( map[ id ] ); } } );
				if ( next.length === doc.length ) { doc = next; pushHistorySoon(); rerender( selection ); }
			}
		};
	}

	/* ---- top bar ---- */
	document.querySelectorAll( '.oss-lpb-device' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			document.querySelectorAll( '.oss-lpb-device' ).forEach( function ( b ) { b.classList.remove( 'is-active' ); } );
			btn.classList.add( 'is-active' );
			device = btn.getAttribute( 'data-device' );
			document.querySelector( '.oss-lpb-canvas' ).setAttribute( 'data-device', device );
			// Responsive fields now edit this breakpoint.
			if ( 'inspector' === activeTab ) { refreshInspector(); }
			setTimeout( function () { toCanvas( { type: 'reposition' } ); }, 320 );
		} );
	} );

	document.querySelectorAll( '.oss-lpb-tab' ).forEach( function ( tab ) {
		tab.addEventListener( 'click', function () {
			activeTab = tab.getAttribute( 'data-tab' );
			UI.setActiveTab( activeTab );
			refreshActiveTab();
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

	window.addEventListener( 'beforeunload', function ( e ) { if ( dirty ) { e.preventDefault(); e.returnValue = ''; } } );

	syncHistoryButtons();
	load();
} )();
