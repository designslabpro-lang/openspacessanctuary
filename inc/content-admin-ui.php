<?php
/**
 * Shared UI for the content-editing admin pages (About, Contact, Events, …):
 * collapsible section toggles that match the Homepage Content page. Each page
 * wraps its sections with oss_cadmin_section_open()/_close(), prints the
 * toolbar with oss_cadmin_toolbar(), and enqueues the behaviour with
 * oss_cadmin_toggle_assets() from its own admin_enqueue_scripts hook.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Expand all / Collapse all toolbar. Print once, just under the page intro.
 */
function oss_cadmin_toolbar() {
	?>
	<p class="oss-sections-toolbar">
		<button type="button" class="button" id="oss-sections-expand"><?php esc_html_e( 'Expand all', 'astra-child' ); ?></button>
		<button type="button" class="button" id="oss-sections-collapse"><?php esc_html_e( 'Collapse all', 'astra-child' ); ?></button>
		<span class="description"><?php esc_html_e( 'Each section below is a toggle. Open the one you want to edit; your open/closed choices are remembered after saving.', 'astra-child' ); ?></span>
	</p>
	<?php
}

/**
 * Open a collapsible section. $id is a short slug (unique on the page); $title
 * is the visible heading.
 */
function oss_cadmin_section_open( $id, $title ) {
	printf(
		'<details class="oss-section" id="oss-section-%1$s"><summary><span class="oss-section__title">%2$s</span><span class="oss-section__hint">%3$s</span></summary><div class="oss-section__body">',
		esc_attr( $id ),
		esc_html( $title ),
		esc_html__( 'Click to expand / collapse', 'astra-child' )
	);
}

/**
 * Close a collapsible section opened with oss_cadmin_section_open().
 */
function oss_cadmin_section_close() {
	echo '</div></details>';
}

/**
 * Stateful helper: closes the previously opened section (if any) and opens a
 * new one. Call it where each section heading used to be, then call
 * oss_cadmin_sections_end() before the submit button — works whatever a
 * section contains (a form table, a repeater, anything).
 */
function oss_cadmin_section( $id, $title ) {
	if ( ! empty( $GLOBALS['__oss_cadmin_open'] ) ) {
		oss_cadmin_section_close();
	}
	oss_cadmin_section_open( $id, $title );
	$GLOBALS['__oss_cadmin_open'] = true;
}

/**
 * Close the last open section. Call once, just before submit_button().
 */
function oss_cadmin_sections_end() {
	if ( ! empty( $GLOBALS['__oss_cadmin_open'] ) ) {
		oss_cadmin_section_close();
	}
	$GLOBALS['__oss_cadmin_open'] = false;
}

/**
 * A labelled text/textarea row for an options-page form table. Generic over the
 * option name so any content editor can use it.
 */
function oss_cadmin_field_row( $option, $key, $value, $label, $type = 'text' ) {
	$id   = $option . '_' . $key;
	$name = $option . '[' . $key . ']';
	echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label></th><td>';
	if ( 'textarea' === $type ) {
		echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="5" class="large-text">' . esc_textarea( $value ) . '</textarea>';
	} else {
		echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="large-text">';
	}
	echo '</td></tr>';
}

/**
 * A media-picker row (attachment id) for an options-page form table.
 */
function oss_cadmin_image_row( $option, $key, $id, $label ) {
	$id  = (int) $id;
	$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';
	echo '<div class="oss-image-field" data-key="' . esc_attr( $key ) . '">';
	echo '<img src="' . esc_url( $src ) . '" style="max-width:180px;height:auto;display:' . ( $src ? 'block' : 'none' ) . ';margin-bottom:8px;border:1px solid #ddd;">';
	echo '<input type="hidden" name="' . esc_attr( $option . '[' . $key . ']' ) . '" class="oss-image-field__id" value="' . esc_attr( $id ) . '">';
	echo '<p><button type="button" class="button oss-image-field__select">' . esc_html__( 'Select Image', 'astra-child' ) . '</button> ';
	echo '<button type="button" class="button oss-image-field__remove"' . ( $src ? '' : ' style="display:none;"' ) . '>' . esc_html__( 'Remove', 'astra-child' ) . '</button></p>';
	echo '</div></td></tr>';
}

/**
 * The core media-picker JS for oss_cadmin_image_row() rows. Call from a page's
 * admin_enqueue hook (after wp_enqueue_media()). Delegated, so it also covers
 * rows added dynamically.
 */
function oss_cadmin_media_js() {
	$js = <<<'JS'
		jQuery(function($){
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
	wp_add_inline_script( 'jquery-core', $js );
}

/**
 * One repeatable icon/label(/link) card row. Pair with oss_cadmin_card_repeater()
 * and oss_cadmin_repeater_js(). Duplicate + Remove buttons per row.
 */
function oss_cadmin_card_row( $option, $field, $i, $row, $icons, $with_url = false ) {
	$row = wp_parse_args( (array) $row, array( 'icon' => 'compass', 'label' => '', 'url' => '/contact/' ) );
	$n   = $option . '[' . $field . '][' . $i . ']';
	?>
	<div class="oss-repeater-row" style="border:1px solid #ccd0d4;background:#fff;padding:12px 16px;margin-bottom:12px;max-width:760px;">
		<p style="margin:0 0 8px;"><label><strong><?php esc_html_e( 'Icon', 'astra-child' ); ?></strong><br>
			<select name="<?php echo esc_attr( $n . '[icon]' ); ?>">
				<?php foreach ( array_keys( $icons ) as $ik ) : ?>
					<option value="<?php echo esc_attr( $ik ); ?>" <?php selected( $row['icon'], $ik ); ?>><?php echo esc_html( ucfirst( $ik ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</label></p>
		<p style="margin:0 0 8px;"><label><strong><?php esc_html_e( 'Label', 'astra-child' ); ?></strong><br>
			<input type="text" class="large-text" name="<?php echo esc_attr( $n . '[label]' ); ?>" value="<?php echo esc_attr( $row['label'] ); ?>"></label></p>
		<?php if ( $with_url ) : ?>
			<p style="margin:0 0 8px;"><label><strong><?php esc_html_e( 'Link', 'astra-child' ); ?></strong><br>
				<input type="text" class="regular-text" name="<?php echo esc_attr( $n . '[url]' ); ?>" value="<?php echo esc_attr( $row['url'] ); ?>"></label></p>
		<?php endif; ?>
		<p style="margin:0;">
			<button type="button" class="button oss-repeater-row__duplicate"><?php esc_html_e( 'Duplicate', 'astra-child' ); ?></button>
			<button type="button" class="button-link-delete oss-repeater-row__remove" style="margin-left:10px;"><?php esc_html_e( 'Remove', 'astra-child' ); ?></button>
		</p>
	</div>
	<?php
}

/**
 * A repeatable list of card rows with an "Add" button. Rows with an empty label
 * are dropped on save (the page's sanitizer handles that).
 */
function oss_cadmin_card_repeater( $option, $field, $rows, $icons, $with_url = false, $add_label = '' ) {
	$add_label = $add_label ? $add_label : __( '+ Add Card', 'astra-child' );
	?>
	<div class="oss-repeater" data-field="<?php echo esc_attr( $field ); ?>">
		<div class="oss-repeater__rows">
			<?php foreach ( array_values( (array) $rows ) as $i => $row ) { oss_cadmin_card_row( $option, $field, $i, $row, $icons, $with_url ); } ?>
		</div>
		<p><button type="button" class="button button-secondary oss-repeater__add"><?php echo esc_html( $add_label ); ?></button></p>
		<template class="oss-repeater__tpl"><?php oss_cadmin_card_row( $option, $field, '__i__', array(), $icons, $with_url ); ?></template>
	</div>
	<?php
}

/**
 * Delegated add/duplicate/remove behaviour for oss_cadmin_card_repeater(). Call
 * from a page's admin_enqueue hook.
 */
function oss_cadmin_repeater_js() {
	$js = <<<'JS'
		jQuery(function($){
			function reindex($row, field){
				var k = 'n' + Date.now() + Math.floor(Math.random() * 1000);
				var re = new RegExp('\\[' + field + '\\]\\[[^\\]]+\\]');
				$row.find('[name]').each(function(){ this.name = this.name.replace(re, '[' + field + '][' + k + ']'); });
			}
			$(document).on('click', '.oss-repeater__add', function(){
				var $rep = $(this).closest('.oss-repeater');
				var html = $rep.find('.oss-repeater__tpl').html().replace(/__i__/g, 'n' + Date.now());
				$rep.find('.oss-repeater__rows').append(html);
			});
			$(document).on('click', '.oss-repeater-row__duplicate', function(){
				var $row = $(this).closest('.oss-repeater-row');
				var $rep = $row.closest('.oss-repeater');
				var $c = $row.clone();
				var $src = $row.find('select, input, textarea');
				$c.find('select, input, textarea').each(function(idx){ $(this).val($src.eq(idx).val()); });
				reindex($c, $rep.data('field'));
				$row.after($c);
			});
			$(document).on('click', '.oss-repeater-row__remove', function(){ $(this).closest('.oss-repeater-row').remove(); });
		});
JS;
	wp_add_inline_script( 'jquery-core', $js );
}

/**
 * Enqueue the shared toggle CSS + JS. Safe to call from several pages; the
 * open/closed memory is namespaced per admin page via the ?page= slug.
 */
function oss_cadmin_toggle_assets() {
	$css = <<<'CSS'
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
CSS;
	wp_register_style( 'oss-cadmin-toggles', false, array(), null );
	wp_enqueue_style( 'oss-cadmin-toggles' );
	wp_add_inline_style( 'oss-cadmin-toggles', $css );

	$js = <<<'JS'
		jQuery(function($){
			// Remember which sections are open, per admin page, so the section you
			// were editing stays open after a Save reloads the screen.
			var page = (new URLSearchParams(window.location.search)).get('page') || 'content';
			var STORE = 'ossCAdminOpen:' + page;
			var $sections = $('.oss-section');
			if (!$sections.length) { return; }
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
JS;
	wp_add_inline_script( 'jquery-core', $js );
}
