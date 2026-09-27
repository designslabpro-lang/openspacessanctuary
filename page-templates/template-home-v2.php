<?php
/**
 * Template Name: Homepage V2 (Custom)
 *
 * A ground-up homepage rebuild — new layout, new CSS (assets/css/home-v2.css),
 * zero Elementor, and it does not reuse or modify the previous Elementor
 * front page or the earlier "Home (Custom)" template. It reads the same
 * admin-editable content store (inc/homepage-content.php) so the existing
 * approved copy carries over and stays editable at
 * Appearance → Homepage Content — only the visual design is new.
 *
 * The individual section markup lives in inc/homepage-sections.php and is
 * rendered here in the client-chosen order (Homepage Content → drag / Up-Down).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$icons          = oss_home_icon_library();
$hero_slide_ids = array_values( array_filter( array_map( 'intval', oss_home_get( 'hero_slide_ids' ) ) ) );

foreach ( oss_home_section_order() as $oss_entry ) {
	list( $oss_section_key, $oss_ctx ) = oss_home_resolve_entry( $oss_entry );
	if ( $oss_section_key ) {
		oss_home_render_section( $oss_section_key, $icons, $hero_slide_ids, $oss_ctx );
	}
}

if ( function_exists( 'oss_sb_render_current' ) ) {
	oss_sb_render_current();
}

get_footer();
