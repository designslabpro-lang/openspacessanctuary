<?php
/**
 * Live Page Builder — REST API.
 *
 * Read and write a page's builder document. Every request is capability- and
 * nonce-checked; the body is run through the schema sanitizer before it is
 * ever stored. Saving never touches the front end until the page uses the
 * builder template, and never creates post revisions.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', 'oss_lpb_register_routes' );

function oss_lpb_register_routes() {
	register_rest_route( 'oss-lpb/v1', '/doc/(?P<id>\d+)', array(
		array(
			'methods'             => 'GET',
			'callback'            => 'oss_lpb_rest_get',
			'permission_callback' => 'oss_lpb_rest_permission',
			'args'                => array( 'id' => array( 'validate_callback' => 'absint' ) ),
		),
		array(
			'methods'             => 'POST',
			'callback'            => 'oss_lpb_rest_save',
			'permission_callback' => 'oss_lpb_rest_permission',
			'args'                => array( 'id' => array( 'validate_callback' => 'absint' ) ),
		),
	) );
}

/**
 * Capability + nonce gate for every builder request.
 */
function oss_lpb_rest_permission( WP_REST_Request $request ) {
	$post_id = (int) $request['id'];
	if ( ! oss_lpb_user_can( $post_id ) ) {
		return new WP_Error( 'oss_lpb_forbidden', __( 'You are not allowed to edit this page.', 'astra-child' ), array( 'status' => 403 ) );
	}
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'oss_lpb_bad_nonce', __( 'Invalid or expired session. Please reload the editor.', 'astra-child' ), array( 'status' => 403 ) );
	}
	return true;
}

function oss_lpb_rest_get( WP_REST_Request $request ) {
	$post_id = (int) $request['id'];
	return rest_ensure_response( array(
		'id'       => $post_id,
		'document' => oss_lpb_get_document( $post_id ),
		'autosave' => (bool) get_post_meta( $post_id, OSS_LPB_AUTOSAVE_META, true ),
	) );
}

function oss_lpb_rest_save( WP_REST_Request $request ) {
	$post_id = (int) $request['id'];
	$body    = $request->get_json_params();

	if ( ! is_array( $body ) || ! isset( $body['document'] ) || ! is_array( $body['document'] ) ) {
		return new WP_Error( 'oss_lpb_bad_body', __( 'Malformed document.', 'astra-child' ), array( 'status' => 400 ) );
	}

	$clean    = oss_lpb_sanitize_document( $body['document'] );
	$autosave = ! empty( $body['autosave'] );
	$meta_key = $autosave ? OSS_LPB_AUTOSAVE_META : OSS_LPB_META;

	update_post_meta( $post_id, $meta_key, $clean );

	// A real save supersedes any pending autosave draft.
	if ( ! $autosave ) {
		delete_post_meta( $post_id, OSS_LPB_AUTOSAVE_META );
	}

	return rest_ensure_response( array(
		'saved'    => true,
		'autosave' => $autosave,
		'document' => $clean,
		'time'     => current_time( 'H:i:s' ),
	) );
}
