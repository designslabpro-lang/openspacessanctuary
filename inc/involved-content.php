<?php
/**
 * Get Involved — editable content store.
 *
 * Same pattern as inc/about-content.php, plus a small repeatable list for the
 * "Ways to Get Involved" cards. Everything lives in one option
 * (oss_involved_content), edited at Appearance → Get Involved. Defaults
 * preserve the copy the page shipped with.
 */

defined( 'ABSPATH' ) || exit;

define( 'OSS_INVOLVED_OPTION', 'oss_involved_content' );

function oss_involved_content_defaults() {
	return array(
		'hero_eyebrow'  => 'Get Involved',
		'hero_heading'  => 'Get Involved',
		'hero_body'     => 'There are many ways to support the herd — volunteer, donate, or sponsor a horse. Reach out to learn how you can get involved.',
		'hero_image_id' => 122,

		'ways_eyebrow'  => 'Get Involved',
		'ways_heading'  => 'Ways to Get Involved',
		'ways'          => array(
			array( 'icon' => 'hands',   'label' => 'Volunteer',          'url' => '/contact/' ),
			array( 'icon' => 'people',  'label' => 'Become a Partner',    'url' => '/contact/' ),
			array( 'icon' => 'star',    'label' => 'Host a Fundraiser',   'url' => '/contact/' ),
			array( 'icon' => 'shield',  'label' => 'Corporate Sponsors',  'url' => '/contact/' ),
			array( 'icon' => 'compass', 'label' => 'Internships',         'url' => '/contact/' ),
		),

		'give_eyebrow'  => 'Support the Sanctuary',
		'give_heading'  => 'Help Us Change Lives',
		'give_body'     => "Every donation creates opportunities for healing—for individuals navigating trauma, illness, grief, and life's many challenges, and for the horses who make this work possible.\n\nYour generosity helps provide scholarships, exceptional horse care, educational programs, and a peaceful sanctuary where transformation can happen.\n\nTogether, we can create hope, one person and one horse at a time.",
		'give_image_id' => 123,
		'give_btn_text' => 'Donate Today',
		'give_btn_url'  => '/contact/',
	);
}

function oss_involved_get( $key ) {
	static $content = null;
	if ( null === $content ) {
		$defaults = oss_involved_content_defaults();
		$saved    = get_option( OSS_INVOLVED_OPTION, array() );
		$content  = array_merge( $defaults, is_array( $saved ) ? $saved : array() );
		if ( empty( $content['ways'] ) || ! is_array( $content['ways'] ) ) {
			$content['ways'] = $defaults['ways'];
		}
	}
	return isset( $content[ $key ] ) ? $content[ $key ] : '';
}

function oss_involved_image( $key, $size = 'large', $alt = '' ) {
	$id = (int) oss_involved_get( $key );
	if ( $id && wp_get_attachment_image_src( $id, $size ) ) {
		return wp_get_attachment_image( $id, $size, false, array( 'alt' => $alt ) );
	}
	return '<div class="oss-image-placeholder" aria-hidden="true"></div>';
}
