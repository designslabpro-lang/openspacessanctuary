<?php
/**
 * FAQ — section render callback + engine registration.
 */

defined( 'ABSPATH' ) || exit;

function oss_faq_render_section( $key, $ctx = null ) {
	$g = 'faq';
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

		case 'faqs':
			$oss_faqs    = (array) oss_sec_f( $g, $ctx, 'faqs' );
			$contact_url = home_url( '/contact/' );
			?>
			<section class="oss-section oss-section--cream oss2-faq" style="text-align:center;">
				<div class="oss-container">
					<div class="oss2-faq__list" data-oss-accordion>
						<?php foreach ( $oss_faqs as $item ) : if ( empty( $item['q'] ) ) { continue; } ?>
							<details class="oss2-faq__item">
								<summary class="oss2-faq__q"><span><?php echo esc_html( $item['q'] ); ?></span><span class="oss2-faq__icon" aria-hidden="true"></span></summary>
								<div class="oss2-faq__a">
									<?php if ( ! empty( $item['a'] ) ) : ?>
										<?php foreach ( explode( "\n", $item['a'] ) as $para ) : ?>
											<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
										<?php endforeach; ?>
									<?php else : ?>
										<p><a class="oss2-faq__ask" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Request Information', 'astra-child' ); ?> &rarr;</a></p>
									<?php endif; ?>
								</div>
							</details>
						<?php endforeach; ?>
					</div>
					<p style="margin:2.5rem 0 0;"><a class="oss-btn oss-btn--primary" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Still have questions? Contact us', 'astra-child' ); ?></a></p>
				</div>
			</section>
			<?php
			break;
	}
}

oss_sec_register( array(
	'group'     => 'faq',
	'option'    => OSS_FAQ_OPTION,
	'get'       => 'oss_faq_get',
	'title'     => __( 'FAQ', 'astra-child' ),
	'page_hook' => 'appearance_page_oss-faq-content',
	'sections'  => array(
		'hero' => __( 'Hero', 'astra-child' ),
		'faqs' => __( 'Questions & Answers', 'astra-child' ),
	),
	'fields'    => array(
		'hero' => array( 'hero_eyebrow', 'hero_heading', 'hero_body', 'hero_image_id', 'hero_image_pos', 'hero_image_fit' ),
		'faqs' => array( 'faqs' ),
	),
	'render'    => 'oss_faq_render_section',
) );
