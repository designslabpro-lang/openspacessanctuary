<?php
/**
 * Live Page Builder — server-side renderer.
 *
 * Turns a sanitized document tree into semantic HTML that reuses the theme's
 * existing design tokens and .oss2-* look, plus a scoped <style> block for
 * per-element (and per-breakpoint) values. Every node carries a stable
 * data-live-section / data-live-element id so the editor can bind to it, but
 * the markup is plain HTML — visitors need no builder JS to view it.
 */

defined( 'ABSPATH' ) || exit;

/** Editing breakpoints (match the theme's responsive.css). */
function oss_lpb_breakpoints() {
	return array( 'tablet' => 900, 'mobile' => 600 );
}

/**
 * Read a page's builder document, falling back to a starter so a freshly
 * opted-in page always has something to click.
 */
function oss_lpb_get_document( $post_id ) {
	$doc = get_post_meta( (int) $post_id, OSS_LPB_META, true );
	if ( is_array( $doc ) && $doc ) {
		return $doc;
	}
	return oss_lpb_starter_document();
}

/**
 * The default document a new builder page starts from.
 */
function oss_lpb_starter_document() {
	return array(
		array(
			'id'       => 'sec_hero0001',
			'type'     => 'section',
			'settings' => array(
				'background_color' => 'var(--oss-primary-dark)',
				'padding'          => array( 'desktop' => '96px 0', 'mobile' => '56px 0' ),
				'content_align'    => 'center',
			),
			'elements' => array(
				array(
					'id'       => 'el_eyebrow01',
					'type'     => 'heading',
					'settings' => array(
						'text'           => 'Healing Begins Here',
						'level'          => 'h6',
						'color'          => 'var(--oss-gold-light)',
						'align'          => 'center',
						'text_transform' => 'uppercase',
						'letter_spacing' => '0.18em',
					),
				),
				array(
					'id'       => 'el_headline1',
					'type'     => 'heading',
					'settings' => array(
						'text'      => 'A Place Where People Grow and Heal',
						'level'     => 'h1',
						'color'     => 'var(--oss-bg)',
						'align'     => 'center',
						'font_size' => array( 'desktop' => '3.4rem', 'mobile' => '2.2rem' ),
					),
				),
				array(
					'id'       => 'el_intro0001',
					'type'     => 'paragraph',
					'settings' => array(
						'text'  => 'Transformative equine-assisted experiences in the peaceful countryside of Ocala, Florida.',
						'color' => 'rgba(245,240,230,0.88)',
						'align' => 'center',
					),
				),
				array(
					'id'       => 'el_cta00001',
					'type'     => 'button',
					'settings' => array(
						'text'          => 'Book Appointment',
						'url'           => '/contact/',
						'bg'            => 'var(--oss-gold)',
						'color'         => 'var(--oss-white)',
						'hover_bg'      => 'var(--oss-primary)',
						'border_radius' => '6px',
						'align'         => 'center',
					),
				),
			),
		),
	);
}

/**
 * Render the whole document (styles + sections) for the front end / canvas.
 */
function oss_lpb_render_document( $doc ) {
	if ( ! is_array( $doc ) || ! $doc ) {
		return '';
	}
	$html  = oss_lpb_render_styles( $doc );
	$html .= '<div class="oss-lpb-doc">';
	foreach ( $doc as $section ) {
		$html .= oss_lpb_render_section( $section );
	}
	$html .= '</div>';
	return $html;
}

/**
 * Resolve a setting's base (desktop) value whether it is scalar or responsive.
 */
function oss_lpb_base_value( $settings, $key, $default = '' ) {
	if ( ! isset( $settings[ $key ] ) ) {
		return $default;
	}
	$v = $settings[ $key ];
	if ( is_array( $v ) ) {
		return isset( $v['desktop'] ) ? $v['desktop'] : $default;
	}
	return $v;
}

function oss_lpb_render_section( $section ) {
	$id       = isset( $section['id'] ) ? $section['id'] : '';
	$settings = isset( $section['settings'] ) ? $section['settings'] : array();
	if ( ! empty( $settings['hidden'] ) ) {
		return '';
	}
	$style = '';
	$bg    = oss_lpb_base_value( $settings, 'background_color' );
	if ( $bg ) {
		$style .= 'background-color:' . esc_attr( $bg ) . ';';
	}
	if ( ! empty( $settings['background_image'] ) ) {
		$url = wp_get_attachment_image_url( (int) $settings['background_image'], 'full' );
		if ( $url ) {
			$style .= 'background-image:url(' . esc_url( $url ) . ');background-size:cover;background-position:center;';
		}
	}
	$align = oss_lpb_base_value( $settings, 'content_align' );

	$inner_style = '';
	$max = oss_lpb_base_value( $settings, 'max_width' );
	if ( $max ) {
		$inner_style .= 'max-width:' . esc_attr( $max ) . ';margin-left:auto;margin-right:auto;';
	}
	if ( $align ) {
		$inner_style .= 'text-align:' . esc_attr( $align ) . ';';
	}

	$html  = '<section class="oss-lpb-section" data-live-section="' . esc_attr( $id ) . '"';
	$html .= $style ? ' style="' . esc_attr( $style ) . '"' : '';
	$html .= '>';
	$html .= '<div class="oss-container oss-lpb-section__inner"' . ( $inner_style ? ' style="' . esc_attr( $inner_style ) . '"' : '' ) . '>';
	foreach ( ( isset( $section['elements'] ) ? $section['elements'] : array() ) as $el ) {
		$html .= oss_lpb_render_element( $el );
	}
	$html .= '</div></section>';
	return $html;
}

function oss_lpb_render_element( $el ) {
	$type = isset( $el['type'] ) ? $el['type'] : '';
	$fn   = 'oss_lpb_render_' . $type;
	if ( ! function_exists( $fn ) ) {
		return '';
	}
	$id       = isset( $el['id'] ) ? $el['id'] : '';
	$settings = isset( $el['settings'] ) ? $el['settings'] : array();
	$inner    = call_user_func( $fn, $id, $settings );
	return '<div class="oss-lpb-el oss-lpb-el--' . esc_attr( $type ) . '" data-live-element="' . esc_attr( $id ) . '" data-live-type="' . esc_attr( $type ) . '">' . $inner . '</div>';
}

function oss_lpb_render_heading( $id, $s ) {
	$levels = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
	$tag    = in_array( oss_lpb_base_value( $s, 'level', 'h2' ), $levels, true ) ? oss_lpb_base_value( $s, 'level', 'h2' ) : 'h2';
	return '<' . $tag . '>' . esc_html( oss_lpb_base_value( $s, 'text' ) ) . '</' . $tag . '>';
}

function oss_lpb_render_paragraph( $id, $s ) {
	return '<p>' . nl2br( esc_html( oss_lpb_base_value( $s, 'text' ) ) ) . '</p>';
}

function oss_lpb_render_richtext( $id, $s ) {
	return '<div class="oss-lpb-rich">' . wp_kses_post( oss_lpb_base_value( $s, 'html' ) ) . '</div>';
}

function oss_lpb_render_list( $id, $s ) {
	$items = isset( $s['items'] ) && is_array( $s['items'] ) ? $s['items'] : array();
	$style = oss_lpb_base_value( $s, 'style', 'disc' );
	$out   = '<ul class="oss-lpb-list oss-lpb-list--' . esc_attr( $style ) . '">';
	foreach ( $items as $item ) {
		$out .= '<li>' . esc_html( $item ) . '</li>';
	}
	return $out . '</ul>';
}

function oss_lpb_render_image( $id, $s ) {
	$att_id = (int) oss_lpb_base_value( $s, 'id' );
	$url    = oss_lpb_base_value( $s, 'url' );
	if ( $att_id && wp_get_attachment_image_src( $att_id, 'large' ) ) {
		$url = wp_get_attachment_image_url( $att_id, 'large' );
	}
	if ( ! $url ) {
		return '<div class="oss-image-placeholder" aria-hidden="true"></div>';
	}
	$img = '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( oss_lpb_base_value( $s, 'alt' ) ) . '" loading="lazy">';
	$link = oss_lpb_base_value( $s, 'link' );
	if ( $link ) {
		$tab = ! empty( $s['link_new_tab'] ) ? ' target="_blank" rel="noopener"' : '';
		$img = '<a href="' . esc_url( $link ) . '"' . $tab . '>' . $img . '</a>';
	}
	return $img;
}

function oss_lpb_render_button( $id, $s ) {
	$tab = ! empty( $s['link_new_tab'] ) ? ' target="_blank" rel="noopener"' : '';
	$icon = oss_lpb_base_value( $s, 'icon' );
	$pos  = oss_lpb_base_value( $s, 'icon_position', 'left' );
	$label = esc_html( oss_lpb_base_value( $s, 'text', 'Button' ) );
	$ico   = $icon ? '<span class="oss-lpb-btn__icon">' . esc_html( $icon ) . '</span>' : '';
	$inner = ( 'left' === $pos ) ? $ico . $label : $label . $ico;
	return '<a class="oss-lpb-btn" href="' . esc_url( oss_lpb_base_value( $s, 'url', '#' ) ) . '"' . $tab . '>' . $inner . '</a>';
}

function oss_lpb_render_icon( $id, $s ) {
	$name = esc_html( oss_lpb_base_value( $s, 'name', '' ) );
	$span = '<span class="oss-lpb-icon">' . $name . '</span>';
	$link = oss_lpb_base_value( $s, 'link' );
	return $link ? '<a href="' . esc_url( $link ) . '">' . $span . '</a>' : $span;
}

/* -------------------------------------------------------------------- */
/* Scoped CSS generation                                                 */
/* -------------------------------------------------------------------- */

/**
 * Build a <style> block scoped by node id, including per-breakpoint media
 * queries, so responsive values don't require inline styles.
 */
function oss_lpb_render_styles( $doc ) {
	$bp   = oss_lpb_breakpoints();
	$base = '';
	$rules = array( 'tablet' => '', 'mobile' => '' );

	$emit = function ( $selector, $prop, $setting ) use ( &$base, &$rules ) {
		if ( is_array( $setting ) ) {
			foreach ( array( 'desktop', 'tablet', 'mobile' ) as $b ) {
				if ( isset( $setting[ $b ] ) && '' !== $setting[ $b ] ) {
					$line = $selector . '{' . $prop . ':' . $setting[ $b ] . ';}';
					if ( 'desktop' === $b ) { $base .= $line; } else { $rules[ $b ] .= $line; }
				}
			}
		} elseif ( '' !== $setting && null !== $setting ) {
			$base .= $selector . '{' . $prop . ':' . $setting . ';}';
		}
	};

	foreach ( $doc as $section ) {
		$sid = isset( $section['id'] ) ? $section['id'] : '';
		$ss  = isset( $section['settings'] ) ? $section['settings'] : array();
		$sel = '[data-live-section="' . $sid . '"]';
		if ( isset( $ss['padding'] ) ) { $emit( $sel, 'padding', $ss['padding'] ); }
		if ( isset( $ss['min_height'] ) ) { $emit( $sel, 'min-height', $ss['min_height'] ); }

		foreach ( ( isset( $section['elements'] ) ? $section['elements'] : array() ) as $el ) {
			$eid = isset( $el['id'] ) ? $el['id'] : '';
			$es  = isset( $el['settings'] ) ? $el['settings'] : array();
			$e   = '[data-live-element="' . $eid . '"]';
			$type = isset( $el['type'] ) ? $el['type'] : '';

			if ( isset( $es['color'] ) )          { $emit( $e . ' *,' . $e, 'color', $es['color'] ); }
			if ( isset( $es['font_family'] ) )    { $emit( $e . ' *', 'font-family', $es['font_family'] ); }
			if ( isset( $es['font_size'] ) )      { $emit( $e . ' >*', 'font-size', $es['font_size'] ); }
			if ( isset( $es['font_weight'] ) )    { $emit( $e . ' >*', 'font-weight', $es['font_weight'] ); }
			if ( isset( $es['line_height'] ) )    { $emit( $e . ' >*', 'line-height', $es['line_height'] ); }
			if ( isset( $es['letter_spacing'] ) ) { $emit( $e . ' >*', 'letter-spacing', $es['letter_spacing'] ); }
			if ( isset( $es['align'] ) )          { $emit( $e, 'text-align', $es['align'] ); }
			if ( isset( $es['text_transform'] ) ) { $emit( $e . ' >*', 'text-transform', $es['text_transform'] ); }

			if ( 'image' === $type ) {
				if ( isset( $es['width'] ) )      { $emit( $e . ' img', 'width', $es['width'] ); }
				if ( isset( $es['height'] ) )     { $emit( $e . ' img', 'height', $es['height'] ); }
				if ( isset( $es['object_fit'] ) ) { $emit( $e . ' img', 'object-fit', $es['object_fit'] ); }
				if ( isset( $es['radius'] ) )     { $emit( $e . ' img', 'border-radius', $es['radius'] ); }
			}
			if ( 'button' === $type ) {
				if ( isset( $es['bg'] ) )            { $emit( $e . ' .oss-lpb-btn', 'background', $es['bg'] ); }
				if ( isset( $es['color'] ) )         { $emit( $e . ' .oss-lpb-btn', 'color', $es['color'] ); }
				if ( isset( $es['border_radius'] ) ) { $emit( $e . ' .oss-lpb-btn', 'border-radius', $es['border_radius'] ); }
				if ( isset( $es['padding'] ) )       { $emit( $e . ' .oss-lpb-btn', 'padding', $es['padding'] ); }
				if ( isset( $es['font_size'] ) )     { $emit( $e . ' .oss-lpb-btn', 'font-size', $es['font_size'] ); }
				if ( isset( $es['font_weight'] ) )   { $emit( $e . ' .oss-lpb-btn', 'font-weight', $es['font_weight'] ); }
			}
			if ( 'icon' === $type ) {
				if ( isset( $es['size'] ) )  { $emit( $e . ' .oss-lpb-icon', 'font-size', $es['size'] ); }
				if ( isset( $es['color'] ) ) { $emit( $e . ' .oss-lpb-icon', 'color', $es['color'] ); }
			}
		}
	}

	$css = $base;
	if ( $rules['tablet'] ) { $css .= '@media(max-width:' . $bp['tablet'] . 'px){' . $rules['tablet'] . '}'; }
	if ( $rules['mobile'] ) { $css .= '@media(max-width:' . $bp['mobile'] . 'px){' . $rules['mobile'] . '}'; }

	return $css ? '<style id="oss-lpb-doc-css">' . $css . '</style>' : '';
}

/**
 * Read the site-wide global colors + typography, merged over defaults.
 */
function oss_lpb_get_globals() {
	$saved = get_option( OSS_LPB_GLOBALS_OPTION, array() );
	return oss_lpb_sanitize_globals( is_array( $saved ) ? $saved : array() );
}

/**
 * Map the globals model to CSS custom properties on :root, then a small set of
 * low-specificity :where() rules so linked elements inherit them while any
 * per-element setting (Phase 1) still wins. Returns raw CSS (no <style> tag).
 */
function oss_lpb_globals_css_raw( $g = null ) {
	if ( null === $g ) { $g = oss_lpb_get_globals(); }
	$c = $g['colors'];
	$f = $g['fonts'];
	$t = $g['tags'];

	$vars  = '';
	$vars .= '--site-primary:' . $c['primary'] . ';';
	$vars .= '--site-secondary:' . $c['secondary'] . ';';
	$vars .= '--site-accent:' . $c['accent'] . ';';
	$vars .= '--site-heading:' . $c['heading'] . ';';
	$vars .= '--site-body:' . $c['body'] . ';';
	$vars .= '--site-background:' . $c['background'] . ';';
	$vars .= '--site-button:' . $c['button'] . ';';
	$vars .= '--site-font-heading:' . $f['heading'] . ';';
	$vars .= '--site-font-body:' . $f['body'] . ';';
	foreach ( $t as $tag => $p ) {
		$vars .= '--site-' . $tag . '-size:' . $p['size'] . ';';
		$vars .= '--site-' . $tag . '-weight:' . $p['weight'] . ';';
		$vars .= '--site-' . $tag . '-lh:' . $p['line_height'] . ';';
	}

	$css  = ':root{' . $vars . '}';
	$css .= ':where(.oss-lpb-doc){font-family:var(--site-font-body);color:var(--site-body);font-size:var(--site-body-size);line-height:var(--site-body-lh);}';
	foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $h ) {
		$css .= ':where(.oss-lpb-doc ' . $h . '){font-family:var(--site-font-heading);color:var(--site-heading);'
			. 'font-size:var(--site-' . $h . '-size);font-weight:var(--site-' . $h . '-weight);line-height:var(--site-' . $h . '-lh);}';
	}
	$css .= ':where(.oss-lpb-doc p),:where(.oss-lpb-doc li){font-size:var(--site-body-size);line-height:var(--site-body-lh);}';
	$css .= ':where(.oss-lpb-doc .oss-lpb-btn){font-family:var(--site-font-body);font-size:var(--site-button-size);font-weight:var(--site-button-weight);line-height:var(--site-button-lh);}';
	return $css;
}

/**
 * The globals CSS wrapped in a <style> tag for printing on builder pages.
 */
function oss_lpb_globals_css( $g = null ) {
	return '<style id="oss-lpb-globals-css">' . oss_lpb_globals_css_raw( $g ) . '</style>';
}
