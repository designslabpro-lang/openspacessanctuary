/**
 * Live Page Builder — element helpers (shell side).
 *
 * Document traversal, setting writes, and (Phase 2) structural mutation
 * helpers: id generation, deep clone with fresh ids, and node factories for
 * "add section" / "add element". Pure functions; the core orchestrates them.
 */
( function () {
	'use strict';

	var LABELS = {
		section: 'Section', heading: 'Heading', paragraph: 'Paragraph',
		richtext: 'Rich Text', list: 'List', image: 'Image', button: 'Button', icon: 'Icon'
	};
	function label( type ) { return LABELS[ type ] || type; }

	/** Locate a node by id → { node, parent, index, scope } or null. */
	function find( doc, id ) {
		for ( var s = 0; s < doc.length; s++ ) {
			if ( doc[ s ].id === id ) { return { node: doc[ s ], parent: doc, index: s, scope: 'section' }; }
			var els = doc[ s ].elements || [];
			for ( var e = 0; e < els.length; e++ ) {
				if ( els[ e ].id === id ) { return { node: els[ e ], parent: els, index: e, scope: 'element', section: doc[ s ] }; }
			}
		}
		return null;
	}

	function baseVal( settings, key, def ) {
		if ( ! settings || ! ( key in settings ) ) { return def === undefined ? '' : def; }
		var v = settings[ key ];
		if ( v && typeof v === 'object' && ! Array.isArray( v ) ) { return 'desktop' in v ? v.desktop : ( def || '' ); }
		return v;
	}

	function isResponsive( type, key ) {
		try {
			var spec = OSS_LPB.schema[ type ] && OSS_LPB.schema[ type ][ key ];
			return typeof spec === 'string' && spec.slice( -11 ) === '_responsive';
		} catch ( e ) { return false; }
	}

	function writeSetting( node, type, key, value, opts ) {
		opts = opts || {};
		node.settings = node.settings || {};
		var empty = value === '' || value == null || ( Array.isArray( value ) && ! value.length );
		if ( empty && ! opts.keepEmpty ) { delete node.settings[ key ]; return; }
		if ( opts.raw || Array.isArray( value ) ) { node.settings[ key ] = value; return; }
		if ( isResponsive( type, key ) ) {
			var cur = node.settings[ key ];
			if ( cur && typeof cur === 'object' && ! Array.isArray( cur ) ) { cur.desktop = value; node.settings[ key ] = cur; }
			else { node.settings[ key ] = { desktop: value }; }
			return;
		}
		node.settings[ key ] = value;
	}

	/* ---- Phase 2: structural helpers ---- */

	function makeId( prefix ) { return prefix + '_' + ( Math.random().toString( 36 ) + '00000000' ).slice( 2, 10 ); }

	function reId( node ) {
		node.id = makeId( 'section' === node.type ? 'sec' : 'el' );
		if ( 'section' === node.type && node.elements ) {
			node.elements.forEach( function ( e ) { e.id = makeId( 'el' ); } );
		}
		return node;
	}

	function deepClone( node ) { return JSON.parse( JSON.stringify( node ) ); }

	function starterSection() {
		return reId( {
			type: 'section',
			settings: { padding: { desktop: '64px 0' }, content_align: 'center' },
			elements: [
				{ type: 'heading', settings: { text: 'New Section', level: 'h2' } },
				{ type: 'paragraph', settings: { text: 'Add your content here.' } }
			]
		} );
	}

	function starterElement( type ) {
		var seeds = {
			heading:   { text: 'New Heading', level: 'h2' },
			paragraph: { text: 'New paragraph text.' },
			richtext:  { html: '<p>Rich text content.</p>' },
			list:      { items: [ 'Item one', 'Item two', 'Item three' ], style: 'disc' },
			image:     {},
			button:    { text: 'Button', url: '#' },
			icon:      { name: '★', size: '2rem' }
		};
		return { id: makeId( 'el' ), type: type, settings: seeds[ type ] || {} };
	}

	/** Section label for lists: first heading text, else "Section N". */
	function sectionLabel( section, i ) {
		var els = section.elements || [];
		for ( var e = 0; e < els.length; e++ ) {
			if ( 'heading' === els[ e ].type ) {
				var t = baseVal( els[ e ].settings, 'text', '' );
				if ( t ) { return t.length > 40 ? t.slice( 0, 40 ) + '…' : t; }
			}
		}
		return 'Section ' + ( i + 1 );
	}

	window.OSSLPBElements = {
		label: label, find: find, baseVal: baseVal, writeSetting: writeSetting, isResponsive: isResponsive,
		makeId: makeId, reId: reId, deepClone: deepClone, starterSection: starterSection, starterElement: starterElement, sectionLabel: sectionLabel
	};
} )();
