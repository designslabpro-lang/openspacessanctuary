<?php
/**
 * Template Name: Contact (V2)
 *
 * Contact page in the V2 design language. Copy from inc/contact-content.php
 * (Appearance → Contact Page); contact details and the form come from the
 * [oss_contact_info] / [oss_contact_form] shortcodes. The FAQ lists the
 * questions from the client's content document (answers not yet supplied).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$oss2_faq = array(
	__( 'What is Equine Assisted Learning?', 'astra-child' ),
	__( 'Do I ride the horses?', 'astra-child' ),
	__( 'Is this therapy?', 'astra-child' ),
	__( 'Who can participate?', 'astra-child' ),
	__( 'What should I wear?', 'astra-child' ),
	__( 'Can children attend?', 'astra-child' ),
	__( 'How much does it cost?', 'astra-child' ),
);

oss2_page_hero( array(
	'eyebrow'  => oss_contact_get( 'hero_eyebrow' ),
	'title'    => oss_contact_get( 'hero_heading' ),
	'intro'    => oss_contact_get( 'hero_body' ),
	'image_id' => oss_contact_get( 'hero_image_id' ),
) );
?>

<section class="oss-section oss-section--cream" id="contact">
	<div class="oss-container">
		<div class="oss2-contact">
			<div class="oss2-contact__card">
				<h3><?php echo esc_html( oss_contact_get( 'info_heading' ) ); ?></h3>
				<?php echo do_shortcode( '[oss_contact_info]' ); ?>
			</div>
			<div class="oss2-contact__card">
				<h3><?php echo esc_html( oss_contact_get( 'form_heading' ) ); ?></h3>
				<?php echo do_shortcode( '[oss_contact_form]' ); ?>
			</div>
		</div>
	</div>
</section>

<section class="oss-section oss-section--white oss2-faq" style="text-align:center;">
	<div class="oss-container">
		<div class="oss-section-heading oss-section-heading--center">
			<span class="oss-eyebrow"><?php esc_html_e( 'FAQ', 'astra-child' ); ?></span>
			<h2><?php esc_html_e( 'Frequently Asked Questions', 'astra-child' ); ?></h2>
		</div>
		<div class="oss2-faq__grid">
			<?php foreach ( $oss2_faq as $q ) : ?>
				<div class="oss2-faq__item"><?php echo esc_html( $q ); ?></div>
			<?php endforeach; ?>
		</div>
		<p style="margin:2.25rem 0 0;"><a class="oss-btn oss-btn--primary" href="#contact"><?php esc_html_e( 'Request Information', 'astra-child' ); ?></a></p>
	</div>
</section>

<section class="oss-section oss-section--sage oss-cta" style="text-align:center;">
	<div class="oss-container oss-on-dark">
		<h2><?php echo esc_html( oss_contact_get( 'cta_heading' ) ); ?></h2>
		<p><?php echo esc_html( oss_contact_get( 'cta_body' ) ); ?></p>
		<div class="oss-cta__actions">
			<a class="oss-btn oss-btn--on-sage" href="<?php echo esc_url( oss_contact_get( 'cta_btn_url' ) ); ?>"><?php echo esc_html( oss_contact_get( 'cta_btn_text' ) ); ?></a>
		</div>
	</div>
</section>

<?php
oss2_connect_panel();
get_footer();
