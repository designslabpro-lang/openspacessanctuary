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
	register_setting( 'oss_home_content_group', OSS_HOME_OPTION, array(
		'type'              => 'object',
		'sanitize_callback' => 'oss_home_content_sanitize',
		// Exposed on /wp/v2/settings (admin-only) so the live site's content
		// can be synced with a single authenticated REST call instead of
		// hand-editing the database.
		'show_in_rest'      => array(
			'schema' => array(
				'type'                 => 'object',
				'additionalProperties' => true,
			),
		),
	) );
}
// On init (not admin_init) so the show_in_rest registration also runs
// during REST requests — otherwise /wp/v2/settings never sees the option.
add_action( 'init', 'oss_home_content_register' );

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
					$quote = isset( $row['quote'] ) ? sanitize_textarea_field( wp_unslash( $row['quote'] ) ) : '';
					$name  = isset( $row['name'] ) ? sanitize_text_field( wp_unslash( $row['name'] ) ) : '';
					if ( '' === $quote && '' === $name ) {
						continue;
					}
					$rows[] = array(
						'quote'    => $quote,
						'name'     => $name,
						'image_id' => isset( $row['image_id'] ) ? absint( $row['image_id'] ) : 0,
					);
				}
			}
			$clean['testimonials'] = $rows ? array_values( $rows ) : $default;
			continue;
		}

		if ( 'hero_slide_ids' === $key ) {
			$ids = array();
			if ( isset( $input['hero_slide_ids'] ) && is_array( $input['hero_slide_ids'] ) ) {
				foreach ( $input['hero_slide_ids'] as $id ) {
					$id = absint( $id );
					if ( $id ) {
						$ids[] = $id;
					}
				}
			}
			$clean['hero_slide_ids'] = $ids ? array_values( $ids ) : $default;
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

function oss_home_content_slide_row( $index, $label ) {
	$ids = oss_home_get( 'hero_slide_ids' );
	$id  = isset( $ids[ $index ] ) ? (int) $ids[ $index ] : 0;
	$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	$name = OSS_HOME_OPTION . '[hero_slide_ids][' . $index . ']';
	echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';
	echo '<div class="oss-image-field" data-key="hero_slide_' . esc_attr( $index ) . '">';
	echo '<img src="' . esc_url( $src ) . '" style="max-width:180px;height:auto;display:' . ( $src ? 'block' : 'none' ) . ';margin-bottom:8px;border:1px solid #ddd;">';
	echo '<input type="hidden" name="' . esc_attr( $name ) . '" class="oss-image-field__id" value="' . esc_attr( $id ) . '">';
	echo '<p><button type="button" class="button oss-image-field__select">' . esc_html__( 'Select Image', 'astra-child' ) . '</button> ';
	echo '<button type="button" class="button oss-image-field__remove"' . ( $src ? '' : ' style="display:none;"' ) . '>' . esc_html__( 'Remove', 'astra-child' ) . '</button></p>';
	echo '</div></td></tr>';
}

/**
 * One testimonial row (quote, name, optional photo) with Duplicate/Remove.
 * $i is the array index ("__i__" for the JS template).
 */
function oss_home_content_story_row( $i, $row ) {
	$row = wp_parse_args( $row, array( 'quote' => '', 'name' => '', 'image_id' => 0 ) );
	$n   = OSS_HOME_OPTION . '[testimonials][' . $i . ']';
	$id  = (int) $row['image_id'];
	$src = $id ? wp_get_attachment_image_url( $id, 'thumbnail' ) : '';
	?>
	<div class="oss-story-row" style="border:1px solid #ccd0d4;background:#fff;padding:12px 16px;margin-bottom:12px;max-width:760px;">
		<p><label><strong><?php esc_html_e( 'Quote', 'astra-child' ); ?></strong><br><textarea class="large-text" rows="3" name="<?php echo esc_attr( $n ); ?>[quote]"><?php echo esc_textarea( $row['quote'] ); ?></textarea></label></p>
		<p><label><strong><?php esc_html_e( 'Name', 'astra-child' ); ?></strong><br><input type="text" class="regular-text" name="<?php echo esc_attr( $n ); ?>[name]" value="<?php echo esc_attr( $row['name'] ); ?>"></label></p>
		<div class="oss-image-field">
			<strong><?php esc_html_e( 'Photo (optional)', 'astra-child' ); ?></strong><br>
			<img src="<?php echo esc_url( $src ); ?>" style="width:72px;height:72px;object-fit:cover;border-radius:50%;display:<?php echo $src ? 'block' : 'none'; ?>;margin:6px 0;border:1px solid #ddd;">
			<input type="hidden" name="<?php echo esc_attr( $n ); ?>[image_id]" class="oss-image-field__id" value="<?php echo esc_attr( $id ); ?>">
			<p><button type="button" class="button oss-image-field__select"><?php esc_html_e( 'Select Image', 'astra-child' ); ?></button>
			<button type="button" class="button oss-image-field__remove"<?php echo $src ? '' : ' style="display:none;"'; ?>><?php esc_html_e( 'Remove', 'astra-child' ); ?></button></p>
		</div>
		<p style="margin:0;"><button type="button" class="button oss-story-row__duplicate"><?php esc_html_e( 'Duplicate', 'astra-child' ); ?></button>
		<button type="button" class="button-link-delete oss-story-row__remove" style="margin-left:10px;"><?php esc_html_e( 'Remove this story', 'astra-child' ); ?></button></p>
	</div>
	<?php
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
				oss_home_content_slide_row( 0, __( 'Slideshow Photo 1', 'astra-child' ) );
				oss_home_content_slide_row( 1, __( 'Slideshow Photo 2', 'astra-child' ) );
				oss_home_content_slide_row( 2, __( 'Slideshow Photo 3 (optional)', 'astra-child' ) );
				?>
			</table>
			<p class="description"><?php esc_html_e( 'The V2 homepage preview shows these as a rotating slideshow. Leave Photo 3 empty to show just two.', 'astra-child' ); ?></p>

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
						<p class="description" style="margin-bottom:10px;"><?php esc_html_e( 'Three stories per row on desktop; extra stories wrap to the next row. Duplicate a story to start from a copy. Rows with no quote and no name are dropped on save.', 'astra-child' ); ?></p>
						<div id="oss-story-rows">
							<?php foreach ( array_values( $stories ) as $i => $row ) { oss_home_content_story_row( $i, $row ); } ?>
						</div>
						<p><button type="button" class="button button-secondary" id="oss-story-add"><?php esc_html_e( '+ Add Story', 'astra-child' ); ?></button></p>
						<template id="oss-story-row-template"><?php oss_home_content_story_row( '__i__', array() ); ?></template>
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
				oss_home_content_image_row( 'final_image_id', __( 'Photo', 'astra-child' ) );
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
	$oss_home_admin_js = <<<'JS'
		jQuery(function($){
			// Give a (new or cloned) story row a unique index so its fields
			// don't collide with an existing row's on save.
			function reindex($row){
				var k = 'n' + Date.now() + Math.floor(Math.random() * 1000);
				$row.find('[name]').each(function(){
					this.name = this.name.replace(/\[testimonials\]\[[^\]]+\]/, '[testimonials][' + k + ']');
				});
			}
			$('#oss-story-add').on('click', function(){
				var $r = $($('#oss-story-row-template').html());
				reindex($r);
				$('#oss-story-rows').append($r);
			});
			$(document).on('click', '.oss-story-row__duplicate', function(){
				var $src = $(this).closest('.oss-story-row');
				var $c = $src.clone();
				$c.find('textarea').val($src.find('textarea').val());
				$c.find('input[type="text"]').val($src.find('input[type="text"]').val());
				$c.find('.oss-image-field__id').val($src.find('.oss-image-field__id').val());
				reindex($c);
				$src.after($c);
			});
			$(document).on('click', '.oss-story-row__remove', function(){ $(this).closest('.oss-story-row').remove(); });
			$(document).on('click', '.oss-image-field__select', function(e){
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
			$(document).on('click', '.oss-image-field__remove', function(e){
				e.preventDefault();
				var wrap = $(this).closest('.oss-image-field');
				wrap.find('.oss-image-field__id').val('');
				wrap.find('img').hide();
				$(this).hide();
			});
		});
JS;
	wp_add_inline_script( 'jquery-core', $oss_home_admin_js );
}
add_action( 'admin_enqueue_scripts', 'oss_home_content_admin_assets' );