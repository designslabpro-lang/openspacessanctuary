<?php
/**
 * Template Name: Homepage V2 (Custom)
 *
 * A ground-up homepage rebuild — new layout, new CSS (assets/css/home-v2.css),
 * zero Elementor, and it does not reuse or modify the previous Elementor
 * front page or the earlier "Home (Custom)" template. It reads the same
 * admin-editable content store (inc/homepage-content.php) so the existing
 * approved copy carries over and stays editable at
 * Appearance → Homepage Content — only the visual design is new.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$icons          = oss_home_icon_library();
$hero_slide_ids = array_values( array_filter( array_map( 'intval', oss_home_get( 'hero_slide_ids' ) ) ) );
?>

<header class="oss2-hero">
	<div class="oss-container">
		<div class="oss2-hero__panel">
			<div class="oss2-hero__panel-inner">
			<span class="oss-eyebrow oss2-hero__eyebrow"><?php echo esc_html( oss_home_get( 'hero_eyebrow' ) ); ?></span>
			<h1><?php echo esc_html( oss_home_get( 'hero_heading' ) ); ?></h1>
			<div class="oss2-hero__body">
				<?php foreach ( explode( "\n", oss_home_get( 'hero_body' ) ) as $para ) : ?>
					<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
				<?php endforeach; ?>
			</div>
			<div class="oss2-hero__actions">
				<a class="oss-btn oss-btn--on-sage" href="<?php echo esc_url( oss_home_get( 'hero_btn1_url' ) ); ?>"><?php echo esc_html( oss_home_get( 'hero_btn1_text' ) ); ?></a>
				<a class="oss-btn oss-btn--secondary" href="<?php echo esc_url( oss_home_get( 'hero_btn2_url' ) ); ?>" style="border-color:var(--oss-bg);color:var(--oss-bg);"><?php echo esc_html( oss_home_get( 'hero_btn2_text' ) ); ?></a>
			</div>
			<p class="oss2-hero__trust"><?php esc_html_e( 'A 501(c)(3) nonprofit organization', 'astra-child' ); ?></p>
			</div>
		</div>
		<div class="oss2-hero__photo">
			<div class="oss-hero__slides" data-oss-hero-slides data-oss-autoplay="6500">
				<?php foreach ( $hero_slide_ids as $i => $slide_id ) :
					$slide_url = wp_get_attachment_image_url( $slide_id, 'full' );
					if ( ! $slide_url ) {
						continue;
					}
					?>
					<div class="oss-hero__slide<?php echo 0 === $i ? ' is-active' : ''; ?>" style="background-image:url('<?php echo esc_url( $slide_url ); ?>');"></div>
				<?php endforeach; ?>
			</div>
			<span class="oss2-hero__badge">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s7-7.4 7-12.5A7 7 0 0 0 5 9.5C5 14.6 12 22 12 22z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
				<?php esc_html_e( 'Ocala, Florida', 'astra-child' ); ?>
			</span>
			<?php if ( count( $hero_slide_ids ) > 1 ) : ?>
				<div class="oss2-hero__dots">
					<?php foreach ( $hero_slide_ids as $i => $slide_id ) : ?>
						<button type="button" class="oss2-hero__dot<?php echo 0 === $i ? ' is-active' : ''; ?>" data-oss-carousel-dot aria-label="<?php echo esc_attr( sprintf( __( 'Go to photo %d', 'astra-child' ), $i + 1 ) ); ?>"></button>
					<?php endforeach; ?>
				</div>
				<div class="oss2-hero__nav">
					<button type="button" class="oss2-hero__nav-btn" data-oss-carousel-prev aria-label="<?php esc_attr_e( 'Previous photo', 'astra-child' ); ?>">&larr;</button>
					<button type="button" class="oss2-hero__nav-btn" data-oss-carousel-next aria-label="<?php esc_attr_e( 'Next photo', 'astra-child' ); ?>">&rarr;</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</header>

<section class="oss-section oss-section--cream">
	<div class="oss-container">
		<div class="oss2-feature">
			<div class="oss2-feature__media">
				<?php echo oss_home_image( 'power_image_id', 'large', 'Open Spaces Sanctuary' ); ?>
				<blockquote class="oss2-feature__quote">&ldquo;<?php echo esc_html( oss_home_get( 'power_quote' ) ); ?>&rdquo;</blockquote>
			</div>
			<div>
				<span class="oss-eyebrow"><?php echo esc_html( oss_home_get( 'power_eyebrow' ) ); ?></span>
				<h2><?php echo esc_html( oss_home_get( 'power_heading' ) ); ?></h2>
				<p><?php echo esc_html( oss_home_get( 'power_body1' ) ); ?></p>
				<p><?php echo esc_html( oss_home_get( 'power_body2' ) ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="oss-section oss2-serve-section" style="text-align:center;">
	<div class="oss-container">
		<div class="oss-section-heading oss-section-heading--center">
			<span class="oss-eyebrow"><?php esc_html_e( 'Who We Serve', 'astra-child' ); ?></span>
			<h2><?php echo esc_html( oss_home_get( 'serve_heading' ) ); ?></h2>
			<p><?php echo esc_html( oss_home_get( 'serve_intro' ) ); ?></p>
		</div>
		<div class="oss2-serve-grid">
			<?php foreach ( oss_home_get( 'serve_items' ) as $i => $item ) : ?>
				<div class="oss2-serve-card oss2-serve-card--c<?php echo esc_attr( ( $i % 5 ) + 1 ); ?>">
					<span class="oss2-serve-card__icon">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><?php echo isset( $icons[ $item['icon'] ] ) ? $icons[ $item['icon'] ] : $icons['compass']; // phpcs:ignore -- trusted static SVG path library. ?></svg>
					</span>
					<span class="oss2-serve-card__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<span class="oss2-serve-card__label"><?php echo esc_html( $item['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
		<p><?php echo esc_html( oss_home_get( 'serve_closing' ) ); ?></p>
	</div>
</section>

<section class="oss-section oss-section--cream oss2-programs-section">
	<div class="oss-container">
		<div class="oss-section-heading oss-section-heading--center" style="text-align:center;margin-left:auto;margin-right:auto;">
			<span class="oss-eyebrow"><?php esc_html_e( 'What We Offer', 'astra-child' ); ?></span>
			<h2><?php echo esc_html( oss_home_get( 'programs_heading' ) ); ?></h2>
			<p><?php echo esc_html( oss_home_get( 'programs_intro' ) ); ?></p>
		</div>
		<?php echo do_shortcode( '[oss_programs_grid limit="4"]' ); ?>
		<p style="text-align:center;margin-top:2.5rem;"><a class="oss-btn oss-btn--secondary" href="<?php echo esc_url( home_url( '/programs/' ) ); ?>"><?php esc_html_e( 'View All Programs', 'astra-child' ); ?></a></p>
	</div>
</section>

<section class="oss2-herd" style="<?php $horses_bg = (int) oss_home_get( 'horses_image_id' ); if ( $horses_bg ) { echo 'background-image:url(' . esc_url( wp_get_attachment_image_url( $horses_bg, 'full' ) ) . ');'; } ?>">
	<div class="oss-container oss2-herd__grid">
		<div class="oss2-herd__panel">
			<span class="oss-eyebrow"><?php esc_html_e( 'The Herd', 'astra-child' ); ?></span>
			<h2><?php echo esc_html( oss_home_get( 'horses_heading' ) ); ?></h2>
			<?php foreach ( explode( "\n", oss_home_get( 'horses_body' ) ) as $para ) : ?>
				<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
			<?php endforeach; ?>
			<a class="oss-btn oss-btn--on-sage" href="<?php echo esc_url( home_url( '/meet-the-herd/' ) ); ?>"><?php echo esc_html( oss_home_get( 'horses_sub' ) ); ?></a>
		</div>
		<div class="oss2-herd__spacer" aria-hidden="true"></div>
	</div>
</section>

<section class="oss-section oss-section--white">
	<div class="oss-container">
		<div class="oss2-founder">
			<div class="oss2-founder__media">
				<?php echo oss_home_image( 'founder_image_id', 'large', esc_attr( oss_home_get( 'founder_name' ) ) ); ?>
			</div>
			<div>
				<span class="oss2-founder__mark" aria-hidden="true">&ldquo;</span>
				<span class="oss-eyebrow"><?php echo esc_html( oss_home_get( 'founder_heading' ) ); ?></span>
				<h2><?php echo esc_html( oss_home_get( 'founder_name' ) ); ?></h2>
				<?php foreach ( explode( "\n", oss_home_get( 'founder_body' ) ) as $para ) : ?>
					<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
				<?php endforeach; ?>
				<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php echo esc_html( oss_home_get( 'founder_btn' ) ); ?></a>
			</div>
		</div>
	</div>
</section>

<section class="oss-section oss-section--sage oss2-stories" style="text-align:center;">
	<span class="oss2-stories__circles oss2-stories__circles--tl" aria-hidden="true"><span></span><span></span><span></span></span>
	<span class="oss2-stories__circles oss2-stories__circles--br" aria-hidden="true"><span></span><span></span><span></span></span>
	<div class="oss-container">
		<span class="oss-eyebrow"><?php esc_html_e( 'Stories of Hope', 'astra-child' ); ?></span>
		<h2><?php echo esc_html( oss_home_get( 'stories_heading' ) ); ?></h2>
		<div class="oss2-stories__grid">
			<?php foreach ( oss_home_get( 'testimonials' ) as $story ) : ?>
				<div class="oss2-stories__card">
					<p class="oss2-stories__quote">&ldquo;<?php echo esc_html( $story['quote'] ); ?>&rdquo;</p>
					<div class="oss2-stories__who">
						<?php
						$story_img = ! empty( $story['image_id'] ) ? (int) $story['image_id'] : 0;
						if ( $story_img && wp_get_attachment_image_src( $story_img, 'thumbnail' ) ) {
							echo wp_get_attachment_image( $story_img, 'thumbnail', false, array( 'class' => 'oss2-stories__avatar', 'alt' => esc_attr( $story['name'] ) ) );
						}
						?>
						<p class="oss2-stories__name">&mdash; <?php echo esc_html( $story['name'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="oss-section oss-section--white oss2-give">
	<span class="oss2-give__circles oss2-give__circles--tl" aria-hidden="true"><span></span><span></span><span></span></span>
	<span class="oss2-give__circles oss2-give__circles--br" aria-hidden="true"><span></span><span></span><span></span></span>
	<div class="oss-container oss2-give__grid">
		<div class="oss2-give__media">
			<?php echo oss_home_image( 'donate_image_id', 'large', esc_attr( oss_home_get( 'donate_heading' ) ) ); ?>
		</div>
		<div class="oss2-give__text">
			<span class="oss-eyebrow"><?php esc_html_e( 'Support the Sanctuary', 'astra-child' ); ?></span>
			<h2><?php echo esc_html( oss_home_get( 'donate_heading' ) ); ?></h2>
			<?php foreach ( explode( "\n", oss_home_get( 'donate_body' ) ) as $para ) : ?>
				<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
			<?php endforeach; ?>
			<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( oss_home_get( 'donate_btn_url' ) ); ?>"><?php echo esc_html( oss_home_get( 'donate_btn' ) ); ?></a>
		</div>
	</div>
</section>

<section class="oss-section oss-section--cream">
	<div class="oss-container">
		<div class="oss2-connect">
			<div class="oss2-connect__text">
				<span class="oss-eyebrow"><?php esc_html_e( 'Newsletter', 'astra-child' ); ?></span>
				<h2><?php echo esc_html( oss_home_get( 'connect_heading' ) ); ?></h2>
				<p><?php echo esc_html( oss_home_get( 'connect_body' ) ); ?></p>
			</div>
			<div class="oss2-connect__form">
				<?php echo do_shortcode( '[oss_newsletter_signup show_name="1"]' ); ?>
			</div>
		</div>
	</div>
</section>

<section class="oss-section oss2-close-section">
	<span class="oss2-close__circles oss2-close__circles--tl" aria-hidden="true"><span></span><span></span><span></span></span>
	<span class="oss2-close__circles oss2-close__circles--br" aria-hidden="true"><span></span><span></span><span></span></span>
	<div class="oss-container">
		<div class="oss2-close">
			<div class="oss2-close__media">
				<?php echo oss_home_image( 'final_image_id', 'large', esc_attr( oss_home_get( 'final_heading' ) ) ); ?>
			</div>
			<div class="oss2-close__text">
				<span class="oss-eyebrow"><?php esc_html_e( 'Welcome', 'astra-child' ); ?></span>
				<h2><?php echo esc_html( oss_home_get( 'final_heading' ) ); ?></h2>
				<div class="oss-divider"></div>
				<?php foreach ( explode( "\n", oss_home_get( 'final_body' ) ) as $para ) : ?>
					<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
				<?php endforeach; ?>
				<p class="oss2-close__sub"><?php echo esc_html( oss_home_get( 'final_sub' ) ); ?></p>
				<div class="oss2-close__actions">
					<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( oss_home_get( 'final_btn1_url' ) ); ?>"><?php echo esc_html( oss_home_get( 'final_btn1_text' ) ); ?></a>
					<a class="oss-btn oss-btn--secondary" href="<?php echo esc_url( oss_home_get( 'final_btn2_url' ) ); ?>"><?php echo esc_html( oss_home_get( 'final_btn2_text' ) ); ?></a>
				</div>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>
