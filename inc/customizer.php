<?php
/**
 * Customizer additions: sanctuary contact info + social links.
 * Site → Customize → Open Space Sanctuary Settings.
 */

defined( 'ABSPATH' ) || exit;

function oss_child_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'oss_sanctuary_settings', array(
		'title'    => __( 'Open Space Sanctuary Settings', 'astra-child' ),
		'priority' => 30,
	) );

	$fields = array(
		'oss_phone'          => array( 'label' => __( 'Phone Number', 'astra-child' ), 'default' => '(555) 123-4567' ),
		'oss_email'          => array( 'label' => __( 'Email Address', 'astra-child' ), 'default' => 'hello@openspacesanctuary.org' ),
		'oss_address'        => array( 'label' => __( 'Address', 'astra-child' ), 'default' => '123 Pasture Lane, Open Space, ST 00000' ),
		'oss_hours'          => array( 'label' => __( 'Hours', 'astra-child' ), 'default' => 'Tue–Sat, 9am–5pm' ),
		'oss_facebook'       => array( 'label' => __( 'Facebook URL', 'astra-child' ), 'default' => '' ),
		'oss_instagram'      => array( 'label' => __( 'Instagram URL', 'astra-child' ), 'default' => '' ),
		'oss_header_cta_text' => array( 'label' => __( 'Header CTA Text', 'astra-child' ), 'default' => 'Learn More' ),
		'oss_header_cta_url'  => array( 'label' => __( 'Header CTA URL', 'astra-child' ), 'default' => '/contact' ),
		'oss_donate_url'      => array( 'label' => __( 'Donate URL', 'astra-child' ), 'default' => '/contact' ),
		'oss_volunteer_url'   => array( 'label' => __( 'Volunteer URL', 'astra-child' ), 'default' => '/contact' ),
		'oss_nonprofit_info'  => array( 'label' => __( 'Nonprofit Status Line', 'astra-child' ), 'default' => 'Open Space Sanctuary is a 501(c)(3) nonprofit organization. EIN: 00-0000000. All donations are tax-deductible.' ),
	);

	foreach ( $fields as $id => $args ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $args['default'],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $args['label'],
			'section' => 'oss_sanctuary_settings',
			'type'    => 'text',
		) );
	}

	// Archive intro text (Programs / Events pages).
	$text_fields = array(
		'oss_programs_intro' => __( 'Programs Page Intro Text', 'astra-child' ),
		'oss_events_intro'   => __( 'Events Page Intro Text', 'astra-child' ),
		'oss_blog_intro'     => __( 'Blog Page Intro Text', 'astra-child' ),
		'oss_mission_statement' => __( 'Footer Mission Statement', 'astra-child' ),
	);
	foreach ( $text_fields as $id => $label ) {
		$wp_customize->add_setting( $id, array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
		$wp_customize->add_control( $id, array(
			'label'   => $label,
			'section' => 'oss_sanctuary_settings',
			'type'    => 'textarea',
		) );
	}

	// Archive hero background images.
	$image_fields = array(
		'oss_programs_hero_image' => __( 'Programs Page Hero Image', 'astra-child' ),
		'oss_events_hero_image'   => __( 'Events Page Hero Image', 'astra-child' ),
		'oss_blog_hero_image'     => __( 'Blog Page Hero Image', 'astra-child' ),
	);
	foreach ( $image_fields as $id => $label ) {
		$wp_customize->add_setting( $id, array( 'sanitize_callback' => 'esc_url_raw' ) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $id, array(
			'label'   => $label,
			'section' => 'oss_sanctuary_settings',
		) ) );
	}
}
add_action( 'customize_register', 'oss_child_customize_register' );
