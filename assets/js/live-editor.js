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
	var Globals = window.OSSLPBGlobals;
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

	// Working copy of the site-wide globals. Editing it lives in the Globals tab;
	// the swatch palette in the color fields reads this same object.
	var globals = ( Globals && OSS_LPB.globals ) ? Globals.clone( OSS_LPB.globals ) : null;
	if ( Globals ) { Globals.set( globals ); }
	var globalsDirty = false;
	var histTimer = null;
	var pendingSelect = null;
	var history = new window.OSSLPBHistory();
	history.onChange = syncHistoryButtons;

	// Phase 5 — autosave + recovery state.
	var savedDoc = [];          // last version committed to real meta (for discard/equality).
	var autosaveTimer = null;   // debounce handle for content autosaves.
	var autosaveActive = false; // an autosave request is in flight.
	var autosaveQueued = false; // another edit landed while a request was in flight.
	var lastAutosaved = '';     // serialized doc as last persisted to the draft.
	var AUTOSAVE_DELAY = 1200;

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
			var real = data.document || [];
			savedDoc = clone( real );
			// A leftover autosave draft that differs from the last save means the
			// previous session ended with unsaved work. The canvas already renders
			// that draft, so continue from it and offer a one-click revert.
			if ( data.draft && ! sameDoc( data.draft, real ) ) {
				doc = data.draft;
				dirty = true;
				lastAutosaved = JSON.stringify( doc ); // the draft is already persisted.
				showRecovery( true );
			} else {
				doc = real;
				lastAutosaved = JSON.stringify( doc );
			}
			history.reset( doc );
		} ).catch( function ( err ) { status( 'error', err.message ); } );
	}

	function clone( o ) { return JSON.parse( JSON.stringify( o ) ); }
	function sameDoc( a, b ) { return JSON.stringify( a ) === JSON.stringify( b ); }

	function status( kind, text ) {
		statusEl.className = 'oss-lpb-status' + ( kind ? ' is-' + kind : '' );
		statusEl.textContent = text || '';
	}

	function save() {
		if ( saveBtn.disabled ) { return; }
		// A committed save supersedes any queued autosave.
		clearTimeout( autosaveTimer );
		autosaveQueued = false;
		status( 'saving', 'Saving…' );
		saveBtn.disabled = true;
		var jobs = [ api( 'POST', { document: doc, autosave: false } ).then( function ( res ) {
			doc = res.document || doc;
			savedDoc = clone( doc );
			lastAutosaved = JSON.stringify( doc );
		} ) ];
		if ( globalsDirty && globals && OSS_LPB.canGlobals ) {
			jobs.push( saveGlobals() );
		}
		Promise.all( jobs ).then( function () {
			dirty = false;
			showRecovery( false ); // the draft is gone; hide any recovery bar.
			status( 'saved', 'Saved ✓' );
			setTimeout( function () { if ( ! dirty ) { status( '', '' ); } }, 2500 );
		} ).catch( function ( err ) { status( 'error', err.message ); } )
			.finally( function () { saveBtn.disabled = false; } );
	}

	function saveGlobals() {
		return fetch( OSS_LPB.restGlobals, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': OSS_LPB.nonce },
			credentials: 'same-origin',
			body: JSON.stringify( { globals: globals } )
		} ).then( function ( r ) {
			if ( ! r.ok ) { return r.json().then( function ( j ) { throw new Error( ( j && j.message ) || ( 'HTTP ' + r.status ) ); } ); }
			return r.json();
		} ).then( function ( res ) {
			if ( res && res.globals ) { globals = res.globals; if ( Globals ) { Globals.set( globals ); } }
			globalsDirty = false;
		} );
	}

	/* ---- enable / disable the builder for this page (Phase 6) ---- */
	function enablePage( on ) {
		var btn = on ? document.getElementById( 'oss-lpb-enable' ) : document.getElementById( 'oss-lpb-disable' );
		if ( btn ) { btn.disabled = true; }
		status( 'saving', on ? 'Enabling…' : 'Turning off…' );
		fetch( OSS_LPB.restEnable, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': OSS_LPB.nonce },
			credentials: 'same-origin',
			body: JSON.stringify( { enabled: !! on } )
		} ).then( function ( r ) {
			if ( ! r.ok ) { return r.json().then( function ( j ) { throw new Error( ( j && j.message ) || ( 'HTTP ' + r.status ) ); } ); }
			return r.json();
		} ).then( function () {
			// Reload the editor shell so it re-renders in the new mode.
			window.location.reload();
		} ).catch( function ( err ) {
			status( 'error', err.message );
			if ( btn ) { btn.disabled = false; }
		} );
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
		clearTimeout( autosaveTimer ); // this write supersedes any debounced one.
		status( 'saving', 'Updating…' );
		var snap = JSON.stringify( doc );
		api( 'POST', { document: doc, autosave: true } ).then( function () {
			lastAutosaved = snap;
			pendingSelect = selectAfter || null;
			reloadCanvas();
			status( '', '' );
		} ).catch( function ( err ) { status( 'error', err.message ); } );
	}

	/* Reload the iframe without writing anything (used after a discard). */
	function reloadCanvas() {
		if ( frame && frame.contentWindow ) { frame.contentWindow.location.reload(); }
	}

	function markSavable() { dirty = true; }

	/* ---- autosave engine (Phase 5) ----
	   Content/field edits update the doc in memory and the canvas in place, so
	   they don't need a canvas reload — but we still persist them to the autosave
	   draft (debounced) so a crash or accidental close is recoverable. */
	function scheduleAutosave() {
		markSavable();
		clearTimeout( autosaveTimer );
		autosaveTimer = setTimeout( flushAutosave, AUTOSAVE_DELAY );
	}
	function flushAutosave() {
		if ( ! dirty ) { return; }
		var snap = JSON.stringify( doc );
		if ( snap === lastAutosaved ) { return; } // nothing new since the last draft write.
		if ( autosaveActive ) { autosaveQueued = true; return; }
		autosaveActive = true;
		if ( ! saveBtn.disabled ) { status( 'saving', 'Autosaving…' ); }
		api( 'POST', { document: doc, autosave: true } ).then( function () {
			lastAutosaved = snap;
			if ( ! saveBtn.disabled && dirty ) {
				status( 'saved', 'Autosaved ✓' );
				setTimeout( function () { if ( statusEl.textContent === 'Autosaved ✓' ) { status( '', '' ); } }, 1800 );
			}
		} ).catch( function () { /* transient; the next edit or Save retries. */ } )
			.finally( function () {
				autosaveActive = false;
				if ( autosaveQueued ) { autosaveQueued = false; flushAutosave(); }
			} );
	}

	/* ---- draft recovery (Phase 5) ---- */
	var recoverBar = document.getElementById( 'oss-lpb-recover' );
	function showRecovery( on ) { if ( recoverBar ) { recoverBar.hidden = ! on; } }
	function discardDraft() {
		clearTimeout( autosaveTimer );
		autosaveQueued = false;
		doc = clone( savedDoc );
		selection = null;
		history.reset( doc );
		dirty = false;
		lastAutosaved = JSON.stringify( doc );
		showRecovery( false );
		status( 'saving', 'Reverting…' );
		api( 'POST', { discard: true } ).then( function () {
			pendingSelect = null;
			reloadCanvas();
			status( '', '' );
			refreshActiveTab();
		} ).catch( function ( err ) { status( 'error', err.message ); } );
	}

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
		else { UI.showGlobals( globals, globalHandlers() ); }
	}

	/* ---- global colors + typography (Phase 4) ---- */
	function globalHandlers() {
		return { canEdit: !! OSS_LPB.canGlobals, onChange: onGlobalChange };
	}
	function onGlobalChange() {
		if ( ! globals ) { return; }
		globalsDirty = true;
		markSavable();
		applyGlobalsToCanvas();
	}
	function applyGlobalsToCanvas() {
		if ( ! Globals || ! globals ) { return; }
		toCanvas( { type: 'globals', css: Globals.css( globals ) } );
	}

	window.addEventListener( 'message', function ( e ) {
		if ( e.origin !== window.location.origin || ! e.data || 'oss-lpb-canvas' !== e.data.source ) { return; }
		var m = e.data;
		if ( 'ready' === m.type ) {
			// The freshly loaded canvas has the server's saved globals; re-push any
			// unsaved edits so the working state survives a canvas reload.
			if ( globalsDirty ) { applyGlobalsToCanvas(); }
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
				if ( 'text-commit' === m.type ) { pushHistorySoon(); scheduleAutosave(); refreshInspector(); }
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
		scheduleAutosave();
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

	var keepBtn = document.getElementById( 'oss-lpb-recover-keep' );
	var discardBtn = document.getElementById( 'oss-lpb-recover-discard' );
	if ( keepBtn ) { keepBtn.addEventListener( 'click', function () { showRecovery( false ); } ); }
	if ( discardBtn ) { discardBtn.addEventListener( 'click', discardDraft ); }

	// Safety net: flush any pending edit at most a few seconds after it lands,
	// even if the user goes idle mid-debounce.
	setInterval( function () { if ( dirty && ! autosaveActive ) { flushAutosave(); } }, 5000 );

	document.addEventListener( 'keydown', function ( e ) {
		var mod = e.ctrlKey || e.metaKey;
		if ( ! mod ) { return; }
		var k = e.key.toLowerCase();
		if ( 's' === k ) { e.preventDefault(); save(); }
		else if ( 'z' === k && ! e.shiftKey ) { e.preventDefault(); applySnapshot( history.undo() ); }
		else if ( 'y' === k || ( 'z' === k && e.shiftKey ) ) { e.preventDefault(); applySnapshot( history.redo() ); }
	} );

	window.addEventListener( 'beforeunload', function ( e ) { if ( dirty ) { e.preventDefault(); e.returnValue = ''; } } );

	var enableBtn = document.getElementById( 'oss-lpb-enable' );
	var disableBtn = document.getElementById( 'oss-lpb-disable' );
	if ( enableBtn ) { enableBtn.addEventListener( 'click', function () { enablePage( true ); } ); }
	if ( disableBtn ) { disableBtn.addEventListener( 'click', function () {
		if ( window.confirm( 'Turn off the Live Builder for this page? It will go back to its normal template and design. Your builder content is kept and returns if you enable it again.' ) ) { enablePage( false ); }
	} ); }

	if ( OSS_LPB.isBuilder ) {
		syncHistoryButtons();
		load();
	} else {
		// Not a builder page: only the "Enable" action is meaningful.
		saveBtn.disabled = true;
		UI.hint( 'This page isn’t built with the Live Builder yet. Use the panel on the right to enable it, then click elements to edit them.' );
	}
} )();
