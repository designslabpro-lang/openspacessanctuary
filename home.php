<?php
/**
 * Blog listing (posts page) — featured post + editorial grid + pagination.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$oss_paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$oss_hero_img = get_theme_mod( 'oss_blog_hero_image', '' );
?>

<header class="oss-hero oss-hero--page"<?php echo $oss_hero_img ? ' style="background-image:url(\'' . esc_url( $oss_hero_img ) . '\');"' : ''; ?>>
	<div class="oss-container oss-hero__inner">
		<span class="oss-eyebrow oss-hero__eyebrow"><?php esc_html_e( 'From the Sanctuary', 'astra-child' ); ?></span>
		<h1><?php esc_html_e( 'Blog', 'astra-child' ); ?></h1>
		<p><?php echo esc_html( get_theme_mod( 'oss_blog_intro', 'Stories, reflections, and updates on healing, horses, and community.' ) ); ?></p>
	</div>
</header>

<?php
$oss_featured = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 1, 'paged' => $oss_paged, 'ignore_sticky_posts' => true ) );
if ( 1 === $oss_paged && $oss_featured->have_posts() ) :
	$oss_featured->the_post();
	?>
	<section class="oss-section oss-section--cream" style="padding-bottom:2rem;">
		<div class="oss-container">
			<div class="oss-split">
				<div class="oss-split__media">
					<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'large' ); ?></a>
				</div>
				<div class="oss-split__content">
					<div class="oss-card__meta"><?php echo esc_html( get_the_date() ); ?></div>
					<h2><a href="<?php the_permalink(); ?>" style="color:var(--oss-text);text-decoration:none;"><?php the_title(); ?></a></h2>
					<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32 ) ); ?></p>
					<a class="oss-btn oss-btn--secondary" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'astra-child' ); ?></a>
				</div>
			</div>
			<?php oss_gold_divider(); ?>
		</div>
	</section>
	<?php
	wp_reset_postdata();
endif;
?>

<section class="oss-section oss-section--cream" style="padding-top:0;">
	<div class="oss-container">
		<?php
		$oss_grid = new WP_Query( array(
			'post_type'      => 'post',
			'posts_per_page' => 9,
			'paged'          => $oss_paged,
		) );
		if ( $oss_grid->have_posts() ) :
			echo '<div class="oss-card-grid">';
			while ( $oss_grid->have_posts() ) :
				$oss_grid->the_post();
				?>
				<article class="oss-card">
					<a class="oss-card__image" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?>
					</a>
					<div class="oss-card__body">
						<?php $cats = get_the_category(); ?>
						<div class="oss-card__meta"><?php echo esc_html( $cats ? $cats[0]->name : '' ); ?> &middot; <?php echo esc_html( get_the_date() ); ?></div>
						<h3 class="oss-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<div class="oss-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></div>
						<a class="oss-card__link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'astra-child' ); ?></a>
					</div>
				</article>
				<?php
			endwhile;
			echo '</div>';

			if ( $oss_grid->max_num_pages > 1 ) :
				echo '<div class="oss-pagination" style="margin-top:3rem;text-align:center;">';
				echo paginate_links( array( 'total' => $oss_grid->max_num_pages, 'current' => $oss_paged ) );
				echo '</div>';
			endif;
		else :
			echo '<p class="oss-empty-state">' . esc_html__( 'No posts yet — publish your first from Posts → Add New.', 'astra-child' ) . '</p>';
		endif;
		wp_reset_postdata();
		?>
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
