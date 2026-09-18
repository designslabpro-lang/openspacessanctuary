<?php
/**
 * Template Name: About (Custom, No Builder)
 *
 * Plain-PHP About page — zero Elementor dependency. All copy/images come
 * from inc/about-content.php (oss_about_get / oss_about_image), editable
 * at Appearance → About Page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$oss_about_hero_url = wp_get_attachment_image_url( (int) oss_about_get( 'hero_image_id' ), 'full' );
?>

<header class="oss-hero oss-hero--page"<?php echo $oss_about_hero_url ? ' style="background-image:url(\'' . esc_url( $oss_about_hero_url ) . '\');"' : ''; ?>>
	<div class="oss-container oss-hero__inner">
		<span class="oss-eyebrow oss-hero__eyebrow"><?php echo esc_html( oss_about_get( 'hero_eyebrow' ) ); ?></span>
		<div class="oss-hero__divider"></div>
		<h1><?php echo esc_html( oss_about_get( 'hero_heading' ) ); ?></h1>
		<p><?php echo esc_html( oss_about_get( 'hero_body' ) ); ?></p>
	</div>
</header>

<section class="oss-section oss-section--cream">
	<div class="oss-container">
		<div class="oss-split">
			<div class="oss-split__media">
				<?php echo oss_about_image( 'story_image_id', 'large', 'Open Spaces Sanctuary' ); ?>
			</div>
			<div class="oss-split__content">
				<span class="oss-eyebrow"><?php echo esc_html( oss_about_get( 'story_eyebrow' ) ); ?></span>
				<h2><?php echo esc_html( oss_about_get( 'story_heading' ) ); ?></h2>
				<?php foreach ( explode( "\n", oss_about_get( 'story_body' ) ) as $para ) : ?>
					<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<section class="oss-section oss-section--white">
	<div class="oss-container">
		<div class="oss-split oss-split--reverse">
			<div class="oss-split__content">
				<span class="oss-eyebrow"><?php echo esc_html( oss_about_get( 'philosophy_eyebrow' ) ); ?></span>
				<h2><?php echo esc_html( oss_about_get( 'philosophy_heading' ) ); ?></h2>
				<?php foreach ( explode( "\n", oss_about_get( 'philosophy_body' ) ) as $para ) : ?>
					<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
				<?php endforeach; ?>
				<p><em><?php echo esc_html( oss_about_get( 'philosophy_quote' ) ); ?></em></p>
			</div>
			<div class="oss-split__media">
				<?php echo oss_about_image( 'philosophy_image_id', 'large', 'A guided equine-assisted session' ); ?>
			</div>
		</div>
	</div>
</section>

<section class="oss-section oss-section--sage" style="text-align:center;">
	<div class="oss-container">
		<div class="oss-section-heading oss-section-heading--center">
			<span class="oss-eyebrow oss-on-dark"><?php echo esc_html( oss_about_get( 'founder_heading' ) ); ?></span>
			<h2 class="oss-on-dark"><?php echo esc_html( oss_about_get( 'founder_name' ) ); ?></h2>
			<?php foreach ( explode( "\n", oss_about_get( 'founder_body' ) ) as $para ) : ?>
				<?php if ( trim( $para ) ) : ?><p class="oss-on-dark"><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="oss-section oss-section--cream" style="text-align:center;">
	<div class="oss-container">
		<div class="oss-section-heading oss-section-heading--center">
			<h2><?php echo esc_html( oss_about_get( 'final_heading' ) ); ?></h2>
			<p><?php echo esc_html( oss_about_get( 'final_body' ) ); ?></p>
			<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( oss_about_get( 'final_btn_url' ) ); ?>"><?php echo esc_html( oss_about_get( 'final_btn_text' ) ); ?></a>
		</div>
	</div>
</section>

<?php get_footer(); ?>
