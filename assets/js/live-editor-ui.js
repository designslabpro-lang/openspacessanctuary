/**
 * Live Page Builder — sidebar UI (shell side).
 *
 * Renders the left-panel contents for the current selection. Phase 0 shows the
 * selected element's type, id, and its toolbar (the same actions as the canvas
 * toolbar). Field controls for each element type land in Phase 1.
 */
( function () {
	'use strict';

	var El = window.OSSLPBElements;

	function panel() { return document.getElementById( 'oss-lpb-panel' ); }

	function hint( text ) {
		panel().innerHTML = '<p class="oss-lpb-hint">' + escapeHtml( text ) + '</p>';
	}

	/**
	 * Show the inspector for a selected node.
	 * selection = { scope, id, elType }; handlers = { onAction(act) }.
	 */
	function showInspector( selection, handlers ) {
		if ( ! selection ) { hint( 'Click an element on the page to select it.' ); return; }
		var p = panel();
		var html = '';
		html += '<div class="oss-lpb-selected">';
		html += '<div class="oss-lpb-selected__type">' + escapeHtml( El.label( selection.elType ) ) + '</div>';
		html += '<div class="oss-lpb-selected__id">' + escapeHtml( selection.id ) + '</div>';
		html += '</div>';
		html += '<p class="oss-lpb-hint">Editing controls for this element arrive in the next phase. For now you can duplicate, move, hide, or delete it.</p>';
		html += '<div class="oss-lpb-eltoolbar">';
		html += '<button data-act="duplicate">Duplicate</button>';
		html += '<button data-act="move-up">Move up</button>';
		html += '<button data-act="move-down">Move down</button>';
		if ( 'section' === selection.scope ) { html += '<button data-act="toggle-hide">Hide / Show</button>'; }
		html += '<button data-act="delete">Delete</button>';
		html += '</div>';
		p.innerHTML = html;

		p.querySelectorAll( '.oss-lpb-eltoolbar button' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				if ( handlers && handlers.onAction ) { handlers.onAction( b.getAttribute( 'data-act' ) ); }
			} );
			// Phase 0: actions are placeholders until Phase 2 wires mutations.
			b.disabled = true;
			b.title = 'Available in a later phase';
		} );
	}

	function setActiveTab( name ) {
		document.querySelectorAll( '.oss-lpb-tab' ).forEach( function ( t ) {
			t.classList.toggle( 'is-active', t.getAttribute( 'data-tab' ) === name );
		} );
	}

	function escapeHtml( s ) {
		return String( s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	window.OSSLPBUI = {
		hint: hint,
		showInspector: showInspector,
		setActiveTab: setActiveTab
	};
} )();
