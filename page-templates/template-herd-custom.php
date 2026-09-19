<?php
/**
 * Template Name: Meet the Herd (Custom, No Builder)
 *
 * Plain-PHP shell — zero Elementor dependency. The page's own title and
 * intro paragraph come from the native WordPress editor (Pages → Meet the
 * Herd), same as any standard page. The herd section below reuses the
 * already-approved "Meet Our Horses" copy from the homepage content store
 * (inc/homepage-content.php) rather than inventing new horse bios.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<header class="oss-hero oss-hero--page" style="background-color:var(--oss-primary);">
		<div class="oss-container oss-hero__inner">
			<span class="oss-eyebrow oss-hero__eyebrow"><?php esc_html_e( 'Our Herd', 'astra-child' ); ?></span>
			<div class="oss-hero__divider"></div>
			<h1><?php the_title(); ?></h1>
			<div class="oss-prose"><?php the_content(); ?></div>
		</div>
	</header>

	<section class="oss-section oss-section--cream">
		<div class="oss-container">
			<div class="oss-split">
				<div class="oss-split__media">
					<div class="oss-horses-media">
						<div class="oss-horses-media__frame"></div>
						<div class="oss-horses-media__photo">
							<?php echo oss_home_image( 'horses_image_id', 'large', esc_attr__( 'A horse at Open Spaces Sanctuary', 'astra-child' ) ); ?>
						</div>
					</div>
				</div>
				<div class="oss-split__content">
					<h2><?php echo esc_html( oss_home_get( 'horses_heading' ) ); ?></h2>
					<?php foreach ( explode( "\n", oss_home_get( 'horses_body' ) ) as $para ) : ?>
						<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>

	<section class="oss-section oss-section--sage" style="text-align:center;">
		<div class="oss-container">
			<h2><?php echo esc_html( oss_home_get( 'connect_heading' ) ); ?></h2>
			<p style="max-width:560px;margin:0 auto 1.5rem;"><?php echo esc_html( oss_home_get( 'connect_body' ) ); ?></p>
			<?php echo oss_connect_form_html(); ?>
		</div>
	</section>

	<?php
endwhile;

get_footer();
