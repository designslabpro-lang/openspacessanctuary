<?php
/**
 * About page — section render callback + engine registration.
 * Markup mirrors the original template-about-v2.php, made instance-aware so a
 * duplicated section renders its own content.
 */

defined( 'ABSPATH' ) || exit;

function oss_about_render_section( $key, $ctx = null ) {
	$g = 'about';
	switch ( $key ) {

		case 'hero':
			oss2_page_hero( array(
				'eyebrow'  => oss_sec_f( $g, $ctx, 'hero_eyebrow' ),
				'title'    => oss_sec_f( $g, $ctx, 'hero_heading' ),
				'intro'    => oss_sec_f( $g, $ctx, 'hero_body' ),
				'image_id' => oss2_image_id_or( oss_sec_f( $g, $ctx, 'hero_image_id' ), oss_home_get( 'power_image_id' ) ),
			) );
			break;

		case 'story':
			$story_img = oss2_image_id_or( oss_sec_f( $g, $ctx, 'story_image_id' ), oss2_first_hero_slide_id() );
			?>
			<section class="oss-section oss-section--cream">
				<div class="oss-container">
					<div class="oss2-feature">
						<div class="oss2-feature__media">
							<?php echo oss2_image( $story_img, oss_sec_f( $g, $ctx, 'story_heading' ) ); ?>
						</div>
						<div>
							<span class="oss-eyebrow"><?php echo esc_html( oss_sec_f( $g, $ctx, 'story_eyebrow' ) ); ?></span>
							<h2><?php echo esc_html( oss_sec_f( $g, $ctx, 'story_heading' ) ); ?></h2>
							<?php echo oss_rich( oss_sec_f( $g, $ctx, 'story_body' ) ); ?>
						</div>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'philosophy':
			$phil_img = oss2_image_id_or( oss_sec_f( $g, $ctx, 'philosophy_image_id' ), oss_home_get( 'power_image_id' ) );
			?>
			<section class="oss-section oss-section--white">
				<div class="oss-container">
					<div class="oss2-feature oss2-feature--reverse">
						<div class="oss2-feature__media">
							<?php echo oss2_image( $phil_img, oss_sec_f( $g, $ctx, 'philosophy_heading' ) ); ?>
							<blockquote class="oss2-feature__quote">&ldquo;<?php echo oss_rich_inline( oss_sec_f( $g, $ctx, 'philosophy_quote' ) ); ?>&rdquo;</blockquote>
						</div>
						<div>
							<span class="oss-eyebrow"><?php echo esc_html( oss_sec_f( $g, $ctx, 'philosophy_eyebrow' ) ); ?></span>
							<h2><?php echo esc_html( oss_sec_f( $g, $ctx, 'philosophy_heading' ) ); ?></h2>
							<?php echo oss_rich( oss_sec_f( $g, $ctx, 'philosophy_body' ) ); ?>
						</div>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'founder':
			?>
			<section class="oss-section oss-section--cream">
				<div class="oss-container">
					<div class="oss2-founder">
						<div class="oss2-founder__media">
							<?php echo oss_home_image( 'founder_image_id', 'large', esc_attr( oss_sec_f( $g, $ctx, 'founder_name' ) ) ); ?>
						</div>
						<div>
							<span class="oss2-founder__mark" aria-hidden="true">&ldquo;</span>
							<span class="oss-eyebrow"><?php echo esc_html( oss_sec_f( $g, $ctx, 'founder_heading' ) ); ?></span>
							<h2><?php echo esc_html( oss_sec_f( $g, $ctx, 'founder_name' ) ); ?></h2>
							<?php echo oss_rich( oss_sec_f( $g, $ctx, 'founder_body' ) ); ?>
						</div>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'final':
			?>
			<section class="oss-section oss2-close-section">
				<span class="oss2-close__circles oss2-close__circles--tl" aria-hidden="true"><span></span><span></span><span></span></span>
				<span class="oss2-close__circles oss2-close__circles--br" aria-hidden="true"><span></span><span></span><span></span></span>
				<div class="oss-container">
					<div class="oss2-close">
						<div class="oss2-close__media">
							<?php echo oss_home_image( 'final_image_id', 'large', esc_attr( oss_sec_f( $g, $ctx, 'final_heading' ) ) ); ?>
						</div>
						<div class="oss2-close__text">
							<h2><?php echo esc_html( oss_sec_f( $g, $ctx, 'final_heading' ) ); ?></h2>
							<div class="oss-divider"></div>
							<?php echo oss_rich( oss_sec_f( $g, $ctx, 'final_body' ) ); ?>
							<div class="oss2-close__actions">
								<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( oss_sec_f( $g, $ctx, 'final_btn_url' ) ); ?>"><?php echo esc_html( oss_sec_f( $g, $ctx, 'final_btn_text' ) ); ?></a>
							</div>
						</div>
					</div>
				</div>
			</section>
			<?php
			break;
	}
}

oss_sec_register( array(
	'group'     => 'about',
	'option'    => OSS_ABOUT_OPTION,
	'get'       => 'oss_about_get',
	'title'     => __( 'About', 'astra-child' ),
	'page_hook' => 'appearance_page_oss-about-content',
	'sections'  => array(
		'hero'       => __( 'Hero', 'astra-child' ),
		'story'      => __( 'Our Story', 'astra-child' ),
		'philosophy' => __( 'Our Philosophy', 'astra-child' ),
		'founder'    => __( 'Our Founder', 'astra-child' ),
		'final'      => __( 'Final CTA', 'astra-child' ),
	),
	'fields'    => array(
		'hero'       => array( 'hero_eyebrow', 'hero_heading', 'hero_body', 'hero_image_id' ),
		'story'      => array( 'story_eyebrow', 'story_heading', 'story_body', 'story_image_id' ),
		'philosophy' => array( 'philosophy_eyebrow', 'philosophy_heading', 'philosophy_body', 'philosophy_quote', 'philosophy_image_id' ),
		'founder'    => array( 'founder_heading', 'founder_name', 'founder_body' ),
		'final'      => array( 'final_heading', 'final_body', 'final_btn_text', 'final_btn_url' ),
	),
	'render'    => 'oss_about_render_section',
) );
