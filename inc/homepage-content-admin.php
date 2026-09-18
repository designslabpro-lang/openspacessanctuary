<?php
/**
 * Homepage Content — admin editor screen.
 * Appearance → Homepage Content. Plain WordPress Settings API + the core
 * media picker; no Elementor, no third-party page builder involved.
 */

defined( 'ABSPATH' ) || exit;

/**
 * "Edit Page" admin bar shortcut — jumps straight to the Homepage
 * Content editor when viewing the front page, the same way Elementor
 * shows "Edit with Elementor" on its own pages.
 */
function oss_home_content_admin_bar( $wp_admin_bar ) {
	if ( ! is_front_page() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$wp_admin_bar->add_node( array(
		'id'    => 'oss-edit-homepage',
		'title' => __( '✎ Edit Page Content', 'astra-child' ),
		'href'  => admin_url( 'themes.php?page=oss-home-content' ),
	) );
}
add_action( 'admin_bar_menu', 'oss_home_content_admin_bar', 81 );

function oss_home_content_menu() {
	add_theme_page(
		__( 'Homepage Content', 'astra-child' ),
		__( 'Homepage Content', 'astra-child' ),
		'edit_theme_options',
		'oss-home-content',
		'oss_home_content_page'
	);
}
add_action( 'admin_menu', 'oss_home_content_menu' );

function oss_home_content_register() {
	register_setting( 'oss_home_content_group', OSS_HOME_OPTION, 'oss_home_content_sanitize' );
}
add_action( 'admin_init', 'oss_home_content_register' );

function oss_home_content_sanitize( $input ) {
	$defaults = oss_home_content_defaults();
	$clean    = array();

	foreach ( $defaults as $key => $default ) {
		if ( 'serve_items' === $key ) {
			$items = array();
			if ( isset( $input['serve_items'] ) && is_array( $input['serve_items'] ) ) {
				foreach ( $input['serve_items'] as $row ) {
					$items[] = array(
						'icon'  => isset( $row['icon'] ) ? sanitize_key( $row['icon'] ) : 'compass',
						'label' => isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '',
					);
				}
			}
			$clean['serve_items'] = $items ? $items : $default;
			continue;
		}

		if ( 'testimonials' === $key ) {
			$rows = array();
			if ( isset( $input['testimonials'] ) && is_array( $input['testimonials'] ) ) {
				foreach ( $input['testimonials'] as $row ) {
					$rows[] = array(
						'quote' => isset( $row['quote'] ) ? sanitize_textarea_field( $row['quote'] ) : '',
						'name'  => isset( $row['name'] ) ? sanitize_text_field( $row['name'] ) : '',
					);
				}
			}
			$clean['testimonials'] = $rows ? $rows : $default;
			continue;
		}

		if ( false !== strpos( $key, '_image_id' ) ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? absint( $input[ $key ] ) : 0;
			continue;
		}

		if ( false !== strpos( $key, '_url' ) ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? esc_url_raw( trim( $input[ $key ] ) ) : $default;
			continue;
		}

		// Multi-paragraph fields (contain \n in their default) use a textarea; sanitize as textarea to keep line breaks.
		if ( false !== strpos( $default, "\n" ) ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( wp_unslash( $input[ $key ] ) ) : $default;
			continue;
		}

		$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : $default;
	}

	return $clean;
}

function oss_home_content_field_row( $key, $label, $type = 'text' ) {
	$value = oss_home_get( $key );
	$name  = OSS_HOME_OPTION . '[' . $key . ']';
	echo '<tr><th scope="row"><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
	if ( 'textarea' === $type ) {
		echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" rows="5" class="large-text">' . esc_textarea( $value ) . '</textarea>';
	} else {
		echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="large-text">';
	}
	echo '</td></tr>';
}

function oss_home_content_image_row( $key, $label ) {
	$id  = (int) oss_home_get( $key );
	$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';
	echo '<div class="oss-image-field" data-key="' . esc_attr( $key ) . '">';
	echo '<img src="' . esc_url( $src ) . '" style="max-width:180px;height:auto;display:' . ( $src ? 'block' : 'none' ) . ';margin-bottom:8px;border:1px solid #ddd;">';
	echo '<input type="hidden" name="' . esc_attr( OSS_HOME_OPTION . '[' . $key . ']' ) . '" class="oss-image-field__id" value="' . esc_attr( $id ) . '">';
	echo '<p><button type="button" class="button oss-image-field__select">' . esc_html__( 'Select Image', 'astra-child' ) . '</button> ';
	echo '<button type="button" class="button oss-image-field__remove"' . ( $src ? '' : ' style="display:none;"' ) . '>' . esc_html__( 'Remove', 'astra-child' ) . '</button></p>';
	echo '</div></td></tr>';
}

function oss_home_content_page() {
	$icons   = oss_home_icon_library();
	$items   = oss_home_get( 'serve_items' );
	$stories = oss_home_get( 'testimonials' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Homepage Content', 'astra-child' ); ?></h1>
		<p><?php esc_html_e( 'Edit every piece of homepage text and imagery here — no page builder, no code. Content here mirrors the approved copy; wording fields are still editable if something needs a small correction.', 'astra-child' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'oss_home_content_group' ); ?>

			<h2><?php esc_html_e( 'Hero', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_home_content_field_row( 'hero_eyebrow', __( 'Eyebrow', 'astra-child' ) );
				oss_home_content_field_row( 'hero_heading', __( 'Supporting Heading', 'astra-child' ) );
				oss_home_content_field_row( 'hero_body', __( 'Body', 'astra-child' ), 'textarea' );
				oss_home_content_field_row( 'hero_btn1_text', __( 'Button 1 Text', 'astra-child' ) );
				oss_home_content_field_row( 'hero_btn1_url', __( 'Button 1 Link', 'astra-child' ) );
				oss_home_content_field_row( 'hero_btn2_text', __( 'Button 2 Text', 'astra-child' ) );
				oss_home_content_field_row( 'hero_btn2_url', __( 'Button 2 Link', 'astra-child' ) );
				oss_home_content_image_row( 'hero_image_id', __( 'Background Image', 'astra-child' ) );
				?>
			</table>

			<h2><?php esc_html_e( 'The Healing Power of Horses', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_home_content_field_row( 'power_heading', __( 'Heading', 'astra-child' ) );
				oss_home_content_field_row( 'power_body1', __( 'Paragraph 1', 'astra-child' ), 'textarea' );
				oss_home_content_field_row( 'power_body2', __( 'Paragraph 2', 'astra-child' ), 'textarea' );
				oss_home_content_field_row( 'power_quote', __( 'Pull Quote', 'astra-child' ), 'textarea' );
				oss_home_content_field_row( 'power_caption', __( 'Photo Caption', 'astra-child' ) );
				oss_home_content_image_row( 'power_image_id', __( 'Photo', 'astra-child' ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Who We Serve', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_home_content_field_row( 'serve_heading', __( 'Heading', 'astra-child' ) );
				oss_home_content_field_row( 'serve_intro', __( 'Intro', 'astra-child' ), 'textarea' );
				?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Audience Cards', 'astra-child' ); ?></th>
					<td>
						<table class="widefat" style="max-width:700px;">
							<thead><tr><th style="width:140px;"><?php esc_html_e( 'Icon', 'astra-child' ); ?></th><th><?php esc_html_e( 'Label', 'astra-child' ); ?></th></tr></thead>
							<tbody>
							<?php foreach ( $items as $i => $row ) : ?>
								<tr>
									<td>
										<select name="<?php echo esc_attr( OSS_HOME_OPTION . '[serve_items][' . $i . '][icon]' ); ?>">
											<?php foreach ( array_keys( $icons ) as $icon_key ) : ?>
												<option value="<?php echo esc_attr( $icon_key ); ?>" <?php selected( $row['icon'], $icon_key ); ?>><?php echo esc_html( ucfirst( $icon_key ) ); ?></option>
											<?php endforeach; ?>
										</select>
									</td>
									<td><input type="text" class="large-text" name="<?php echo esc_attr( OSS_HOME_OPTION . '[serve_items][' . $i . '][label]' ); ?>" value="<?php echo esc_attr( $row['label'] ); ?>"></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</td>
				</tr>
				<?php oss_home_content_field_row( 'serve_closing', __( 'Closing Line', 'astra-child' ) ); ?>
			</table>

			<h2><?php esc_html_e( 'Our Programs', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_home_content_field_row( 'programs_heading', __( 'Heading', 'astra-child' ) );
				oss_home_content_field_row( 'programs_intro', __( 'Intro', 'astra-child' ), 'textarea' );
				?>
				<tr><th></th><td><p class="description"><?php
					printf(
						/* translators: %s: link to Programs admin screen */
						esc_html__( 'Program cards come from %s — add, edit, or reorder them there.', 'astra-child' ),
						'<a href="' . esc_url( admin_url( 'edit.php?post_type=oss_program' ) ) . '">' . esc_html__( 'Programs', 'astra-child' ) . '</a>'
					);
				?></p></td></tr>
			</table>

			<h2><?php esc_html_e( 'Meet Our Horses', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_home_content_field_row( 'horses_heading', __( 'Heading', 'astra-child' ) );
				oss_home_content_field_row( 'horses_body', __( 'Body', 'astra-child' ), 'textarea' );
				oss_home_content_field_row( 'horses_sub', __( 'Subheading / Link Text', 'astra-child' ) );
				oss_home_content_image_row( 'horses_image_id', __( 'Photo', 'astra-child' ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Our Founder', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_home_content_field_row( 'founder_heading', __( 'Heading', 'astra-child' ) );
				oss_home_content_field_row( 'founder_name', __( 'Name', 'astra-child' ) );
				oss_home_content_field_row( 'founder_body', __( 'Biography', 'astra-child' ), 'textarea' );
				oss_home_content_field_row( 'founder_btn', __( 'Button Text', 'astra-child' ) );
				oss_home_content_image_row( 'founder_image_id', __( 'Photo', 'astra-child' ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Stories of Hope', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php oss_home_content_field_row( 'stories_heading', __( 'Heading', 'astra-child' ) ); ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Testimonials', 'astra-child' ); ?></th>
					<td>
						<table class="widefat" style="max-width:700px;">
							<thead><tr><th><?php esc_html_e( 'Quote', 'astra-child' ); ?></th><th style="width:160px;"><?php esc_html_e( 'Name', 'astra-child' ); ?></th></tr></thead>
							<tbody>
							<?php foreach ( $stories as $i => $row ) : ?>
								<tr>
									<td><textarea class="large-text" rows="3" name="<?php echo esc_attr( OSS_HOME_OPTION . '[testimonials][' . $i . '][quote]' ); ?>"><?php echo esc_textarea( $row['quote'] ); ?></textarea></td>
									<td><input type="text" class="regular-text" name="<?php echo esc_attr( OSS_HOME_OPTION . '[testimonials][' . $i . '][name]' ); ?>" value="<?php echo esc_attr( $row['name'] ); ?>"></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Help Us Change Lives', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_home_content_field_row( 'donate_heading', __( 'Heading', 'astra-child' ) );
				oss_home_content_field_row( 'donate_body', __( 'Body', 'astra-child' ), 'textarea' );
				oss_home_content_field_row( 'donate_btn', __( 'Button Text', 'astra-child' ) );
				oss_home_content_field_row( 'donate_btn_url', __( 'Button Link', 'astra-child' ) );
				oss_home_content_image_row( 'donate_image_id', __( 'Background Image', 'astra-child' ) );
				?>
			</table>

			<h2><?php esc_html_e( 'Stay Connected', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_home_content_field_row( 'connect_heading', __( 'Heading', 'astra-child' ) );
				oss_home_content_field_row( 'connect_body', __( 'Body', 'astra-child' ), 'textarea' );
				?>
				<tr><th></th><td><p class="description"><?php esc_html_e( 'The signup form itself is the [oss_newsletter_signup] shortcode — connect a mailing list plugin any time and it activates automatically.', 'astra-child' ); ?></p></td></tr>
			</table>

			<h2><?php esc_html_e( 'Final CTA', 'astra-child' ); ?></h2>
			<table class="form-table">
				<?php
				oss_home_content_field_row( 'final_heading', __( 'Heading', 'astra-child' ) );
				oss_home_content_field_row( 'final_body', __( 'Body', 'astra-child' ), 'textarea' );
				oss_home_content_field_row( 'final_sub', __( 'Supporting Line', 'astra-child' ) );
				oss_home_content_field_row( 'final_btn1_text', __( 'Button 1 Text', 'astra-child' ) );
				oss_home_content_field_row( 'final_btn1_url', __( 'Button 1 Link', 'astra-child' ) );
				oss_home_content_field_row( 'final_btn2_text', __( 'Button 2 Text', 'astra-child' ) );
				oss_home_content_field_row( 'final_btn2_url', __( 'Button 2 Link', 'astra-child' ) );
				?>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function oss_home_content_admin_assets( $hook ) {
	if ( 'appearance_page_oss-home-content' !== $hook ) {
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
add_action( 'admin_enqueue_scripts', 'oss_home_content_admin_assets' );