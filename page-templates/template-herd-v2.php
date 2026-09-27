<?php
/**
 * Template Name: Meet the Herd (V2)
 *
 * The page's own title and intro come from the native editor; the herd
 * section reuses the approved "Meet Our Horses" copy from the homepage
 * content store. "Sponsor a Horse" is one of the approved site buttons.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	if ( function_exists( 'oss_sec_render' ) ) {
		oss_sec_render( 'herd' );
	} else {
		oss_herd_render_section( 'hero', null );
		oss_herd_render_section( 'herd', null );
	}

endwhile;

if ( function_exists( 'oss_sb_render_current' ) ) {
	oss_sb_render_current();
}
oss2_connect_panel();
get_footer();
