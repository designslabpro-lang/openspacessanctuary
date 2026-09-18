/**
 * Live Page Builder — sidebar UI (shell side).
 *
 * Phase 1: field controls for the selected node.
 * Phase 2: enabled element/section actions (duplicate, move, delete, hide),
 * an "Add element" picker in the inspector, and a Sections tab that lists,
 * selects, reorders (drag), adds, and manages sections.
 */
( function () {
	'use strict';

	var El = window.OSSLPBElements;
	var Fields = window.OSSLPBFields;

	var ADDABLE = [ 'heading', 'paragraph', 'richtext', 'list', 'image', 'button', 'icon' ];

	function panel() { return document.getElementById( 'oss-lpb-panel' ); }
	function hint( text ) { panel().innerHTML = '<p class="oss-lpb-hint">' + esc( text ) + '</p>'; }

	/**
	 * Inspector for a selected node.
	 * handlers = { onChange, onAction, onAddElement }
	 */
	function showInspector( selection, node, handlers, device ) {
		if ( ! selection || ! node ) { hint( 'Click an element on the page to select it. Double-click text to edit it inline.' ); return; }
		var p = panel();
		p.innerHTML = '';

		var head = document.createElement( 'div' );
		head.className = 'oss-lpb-selected';
		head.innerHTML =
			'<div class="oss-lpb-selected__type">' + esc( El.label( selection.elType ) ) + '</div>' +
			'<div class="oss-lpb-selected__id">' + esc( selection.id ) + '</div>';
		p.appendChild( head );

		var fields = document.createElement( 'div' );
		fields.className = 'oss-lpb-fields';
		p.appendChild( fields );
		Fields.render( fields, node, function ( key, value, opts ) { handlers.onChange( key, value, opts || {} ); }, device );

		// Structural actions for this node.
		var bar = document.createElement( 'div' );
		bar.className = 'oss-lpb-eltoolbar';
		var acts = [ [ 'duplicate', 'Duplicate' ], [ 'move-up', 'Move up' ], [ 'move-down', 'Move down' ], [ 'delete', 'Delete' ] ];
		acts.forEach( function ( a ) {
			var b = document.createElement( 'button' );
			b.textContent = a[ 1 ];
			if ( 'delete' === a[ 0 ] ) { b.className = 'is-danger'; }
			b.addEventListener( 'click', function () { handlers.onAction( a[ 0 ] ); } );
			bar.appendChild( b );
		} );
		p.appendChild( bar );

		// "Add element" into the section that owns this selection.
		var add = document.createElement( 'div' );
		add.className = 'oss-lpb-addrow';
		var sel = document.createElement( 'select' );
		sel.innerHTML = '<option value="">+ Add element…</option>' + ADDABLE.map( function ( t ) {
			return '<option value="' + t + '">' + esc( El.label( t ) ) + '</option>';
		} ).join( '' );
		sel.addEventListener( 'change', function () { if ( sel.value ) { handlers.onAddElement( sel.value ); sel.value = ''; } } );
		add.appendChild( sel );
		p.appendChild( add );
	}

	/**
	 * Sections tab: manage the whole page structure.
	 * handlers = { onSelectNode(scope,id), onSectionAction(id,act), onAddSection, onReorder(ids) }
	 */
	function showSections( doc, handlers ) {
		var p = panel();
		p.innerHTML = '';

		var addBtn = document.createElement( 'button' );
		addBtn.className = 'oss-lpb-mini oss-lpb-addsection';
		addBtn.textContent = '+ Add Section';
		addBtn.addEventListener( 'click', handlers.onAddSection );
		p.appendChild( addBtn );

		var list = document.createElement( 'ul' );
		list.className = 'oss-lpb-seclist';
		doc.forEach( function ( section, i ) {
			list.appendChild( sectionRow( section, i, handlers ) );
		} );
		enableDrag( list, handlers );
		p.appendChild( list );

		if ( ! doc.length ) {
			var empty = document.createElement( 'p' );
			empty.className = 'oss-lpb-hint';
			empty.textContent = 'No sections yet. Add one to start building.';
			p.appendChild( empty );
		}
	}

	function sectionRow( section, i, handlers ) {
		var li = document.createElement( 'li' );
		li.className = 'oss-lpb-secitem' + ( section.settings && section.settings.hidden ? ' is-hidden' : '' );
		li.setAttribute( 'draggable', 'true' );
		li.setAttribute( 'data-id', section.id );

		var handle = document.createElement( 'span' );
		handle.className = 'oss-lpb-sechandle';
		handle.textContent = '⋮⋮';
		li.appendChild( handle );

		var name = document.createElement( 'button' );
		name.className = 'oss-lpb-secname';
		name.textContent = El.sectionLabel( section, i );
		name.addEventListener( 'click', function () { handlers.onSelectNode( 'section', section.id ); } );
		li.appendChild( name );

		var tools = document.createElement( 'span' );
		tools.className = 'oss-lpb-sectools';
		[
			[ 'move-up', '↑', 'Move up' ],
			[ 'move-down', '↓', 'Move down' ],
			[ 'toggle-hide', ( section.settings && section.settings.hidden ) ? '🚫' : '👁', 'Show / hide' ],
			[ 'duplicate', '⧉', 'Duplicate' ],
			[ 'delete', '🗑', 'Delete' ]
		].forEach( function ( t ) {
			var b = document.createElement( 'button' );
			b.textContent = t[ 1 ];
			b.title = t[ 2 ];
			if ( 'delete' === t[ 0 ] ) { b.className = 'is-danger'; }
			b.addEventListener( 'click', function ( e ) { e.stopPropagation(); handlers.onSectionAction( section.id, t[ 0 ] ); } );
			tools.appendChild( b );
		} );
		li.appendChild( tools );
		return li;
	}

	function enableDrag( list, handlers ) {
		var dragEl = null;
		list.addEventListener( 'dragstart', function ( e ) {
			dragEl = e.target.closest( '.oss-lpb-secitem' );
			if ( dragEl ) { dragEl.classList.add( 'is-dragging' ); e.dataTransfer.effectAllowed = 'move'; }
		} );
		list.addEventListener( 'dragend', function () {
			if ( dragEl ) { dragEl.classList.remove( 'is-dragging' ); dragEl = null; }
			handlers.onReorder( Array.prototype.map.call( list.querySelectorAll( '.oss-lpb-secitem' ), function ( n ) { return n.getAttribute( 'data-id' ); } ) );
		} );
		list.addEventListener( 'dragover', function ( e ) {
			e.preventDefault();
			var after = elementAfter( list, e.clientY );
			if ( ! dragEl ) { return; }
			if ( after == null ) { list.appendChild( dragEl ); }
			else { list.insertBefore( dragEl, after ); }
		} );
	}
	function elementAfter( list, y ) {
		var items = Array.prototype.slice.call( list.querySelectorAll( '.oss-lpb-secitem:not(.is-dragging)' ) );
		return items.reduce( function ( closest, child ) {
			var box = child.getBoundingClientRect();
			var offset = y - box.top - box.height / 2;
			if ( offset < 0 && offset > closest.offset ) { return { offset: offset, element: child }; }
			return closest;
		}, { offset: -Infinity, element: null } ).element;
	}

	function setActiveTab( name ) {
		document.querySelectorAll( '.oss-lpb-tab' ).forEach( function ( t ) {
			t.classList.toggle( 'is-active', t.getAttribute( 'data-tab' ) === name );
		} );
	}

	function esc( s ) {
		return String( s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	window.OSSLPBUI = { hint: hint, showInspector: showInspector, showSections: showSections, setActiveTab: setActiveTab };
} )();
