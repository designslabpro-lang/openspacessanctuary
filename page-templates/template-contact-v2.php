<?php
/**
 * Template Name: Contact (V2)
 *
 * Contact page in the V2 design language. Copy from inc/contact-content.php
 * (Appearance → Contact Page); contact details and the form come from the
 * [oss_contact_info] / [oss_contact_form] shortcodes. The FAQ lists the
 * questions from the client's content document (answers not yet supplied).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( function_exists( 'oss_sec_render' ) ) {
	oss_sec_render( 'contact' );
} else {
	foreach ( array( 'hero', 'info', 'faq', 'cta' ) as $oss_k ) {
		oss_contact_render_section( $oss_k, null );
	}
}
?>

<?php
if ( function_exists( 'oss_sb_render_current' ) ) {
	oss_sb_render_current();
}
oss2_connect_panel();
get_footer();
