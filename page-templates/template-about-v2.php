<?php
/**
 * Template Name: About (V2)
 *
 * About page in the V2 design language. Copy comes from inc/about-content.php
 * (Appearance → About Page); image slots fall back to the homepage's real
 * photos when no image has been chosen for the About page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( function_exists( 'oss_sec_render' ) ) {
	oss_sec_render( 'about' );
} else {
	// Fallback: render in default order if the engine is unavailable.
	foreach ( array( 'hero', 'story', 'philosophy', 'founder', 'final' ) as $oss_k ) {
		oss_about_render_section( $oss_k, null );
	}
}

if ( function_exists( 'oss_sb_render_current' ) ) {
	oss_sb_render_current();
}
oss2_connect_panel();
get_footer();
