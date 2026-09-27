<?php
/**
 * Homepage section duplicates — admin UI (Duplicate button, duplicate editor
 * panels, AJAX create/remove). Renders inside the Homepage Content screen.
 * Kept separate from homepage-content-admin.php so the original panels are
 * untouched (zero risk to the existing editor).
 */

defined( 'ABSPATH' ) || exit;

/**
 * The editable flat-key fields that make up each section type. Used to copy a
 * section's content into a duplicate and to render the duplicate's editor.
 */
function oss_home_type_fields( $type ) {
	$map = array(
		'hero'     => array( 'hero_eyebrow', 'hero_heading', 'hero_body', 'hero_btn1_text', 'hero_btn1_url', 'hero_btn2_text', 'hero_btn2_url', 'hero_trust', 'hero_image_id', 'hero_slide_ids' ),
		'power'    => array( 'power_eyebrow', 'power_heading', 'power_body1', 'power_body2', 'power_quote', 'power_caption', 'power_image_id', 'power_layout' ),
		'serve'    => array( 'serve_heading', 'serve_intro', 'serve_items', 'serve_closing', 'serve_layout' ),
		'programs' => array( 'programs_heading', 'programs_intro', 'programs_layout' ),
		'horses'   => array( 'horses_heading', 'horses_body', 'horses_sub', 'horses_image_id', 'horses_layout' ),
		'founder'  => array( 'founder_heading', 'founder_name', 'founder_body', 'founder_btn', 'founder_image_id', 'founder_layout' ),
		'stories'  => array( 'stories_heading', 'stories_layout', 'testimonials' ),
		'donate'   => array( 'donate_heading', 'donate_body', 'donate_btn', 'donate_btn_url', 'donate_image_id', 'donate_layout' ),
		'connect'  => array( 'connect_heading', 'connect_body', 'connect_layout' ),
		'final'    => array( 'final_heading', 'final_body', 'final_sub', 'final_btn1_text', 'final_btn1_url', 'final_btn2_text', 'final_btn2_url', 'final_image_id', 'final_layout' ),
	);
	return isset( $map[ $type ] ) ? $map[ $type ] : array();
}

/**
 * A friendly label for a flat field key (drops the section-type prefix).
 */
function oss_home_field_label( $field ) {
	$suffix = preg_replace( '/^[a-z]+_/', '', $field );
	$labels = array(
		'eyebrow'   => __( 'Eyebrow', 'astra-child' ),
		'heading'   => __( 'Heading', 'astra-child' ),
		'body'      => __( 'Body', 'astra-child' ),
		'body1'     => __( 'Paragraph 1', 'astra-child' ),
		'body2'     => __( 'Paragraph 2', 'astra-child' ),
		'quote'     => __( 'Pull Quote', 'astra-child' ),
		'caption'   => __( 'Photo Caption', 'astra-child' ),
		'image_id'  => __( 'Image', 'astra-child' ),
		'layout'    => __( 'Section Design', 'astra-child' ),
		'intro'     => __( 'Intro', 'astra-child' ),
		'items'     => __( 'Cards', 'astra-child' ),
		'closing'   => __( 'Closing Line', 'astra-child' ),
		'sub'       => __( 'Subheading / Link Text', 'astra-child' ),
		'name'      => __( 'Name', 'astra-child' ),
		'btn'       => __( 'Button Text', 'astra-child' ),
		'btn_text'  => __( 'Button Text', 'astra-child' ),
		'btn_url'   => __( 'Button Link', 'astra-child' ),
		'btn1_text' => __( 'Button 1 Text', 'astra-child' ),
		'btn1_url'  => __( 'Button 1 Link', 'astra-child' ),
		'btn2_text' => __( 'Button 2 Text', 'astra-child' ),
		'btn2_url'  => __( 'Button 2 Link', 'astra-child' ),
		'trust'     => __( 'Trust Line', 'astra-child' ),
		'slide_ids' => __( 'Slideshow Photos', 'astra-child' ),
		'testimonials' => __( 'Testimonials', 'astra-child' ),
	);
	return isset( $labels[ $suffix ] ) ? $labels[ $suffix ] : ucwords( str_replace( '_', ' ', $suffix ) );
}

/**
 * A reusable image picker bound to an explicit input name (works with the
 * shared .oss-image-field JS in homepage-content-admin.php).
 */
function oss_home_dup_image_control( $name, $id ) {
	$id  = (int) $id;
	$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	echo '<div class="oss-image-field">';
	echo '<img src="' . esc_url( $src ) . '" style="max-width:180px;height:auto;display:' . ( $src ? 'block' : 'none' ) . ';margin-bottom:8px;border:1px solid #ddd;">';
	echo '<input type="hidden" name="' . esc_attr( $name ) . '" class="oss-image-field__id" value="' . esc_attr( $id ) . '">';
	echo '<p><button type="button" class="button oss-image-field__select">' . esc_html__( 'Select Image', 'astra-child' ) . '</button> ';
	echo '<button type="button" class="button oss-image-field__remove"' . ( $src ? '' : ' style="display:none;"' ) . '>' . esc_html__( 'Remove', 'astra-child' ) . '</button></p>';
	echo '</div>';
}

/**
 * Render one field row inside a duplicate's editor.
 */
function oss_home_dup_control( $opt, $data, $field, $icons ) {
	$name  = $opt . '[' . $field . ']';
	$val   = array_key_exists( $field, (array) $data ) ? $data[ $field ] : oss_home_get( $field );
	$label = oss_home_field_label( $field );
	echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';

	if ( '_image_id' === substr( $field, -9 ) ) {
		oss_home_dup_image_control( $name, (int) $val );
	} elseif ( '_layout' === substr( $field, -7 ) ) {
		echo '<select name="' . esc_attr( $name ) . '">';
		echo '<option value="1" ' . selected( $val, '1', false ) . '>' . esc_html__( 'Style 1 (default)', 'astra-child' ) . '</option>';
		echo '<option value="2" ' . selected( $val, '2', false ) . '>' . esc_html__( 'Style 2', 'astra-child' ) . '</option>';
		echo '</select>';
	} elseif ( 'serve_items' === $field ) {
		$rows = is_array( $val ) ? $val : array();
		oss_cadmin_card_repeater( $opt, 'serve_items', $rows, $icons, false, __( '+ Add Card', 'astra-child' ) );
	} elseif ( in_array( $field, array( 'testimonials', 'hero_slide_ids' ), true ) ) {
		echo '<p class="description">' . esc_html__( 'Copied from the original section. To change these, edit the original section above.', 'astra-child' ) . '</p>';
	} else {
		$suffix    = preg_replace( '/^[a-z]+_/', '', $field );
		$textareas = array( 'body', 'body1', 'body2', 'intro', 'quote', 'caption', 'closing' );
		if ( in_array( $suffix, $textareas, true ) ) {
			echo '<textarea class="large-text" rows="4" name="' . esc_attr( $name ) . '">' . esc_textarea( $val ) . '</textarea>';
			echo '<p class="description">' . esc_html__( 'Plain text — line breaks become paragraphs on the page.', 'astra-child' ) . '</p>';
		} else {
			echo '<input type="text" class="large-text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '">';
		}
	}
	echo '</td></tr>';
}

/**
 * Render a duplicate section's editor panel (a tab in the Homepage Content list).
 */
function oss_home_render_dup_panel( $id, $dup, $icons ) {
	$type = isset( $dup['type'] ) ? $dup['type'] : '';
	$list = oss_home_sections_list();
	if ( ! isset( $list[ $type ] ) ) {
		return;
	}
	$opt = OSS_HOME_OPTION . '[dups][' . $id . ']';
	?>
	<details class="oss-section oss-section--dup" id="oss-section-dup-<?php echo esc_attr( $id ); ?>" data-oss-type="<?php echo esc_attr( $type ); ?>">
		<summary><span class="oss-section__title"><?php echo esc_html( $list[ $type ]['label'] ); ?> <em><?php esc_html_e( '(copy)', 'astra-child' ); ?></em></span><span class="oss-section__hint"><?php esc_html_e( 'Duplicate — click to expand / collapse', 'astra-child' ); ?></span></summary>
		<div class="oss-section__body">
			<input type="hidden" name="<?php echo esc_attr( $opt . '[type]' ); ?>" value="<?php echo esc_attr( $type ); ?>">
			<table class="form-table">
				<?php foreach ( oss_home_type_fields( $type ) as $field ) {
					oss_home_dup_control( $opt, $dup, $field, $icons );
				} ?>
			</table>
			<p><button type="button" class="button-link-delete oss-dup-remove" data-dup="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Remove this duplicate', 'astra-child' ); ?></button></p>
		</div>
	</details>
	<?php
}

/* ------------------------------------------------------------------ */
/* AJAX: create / delete a duplicate                                   */
/* ------------------------------------------------------------------ */

add_action( 'wp_ajax_oss_home_dup_create', 'oss_home_dup_create_ajax' );
function oss_home_dup_create_ajax() {
	check_ajax_referer( 'oss_home_dup', 'nonce' );
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( array( 'msg' => 'forbidden' ) );
	}
	$type   = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';
	$source = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : '';
	if ( ! array_key_exists( $type, oss_home_sections_list() ) ) {
		wp_send_json_error( array( 'msg' => 'bad type' ) );
	}

	$o    = get_option( OSS_HOME_OPTION, array() );
	$o    = is_array( $o ) ? $o : array();
	$dups = ( isset( $o['dups'] ) && is_array( $o['dups'] ) ) ? $o['dups'] : array();

	$content = array( 'type' => $type );
	if ( isset( $dups[ $source ] ) && is_array( $dups[ $source ] ) ) {
		foreach ( $dups[ $source ] as $k => $v ) {
			if ( 'type' !== $k ) {
				$content[ $k ] = $v;
			}
		}
	} else {
		foreach ( oss_home_type_fields( $type ) as $f ) {
			$content[ $f ] = oss_home_get( $f );
		}
	}

	$nid          = 'dup_' . $type . '_' . substr( md5( uniqid( '', true ) ), 0, 6 );
	$dups[ $nid ] = $content;
	$o['dups']    = $dups;

	$order = oss_home_section_order();
	$pos   = array_search( $source, $order, true );
	if ( false === $pos ) {
		$order[] = $nid;
	} else {
		array_splice( $order, $pos + 1, 0, $nid );
	}
	$o['section_order'] = $order;

	update_option( OSS_HOME_OPTION, $o );
	wp_send_json_success( array( 'id' => $nid ) );
}

add_action( 'wp_ajax_oss_home_dup_delete', 'oss_home_dup_delete_ajax' );
function oss_home_dup_delete_ajax() {
	check_ajax_referer( 'oss_home_dup', 'nonce' );
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( array( 'msg' => 'forbidden' ) );
	}
	$id = isset( $_POST['id'] ) ? sanitize_key( wp_unslash( $_POST['id'] ) ) : '';
	$o  = get_option( OSS_HOME_OPTION, array() );
	$o  = is_array( $o ) ? $o : array();
	if ( isset( $o['dups'][ $id ] ) ) {
		unset( $o['dups'][ $id ] );
	}
	if ( isset( $o['section_order'] ) && is_array( $o['section_order'] ) ) {
		$o['section_order'] = array_values( array_diff( $o['section_order'], array( $id ) ) );
	}
	update_option( OSS_HOME_OPTION, $o );
	wp_send_json_success();
}

/* ------------------------------------------------------------------ */
/* Admin assets: Duplicate button + AJAX wiring + styles               */
/* ------------------------------------------------------------------ */

add_action( 'admin_enqueue_scripts', 'oss_home_dups_admin_assets', 20 );
function oss_home_dups_admin_assets( $hook ) {
	if ( 'appearance_page_oss-home-content' !== $hook ) {
		return;
	}
	$css = <<<'CSS'
		#oss-home-sections{margin-top:4px;}
		.oss-section{border:1px solid #dcdcde;border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,.04);margin:0 0 10px;overflow:hidden;transition:box-shadow .15s ease,border-color .15s ease;}
		.oss-section:hover{border-color:#c3c4c7;box-shadow:0 2px 10px rgba(0,0,0,.07);}
		.oss-section > summary{padding:13px 16px;gap:8px;transition:background .12s ease;}
		.oss-section > summary::before{content:none;}
		.oss-section > summary::after{content:"";width:8px;height:8px;border-right:2px solid #a7aaad;border-bottom:2px solid #a7aaad;transform:rotate(45deg);transition:transform .18s ease;flex:0 0 auto;margin:0 2px 0 10px;}
		.oss-section[open] > summary::after{transform:rotate(-135deg);}
		.oss-section > summary:hover::after{border-color:#50575e;}
		.oss-section[open] > summary{border-bottom:1px solid #ececed;background:#fbfbfc;}
		.oss-section__body{padding:14px 16px 8px;}
		.oss-section__handle{color:#a7aaad;}
		.oss-section__handle:hover{color:#50575e;background:#f0f0f1;}
		.oss-section--dup{border-left:3px solid #2271b1;}
		.oss-section--dup > summary .oss-section__title em{font-style:normal;color:#2271b1;font-weight:600;}
		.oss-section__dup{display:inline-flex;align-items:center;gap:6px;flex:0 0 auto;margin-left:8px;padding-left:10px;border-left:1px solid #dcdcde;}
		.oss-section__dup .button{height:32px;min-height:32px;padding:0 12px;display:inline-flex;align-items:center;justify-content:center;gap:6px;border-radius:5px;white-space:nowrap;box-shadow:none;}
		.oss-section__dup .button .dashicons{display:block;width:18px;height:18px;font-size:18px;line-height:18px;}
		.oss-dup-btn .dashicons{color:#2271b1;}
		.oss-sec-delete{color:#b32d2e !important;border-color:#ccb3b3 !important;}
		.oss-sec-delete:hover{background:#b32d2e !important;color:#fff !important;border-color:#b32d2e !important;}
		.oss-sec-delete:hover .dashicons{color:#fff !important;}
		.oss-sec-hide{color:#7a6410 !important;border-color:#dbcb8e !important;}
		.oss-sec-hide .dashicons{color:#9a7d16 !important;}
		.oss-sec-hide:hover{background:#f7f0d6 !important;color:#5f4e0c !important;border-color:#cbb96a !important;}
		.oss-sec-hide:hover .dashicons{color:#5f4e0c !important;}
		.oss-sec-restore{color:#1e7e34 !important;border-color:#a3cfae !important;}
		.oss-sec-restore .dashicons{color:#1e7e34 !important;}
		.oss-sec-restore:hover{background:#1e7e34 !important;color:#fff !important;border-color:#1e7e34 !important;}
		.oss-sec-restore:hover .dashicons{color:#fff !important;}
		.oss-section--hidden{opacity:.6;}
		.oss-section--hidden > summary{background:#fcf2f2;}
		.oss-section--hidden > summary .oss-section__title:after{content:" · Hidden";color:#b32d2e;font-weight:600;font-size:12px;}
		@media (max-width:782px){.oss-section__dup{margin-left:0;border-left:0;padding-left:0;}}
CSS;
	wp_register_style( 'oss-home-dups-admin', false, array(), null );
	wp_enqueue_style( 'oss-home-dups-admin' );
	wp_add_inline_style( 'oss-home-dups-admin', $css );

	$nonce   = wp_create_nonce( 'oss_home_dup' );
	$removed = wp_json_encode( array_values( oss_home_removed() ) );
	$js      = <<<JS
		jQuery(function($){
			var NONCE = '{$nonce}';
			var REMOVED = {$removed};
			var MAP = window.ossHomeSectionMap || {};
			function isDup(id){ return (id || '').indexOf('oss-section-dup-') === 0; }

			// Add Duplicate + Delete (or Restore) buttons to every section tab.
			$('#oss-home-sections .oss-section').each(function(){
				var id = this.id, \$panel = $(this), \$sum = \$panel.children('summary');
				if (\$sum.find('.oss-section__dup').length) { return; }
				var key = MAP[id];
				var hidden = !isDup(id) && key && REMOVED.indexOf(key) !== -1;
				if (hidden) { \$panel.addClass('oss-section--hidden'); }
				var \$wrap = $('<span class="oss-section__dup"></span>');
				\$wrap.append('<button type="button" class="button oss-dup-btn" title="Duplicate this section"><span class="dashicons dashicons-admin-page"></span> Duplicate</button>');
				if (hidden) {
					\$wrap.append('<button type="button" class="button oss-sec-restore" title="Show this section on the page again"><span class="dashicons dashicons-visibility"></span> Restore</button>');
				} else if (isDup(id)) {
					\$wrap.append('<button type="button" class="button oss-sec-delete" title="Delete this duplicate permanently"><span class="dashicons dashicons-trash"></span> Delete</button>');
				} else {
					\$wrap.append('<button type="button" class="button oss-sec-delete oss-sec-hide" title="Hide from the page — you can restore it any time"><span class="dashicons dashicons-hidden"></span> Hide</button>');
				}
				\$sum.append(\$wrap);
			});
			// Tidy: move hidden sections to the bottom of the list.
			$('#oss-home-sections').children('.oss-section--hidden').appendTo('#oss-home-sections');

			function reload(){ window.location.reload(); }

			$('#oss-home-sections').on('click', '.oss-dup-btn', function(e){
				e.preventDefault(); e.stopPropagation();
				var \$panel = $(this).closest('.oss-section');
				var key = MAP[\$panel.attr('id')];
				var type = \$panel.data('oss-type') || key;
				if (!key || !type) { return; }
				var \$b = $(this); \$b.prop('disabled', true).text('Duplicating…');
				$.post(ajaxurl, { action:'oss_home_dup_create', nonce:NONCE, type:type, source:key })
					.done(function(r){ if (r && r.success) { reload(); } else { window.alert('Could not duplicate this section.'); \$b.prop('disabled', false).text('Duplicate'); } })
					.fail(function(){ window.alert('Could not duplicate this section.'); \$b.prop('disabled', false).text('Duplicate'); });
			});

			$('#oss-home-sections').on('click', '.oss-sec-delete', function(e){
				e.preventDefault(); e.stopPropagation();
				var id = $(this).closest('.oss-section').attr('id');
				var key = MAP[id];
				if (!key) { return; }
				var \$b = $(this);
				if (isDup(id)) {
					if (!window.confirm('Delete this duplicated section? This cannot be undone.')) { return; }
					\$b.prop('disabled', true);
					$.post(ajaxurl, { action:'oss_home_dup_delete', nonce:NONCE, id:key })
						.done(reload).fail(function(){ window.alert('Could not delete.'); \$b.prop('disabled', false); });
				} else {
					if (!window.confirm('Hide this section from the page?\\n\\nIt is NOT deleted — its content is kept and you can bring it back any time with Restore.')) { return; }
					\$b.prop('disabled', true);
					$.post(ajaxurl, { action:'oss_home_sec_hide', nonce:NONCE, key:key })
						.done(reload).fail(function(){ window.alert('Could not hide.'); \$b.prop('disabled', false); });
				}
			});

			$('#oss-home-sections').on('click', '.oss-sec-restore', function(e){
				e.preventDefault(); e.stopPropagation();
				var key = MAP[$(this).closest('.oss-section').attr('id')];
				if (!key) { return; }
				var \$b = $(this); \$b.prop('disabled', true);
				$.post(ajaxurl, { action:'oss_home_sec_restore', nonce:NONCE, key:key })
					.done(reload).fail(function(){ window.alert('Could not restore.'); \$b.prop('disabled', false); });
			});

			// Remove button inside a duplicate's own panel body.
			$('#oss-home-sections').on('click', '.oss-dup-remove', function(e){
				e.preventDefault();
				if (!window.confirm('Delete this duplicated section? This cannot be undone.')) { return; }
				var id = $(this).data('dup'); var \$b = $(this); \$b.prop('disabled', true);
				$.post(ajaxurl, { action:'oss_home_dup_delete', nonce:NONCE, id:id })
					.done(reload).fail(function(){ window.alert('Could not delete.'); \$b.prop('disabled', false); });
			});
		});
JS;
	wp_add_inline_script( 'jquery-core', $js );
}

/* ------------------------------------------------------------------ */
/* AJAX: hide / restore a built-in section                             */
/* ------------------------------------------------------------------ */

add_action( 'wp_ajax_oss_home_sec_hide', 'oss_home_sec_hide_ajax' );
function oss_home_sec_hide_ajax() {
	check_ajax_referer( 'oss_home_dup', 'nonce' );
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error();
	}
	$key = isset( $_POST['key'] ) ? sanitize_key( wp_unslash( $_POST['key'] ) ) : '';
	if ( ! array_key_exists( $key, oss_home_sections_list() ) ) {
		wp_send_json_error();
	}
	$o  = get_option( OSS_HOME_OPTION, array() );
	$o  = is_array( $o ) ? $o : array();
	$rm = ( isset( $o['removed'] ) && is_array( $o['removed'] ) ) ? $o['removed'] : array();
	if ( ! in_array( $key, $rm, true ) ) {
		$rm[] = $key;
	}
	$o['removed'] = array_values( $rm );
	update_option( OSS_HOME_OPTION, $o );
	wp_send_json_success();
}

add_action( 'wp_ajax_oss_home_sec_restore', 'oss_home_sec_restore_ajax' );
function oss_home_sec_restore_ajax() {
	check_ajax_referer( 'oss_home_dup', 'nonce' );
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error();
	}
	$key = isset( $_POST['key'] ) ? sanitize_key( wp_unslash( $_POST['key'] ) ) : '';
	$o   = get_option( OSS_HOME_OPTION, array() );
	$o   = is_array( $o ) ? $o : array();
	if ( isset( $o['removed'] ) && is_array( $o['removed'] ) ) {
		$o['removed'] = array_values( array_diff( $o['removed'], array( $key ) ) );
	}
	update_option( OSS_HOME_OPTION, $o );
	wp_send_json_success();
}
