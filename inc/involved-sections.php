<?php
/**
 * Get Involved — section render callback + engine registration.
 */

defined( 'ABSPATH' ) || exit;

function oss_involved_render_section( $key, $ctx = null ) {
	$g = 'involved';
	switch ( $key ) {

		case 'hero':
			oss2_page_hero( array(
				'eyebrow'        => oss_sec_f( $g, $ctx, 'hero_eyebrow' ),
				'title'          => oss_sec_f( $g, $ctx, 'hero_heading' ),
				'intro'          => oss_sec_f( $g, $ctx, 'hero_body' ),
				'image_id'       => (int) oss_sec_f( $g, $ctx, 'hero_image_id' ),
				'image_position' => oss_sec_f( $g, $ctx, 'hero_image_pos' ),
				'image_fit'      => oss_sec_f( $g, $ctx, 'hero_image_fit' ),
			) );
			break;

		case 'ways':
			$icons = oss_home_icon_library();
			$ways  = (array) oss_sec_f( $g, $ctx, 'ways' );
			?>
			<section class="oss-section oss2-serve-section" style="text-align:center;">
				<div class="oss-container">
					<div class="oss-section-heading oss-section-heading--center">
						<span class="oss-eyebrow"><?php echo esc_html( oss_sec_f( $g, $ctx, 'ways_eyebrow' ) ); ?></span>
						<h2><?php echo esc_html( oss_sec_f( $g, $ctx, 'ways_heading' ) ); ?></h2>
					</div>
					<div class="oss2-serve-grid">
						<?php foreach ( $ways as $i => $way ) : ?>
							<a class="oss2-serve-card oss2-serve-card--c<?php echo esc_attr( ( $i % 5 ) + 1 ); ?>" href="<?php echo esc_url( isset( $way['url'] ) ? $way['url'] : home_url( '/contact/' ) ); ?>">
								<span class="oss2-serve-card__icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><?php echo isset( $icons[ $way['icon'] ] ) ? $icons[ $way['icon'] ] : $icons['compass']; // phpcs:ignore -- trusted static SVG path library. ?></svg>
								</span>
								<span class="oss2-serve-card__label"><?php echo esc_html( $way['label'] ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'give':
			?>
			<section class="oss-section oss-section--white oss2-give">
				<span class="oss2-give__circles oss2-give__circles--tl" aria-hidden="true"><span></span><span></span><span></span></span>
				<span class="oss2-give__circles oss2-give__circles--br" aria-hidden="true"><span></span><span></span><span></span></span>
				<div class="oss-container oss2-give__grid">
					<div class="oss2-give__media">
						<?php echo oss_sec_image( $g, $ctx, 'give_image_id', 'large', esc_attr( oss_sec_f( $g, $ctx, 'give_heading' ) ) ); ?>
					</div>
					<div class="oss2-give__text">
						<span class="oss-eyebrow"><?php echo esc_html( oss_sec_f( $g, $ctx, 'give_eyebrow' ) ); ?></span>
						<h2><?php echo esc_html( oss_sec_f( $g, $ctx, 'give_heading' ) ); ?></h2>
						<?php echo oss_rich( oss_sec_f( $g, $ctx, 'give_body' ) ); ?>
						<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( oss_sec_f( $g, $ctx, 'give_btn_url' ) ); ?>"><?php echo esc_html( oss_sec_f( $g, $ctx, 'give_btn_text' ) ); ?></a>
					</div>
				</div>
			</section>
			<?php
			break;
	}
}

oss_sec_register( array(
	'group'     => 'involved',
	'option'    => OSS_INVOLVED_OPTION,
	'get'       => 'oss_involved_get',
	'title'     => __( 'Get Involved', 'astra-child' ),
	'page_hook' => 'appearance_page_oss-involved-content',
	'sections'  => array(
		'hero' => __( 'Hero', 'astra-child' ),
		'ways' => __( 'Ways to Get Involved', 'astra-child' ),
		'give' => __( 'Support the Sanctuary', 'astra-child' ),
	),
	'fields'    => array(
		'hero' => array( 'hero_eyebrow', 'hero_heading', 'hero_body', 'hero_image_id', 'hero_image_pos', 'hero_image_fit' ),
		'ways' => array( 'ways_eyebrow', 'ways_heading', 'ways' ),
		'give' => array( 'give_eyebrow', 'give_heading', 'give_body', 'give_image_id', 'give_btn_text', 'give_btn_url' ),
	),
	'render'    => 'oss_involved_render_section',
) );
