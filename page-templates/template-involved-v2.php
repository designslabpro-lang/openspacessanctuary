<?php
/**
 * Template Name: Get Involved (V2)
 *
 * The page's own title and intro come from the native editor. The five
 * ways to get involved are the sub-pages listed in the client's content
 * document (no descriptions were supplied, so each is a titled card that
 * leads to the contact page). The donate section reuses the approved
 * "Help Us Change Lives" copy from the homepage content store.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	if ( function_exists( 'oss_sec_render' ) ) {
		oss_sec_render( 'involved' );
	} else {
		foreach ( array( 'hero', 'ways', 'give' ) as $oss_k ) {
			oss_involved_render_section( $oss_k, null );
		}
	}

endwhile;

if ( function_exists( 'oss_sb_render_current' ) ) {
	oss_sb_render_current();
}
oss2_connect_panel();
get_footer();
