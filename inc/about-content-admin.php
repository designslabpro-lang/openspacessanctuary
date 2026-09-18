<?php
/**
 * About Page Content — admin editor screen.
 * Appearance → About Page. Plain WordPress Settings API + the core media
 * picker; no Elementor, no third-party page builder involved.
 */

defined( 'ABSPATH' ) || exit;

/**
 * "Edit Page" admin bar shortcut — jumps straight to the About Page
 * Content editor when viewing a page using the About custom template.
 */
function oss_about_content_admin_bar( $wp_admin_bar ) {
	if ( ! current_user_can( 'edit_theme_options' ) || ! is_page_template( 'page-templates/template-about-custom.php' ) ) {
		return;
	}
	$wp_admin_bar->add_node( array(
		'id'    => 'oss-edit-about',
		'title' => __( '✎ Edit Page Content', 'astra-child' ),
		'href'  => admin_url( 'themes.php?page=oss-about-content' ),
	) );
}
add_action( 'admin_bar_menu', 'oss_about_content_admin_bar', 81 );

function oss_about_content_menu() {
	add_theme_page(
		__( 'About Page', 'astra-child' ),
		__( 'About Page', 'astra-child' ),
		'edit_theme_options',
		'oss-about-content',
		'oss_about_content_page'
	);
}
add_action( 'admin_menu', 'oss_about_content_menu' );

function oss_about_content_register() {
	register_setting( 'oss_about_content_group', OSS_ABOUT_OPTION, 'oss_about_content_sanitize' );
}
add_action( 'admin_init', 'oss_about_content_register' );

function oss_about_content_sanitize( $input ) {
	$defaults = oss_about_content_defaults();
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

function oss_about_content_field_row( $key, $label, $type = 'text' ) {
	$value = oss_about_get( $key );
	$name  = OSS_ABOUT_OPTION . '[' . $key . ']';
	echo '<tr><th scope="row"><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
	if ( 'textarea' === $type ) {
		echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" rows="5" class="large-text">' . esc_textarea( $value ) . '</textarea>';
	} else {
		echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="large-text">';
	}
	echo '</td></tr>';
}

function oss_about_content_image_row( $key, $label ) {
	$id  = (int) oss_about_get( $key );
	$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';
	echo '<div class="oss-image-field" data-key="' . esc_attr( $key ) . '">';
	echo '<img src="' . esc_url( $src ) . '" style="max-width:180px;height:auto;display:' . ( $src ? 'block' : 'none' ) . ';margin-bottom:8px;border:1px solid #ddd;">';
	echo '<input type="hidden" name="' . esc_attr( OSS_ABOUT_OPTION . '[' . $key . ']' ) . '" class="oss-image-field__id" value="' . esc_attr( $id ) . '">';
	echo '<p><button type="button" class="button oss-image-field__select">' . esc_html__( 'Select Image', 'astra-child' ) . '</button> ';
	echo '<button type="button" class="button oss-image-field__remove"' . ( $src ? '' : ' style="display:none;"' ) . '>' . esc_html__( 'Remove', 'astra-child' ) . '</button></p>';
	echo '</div></td></tr>';
}

function oss_about_content_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'About Page Content', 'astra-child' ); ?></h1>
		<p><?php esc_html_e( 'Edit every piece of About page text and imagery here — no page builder, no code.', 'astra-child' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'oss_about_content_group' ); ?>

			<h2><?php esc_html_e( 'Page Banner', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_about_content_field_row( 'hero_eyebrow', __( 'Eyebrow', 'astra-child' ) );
				oss_about_content_field_row( 'hero_heading', __( 'Heading', 'astra-child' ) );
				oss_about_content_field_row( 'hero_body', __( 'Body', 'astra-child' ), 'textarea' );
				oss_about_content_image_row( 'hero_image_id', __( 'Background Image', 'astra-child' ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Our Story', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_about_content_field_row( 'story_eyebrow', __( 'Eyebrow', 'astra-child' ) );
				oss_about_content_field_row( 'story_heading', __( 'Heading', 'astra-child' ) );
				oss_about_content_field_row( 'story_body', __( 'Body', 'astra-child' ), 'textarea' );
				oss_about_content_image_row( 'story_image_id', __( 'Photo', 'astra-child' ) );
				?>
			</table>

			<h2><?php esc_html_e( 'The Healing Power of Horses', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_about_content_field_row( 'philosophy_eyebrow', __( 'Eyebrow', 'astra-child' ) );
				oss_about_content_field_row( 'philosophy_heading', __( 'Heading', 'astra-child' ) );
				oss_about_content_field_row( 'philosophy_body', __( 'Body', 'astra-child' ), 'textarea' );
				oss_about_content_field_row( 'philosophy_quote', __( 'Pull Quote', 'astra-child' ), 'textarea' );
				oss_about_content_image_row( 'philosophy_image_id', __( 'Photo', 'astra-child' ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Meet Our Founder', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_about_content_field_row( 'founder_heading', __( 'Eyebrow', 'astra-child' ) );
				oss_about_content_field_row( 'founder_name', __( 'Name', 'astra-child' ) );
				oss_about_content_field_row( 'founder_body', __( 'Biography', 'astra-child' ), 'textarea' );
				?>
			</table>

			<h2><?php esc_html_e( 'Final CTA', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_about_content_field_row( 'final_heading', __( 'Heading', 'astra-child' ) );
				oss_about_content_field_row( 'final_body', __( 'Body', 'astra-child' ), 'textarea' );
				oss_about_content_field_row( 'final_btn_text', __( 'Button Text', 'astra-child' ) );
				oss_about_content_field_row( 'final_btn_url', __( 'Button Link', 'astra-child' ) );
				?>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function oss_about_content_admin_assets( $hook ) {
	if ( 'appearance_page_oss-about-content' !== $hook ) {
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
add_action( 'admin_enqueue_scripts', 'oss_about_content_admin_assets' );
