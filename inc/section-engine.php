<?php
/**
 * Reusable section engine — reorder + duplicate for any content-store page.
 *
 * The homepage has its own (earlier, live-tested) implementation; this engine
 * gives the same capability to the other V2 pages (About, Contact, Meet the
 * Herd, Get Involved, FAQ) from one shared, tested codebase. A page registers a
 * "group" describing its option, sections, per-type fields, and a render
 * callback; the engine handles ordering, duplicate instances, and sanitizing.
 */

defined( 'ABSPATH' ) || exit;

$GLOBALS['oss_sec_groups'] = array();

/**
 * Register a section group.
 *
 * @param array $cfg {
 *   'group'    => string   unique slug (e.g. 'about')
 *   'option'   => string   content option name
 *   'get'      => callable value getter: fn( $key )
 *   'sections' => array    key => admin label, in default order
 *   'fields'   => array    key => list of flat field keys (for duplicating)
 *   'render'   => callable fn( $key, $ctx )  outputs a section's markup
 *   'page_hook'=> string   admin page hook suffix for its settings screen
 *   'title'    => string   short group title (for the admin toolbar)
 * }
 */
function oss_sec_register( $cfg ) {
	if ( empty( $cfg['group'] ) ) {
		return;
	}
	$GLOBALS['oss_sec_groups'][ $cfg['group'] ] = $cfg;
}

function oss_sec_cfg( $group ) {
	return isset( $GLOBALS['oss_sec_groups'][ $group ] ) ? $GLOBALS['oss_sec_groups'][ $group ] : null;
}

function oss_sec_get( $group, $key ) {
	$cfg = oss_sec_cfg( $group );
	if ( ! $cfg || ! is_callable( $cfg['get'] ) ) {
		return '';
	}
	return call_user_func( $cfg['get'], $key );
}

function oss_sec_default_order( $group ) {
	$cfg = oss_sec_cfg( $group );
	return $cfg && isset( $cfg['sections'] ) ? array_keys( $cfg['sections'] ) : array();
}

function oss_sec_dups( $group ) {
	$d = oss_sec_get( $group, 'dups' );
	return is_array( $d ) ? $d : array();
}

/**
 * Original section keys the client has hidden ("removed from page"). Their
 * content is kept; they simply don't render and aren't re-appended.
 */
function oss_sec_removed( $group ) {
	$r = oss_sec_get( $group, 'removed' );
	if ( ! is_array( $r ) ) {
		return array();
	}
	return array_values( array_intersect( $r, oss_sec_default_order( $group ) ) );
}

/**
 * Validated render order: originals + duplicate ids, unknowns dropped, missing
 * originals appended (unless hidden), and not-yet-placed duplicates appended.
 */
function oss_sec_order( $group ) {
	$default = oss_sec_default_order( $group );
	$dupids  = array_keys( oss_sec_dups( $group ) );
	$removed = oss_sec_removed( $group );
	$valid   = array_merge( $default, $dupids );
	$saved   = oss_sec_get( $group, 'section_order' );
	if ( ! is_array( $saved ) || ! $saved ) {
		$saved = $default;
	}
	$order = array();
	foreach ( $saved as $k ) {
		if ( in_array( $k, $removed, true ) ) {
			continue;
		}
		if ( in_array( $k, $valid, true ) && ! in_array( $k, $order, true ) ) {
			$order[] = $k;
		}
	}
	foreach ( $default as $k ) {
		if ( ! in_array( $k, $order, true ) && ! in_array( $k, $removed, true ) ) {
			$order[] = $k;
		}
	}
	foreach ( $dupids as $k ) {
		if ( ! in_array( $k, $order, true ) ) {
			$order[] = $k;
		}
	}
	return $order;
}

/**
 * Resolve an order entry to array( section_key, context ). Context is null for
 * an original, or the duplicate's stored content array.
 */
function oss_sec_resolve( $group, $entry ) {
	$dups = oss_sec_dups( $group );
	if ( isset( $dups[ $entry ] ) && is_array( $dups[ $entry ] ) ) {
		$type = isset( $dups[ $entry ]['type'] ) ? $dups[ $entry ]['type'] : '';
		$cfg  = oss_sec_cfg( $group );
		if ( $cfg && isset( $cfg['sections'][ $type ] ) ) {
			return array( $type, $dups[ $entry ] );
		}
		return array( '', null );
	}
	return array( $entry, null );
}

/**
 * Instance-aware field read used by render callbacks.
 */
function oss_sec_f( $group, $ctx, $key ) {
	if ( is_array( $ctx ) && array_key_exists( $key, $ctx ) ) {
		return $ctx[ $key ];
	}
	return oss_sec_get( $group, $key );
}

/**
 * Instance-aware image render (id or placeholder), mirroring the per-page image
 * helpers so a duplicate shows its own image.
 */
function oss_sec_image( $group, $ctx, $key, $size = 'large', $alt = '' ) {
	$id = (int) oss_sec_f( $group, $ctx, $key );
	if ( $id && wp_get_attachment_image_src( $id, $size ) ) {
		return wp_get_attachment_image( $id, $size, false, array( 'alt' => $alt ) );
	}
	return '<div class="oss-image-placeholder" aria-hidden="true"></div>';
}

/**
 * Render every section of a group in the saved order (originals + duplicates).
 */
function oss_sec_render( $group ) {
	$cfg = oss_sec_cfg( $group );
	if ( ! $cfg || ! is_callable( $cfg['render'] ) ) {
		return;
	}
	foreach ( oss_sec_order( $group ) as $entry ) {
		list( $key, $ctx ) = oss_sec_resolve( $group, $entry );
		if ( $key ) {
			call_user_func( $cfg['render'], $key, $ctx );
		}
	}
}

/* ------------------------------------------------------------------ */
/* Sanitizing                                                          */
/* ------------------------------------------------------------------ */

/**
 * Sanitize one field value by its key using generic naming heuristics.
 */
function oss_sec_sanitize_value( $key, $value ) {
	if ( false !== strpos( $key, '_image_id' ) ) {
		return absint( $value );
	}
	if ( '_layout' === substr( $key, -7 ) ) {
		return in_array( $value, array( '1', '2' ), true ) ? $value : '1';
	}
	if ( false !== strpos( $key, '_url' ) ) {
		return esc_url_raw( trim( (string) $value ) );
	}
	$suffix = preg_replace( '/^[a-z0-9]+_/', '', $key );
	$rich   = array( 'body', 'body1', 'body2', 'body3', 'quote', 'intro' );
	if ( in_array( $suffix, $rich, true ) || '_body' === substr( $key, -5 ) || '_quote' === substr( $key, -6 ) || '_intro' === substr( $key, -6 ) ) {
		return wp_kses_post( wp_unslash( $value ) );
	}
	if ( is_array( $value ) ) {
		return array_map( 'sanitize_text_field', $value );
	}
	if ( false !== strpos( (string) $value, "\n" ) ) {
		return sanitize_textarea_field( wp_unslash( $value ) );
	}
	return sanitize_text_field( wp_unslash( $value ) );
}

/**
 * Sanitize a group's duplicate store, merging over the stored copy so fields
 * not present in the submission are preserved.
 */
function oss_sec_sanitize_dups( $group, $raw ) {
	$cfg = oss_sec_cfg( $group );
	if ( ! $cfg ) {
		return array();
	}
	$valid_types = array_keys( $cfg['sections'] );
	$existing    = oss_sec_dups( $group );
	$out         = array();
	foreach ( (array) $raw as $id => $inst ) {
		if ( ! is_array( $inst ) ) {
			continue;
		}
		$id = sanitize_key( $id );
		if ( '' === $id ) {
			continue;
		}
		$type = isset( $inst['type'] ) ? sanitize_key( $inst['type'] ) : '';
		if ( ! in_array( $type, $valid_types, true ) ) {
			continue;
		}
		$clean         = ( isset( $existing[ $id ] ) && is_array( $existing[ $id ] ) ) ? $existing[ $id ] : array();
		$clean['type'] = $type;
		foreach ( $inst as $k => $v ) {
			if ( 'type' === $k ) {
				continue;
			}
			$k = sanitize_key( $k );
			if ( '' === $k ) {
				continue;
			}
			$clean[ $k ] = oss_sec_sanitize_value( $k, $v );
		}
		$out[ $id ] = $clean;
	}
	return $out;
}

/**
 * Sanitize a group's section order. Keeps only valid original keys and valid
 * duplicate ids (from the submission's dups or the stored dups).
 */
function oss_sec_sanitize_order( $group, $input_order, $input_dups = null ) {
	$originals = oss_sec_default_order( $group );
	$dupids    = array();
	if ( is_array( $input_dups ) ) {
		$dupids = array_keys( $input_dups );
	}
	$dupids = array_merge( $dupids, array_keys( oss_sec_dups( $group ) ) );
	$valid  = array_merge( $originals, $dupids );
	$order  = array();
	foreach ( (array) $input_order as $sk ) {
		$sk = sanitize_text_field( $sk );
		if ( in_array( $sk, $valid, true ) && ! in_array( $sk, $order, true ) ) {
			$order[] = $sk;
		}
	}
	return $order;
}

/**
 * Convenience: merge order + dups sanitizing into a page's own sanitize
 * routine. Call from each group's sanitize callback for its 'section_order'
 * and 'dups' keys. Returns the cleaned value for the given key, or null if the
 * key is not one the engine handles.
 */
function oss_sec_sanitize_key( $group, $key, $input, $existing_getter ) {
	if ( 'dups' === $key ) {
		if ( isset( $input['dups'] ) && is_array( $input['dups'] ) ) {
			return oss_sec_sanitize_dups( $group, $input['dups'] );
		}
		return (array) call_user_func( $existing_getter, 'dups' );
	}
	if ( 'section_order' === $key ) {
		if ( ! isset( $input['section_order'] ) || ! is_array( $input['section_order'] ) ) {
			return (array) call_user_func( $existing_getter, 'section_order' );
		}
		return oss_sec_sanitize_order( $group, $input['section_order'], isset( $input['dups'] ) ? $input['dups'] : null );
	}
	if ( 'removed' === $key ) {
		if ( ! isset( $input['removed'] ) || ! is_array( $input['removed'] ) ) {
			return (array) call_user_func( $existing_getter, 'removed' );
		}
		$originals = oss_sec_default_order( $group );
		$out       = array();
		foreach ( $input['removed'] as $rk ) {
			$rk = sanitize_key( $rk );
			if ( in_array( $rk, $originals, true ) && ! in_array( $rk, $out, true ) ) {
				$out[] = $rk;
			}
		}
		return $out;
	}
	return null;
}
