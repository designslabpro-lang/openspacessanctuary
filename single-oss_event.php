<?php
/**
 * Single Event.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$date = get_post_meta( get_the_ID(), 'oss_event_date', true );
	$time = get_post_meta( get_the_ID(), 'oss_event_time', true );
	$loc  = get_post_meta( get_the_ID(), 'oss_event_location', true );
	$reg  = get_post_meta( get_the_ID(), 'oss_event_registration', true );
	?>

	<header class="oss-hero oss-hero--page"<?php echo has_post_thumbnail() ? ' style="background-image:url(\'' . esc_url( get_the_post_thumbnail_url( get_the_ID(), 'full' ) ) . '\');"' : ''; ?>>
		<div class="oss-container oss-hero__inner">
			<span class="oss-eyebrow oss-hero__eyebrow"><?php esc_html_e( 'Event', 'astra-child' ); ?></span>
			<h1><?php the_title(); ?></h1>
		</div>
	</header>

	<section class="oss-section oss-section--cream">
		<div class="oss-container" style="max-width:820px;">
			<ul class="oss-event-card__details" style="font-size:1rem;margin-bottom:2rem;">
				<?php if ( $date ) : ?><li><strong><?php esc_html_e( 'Date:', 'astra-child' ); ?></strong> <?php echo esc_html( gmdate( 'F j, Y', strtotime( $date ) ) ); ?></li><?php endif; ?>
				<?php if ( $time ) : ?><li><strong><?php esc_html_e( 'Time:', 'astra-child' ); ?></strong> <?php echo esc_html( $time ); ?></li><?php endif; ?>
				<?php if ( $loc ) : ?><li><strong><?php esc_html_e( 'Location:', 'astra-child' ); ?></strong> <?php echo esc_html( $loc ); ?></li><?php endif; ?>
			</ul>
			<div class="oss-prose"><?php the_content(); ?></div>
			<?php if ( $reg ) : ?>
				<p style="margin-top:2rem;"><a class="oss-btn oss-btn--primary" href="<?php echo esc_url( $reg ); ?>"><?php esc_html_e( 'Register Now', 'astra-child' ); ?></a></p>
			<?php endif; ?>
		</div>
	</section>

	<?php
endwhile;

get_footer();
