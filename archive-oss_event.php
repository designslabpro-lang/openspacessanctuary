<?php
/**
 * Events archive — scalable, admin adds events from Events → Add New.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$oss_hero_img = get_theme_mod( 'oss_events_hero_image', '' );
?>

<header class="oss-hero oss-hero--page"<?php echo $oss_hero_img ? ' style="background-image:url(\'' . esc_url( $oss_hero_img ) . '\');"' : ''; ?>>
	<div class="oss-container oss-hero__inner">
		<span class="oss-eyebrow oss-hero__eyebrow"><?php esc_html_e( 'Join Us', 'astra-child' ); ?></span>
		<h1><?php esc_html_e( 'Upcoming Events', 'astra-child' ); ?></h1>
		<p><?php echo esc_html( get_theme_mod( 'oss_events_intro', 'From open houses to community workshops, here is what is happening at the sanctuary.' ) ); ?></p>
	</div>
</header>

<section class="oss-section oss-section--cream">
	<div class="oss-container">
		<?php echo do_shortcode( '[oss_events_grid limit="-1"]' ); ?>
	</div>
</section>

<section class="oss-section oss-section--sage oss-cta">
	<div class="oss-container">
		<h2><?php esc_html_e( 'Ready to Begin?', 'astra-child' ); ?></h2>
		<p><?php esc_html_e( 'Explore how Open Spaces Sanctuary can help create meaningful connection and healing.', 'astra-child' ); ?></p>
		<div class="oss-cta__actions">
			<a class="oss-btn oss-btn--on-sage" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Get In Touch', 'astra-child' ); ?></a>
		</div>
	</div>
</section>

<?php get_footer(); ?>
