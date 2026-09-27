<?php
/**
 * Template Name: Section Builder
 *
 * Renders the page's native editor content (if any) followed by the flexible
 * sections built with the "Page Sections" meta box (see inc/section-builder.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$oss_sb_content = trim( wp_strip_all_tags( get_the_content() ) );
	if ( '' !== $oss_sb_content ) :
		?>
		<section class="oss-section oss-section--cream">
			<div class="oss-container oss-content-narrow">
				<?php the_content(); ?>
			</div>
		</section>
		<?php
	endif;

	if ( function_exists( 'oss_sb_render' ) ) {
		oss_sb_render( get_the_ID() );
	}

endwhile;

get_footer();
