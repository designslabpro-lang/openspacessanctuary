<?php
/**
 * Contact Page Content — plain-PHP editable data store.
 *
 * Same pattern as inc/homepage-content.php: every piece of copy/media lives
 * in a single option (oss_contact_content), edited through a real wp-admin
 * screen (Appearance → Contact Page) and rendered by
 * page-templates/template-contact-custom.php. No Elementor dependency.
 * The contact details / form themselves stay in the existing
 * [oss_contact_info] / [oss_contact_form] shortcodes.
 */

defined( 'ABSPATH' ) || exit;

define( 'OSS_CONTACT_OPTION', 'oss_contact_content' );

/**
 * Default values — the source Contact page content.
 */
function oss_contact_content_defaults() {
	return array(
		'hero_eyebrow' => "We're Here For You",
		'hero_heading' => 'Contact Us',
		'hero_body'    => 'Questions about our programs, upcoming events, or how to get involved? We would love to hear from you.',
		'hero_image_id' => 0,

		'info_heading'   => 'Get In Touch',
		'form_heading'   => 'Send a Message',
		'form_shortcode' => '[gravityform id="1" title="false"]',

		'cta_heading' => 'Ready to Begin?',
		'cta_body'    => 'Explore how Open Spaces Sanctuary can help create meaningful connection and healing.',
		'cta_btn_text' => 'View Our Programs',
		'cta_btn_url'  => '/programs/',
	);
}

/**
 * Read a Contact page content field, falling back to its default.
 */
function oss_contact_get( $key ) {
	static $content = null;
	if ( null === $content ) {
		$defaults = oss_contact_content_defaults();
		$saved    = get_option( OSS_CONTACT_OPTION, array() );
		$content  = array_merge( $defaults, is_array( $saved ) ? $saved : array() );
	}
	return isset( $content[ $key ] ) ? $content[ $key ] : '';
}

/**
 * Render a stored image (by attachment ID) with a fallback placeholder.
 */
function oss_contact_image( $key, $size = 'large', $alt = '' ) {
	$id = (int) oss_contact_get( $key );
	if ( $id && wp_get_attachment_image_src( $id, $size ) ) {
		return wp_get_attachment_image( $id, $size, false, array( 'alt' => $alt ) );
	}
	return '<div class="oss-image-placeholder" aria-hidden="true"></div>';
}
