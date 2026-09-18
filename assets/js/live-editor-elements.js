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
			return !! ( spec && typeof spec === 'object' && ! Array.isArray( spec ) && spec.responsive );
		} catch ( e ) { return false; }
	}

	/** A responsive setting's own value at one breakpoint (no inheritance). */
	function responsiveVal( settings, key, device ) {
		if ( ! settings || ! ( key in settings ) ) { return ''; }
		var v = settings[ key ];
		if ( v && typeof v === 'object' && ! Array.isArray( v ) ) { return device in v ? v[ device ] : ''; }
		return 'desktop' === device ? v : ''; // legacy scalar counts as desktop
	}

	/** The value that actually applies at a breakpoint (mobile←tablet←desktop). */
	function inheritedVal( settings, key, device ) {
		if ( ! settings || ! ( key in settings ) ) { return ''; }
		var v = settings[ key ];
		if ( ! v || typeof v !== 'object' || Array.isArray( v ) ) { return v || ''; }
		var chain = { desktop: [ 'desktop' ], tablet: [ 'tablet', 'desktop' ], mobile: [ 'mobile', 'tablet', 'desktop' ] }[ device ] || [ 'desktop' ];
		for ( var i = 0; i < chain.length; i++ ) { if ( v[ chain[ i ] ] ) { return v[ chain[ i ] ]; } }
		return '';
	}

	/** Write a per-breakpoint value into a responsive setting. */
	function writeResponsive( node, key, device, value ) {
		node.settings = node.settings || {};
		var cur = node.settings[ key ];
		if ( ! cur || typeof cur !== 'object' || Array.isArray( cur ) ) {
			cur = cur ? { desktop: cur } : {};
		}
		if ( value === '' || value == null ) { delete cur[ device ]; }
		else { cur[ device ] = value; }
		if ( Object.keys( cur ).length ) { node.settings[ key ] = cur; }
		else { delete node.settings[ key ]; }
	}

	function writeSetting( node, type, key, value, opts ) {
		opts = opts || {};
		node.settings = node.settings || {};
		var empty = value === '' || value == null || ( Array.isArray( value ) && ! value.length );
		if ( opts.device && isResponsive( type, key ) ) { writeResponsive( node, key, opts.device, value ); return; }
		if ( empty && ! opts.keepEmpty ) { delete node.settings[ key ]; return; }
		if ( opts.raw || Array.isArray( value ) ) { node.settings[ key ] = value; return; }
		if ( isResponsive( type, key ) ) { writeResponsive( node, key, 'desktop', value ); return; }
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
		responsiveVal: responsiveVal, inheritedVal: inheritedVal, writeResponsive: writeResponsive,
		makeId: makeId, reId: reId, deepClone: deepClone, starterSection: starterSection, starterElement: starterElement, sectionLabel: sectionLabel
	};
} )();
