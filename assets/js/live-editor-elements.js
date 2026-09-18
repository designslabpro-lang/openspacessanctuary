/**
 * Live Page Builder — element helpers (shell side).
 *
 * Document traversal utilities shared by the UI/core: find a node by id,
 * describe an element type, and (later phases) build/duplicate nodes. Phase 0
 * exposes lookup + labels; mutation helpers are stubbed for later wiring.
 */
( function () {
	'use strict';

	var LABELS = {
		section: 'Section',
		heading: 'Heading',
		paragraph: 'Paragraph',
		richtext: 'Rich Text',
		list: 'List',
		image: 'Image',
		button: 'Button',
		icon: 'Icon'
	};

	function label( type ) { return LABELS[ type ] || type; }

	/**
	 * Locate a node (section or element) by id. Returns
	 * { node, parent, index, scope } or null.
	 */
	function find( doc, id ) {
		for ( var s = 0; s < doc.length; s++ ) {
			if ( doc[ s ].id === id ) {
				return { node: doc[ s ], parent: doc, index: s, scope: 'section' };
			}
			var els = doc[ s ].elements || [];
			for ( var e = 0; e < els.length; e++ ) {
				if ( els[ e ].id === id ) {
					return { node: els[ e ], parent: els, index: e, scope: 'element' };
				}
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

	window.OSSLPBElements = {
		label: label,
		find: find,
		baseVal: baseVal
	};
} )();
