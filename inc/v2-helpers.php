<?php
/**
 * V2 building blocks shared by the inner-page templates and the
 * Programs / Events archives: the split page hero and the site-wide
 * "Stay Connected" panel. Copy in the panel is verbatim from the
 * client's content document (section 8, "should appear on every page").
 */

defined( 'ABSPATH' ) || exit;

function oss2_first_hero_slide_id() {
	$ids = array_values( array_filter( array_map( 'intval', (array) oss_home_get( 'hero_slide_ids' ) ) ) );
	return $ids ? $ids[0] : (int) oss_home_get( 'hero_image_id' );
}

/**
 * Use $id if it points at a real attachment, otherwise $fallback.
 */
function oss2_image_id_or( $id, $fallback ) {
	$id = (int) $id;
	return ( $id && wp_get_attachment_image_src( $id, 'large' ) ) ? $id : (int) $fallback;
}

function oss2_image( $id, $alt = '' ) {
	$id = (int) $id;
	if ( $id && wp_get_attachment_image_src( $id, 'large' ) ) {
		return wp_get_attachment_image( $id, 'large', false, array( 'alt' => $alt ) );
	}
	return '<div class="oss-image-placeholder" aria-hidden="true"></div>';
}

/**
 * Split page hero: dark panel (eyebrow, h1, intro paragraphs, optional
 * extra markup) on the left, photo on the right.
 */
/**
 * Allowed background-image focal positions (CSS value => label).
 */
function oss_bg_positions() {
	return array(
		'center center' => __( 'Center', 'astra-child' ),
		'top center'    => __( 'Top', 'astra-child' ),
		'bottom center' => __( 'Bottom', 'astra-child' ),
		'center left'   => __( 'Left', 'astra-child' ),
		'center right'  => __( 'Right', 'astra-child' ),
		'top left'      => __( 'Top Left', 'astra-child' ),
		'top right'     => __( 'Top Right', 'astra-child' ),
		'bottom left'   => __( 'Bottom Left', 'astra-child' ),
		'bottom right'  => __( 'Bottom Right', 'astra-child' ),
	);
}

/**
 * Allowed background-image fit modes.
 */
function oss_bg_fits() {
	return array(
		'cover'   => __( 'Fill (cover)', 'astra-child' ),
		'contain' => __( 'Fit (contain)', 'astra-child' ),
	);
}

/**
 * A safe inline style attribute for a background image with an optional focal
 * position + fit. Returns '' when there is no url. Position and fit are
 * whitelisted, so the output is safe to echo.
 */
function oss_bg_image_style( $url, $position = '', $fit = '' ) {
	if ( ! $url ) {
		return '';
	}
	$positions = oss_bg_positions();
	$fits      = oss_bg_fits();
	$pos = ( $position && isset( $positions[ $position ] ) ) ? $position : 'center center';
	$fit = ( $fit && isset( $fits[ $fit ] ) ) ? $fit : 'cover';
	return ' style="background-image:url(\'' . esc_url( $url ) . '\');background-position:' . esc_attr( $pos ) . ';background-size:' . esc_attr( $fit ) . ';"';
}

function oss2_page_hero( $args ) {
	$a = wp_parse_args( $args, array(
		'eyebrow'        => '',
		'title'          => '',
		'intro'          => '',
		'image_id'       => 0,
		'image_url'      => '',
		'image_position' => '',
		'image_fit'      => '',
		'after'          => '',
	) );
	$url = $a['image_url'];
	if ( ! $url ) {
		$id  = oss2_image_id_or( $a['image_id'], oss2_first_hero_slide_id() );
		$url = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
	}
	$photo_style = oss_bg_image_style( $url, $a['image_position'], $a['image_fit'] );
	$intro = is_array( $a['intro'] ) ? $a['intro'] : explode( "\n", wp_strip_all_tags( (string) $a['intro'] ) );
	?>
	<header class="oss2-page-hero">
		<div class="oss-container oss2-page-hero__grid">
			<div class="oss2-page-hero__panel">
				<div class="oss2-page-hero__inner">
					<?php if ( $a['eyebrow'] ) : ?><span class="oss-eyebrow oss2-hero__eyebrow"><?php echo esc_html( $a['eyebrow'] ); ?></span><?php endif; ?>
					<h1><?php echo esc_html( $a['title'] ); ?></h1>
					<?php foreach ( $intro as $para ) : ?>
						<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
					<?php endforeach; ?>
					<?php echo $a['after']; // phpcs:ignore -- caller-escaped markup. ?>
				</div>
			</div>
			<div class="oss2-page-hero__photo"<?php echo $photo_style; // phpcs:ignore -- built by oss_bg_image_style(), values escaped there. ?>></div>
		</div>
	</header>
	<?php
}

/**
 * The Stay Connected signup form. Renders Gravity Forms form 2 ("Stay
 * Connected": Name, Email) when it exists, and nothing otherwise — so the
 * panel never shows a "form not found" message if the form hasn't been
 * imported on a given environment yet.
 */
function oss_connect_form_html() {
	if ( class_exists( 'GFAPI' ) && GFAPI::get_form( 2 ) ) {
		return do_shortcode( '[gravityform id="2" title="false" description="false" ajax="true"]' );
	}
	return '';
}

function oss2_connect_panel() {
	?>
	<section class="oss-section oss-section--cream">
		<div class="oss-container">
			<div class="oss2-connect">
				<div class="oss2-connect__text">
					<span class="oss-eyebrow"><?php esc_html_e( 'Newsletter', 'astra-child' ); ?></span>
					<h2><?php esc_html_e( 'Stay Connected', 'astra-child' ); ?></h2>
					<p><?php esc_html_e( 'Receive stories, upcoming events, healing resources and opportunities to make a difference.', 'astra-child' ); ?></p>
				</div>
				<div class="oss2-connect__form">
					<?php echo oss_connect_form_html(); ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}
