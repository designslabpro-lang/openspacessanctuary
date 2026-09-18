<?php
/**
 * Asset enqueueing: parent + child CSS, brand fonts, JS.
 */

defined( 'ABSPATH' ) || exit;

function oss_child_enqueue_assets() {
	// Parent Astra stylesheet.
	wp_enqueue_style( 'astra-parent-style', get_template_directory_uri() . '/style.css', array(), OSS_CHILD_VERSION );

	// Brand fonts: Playfair Display (headings) + Montserrat (body) + Alex Brush (script accents).
	wp_enqueue_style(
		'oss-fonts',
		'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Montserrat:wght@300;400;500;600;700&family=Alex+Brush&display=swap',
		array(),
		null
	);

	// Design tokens + global styles.
	wp_enqueue_style( 'oss-global', OSS_CHILD_URI . '/assets/css/global.css', array( 'astra-parent-style' ), OSS_CHILD_VERSION );
	wp_enqueue_style( 'oss-header', OSS_CHILD_URI . '/assets/css/header.css', array( 'oss-global' ), OSS_CHILD_VERSION );
	wp_enqueue_style( 'oss-footer', OSS_CHILD_URI . '/assets/css/footer.css', array( 'oss-global' ), OSS_CHILD_VERSION );
	wp_enqueue_style( 'oss-components', OSS_CHILD_URI . '/assets/css/components.css', array( 'oss-global' ), OSS_CHILD_VERSION );
	wp_enqueue_style( 'oss-responsive', OSS_CHILD_URI . '/assets/css/responsive.css', array( 'oss-components' ), OSS_CHILD_VERSION );

	// Child stylesheet last (allows minor overrides via style.css if ever needed).
	wp_enqueue_style( 'astra-child-style', get_stylesheet_uri(), array( 'oss-responsive' ), OSS_CHILD_VERSION );

	wp_enqueue_script( 'oss-main', OSS_CHILD_URI . '/assets/js/main.js', array(), OSS_CHILD_VERSION, true );

	if ( is_page_template( 'page-templates/template-home-v2.php' ) ) {
		wp_enqueue_style( 'oss-home-v2', OSS_CHILD_URI . '/assets/css/home-v2.css', array( 'astra-child-style' ), OSS_CHILD_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'oss_child_enqueue_assets' );

/**
 * Editor styles so the block/Elementor editor previews brand fonts.
 */
function oss_child_editor_assets() {
	add_editor_style( array(
		'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Montserrat:wght@300;400;500;600;700&display=swap',
		OSS_CHILD_URI . '/assets/css/global.css',
	) );
}
add_action( 'admin_init', 'oss_child_editor_assets' );
