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
		if ( in_array( $key, array( 'hero_body', 'power_body1', 'power_body2', 'power_quote', 'serve_intro', 'programs_intro', 'horses_body', 'founder_body', 'donate_body', 'connect_body', 'final_body' ), true ) ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? wp_kses_post( wp_unslash( $input[ $key ] ) ) : $default;
			continue;
		}
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

		if ( 'dups' === $key ) {
			if ( isset( $input['dups'] ) && is_array( $input['dups'] ) && function_exists( 'oss_home_sanitize_dups' ) ) {
				$clean['dups'] = oss_home_sanitize_dups( $input['dups'] );
			} else {
				// No duplicate content in this submission → keep what's stored.
				$clean['dups'] = (array) oss_home_get( 'dups' );
			}
			continue;
		}

		if ( 'removed' === $key ) {
			if ( ! isset( $input['removed'] ) || ! is_array( $input['removed'] ) ) {
				$clean['removed'] = (array) oss_home_get( 'removed' );
			} else {
				$valid = function_exists( 'oss_home_default_order' ) ? oss_home_default_order() : array();
				$out   = array();
				foreach ( $input['removed'] as $rk ) {
					$rk = sanitize_key( $rk );
					if ( in_array( $rk, $valid, true ) && ! in_array( $rk, $out, true ) ) {
						$out[] = $rk;
					}
				}
				$clean['removed'] = $out;
			}
			continue;
		}

		if ( 'section_order' === $key ) {
			// Missing from POST (e.g. JS off) → keep the existing saved order.
			if ( ! isset( $input['section_order'] ) || ! is_array( $input['section_order'] ) ) {
				$clean['section_order'] = (array) oss_home_get( 'section_order' );
				continue;
			}
			$originals = function_exists( 'oss_home_default_order' ) ? oss_home_default_order() : array();
			// Duplicate ids that are valid: those in this submission's dups, or already stored.
			$dupids = array();
			if ( isset( $input['dups'] ) && is_array( $input['dups'] ) ) {
				$dupids = array_keys( $input['dups'] );
			}
			if ( function_exists( 'oss_home_dups' ) ) {
				$dupids = array_merge( $dupids, array_keys( oss_home_dups() ) );
			}
			$valid = array_merge( $originals, $dupids );
			$order = array();
			foreach ( $input['section_order'] as $sk ) {
				$sk = sanitize_text_field( $sk );
				if ( in_array( $sk, $valid, true ) && ! in_array( $sk, $order, true ) ) {
					$order[] = $sk;
				}
			}
			$clean['section_order'] = $order;
			continue;
		}

		if ( '_layout' === substr( $key, -7 ) ) {
			$val               = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : $default;
			$clean[ $key ]     = in_array( $val, array( '1', '2' ), true ) ? $val : '1';
			continue;
		}

		// Skip any array-valued default not handled above (avoids strpos on arrays).
		if ( is_array( $default ) ) {
			$clean[ $key ] = isset( $input[ $key ] ) && is_array( $input[ $key ] ) ? $input[ $key ] : $default;
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
		<p class="oss-sections-toolbar">
			<button type="button" class="button" id="oss-sections-expand"><?php esc_html_e( 'Expand all', 'astra-child' ); ?></button>
			<button type="button" class="button" id="oss-sections-collapse"><?php esc_html_e( 'Collapse all', 'astra-child' ); ?></button>
			<span class="description"><?php esc_html_e( 'Drag a section by the ⠿ handle (or use ↑ / ↓) to reorder how it appears on the homepage. Click a title to expand and edit. Remember to Save Changes.', 'astra-child' ); ?></span>
		</p>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'oss_home_content_group' );
			// Feed the current order + panel-id→key map to the reorder script.
			$oss_order_map = array();
			foreach ( oss_home_sections_list() as $skey => $sdata ) {
				$oss_order_map[ $sdata['id'] ] = $skey;
			}
			foreach ( oss_home_dups() as $did => $ddata ) {
				$oss_order_map[ 'oss-section-dup-' . $did ] = $did;
			}
			?>
			<script type="text/javascript">
				window.ossHomeSectionMap = <?php echo wp_json_encode( $oss_order_map ); ?>;
				window.ossHomeSectionOrder = <?php echo wp_json_encode( oss_home_section_order() ); ?>;
			</script>

			<div id="oss-home-sections">

			<details class="oss-section" id="oss-section-hero">
				<summary><span class="oss-section__title"><?php esc_html_e( 'Hero', 'astra-child' ); ?></span><span class="oss-section__hint"><?php esc_html_e( 'Click to expand / collapse', 'astra-child' ); ?></span></summary>
				<div class="oss-section__body">
			<table class="form-table">
				<?php
				oss_home_content_field_row( 'hero_eyebrow', __( 'Eyebrow', 'astra-child' ) );
				oss_home_content_field_row( 'hero_heading', __( 'Supporting Heading', 'astra-child' ) );
				oss_cadmin_editor_row( OSS_HOME_OPTION, 'hero_body', oss_home_get( 'hero_body' ), __( 'Body', 'astra-child' ) );
				oss_home_content_field_row( 'hero_btn1_text', __( 'Button 1 Text', 'astra-child' ) );
				oss_home_content_field_row( 'hero_btn1_url', __( 'Button 1 Link', 'astra-child' ) );
				oss_home_content_field_row( 'hero_btn2_text', __( 'Button 2 Text', 'astra-child' ) );
				oss_home_content_field_row( 'hero_btn2_url', __( 'Button 2 Link', 'astra-child' ) );
				oss_home_content_field_row( 'hero_trust', __( 'Trust Line (below buttons)', 'astra-child' ) );
				oss_home_content_image_row( 'hero_image_id', __( 'Background Image', 'astra-child' ) );
				oss_home_content_slide_row( 0, __( 'Slideshow Photo 1', 'astra-child' ) );
				oss_home_content_slide_row( 1, __( 'Slideshow Photo 2', 'astra-child' ) );
				oss_home_content_slide_row( 2, __( 'Slideshow Photo 3 (optional)', 'astra-child' ) );
				?>
			</table>
			<p class="description"><?php esc_html_e( 'The V2 homepage preview shows these as a rotating slideshow. Leave Photo 3 empty to show just two.', 'astra-child' ); ?></p>

			</div></details>

			<details class="oss-section" id="oss-section-the-healing-power-of-horses">
				<summary><span class="oss-section__title"><?php esc_html_e( 'The Healing Power of Horses', 'astra-child' ); ?></span><span class="oss-section__hint"><?php esc_html_e( 'Click to expand / collapse', 'astra-child' ); ?></span></summary>
				<div class="oss-section__body">
			<table class="form-table">
				<?php
				oss_cadmin_select_row(
					OSS_HOME_OPTION,
					'power_layout',
					oss_home_get( 'power_layout' ),
					__( 'Section Design', 'astra-child' ),
					array(
						'1' => __( 'Style 1 — Photo on the left, text on the right (default)', 'astra-child' ),
						'2' => __( 'Style 2 — Text leads, photo on the right', 'astra-child' ),
					)
				);
				oss_home_content_field_row( 'power_heading', __( 'Heading', 'astra-child' ) );
				oss_cadmin_editor_row( OSS_HOME_OPTION, 'power_body1', oss_home_get( 'power_body1' ), __( 'Paragraph 1', 'astra-child' ) );
				oss_cadmin_editor_row( OSS_HOME_OPTION, 'power_body2', oss_home_get( 'power_body2' ), __( 'Paragraph 2', 'astra-child' ) );
				oss_cadmin_editor_row( OSS_HOME_OPTION, 'power_quote', oss_home_get( 'power_quote' ), __( 'Pull Quote', 'astra-child' ) );
				oss_home_content_field_row( 'power_caption', __( 'Photo Caption', 'astra-child' ) );
				oss_home_content_image_row( 'power_image_id', __( 'Photo', 'astra-child' ) );
				?>
			</table>

			</div></details>

			<details class="oss-section" id="oss-section-who-we-serve">
				<summary><span class="oss-section__title"><?php esc_html_e( 'Who We Serve', 'astra-child' ); ?></span><span class="oss-section__hint"><?php esc_html_e( 'Click to expand / collapse', 'astra-child' ); ?></span></summary>
				<div class="oss-section__body">
			<table class="form-table">
				<?php
				oss_cadmin_select_row(
					OSS_HOME_OPTION,
					'serve_layout',
					oss_home_get( 'serve_layout' ),
					__( 'Section Design', 'astra-child' ),
					array(
						'1' => __( 'Style 1 — Centered card grid (default)', 'astra-child' ),
						'2' => __( 'Style 2 — Left-aligned list of rows', 'astra-child' ),
					)
				);
				oss_home_content_field_row( 'serve_heading', __( 'Heading', 'astra-child' ) );
				oss_cadmin_editor_row( OSS_HOME_OPTION, 'serve_intro', oss_home_get( 'serve_intro' ), __( 'Intro', 'astra-child' ) );
				?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Audience Cards', 'astra-child' ); ?></th>
					<td>
						<p class="description" style="margin:0 0 10px;"><?php esc_html_e( 'Add, duplicate, reorder, or remove cards. A card with no label is dropped on save.', 'astra-child' ); ?></p>
						<?php oss_cadmin_card_repeater( OSS_HOME_OPTION, 'serve_items', $items, $icons, false, __( '+ Add Audience Card', 'astra-child' ) ); ?>
					</td>
				</tr>
				<?php oss_home_content_field_row( 'serve_closing', __( 'Closing Line', 'astra-child' ) ); ?>
			</table>

			</div></details>

			<details class="oss-section" id="oss-section-our-programs">
				<summary><span class="oss-section__title"><?php esc_html_e( 'Our Programs', 'astra-child' ); ?></span><span class="oss-section__hint"><?php esc_html_e( 'Click to expand / collapse', 'astra-child' ); ?></span></summary>
				<div class="oss-section__body">
			<table class="form-table">
				<?php
				oss_cadmin_select_row(
					OSS_HOME_OPTION,
					'programs_layout',
					oss_home_get( 'programs_layout' ),
					__( 'Section Design', 'astra-child' ),
					array(
						'1' => __( 'Style 1 — Centered header (default)', 'astra-child' ),
						'2' => __( 'Style 2 — Left-aligned header', 'astra-child' ),
					)
				);
				oss_home_content_field_row( 'programs_heading', __( 'Heading', 'astra-child' ) );
				oss_cadmin_editor_row( OSS_HOME_OPTION, 'programs_intro', oss_home_get( 'programs_intro' ), __( 'Intro', 'astra-child' ) );
				?>
				<tr><th></th><td><p class="description"><?php
					printf(
						/* translators: %s: link to Programs admin screen */
						esc_html__( 'Program cards come from %s — add, edit, or reorder them there.', 'astra-child' ),
						'<a href="' . esc_url( admin_url( 'edit.php?post_type=oss_program' ) ) . '">' . esc_html__( 'Programs', 'astra-child' ) . '</a>'
					);
				?></p></td></tr>
			</table>

			</div></details>

			<details class="oss-section" id="oss-section-meet-our-horses">
				<summary><span class="oss-section__title"><?php esc_html_e( 'Meet Our Horses', 'astra-child' ); ?></span><span class="oss-section__hint"><?php esc_html_e( 'Click to expand / collapse', 'astra-child' ); ?></span></summary>
				<div class="oss-section__body">
			<table class="form-table">
				<?php
				oss_cadmin_select_row(
					OSS_HOME_OPTION,
					'horses_layout',
					oss_home_get( 'horses_layout' ),
					__( 'Section Design', 'astra-child' ),
					array(
						'1' => __( 'Style 1 — Text panel on the left (default)', 'astra-child' ),
						'2' => __( 'Style 2 — Text panel on the right', 'astra-child' ),
					)
				);
				oss_home_content_field_row( 'horses_heading', __( 'Heading', 'astra-child' ) );
				oss_cadmin_editor_row( OSS_HOME_OPTION, 'horses_body', oss_home_get( 'horses_body' ), __( 'Body', 'astra-child' ) );
				oss_home_content_field_row( 'horses_sub', __( 'Subheading / Link Text', 'astra-child' ) );
				oss_home_content_image_row( 'horses_image_id', __( 'Photo', 'astra-child' ) );
				?>
			</table>

			</div></details>

			<details class="oss-section" id="oss-section-our-founder">
				<summary><span class="oss-section__title"><?php esc_html_e( 'Our Founder', 'astra-child' ); ?></span><span class="oss-section__hint"><?php esc_html_e( 'Click to expand / collapse', 'astra-child' ); ?></span></summary>
				<div class="oss-section__body">
			<table class="form-table">
				<?php
				oss_cadmin_select_row(
					OSS_HOME_OPTION,
					'founder_layout',
					oss_home_get( 'founder_layout' ),
					__( 'Section Design', 'astra-child' ),
					array(
						'1' => __( 'Style 1 — Photo left, biography right (default)', 'astra-child' ),
						'2' => __( 'Style 2 — Centered round portrait testimonial', 'astra-child' ),
					)
				);
				oss_home_content_field_row( 'founder_heading', __( 'Heading', 'astra-child' ) );
				oss_home_content_field_row( 'founder_name', __( 'Name', 'astra-child' ) );
				oss_cadmin_editor_row( OSS_HOME_OPTION, 'founder_body', oss_home_get( 'founder_body' ), __( 'Biography', 'astra-child' ) );
				oss_home_content_field_row( 'founder_btn', __( 'Button Text', 'astra-child' ) );
				oss_home_content_image_row( 'founder_image_id', __( 'Photo', 'astra-child' ) );
				?>
			</table>

			</div></details>

			<details class="oss-section" id="oss-section-stories-of-hope">
				<summary><span class="oss-section__title"><?php esc_html_e( 'Stories of Hope', 'astra-child' ); ?></span><span class="oss-section__hint"><?php esc_html_e( 'Click to expand / collapse', 'astra-child' ); ?></span></summary>
				<div class="oss-section__body">
			<table class="form-table">
				<?php
				oss_cadmin_select_row(
					OSS_HOME_OPTION,
					'stories_layout',
					oss_home_get( 'stories_layout' ),
					__( 'Section Design', 'astra-child' ),
					array(
						'1' => __( 'Style 1 — Multi-column card grid (default)', 'astra-child' ),
						'2' => __( 'Style 2 — Single centered column', 'astra-child' ),
					)
				);
				oss_home_content_field_row( 'stories_heading', __( 'Heading', 'astra-child' ) );
				?>
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

			</div></details>

			<details class="oss-section" id="oss-section-help-us-change-lives">
				<summary><span class="oss-section__title"><?php esc_html_e( 'Help Us Change Lives', 'astra-child' ); ?></span><span class="oss-section__hint"><?php esc_html_e( 'Click to expand / collapse', 'astra-child' ); ?></span></summary>
				<div class="oss-section__body">
			<table class="form-table">
				<?php
				oss_cadmin_select_row(
					OSS_HOME_OPTION,
					'donate_layout',
					oss_home_get( 'donate_layout' ),
					__( 'Section Design', 'astra-child' ),
					array(
						'1' => __( 'Style 1 — Image left, text right (default)', 'astra-child' ),
						'2' => __( 'Style 2 — Image right, text left', 'astra-child' ),
					)
				);
				oss_home_content_field_row( 'donate_heading', __( 'Heading', 'astra-child' ) );
				oss_cadmin_editor_row( OSS_HOME_OPTION, 'donate_body', oss_home_get( 'donate_body' ), __( 'Body', 'astra-child' ) );
				oss_home_content_field_row( 'donate_btn', __( 'Button Text', 'astra-child' ) );
				oss_home_content_field_row( 'donate_btn_url', __( 'Button Link', 'astra-child' ) );
				oss_home_content_image_row( 'donate_image_id', __( 'Background Image', 'astra-child' ) );
				?>
			</table>

			</div></details>

			<details class="oss-section" id="oss-section-stay-connected">
				<summary><span class="oss-section__title"><?php esc_html_e( 'Stay Connected', 'astra-child' ); ?></span><span class="oss-section__hint"><?php esc_html_e( 'Click to expand / collapse', 'astra-child' ); ?></span></summary>
				<div class="oss-section__body">
			<table class="form-table">
				<?php
				oss_cadmin_select_row(
					OSS_HOME_OPTION,
					'connect_layout',
					oss_home_get( 'connect_layout' ),
					__( 'Section Design', 'astra-child' ),
					array(
						'1' => __( 'Style 1 — Text left, form right (default)', 'astra-child' ),
						'2' => __( 'Style 2 — Centered, form below text', 'astra-child' ),
					)
				);
				oss_home_content_field_row( 'connect_heading', __( 'Heading', 'astra-child' ) );
				oss_cadmin_editor_row( OSS_HOME_OPTION, 'connect_body', oss_home_get( 'connect_body' ), __( 'Body', 'astra-child' ) );
				?>
				<tr><th></th><td><p class="description"><?php esc_html_e( 'The signup form itself is the [oss_newsletter_signup] shortcode — connect a mailing list plugin any time and it activates automatically.', 'astra-child' ); ?></p></td></tr>
			</table>

			</div></details>

			<details class="oss-section" id="oss-section-final-cta">
				<summary><span class="oss-section__title"><?php esc_html_e( 'Final CTA', 'astra-child' ); ?></span><span class="oss-section__hint"><?php esc_html_e( 'Click to expand / collapse', 'astra-child' ); ?></span></summary>
				<div class="oss-section__body">
			<table class="form-table">
				<?php
				oss_cadmin_select_row(
					OSS_HOME_OPTION,
					'final_layout',
					oss_home_get( 'final_layout' ),
					__( 'Section Design', 'astra-child' ),
					array(
						'1' => __( 'Style 1 — Image left, text right (default)', 'astra-child' ),
						'2' => __( 'Style 2 — Image right, text left', 'astra-child' ),
					)
				);
				oss_home_content_field_row( 'final_heading', __( 'Heading', 'astra-child' ) );
				oss_cadmin_editor_row( OSS_HOME_OPTION, 'final_body', oss_home_get( 'final_body' ), __( 'Body', 'astra-child' ) );
				oss_home_content_field_row( 'final_sub', __( 'Supporting Line', 'astra-child' ) );
				oss_home_content_field_row( 'final_btn1_text', __( 'Button 1 Text', 'astra-child' ) );
				oss_home_content_field_row( 'final_btn1_url', __( 'Button 1 Link', 'astra-child' ) );
				oss_home_content_field_row( 'final_btn2_text', __( 'Button 2 Text', 'astra-child' ) );
				oss_home_content_field_row( 'final_btn2_url', __( 'Button 2 Link', 'astra-child' ) );
				oss_home_content_image_row( 'final_image_id', __( 'Photo', 'astra-child' ) );
				?>
			</table>

			</div></details>

			<?php
			// Duplicate section panels (rendered after the originals; the reorder
			// script places them into the saved order on load).
			foreach ( oss_home_dups() as $oss_did => $oss_ddata ) {
				oss_home_render_dup_panel( $oss_did, $oss_ddata, $icons );
			}
			?>

			</div><!-- #oss-home-sections -->

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
	wp_enqueue_script( 'jquery-ui-sortable' );
	oss_cadmin_repeater_js();
	oss_cadmin_editor_assets();

	// Collapsible section toggles — styled like core postboxes.
	$oss_home_admin_css = <<<'CSS'
		.oss-sections-toolbar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 16px;}
		.oss-sections-toolbar .description{margin-left:6px;}
		.oss-section{background:#fff;border:1px solid #c3c4c7;box-shadow:0 1px 1px rgba(0,0,0,.04);margin:0 0 12px;max-width:1100px;}
		.oss-section > summary{list-style:none;cursor:pointer;display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:14px;font-weight:600;line-height:1.4;user-select:none;}
		.oss-section > summary::-webkit-details-marker{display:none;}
		.oss-section > summary::before{content:"";display:inline-block;width:8px;height:8px;border-right:2px solid #50575e;border-bottom:2px solid #50575e;transform:rotate(-45deg);transition:transform .15s ease;margin:0 4px 0 2px;flex:0 0 auto;}
		.oss-section[open] > summary::before{transform:rotate(45deg);}
		.oss-section[open] > summary{border-bottom:1px solid #dcdcde;}
		.oss-section > summary:hover{background:#f6f7f7;}
		.oss-section > summary:focus-visible{outline:2px solid #2271b1;outline-offset:-2px;}
		.oss-section__title{flex:1 1 auto;}
		.oss-section__hint{font-weight:400;font-size:12px;color:#787c82;}
		.oss-section[open] .oss-section__hint{display:none;}
		.oss-section__body{padding:0 16px 8px;}
		.oss-section__body .form-table{margin-top:0;}
		.oss-section > summary{gap:8px;}
		.oss-section__hint{display:none;}
		.oss-section__handle{cursor:grab;color:#8c8f94;font-size:18px;width:26px;height:30px;line-height:30px;text-align:center;border-radius:4px;flex:0 0 auto;transition:color .12s ease,background .12s ease;}
		.oss-section__handle:hover{color:#1d2327;background:#f0f0f1;}
		.oss-section__handle:active{cursor:grabbing;}
		.oss-section__move{display:inline-flex;align-items:center;gap:4px;flex:0 0 auto;margin-left:auto;}
		.oss-section__move .button{width:32px;height:32px;min-height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:5px;color:#50575e;box-shadow:none;}
		.oss-section__move .button:hover{color:#135e96;border-color:#135e96;background:#f6f7f7;}
		.oss-section__move .button .dashicons{display:block;width:18px;height:18px;font-size:18px;line-height:18px;}
		.oss-sortable-placeholder{border:2px dashed #2271b1;background:#f0f6fc;border-radius:4px;margin:0 0 12px;height:54px;max-width:1100px;}
		.oss-section.ui-sortable-helper{box-shadow:0 10px 26px rgba(0,0,0,.16);}
		@media (max-width:782px){.oss-section > summary{flex-wrap:wrap;}.oss-section__title{flex:1 1 100%;}.oss-section__move{margin-left:0;}}
		.oss-section.ui-sortable-helper{box-shadow:0 8px 22px rgba(0,0,0,.16);}
CSS;
	wp_register_style( 'oss-home-content-admin', false, array(), null );
	wp_enqueue_style( 'oss-home-content-admin' );
	wp_add_inline_style( 'oss-home-content-admin', $oss_home_admin_css );

	$oss_home_admin_js = <<<'JS'
		jQuery(function($){
			// Section toggles: remember which ones are open (per browser) so the
			// section you were editing is still open after Save reloads the page.
			var STORE = 'ossHomeContentOpenSections';
			var $sections = $('.oss-section');
			function readOpen(){
				try { return JSON.parse(window.localStorage.getItem(STORE) || '[]'); } catch (e) { return []; }
			}
			function saveOpen(){
				var ids = $sections.filter('[open]').map(function(){ return this.id; }).get();
				try { window.localStorage.setItem(STORE, JSON.stringify(ids)); } catch (e) {}
			}
			var open = readOpen();
			$sections.each(function(){ this.open = open.indexOf(this.id) !== -1; });
			if (window.location.hash && $(window.location.hash).is('.oss-section')) {
				$(window.location.hash).prop('open', true);
			}
			$sections.on('toggle', saveOpen);
			$('#oss-sections-expand').on('click', function(){ $sections.prop('open', true); saveOpen(); });
			$('#oss-sections-collapse').on('click', function(){ $sections.prop('open', false); saveOpen(); });
		});
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
		jQuery(function($){
			// Reorder homepage sections: drag by the handle or use the up/down
			// buttons. A hidden input per panel captures the order; the panels
			// live inside the settings form, so DOM order == submitted order.
			var map   = window.ossHomeSectionMap || {};
			var order = window.ossHomeSectionOrder || [];
			var $container = $('#oss-home-sections');
			if (!$container.length) { return; }

			$container.children('.oss-section').each(function(){
				var $panel = $(this), key = map[this.id];
				if (!key) { return; }
				var $summary = $panel.children('summary');
				if (!$summary.find('.oss-section__handle').length) {
					$summary.prepend('<span class="oss-section__handle dashicons dashicons-menu" title="Drag to reorder"></span>');
					$summary.append('<span class="oss-section__move"><button type="button" class="button oss-move-up" title="Move up"><span class="dashicons dashicons-arrow-up-alt2"></span></button><button type="button" class="button oss-move-down" title="Move down"><span class="dashicons dashicons-arrow-down-alt2"></span></button></span>');
				}
				if (!$panel.children('.oss-order-input').length) {
					$('<input>', { type:'hidden', 'class':'oss-order-input', name:'oss_home_content[section_order][]', value:key }).appendTo($panel);
				}
			});

			// Apply the saved order to the panels on load.
			order.forEach(function(key){
				for (var id in map) { if (map[id] === key) { var el = document.getElementById(id); if (el) { $container.append(el); } } }
			});

			// Keep the handle / arrow buttons from toggling the <details>.
			$container.on('click', '.oss-section__handle, .oss-move-up, .oss-move-down', function(e){ e.preventDefault(); e.stopPropagation(); });
			function ossMoveSafe($p, fn){
				if (window.ossCadminSaveRemove) { window.ossCadminSaveRemove($p); }
				fn();
				if (window.ossCadminInitEditors) { setTimeout(window.ossCadminInitEditors, 40); }
			}
			$container.on('click', '.oss-move-up', function(){ var $p=$(this).closest('.oss-section'), $prev=$p.prev('.oss-section'); if ($prev.length) { ossMoveSafe($p, function(){ $p.insertBefore($prev); }); } });
			$container.on('click', '.oss-move-down', function(){ var $p=$(this).closest('.oss-section'), $next=$p.next('.oss-section'); if ($next.length) { ossMoveSafe($p, function(){ $p.insertAfter($next); }); } });

			if ($.fn.sortable) {
				$container.sortable({
					handle:'.oss-section__handle', items:'> .oss-section', placeholder:'oss-sortable-placeholder', forcePlaceholderSize:true, tolerance:'pointer', axis:'y',
					start: function(e, ui){ if (window.ossCadminSaveRemove) { window.ossCadminSaveRemove(ui.item); } },
					stop:  function(e, ui){ if (window.ossCadminInitEditors) { setTimeout(window.ossCadminInitEditors, 40); } }
				});
			}
		});
JS;
	wp_add_inline_script( 'jquery-core', $oss_home_admin_js );
}
add_action( 'admin_enqueue_scripts', 'oss_home_content_admin_assets' );