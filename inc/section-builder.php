<?php
/**
 * Section Builder — a reusable, per-page flexible-content system.
 *
 * Assign the "Section Builder" page template to any page, then use the
 * "Page Sections" meta box to add ready-made section templates (two-column,
 * icon grid, text, CTA, quote, photo row). Sections can be added, duplicated,
 * removed, and reordered. Content is stored as post meta (_oss_sections), so
 * uploading theme files never touches it.
 */

defined( 'ABSPATH' ) || exit;

define( 'OSS_SB_META', '_oss_sections' );

/**
 * The ready-made section types the client can choose from.
 */
function oss_sb_types() {
	return array(
		'two_col'   => __( 'Two-Column: Image + Text', 'astra-child' ),
		'icon_grid' => __( 'Icon / Feature Grid', 'astra-child' ),
		'text'      => __( 'Full-Width Text', 'astra-child' ),
		'cta'       => __( 'Call-to-Action Banner', 'astra-child' ),
		'quote'     => __( 'Quote / Testimonial', 'astra-child' ),
		'photo_row' => __( 'Photo Row', 'astra-child' ),
	);
}

/**
 * Background choices shared by every section type.
 */
function oss_sb_bg_choices() {
	return array(
		''      => __( 'Cream (default)', 'astra-child' ),
		'white' => __( 'White', 'astra-child' ),
		'sand'  => __( 'Sand', 'astra-child' ),
		'sage'  => __( 'Sage (dark)', 'astra-child' ),
	);
}

/**
 * Icon choices for the icon-grid cards, taken from the shared library.
 */
function oss_sb_icon_choices() {
	if ( function_exists( 'oss_home_icon_library' ) ) {
		return array_keys( oss_home_icon_library() );
	}
	return array( 'compass', 'heart', 'star', 'shield', 'hands', 'ribbon', 'bloom', 'people', 'leaf' );
}

/** Max fixed slots for the grid cards and photo row (blanks are ignored). */
function oss_sb_slots() {
	return 6;
}

/* ==================================================================== */
/* Save                                                                  */
/* ==================================================================== */

add_action( 'save_post_page', 'oss_sb_save', 10, 2 );
function oss_sb_save( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['oss_sb_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['oss_sb_nonce'] ) ), 'oss_sb_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	$raw = isset( $_POST['oss_sections'] ) && is_array( $_POST['oss_sections'] ) ? wp_unslash( $_POST['oss_sections'] ) : array();
	$out = array();
	foreach ( $raw as $block ) {
		$clean = oss_sb_sanitize_block( (array) $block );
		if ( $clean ) {
			$out[] = $clean;
		}
	}

	if ( $out ) {
		update_post_meta( $post_id, OSS_SB_META, $out );
	} else {
		delete_post_meta( $post_id, OSS_SB_META );
	}
}

/**
 * Sanitize a single submitted block down to just the fields its type uses.
 */
function oss_sb_sanitize_block( $b ) {
	$types = oss_sb_types();
	$type  = isset( $b['type'] ) ? sanitize_key( $b['type'] ) : '';
	if ( ! isset( $types[ $type ] ) ) {
		return null;
	}

	$bg  = isset( $b['bg'] ) ? sanitize_key( $b['bg'] ) : '';
	if ( ! array_key_exists( $bg, oss_sb_bg_choices() ) ) {
		$bg = '';
	}
	$out = array( 'type' => $type, 'bg' => $bg );

	$text = function ( $k ) use ( $b ) {
		return isset( $b[ $k ] ) ? sanitize_text_field( $b[ $k ] ) : '';
	};
	$rich = function ( $k ) use ( $b ) {
		return isset( $b[ $k ] ) ? wp_kses_post( $b[ $k ] ) : '';
	};
	$url = function ( $k ) use ( $b ) {
		return isset( $b[ $k ] ) ? esc_url_raw( trim( $b[ $k ] ) ) : '';
	};
	$img = function ( $k ) use ( $b ) {
		return isset( $b[ $k ] ) ? absint( $b[ $k ] ) : 0;
	};

	switch ( $type ) {
		case 'two_col':
			$out['heading']    = $text( 'heading' );
			$out['body']       = $rich( 'body' );
			$out['image_id']   = $img( 'image_id' );
			$out['btn_text']   = $text( 'btn_text' );
			$out['btn_url']    = $url( 'btn_url' );
			$out['image_side'] = ( isset( $b['image_side'] ) && 'right' === $b['image_side'] ) ? 'right' : 'left';
			break;

		case 'icon_grid':
			$out['heading'] = $text( 'heading' );
			$out['intro']   = $rich( 'intro' );
			$icons          = oss_sb_icon_choices();
			for ( $i = 0; $i < oss_sb_slots(); $i++ ) {
				$icon = isset( $b[ "card{$i}_icon" ] ) ? sanitize_key( $b[ "card{$i}_icon" ] ) : '';
				$out[ "card{$i}_icon" ]  = in_array( $icon, $icons, true ) ? $icon : 'compass';
				$out[ "card{$i}_label" ] = $text( "card{$i}_label" );
				$out[ "card{$i}_text" ]  = isset( $b[ "card{$i}_text" ] ) ? sanitize_textarea_field( $b[ "card{$i}_text" ] ) : '';
			}
			break;

		case 'text':
			$out['heading'] = $text( 'heading' );
			$out['body']    = $rich( 'body' );
			$out['align']   = ( isset( $b['align'] ) && 'center' === $b['align'] ) ? 'center' : 'left';
			break;

		case 'cta':
			$out['heading']  = $text( 'heading' );
			$out['body']     = $rich( 'body' );
			$out['btn_text'] = $text( 'btn_text' );
			$out['btn_url']  = $url( 'btn_url' );
			break;

		case 'quote':
			$out['quote']    = isset( $b['quote'] ) ? sanitize_textarea_field( $b['quote'] ) : '';
			$out['cite']     = $text( 'cite' );
			$out['image_id'] = $img( 'image_id' );
			break;

		case 'photo_row':
			$out['heading'] = $text( 'heading' );
			for ( $i = 0; $i < oss_sb_slots(); $i++ ) {
				$out[ "photo{$i}_id" ] = $img( "photo{$i}_id" );
			}
			break;
	}

	return $out;
}

/* ==================================================================== */
/* Front-end render                                                      */
/* ==================================================================== */

/**
 * Render all builder sections for a post. Called by the page template.
 */
function oss_sb_render( $post_id ) {
	$blocks = get_post_meta( $post_id, OSS_SB_META, true );
	if ( ! is_array( $blocks ) || ! $blocks ) {
		return;
	}
	foreach ( $blocks as $block ) {
		oss_sb_render_block( (array) $block );
	}
}

/**
 * Render the current page's builder sections. Safe to call from any page
 * template — renders nothing if the page has no sections. Lets clients append
 * ready-made sections to existing pages without changing their template.
 */
function oss_sb_render_current() {
	$id = get_queried_object_id();
	if ( $id ) {
		oss_sb_render( $id );
	}
}

function oss_sb_bg_class( $bg ) {
	switch ( $bg ) {
		case 'white':
			return 'oss-section--white';
		case 'sand':
			return 'oss-section--cream';
		case 'sage':
			return 'oss-section--sage';
		default:
			return 'oss-section--cream';
	}
}

function oss_sb_rich( $html ) {
	if ( function_exists( 'oss_rich' ) ) {
		return oss_rich( $html );
	}
	return wpautop( wp_kses_post( $html ) );
}

function oss_sb_button( $text, $url, $class = 'oss-btn oss-btn--primary' ) {
	$text = trim( (string) $text );
	$url  = trim( (string) $url );
	if ( '' === $text || '' === $url ) {
		return '';
	}
	return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>';
}

function oss_sb_render_block( $b ) {
	$type = isset( $b['type'] ) ? $b['type'] : '';
	if ( ! isset( oss_sb_types()[ $type ] ) ) {
		return;
	}
	$bg = isset( $b['bg'] ) ? $b['bg'] : '';

	echo '<section class="oss-section ' . esc_attr( oss_sb_bg_class( $bg ) ) . ' oss-sb oss-sb--' . esc_attr( $type ) . '"><div class="oss-container">';

	switch ( $type ) {
		case 'two_col':
			$side = ( isset( $b['image_side'] ) && 'right' === $b['image_side'] ) ? ' oss-sb-twocol--right' : '';
			echo '<div class="oss-sb-twocol' . $side . '">';
			echo '<div class="oss-sb-twocol__media">';
			if ( ! empty( $b['image_id'] ) && wp_get_attachment_image_src( (int) $b['image_id'], 'large' ) ) {
				echo wp_get_attachment_image( (int) $b['image_id'], 'large', false, array( 'alt' => esc_attr( $b['heading'] ) ) );
			}
			echo '</div><div class="oss-sb-twocol__text">';
			if ( ! empty( $b['heading'] ) ) {
				echo '<h2>' . esc_html( $b['heading'] ) . '</h2>';
			}
			echo oss_sb_rich( isset( $b['body'] ) ? $b['body'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo oss_sb_button( isset( $b['btn_text'] ) ? $b['btn_text'] : '', isset( $b['btn_url'] ) ? $b['btn_url'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo '</div></div>';
			break;

		case 'icon_grid':
			if ( ! empty( $b['heading'] ) || ! empty( $b['intro'] ) ) {
				echo '<div class="oss-section-heading oss-section-heading--center">';
				if ( ! empty( $b['heading'] ) ) {
					echo '<h2>' . esc_html( $b['heading'] ) . '</h2>';
				}
				echo oss_sb_rich( isset( $b['intro'] ) ? $b['intro'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo '</div>';
			}
			$icons = function_exists( 'oss_home_icon_library' ) ? oss_home_icon_library() : array();
			echo '<div class="oss-sb-grid">';
			for ( $i = 0; $i < oss_sb_slots(); $i++ ) {
				$label = isset( $b[ "card{$i}_label" ] ) ? $b[ "card{$i}_label" ] : '';
				if ( '' === trim( $label ) ) {
					continue;
				}
				$ikey = isset( $b[ "card{$i}_icon" ] ) ? $b[ "card{$i}_icon" ] : 'compass';
				$path = isset( $icons[ $ikey ] ) ? $icons[ $ikey ] : ( isset( $icons['compass'] ) ? $icons['compass'] : '' );
				echo '<div class="oss-sb-card">';
				echo '<span class="oss-sb-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg></span>'; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted static SVG library.
				echo '<h3 class="oss-sb-card__label">' . esc_html( $label ) . '</h3>';
				if ( ! empty( $b[ "card{$i}_text" ] ) ) {
					echo '<p class="oss-sb-card__text">' . esc_html( $b[ "card{$i}_text" ] ) . '</p>';
				}
				echo '</div>';
			}
			echo '</div>';
			break;

		case 'text':
			$align = ( isset( $b['align'] ) && 'center' === $b['align'] ) ? ' oss-sb-text--center' : '';
			echo '<div class="oss-sb-text' . $align . '">';
			if ( ! empty( $b['heading'] ) ) {
				echo '<h2>' . esc_html( $b['heading'] ) . '</h2>';
			}
			echo oss_sb_rich( isset( $b['body'] ) ? $b['body'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo '</div>';
			break;

		case 'cta':
			echo '<div class="oss-sb-cta">';
			if ( ! empty( $b['heading'] ) ) {
				echo '<h2>' . esc_html( $b['heading'] ) . '</h2>';
			}
			echo oss_sb_rich( isset( $b['body'] ) ? $b['body'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo oss_sb_button( isset( $b['btn_text'] ) ? $b['btn_text'] : '', isset( $b['btn_url'] ) ? $b['btn_url'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo '</div>';
			break;

		case 'quote':
			echo '<blockquote class="oss-sb-quote">';
			if ( ! empty( $b['quote'] ) ) {
				echo '<p class="oss-sb-quote__text">&ldquo;' . esc_html( $b['quote'] ) . '&rdquo;</p>';
			}
			echo '<footer class="oss-sb-quote__who">';
			if ( ! empty( $b['image_id'] ) && wp_get_attachment_image_src( (int) $b['image_id'], 'thumbnail' ) ) {
				echo wp_get_attachment_image( (int) $b['image_id'], 'thumbnail', false, array( 'class' => 'oss-sb-quote__avatar', 'alt' => esc_attr( $b['cite'] ) ) );
			}
			if ( ! empty( $b['cite'] ) ) {
				echo '<cite>&mdash; ' . esc_html( $b['cite'] ) . '</cite>';
			}
			echo '</footer></blockquote>';
			break;

		case 'photo_row':
			if ( ! empty( $b['heading'] ) ) {
				echo '<div class="oss-section-heading oss-section-heading--center"><h2>' . esc_html( $b['heading'] ) . '</h2></div>';
			}
			echo '<div class="oss-sb-photos">';
			for ( $i = 0; $i < oss_sb_slots(); $i++ ) {
				$pid = isset( $b[ "photo{$i}_id" ] ) ? (int) $b[ "photo{$i}_id" ] : 0;
				if ( $pid && wp_get_attachment_image_src( $pid, 'large' ) ) {
					echo '<div class="oss-sb-photos__item">' . wp_get_attachment_image( $pid, 'large', false, array( 'alt' => '' ) ) . '</div>';
				}
			}
			echo '</div>';
			break;
	}

	echo '</div></section>';
}

/* ==================================================================== */
/* Front-end assets                                                      */
/* ==================================================================== */

add_action( 'wp_enqueue_scripts', 'oss_sb_front_assets', 20 );
function oss_sb_front_assets() {
	wp_enqueue_style(
		'oss-section-builder',
		OSS_CHILD_URI . '/assets/css/section-builder.css',
		array(),
		OSS_CHILD_VERSION
	);
}

require_once OSS_CHILD_DIR . '/inc/section-builder-admin.php';
