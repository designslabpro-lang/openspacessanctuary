<?php
/**
 * Single Program.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$audience = get_post_meta( get_the_ID(), 'oss_program_audience', true );
	?>

	<header class="oss-hero oss-hero--page"<?php echo has_post_thumbnail() ? ' style="background-image:url(\'' . esc_url( get_the_post_thumbnail_url( get_the_ID(), 'full' ) ) . '\');"' : ''; ?>>
		<div class="oss-container oss-hero__inner">
			<?php if ( $audience ) : ?><span class="oss-eyebrow oss-hero__eyebrow"><?php echo esc_html( $audience ); ?></span><div class="oss-hero__divider"></div><?php endif; ?>
			<h1><?php the_title(); ?></h1>
		</div>
	</header>

	<section class="oss-section oss-section--cream">
		<div class="oss-container" style="max-width:820px;">
			<div class="oss-prose"><?php the_content(); ?></div>
		</div>
	</section>

	<section class="oss-section oss-section--sage oss-cta">
		<div class="oss-container">
			<h2><?php esc_html_e( 'Interested in this program?', 'astra-child' ); ?></h2>
			<div class="oss-cta__actions">
				<a class="oss-btn oss-btn--on-sage" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Get In Touch', 'astra-child' ); ?></a>
				<a class="oss-btn oss-btn--secondary" style="border-color:var(--oss-bg);color:var(--oss-bg);" href="<?php echo esc_url( get_post_type_archive_link( 'oss_program' ) ); ?>"><?php esc_html_e( 'All Programs', 'astra-child' ); ?></a>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
