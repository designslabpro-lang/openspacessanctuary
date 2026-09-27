<?php
/**
 * Template Name: FAQ (V2)
 *
 * A dedicated FAQ page. Banner + accordion of questions, all editable at
 * Appearance → FAQ Page (inc/faq-content.php). Reuses the shared FAQ accordion
 * markup, CSS (.oss2-faq__*), and the one-open-at-a-time accordion JS.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( function_exists( 'oss_sec_render' ) ) {
	oss_sec_render( 'faq' );
} else {
	oss_faq_render_section( 'hero', null );
	oss_faq_render_section( 'faqs', null );
}
?>

<?php
if ( function_exists( 'oss_sb_render_current' ) ) {
	oss_sb_render_current();
}
oss2_connect_panel();
get_footer();
