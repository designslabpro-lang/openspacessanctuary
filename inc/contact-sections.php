<?php
/**
 * Contact — section render callback + engine registration.
 */

defined( 'ABSPATH' ) || exit;

function oss_contact_render_section( $key, $ctx = null ) {
	$g = 'contact';
	switch ( $key ) {

		case 'hero':
			oss2_page_hero( array(
				'eyebrow'  => oss_sec_f( $g, $ctx, 'hero_eyebrow' ),
				'title'    => oss_sec_f( $g, $ctx, 'hero_heading' ),
				'intro'    => oss_sec_f( $g, $ctx, 'hero_body' ),
				'image_id' => oss_sec_f( $g, $ctx, 'hero_image_id' ),
			) );
			break;

		case 'info':
			?>
			<section class="oss-section oss-section--cream" id="contact">
				<div class="oss-container">
					<div class="oss2-contact">
						<div class="oss2-contact__card">
							<h3><?php echo esc_html( oss_sec_f( $g, $ctx, 'info_heading' ) ); ?></h3>
							<?php echo do_shortcode( '[oss_contact_info]' ); ?>
						</div>
						<div class="oss2-contact__card">
							<h3><?php echo esc_html( oss_sec_f( $g, $ctx, 'form_heading' ) ); ?></h3>
							<?php
							$oss_form_sc  = trim( (string) oss_sec_f( $g, $ctx, 'form_shortcode' ) );
							$oss_form_tag = preg_match( '/^\[(\w+)/', $oss_form_sc, $m ) ? $m[1] : '';
							if ( $oss_form_tag && shortcode_exists( $oss_form_tag ) ) {
								echo do_shortcode( $oss_form_sc );
							} else {
								echo do_shortcode( '[oss_contact_form]' );
							}
							?>
						</div>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'faq':
			$oss2_faq = (array) oss_sec_f( $g, $ctx, 'faq' );
			?>
			<section class="oss-section oss-section--white oss2-faq" style="text-align:center;">
				<div class="oss-container">
					<div class="oss-section-heading oss-section-heading--center">
						<span class="oss-eyebrow"><?php esc_html_e( 'FAQ', 'astra-child' ); ?></span>
						<h2><?php esc_html_e( 'Frequently Asked Questions', 'astra-child' ); ?></h2>
					</div>
					<div class="oss2-faq__list" data-oss-accordion>
						<?php foreach ( $oss2_faq as $item ) : if ( empty( $item['q'] ) ) { continue; } ?>
							<details class="oss2-faq__item">
								<summary class="oss2-faq__q"><span><?php echo esc_html( $item['q'] ); ?></span><span class="oss2-faq__icon" aria-hidden="true"></span></summary>
								<div class="oss2-faq__a">
									<?php if ( ! empty( $item['a'] ) ) : ?>
										<?php foreach ( explode( "\n", $item['a'] ) as $para ) : ?>
											<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
										<?php endforeach; ?>
									<?php else : ?>
										<p><a class="oss2-faq__ask" href="#contact"><?php esc_html_e( 'Request Information', 'astra-child' ); ?> &rarr;</a></p>
									<?php endif; ?>
								</div>
							</details>
						<?php endforeach; ?>
					</div>
					<p style="margin:2.25rem 0 0;"><a class="oss-btn oss-btn--primary" href="#contact"><?php esc_html_e( 'Request Information', 'astra-child' ); ?></a></p>
				</div>
			</section>
			<?php
			break;

		case 'cta':
			?>
			<section class="oss-section oss-section--sage oss-cta" style="text-align:center;">
				<div class="oss-container oss-on-dark">
					<h2><?php echo esc_html( oss_sec_f( $g, $ctx, 'cta_heading' ) ); ?></h2>
					<?php echo oss_rich( oss_sec_f( $g, $ctx, 'cta_body' ) ); ?>
					<div class="oss-cta__actions">
						<a class="oss-btn oss-btn--on-sage" href="<?php echo esc_url( oss_sec_f( $g, $ctx, 'cta_btn_url' ) ); ?>"><?php echo esc_html( oss_sec_f( $g, $ctx, 'cta_btn_text' ) ); ?></a>
					</div>
				</div>
			</section>
			<?php
			break;
	}
}

oss_sec_register( array(
	'group'     => 'contact',
	'option'    => OSS_CONTACT_OPTION,
	'get'       => 'oss_contact_get',
	'title'     => __( 'Contact', 'astra-child' ),
	'page_hook' => 'appearance_page_oss-contact-content',
	'sections'  => array(
		'hero' => __( 'Hero', 'astra-child' ),
		'info' => __( 'Info & Form', 'astra-child' ),
		'faq'  => __( 'FAQ', 'astra-child' ),
		'cta'  => __( 'Final CTA', 'astra-child' ),
	),
	'fields'    => array(
		'hero' => array( 'hero_eyebrow', 'hero_heading', 'hero_body', 'hero_image_id' ),
		'info' => array( 'info_heading', 'form_heading', 'form_shortcode' ),
		'faq'  => array( 'faq' ),
		'cta'  => array( 'cta_heading', 'cta_body', 'cta_btn_text', 'cta_btn_url' ),
	),
	'render'    => 'oss_contact_render_section',
) );
