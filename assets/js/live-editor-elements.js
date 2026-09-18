/**
 * Live Page Builder — element helpers (shell side).
 *
 * Document traversal + setting writes shared by the core/UI. Knows which
 * settings are responsive (from the localized schema) so a written value is
 * stored as a {desktop:…} triple where appropriate and a scalar otherwise.
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
				if ( els[ e ].id === id ) { return { node: els[ e ], parent: els, index: e, scope: 'element' }; }
			}
		}
		return null;
	}

	/** Base (desktop) value of a possibly-responsive setting. */
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

	/**
	 * Write a setting into a node's settings, honouring responsive keys and
	 * clearing empties. `raw` keeps arrays (e.g. list items) as-is.
	 */
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

	window.OSSLPBElements = { label: label, find: find, baseVal: baseVal, writeSetting: writeSetting, isResponsive: isResponsive };
} )();
