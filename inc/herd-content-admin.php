<?php
/**
 * Meet the Herd — admin editor screen (Appearance → Meet the Herd).
 * Plain Settings API + the core media picker + the shared collapsible-section
 * UI (inc/content-admin-ui.php). No page builder.
 */

defined( 'ABSPATH' ) || exit;

function oss_herd_content_admin_bar( $wp_admin_bar ) {
	if ( ! current_user_can( 'edit_theme_options' ) || ! is_page_template( 'page-templates/template-herd-v2.php' ) ) {
		return;
	}
	$wp_admin_bar->add_node( array(
		'id'    => 'oss-edit-herd',
		'title' => __( '✎ Edit Page Content', 'astra-child' ),
		'href'  => admin_url( 'themes.php?page=oss-herd-content' ),
	) );
}
add_action( 'admin_bar_menu', 'oss_herd_content_admin_bar', 81 );

function oss_herd_content_menu() {
	add_theme_page(
		__( 'Meet the Herd', 'astra-child' ),
		__( 'Meet the Herd', 'astra-child' ),
		'edit_theme_options',
		'oss-herd-content',
		'oss_herd_content_page'
	);
}
add_action( 'admin_menu', 'oss_herd_content_menu' );

function oss_herd_content_register() {
	register_setting( 'oss_herd_content_group', OSS_HERD_OPTION, 'oss_herd_content_sanitize' );
}
add_action( 'admin_init', 'oss_herd_content_register' );

function oss_herd_content_sanitize( $input ) {
	$defaults = oss_herd_content_defaults();
	$clean    = array();
	foreach ( $defaults as $key => $default ) {
		if ( false !== strpos( $key, '_image_id' ) ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? absint( $input[ $key ] ) : 0;
		} elseif ( false !== strpos( $key, '_url' ) ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? esc_url_raw( trim( $input[ $key ] ) ) : $default;
		} elseif ( false !== strpos( (string) $default, "\n" ) ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( wp_unslash( $input[ $key ] ) ) : $default;
		} else {
			$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : $default;
		}
	}
	return $clean;
}

function oss_herd_content_page() {
	$o = OSS_HERD_OPTION;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Meet the Herd Content', 'astra-child' ); ?></h1>
		<p><?php esc_html_e( 'Edit every piece of the Meet the Herd page here — no page builder, no code. This page has its own content, so changes here no longer affect the homepage.', 'astra-child' ); ?></p>
		<?php oss_cadmin_toolbar(); ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'oss_herd_content_group' ); ?>

			<?php oss_cadmin_section( 'page-banner', __( 'Page Banner', 'astra-child' ) ); ?>
			<table class="form-table">
				<?php
				oss_cadmin_field_row( $o, 'hero_eyebrow', oss_herd_get( 'hero_eyebrow' ), __( 'Eyebrow', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'hero_heading', oss_herd_get( 'hero_heading' ), __( 'Heading', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'hero_body', oss_herd_get( 'hero_body' ), __( 'Intro', 'astra-child' ), 'textarea' );
				oss_cadmin_image_row( $o, 'hero_image_id', oss_herd_get( 'hero_image_id' ), __( 'Background Image', 'astra-child' ) );
				?>
			</table>

			<?php oss_cadmin_section( 'the-herd', __( 'The Herd', 'astra-child' ) ); ?>
			<table class="form-table">
				<?php
				oss_cadmin_field_row( $o, 'herd_eyebrow', oss_herd_get( 'herd_eyebrow' ), __( 'Eyebrow', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'herd_heading', oss_herd_get( 'herd_heading' ), __( 'Heading', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'herd_body', oss_herd_get( 'herd_body' ), __( 'Body', 'astra-child' ), 'textarea' );
				oss_cadmin_image_row( $o, 'herd_image_id', oss_herd_get( 'herd_image_id' ), __( 'Photo', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'herd_btn_text', oss_herd_get( 'herd_btn_text' ), __( 'Button Text', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'herd_btn_url', oss_herd_get( 'herd_btn_url' ), __( 'Button Link', 'astra-child' ) );
				?>
			</table>

			<?php oss_cadmin_sections_end(); ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function oss_herd_content_admin_assets( $hook ) {
	if ( 'appearance_page_oss-herd-content' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	oss_cadmin_toggle_assets();
	oss_cadmin_media_js();
}
add_action( 'admin_enqueue_scripts', 'oss_herd_content_admin_assets' );
