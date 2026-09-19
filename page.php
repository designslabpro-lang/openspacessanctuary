<?php
/**
 * Default page template. Pages on the "Default" template (Privacy Policy,
 * Terms, and any simple page) get the site's hero + readable prose layout
 * instead of Astra's bare default, using only globally-loaded base styles.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<header class="oss-hero oss-hero--page oss-hero--slim"<?php echo has_post_thumbnail() ? ' style="background-image:url(\'' . esc_url( get_the_post_thumbnail_url( get_the_ID(), 'full' ) ) . '\');"' : ''; ?>>
		<div class="oss-container oss-hero__inner">
			<h1><?php the_title(); ?></h1>
		</div>
	</header>

	<section class="oss-section oss-section--white">
		<div class="oss-container oss-content-narrow">
			<div class="oss-prose"><?php the_content(); ?></div>
			<?php wp_link_pages( array( 'before' => '<p class="oss-page-links">' . esc_html__( 'Pages:', 'astra-child' ) . ' ', 'after' => '</p>' ) ); ?>
		</div>
	</section>

	<?php
endwhile;

get_footer();
