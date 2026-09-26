<?php
/**
 * FAQ Page — admin editor (Appearance → FAQ Page). Settings API + shared
 * collapsible-section UI + a repeatable question/answer list.
 */

defined( 'ABSPATH' ) || exit;

function oss_faq_content_admin_bar( $wp_admin_bar ) {
	if ( ! current_user_can( 'edit_theme_options' ) || ! is_page_template( 'page-templates/template-faq-v2.php' ) ) {
		return;
	}
	$wp_admin_bar->add_node( array(
		'id'    => 'oss-edit-faq',
		'title' => __( '✎ Edit Page Content', 'astra-child' ),
		'href'  => admin_url( 'themes.php?page=oss-faq-content' ),
	) );
}
add_action( 'admin_bar_menu', 'oss_faq_content_admin_bar', 81 );

function oss_faq_content_menu() {
	add_theme_page(
		__( 'FAQ Page', 'astra-child' ),
		__( 'FAQ Page', 'astra-child' ),
		'edit_theme_options',
		'oss-faq-content',
		'oss_faq_content_page'
	);
}
add_action( 'admin_menu', 'oss_faq_content_menu' );

function oss_faq_content_register() {
	register_setting( 'oss_faq_content_group', OSS_FAQ_OPTION, 'oss_faq_content_sanitize' );
}
add_action( 'admin_init', 'oss_faq_content_register' );

function oss_faq_content_sanitize( $input ) {
	$defaults = oss_faq_content_defaults();
	$clean    = array();

	foreach ( array( 'hero_eyebrow', 'hero_heading', 'hero_body', 'hero_image_pos', 'hero_image_fit' ) as $key ) {
		$clean[ $key ] = isset( $input[ $key ] )
			? ( 'hero_body' === $key ? sanitize_textarea_field( wp_unslash( $input[ $key ] ) ) : sanitize_text_field( wp_unslash( $input[ $key ] ) ) )
			: $defaults[ $key ];
	}
	$clean['hero_image_id'] = isset( $input['hero_image_id'] ) ? absint( $input['hero_image_id'] ) : 0;

	$rows = array();
	if ( isset( $input['faqs'] ) && is_array( $input['faqs'] ) ) {
		foreach ( $input['faqs'] as $row ) {
			$q = isset( $row['q'] ) ? sanitize_text_field( wp_unslash( $row['q'] ) ) : '';
			if ( '' === $q ) {
				continue;
			}
			$rows[] = array(
				'q' => $q,
				'a' => isset( $row['a'] ) ? sanitize_textarea_field( wp_unslash( $row['a'] ) ) : '',
			);
		}
	}
	$clean['faqs'] = $rows;

	return $clean;
}

function oss_faq_content_faq_row( $i, $row ) {
	$row = wp_parse_args( $row, array( 'q' => '', 'a' => '' ) );
	$n   = OSS_FAQ_OPTION . '[faqs][' . $i . ']';
	?>
	<div class="oss-faq-row" style="border:1px solid #ccd0d4;background:#fff;padding:12px 16px;margin-bottom:12px;">
		<p><label><strong><?php esc_html_e( 'Question', 'astra-child' ); ?></strong><br><input type="text" class="large-text" name="<?php echo esc_attr( $n ); ?>[q]" value="<?php echo esc_attr( $row['q'] ); ?>"></label></p>
		<p><label><strong><?php esc_html_e( 'Answer', 'astra-child' ); ?></strong><br><textarea class="large-text" rows="3" name="<?php echo esc_attr( $n ); ?>[a]"><?php echo esc_textarea( $row['a'] ); ?></textarea></label></p>
		<p style="margin:0;"><button type="button" class="button-link-delete oss-faq-row__remove"><?php esc_html_e( 'Remove this question', 'astra-child' ); ?></button></p>
	</div>
	<?php
}

function oss_faq_content_page() {
	$o    = OSS_FAQ_OPTION;
	$faqs = (array) oss_faq_get( 'faqs' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'FAQ Page Content', 'astra-child' ); ?></h1>
		<p><?php esc_html_e( 'Edit the FAQ page banner and the questions & answers here — no page builder, no code.', 'astra-child' ); ?></p>
		<?php oss_cadmin_toolbar(); ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'oss_faq_content_group' ); ?>

			<?php oss_cadmin_section( 'page-banner', __( 'Page Banner', 'astra-child' ) ); ?>
			<table class="form-table">
				<?php
				oss_cadmin_field_row( $o, 'hero_eyebrow', oss_faq_get( 'hero_eyebrow' ), __( 'Eyebrow', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'hero_heading', oss_faq_get( 'hero_heading' ), __( 'Heading', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'hero_body', oss_faq_get( 'hero_body' ), __( 'Intro', 'astra-child' ), 'textarea' );
				oss_cadmin_bg_image_row( $o, 'hero_image_id', oss_faq_get( 'hero_image_id' ), oss_faq_get( 'hero_image_pos' ), oss_faq_get( 'hero_image_fit' ), __( 'Background Image', 'astra-child' ) );
				?>
			</table>

			<?php oss_cadmin_section( 'questions', __( 'Questions & Answers', 'astra-child' ) ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Questions', 'astra-child' ); ?></th>
					<td>
						<p class="description" style="margin-bottom:10px;"><?php esc_html_e( 'Each question is a toggle on the page. A question with no answer yet shows a "Request Information" link to the contact page. Questions with no text are dropped on save.', 'astra-child' ); ?></p>
						<div id="oss-faq-rows">
							<?php foreach ( array_values( $faqs ) as $i => $row ) { oss_faq_content_faq_row( $i, $row ); } ?>
						</div>
						<p><button type="button" class="button button-secondary" id="oss-faq-add"><?php esc_html_e( '+ Add Question', 'astra-child' ); ?></button></p>
						<template id="oss-faq-row-template"><?php oss_faq_content_faq_row( '__i__', array() ); ?></template>
					</td>
				</tr>
			</table>

			<?php oss_cadmin_sections_end(); ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function oss_faq_content_admin_assets( $hook ) {
	if ( 'appearance_page_oss-faq-content' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	oss_cadmin_toggle_assets();
	oss_cadmin_media_js();
	$js = <<<'JS'
		jQuery(function($){
			$('#oss-faq-add').on('click', function(){
				$('#oss-faq-rows').append($('#oss-faq-row-template').html().replace(/__i__/g, 'n' + Date.now()));
			});
			$(document).on('click', '.oss-faq-row__remove', function(){ $(this).closest('.oss-faq-row').remove(); });
		});
JS;
	wp_add_inline_script( 'jquery-core', $js );
}
add_action( 'admin_enqueue_scripts', 'oss_faq_content_admin_assets' );
