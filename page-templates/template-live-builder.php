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
	$oss_lpb_pid = get_the_ID();
	// In the editor's canvas, render the in-progress autosave draft (if any)
	// so structural edits appear before the admin commits a real Save.
	$oss_lpb_doc = oss_lpb_get_document( $oss_lpb_pid );
	if ( oss_lpb_is_canvas() && oss_lpb_user_can( $oss_lpb_pid ) ) {
		$oss_lpb_draft = get_post_meta( $oss_lpb_pid, OSS_LPB_AUTOSAVE_META, true );
		if ( is_array( $oss_lpb_draft ) && $oss_lpb_draft ) {
			$oss_lpb_doc = $oss_lpb_draft;
		}
	}
	echo oss_lpb_render_document( $oss_lpb_doc ); // phpcs:ignore WordPress.Security.EscapingOutput -- renderer escapes every value.
endwhile;

get_footer();
