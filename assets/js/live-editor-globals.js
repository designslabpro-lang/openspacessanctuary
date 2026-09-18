/**
 * Live Page Builder — global colors + typography model (Phase 4).
 *
 * Holds the working copy of the site-wide globals, and generates the exact
 * same CSS the PHP renderer emits (:root custom properties + low-specificity
 * :where() inherit rules) so the canvas can preview edits instantly without a
 * server round-trip. Editor-only; visitors never load this.
 */
( function () {
	'use strict';

	var COLORS = [
		{ key: 'primary', label: 'Primary' },
		{ key: 'secondary', label: 'Secondary' },
		{ key: 'accent', label: 'Accent' },
		{ key: 'heading', label: 'Heading' },
		{ key: 'body', label: 'Body Text' },
		{ key: 'background', label: 'Background' },
		{ key: 'button', label: 'Button' }
	];
	var HEADINGS = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
	var TAGS = [
		{ key: 'h1', label: 'Heading 1' },
		{ key: 'h2', label: 'Heading 2' },
		{ key: 'h3', label: 'Heading 3' },
		{ key: 'h4', label: 'Heading 4' },
		{ key: 'h5', label: 'Heading 5' },
		{ key: 'h6', label: 'Heading 6' },
		{ key: 'body', label: 'Body' },
		{ key: 'button', label: 'Button' }
	];

	var current = null;

	function set( g ) { if ( g && typeof g === 'object' ) { current = g; } }
	function get() { return current; }

	function v( x ) { return ( x === undefined || x === null ) ? '' : String( x ); }

	function css( g ) {
		g = g || current;
		if ( ! g ) { return ''; }
		var c = g.colors || {}, f = g.fonts || {}, t = g.tags || {};
		var vars = '';
		vars += '--site-primary:' + v( c.primary ) + ';';
		vars += '--site-secondary:' + v( c.secondary ) + ';';
		vars += '--site-accent:' + v( c.accent ) + ';';
		vars += '--site-heading:' + v( c.heading ) + ';';
		vars += '--site-body:' + v( c.body ) + ';';
		vars += '--site-background:' + v( c.background ) + ';';
		vars += '--site-button:' + v( c.button ) + ';';
		vars += '--site-font-heading:' + v( f.heading ) + ';';
		vars += '--site-font-body:' + v( f.body ) + ';';
		TAGS.forEach( function ( tag ) {
			var p = t[ tag.key ] || {};
			vars += '--site-' + tag.key + '-size:' + v( p.size ) + ';';
			vars += '--site-' + tag.key + '-weight:' + v( p.weight ) + ';';
			vars += '--site-' + tag.key + '-lh:' + v( p.line_height ) + ';';
		} );

		var out = ':root{' + vars + '}';
		out += ':where(.oss-lpb-doc){font-family:var(--site-font-body);color:var(--site-body);font-size:var(--site-body-size);line-height:var(--site-body-lh);}';
		HEADINGS.forEach( function ( h ) {
			out += ':where(.oss-lpb-doc ' + h + '){font-family:var(--site-font-heading);color:var(--site-heading);'
				+ 'font-size:var(--site-' + h + '-size);font-weight:var(--site-' + h + '-weight);line-height:var(--site-' + h + '-lh);}';
		} );
		out += ':where(.oss-lpb-doc p),:where(.oss-lpb-doc li){font-size:var(--site-body-size);line-height:var(--site-body-lh);}';
		out += ':where(.oss-lpb-doc .oss-lpb-btn){font-family:var(--site-font-body);font-size:var(--site-button-size);font-weight:var(--site-button-weight);line-height:var(--site-button-lh);}';
		return out;
	}

	/* Deep clone so edits never mutate the loaded baseline until saved. */
	function clone( g ) { return g ? JSON.parse( JSON.stringify( g ) ) : g; }

	window.OSSLPBGlobals = {
		set: set,
		get: get,
		css: css,
		clone: clone,
		COLORS: COLORS,
		TAGS: TAGS
	};
} )();
