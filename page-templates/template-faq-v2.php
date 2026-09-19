<?php
/**
 * Template Name: FAQ (V2)
 *
 * A dedicated FAQ page. Banner + accordion of questions, all editable at
 * Appearance → FAQ Page (inc/faq-content.php). Reuses the shared FAQ accordion
 * markup, CSS (.oss2-faq__*), and the one-open-at-a-time accordion JS.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$oss_faqs   = (array) oss_faq_get( 'faqs' );
$contact_url = home_url( '/contact/' );

oss2_page_hero( array(
	'eyebrow'  => oss_faq_get( 'hero_eyebrow' ),
	'title'    => oss_faq_get( 'hero_heading' ),
	'intro'    => oss_faq_get( 'hero_body' ),
	'image_id' => (int) oss_faq_get( 'hero_image_id' ),
) );
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
oss2_connect_panel();
get_footer();
