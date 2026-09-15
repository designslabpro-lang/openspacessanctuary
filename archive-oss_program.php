<?php
/**
 * Programs archive — scalable, admin adds programs from Programs → Add New.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php $oss_hero_img = get_theme_mod( 'oss_programs_hero_image', '' ); ?>
<header class="oss-hero oss-hero--page"<?php echo $oss_hero_img ? ' style="background-image:url(\'' . esc_url( $oss_hero_img ) . '\');"' : ''; ?>>
	<div class="oss-container oss-hero__inner">
		<span class="oss-eyebrow oss-hero__eyebrow"><?php esc_html_e( 'What We Offer', 'astra-child' ); ?></span>
		<h1><?php esc_html_e( 'Our Programs', 'astra-child' ); ?></h1>
		<p><?php echo esc_html( get_theme_mod( 'oss_programs_intro', 'Equine-assisted programs designed for connection, confidence, and healing — for every stage of the journey.' ) ); ?></p>
	</div>
</header>

<section class="oss-section oss-section--cream">
	<div class="oss-container">
		<?php echo do_shortcode( '[oss_programs_grid limit="-1"]' ); ?>
	</div>
</section>

<section class="oss-section oss-section--sage oss-cta">
	<div class="oss-container">
		<h2><?php esc_html_e( 'Not sure which program is right for you?', 'astra-child' ); ?></h2>
		<p><?php esc_html_e( 'Reach out and our team will help you find the best fit.', 'astra-child' ); ?></p>
		<div class="oss-cta__actions">
			<a class="oss-btn oss-btn--on-sage" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Get In Touch', 'astra-child' ); ?></a>
		</div>
	</div>
</section>

<?php get_footer(); ?>
