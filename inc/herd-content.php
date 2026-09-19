<?php
/**
 * Meet the Herd — editable content store.
 *
 * Same pattern as inc/about-content.php. Everything the Meet the Herd page
 * shows lives in one option (oss_herd_content), edited at Appearance → Meet
 * the Herd. Defaults preserve the exact copy the page shipped with (previously
 * borrowed from the homepage store and native page fields).
 */

defined( 'ABSPATH' ) || exit;

define( 'OSS_HERD_OPTION', 'oss_herd_content' );

function oss_herd_content_defaults() {
	return array(
		'hero_eyebrow'  => 'Our Herd',
		'hero_heading'  => 'Meet the Herd',
		'hero_body'     => 'Every member of our herd has a story. Get to know the horses who make Open Spaces Sanctuary a place of healing.',
		'hero_image_id' => 110,

		'herd_eyebrow'  => 'The Herd',
		'herd_heading'  => 'Meet Our Horses',
		'herd_body'     => "Every horse at Open Spaces Sanctuary has a story.\n\nSome have overcome hardship. Some have found a second chance. Together, they become remarkable teachers—offering honesty, patience, trust, and unconditional acceptance to every person they meet.\n\nOur horses are not tools. They are partners in healing.",
		'herd_image_id' => 52,
		'herd_btn_text' => 'Sponsor a Horse',
		'herd_btn_url'  => '/contact/',
	);
}

function oss_herd_get( $key ) {
	static $content = null;
	if ( null === $content ) {
		$defaults = oss_herd_content_defaults();
		$saved    = get_option( OSS_HERD_OPTION, array() );
		$content  = array_merge( $defaults, is_array( $saved ) ? $saved : array() );
	}
	return isset( $content[ $key ] ) ? $content[ $key ] : '';
}

function oss_herd_image( $key, $size = 'large', $alt = '' ) {
	$id = (int) oss_herd_get( $key );
	if ( $id && wp_get_attachment_image_src( $id, $size ) ) {
		return wp_get_attachment_image( $id, $size, false, array( 'alt' => $alt ) );
	}
	return '<div class="oss-image-placeholder" aria-hidden="true"></div>';
}
