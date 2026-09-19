<?php
/**
 * Programs custom post type — a scalable, admin-manageable list of programs.
 * Each program is a normal WP post edited with the block editor / Elementor,
 * with a small meta box for "audience" (shown as a label on program cards).
 */

defined( 'ABSPATH' ) || exit;

function oss_child_register_program_cpt() {
	register_post_type( 'oss_program', array(
		'labels' => array(
			'name'          => __( 'Programs', 'astra-child' ),
			'singular_name' => __( 'Program', 'astra-child' ),
			'add_new_item'  => __( 'Add New Program', 'astra-child' ),
			'edit_item'     => __( 'Edit Program', 'astra-child' ),
			'all_items'     => __( 'Programs', 'astra-child' ),
			'menu_name'     => __( 'Programs', 'astra-child' ),
		),
		'public'       => true,
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'programs' ),
		'menu_icon'    => 'dashicons-groups',
		'show_in_rest' => true,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
	) );
}
add_action( 'init', 'oss_child_register_program_cpt' );

function oss_child_program_meta_box() {
	add_meta_box( 'oss_program_details', __( 'Program Details', 'astra-child' ), 'oss_child_program_meta_box_html', 'oss_program', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'oss_child_program_meta_box' );

/**
 * Text + textarea fields for a program, beyond the block-editor body. These
 * drive the extra sections on the single-program template; empty ones are
 * simply not shown on the front end.
 */
function oss_child_program_fields() {
	return array(
		'oss_program_audience'           => array( 'label' => __( 'Audience / Best For', 'astra-child' ), 'type' => 'text', 'ph' => 'e.g. Veterans, Youth, Adults' ),
		'oss_program_tagline'            => array( 'label' => __( 'Hero Tagline', 'astra-child' ), 'type' => 'text', 'ph' => 'A short line under the title' ),
		'oss_program_duration'           => array( 'label' => __( 'Duration', 'astra-child' ), 'type' => 'text', 'ph' => 'e.g. Half-day', 'group' => __( 'At a Glance', 'astra-child' ) ),
		'oss_program_format'             => array( 'label' => __( 'Format', 'astra-child' ), 'type' => 'text', 'ph' => 'e.g. Ground-based, no riding' ),
		'oss_program_location'           => array( 'label' => __( 'Location', 'astra-child' ), 'type' => 'text', 'ph' => 'e.g. Citra, FL' ),
		'oss_program_experience_heading' => array( 'label' => __( 'Heading', 'astra-child' ), 'type' => 'text', 'ph' => "What You'll Experience", 'group' => __( "Section: What You'll Experience", 'astra-child' ) ),
		'oss_program_experience_intro'   => array( 'label' => __( 'Intro', 'astra-child' ), 'type' => 'textarea', 'rows' => 2 ),
		'oss_program_experience_items'   => array( 'label' => __( 'Bullet points', 'astra-child' ), 'type' => 'textarea', 'rows' => 5, 'ph' => "One per line" ),
		'oss_program_outcomes_heading'   => array( 'label' => __( 'Heading', 'astra-child' ), 'type' => 'text', 'ph' => "What You'll Gain", 'group' => __( "Section: What You'll Gain", 'astra-child' ) ),
		'oss_program_outcomes_items'     => array( 'label' => __( 'Bullet points', 'astra-child' ), 'type' => 'textarea', 'rows' => 5, 'ph' => "One per line" ),
	);
}

function oss_child_program_meta_box_html( $post ) {
	wp_nonce_field( 'oss_program_save', 'oss_program_nonce' );
	echo '<table class="form-table">';
	foreach ( oss_child_program_fields() as $key => $f ) {
		if ( ! empty( $f['group'] ) ) {
			echo '<tr><th colspan="2" style="padding-bottom:0;"><strong style="font-size:13px;color:#1d2327;">' . esc_html( $f['group'] ) . '</strong></th></tr>';
		}
		$val = get_post_meta( $post->ID, $key, true );
		$ph  = isset( $f['ph'] ) ? $f['ph'] : '';
		echo '<tr><th scope="row"><label for="' . esc_attr( $key ) . '">' . esc_html( $f['label'] ) . '</label></th><td>';
		if ( 'textarea' === $f['type'] ) {
			echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="' . esc_attr( isset( $f['rows'] ) ? $f['rows'] : 3 ) . '" class="large-text" placeholder="' . esc_attr( $ph ) . '">' . esc_textarea( $val ) . '</textarea>';
		} else {
			echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" class="large-text" placeholder="' . esc_attr( $ph ) . '" />';
		}
		echo '</td></tr>';
	}
	echo '</table>';
}

function oss_child_save_program_meta( $post_id ) {
	if ( ! isset( $_POST['oss_program_nonce'] ) || ! wp_verify_nonce( $_POST['oss_program_nonce'], 'oss_program_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( oss_child_program_fields() as $key => $f ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$value = 'textarea' === $f['type']
			? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) )
			: sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
		update_post_meta( $post_id, $key, $value );
	}
}
add_action( 'save_post_oss_program', 'oss_child_save_program_meta' );
