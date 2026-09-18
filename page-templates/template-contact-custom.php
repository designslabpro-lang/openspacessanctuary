<?php
/**
 * Template Name: Contact (Custom, No Builder)
 *
 * Plain-PHP Contact page — zero Elementor dependency. All copy/images come
 * from inc/contact-content.php (oss_contact_get), editable at
 * Appearance → Contact Page. Contact details and the form itself stay in
 * the existing [oss_contact_info] / [oss_contact_form] shortcodes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$oss_contact_hero_url = wp_get_attachment_image_url( (int) oss_contact_get( 'hero_image_id' ), 'full' );
?>

<header class="oss-hero oss-hero--page"<?php echo $oss_contact_hero_url ? ' style="background-image:url(\'' . esc_url( $oss_contact_hero_url ) . '\');"' : ''; ?>>
	<div class="oss-container oss-hero__inner">
		<span class="oss-eyebrow oss-hero__eyebrow"><?php echo esc_html( oss_contact_get( 'hero_eyebrow' ) ); ?></span>
		<div class="oss-hero__divider"></div>
		<h1><?php echo esc_html( oss_contact_get( 'hero_heading' ) ); ?></h1>
		<p><?php echo esc_html( oss_contact_get( 'hero_body' ) ); ?></p>
	</div>
</header>

<section class="oss-section oss-section--cream">
	<div class="oss-container oss-contact-grid">
		<div class="oss-contact-info">
			<h3><?php echo esc_html( oss_contact_get( 'info_heading' ) ); ?></h3>
			<?php echo do_shortcode( '[oss_contact_info]' ); ?>
		</div>
		<div class="oss-contact-form-panel">
			<h3><?php echo esc_html( oss_contact_get( 'form_heading' ) ); ?></h3>
			<?php echo do_shortcode( '[oss_contact_form]' ); ?>
		</div>
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

<?php get_footer(); ?>
