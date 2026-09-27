<?php
/**
 * Meet the Herd — section render callback + engine registration.
 */

defined( 'ABSPATH' ) || exit;

function oss_herd_render_section( $key, $ctx = null ) {
	$g = 'herd';
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

		case 'herd':
			?>
			<section class="oss-section oss-section--cream">
				<div class="oss-container">
					<div class="oss2-feature oss2-feature--reverse">
						<div class="oss2-feature__media">
							<?php echo oss_sec_image( $g, $ctx, 'herd_image_id', 'large', esc_attr__( 'A horse at Open Spaces Sanctuary', 'astra-child' ) ); ?>
						</div>
						<div>
							<span class="oss-eyebrow"><?php echo esc_html( oss_sec_f( $g, $ctx, 'herd_eyebrow' ) ); ?></span>
							<h2><?php echo esc_html( oss_sec_f( $g, $ctx, 'herd_heading' ) ); ?></h2>
							<?php echo oss_rich( oss_sec_f( $g, $ctx, 'herd_body' ) ); ?>
							<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( oss_sec_f( $g, $ctx, 'herd_btn_url' ) ); ?>"><?php echo esc_html( oss_sec_f( $g, $ctx, 'herd_btn_text' ) ); ?></a>
						</div>
					</div>
				</div>
			</section>
			<?php
			break;
	}
}

oss_sec_register( array(
	'group'     => 'herd',
	'option'    => OSS_HERD_OPTION,
	'get'       => 'oss_herd_get',
	'title'     => __( 'Meet the Herd', 'astra-child' ),
	'page_hook' => 'appearance_page_oss-herd-content',
	'sections'  => array(
		'hero' => __( 'Hero', 'astra-child' ),
		'herd' => __( 'The Herd', 'astra-child' ),
	),
	'fields'    => array(
		'hero' => array( 'hero_eyebrow', 'hero_heading', 'hero_body', 'hero_image_id', 'hero_image_pos', 'hero_image_fit' ),
		'herd' => array( 'herd_eyebrow', 'herd_heading', 'herd_body', 'herd_image_id', 'herd_btn_text', 'herd_btn_url' ),
	),
	'render'    => 'oss_herd_render_section',
) );
