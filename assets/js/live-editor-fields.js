/**
 * Live Page Builder — sidebar field factory (shell side).
 *
 * Builds the property controls for a selected node and calls back on change.
 * Field definitions per element type (ordered, labelled) map to control types
 * (text, textarea, select, color, unit, bool, media, url, list). Media uses
 * the native WP Media Library. Responsive values (Phase 3) are edited as the
 * desktop value here; the control set stays forward-compatible.
 */
( function () {
	'use strict';

	var El = window.OSSLPBElements;

	var ALIGN = [ [ '', 'Default' ], [ 'left', 'Left' ], [ 'center', 'Center' ], [ 'right', 'Right' ] ];
	var LEVELS = [ [ 'h1', 'H1' ], [ 'h2', 'H2' ], [ 'h3', 'H3' ], [ 'h4', 'H4' ], [ 'h5', 'H5' ], [ 'h6', 'H6' ] ];
	var FIT = [ [ 'cover', 'Cover' ], [ 'contain', 'Contain' ], [ 'fill', 'Fill' ], [ 'none', 'None' ] ];
	var TRANSFORM = [ [ 'none', 'None' ], [ 'uppercase', 'UPPERCASE' ], [ 'capitalize', 'Capitalize' ], [ 'lowercase', 'lowercase' ] ];
	var LISTSTYLE = [ [ 'disc', 'Bullets' ], [ 'check', 'Checkmarks' ], [ 'none', 'None' ] ];
	var ICONPOS = [ [ 'left', 'Left' ], [ 'right', 'Right' ] ];

	var FIELDS = {
		heading: [
			{ k: 'text', l: 'Text', c: 'text' },
			{ k: 'level', l: 'Tag', c: 'select', o: LEVELS },
			{ k: 'color', l: 'Color', c: 'color' },
			{ k: 'align', l: 'Alignment', c: 'select', o: ALIGN },
			{ k: 'font_size', l: 'Font size', c: 'unit', ph: 'e.g. 2.4rem' },
			{ k: 'font_weight', l: 'Font weight', c: 'text', ph: '400–700' },
			{ k: 'line_height', l: 'Line height', c: 'unit', ph: '1.2' },
			{ k: 'letter_spacing', l: 'Letter spacing', c: 'unit', ph: '0.02em' },
			{ k: 'text_transform', l: 'Transform', c: 'select', o: TRANSFORM }
		],
		paragraph: [
			{ k: 'text', l: 'Text', c: 'textarea' },
			{ k: 'color', l: 'Color', c: 'color' },
			{ k: 'align', l: 'Alignment', c: 'select', o: ALIGN },
			{ k: 'font_size', l: 'Font size', c: 'unit', ph: 'e.g. 1rem' },
			{ k: 'line_height', l: 'Line height', c: 'unit', ph: '1.7' }
		],
		richtext: [
			{ k: 'html', l: 'HTML', c: 'textarea' },
			{ k: 'color', l: 'Color', c: 'color' },
			{ k: 'align', l: 'Alignment', c: 'select', o: ALIGN }
		],
		list: [
			{ k: 'items', l: 'Items (one per line)', c: 'list' },
			{ k: 'style', l: 'Style', c: 'select', o: LISTSTYLE },
			{ k: 'color', l: 'Color', c: 'color' }
		],
		image: [
			{ k: 'id', l: 'Image', c: 'media' },
			{ k: 'alt', l: 'Alt text', c: 'text' },
			{ k: 'url', l: 'Or image URL', c: 'url' },
			{ k: 'width', l: 'Width', c: 'unit', ph: '100%' },
			{ k: 'radius', l: 'Border radius', c: 'unit', ph: '12px' },
			{ k: 'object_fit', l: 'Object fit', c: 'select', o: FIT },
			{ k: 'link', l: 'Link URL', c: 'url' },
			{ k: 'link_new_tab', l: 'Open link in new tab', c: 'bool' },
			{ k: 'align', l: 'Alignment', c: 'select', o: ALIGN }
		],
		button: [
			{ k: 'text', l: 'Button text', c: 'text' },
			{ k: 'url', l: 'Link URL', c: 'url' },
			{ k: 'link_new_tab', l: 'Open in new tab', c: 'bool' },
			{ k: 'bg', l: 'Background', c: 'color' },
			{ k: 'color', l: 'Text color', c: 'color' },
			{ k: 'hover_bg', l: 'Hover background', c: 'color' },
			{ k: 'hover_color', l: 'Hover text', c: 'color' },
			{ k: 'border_radius', l: 'Border radius', c: 'unit', ph: '6px' },
			{ k: 'padding', l: 'Padding', c: 'unit', ph: '15px 26px' },
			{ k: 'font_size', l: 'Font size', c: 'unit', ph: '0.85rem' },
			{ k: 'icon', l: 'Icon (text/emoji)', c: 'text' },
			{ k: 'icon_position', l: 'Icon position', c: 'select', o: ICONPOS },
			{ k: 'align', l: 'Alignment', c: 'select', o: ALIGN }
		],
		icon: [
			{ k: 'name', l: 'Icon (text/emoji)', c: 'text' },
			{ k: 'size', l: 'Size', c: 'unit', ph: '2rem' },
			{ k: 'color', l: 'Color', c: 'color' },
			{ k: 'link', l: 'Link URL', c: 'url' },
			{ k: 'align', l: 'Alignment', c: 'select', o: ALIGN }
		],
		section: [
			{ k: 'background_color', l: 'Background color', c: 'color' },
			{ k: 'background_image', l: 'Background image', c: 'media' },
			{ k: 'padding', l: 'Padding', c: 'unit', ph: '96px 0' },
			{ k: 'min_height', l: 'Min height', c: 'unit', ph: '60vh' },
			{ k: 'max_width', l: 'Content max width', c: 'unit', ph: '1300px' },
			{ k: 'content_align', l: 'Content alignment', c: 'select', o: ALIGN },
			{ k: 'hidden', l: 'Hide this section', c: 'bool' }
		]
	};

	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	var DEVLABEL = { desktop: 'Desktop', tablet: 'Tablet', mobile: 'Mobile' };

	/**
	 * Render controls for a node into `container`.
	 * node = { type, settings }; onChange(key, value, opts); device = active bp.
	 */
	function render( container, node, onChange, device ) {
		device = device || 'desktop';
		var defs = FIELDS[ node.type ] || [];
		container.innerHTML = '';
		defs.forEach( function ( def ) {
			container.appendChild( control( def, node, onChange, device ) );
		} );
	}

	function val( node, key ) {
		return El.baseVal( node.settings, key, '' );
	}

	function control( def, node, onChange, device ) {
		var wrap = document.createElement( 'label' );
		wrap.className = 'oss-lpb-field oss-lpb-field--' + def.c;
		var responsive = El.isResponsive( node.type, def.k );
		// Responsive fields edit the active breakpoint; others read the base.
		var current = responsive ? El.responsiveVal( node.settings, def.k, device ) : val( node, def.k );
		var inherited = responsive ? El.inheritedVal( node.settings, def.k, device ) : '';
		var placeholder = def.ph;
		if ( responsive && '' === current && inherited ) { placeholder = inherited + ' (inherited)'; }

		if ( 'bool' === def.c ) {
			wrap.classList.add( 'oss-lpb-field--inline' );
			var cb = document.createElement( 'input' );
			cb.type = 'checkbox';
			cb.checked = !! ( node.settings[ def.k ] );
			cb.addEventListener( 'change', function () { onChange( def.k, cb.checked ? 1 : '' ); } );
			wrap.appendChild( cb );
			wrap.appendChild( span( def.l ) );
			return wrap;
		}

		var lbl = span( def.l );
		if ( responsive ) {
			var badge = document.createElement( 'span' );
			badge.className = 'oss-lpb-devbadge';
			badge.textContent = DEVLABEL[ device ];
			lbl.appendChild( badge );
		}
		wrap.appendChild( lbl );

		if ( 'select' === def.c ) {
			var sel = document.createElement( 'select' );
			def.o.forEach( function ( o ) {
				var op = document.createElement( 'option' );
				op.value = o[ 0 ]; op.textContent = o[ 1 ];
				if ( String( current ) === String( o[ 0 ] ) ) { op.selected = true; }
				sel.appendChild( op );
			} );
			sel.addEventListener( 'change', function () { onChange( def.k, sel.value ); } );
			wrap.appendChild( sel );
			return wrap;
		}

		if ( 'textarea' === def.c ) {
			var ta = document.createElement( 'textarea' );
			ta.rows = 3; ta.value = current;
			ta.addEventListener( 'input', function () { onChange( def.k, ta.value, { live: true } ); } );
			wrap.appendChild( ta );
			return wrap;
		}

		if ( 'list' === def.c ) {
			var tl = document.createElement( 'textarea' );
			tl.rows = 4;
			tl.value = ( node.settings[ def.k ] && node.settings[ def.k ].join ) ? node.settings[ def.k ].join( '\n' ) : '';
			tl.addEventListener( 'input', function () {
				var items = tl.value.split( '\n' ).map( function ( s ) { return s.trim(); } ).filter( Boolean );
				onChange( def.k, items, { live: true, raw: true } );
			} );
			wrap.appendChild( tl );
			return wrap;
		}

		if ( 'color' === def.c ) {
			var row = document.createElement( 'span' );
			row.className = 'oss-lpb-color';
			var text = document.createElement( 'input' );
			text.type = 'text'; text.value = current; text.placeholder = '#RRGGBB or var(--token)';
			var sw = document.createElement( 'input' );
			sw.type = 'color';
			sw.value = /^#([0-9a-f]{6})$/i.test( current ) ? current : '#000000';
			text.addEventListener( 'input', function () { onChange( def.k, text.value, { live: true } ); } );
			sw.addEventListener( 'input', function () { text.value = sw.value; onChange( def.k, sw.value, { live: true } ); } );
			row.appendChild( text ); row.appendChild( sw );
			wrap.appendChild( row );

			// Global-color swatches: click to link this value to a site token so
			// changing the global later propagates here automatically.
			var G = window.OSSLPBGlobals;
			if ( G && G.get() && G.get().colors ) {
				var pal = document.createElement( 'span' );
				pal.className = 'oss-lpb-swatches';
				G.COLORS.forEach( function ( gc ) {
					var token = 'var(--site-' + gc.key + ')';
					var b = document.createElement( 'button' );
					b.type = 'button';
					b.className = 'oss-lpb-swatch' + ( current === token ? ' is-active' : '' );
					b.style.background = G.get().colors[ gc.key ] || '#fff';
					b.title = gc.label + ' (' + token + ')';
					b.addEventListener( 'click', function () {
						text.value = token;
						pal.querySelectorAll( '.oss-lpb-swatch' ).forEach( function ( x ) { x.classList.remove( 'is-active' ); } );
						b.classList.add( 'is-active' );
						onChange( def.k, token, { live: true } );
					} );
					pal.appendChild( b );
				} );
				wrap.appendChild( pal );
			}
			return wrap;
		}

		if ( 'media' === def.c ) {
			var mediaWrap = document.createElement( 'span' );
			mediaWrap.className = 'oss-lpb-media';
			var id = node.settings[ def.k ] ? parseInt( node.settings[ def.k ], 10 ) : 0;
			var prev = document.createElement( 'span' );
			prev.className = 'oss-lpb-media__prev';
			mediaWrap.appendChild( prev );
			var pick = document.createElement( 'button' );
			pick.type = 'button'; pick.className = 'oss-lpb-mini'; pick.textContent = id ? 'Change image' : 'Select image';
			var clear = document.createElement( 'button' );
			clear.type = 'button'; clear.className = 'oss-lpb-mini oss-lpb-mini--ghost'; clear.textContent = 'Remove';
			clear.style.display = id ? '' : 'none';
			pick.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var frame = wp.media( { title: 'Select Image', multiple: false, library: { type: 'image' } } );
				frame.on( 'select', function () {
					var att = frame.state().get( 'selection' ).first().toJSON();
					var url = att.sizes && att.sizes.large ? att.sizes.large.url : att.url;
					prev.style.backgroundImage = 'url(' + url + ')';
					pick.textContent = 'Change image';
					clear.style.display = '';
					onChange( def.k, att.id, { live: true, previewUrl: url } );
				} );
				frame.open();
			} );
			clear.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				prev.style.backgroundImage = '';
				pick.textContent = 'Select image';
				clear.style.display = 'none';
				onChange( def.k, '', { live: true } );
			} );
			mediaWrap.appendChild( pick ); mediaWrap.appendChild( clear );
			wrap.appendChild( mediaWrap );
			return wrap;
		}

		// text / unit / url
		var inp = document.createElement( 'input' );
		inp.type = ( 'url' === def.c ) ? 'url' : 'text';
		inp.value = current;
		if ( placeholder ) { inp.placeholder = placeholder; }
		inp.addEventListener( 'input', function () { onChange( def.k, inp.value, { live: true, device: responsive ? device : undefined } ); } );
		wrap.appendChild( inp );
		return wrap;
	}

	function span( text ) { var s = document.createElement( 'span' ); s.className = 'oss-lpb-field__label'; s.textContent = text; return s; }

	window.OSSLPBFields = { render: render };
} )();
