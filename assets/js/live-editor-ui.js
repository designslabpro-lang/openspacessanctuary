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

	/* ---- Globals panel (Phase 4): site-wide colors + typography ---- */
	function showGlobals( globals, handlers ) {
		var p = panel();
		p.innerHTML = '';
		var G = window.OSSLPBGlobals;

		if ( ! handlers.canEdit ) {
			hint( 'Global colors and typography are site-wide. Your role can style this page but not change site globals.' );
			return;
		}
		if ( ! globals ) { hint( 'Loading global styles…' ); return; }

		var intro = document.createElement( 'p' );
		intro.className = 'oss-lpb-hint';
		intro.style.marginTop = '0';
		intro.textContent = 'Site-wide colors and type. Elements linked to a color token update everywhere when you change it here.';
		p.appendChild( intro );

		// Colors
		p.appendChild( groupTitle( 'Colors' ) );
		G.COLORS.forEach( function ( c ) {
			p.appendChild( colorRow( c.label, globals.colors[ c.key ], function ( val ) {
				globals.colors[ c.key ] = val;
				handlers.onChange();
			} ) );
		} );

		// Typography — font families
		p.appendChild( groupTitle( 'Typography' ) );
		p.appendChild( textRow( 'Heading font', globals.fonts.heading, "e.g. 'Playfair Display', serif", function ( val ) {
			globals.fonts.heading = val; handlers.onChange();
		} ) );
		p.appendChild( textRow( 'Body font', globals.fonts.body, "e.g. 'Montserrat', sans-serif", function ( val ) {
			globals.fonts.body = val; handlers.onChange();
		} ) );

		// Typography — per-tag size / weight / line-height
		G.TAGS.forEach( function ( tag ) {
			p.appendChild( tagRow( tag.label, globals.tags[ tag.key ], function () { handlers.onChange(); } ) );
		} );
	}

	function groupTitle( text ) {
		var h = document.createElement( 'div' );
		h.className = 'oss-lpb-gtitle';
		h.textContent = text;
		return h;
	}

	function fieldShell( labelText ) {
		var f = document.createElement( 'label' );
		f.className = 'oss-lpb-field';
		var l = document.createElement( 'span' );
		l.className = 'oss-lpb-field__label';
		l.textContent = labelText;
		f.appendChild( l );
		return f;
	}

	function colorRow( labelText, value, onSet ) {
		var f = fieldShell( labelText );
		var row = document.createElement( 'span' );
		row.className = 'oss-lpb-color';
		var text = document.createElement( 'input' );
		text.type = 'text'; text.value = value || ''; text.placeholder = '#RRGGBB';
		var sw = document.createElement( 'input' );
		sw.type = 'color';
		sw.value = /^#([0-9a-f]{6})$/i.test( value ) ? value : '#000000';
		text.addEventListener( 'input', function () { onSet( text.value ); } );
		sw.addEventListener( 'input', function () { text.value = sw.value; onSet( sw.value ); } );
		row.appendChild( text ); row.appendChild( sw );
		f.appendChild( row );
		return f;
	}

	function textRow( labelText, value, placeholder, onSet ) {
		var f = fieldShell( labelText );
		var inp = document.createElement( 'input' );
		inp.type = 'text'; inp.value = value || ''; inp.placeholder = placeholder || '';
		inp.addEventListener( 'input', function () { onSet( inp.value ); } );
		f.appendChild( inp );
		return f;
	}

	function tagRow( labelText, obj, onChange ) {
		var f = document.createElement( 'div' );
		f.className = 'oss-lpb-field oss-lpb-tagrow';
		var l = document.createElement( 'span' );
		l.className = 'oss-lpb-field__label';
		l.textContent = labelText;
		f.appendChild( l );
		var grid = document.createElement( 'span' );
		grid.className = 'oss-lpb-tagrow__grid';
		[
			[ 'size', 'Size' ],
			[ 'weight', 'Weight' ],
			[ 'line_height', 'Line height' ]
		].forEach( function ( pair ) {
			var i = document.createElement( 'input' );
			i.type = 'text'; i.value = obj[ pair[ 0 ] ] || ''; i.placeholder = pair[ 1 ]; i.title = pair[ 1 ];
			i.addEventListener( 'input', function () { obj[ pair[ 0 ] ] = i.value; onChange(); } );
			grid.appendChild( i );
		} );
		f.appendChild( grid );
		return f;
	}

	function esc( s ) {
		return String( s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	window.OSSLPBUI = { hint: hint, showInspector: showInspector, showSections: showSections, showGlobals: showGlobals, setActiveTab: setActiveTab };
} )();
