<?php
/**
 * Reusable section-engine ADMIN: reorder (drag + â/â), Duplicate, and Delete
 * (red) / Restore controls for any registered group's settings screen, plus
 * server-rendered duplicate editor panels and the AJAX behind the buttons.
 *
 * A page opts in by: registering its group (see section-engine.php), tagging
 * its panels with oss_cadmin_section($slug,$title,$key,$group), calling
 * oss_sec_render_dup_panels($group) before the submit button, and having its
 * sanitizer route 'section_order'/'dups'/'removed' through oss_sec_sanitize_key().
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Duplicate editor panels                                             */
/* ------------------------------------------------------------------ */

function oss_sec_field_label( $field ) {
	$suffix = preg_replace( '/^[a-z0-9]+_/', '', $field );
	$labels = array(
		'eyebrow' => __( 'Eyebrow', 'astra-child' ),
		'heading' => __( 'Heading', 'astra-child' ),
		'body'    => __( 'Body', 'astra-child' ),
		'body1'   => __( 'Paragraph 1', 'astra-child' ),
		'body2'   => __( 'Paragraph 2', 'astra-child' ),
		'quote'   => __( 'Pull Quote', 'astra-child' ),
		'intro'   => __( 'Intro', 'astra-child' ),
		'caption' => __( 'Caption', 'astra-child' ),
		'closing' => __( 'Closing Line', 'astra-child' ),
		'image_id'=> __( 'Image', 'astra-child' ),
		'layout'  => __( 'Section Design', 'astra-child' ),
		'name'    => __( 'Name', 'astra-child' ),
		'sub'     => __( 'Subheading', 'astra-child' ),
		'btn'      => __( 'Button Text', 'astra-child' ),
		'btn_text' => __( 'Button Text', 'astra-child' ),
		'btn_url'  => __( 'Button Link', 'astra-child' ),
	);
	return isset( $labels[ $suffix ] ) ? $labels[ $suffix ] : ucwords( str_replace( '_', ' ', $suffix ) );
}

function oss_sec_dup_media( $name, $id ) {
	$id  = (int) $id;
	$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	echo '<div class="oss-sec-media">';
	echo '<img src="' . esc_url( $src ) . '" style="max-width:160px;height:auto;display:' . ( $src ? 'block' : 'none' ) . ';margin-bottom:8px;border:1px solid #ddd;">';
	echo '<input type="hidden" name="' . esc_attr( $name ) . '" class="oss-sec-media__id" value="' . esc_attr( $id ) . '">';
	echo '<p><button type="button" class="button oss-sec-media__pick">' . esc_html__( 'Select Image', 'astra-child' ) . '</button> ';
	echo '<button type="button" class="button oss-sec-media__clear"' . ( $src ? '' : ' style="display:none;"' ) . '>' . esc_html__( 'Remove', 'astra-child' ) . '</button></p>';
	echo '</div>';
}

function oss_sec_dup_control( $group, $opt, $data, $field ) {
	$name  = $opt . '[' . $field . ']';
	$val   = array_key_exists( $field, (array) $data ) ? $data[ $field ] : oss_sec_get( $group, $field );
	$label = oss_sec_field_label( $field );
	echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';

	if ( '_image_id' === substr( $field, -9 ) ) {
		oss_sec_dup_media( $name, (int) $val );
	} elseif ( '_layout' === substr( $field, -7 ) ) {
		echo '<select name="' . esc_attr( $name ) . '">';
		echo '<option value="1" ' . selected( $val, '1', false ) . '>' . esc_html__( 'Style 1 (default)', 'astra-child' ) . '</option>';
		echo '<option value="2" ' . selected( $val, '2', false ) . '>' . esc_html__( 'Style 2', 'astra-child' ) . '</option>';
		echo '</select>';
	} elseif ( is_array( $val ) ) {
		echo '<p class="description">' . esc_html__( 'Copied from the original section. To change these items, edit the original section.', 'astra-child' ) . '</p>';
	} else {
		$suffix    = preg_replace( '/^[a-z0-9]+_/', '', $field );
		$textareas = array( 'body', 'body1', 'body2', 'body3', 'intro', 'quote', 'caption', 'closing' );
		if ( in_array( $suffix, $textareas, true ) || '_body' === substr( $field, -5 ) || '_quote' === substr( $field, -6 ) || '_intro' === substr( $field, -6 ) ) {
			echo '<textarea class="large-text" rows="4" name="' . esc_attr( $name ) . '">' . esc_textarea( $val ) . '</textarea>';
		} else {
			echo '<input type="text" class="large-text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '">';
		}
	}
	echo '</td></tr>';
}

function oss_sec_dup_panel( $group, $id, $dup ) {
	$cfg  = oss_sec_cfg( $group );
	$type = isset( $dup['type'] ) ? $dup['type'] : '';
	if ( ! $cfg || ! isset( $cfg['sections'][ $type ] ) ) {
		return;
	}
	$opt    = $cfg['option'] . '[dups][' . $id . ']';
	$fields = isset( $cfg['fields'][ $type ] ) ? $cfg['fields'][ $type ] : array();
	?>
	<details class="oss-section oss-section--dup" id="oss-section-dup-<?php echo esc_attr( $id ); ?>" data-oss-key="<?php echo esc_attr( $id ); ?>" data-oss-group="<?php echo esc_attr( $group ); ?>" data-oss-dup="1">
		<summary><span class="oss-section__title"><?php echo esc_html( $cfg['sections'][ $type ] ); ?> <em><?php esc_html_e( '(copy)', 'astra-child' ); ?></em></span><span class="oss-section__hint"><?php esc_html_e( 'Duplicate â click to expand / collapse', 'astra-child' ); ?></span></summary>
		<div class="oss-section__body">
			<input type="hidden" name="<?php echo esc_attr( $opt . '[type]' ); ?>" value="<?php echo esc_attr( $type ); ?>">
			<table class="form-table">
				<?php foreach ( $fields as $f ) { oss_sec_dup_control( $group, $opt, $dup, $f ); } ?>
			</table>
			<p><button type="button" class="button-link-delete oss-sec-dup-remove" data-dup="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Remove this duplicate', 'astra-child' ); ?></button></p>
		</div>
	</details>
	<?php
}

function oss_sec_render_dup_panels( $group ) {
	foreach ( oss_sec_dups( $group ) as $id => $dup ) {
		if ( is_array( $dup ) ) {
			oss_sec_dup_panel( $group, $id, $dup );
		}
	}
}

/* ------------------------------------------------------------------ */
/* AJAX                                                                */
/* ------------------------------------------------------------------ */

function oss_sec_ajax_guard() {
	check_ajax_referer( 'oss_sec', 'nonce' );
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( array( 'msg' => 'forbidden' ) );
	}
	$group = isset( $_POST['group'] ) ? sanitize_key( wp_unslash( $_POST['group'] ) ) : '';
	$cfg   = oss_sec_cfg( $group );
	if ( ! $cfg ) {
		wp_send_json_error( array( 'msg' => 'bad group' ) );
	}
	return array( $group, $cfg );
}

add_action( 'wp_ajax_oss_sec_dup_create', 'oss_sec_dup_create_ajax' );
function oss_sec_dup_create_ajax() {
	list( $group, $cfg ) = oss_sec_ajax_guard();
	$type   = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';
	$source = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : '';
	if ( ! isset( $cfg['sections'][ $type ] ) ) {
		wp_send_json_error( array( 'msg' => 'bad type' ) );
	}
	$opt  = $cfg['option'];
	$o    = get_option( $opt, array() );
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
		$fields = isset( $cfg['fields'][ $type ] ) ? $cfg['fields'][ $type ] : array();
		foreach ( $fields as $f ) {
			$content[ $f ] = oss_sec_get( $group, $f );
		}
	}

	$nid          = 'dup_' . $type . '_' . substr( md5( uniqid( '', true ) ), 0, 6 );
	$dups[ $nid ] = $content;
	$o['dups']    = $dups;

	$order = oss_sec_order( $group );
	$pos   = array_search( $source, $order, true );
	if ( false === $pos ) {
		$order[] = $nid;
	} else {
		array_splice( $order, $pos + 1, 0, $nid );
	}
	$o['section_order'] = $order;

	update_option( $opt, $o );
	wp_send_json_success( array( 'id' => $nid ) );
}

add_action( 'wp_ajax_oss_sec_dup_delete', 'oss_sec_dup_delete_ajax' );
function oss_sec_dup_delete_ajax() {
	list( $group, $cfg ) = oss_sec_ajax_guard();
	$id  = isset( $_POST['id'] ) ? sanitize_key( wp_unslash( $_POST['id'] ) ) : '';
	$opt = $cfg['option'];
	$o   = get_option( $opt, array() );
	$o   = is_array( $o ) ? $o : array();
	if ( isset( $o['dups'][ $id ] ) ) {
		unset( $o['dups'][ $id ] );
	}
	if ( isset( $o['section_order'] ) && is_array( $o['section_order'] ) ) {
		$o['section_order'] = array_values( array_diff( $o['section_order'], array( $id ) ) );
	}
	update_option( $opt, $o );
	wp_send_json_success();
}

add_action( 'wp_ajax_oss_sec_hide', 'oss_sec_hide_ajax' );
function oss_sec_hide_ajax() {
	list( $group, $cfg ) = oss_sec_ajax_guard();
	$key = isset( $_POST['key'] ) ? sanitize_key( wp_unslash( $_POST['key'] ) ) : '';
	if ( ! isset( $cfg['sections'][ $key ] ) ) {
		wp_send_json_error();
	}
	$opt = $cfg['option'];
	$o   = get_option( $opt, array() );
	$o   = is_array( $o ) ? $o : array();
	$rm  = ( isset( $o['removed'] ) && is_array( $o['removed'] ) ) ? $o['removed'] : array();
	if ( ! in_array( $key, $rm, true ) ) {
		$rm[] = $key;
	}
	$o['removed'] = array_values( $rm );
	update_option( $opt, $o );
	wp_send_json_success();
}

add_action( 'wp_ajax_oss_sec_restore', 'oss_sec_restore_ajax' );
function oss_sec_restore_ajax() {
	list( $group, $cfg ) = oss_sec_ajax_guard();
	$key = isset( $_POST['key'] ) ? sanitize_key( wp_unslash( $_POST['key'] ) ) : '';
	$opt = $cfg['option'];
	$o   = get_option( $opt, array() );
	$o   = is_array( $o ) ? $o : array();
	if ( isset( $o['removed'] ) && is_array( $o['removed'] ) ) {
		$o['removed'] = array_values( array_diff( $o['removed'], array( $key ) ) );
	}
	update_option( $opt, $o );
	wp_send_json_success();
}

/* ------------------------------------------------------------------ */
/* Admin assets (per registered group's settings page)                */
/* ------------------------------------------------------------------ */

add_action( 'admin_enqueue_scripts', 'oss_sec_admin_assets_all', 25 );
function oss_sec_admin_assets_all( $hook ) {
	if ( empty( $GLOBALS['oss_sec_groups'] ) ) {
		return;
	}
	foreach ( $GLOBALS['oss_sec_groups'] as $group => $cfg ) {
		if ( isset( $cfg['page_hook'] ) && $cfg['page_hook'] === $hook ) {
			oss_sec_admin_enqueue( $group, $cfg );
			return;
		}
	}
}

function oss_sec_admin_enqueue( $group, $cfg ) {
	wp_enqueue_media();
	wp_enqueue_script( 'jquery-ui-sortable' );

	$css = <<<'CSS'
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
		.oss-section__hint{display:none;}
		.oss-section__handle{cursor:grab;color:#8c8f94;font-size:18px;width:26px;height:30px;line-height:30px;text-align:center;border-radius:4px;flex:0 0 auto;transition:color .12s ease,background .12s ease;}
		.oss-section__handle:hover{color:#1d2327;background:#f0f0f1;}
		.oss-section__handle:active{cursor:grabbing;}
		.oss-section__title{flex:1 1 auto;}
		.oss-section__move{display:inline-flex;align-items:center;gap:4px;flex:0 0 auto;margin-left:auto;}
		.oss-section__move .button{width:32px;height:32px;min-height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:5px;color:#50575e;box-shadow:none;}
		.oss-section__move .button:hover{color:#135e96;border-color:#135e96;background:#f6f7f7;}
		.oss-section__dup{display:inline-flex;align-items:center;gap:6px;flex:0 0 auto;margin-left:8px;padding-left:10px;border-left:1px solid #dcdcde;}
		.oss-section__dup .button{height:32px;min-height:32px;padding:0 12px;display:inline-flex;align-items:center;justify-content:center;gap:6px;border-radius:5px;white-space:nowrap;box-shadow:none;}
		.oss-section__move .button .dashicons,.oss-section__dup .button .dashicons{display:block;width:18px;height:18px;font-size:18px;line-height:18px;}
		.oss-dup-btn .dashicons{color:#2271b1;}
		.oss-sortable-placeholder{border:2px dashed #2271b1;background:#f0f6fc;border-radius:4px;margin:0 0 12px;height:54px;max-width:1100px;}
		.oss-section.ui-sortable-helper{box-shadow:0 10px 26px rgba(0,0,0,.16);}
		.oss-section--dup{border-left:3px solid #2271b1;}
		.oss-section--dup > summary .oss-section__title em{font-style:normal;color:#2271b1;font-weight:600;}
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
		.oss-section--hidden > summary .oss-section__title:after{content:" \00b7 Hidden";color:#b32d2e;font-weight:600;font-size:12px;}
		.oss-sec-media{border:1px dashed #c3c4c7;border-radius:5px;padding:8px;margin:4px 0;}
		@media (max-width:782px){.oss-section > summary{flex-wrap:wrap;}.oss-section__title{flex:1 1 100%;}.oss-section__move{margin-left:0;}.oss-section__dup{margin-left:0;border-left:0;padding-left:0;}}
CSS;
	wp_register_style( 'oss-sec-admin', false, array(), null );
	wp_enqueue_style( 'oss-sec-admin' );
	wp_add_inline_style( 'oss-sec-admin', $css );

	$data = array(
		'group'   => $group,
		'option'  => $cfg['option'],
		'nonce'   => wp_create_nonce( 'oss_sec' ),
		'order'   => array_values( oss_sec_order( $group ) ),
		'removed' => array_values( oss_sec_removed( $group ) ),
	);
	wp_register_script( 'oss-sec-admin', '', array( 'jquery-core', 'jquery-ui-sortable' ), null, true );
	wp_enqueue_script( 'oss-sec-admin' );
	wp_add_inline_script( 'oss-sec-admin', 'window.ossSecData=' . wp_json_encode( $data ) . ';', 'before' );
	wp_add_inline_script( 'oss-sec-admin', oss_sec_admin_js() );
}

function oss_sec_admin_js() {
	return <<<'JS'
	jQuery(function($){
		var D = window.ossSecData || {};
		if (!D.group) { return; }
		var SEL = '.oss-section[data-oss-group="' + D.group + '"]';
		var $panels = $(SEL);
		if (!$panels.length) { return; }

		// Wrap the group's panels in a sortable container.
		var $wrap = $('#oss-sec-wrap-' + D.group);
		if (!$wrap.length) {
			$wrap = $('<div class="oss-sec-wrap" id="oss-sec-wrap-' + D.group + '"></div>');
			$panels.first().before($wrap);
			$wrap.append($panels);
		}

		function keyOf($p){ return $p.attr('data-oss-key'); }
		function isDup($p){ return $p.attr('data-oss-dup') === '1'; }

		$wrap.children('.oss-section').each(function(){
			var $p = $(this), key = keyOf($p);
			if ($p.children('summary').find('.oss-section__handle').length) { return; }
			var $sum = $p.children('summary');
			var hidden = !isDup($p) && $.inArray(key, D.removed || []) !== -1;
			if (hidden) { $p.addClass('oss-section--hidden'); }
			$sum.prepend('<span class="oss-section__handle dashicons dashicons-menu" title="Drag to reorder"></span>');
			$sum.append('<span class="oss-section__move"><button type="button" class="button oss-move-up" title="Move up"><span class="dashicons dashicons-arrow-up-alt2"></span></button><button type="button" class="button oss-move-down" title="Move down"><span class="dashicons dashicons-arrow-down-alt2"></span></button></span>');
			var dupBtn = '<button type="button" class="button oss-dup-btn" title="Duplicate this section"><span class="dashicons dashicons-admin-page"></span> Duplicate</button>';
			var actBtn;
			if (hidden) {
				actBtn = '<button type="button" class="button oss-sec-restore" title="Show this section on the page again"><span class="dashicons dashicons-visibility"></span> Restore</button>';
			} else if (isDup($p)) {
				actBtn = '<button type="button" class="button oss-sec-delete" title="Delete this duplicate permanently"><span class="dashicons dashicons-trash"></span> Delete</button>';
			} else {
				actBtn = '<button type="button" class="button oss-sec-delete oss-sec-hide" title="Hide from the page — you can restore it any time"><span class="dashicons dashicons-hidden"></span> Hide</button>';
			}
			$sum.append('<span class="oss-section__dup">' + dupBtn + actBtn + '</span>');
			if (!$p.children('.oss-order-input').length) {
				$('<input>', { type:'hidden', 'class':'oss-order-input', name: D.option + '[section_order][]', value: key }).appendTo($p);
			}
		});

		// Apply saved order.
		(D.order || []).forEach(function(key){
			var el = $wrap.children('[data-oss-key="' + key + '"]').get(0);
			if (el) { $wrap.append(el); }
		});

		function reload(){ window.location.reload(); }
		function post(action, extra, $btn, failMsg){
			var body = $.extend({ action: action, nonce: D.nonce, group: D.group }, extra);
			$.post(ajaxurl, body).done(function(r){ reload(); }).fail(function(){ window.alert(failMsg); if ($btn) { $btn.prop('disabled', false); } });
		}

		$wrap.on('click', '.oss-section__handle, .oss-move-up, .oss-move-down, .oss-dup-btn, .oss-sec-delete, .oss-sec-restore', function(e){ e.preventDefault(); e.stopPropagation(); });
		function moveSafe($p, fn){
			if (window.ossCadminSaveRemove) { window.ossCadminSaveRemove($p); }
			fn();
			if (window.ossCadminInitEditors) { setTimeout(window.ossCadminInitEditors, 40); }
		}
		$wrap.on('click', '.oss-move-up', function(){ var $p=$(this).closest('.oss-section'), $x=$p.prev('.oss-section'); if ($x.length) { moveSafe($p, function(){ $p.insertBefore($x); }); } });
		$wrap.on('click', '.oss-move-down', function(){ var $p=$(this).closest('.oss-section'), $x=$p.next('.oss-section'); if ($x.length) { moveSafe($p, function(){ $p.insertAfter($x); }); } });

		$wrap.on('click', '.oss-dup-btn', function(){
			var $p=$(this).closest('.oss-section'), key=keyOf($p);
			// For an original the key is the section type; a duplicate carries its type in data-oss-type.
			var srcType = $p.attr('data-oss-type') || key;
			var $b=$(this); $b.prop('disabled', true).text('Duplicatingâ¦');
			post('oss_sec_dup_create', { type: srcType, source: key }, $b, 'Could not duplicate.');
		});

		$wrap.on('click', '.oss-sec-delete', function(){
			var $p=$(this).closest('.oss-section'), key=keyOf($p), $b=$(this);
			if (isDup($p)) {
				if (!window.confirm('Delete this duplicated section? This cannot be undone.')) { return; }
				$b.prop('disabled', true);
				post('oss_sec_dup_delete', { id: key }, $b, 'Could not delete.');
			} else {
				if (!window.confirm('Hide this section from the page?\n\nIt is NOT deleted — its content is kept and you can bring it back any time with Restore.')) { return; }
				$b.prop('disabled', true);
				post('oss_sec_hide', { key: key }, $b, 'Could not hide.');
			}
		});
		$wrap.on('click', '.oss-sec-restore', function(){
			var key=keyOf($(this).closest('.oss-section')), $b=$(this); $b.prop('disabled', true);
			post('oss_sec_restore', { key: key }, $b, 'Could not restore.');
		});
		$wrap.on('click', '.oss-sec-dup-remove', function(e){
			e.preventDefault();
			if (!window.confirm('Delete this duplicated section? This cannot be undone.')) { return; }
			var id=$(this).data('dup'), $b=$(this); $b.prop('disabled', true);
			post('oss_sec_dup_delete', { id: id }, $b, 'Could not delete.');
		});

		// Media picker for duplicate image fields.
		$wrap.on('click', '.oss-sec-media__pick', function(e){
			e.preventDefault();
			var $m=$(this).closest('.oss-sec-media');
			var frame = wp.media({ title:'Select image', multiple:false });
			frame.on('select', function(){
				var a=frame.state().get('selection').first().toJSON();
				var url=(a.sizes && a.sizes.medium)?a.sizes.medium.url:a.url;
				$m.find('.oss-sec-media__id').val(a.id);
				$m.find('img').attr('src', url).show();
				$m.find('.oss-sec-media__clear').show();
			});
			frame.open();
		});
		$wrap.on('click', '.oss-sec-media__clear', function(e){
			e.preventDefault();
			var $m=$(this).closest('.oss-sec-media');
			$m.find('.oss-sec-media__id').val('');
			$m.find('img').hide();
			$(this).hide();
		});

		if ($.fn.sortable) {
			$wrap.sortable({
				handle:'.oss-section__handle', items:'> .oss-section', placeholder:'oss-sortable-placeholder', forcePlaceholderSize:true, tolerance:'pointer', axis:'y',
				start: function(e, ui){ if (window.ossCadminSaveRemove) { window.ossCadminSaveRemove(ui.item); } },
				stop:  function(e, ui){ if (window.ossCadminInitEditors) { setTimeout(window.ossCadminInitEditors, 40); } }
			});
		}
	});
JS;
}
