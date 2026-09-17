<?php
/**
 * Small reusable template helpers.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output a thin gold divider — the sanctuary's signature decorative element.
 */
function oss_gold_divider( $align = 'center' ) {
	printf( '<div class="oss-divider oss-divider--%s" aria-hidden="true"></div>', esc_attr( $align ) );
}

/**
 * Render the branded header CTA button (Customizer-controlled).
 */
function oss_header_cta() {
	$text = get_theme_mod( 'oss_header_cta_text', 'Donate' );
	$url  = get_theme_mod( 'oss_header_cta_url', '/contact' );
	if ( empty( $text ) ) {
		return;
	}
	printf(
		'<a class="oss-btn oss-btn--primary oss-btn--sm" href="%s">%s</a>',
		esc_url( $url ),
		esc_html( $text )
	);
}

/**
 * Fallback nav (only shown until an admin assigns a menu under
 * Appearance → Menus → Primary Navigation). Lists published top-level pages.
 */
function oss_child_default_menu_fallback() {
	echo '<ul>';
	wp_list_pages( array(
		'title_li' => '',
		'depth'    => 1,
		'sort_column' => 'menu_order',
	) );
	echo '</ul>';
}

/**
 * Whether at least one social network URL is configured, so callers can
 * skip rendering an empty "Social Media" heading.
 */
function oss_has_social_links() {
	$networks = array_filter( array(
		get_theme_mod( 'oss_facebook', '' ),
		get_theme_mod( 'oss_instagram', '' ),
	) );
	return ! empty( $networks );
}

/**
 * Social links list, reused in header (if desired) and footer.
 */
function oss_social_links() {
	$networks = array(
		'facebook'  => get_theme_mod( 'oss_facebook', '' ),
		'instagram' => get_theme_mod( 'oss_instagram', '' ),
	);
	$networks = array_filter( $networks );
	if ( empty( $networks ) ) {
		return;
	}
	echo '<ul class="oss-social-links">';
	foreach ( $networks as $network => $url ) {
		printf(
			'<li><a href="%s" target="_blank" rel="noopener noreferrer" aria-label="%s">%s</a></li>',
			esc_url( $url ),
			esc_attr( ucfirst( $network ) ),
			esc_html( ucfirst( $network ) )
		);
	}
	echo '</ul>';
}
