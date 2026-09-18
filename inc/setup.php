<?php
/**
 * Theme setup: supports, menus, widget areas, CPT-agnostic helpers.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme support + navigation menus.
 */
function oss_child_setup() {
	add_theme_support( 'custom-logo', array(
		'height'      => 100,
		'width'       => 280,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'astra-child' ),
		'footer'  => __( 'Footer Navigation', 'astra-child' ),
	) );
}
add_action( 'after_setup_theme', 'oss_child_setup' );

/**
 * Body class for whichever page currently has the transparent,
 * overlaid-on-photo header — lets CSS give that page's hero enough
 * top clearance without affecting solid-header pages/banners.
 */
function oss_child_body_class( $classes ) {
	if ( is_front_page() && ! is_page_template( 'page-templates/template-home-v2.php' ) ) {
		$classes[] = 'oss-transparent-header';
	}
	return $classes;
}
add_filter( 'body_class', 'oss_child_body_class' );

/**
 * Footer widget areas (4 columns), editable via Appearance → Widgets.
 */
function oss_child_widgets_init() {
	for ( $i = 1; $i <= 4; $i++ ) {
		register_sidebar( array(
			'name'          => sprintf( __( 'Footer Column %d', 'astra-child' ), $i ),
			'id'            => 'oss-footer-' . $i,
			'before_widget' => '<div class="oss-footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="oss-footer-widget-title">',
			'after_title'   => '</h3>',
		) );
	}
}
add_action( 'widgets_init', 'oss_child_widgets_init' );

/**
 * Set sensible defaults on first activation only (never overwrite admin edits).
 */
function oss_child_activation_defaults() {
	if ( ! get_option( 'oss_child_defaults_set' ) ) {
		if ( ! wp_get_nav_menu_object( 'Primary Menu' ) ) {
			$menu_id = wp_create_nav_menu( 'Primary Menu' );
		}
		update_option( 'oss_child_defaults_set', 1 );
	}
}
add_action( 'after_switch_theme', 'oss_child_activation_defaults' );
