/**
 * Live Page Builder — canvas agent (runs inside the iframe).
 *
 * Phase 0: hover/select, floating type tag + toolbar, postMessage bridge.
 * Phase 1: applies live edits to the selected node without a reload (text,
 * styles, image, button, section), and supports inline text editing that
 * syncs back to the shell. Mirrors the PHP renderer's scoped-CSS approach via
 * a single live <style> block keyed by node id. Vanilla JS, no dependencies.
 */
( function () {
	'use strict';

	var selectedEl = null;
	var tag = null;
	var toolbar = null;
	var liveStyle = null;
	var liveRules = {}; // id -> css string

	/* ---- messaging ---- */
	function parentPost( msg ) {
		try { window.parent.postMessage( Object.assign( { source: 'oss-lpb-canvas' }, msg ), window.location.origin ); } catch ( e ) {}
	}

	function nodeInfo( el ) {
		if ( el.hasAttribute( 'data-live-element' ) ) {
			return { scope: 'element', id: el.getAttribute( 'data-live-element' ), elType: el.getAttribute( 'data-live-type' ) || 'element' };
		}
		return { scope: 'section', id: el.getAttribute( 'data-live-section' ), elType: 'section' };
	}

	/* ---- selection chrome ---- */
	function ensureChrome() {
		if ( ! tag ) {
			tag = document.createElement( 'div' );
			tag.className = 'oss-lpb-tag';
			document.body.appendChild( tag );
		}
		if ( ! toolbar ) {
			toolbar = document.createElement( 'div' );
			toolbar.className = 'oss-lpb-toolbar';
			toolbar.innerHTML =
				'<button data-act="duplicate">Duplicate</button>' +
				'<button data-act="move-up">↑</button>' +
				'<button data-act="move-down">↓</button>' +
				'<button data-act="delete">Delete</button>';
			toolbar.addEventListener( 'click', function ( e ) {
				var b = e.target.closest( 'button' );
				if ( ! b || ! selectedEl ) { return; }
				e.preventDefault();
				var info = nodeInfo( selectedEl );
				parentPost( { type: 'action', act: b.getAttribute( 'data-act' ), scope: info.scope, id: info.id, elType: info.elType } );
			} );
			document.body.appendChild( toolbar );
		}
	}

	function positionChrome() {
		if ( ! selectedEl ) { return; }
		var r = selectedEl.getBoundingClientRect();
		var top = r.top + window.scrollY;
		var left = r.left + window.scrollX;
		var info = nodeInfo( selectedEl );
		tag.textContent = info.elType;
		tag.className = 'oss-lpb-tag' + ( 'section' === info.scope ? ' oss-lpb-tag--section' : '' );
		tag.style.top = top + 'px';
		tag.style.left = left + 'px';
		tag.style.display = 'block';
		toolbar.style.top = top + 'px';
		toolbar.style.left = ( left + Math.max( 0, r.width - 210 ) ) + 'px';
		toolbar.style.display = 'flex';
	}

	function elById( scope, id ) {
		return document.querySelector( 'section' === scope ? '[data-live-section="' + css( id ) + '"]' : '[data-live-element="' + css( id ) + '"]' );
	}
	function css( s ) { return String( s ).replace( /"/g, '' ); }

	function select( el ) {
		if ( selectedEl ) { selectedEl.classList.remove( 'oss-lpb-selected' ); disableInline( selectedEl ); }
		selectedEl = el;
		if ( ! el ) { if ( tag ) tag.style.display = 'none'; if ( toolbar ) toolbar.style.display = 'none'; parentPost( { type: 'deselect' } ); return; }
		ensureChrome();
		el.classList.add( 'oss-lpb-selected' );
		positionChrome();
		var info = nodeInfo( el );
		parentPost( { type: 'select', scope: info.scope, id: info.id, elType: info.elType } );
	}

	function findEditable( target ) {
		return target.closest( '[data-live-element]' ) || target.closest( '[data-live-section]' );
	}

	document.addEventListener( 'mouseover', function ( e ) {
		var el = findEditable( e.target );
		if ( el && el !== selectedEl ) { el.classList.add( 'oss-lpb-hover' ); }
	} );
	document.addEventListener( 'mouseout', function ( e ) {
		var el = findEditable( e.target );
		if ( el ) { el.classList.remove( 'oss-lpb-hover' ); }
	} );

	document.addEventListener( 'click', function ( e ) {
		if ( e.target.isContentEditable ) { return; }
		var el = findEditable( e.target );
		if ( el ) {
			e.preventDefault();
			e.stopPropagation();
			select( e.target.closest( '[data-live-element]' ) || el );
		} else {
			select( null );
		}
	}, true );

	/* Double-click a text element to edit its text inline. */
	document.addEventListener( 'dblclick', function ( e ) {
		var el = e.target.closest( '[data-live-element]' );
		if ( ! el ) { return; }
		var type = el.getAttribute( 'data-live-type' );
		if ( 'heading' === type || 'paragraph' === type || 'button' === type ) {
			e.preventDefault();
			enableInline( el, type );
		}
	} );

	function inlineTarget( el, type ) {
		if ( 'button' === type ) { return el.querySelector( '.oss-lpb-btn' ); }
		return el.querySelector( 'h1,h2,h3,h4,h5,h6,p' ) || el;
	}
	function enableInline( el, type ) {
		var t = inlineTarget( el, type );
		if ( ! t ) { return; }
		t.setAttribute( 'contenteditable', 'true' );
		t.focus();
		document.execCommand && document.execCommand( 'selectAll', false, null );
		t.oninput = function () {
			parentPost( { type: 'text-input', id: el.getAttribute( 'data-live-element' ), text: t.textContent } );
			positionChrome();
		};
		t.onblur = function () {
			parentPost( { type: 'text-commit', id: el.getAttribute( 'data-live-element' ), text: t.textContent } );
			t.removeAttribute( 'contenteditable' );
			t.oninput = t.onblur = null;
		};
	}
	function disableInline( el ) {
		if ( ! el ) { return; }
		el.querySelectorAll( '[contenteditable="true"]' ).forEach( function ( n ) { n.removeAttribute( 'contenteditable' ); n.oninput = n.onblur = null; } );
	}

	window.addEventListener( 'scroll', positionChrome, { passive: true } );
	window.addEventListener( 'resize', positionChrome );

	/* ---- live style block ---- */
	function ensureLiveStyle() {
		if ( ! liveStyle ) {
			liveStyle = document.createElement( 'style' );
			liveStyle.id = 'oss-lpb-live-css';
			document.head.appendChild( liveStyle );
		}
	}
	function renderLive() {
		ensureLiveStyle();
		var out = '';
		for ( var id in liveRules ) { if ( liveRules[ id ] ) { out += liveRules[ id ]; } }
		liveStyle.textContent = out;
	}

	function baseVal( s, key, def ) {
		if ( ! s || ! ( key in s ) ) { return def === undefined ? '' : def; }
		var v = s[ key ];
		if ( v && typeof v === 'object' && ! Array.isArray( v ) ) { return 'desktop' in v ? v.desktop : ( def || '' ); }
		return v;
	}

	function cssForElement( id, type, s ) {
		var e = '[data-live-element="' + css( id ) + '"]';
		var out = '';
		var add = function ( sel, prop, val ) { if ( val !== '' && val != null ) { out += sel + '{' + prop + ':' + val + '}'; } };
		add( e + ' *,' + e, 'color', baseVal( s, 'color' ) );
		add( e + ' *', 'font-family', baseVal( s, 'font_family' ) );
		add( e + ' >*', 'font-size', baseVal( s, 'font_size' ) );
		add( e + ' >*', 'font-weight', baseVal( s, 'font_weight' ) );
		add( e + ' >*', 'line-height', baseVal( s, 'line_height' ) );
		add( e + ' >*', 'letter-spacing', baseVal( s, 'letter_spacing' ) );
		add( e, 'text-align', baseVal( s, 'align' ) );
		add( e + ' >*', 'text-transform', baseVal( s, 'text_transform' ) );
		if ( 'image' === type ) {
			add( e + ' img', 'width', baseVal( s, 'width' ) );
			add( e + ' img', 'height', baseVal( s, 'height' ) );
			add( e + ' img', 'object-fit', baseVal( s, 'object_fit' ) );
			add( e + ' img', 'border-radius', baseVal( s, 'radius' ) );
		}
		if ( 'button' === type ) {
			add( e + ' .oss-lpb-btn', 'background', baseVal( s, 'bg' ) );
			add( e + ' .oss-lpb-btn', 'color', baseVal( s, 'color' ) );
			add( e + ' .oss-lpb-btn', 'border-radius', baseVal( s, 'border_radius' ) );
			add( e + ' .oss-lpb-btn', 'padding', baseVal( s, 'padding' ) );
			add( e + ' .oss-lpb-btn', 'font-size', baseVal( s, 'font_size' ) );
			add( e + ' .oss-lpb-btn', 'font-weight', baseVal( s, 'font_weight' ) );
		}
		if ( 'icon' === type ) {
			add( e + ' .oss-lpb-icon', 'font-size', baseVal( s, 'size' ) );
			add( e + ' .oss-lpb-icon', 'color', baseVal( s, 'color' ) );
		}
		return out;
	}

	function esc( str ) { return String( str ).replace( /[&<>]/g, function ( c ) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[ c ]; } ); }

	/* ---- apply live edits ---- */
	function applyElement( id, type, s ) {
		var wrap = elById( 'element', id );
		if ( ! wrap ) { return; }

		if ( 'heading' === type ) {
			var lvl = baseVal( s, 'level', 'h2' );
			var cur = wrap.querySelector( 'h1,h2,h3,h4,h5,h6' );
			if ( cur && cur.tagName.toLowerCase() !== lvl ) {
				var nh = document.createElement( lvl );
				nh.textContent = baseVal( s, 'text' );
				cur.replaceWith( nh );
			} else if ( cur ) {
				cur.textContent = baseVal( s, 'text' );
			}
		} else if ( 'paragraph' === type ) {
			var p = wrap.querySelector( 'p' );
			if ( p ) { p.innerHTML = esc( baseVal( s, 'text' ) ).replace( /\n/g, '<br>' ); }
		} else if ( 'button' === type ) {
			var a = wrap.querySelector( '.oss-lpb-btn' );
			if ( a ) {
				var icon = baseVal( s, 'icon' );
				var ico = icon ? '<span class="oss-lpb-btn__icon">' + esc( icon ) + '</span>' : '';
				var label = esc( baseVal( s, 'text', 'Button' ) );
				a.innerHTML = ( 'right' === baseVal( s, 'icon_position', 'left' ) ) ? label + ico : ico + label;
				a.setAttribute( 'href', baseVal( s, 'url', '#' ) || '#' );
				if ( s.link_new_tab ) { a.setAttribute( 'target', '_blank' ); a.setAttribute( 'rel', 'noopener' ); }
				else { a.removeAttribute( 'target' ); a.removeAttribute( 'rel' ); }
			}
		} else if ( 'image' === type ) {
			applyImage( wrap, s );
		} else if ( 'richtext' === type ) {
			var rt = wrap.querySelector( '.oss-lpb-rich' );
			if ( rt ) { rt.innerHTML = baseVal( s, 'html' ); }
		} else if ( 'list' === type ) {
			applyList( wrap, s );
		} else if ( 'icon' === type ) {
			var ic = wrap.querySelector( '.oss-lpb-icon' );
			if ( ic ) { ic.textContent = baseVal( s, 'name' ); }
		}

		liveRules[ id ] = cssForElement( id, type, s );
		renderLive();
		positionChrome();
	}

	function applyImage( wrap, s ) {
		var url = s._preview || baseVal( s, 'url' );
		var img = wrap.querySelector( 'img' );
		if ( ! url ) { return; }
		if ( ! img ) {
			wrap.innerHTML = '';
			img = document.createElement( 'img' );
			wrap.appendChild( img );
		}
		img.setAttribute( 'src', url );
		img.setAttribute( 'alt', baseVal( s, 'alt' ) );
		// Link wrap.
		var link = baseVal( s, 'link' );
		var parentA = img.closest( 'a' );
		if ( link ) {
			if ( ! parentA ) { parentA = document.createElement( 'a' ); img.replaceWith( parentA ); parentA.appendChild( img ); }
			parentA.setAttribute( 'href', link );
			if ( s.link_new_tab ) { parentA.setAttribute( 'target', '_blank' ); parentA.setAttribute( 'rel', 'noopener' ); }
			else { parentA.removeAttribute( 'target' ); parentA.removeAttribute( 'rel' ); }
		} else if ( parentA ) {
			parentA.replaceWith( img );
		}
	}

	function applyList( wrap, s ) {
		var ul = wrap.querySelector( 'ul' );
		if ( ! ul ) { return; }
		ul.className = 'oss-lpb-list oss-lpb-list--' + ( baseVal( s, 'style', 'disc' ) );
		var items = ( s.items && s.items.length ) ? s.items : [];
		ul.innerHTML = items.map( function ( i ) { return '<li>' + esc( i ) + '</li>'; } ).join( '' );
	}

	function applySection( id, s ) {
		var sec = elById( 'section', id );
		if ( ! sec ) { return; }
		sec.style.backgroundColor = baseVal( s, 'background_color' ) || '';
		if ( s._bg_preview ) { sec.style.backgroundImage = 'url(' + s._bg_preview + ')'; sec.style.backgroundSize = 'cover'; sec.style.backgroundPosition = 'center'; }
		else if ( ! s.background_image ) { sec.style.backgroundImage = ''; }
		var inner = sec.querySelector( '.oss-lpb-section__inner' );
		if ( inner ) {
			inner.style.textAlign = baseVal( s, 'content_align' ) || '';
			var mx = baseVal( s, 'max_width' );
			inner.style.maxWidth = mx || '';
			inner.style.marginLeft = mx ? 'auto' : '';
			inner.style.marginRight = mx ? 'auto' : '';
		}
		sec.style.display = s.hidden ? 'none' : '';
		var sel = '[data-live-section="' + css( id ) + '"]';
		var out = '';
		var pad = baseVal( s, 'padding' ); if ( pad ) { out += sel + '{padding:' + pad + '}'; }
		var mh = baseVal( s, 'min_height' ); if ( mh ) { out += sel + '{min-height:' + mh + '}'; }
		liveRules[ id ] = out;
		renderLive();
		positionChrome();
	}

	/* ---- messages from the shell ---- */
	window.addEventListener( 'message', function ( e ) {
		if ( e.origin !== window.location.origin || ! e.data || 'oss-lpb-shell' !== e.data.source ) { return; }
		var m = e.data;
		if ( 'reposition' === m.type ) { positionChrome(); }
		else if ( 'deselect' === m.type ) { select( null ); }
		else if ( 'apply' === m.type ) {
			if ( 'section' === m.scope ) { applySection( m.id, m.settings ); }
			else { applyElement( m.id, m.elType, m.settings ); }
		}
		else if ( 'select-node' === m.type ) {
			var node = elById( m.scope, m.id );
			if ( node ) { select( node ); node.scrollIntoView( { block: 'center', behavior: 'smooth' } ); }
		}
	} );

	parentPost( { type: 'ready' } );
} )();
