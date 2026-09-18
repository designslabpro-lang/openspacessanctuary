<?php
/**
 * Template Name: Live Builder
 *
 * Renders a page's Live Page Builder document (post meta) as plain, semantic
 * HTML using the theme's design tokens. No builder JS is loaded for normal
 * visitors — only in canvas mode for a permitted editor.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$oss_lpb_doc = oss_lpb_get_document( get_the_ID() );
	echo oss_lpb_render_document( $oss_lpb_doc ); // phpcs:ignore WordPress.Security.EscapingOutput -- renderer escapes every value.
endwhile;

get_footer();
