/**
 * Live Page Builder — sidebar UI (shell side).
 *
 * Phase 1: renders the field controls (via OSSLPBFields) for the selected
 * node so its text, style, image, and button properties are editable, plus a
 * small action toolbar. Phase 0's placeholder inspector is replaced here.
 */
( function () {
	'use strict';

	var El = window.OSSLPBElements;
	var Fields = window.OSSLPBFields;

	function panel() { return document.getElementById( 'oss-lpb-panel' ); }

	function hint( text ) {
		panel().innerHTML = '<p class="oss-lpb-hint">' + escapeHtml( text ) + '</p>';
	}

	/**
	 * selection = { scope, id, elType }
	 * node      = the live document node (with .settings) or null
	 * handlers  = { onChange(key,value,opts), onAction(act) }
	 */
	function showInspector( selection, node, handlers ) {
		if ( ! selection || ! node ) { hint( 'Click an element on the page to select it. Double-click text to edit it inline.' ); return; }
		var p = panel();
		p.innerHTML = '';

		var head = document.createElement( 'div' );
		head.className = 'oss-lpb-selected';
		head.innerHTML =
			'<div class="oss-lpb-selected__type">' + escapeHtml( El.label( selection.elType ) ) + '</div>' +
			'<div class="oss-lpb-selected__id">' + escapeHtml( selection.id ) + '</div>';
		p.appendChild( head );

		var fields = document.createElement( 'div' );
		fields.className = 'oss-lpb-fields';
		p.appendChild( fields );
		Fields.render( fields, node, function ( key, value, opts ) {
			handlers.onChange( key, value, opts || {} );
		} );

		var bar = document.createElement( 'div' );
		bar.className = 'oss-lpb-eltoolbar';
		var acts = [ [ 'duplicate', 'Duplicate' ], [ 'move-up', 'Move up' ], [ 'move-down', 'Move down' ] ];
		if ( 'section' === selection.scope ) { acts.push( [ 'toggle-hide', 'Hide / Show' ] ); }
		acts.push( [ 'delete', 'Delete' ] );
		acts.forEach( function ( a ) {
			var b = document.createElement( 'button' );
			b.textContent = a[ 1 ];
			b.disabled = true; // Section actions are wired in Phase 2.
			b.title = 'Available in the next phase';
			b.addEventListener( 'click', function () { handlers.onAction( a[ 0 ] ); } );
			bar.appendChild( b );
		} );
		p.appendChild( bar );
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

	window.OSSLPBUI = { hint: hint, showInspector: showInspector, setActiveTab: setActiveTab };
} )();
