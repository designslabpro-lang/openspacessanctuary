<?php
/**
 * Section Builder — admin meta box UI.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes_page', 'oss_sb_add_meta_box' );
function oss_sb_add_meta_box() {
	add_meta_box(
		'oss-section-builder',
		__( 'Page Sections (Section Builder)', 'astra-child' ),
		'oss_sb_meta_box',
		'page',
		'normal',
		'high'
	);
}

add_action( 'admin_enqueue_scripts', 'oss_sb_admin_assets' );
function oss_sb_admin_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
}

/**
 * One media picker (hidden id + preview + choose/remove).
 */
function oss_sb_admin_media( $name, $value, $label ) {
	$id  = (int) $value;
	$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	echo '<div class="oss-sb-media">';
	echo '<span class="oss-sb-media__label">' . esc_html( $label ) . '</span>';
	echo '<div class="oss-sb-media__preview">' . ( $src ? '<img src="' . esc_url( $src ) . '" alt="">' : '' ) . '</div>';
	echo '<input type="hidden" class="oss-sb-media__id" name="' . esc_attr( $name ) . '" value="' . esc_attr( $id ) . '">';
	echo '<button type="button" class="button oss-sb-media__choose">' . esc_html__( 'Choose Image', 'astra-child' ) . '</button> ';
	echo '<button type="button" class="button-link-delete oss-sb-media__remove"' . ( $id ? '' : ' style="display:none;"' ) . '>' . esc_html__( 'Remove', 'astra-child' ) . '</button>';
	echo '</div>';
}

/**
 * Render one builder row. $key is the array index (or __I__ for the JS template).
 */
function oss_sb_admin_row( $key, $block ) {
	$block = (array) $block;
	$n = function ( $f ) use ( $key ) {
		return 'oss_sections[' . $key . '][' . $f . ']';
	};
	$v = function ( $f, $d = '' ) use ( $block ) {
		return isset( $block[ $f ] ) ? $block[ $f ] : $d;
	};
	$slots = oss_sb_slots();
	?>
	<div class="oss-sb-row" data-key="<?php echo esc_attr( $key ); ?>">
		<div class="oss-sb-row__head">
			<span class="oss-sb-row__handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Use Move Up / Move Down to reorder', 'astra-child' ); ?>"></span>
			<label class="oss-sb-row__typewrap"><?php esc_html_e( 'Section type:', 'astra-child' ); ?>
				<select class="oss-sb-type" name="<?php echo esc_attr( $n( 'type' ) ); ?>">
					<?php foreach ( oss_sb_types() as $tk => $tl ) : ?>
						<option value="<?php echo esc_attr( $tk ); ?>" <?php selected( $v( 'type', 'two_col' ), $tk ); ?>><?php echo esc_html( $tl ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="oss-sb-row__bgwrap"><?php esc_html_e( 'Background:', 'astra-child' ); ?>
				<select name="<?php echo esc_attr( $n( 'bg' ) ); ?>">
					<?php foreach ( oss_sb_bg_choices() as $bk => $bl ) : ?>
						<option value="<?php echo esc_attr( $bk ); ?>" <?php selected( $v( 'bg' ), $bk ); ?>><?php echo esc_html( $bl ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<span class="oss-sb-row__actions">
				<button type="button" class="button oss-sb-up" title="<?php esc_attr_e( 'Move up', 'astra-child' ); ?>">&uarr;</button>
				<button type="button" class="button oss-sb-down" title="<?php esc_attr_e( 'Move down', 'astra-child' ); ?>">&darr;</button>
				<button type="button" class="button oss-sb-dup"><?php esc_html_e( 'Duplicate', 'astra-child' ); ?></button>
				<button type="button" class="button-link-delete oss-sb-remove"><?php esc_html_e( 'Remove', 'astra-child' ); ?></button>
			</span>
		</div>
		<div class="oss-sb-row__body">

			<!-- TWO COLUMN -->
			<div class="oss-sb-fields" data-type="two_col">
				<p><label><?php esc_html_e( 'Heading', 'astra-child' ); ?><br><input type="text" class="widefat" name="<?php echo esc_attr( $n( 'heading' ) ); ?>" value="<?php echo esc_attr( $v( 'heading' ) ); ?>"></label></p>
				<p><label><?php esc_html_e( 'Body (each line/blank line becomes a paragraph)', 'astra-child' ); ?><br><textarea class="widefat" rows="4" name="<?php echo esc_attr( $n( 'body' ) ); ?>"><?php echo esc_textarea( $v( 'body' ) ); ?></textarea></label></p>
				<?php oss_sb_admin_media( $n( 'image_id' ), $v( 'image_id' ), __( 'Image', 'astra-child' ) ); ?>
				<p class="oss-sb-inline">
					<label><?php esc_html_e( 'Image side', 'astra-child' ); ?>
						<select name="<?php echo esc_attr( $n( 'image_side' ) ); ?>">
							<option value="left" <?php selected( $v( 'image_side', 'left' ), 'left' ); ?>><?php esc_html_e( 'Left', 'astra-child' ); ?></option>
							<option value="right" <?php selected( $v( 'image_side' ), 'right' ); ?>><?php esc_html_e( 'Right', 'astra-child' ); ?></option>
						</select>
					</label>
					<label><?php esc_html_e( 'Button text', 'astra-child' ); ?> <input type="text" name="<?php echo esc_attr( $n( 'btn_text' ) ); ?>" value="<?php echo esc_attr( $v( 'btn_text' ) ); ?>"></label>
					<label><?php esc_html_e( 'Button link', 'astra-child' ); ?> <input type="text" name="<?php echo esc_attr( $n( 'btn_url' ) ); ?>" value="<?php echo esc_attr( $v( 'btn_url' ) ); ?>"></label>
				</p>
			</div>

			<!-- ICON GRID -->
			<div class="oss-sb-fields" data-type="icon_grid">
				<p><label><?php esc_html_e( 'Heading', 'astra-child' ); ?><br><input type="text" class="widefat" name="<?php echo esc_attr( $n( 'heading' ) ); ?>" value="<?php echo esc_attr( $v( 'heading' ) ); ?>"></label></p>
				<p><label><?php esc_html_e( 'Intro', 'astra-child' ); ?><br><textarea class="widefat" rows="2" name="<?php echo esc_attr( $n( 'intro' ) ); ?>"><?php echo esc_textarea( $v( 'intro' ) ); ?></textarea></label></p>
				<p class="description"><?php esc_html_e( 'Fill in as many cards as you need — empty ones are skipped.', 'astra-child' ); ?></p>
				<div class="oss-sb-cards">
					<?php for ( $i = 0; $i < $slots; $i++ ) : ?>
						<div class="oss-sb-cardrow">
							<select name="<?php echo esc_attr( $n( "card{$i}_icon" ) ); ?>">
								<?php foreach ( oss_sb_icon_choices() as $ic ) : ?>
									<option value="<?php echo esc_attr( $ic ); ?>" <?php selected( $v( "card{$i}_icon", 'compass' ), $ic ); ?>><?php echo esc_html( ucfirst( $ic ) ); ?></option>
								<?php endforeach; ?>
							</select>
							<input type="text" placeholder="<?php esc_attr_e( 'Card label', 'astra-child' ); ?>" name="<?php echo esc_attr( $n( "card{$i}_label" ) ); ?>" value="<?php echo esc_attr( $v( "card{$i}_label" ) ); ?>">
							<input type="text" placeholder="<?php esc_attr_e( 'Short text (optional)', 'astra-child' ); ?>" name="<?php echo esc_attr( $n( "card{$i}_text" ) ); ?>" value="<?php echo esc_attr( $v( "card{$i}_text" ) ); ?>">
						</div>
					<?php endfor; ?>
				</div>
			</div>

			<!-- TEXT -->
			<div class="oss-sb-fields" data-type="text">
				<p><label><?php esc_html_e( 'Heading', 'astra-child' ); ?><br><input type="text" class="widefat" name="<?php echo esc_attr( $n( 'heading' ) ); ?>" value="<?php echo esc_attr( $v( 'heading' ) ); ?>"></label></p>
				<p><label><?php esc_html_e( 'Body', 'astra-child' ); ?><br><textarea class="widefat" rows="5" name="<?php echo esc_attr( $n( 'body' ) ); ?>"><?php echo esc_textarea( $v( 'body' ) ); ?></textarea></label></p>
				<p><label><?php esc_html_e( 'Alignment', 'astra-child' ); ?>
					<select name="<?php echo esc_attr( $n( 'align' ) ); ?>">
						<option value="left" <?php selected( $v( 'align', 'left' ), 'left' ); ?>><?php esc_html_e( 'Left', 'astra-child' ); ?></option>
						<option value="center" <?php selected( $v( 'align' ), 'center' ); ?>><?php esc_html_e( 'Center', 'astra-child' ); ?></option>
					</select>
				</label></p>
			</div>

			<!-- CTA -->
			<div class="oss-sb-fields" data-type="cta">
				<p><label><?php esc_html_e( 'Heading', 'astra-child' ); ?><br><input type="text" class="widefat" name="<?php echo esc_attr( $n( 'heading' ) ); ?>" value="<?php echo esc_attr( $v( 'heading' ) ); ?>"></label></p>
				<p><label><?php esc_html_e( 'Body', 'astra-child' ); ?><br><textarea class="widefat" rows="3" name="<?php echo esc_attr( $n( 'body' ) ); ?>"><?php echo esc_textarea( $v( 'body' ) ); ?></textarea></label></p>
				<p class="oss-sb-inline">
					<label><?php esc_html_e( 'Button text', 'astra-child' ); ?> <input type="text" name="<?php echo esc_attr( $n( 'btn_text' ) ); ?>" value="<?php echo esc_attr( $v( 'btn_text' ) ); ?>"></label>
					<label><?php esc_html_e( 'Button link', 'astra-child' ); ?> <input type="text" name="<?php echo esc_attr( $n( 'btn_url' ) ); ?>" value="<?php echo esc_attr( $v( 'btn_url' ) ); ?>"></label>
				</p>
			</div>

			<!-- QUOTE -->
			<div class="oss-sb-fields" data-type="quote">
				<p><label><?php esc_html_e( 'Quote', 'astra-child' ); ?><br><textarea class="widefat" rows="3" name="<?php echo esc_attr( $n( 'quote' ) ); ?>"><?php echo esc_textarea( $v( 'quote' ) ); ?></textarea></label></p>
				<p><label><?php esc_html_e( 'Attribution / name', 'astra-child' ); ?><br><input type="text" class="widefat" name="<?php echo esc_attr( $n( 'cite' ) ); ?>" value="<?php echo esc_attr( $v( 'cite' ) ); ?>"></label></p>
				<?php oss_sb_admin_media( $n( 'image_id' ), $v( 'image_id' ), __( 'Photo (optional)', 'astra-child' ) ); ?>
			</div>

			<!-- PHOTO ROW -->
			<div class="oss-sb-fields" data-type="photo_row">
				<p><label><?php esc_html_e( 'Heading (optional)', 'astra-child' ); ?><br><input type="text" class="widefat" name="<?php echo esc_attr( $n( 'heading' ) ); ?>" value="<?php echo esc_attr( $v( 'heading' ) ); ?>"></label></p>
				<div class="oss-sb-photogrid">
					<?php for ( $i = 0; $i < $slots; $i++ ) : ?>
						<?php oss_sb_admin_media( $n( "photo{$i}_id" ), $v( "photo{$i}_id" ), sprintf( __( 'Photo %d', 'astra-child' ), $i + 1 ) ); ?>
					<?php endfor; ?>
				</div>
			</div>

		</div>
	</div>
	<?php
}

function oss_sb_meta_box( $post ) {
	wp_nonce_field( 'oss_sb_save', 'oss_sb_nonce' );
	$blocks = get_post_meta( $post->ID, OSS_SB_META, true );
	$blocks = is_array( $blocks ) ? $blocks : array();
	?>
	<style>
		.oss-sb-note { margin: 0 0 12px; }
		.oss-sb-row { border: 1px solid #c3c4c7; background: #fff; border-radius: 6px; margin-bottom: 14px; }
		.oss-sb-row__head { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 10px 12px; background: #f6f7f7; border-bottom: 1px solid #e0e0e0; border-radius: 6px 6px 0 0; }
		.oss-sb-row__handle { color: #787c82; cursor: default; }
		.oss-sb-row__actions { margin-left: auto; display: flex; gap: 6px; }
		.oss-sb-row__body { padding: 12px 14px; }
		.oss-sb-fields { display: none; }
		.oss-sb-fields.is-active { display: block; }
		.oss-sb-inline { display: flex; flex-wrap: wrap; gap: 16px; align-items: center; }
		.oss-sb-cardrow { display: grid; grid-template-columns: 130px 1fr 1fr; gap: 8px; margin-bottom: 8px; }
		.oss-sb-photogrid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
		.oss-sb-media { border: 1px dashed #c3c4c7; border-radius: 5px; padding: 8px; margin: 6px 0; }
		.oss-sb-media__label { display: block; font-weight: 600; margin-bottom: 4px; }
		.oss-sb-media__preview img { max-width: 120px; height: auto; display: block; margin-bottom: 6px; border-radius: 4px; }
	</style>

	<p class="oss-sb-note description"><?php esc_html_e( 'Sections added here appear at the bottom of this page — on any page, including Home, About, Contact, etc. (For a page built entirely of these sections, choose the "Section Builder" template under Page Attributes.) Add, duplicate, remove, and reorder as needed.', 'astra-child' ); ?></p>

	<div id="oss-sb-rows">
		<?php foreach ( array_values( $blocks ) as $i => $block ) { oss_sb_admin_row( $i, $block ); } ?>
	</div>

	<p>
		<button type="button" class="button button-primary" id="oss-sb-add"><?php esc_html_e( '+ Add Section', 'astra-child' ); ?></button>
	</p>

	<template id="oss-sb-tpl"><?php oss_sb_admin_row( '__I__', array() ); ?></template>

	<script>
	( function( $ ) {
		function toggleRow( $row ) {
			var t = $row.find( '.oss-sb-type' ).val();
			$row.find( '.oss-sb-fields' ).removeClass( 'is-active' ).filter( '[data-type="' + t + '"]' ).addClass( 'is-active' );
		}
		function uniqueKey() { return 'k' + Date.now() + Math.floor( Math.random() * 1000 ); }
		function reindex( $row, key ) {
			$row.attr( 'data-key', key );
			$row.find( '[name]' ).each( function() {
				this.name = this.name.replace( /oss_sections\[[^\]]+\]/, 'oss_sections[' + key + ']' );
			} );
		}

		$( function() {
			$( '#oss-sb-rows .oss-sb-row' ).each( function() { toggleRow( $( this ) ); } );
		} );

		$( document ).on( 'change', '.oss-sb-type', function() { toggleRow( $( this ).closest( '.oss-sb-row' ) ); } );

		$( document ).on( 'click', '#oss-sb-add', function() {
			var html = $( '#oss-sb-tpl' ).html().replace( /__I__/g, uniqueKey() );
			var $row = $( html );
			$( '#oss-sb-rows' ).append( $row );
			toggleRow( $row );
		} );

		$( document ).on( 'click', '.oss-sb-dup', function() {
			var $row = $( this ).closest( '.oss-sb-row' );
			var $src = $row.find( 'select, input, textarea' );
			var $c = $row.clone();
			$c.find( 'select, input, textarea' ).each( function( idx ) { $( this ).val( $src.eq( idx ).val() ); } );
			reindex( $c, uniqueKey() );
			$row.after( $c );
			toggleRow( $c );
		} );

		$( document ).on( 'click', '.oss-sb-remove', function() { $( this ).closest( '.oss-sb-row' ).remove(); } );
		$( document ).on( 'click', '.oss-sb-up', function() {
			var $row = $( this ).closest( '.oss-sb-row' ), $p = $row.prev( '.oss-sb-row' );
			if ( $p.length ) { $row.insertBefore( $p ); }
		} );
		$( document ).on( 'click', '.oss-sb-down', function() {
			var $row = $( this ).closest( '.oss-sb-row' ), $nx = $row.next( '.oss-sb-row' );
			if ( $nx.length ) { $row.insertAfter( $nx ); }
		} );

		// Media picker.
		$( document ).on( 'click', '.oss-sb-media__choose', function( e ) {
			e.preventDefault();
			var $wrap = $( this ).closest( '.oss-sb-media' );
			var frame = wp.media( { title: '<?php echo esc_js( __( 'Select image', 'astra-child' ) ); ?>', multiple: false } );
			frame.on( 'select', function() {
				var att = frame.state().get( 'selection' ).first().toJSON();
				var url = ( att.sizes && att.sizes.medium ) ? att.sizes.medium.url : att.url;
				$wrap.find( '.oss-sb-media__id' ).val( att.id );
				$wrap.find( '.oss-sb-media__preview' ).html( '<img src="' + url + '" alt="">' );
				$wrap.find( '.oss-sb-media__remove' ).show();
			} );
			frame.open();
		} );
		$( document ).on( 'click', '.oss-sb-media__remove', function( e ) {
			e.preventDefault();
			var $wrap = $( this ).closest( '.oss-sb-media' );
			$wrap.find( '.oss-sb-media__id' ).val( '' );
			$wrap.find( '.oss-sb-media__preview' ).empty();
			$( this ).hide();
		} );
	} )( jQuery );
	</script>
	<?php
}
