<?php
/**
 * Contact Page Content — admin editor screen.
 * Appearance → Contact Page. Plain WordPress Settings API + the core media
 * picker; no Elementor, no third-party page builder involved.
 */

defined( 'ABSPATH' ) || exit;

/**
 * "Edit Page" admin bar shortcut — jumps straight to the Contact Page
 * Content editor when viewing a page using the Contact custom template.
 */
function oss_contact_content_admin_bar( $wp_admin_bar ) {
	if ( ! current_user_can( 'edit_theme_options' ) || ! is_page_template( 'page-templates/template-contact-custom.php' ) ) {
		return;
	}
	$wp_admin_bar->add_node( array(
		'id'    => 'oss-edit-contact',
		'title' => __( '✎ Edit Page Content', 'astra-child' ),
		'href'  => admin_url( 'themes.php?page=oss-contact-content' ),
	) );
}
add_action( 'admin_bar_menu', 'oss_contact_content_admin_bar', 81 );

function oss_contact_content_menu() {
	add_theme_page(
		__( 'Contact Page', 'astra-child' ),
		__( 'Contact Page', 'astra-child' ),
		'edit_theme_options',
		'oss-contact-content',
		'oss_contact_content_page'
	);
}
add_action( 'admin_menu', 'oss_contact_content_menu' );

function oss_contact_content_register() {
	register_setting( 'oss_contact_content_group', OSS_CONTACT_OPTION, 'oss_contact_content_sanitize' );
}
add_action( 'admin_init', 'oss_contact_content_register' );

function oss_contact_content_sanitize( $input ) {
	$defaults = oss_contact_content_defaults();
	$clean    = array();

	foreach ( $defaults as $key => $default ) {
		if ( false !== strpos( $key, '_image_id' ) ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? absint( $input[ $key ] ) : 0;
			continue;
		}

		if ( false !== strpos( $key, '_url' ) ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? esc_url_raw( trim( $input[ $key ] ) ) : $default;
			continue;
		}

		if ( false !== strpos( $default, "\n" ) ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( wp_unslash( $input[ $key ] ) ) : $default;
			continue;
		}

		$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : $default;
	}

	return $clean;
}

function oss_contact_content_field_row( $key, $label, $type = 'text' ) {
	$value = oss_contact_get( $key );
	$name  = OSS_CONTACT_OPTION . '[' . $key . ']';
	echo '<tr><th scope="row"><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
	if ( 'textarea' === $type ) {
		echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" rows="5" class="large-text">' . esc_textarea( $value ) . '</textarea>';
	} else {
		echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="large-text">';
	}
	echo '</td></tr>';
}

function oss_contact_content_image_row( $key, $label ) {
	$id  = (int) oss_contact_get( $key );
	$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';
	echo '<div class="oss-image-field" data-key="' . esc_attr( $key ) . '">';
	echo '<img src="' . esc_url( $src ) . '" style="max-width:180px;height:auto;display:' . ( $src ? 'block' : 'none' ) . ';margin-bottom:8px;border:1px solid #ddd;">';
	echo '<input type="hidden" name="' . esc_attr( OSS_CONTACT_OPTION . '[' . $key . ']' ) . '" class="oss-image-field__id" value="' . esc_attr( $id ) . '">';
	echo '<p><button type="button" class="button oss-image-field__select">' . esc_html__( 'Select Image', 'astra-child' ) . '</button> ';
	echo '<button type="button" class="button oss-image-field__remove"' . ( $src ? '' : ' style="display:none;"' ) . '>' . esc_html__( 'Remove', 'astra-child' ) . '</button></p>';
	echo '</div></td></tr>';
}

function oss_contact_content_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Contact Page Content', 'astra-child' ); ?></h1>
		<p><?php esc_html_e( 'Edit every piece of Contact page text and imagery here — no page builder, no code. The address/phone/hours and the form fields themselves come from the Contact Info / Contact Form shortcodes used elsewhere on the site.', 'astra-child' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'oss_contact_content_group' ); ?>

			<h2><?php esc_html_e( 'Page Banner', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_contact_content_field_row( 'hero_eyebrow', __( 'Eyebrow', 'astra-child' ) );
				oss_contact_content_field_row( 'hero_heading', __( 'Heading', 'astra-child' ) );
				oss_contact_content_field_row( 'hero_body', __( 'Body', 'astra-child' ), 'textarea' );
				oss_contact_content_image_row( 'hero_image_id', __( 'Background Image', 'astra-child' ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Info & Form Panels', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_contact_content_field_row( 'info_heading', __( 'Info Panel Heading', 'astra-child' ) );
				oss_contact_content_field_row( 'form_heading', __( 'Form Panel Heading', 'astra-child' ) );
				oss_contact_content_field_row( 'form_shortcode', __( 'Form Shortcode', 'astra-child' ) );
				?>
				<tr><th></th><td><p class="description"><?php esc_html_e( 'The form plugin shortcode to render, e.g. [gravityform id="1" title="false"]. Leave empty to show the built-in placeholder.', 'astra-child' ); ?></p></td></tr>
			</table>

			<h2><?php esc_html_e( 'Final CTA', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_contact_content_field_row( 'cta_heading', __( 'Heading', 'astra-child' ) );
				oss_contact_content_field_row( 'cta_body', __( 'Body', 'astra-child' ), 'textarea' );
				oss_contact_content_field_row( 'cta_btn_text', __( 'Button Text', 'astra-child' ) );
				oss_contact_content_field_row( 'cta_btn_url', __( 'Button Link', 'astra-child' ) );
				?>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function oss_contact_content_admin_assets( $hook ) {
	if ( 'appearance_page_oss-contact-content' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_add_inline_script( 'jquery-core', "
		jQuery(function($){
			$('.oss-image-field__select').on('click', function(e){
				e.preventDefault();
				var wrap = $(this).closest('.oss-image-field');
				var frame = wp.media({ title: 'Select Image', multiple: false });
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					wrap.find('.oss-image-field__id').val(att.id);
					wrap.find('img').attr('src', att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url).show();
					wrap.find('.oss-image-field__remove').show();
				});
				frame.open();
			});
			$('.oss-image-field__remove').on('click', function(e){
				e.preventDefault();
				var wrap = $(this).closest('.oss-image-field');
				wrap.find('.oss-image-field__id').val('');
				wrap.find('img').hide();
				$(this).hide();
			});
		});
	" );
}
add_action( 'admin_enqueue_scripts', 'oss_contact_content_admin_assets' );
