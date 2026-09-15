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

function oss_child_program_meta_box_html( $post ) {
	wp_nonce_field( 'oss_program_save', 'oss_program_nonce' );
	$audience = get_post_meta( $post->ID, 'oss_program_audience', true );
	echo '<table class="form-table"><tr><th><label for="oss_program_audience">' . esc_html__( 'Audience', 'astra-child' ) . '</label></th>';
	echo '<td><input type="text" id="oss_program_audience" name="oss_program_audience" value="' . esc_attr( $audience ) . '" class="regular-text" placeholder="e.g. Youth, Veterans, Adults" /></td></tr></table>';
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
	if ( isset( $_POST['oss_program_audience'] ) ) {
		update_post_meta( $post_id, 'oss_program_audience', sanitize_text_field( wp_unslash( $_POST['oss_program_audience'] ) ) );
	}
}
add_action( 'save_post_oss_program', 'oss_child_save_program_meta' );
