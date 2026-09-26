<?php
/**
 * FAQ Page — editable content store (option oss_faq_content), edited at
 * Appearance → FAQ Page and rendered by page-templates/template-faq-v2.php.
 * Same pattern as the other content pages.
 */

defined( 'ABSPATH' ) || exit;

define( 'OSS_FAQ_OPTION', 'oss_faq_content' );

function oss_faq_content_defaults() {
	return array(
		'hero_eyebrow'  => 'Questions & Answers',
		'hero_heading'  => 'Frequently Asked Questions',
		'hero_body'     => "Answers to the questions we hear most. Don't see yours? Reach out anytime — we're happy to help.",
		'hero_image_id' => 0,
		'hero_image_pos' => 'center center',
		'hero_image_fit' => 'cover',

		// Questions carried over from the site's content; answers are editable.
		'faqs' => array(
			array( 'q' => 'What is Equine Assisted Learning?', 'a' => '' ),
			array( 'q' => 'Do I ride the horses?', 'a' => '' ),
			array( 'q' => 'Is this therapy?', 'a' => '' ),
			array( 'q' => 'Who can participate?', 'a' => '' ),
			array( 'q' => 'What should I wear?', 'a' => '' ),
			array( 'q' => 'Can children attend?', 'a' => '' ),
			array( 'q' => 'How much does it cost?', 'a' => '' ),
		),
	);
}

function oss_faq_get( $key ) {
	static $content = null;
	if ( null === $content ) {
		$defaults = oss_faq_content_defaults();
		$saved    = get_option( OSS_FAQ_OPTION, array() );
		$content  = array_merge( $defaults, is_array( $saved ) ? $saved : array() );
		if ( empty( $content['faqs'] ) || ! is_array( $content['faqs'] ) ) {
			$content['faqs'] = $defaults['faqs'];
		}
	}
	return isset( $content[ $key ] ) ? $content[ $key ] : '';
}

function oss_faq_image( $key, $size = 'large', $alt = '' ) {
	$id = (int) oss_faq_get( $key );
	if ( $id && wp_get_attachment_image_src( $id, $size ) ) {
		return wp_get_attachment_image( $id, $size, false, array( 'alt' => $alt ) );
	}
	return '<div class="oss-image-placeholder" aria-hidden="true"></div>';
}
