<?php
/**
 * Elementor compatibility layer.
 */

defined( 'ABSPATH' ) || exit;

function oss_child_elementor_support() {
	add_theme_support( 'elementor' );
}
add_action( 'after_setup_theme', 'oss_child_elementor_support' );

/**
 * Hide Astra's default page title/banner on pages built with Elementor —
 * the Elementor content already supplies its own hero/heading.
 */
function oss_child_hide_title_for_elementor_pages( $visibility ) {
	$post_id = get_the_ID();
	if ( $post_id && 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
		return false;
	}
	return $visibility;
}
add_filter( 'astra_single_layout_one_banner_visibility', 'oss_child_hide_title_for_elementor_pages' );

/**
 * Widen Elementor's editor preview so it inherits the child theme's fonts.
 */
function oss_child_elementor_editor_styles() {
	echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Montserrat:wght@300;400;500;600;700&display=swap">';
	echo '<link rel="stylesheet" href="' . esc_url( OSS_CHILD_URI . '/assets/css/global.css' ) . '">';
	echo '<link rel="stylesheet" href="' . esc_url( OSS_CHILD_URI . '/assets/css/components.css' ) . '">';
}
add_action( 'elementor/editor/before_enqueue_scripts', 'oss_child_elementor_editor_styles' );

/**
 * Default Elementor kit colors/typography to match the brand palette,
 * so new sections built in Elementor default to on-brand choices.
 * Runs once; does not overwrite an admin's existing kit customizations.
 */
function oss_child_seed_elementor_kit_defaults() {
	if ( ! did_action( 'elementor/loaded' ) || get_option( 'oss_child_kit_seeded' ) ) {
		return;
	}
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return;
	}

	$kit_id = \Elementor\Plugin::$instance->kits_manager->get_active_id();
	if ( ! $kit_id ) {
		return;
	}

	$system_colors = array(
		array( '_id' => 'primary', 'title' => 'Sage Green', 'color' => '#596B58' ),
		array( '_id' => 'secondary', 'title' => 'Earthy Brown', 'color' => '#755B45' ),
		array( '_id' => 'text', 'title' => 'Text', 'color' => '#353A35' ),
		array( '_id' => 'accent', 'title' => 'Antique Gold', 'color' => '#B49A68' ),
	);

	$system_typography = array(
		array(
			'_id'                          => 'primary',
			'title'                        => 'Heading',
			'typography_typography'        => 'custom',
			'typography_font_family'       => 'Playfair Display',
			'typography_font_weight'       => '600',
		),
		array(
			'_id'                    => 'text',
			'title'                  => 'Body',
			'typography_typography'  => 'custom',
			'typography_font_family' => 'Montserrat',
			'typography_font_weight' => '400',
		),
	);

	update_post_meta( $kit_id, '_elementor_page_settings', array_merge(
		(array) get_post_meta( $kit_id, '_elementor_page_settings', true ),
		array(
			'system_colors'     => $system_colors,
			'system_typography' => $system_typography,
			'background_body_background' => 'classic',
			'background_body_color'      => '#F5F0E6',
		)
	) );

	update_option( 'oss_child_kit_seeded', 1 );
}
add_action( 'init', 'oss_child_seed_elementor_kit_defaults', 20 );
