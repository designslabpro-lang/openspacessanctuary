<?php
/**
 * Events & Retreats — admin editor screen (Appearance → Events).
 * Repeatable event rows, plain Settings API + the core media picker.
 */

defined( 'ABSPATH' ) || exit;

function oss_events_content_admin_bar( $wp_admin_bar ) {
	if ( ! current_user_can( 'edit_theme_options' ) || ! is_page_template( 'page-templates/template-events-v2.php' ) ) {
		return;
	}
	$wp_admin_bar->add_node( array(
		'id'    => 'oss-edit-events',
		'title' => __( '✎ Edit Page Content', 'astra-child' ),
		'href'  => admin_url( 'themes.php?page=oss-events-content' ),
	) );
}
add_action( 'admin_bar_menu', 'oss_events_content_admin_bar', 81 );

function oss_events_content_menu() {
	add_theme_page(
		__( 'Events', 'astra-child' ),
		__( 'Events', 'astra-child' ),
		'edit_theme_options',
		'oss-events-content',
		'oss_events_content_page'
	);
}
add_action( 'admin_menu', 'oss_events_content_menu' );

function oss_events_content_register() {
	register_setting( 'oss_events_content_group', OSS_EVENTS_OPTION, array(
		'type'              => 'object',
		'sanitize_callback' => 'oss_events_content_sanitize',
		'show_in_rest'      => array(
			'schema' => array(
				'type'                 => 'object',
				'additionalProperties' => true,
			),
		),
	) );
}
add_action( 'init', 'oss_events_content_register' );

function oss_events_content_sanitize( $input ) {
	$defaults = oss_events_content_defaults();
	$types    = oss_events_types();
	$clean    = array(
		'heading' => isset( $input['heading'] ) ? sanitize_text_field( wp_unslash( $input['heading'] ) ) : $defaults['heading'],
		'intro'   => isset( $input['intro'] ) ? sanitize_textarea_field( wp_unslash( $input['intro'] ) ) : $defaults['intro'],
		'events'  => array(),
	);

	if ( isset( $input['events'] ) && is_array( $input['events'] ) ) {
		foreach ( $input['events'] as $row ) {
			$title = isset( $row['title'] ) ? sanitize_text_field( wp_unslash( $row['title'] ) ) : '';
			if ( '' === $title ) {
				continue;
			}
			$type = isset( $row['type'] ) ? wp_unslash( $row['type'] ) : '';
			$date = isset( $row['date'] ) ? sanitize_text_field( $row['date'] ) : '';
			$clean['events'][] = array(
				'title'       => $title,
				'type'        => in_array( $type, $types, true ) ? $type : '',
				'date'        => preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '',
				'time'        => isset( $row['time'] ) ? sanitize_text_field( wp_unslash( $row['time'] ) ) : '',
				'location'    => isset( $row['location'] ) ? sanitize_text_field( wp_unslash( $row['location'] ) ) : '',
				'description' => isset( $row['description'] ) ? sanitize_textarea_field( wp_unslash( $row['description'] ) ) : '',
				'link'        => isset( $row['link'] ) ? esc_url_raw( trim( wp_unslash( $row['link'] ) ) ) : '',
				'image_id'    => isset( $row['image_id'] ) ? absint( $row['image_id'] ) : 0,
			);
		}
	}

	return $clean;
}

/**
 * One event row. $i is the array index ("__i__" for the JS template).
 */
function oss_events_content_row( $i, $row ) {
	$row  = wp_parse_args( $row, array( 'title' => '', 'type' => '', 'date' => '', 'time' => '', 'location' => '', 'description' => '', 'link' => '', 'image_id' => 0 ) );
	$n    = OSS_EVENTS_OPTION . '[events][' . $i . ']';
	$id   = (int) $row['image_id'];
	$src  = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	?>
	<div class="oss-event-row" style="border:1px solid #ccd0d4;background:#fff;padding:16px 20px;margin-bottom:16px;">
		<table class="form-table" style="margin:0;">
			<tr><th scope="row"><?php esc_html_e( 'Title', 'astra-child' ); ?></th><td><input type="text" class="large-text" name="<?php echo esc_attr( $n ); ?>[title]" value="<?php echo esc_attr( $row['title'] ); ?>"></td></tr>
			<tr><th scope="row"><?php esc_html_e( 'Type', 'astra-child' ); ?></th><td>
				<select name="<?php echo esc_attr( $n ); ?>[type]">
					<option value=""><?php esc_html_e( '— None —', 'astra-child' ); ?></option>
					<?php foreach ( oss_events_types() as $type ) : ?>
						<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $row['type'], $type ); ?>><?php echo esc_html( $type ); ?></option>
					<?php endforeach; ?>
				</select>
			</td></tr>
			<tr><th scope="row"><?php esc_html_e( 'Date', 'astra-child' ); ?></th><td><input type="date" name="<?php echo esc_attr( $n ); ?>[date]" value="<?php echo esc_attr( $row['date'] ); ?>"></td></tr>
			<tr><th scope="row"><?php esc_html_e( 'Time', 'astra-child' ); ?></th><td><input type="text" class="regular-text" name="<?php echo esc_attr( $n ); ?>[time]" value="<?php echo esc_attr( $row['time'] ); ?>" placeholder="10:00 AM – 1:00 PM"></td></tr>
			<tr><th scope="row"><?php esc_html_e( 'Location', 'astra-child' ); ?></th><td><input type="text" class="large-text" name="<?php echo esc_attr( $n ); ?>[location]" value="<?php echo esc_attr( $row['location'] ); ?>"></td></tr>
			<tr><th scope="row"><?php esc_html_e( 'Description', 'astra-child' ); ?></th><td><textarea class="large-text" rows="3" name="<?php echo esc_attr( $n ); ?>[description]"><?php echo esc_textarea( $row['description'] ); ?></textarea></td></tr>
			<tr><th scope="row"><?php esc_html_e( 'Registration Link', 'astra-child' ); ?></th><td><input type="url" class="large-text" name="<?php echo esc_attr( $n ); ?>[link]" value="<?php echo esc_attr( $row['link'] ); ?>" placeholder="https://"></td></tr>
			<tr><th scope="row"><?php esc_html_e( 'Image (optional)', 'astra-child' ); ?></th><td>
				<div class="oss-image-field">
					<img src="<?php echo esc_url( $src ); ?>" style="max-width:180px;height:auto;display:<?php echo $src ? 'block' : 'none'; ?>;margin-bottom:8px;border:1px solid #ddd;">
					<input type="hidden" name="<?php echo esc_attr( $n ); ?>[image_id]" class="oss-image-field__id" value="<?php echo esc_attr( $id ); ?>">
					<p><button type="button" class="button oss-image-field__select"><?php esc_html_e( 'Select Image', 'astra-child' ); ?></button>
					<button type="button" class="button oss-image-field__remove"<?php echo $src ? '' : ' style="display:none;"'; ?>><?php esc_html_e( 'Remove', 'astra-child' ); ?></button></p>
				</div>
			</td></tr>
		</table>
		<p style="margin:8px 0 0;"><button type="button" class="button-link-delete oss-event-row__remove"><?php esc_html_e( 'Remove this event', 'astra-child' ); ?></button></p>
	</div>
	<?php
}

function oss_events_content_page() {
	$events = (array) oss_events_get( 'events' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Events & Retreats', 'astra-child' ); ?></h1>
		<p><?php esc_html_e( 'Events shown on the Events & Retreats page as a timeline. Add, edit, or remove events here — no page builder, no code. Rows without a title are dropped on save.', 'astra-child' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'oss_events_content_group' ); ?>

			<h2><?php esc_html_e( 'Page Banner', 'astra-child' ); ?></h2>
			<table class="form-table">
				<tr><th scope="row"><label for="oss_events_heading"><?php esc_html_e( 'Heading', 'astra-child' ); ?></label></th><td><input type="text" id="oss_events_heading" class="large-text" name="<?php echo esc_attr( OSS_EVENTS_OPTION ); ?>[heading]" value="<?php echo esc_attr( oss_events_get( 'heading' ) ); ?>"></td></tr>
				<tr><th scope="row"><label for="oss_events_intro"><?php esc_html_e( 'Intro', 'astra-child' ); ?></label></th><td><textarea id="oss_events_intro" class="large-text" rows="3" name="<?php echo esc_attr( OSS_EVENTS_OPTION ); ?>[intro]"><?php echo esc_textarea( oss_events_get( 'intro' ) ); ?></textarea></td></tr>
			</table>

			<h2><?php esc_html_e( 'Events', 'astra-child' ); ?></h2>
			<div id="oss-event-rows">
				<?php foreach ( array_values( $events ) as $i => $row ) { oss_events_content_row( $i, $row ); } ?>
			</div>
			<p><button type="button" class="button button-secondary" id="oss-event-add"><?php esc_html_e( '+ Add Event', 'astra-child' ); ?></button></p>
			<template id="oss-event-row-template"><?php oss_events_content_row( '__i__', array() ); ?></template>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function oss_events_content_admin_assets( $hook ) {
	if ( 'appearance_page_oss-events-content' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_add_inline_script( 'jquery-core', "
		jQuery(function($){
			$('#oss-event-add').on('click', function(){
				var html = $('#oss-event-row-template').html().replace(/__i__/g, 'n' + Date.now());
				$('#oss-event-rows').append(html);
			});
			$(document).on('click', '.oss-event-row__remove', function(){
				$(this).closest('.oss-event-row').remove();
			});
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
	" );
}
add_action( 'admin_enqueue_scripts', 'oss_events_content_admin_assets' );
