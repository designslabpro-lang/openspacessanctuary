<?php
/**
 * Live Page Builder — security, element schema, and sanitization.
 *
 * The builder document is a generic tree stored in post meta. NOTHING is
 * ever rendered or saved that is not described by the schema below: unknown
 * element types are dropped, unknown settings are dropped, and every setting
 * value is sanitized by a whitelisted type. This is the single source of
 * truth the REST layer and the renderer both trust.
 */

defined( 'ABSPATH' ) || exit;

define( 'OSS_LPB_META', '_oss_lpb' );
define( 'OSS_LPB_AUTOSAVE_META', '_oss_lpb_autosave' );
define( 'OSS_LPB_GLOBALS_OPTION', 'oss_lpb_globals' );

/**
 * Capability required to use the builder on a page.
 */
function oss_lpb_capability() {
	return 'edit_pages';
}

/**
 * Global styles are site-wide, so they need the higher theme-options cap.
 */
function oss_lpb_user_can_globals() {
	return current_user_can( 'edit_theme_options' );
}

/**
 * Global colors + typography defaults (seeded from the theme's own tokens).
 */
function oss_lpb_default_globals() {
	return array(
		'colors' => array(
			'primary'    => '#596B58',
			'secondary'  => '#755B45',
			'accent'     => '#B49A68',
			'heading'    => '#353A35',
			'body'       => '#755B45',
			'background' => '#F5F0E6',
			'button'     => '#596B58',
		),
		'fonts'  => array(
			'heading' => "'Playfair Display', Georgia, serif",
			'body'    => "'Montserrat', -apple-system, sans-serif",
		),
		'tags'   => array(
			'h1'     => array( 'size' => '3rem',    'weight' => '600', 'line_height' => '1.15' ),
			'h2'     => array( 'size' => '2.4rem',  'weight' => '600', 'line_height' => '1.2' ),
			'h3'     => array( 'size' => '1.8rem',  'weight' => '600', 'line_height' => '1.3' ),
			'h4'     => array( 'size' => '1.4rem',  'weight' => '600', 'line_height' => '1.3' ),
			'h5'     => array( 'size' => '1.15rem', 'weight' => '600', 'line_height' => '1.4' ),
			'h6'     => array( 'size' => '0.85rem', 'weight' => '700', 'line_height' => '1.4' ),
			'body'   => array( 'size' => '1rem',    'weight' => '400', 'line_height' => '1.7' ),
			'button' => array( 'size' => '0.85rem', 'weight' => '600', 'line_height' => '1' ),
		),
	);
}

/**
 * Sanitize a globals payload against the default shape.
 */
function oss_lpb_sanitize_globals( $raw ) {
	$def = oss_lpb_default_globals();
	if ( ! is_array( $raw ) ) { return $def; }
	$out = array( 'colors' => array(), 'fonts' => array(), 'tags' => array() );

	foreach ( $def['colors'] as $k => $d ) {
		$v = isset( $raw['colors'][ $k ] ) ? oss_lpb_sanitize_color( (string) $raw['colors'][ $k ] ) : '';
		$out['colors'][ $k ] = $v ? $v : $d;
	}
	foreach ( $def['fonts'] as $k => $d ) {
		$v = isset( $raw['fonts'][ $k ] ) ? sanitize_text_field( wp_unslash( (string) $raw['fonts'][ $k ] ) ) : '';
		$out['fonts'][ $k ] = $v ? $v : $d;
	}
	foreach ( $def['tags'] as $tag => $props ) {
		foreach ( $props as $p => $d ) {
			$rawv = isset( $raw['tags'][ $tag ][ $p ] ) ? (string) $raw['tags'][ $tag ][ $p ] : '';
			if ( 'weight' === $p ) {
				$v = preg_match( '/^[1-9]00$|^(normal|bold|lighter|bolder)$/', trim( $rawv ) ) ? trim( $rawv ) : '';
			} else {
				$v = oss_lpb_sanitize_unit( $rawv );
			}
			$out['tags'][ $tag ][ $p ] = ( '' !== $v ) ? $v : $d;
		}
	}
	return $out;
}

/**
 * Whether the current user may edit a given post's builder document.
 */
function oss_lpb_user_can( $post_id ) {
	$post_id = (int) $post_id;
	if ( ! $post_id ) {
		return false;
	}
	return current_user_can( oss_lpb_capability() ) && current_user_can( 'edit_post', $post_id );
}

/**
 * Wrap a base sanitizer spec so a setting accepts a per-breakpoint
 * { desktop, tablet, mobile } value.
 */
function oss_lpb_resp( $of ) {
	return array( 'responsive' => true, 'of' => $of );
}

/**
 * Element schema: type => ( setting => sanitizer spec ).
 * A sanitizer spec is one of: a keyword string, an array (=> allow-list),
 * or oss_lpb_resp(<spec>) for a per-breakpoint {desktop,tablet,mobile} value.
 */
function oss_lpb_schema() {
	static $schema = null;
	if ( null !== $schema ) {
		return $schema;
	}
	$schema = array(
		'section'   => array(
			'background_color' => 'color',
			'background_image' => 'int',
			'padding'          => oss_lpb_resp( 'unit' ),
			'min_height'       => oss_lpb_resp( 'unit' ),
			'max_width'        => 'unit',
			'content_align'    => array( 'left', 'center', 'right' ),
			'hidden'           => 'bool',
		),
		'heading'   => array(
			'text'           => 'text',
			'level'          => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ),
			'color'          => 'color',
			'font_family'    => 'text',
			'font_size'      => oss_lpb_resp( 'unit' ),
			'font_weight'    => 'text',
			'line_height'    => oss_lpb_resp( 'unit' ),
			'letter_spacing' => 'unit',
			'align'          => array( 'left', 'center', 'right' ),
			'text_transform' => array( 'none', 'uppercase', 'capitalize', 'lowercase' ),
		),
		'paragraph' => array(
			'text'        => 'textarea',
			'color'       => 'color',
			'font_family' => 'text',
			'font_size'   => oss_lpb_resp( 'unit' ),
			'font_weight' => 'text',
			'line_height' => oss_lpb_resp( 'unit' ),
			'align'       => array( 'left', 'center', 'right' ),
		),
		'richtext'  => array(
			'html'  => 'html',
			'color' => 'color',
			'align' => array( 'left', 'center', 'right' ),
		),
		'list'      => array(
			'items' => 'text_list',
			'style' => array( 'disc', 'check', 'none' ),
			'color' => 'color',
		),
		'image'     => array(
			'id'           => 'int',
			'url'          => 'url',
			'alt'          => 'text',
			'width'        => oss_lpb_resp( 'unit' ),
			'height'       => oss_lpb_resp( 'unit' ),
			'object_fit'   => array( 'cover', 'contain', 'fill', 'none' ),
			'radius'       => 'unit',
			'shadow'       => array( 'none', 'sm', 'md', 'lg' ),
			'link'         => 'url',
			'link_new_tab' => 'bool',
			'align'        => array( 'left', 'center', 'right' ),
		),
		'button'    => array(
			'text'          => 'text',
			'url'           => 'url',
			'link_new_tab'  => 'bool',
			'bg'            => 'color',
			'color'         => 'color',
			'hover_bg'      => 'color',
			'hover_color'   => 'color',
			'border_radius' => 'unit',
			'padding'       => oss_lpb_resp( 'unit' ),
			'font_size'     => oss_lpb_resp( 'unit' ),
			'font_weight'   => 'text',
			'icon'          => 'text',
			'icon_position' => array( 'left', 'right' ),
			'align'         => array( 'left', 'center', 'right' ),
		),
		'icon'      => array(
			'name'  => 'text',
			'size'  => 'unit',
			'color' => 'color',
			'link'  => 'url',
			'align' => array( 'left', 'center', 'right' ),
		),
	);
	return $schema;
}

/**
 * Element types that may contain child elements.
 */
function oss_lpb_container_types() {
	return array( 'section' );
}

/**
 * Sanitize one raw value against a sanitizer spec.
 */
function oss_lpb_sanitize_value( $spec, $value ) {
	// Responsive: apply the base spec to each breakpoint (check before the
	// allow-list branch — an allow-list is a numeric array, this is assoc).
	if ( is_array( $spec ) && ! empty( $spec['responsive'] ) ) {
		$of    = $spec['of'];
		$out   = array();
		$value = is_array( $value ) ? $value : array( 'desktop' => $value );
		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $bp ) {
			if ( isset( $value[ $bp ] ) && '' !== $value[ $bp ] ) {
				$v = oss_lpb_sanitize_value( $of, $value[ $bp ] );
				if ( '' !== $v && array() !== $v ) { $out[ $bp ] = $v; }
			}
		}
		return $out;
	}

	// Allow-list array.
	if ( is_array( $spec ) ) {
		$value = is_scalar( $value ) ? (string) $value : '';
		return in_array( $value, $spec, true ) ? $value : ( isset( $spec[0] ) ? $spec[0] : '' );
	}

	switch ( $spec ) {
		case 'text':
			return sanitize_text_field( is_scalar( $value ) ? wp_unslash( (string) $value ) : '' );
		case 'textarea':
			return sanitize_textarea_field( is_scalar( $value ) ? wp_unslash( (string) $value ) : '' );
		case 'html':
			return wp_kses_post( is_scalar( $value ) ? wp_unslash( (string) $value ) : '' );
		case 'url':
			return esc_url_raw( is_scalar( $value ) ? trim( wp_unslash( (string) $value ) ) : '' );
		case 'int':
			return absint( $value );
		case 'bool':
			return (bool) $value ? 1 : 0;
		case 'color':
			return oss_lpb_sanitize_color( is_scalar( $value ) ? (string) $value : '' );
		case 'unit':
			return oss_lpb_sanitize_unit( is_scalar( $value ) ? (string) $value : '' );
		case 'text_list':
			$items = is_array( $value ) ? $value : array();
			$items = array_map( function ( $i ) { return sanitize_text_field( wp_unslash( (string) $i ) ); }, $items );
			return array_values( array_filter( $items, function ( $i ) { return '' !== $i; } ) );
	}
	return '';
}

/**
 * A CSS color: #hex, rgb()/rgba(), a bare CSS var name, or var(--x). Empty otherwise.
 */
function oss_lpb_sanitize_color( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	if ( preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value ) ) {
		return $value;
	}
	if ( preg_match( '/^rgba?\(\s*[\d.,%\s\/]+\)$/', $value ) ) {
		return $value;
	}
	if ( preg_match( '/^var\(\s*--[a-zA-Z0-9-]+\s*\)$/', $value ) ) {
		return $value;
	}
	if ( preg_match( '/^--[a-zA-Z0-9-]+$/', $value ) ) {
		return 'var(' . $value . ')';
	}
	return '';
}

/**
 * A CSS length/size: number+unit, %, auto, or a couple of keywords. Empty otherwise.
 */
function oss_lpb_sanitize_unit( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	if ( in_array( $value, array( 'auto', 'none', 'fit-content', 'max-content', 'min-content' ), true ) ) {
		return $value;
	}
	// Up to four space-separated tokens (for padding/margin shorthands).
	$tokens = preg_split( '/\s+/', $value );
	if ( count( $tokens ) > 4 ) {
		return '';
	}
	foreach ( $tokens as $t ) {
		if ( ! preg_match( '/^-?(\d+(\.\d+)?)(px|rem|em|%|vh|vw|vmin|vmax)?$/', $t ) ) {
			return '';
		}
	}
	return implode( ' ', $tokens );
}

/**
 * Generate a stable, unique node id like "el_9fa3c1".
 */
function oss_lpb_new_id( $prefix = 'el' ) {
	return $prefix . '_' . substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 8 );
}

/**
 * A node id is safe if it matches our own format.
 */
function oss_lpb_valid_id( $id ) {
	return is_string( $id ) && preg_match( '/^(sec|el)_[a-z0-9]{4,16}$/', $id );
}

/**
 * Sanitize a whole document (array of sections). Returns a clean tree.
 */
function oss_lpb_sanitize_document( $raw ) {
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$schema = oss_lpb_schema();
	$out    = array();
	foreach ( $raw as $section ) {
		if ( ! is_array( $section ) || ( isset( $section['type'] ) && 'section' !== $section['type'] ) ) {
			continue;
		}
		$node = array(
			'id'       => oss_lpb_valid_id( $section['id'] ?? '' ) ? $section['id'] : oss_lpb_new_id( 'sec' ),
			'type'     => 'section',
			'settings' => oss_lpb_sanitize_settings( 'section', $section['settings'] ?? array() ),
			'elements' => array(),
		);
		$children = isset( $section['elements'] ) && is_array( $section['elements'] ) ? $section['elements'] : array();
		foreach ( $children as $el ) {
			if ( ! is_array( $el ) ) {
				continue;
			}
			$type = isset( $el['type'] ) ? (string) $el['type'] : '';
			if ( ! isset( $schema[ $type ] ) || 'section' === $type ) {
				continue;
			}
			$node['elements'][] = array(
				'id'       => oss_lpb_valid_id( $el['id'] ?? '' ) ? $el['id'] : oss_lpb_new_id( 'el' ),
				'type'     => $type,
				'settings' => oss_lpb_sanitize_settings( $type, $el['settings'] ?? array() ),
			);
		}
		$out[] = $node;
	}
	return $out;
}

/**
 * Sanitize a settings bag for one element type against the schema.
 */
function oss_lpb_sanitize_settings( $type, $settings ) {
	$schema = oss_lpb_schema();
	if ( ! isset( $schema[ $type ] ) || ! is_array( $settings ) ) {
		return array();
	}
	$clean = array();
	foreach ( $schema[ $type ] as $key => $spec ) {
		if ( ! array_key_exists( $key, $settings ) ) {
			continue;
		}
		$val = oss_lpb_sanitize_value( $spec, $settings[ $key ] );
		if ( '' === $val || array() === $val ) {
			continue;
		}
		$clean[ $key ] = $val;
	}
	return $clean;
}
