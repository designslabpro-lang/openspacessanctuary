<?php
/**
 * Events custom post type — lets admins add events from WP without a plugin.
 * If a dedicated events plugin (The Events Calendar, etc.) is later installed,
 * this can be deactivated by removing the register_post_type call below.
 */

defined( 'ABSPATH' ) || exit;

function oss_child_register_event_cpt() {
	register_post_type( 'oss_event', array(
		'labels' => array(
			'name'               => __( 'Events', 'astra-child' ),
			'singular_name'      => __( 'Event', 'astra-child' ),
			'add_new_item'       => __( 'Add New Event', 'astra-child' ),
			'edit_item'          => __( 'Edit Event', 'astra-child' ),
			'all_items'          => __( 'Events', 'astra-child' ),
			'menu_name'          => __( 'Events', 'astra-child' ),
		),
		'public'       => true,
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'events' ),
		'menu_icon'    => 'dashicons-calendar-alt',
		'show_in_rest' => true,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
	) );
}
add_action( 'init', 'oss_child_register_event_cpt' );

function oss_child_event_meta_box() {
	add_meta_box( 'oss_event_details', __( 'Event Details', 'astra-child' ), 'oss_child_event_meta_box_html', 'oss_event', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'oss_child_event_meta_box' );

function oss_child_event_meta_box_html( $post ) {
	wp_nonce_field( 'oss_event_save', 'oss_event_nonce' );
	$fields = array(
		'oss_event_date'          => __( 'Date', 'astra-child' ),
		'oss_event_time'          => __( 'Time', 'astra-child' ),
		'oss_event_location'      => __( 'Location', 'astra-child' ),
		'oss_event_registration'  => __( 'Registration URL', 'astra-child' ),
	);
	echo '<table class="form-table">';
	foreach ( $fields as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true );
		$type  = ( 'oss_event_date' === $key ) ? 'date' : ( 'oss_event_time' === $key ? 'time' : ( 'oss_event_registration' === $key ? 'url' : 'text' ) );
		printf(
			'<tr><th><label for="%1$s">%2$s</label></th><td><input type="%3$s" id="%1$s" name="%1$s" value="%4$s" class="regular-text" /></td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $type ),
			esc_attr( $value )
		);
	}
	echo '</table>';
}

function oss_child_save_event_meta( $post_id ) {
	if ( ! isset( $_POST['oss_event_nonce'] ) || ! wp_verify_nonce( $_POST['oss_event_nonce'], 'oss_event_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$keys = array( 'oss_event_date', 'oss_event_time', 'oss_event_location', 'oss_event_registration' );
	foreach ( $keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$value = ( 'oss_event_registration' === $key ) ? esc_url_raw( wp_unslash( $_POST[ $key ] ) ) : sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_oss_event', 'oss_child_save_event_meta' );
