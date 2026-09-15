<?php
/**
 * Single blog post — premium editorial layout.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$cats = get_the_category();
	?>

	<header class="oss-hero oss-hero--page"<?php echo has_post_thumbnail() ? ' style="background-image:url(\'' . esc_url( get_the_post_thumbnail_url( get_the_ID(), 'full' ) ) . '\');"' : ''; ?>>
		<div class="oss-container oss-hero__inner">
			<?php if ( $cats ) : ?><span class="oss-eyebrow oss-hero__eyebrow"><?php echo esc_html( $cats[0]->name ); ?></span><?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<p style="font-size:0.9rem;">
				<?php echo esc_html( get_the_date() ); ?>
				<?php if ( get_the_author() ) : ?> &middot; <?php esc_html_e( 'By', 'astra-child' ); ?> <?php the_author(); ?><?php endif; ?>
			</p>
		</div>
	</header>

	<section class="oss-section oss-section--cream">
		<div class="oss-container" style="max-width:760px;">
			<div class="oss-prose"><?php the_content(); ?></div>

			<?php oss_gold_divider(); ?>

			<div class="oss-share" style="margin-bottom:2rem;">
				<span class="oss-eyebrow" style="margin-right:0.75rem;"><?php esc_html_e( 'Share:', 'astra-child' ); ?></span>
				<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Facebook', 'astra-child' ); ?></a>
				&nbsp;&middot;&nbsp;
				<a href="mailto:?subject=<?php echo rawurlencode( get_the_title() ); ?>&body=<?php echo rawurlencode( get_permalink() ); ?>"><?php esc_html_e( 'Email', 'astra-child' ); ?></a>
			</div>

			<?php if ( comments_open() || get_comments_number() ) : ?>
				<?php comments_template(); ?>
			<?php endif; ?>
		</div>
	</section>

	<?php
	$related = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post__not_in'   => array( get_the_ID() ),
		'category__in'   => wp_list_pluck( $cats, 'term_id' ),
		'orderby'        => 'rand',
	) );
	if ( $related->have_posts() ) :
		?>
		<section class="oss-section oss-section--white" style="padding-top:3rem;padding-bottom:4rem;">
			<div class="oss-container">
				<div class="oss-section-heading oss-section-heading--center">
					<span class="oss-eyebrow"><?php esc_html_e( 'Continue Reading', 'astra-child' ); ?></span>
					<h2><?php esc_html_e( 'Related Posts', 'astra-child' ); ?></h2>
				</div>
				<div class="oss-card-grid">
					<?php while ( $related->have_posts() ) : $related->the_post(); ?>
						<article class="oss-card">
							<a class="oss-card__image" href="<?php the_permalink(); ?>">
								<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?>
							</a>
							<div class="oss-card__body">
								<div class="oss-card__meta"><?php echo esc_html( get_the_date() ); ?></div>
								<h3 class="oss-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<a class="oss-card__link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'astra-child' ); ?></a>
							</div>
						</article>
					<?php endwhile; wp_reset_postdata(); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="oss-section oss-section--sage oss-cta">
		<div class="oss-container">
			<h2><?php esc_html_e( 'Ready to Begin?', 'astra-child' ); ?></h2>
			<div class="oss-cta__actions">
				<a class="oss-btn oss-btn--on-sage" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Get In Touch', 'astra-child' ); ?></a>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
