<?php
/**
 * About Page Content — plain-PHP editable data store.
 *
 * Same pattern as inc/homepage-content.php: every piece of copy/media lives
 * in a single option (oss_about_content), edited through a real wp-admin
 * screen (Appearance → About Page) and rendered by
 * page-templates/template-about-custom.php. No Elementor dependency.
 */

defined( 'ABSPATH' ) || exit;

define( 'OSS_ABOUT_OPTION', 'oss_about_content' );

/**
 * Default values — the source About page content, used until an admin
 * edits a field (and as the fallback if a field is ever cleared).
 */
function oss_about_content_defaults() {
	return array(
		'hero_eyebrow' => 'About Us',
		'hero_heading' => 'Our Story',
		'hero_body'    => 'Nestled in the peaceful countryside of Ocala, Florida.',
		'hero_image_id' => 7,

		'story_eyebrow' => 'Our Story',
		'story_heading' => 'A Place to Grow and Heal',
		'story_body'    => "Life can leave us carrying burdens that feel too heavy to bear—grief, trauma, stress, illness, burnout, or the weight of caring for others. At Open Spaces Sanctuary, we believe healing begins when we reconnect with ourselves, with nature, and with the quiet wisdom of horses.\n\nNestled in the peaceful countryside of Ocala, Florida, Open Spaces Sanctuary offers transformative equine-assisted learning experiences designed to restore hope, build resilience, and inspire lasting personal growth. Here, people and horses come together in a safe, supportive environment where authentic connection leads to meaningful change.",
		'story_image_id' => 7,

		'philosophy_eyebrow' => 'The Healing Philosophy',
		'philosophy_heading' => 'The Healing Power of Horses',
		'philosophy_body'    => "Horses have an extraordinary ability to reflect what we are feeling without judgment or expectation. They respond honestly to our emotions, energy, and intentions, helping us become more aware of ourselves in ways words often cannot.\n\nThrough guided, ground-based experiences—no riding—participants discover greater confidence, healthier boundaries, improved communication, emotional resilience, and renewed hope.",
		'philosophy_quote'   => "Healing doesn't happen because of what we do to the horses. It happens because of the experiences and relationships we build with them.",
		'philosophy_image_id' => 13,

		'founder_heading' => 'Meet Our Founder',
		'founder_name'    => 'Donna Blem',
		'founder_body'    => "Open Spaces Sanctuary was founded by Donna Blem, whose lifelong passion for horses and service has impacted thousands of lives.\n\nAs the co-founder and Executive Director of one of the nation's largest therapeutic riding centers, Donna spent over two decades building programs that changed lives while mentoring staff, volunteers, and equine partners. Her experience in nonprofit leadership, equine-assisted learning, and natural horsemanship now guides the vision of Open Spaces Sanctuary—a place where healing, hope, and connection flourish for both people and horses.",

		'final_heading' => 'Everyone Deserves a Place to Heal',
		'final_body'    => "Whether you're looking for support, searching for meaningful ways to give back, or simply curious about the incredible partnership between people and horses, we'd love to welcome you to Open Spaces Sanctuary.",
		'final_btn_text' => 'Get In Touch',
		'final_btn_url'  => '/contact/',
	);
}

/**
 * Read an About page content field, falling back to its default.
 */
function oss_about_get( $key ) {
	static $content = null;
	if ( null === $content ) {
		$defaults = oss_about_content_defaults();
		$saved    = get_option( OSS_ABOUT_OPTION, array() );
		$content  = array_merge( $defaults, is_array( $saved ) ? $saved : array() );
	}
	return isset( $content[ $key ] ) ? $content[ $key ] : '';
}

/**
 * Render a stored image (by attachment ID) with a fallback placeholder.
 */
function oss_about_image( $key, $size = 'large', $alt = '' ) {
	$id = (int) oss_about_get( $key );
	if ( $id && wp_get_attachment_image_src( $id, $size ) ) {
		return wp_get_attachment_image( $id, $size, false, array( 'alt' => $alt ) );
	}
	return '<div class="oss-image-placeholder" aria-hidden="true"></div>';
}
