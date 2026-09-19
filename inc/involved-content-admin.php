<?php
/**
 * Get Involved — admin editor screen (Appearance → Get Involved).
 * Plain Settings API + core media picker + shared collapsible-section UI.
 */

defined( 'ABSPATH' ) || exit;

function oss_involved_content_admin_bar( $wp_admin_bar ) {
	if ( ! current_user_can( 'edit_theme_options' ) || ! is_page_template( 'page-templates/template-involved-v2.php' ) ) {
		return;
	}
	$wp_admin_bar->add_node( array(
		'id'    => 'oss-edit-involved',
		'title' => __( '✎ Edit Page Content', 'astra-child' ),
		'href'  => admin_url( 'themes.php?page=oss-involved-content' ),
	) );
}
add_action( 'admin_bar_menu', 'oss_involved_content_admin_bar', 81 );

function oss_involved_content_menu() {
	add_theme_page(
		__( 'Get Involved', 'astra-child' ),
		__( 'Get Involved', 'astra-child' ),
		'edit_theme_options',
		'oss-involved-content',
		'oss_involved_content_page'
	);
}
add_action( 'admin_menu', 'oss_involved_content_menu' );

function oss_involved_content_register() {
	register_setting( 'oss_involved_content_group', OSS_INVOLVED_OPTION, 'oss_involved_content_sanitize' );
}
add_action( 'admin_init', 'oss_involved_content_register' );

function oss_involved_content_sanitize( $input ) {
	$defaults = oss_involved_content_defaults();
	$clean    = array();

	foreach ( $defaults as $key => $default ) {
		if ( 'ways' === $key ) {
			continue; // handled below
		}
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

	// Ways: icon (whitelisted), label (text), url. Rows with no label are dropped.
	$icons = array_keys( oss_home_icon_library() );
	$ways  = array();
	if ( isset( $input['ways'] ) && is_array( $input['ways'] ) ) {
		foreach ( $input['ways'] as $row ) {
			$label = isset( $row['label'] ) ? sanitize_text_field( wp_unslash( $row['label'] ) ) : '';
			if ( '' === $label ) {
				continue;
			}
			$icon = isset( $row['icon'] ) && in_array( $row['icon'], $icons, true ) ? $row['icon'] : 'compass';
			$url  = isset( $row['url'] ) ? esc_url_raw( trim( $row['url'] ) ) : '/contact/';
			$ways[] = array( 'icon' => $icon, 'label' => $label, 'url' => $url ? $url : '/contact/' );
		}
	}
	$clean['ways'] = $ways ? $ways : $defaults['ways'];

	return $clean;
}

function oss_involved_content_page() {
	$o     = OSS_INVOLVED_OPTION;
	$icons = oss_home_icon_library();
	$ways  = (array) oss_involved_get( 'ways' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Get Involved Content', 'astra-child' ); ?></h1>
		<p><?php esc_html_e( 'Edit every piece of the Get Involved page here — no page builder, no code. This page has its own content, so changes here no longer affect the homepage.', 'astra-child' ); ?></p>
		<?php oss_cadmin_toolbar(); ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'oss_involved_content_group' ); ?>

			<?php oss_cadmin_section( 'page-banner', __( 'Page Banner', 'astra-child' ) ); ?>
			<table class="form-table">
				<?php
				oss_cadmin_field_row( $o, 'hero_eyebrow', oss_involved_get( 'hero_eyebrow' ), __( 'Eyebrow', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'hero_heading', oss_involved_get( 'hero_heading' ), __( 'Heading', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'hero_body', oss_involved_get( 'hero_body' ), __( 'Intro', 'astra-child' ), 'textarea' );
				oss_cadmin_image_row( $o, 'hero_image_id', oss_involved_get( 'hero_image_id' ), __( 'Background Image', 'astra-child' ) );
				?>
			</table>

			<?php oss_cadmin_section( 'ways-to-get-involved', __( 'Ways to Get Involved', 'astra-child' ) ); ?>
			<table class="form-table">
				<?php
				oss_cadmin_field_row( $o, 'ways_eyebrow', oss_involved_get( 'ways_eyebrow' ), __( 'Eyebrow', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'ways_heading', oss_involved_get( 'ways_heading' ), __( 'Heading', 'astra-child' ) );
				?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Cards', 'astra-child' ); ?></th>
					<td>
						<p class="description" style="margin-bottom:10px;"><?php esc_html_e( 'Each card links somewhere (the contact page by default). Clear a label to remove that card on save.', 'astra-child' ); ?></p>
						<table class="widefat" style="max-width:820px;">
							<thead><tr>
								<th style="width:150px;"><?php esc_html_e( 'Icon', 'astra-child' ); ?></th>
								<th><?php esc_html_e( 'Label', 'astra-child' ); ?></th>
								<th style="width:220px;"><?php esc_html_e( 'Link', 'astra-child' ); ?></th>
							</tr></thead>
							<tbody>
							<?php foreach ( $ways as $i => $row ) : ?>
								<tr>
									<td>
										<select name="<?php echo esc_attr( $o . '[ways][' . $i . '][icon]' ); ?>">
											<?php foreach ( array_keys( $icons ) as $icon_key ) : ?>
												<option value="<?php echo esc_attr( $icon_key ); ?>" <?php selected( isset( $row['icon'] ) ? $row['icon'] : '', $icon_key ); ?>><?php echo esc_html( ucfirst( $icon_key ) ); ?></option>
											<?php endforeach; ?>
										</select>
									</td>
									<td><input type="text" class="large-text" name="<?php echo esc_attr( $o . '[ways][' . $i . '][label]' ); ?>" value="<?php echo esc_attr( isset( $row['label'] ) ? $row['label'] : '' ); ?>"></td>
									<td><input type="text" class="regular-text" name="<?php echo esc_attr( $o . '[ways][' . $i . '][url]' ); ?>" value="<?php echo esc_attr( isset( $row['url'] ) ? $row['url'] : '/contact/' ); ?>"></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</td>
				</tr>
			</table>

			<?php oss_cadmin_section( 'support-the-sanctuary', __( 'Support the Sanctuary', 'astra-child' ) ); ?>
			<table class="form-table">
				<?php
				oss_cadmin_field_row( $o, 'give_eyebrow', oss_involved_get( 'give_eyebrow' ), __( 'Eyebrow', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'give_heading', oss_involved_get( 'give_heading' ), __( 'Heading', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'give_body', oss_involved_get( 'give_body' ), __( 'Body', 'astra-child' ), 'textarea' );
				oss_cadmin_image_row( $o, 'give_image_id', oss_involved_get( 'give_image_id' ), __( 'Photo', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'give_btn_text', oss_involved_get( 'give_btn_text' ), __( 'Button Text', 'astra-child' ) );
				oss_cadmin_field_row( $o, 'give_btn_url', oss_involved_get( 'give_btn_url' ), __( 'Button Link', 'astra-child' ) );
				?>
			</table>

			<?php oss_cadmin_sections_end(); ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function oss_involved_content_admin_assets( $hook ) {
	if ( 'appearance_page_oss-involved-content' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	oss_cadmin_toggle_assets();
	oss_cadmin_media_js();
}
add_action( 'admin_enqueue_scripts', 'oss_involved_content_admin_assets' );
