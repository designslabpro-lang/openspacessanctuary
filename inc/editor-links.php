<?php
/**
 * Convenience links between a page and its content editor:
 *  - a "View page ↗" button beside the H1 on each Appearance → … Content screen
 *  - an "Edit Content" button in the front-end admin bar on each V2 page
 * So the client can hop between editing and seeing the result in one click.
 */

defined( 'ABSPATH' ) || exit;

/**
 * admin page hook => details about the front-end page it edits.
 */
function oss_editor_page_map() {
	return array(
		'appearance_page_oss-home-content'     => array( 'label' => __( 'Homepage', 'astra-child' ), 'front' => true ),
		'appearance_page_oss-about-content'    => array( 'label' => __( 'About page', 'astra-child' ), 'tpl' => 'page-templates/template-about-v2.php', 'slug' => 'about' ),
		'appearance_page_oss-herd-content'     => array( 'label' => __( 'Meet the Herd page', 'astra-child' ), 'tpl' => 'page-templates/template-herd-v2.php', 'slug' => 'meet-the-herd' ),
		'appearance_page_oss-involved-content' => array( 'label' => __( 'Get Involved page', 'astra-child' ), 'tpl' => 'page-templates/template-involved-v2.php', 'slug' => 'get-involved' ),
		'appearance_page_oss-faq-content'      => array( 'label' => __( 'FAQ page', 'astra-child' ), 'tpl' => 'page-templates/template-faq-v2.php', 'slug' => 'faqs' ),
		'appearance_page_oss-contact-content'  => array( 'label' => __( 'Contact page', 'astra-child' ), 'tpl' => 'page-templates/template-contact-v2.php', 'slug' => 'contact' ),
	);
}

function oss_page_url_for( $cfg ) {
	if ( ! empty( $cfg['front'] ) ) {
		return home_url( '/' );
	}
	if ( ! empty( $cfg['tpl'] ) ) {
		$ids = get_posts( array(
			'post_type'      => 'page',
			'numberposts'    => 1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'meta_key'       => '_wp_page_template',
			'meta_value'     => $cfg['tpl'],
			'no_found_rows'  => true,
		) );
		if ( ! empty( $ids ) ) {
			return get_permalink( $ids[0] );
		}
	}
	if ( ! empty( $cfg['slug'] ) ) {
		$p = get_page_by_path( $cfg['slug'] );
		if ( $p ) {
			return get_permalink( $p );
		}
		return home_url( '/' . $cfg['slug'] . '/' );
	}
	return home_url( '/' );
}

/**
 * "View page ↗" button beside the editor's H1.
 */
add_action( 'admin_enqueue_scripts', 'oss_editor_view_link', 30 );
function oss_editor_view_link( $hook ) {
	$map = oss_editor_page_map();
	if ( ! isset( $map[ $hook ] ) ) {
		return;
	}
	$url  = oss_page_url_for( $map[ $hook ] );
	$html = ' <a class="page-title-action oss-view-page" target="_blank" rel="noopener" href="' . esc_url( $url ) . '">'
		. esc_html( sprintf( __( 'View %s', 'astra-child' ), $map[ $hook ]['label'] ) ) . ' &#8599;</a>';
	$js   = 'jQuery(function($){var h=$(".wrap > h1").first();if(h.length&&!h.find(".oss-view-page").length){h.append(' . wp_json_encode( $html ) . ');}});';
	wp_add_inline_script( 'jquery-core', $js );
}

/**
 * "Edit Content" node in the front-end admin bar on each V2 template page
 * (the homepage has its own node already).
 */
add_action( 'admin_bar_menu', 'oss_frontend_edit_content_bar', 90 );
function oss_frontend_edit_content_bar( $bar ) {
	if ( is_admin() || ! is_page() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$tpl_map = array(
		'page-templates/template-about-v2.php'    => 'oss-about-content',
		'page-templates/template-herd-v2.php'     => 'oss-herd-content',
		'page-templates/template-involved-v2.php' => 'oss-involved-content',
		'page-templates/template-faq-v2.php'      => 'oss-faq-content',
		'page-templates/template-contact-v2.php'  => 'oss-contact-content',
	);
	$tpl = get_page_template_slug( get_queried_object_id() );
	if ( ! isset( $tpl_map[ $tpl ] ) ) {
		return;
	}
	$bar->add_node( array(
		'id'    => 'oss-edit-content',
		'title' => __( 'Edit Content', 'astra-child' ),
		'href'  => admin_url( 'themes.php?page=' . $tpl_map[ $tpl ] ),
		'meta'  => array( 'title' => __( "Edit this page's content", 'astra-child' ) ),
	) );
}
