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
function oss2_page_hero( $args ) {
	$a = wp_parse_args( $args, array(
		'eyebrow'   => '',
		'title'     => '',
		'intro'     => '',
		'image_id'  => 0,
		'image_url' => '',
		'after'     => '',
	) );
	$url = $a['image_url'];
	if ( ! $url ) {
		$id  = oss2_image_id_or( $a['image_id'], oss2_first_hero_slide_id() );
		$url = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
	}
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
			<div class="oss2-page-hero__photo"<?php echo $url ? ' style="background-image:url(\'' . esc_url( $url ) . '\');"' : ''; ?>></div>
		</div>
	</header>
	<?php
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
					<?php echo do_shortcode( '[oss_newsletter_signup show_name="1" button="Join Our Community"]' ); ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}
