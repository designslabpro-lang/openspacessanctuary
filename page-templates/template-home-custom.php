<?php
/**
 * Template Name: Homepage (Custom, No Builder)
 *
 * Plain-PHP homepage — zero Elementor dependency. All copy/images come
 * from inc/homepage-content.php (oss_home_get / oss_home_image), editable
 * at Appearance → Homepage Content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$icons = oss_home_icon_library();
?>

<header class="oss-hero" style="background-color:var(--oss-primary);">
	<?php
	$hero_img_id = (int) oss_home_get( 'hero_image_id' );
	$hero_img    = $hero_img_id ? wp_get_attachment_image_url( $hero_img_id, 'full' ) : '';
	if ( $hero_img ) {
		echo '<style>.oss-hero{background-image:url(\'' . esc_url( $hero_img ) . '\');}</style>';
	}
	?>
	<div class="oss-container oss-hero__inner">
		<span class="oss-eyebrow oss-hero__eyebrow"><?php echo esc_html( oss_home_get( 'hero_eyebrow' ) ); ?></span>
		<div class="oss-hero__divider"></div>
		<h1><?php echo esc_html( oss_home_get( 'hero_heading' ) ); ?></h1>
		<?php foreach ( explode( "\n", oss_home_get( 'hero_body' ) ) as $para ) : ?>
			<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
		<?php endforeach; ?>
		<div class="oss-hero__btn-row">
			<a class="oss-btn oss-btn--on-sage" href="<?php echo esc_url( oss_home_get( 'hero_btn1_url' ) ); ?>"><?php echo esc_html( oss_home_get( 'hero_btn1_text' ) ); ?></a>
			<a class="oss-btn oss-btn--secondary" href="<?php echo esc_url( oss_home_get( 'hero_btn2_url' ) ); ?>" style="border-color:var(--oss-bg);color:var(--oss-bg);"><?php echo esc_html( oss_home_get( 'hero_btn2_text' ) ); ?></a>
		</div>
	</div>
	<div class="oss-hero__scroll">
		<span class="oss-hero__scroll-text"><?php esc_html_e( 'Scroll', 'astra-child' ); ?></span>
		<span class="oss-hero__scroll-visual"><span class="oss-hero__scroll-line"></span><span class="oss-hero__scroll-circle">&darr;</span></span>
	</div>
</header>

<section class="oss-section oss-section--cream oss-power">
	<div class="oss-container">
		<div class="oss-split">
			<div class="oss-split__media oss-power__media">
				<div class="oss-power__media-frame"></div>
				<div class="oss-power__media-photo">
					<?php echo oss_home_image( 'power_image_id', 'large', 'Open Spaces Sanctuary' ); ?>
					<div class="oss-power__media-caption">
						<?php echo wp_kses_post( nl2br( esc_html( oss_home_get( 'power_caption' ) ) ) ); ?>
						<div class="oss-power__media-caption-rule"></div>
					</div>
				</div>
			</div>
			<div class="oss-split__content">
				<div class="oss-power__eyebrow-row">
					<span class="oss-eyebrow"><?php echo esc_html( oss_home_get( 'power_eyebrow' ) ); ?></span>
					<span class="oss-power__eyebrow-line"></span>
				</div>
				<h2><?php echo esc_html( oss_home_get( 'power_heading' ) ); ?></h2>
				<p><?php echo esc_html( oss_home_get( 'power_body1' ) ); ?></p>
				<p><?php echo esc_html( oss_home_get( 'power_body2' ) ); ?></p>
				<div class="oss-power__quote">
					<span class="oss-power__quote-mark">&ldquo;</span>
					<div>
						<blockquote><?php echo esc_html( oss_home_get( 'power_quote' ) ); ?></blockquote>
						<div class="oss-power__quote-rule"></div>
						<cite><?php esc_html_e( 'Open Spaces Sanctuary', 'astra-child' ); ?></cite>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="oss-section oss-section--sage" style="text-align:center;">
	<div class="oss-container">
		<h2><?php echo esc_html( oss_home_get( 'serve_heading' ) ); ?></h2>
		<p style="max-width:640px;margin-left:auto;margin-right:auto;"><?php echo esc_html( oss_home_get( 'serve_intro' ) ); ?></p>
		<div class="oss-who-grid">
			<?php foreach ( oss_home_get( 'serve_items' ) as $item ) : ?>
				<div class="oss-who-card">
					<span class="oss-who-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><?php echo isset( $icons[ $item['icon'] ] ) ? $icons[ $item['icon'] ] : $icons['compass']; // phpcs:ignore -- trusted static SVG path library. ?></svg></span>
					<span class="oss-who-card__label"><?php echo esc_html( $item['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
		<p style="margin-top:2rem;"><?php echo esc_html( oss_home_get( 'serve_closing' ) ); ?></p>
	</div>
</section>

<section class="oss-section oss-section--white">
	<div class="oss-container">
		<div class="oss-section-heading oss-section-heading--center">
			<h2><?php echo esc_html( oss_home_get( 'programs_heading' ) ); ?></h2>
			<p><?php echo esc_html( oss_home_get( 'programs_intro' ) ); ?></p>
		</div>
		<?php echo do_shortcode( '[oss_programs_grid limit="4"]' ); ?>
		<p style="text-align:center;margin-top:2.5rem;"><a class="oss-btn oss-btn--secondary" href="<?php echo esc_url( home_url( '/programs/' ) ); ?>"><?php esc_html_e( 'View All Programs', 'astra-child' ); ?></a></p>
	</div>
</section>

<section class="oss-section oss-section--cream">
	<div class="oss-container">
		<div class="oss-split oss-split--reverse">
			<div class="oss-split__media">
				<?php echo oss_home_image( 'horses_image_id', 'large', esc_attr__( 'A horse at Open Spaces Sanctuary', 'astra-child' ) ); ?>
			</div>
			<div class="oss-split__content" style="text-align:center;">
				<h2><?php echo esc_html( oss_home_get( 'horses_heading' ) ); ?></h2>
				<?php foreach ( explode( "\n", oss_home_get( 'horses_body' ) ) as $para ) : ?>
					<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
				<?php endforeach; ?>
				<a class="oss-btn oss-btn--secondary" href="<?php echo esc_url( home_url( '/meet-the-herd/' ) ); ?>"><?php echo esc_html( oss_home_get( 'horses_sub' ) ); ?></a>
			</div>
		</div>
	</div>
</section>

<section class="oss-section oss-section--white">
	<div class="oss-container">
		<div class="oss-split">
			<div class="oss-split__media">
				<?php echo oss_home_image( 'founder_image_id', 'large', esc_attr( oss_home_get( 'founder_name' ) ) ); ?>
			</div>
			<div class="oss-split__content">
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

<section class="oss-section oss-section--sage" style="text-align:center;">
	<div class="oss-container">
		<h2><?php echo esc_html( oss_home_get( 'stories_heading' ) ); ?></h2>
		<?php echo do_shortcode( '[oss_testimonials]' ); ?>
	</div>
</section>

<section class="oss-section oss-cta" style="background:var(--oss-primary-dark);color:var(--oss-bg);<?php $donate_bg = (int) oss_home_get( 'donate_image_id' ); if ( $donate_bg ) { echo 'background-image:linear-gradient(rgba(30,34,28,.72),rgba(30,34,28,.72)),url(' . esc_url( wp_get_attachment_image_url( $donate_bg, 'full' ) ) . ');background-size:cover;background-position:center;'; } ?>">
	<div class="oss-container oss-on-dark">
		<h2><?php echo esc_html( oss_home_get( 'donate_heading' ) ); ?></h2>
		<?php foreach ( explode( "\n", oss_home_get( 'donate_body' ) ) as $para ) : ?>
			<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
		<?php endforeach; ?>
		<div class="oss-cta__actions">
			<a class="oss-btn oss-btn--on-sage" href="<?php echo esc_url( oss_home_get( 'donate_btn_url' ) ); ?>"><?php echo esc_html( oss_home_get( 'donate_btn' ) ); ?></a>
		</div>
	</div>
</section>

<section class="oss-section oss-section--sage" style="text-align:center;">
	<div class="oss-container">
		<h2><?php echo esc_html( oss_home_get( 'connect_heading' ) ); ?></h2>
		<p style="max-width:560px;margin:0 auto 1.5rem;"><?php echo esc_html( oss_home_get( 'connect_body' ) ); ?></p>
		<?php echo do_shortcode( '[oss_newsletter_signup show_name="1"]' ); ?>
	</div>
</section>

<section class="oss-section oss-section--cream oss-cta">
	<div class="oss-container">
		<h2><?php echo esc_html( oss_home_get( 'final_heading' ) ); ?></h2>
		<?php foreach ( explode( "\n", oss_home_get( 'final_body' ) ) as $para ) : ?>
			<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
		<?php endforeach; ?>
		<p style="font-style:italic;color:var(--oss-brown);"><?php echo esc_html( oss_home_get( 'final_sub' ) ); ?></p>
		<div class="oss-cta__actions oss-hero__btn-row" style="justify-content:center;">
			<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( oss_home_get( 'final_btn1_url' ) ); ?>"><?php echo esc_html( oss_home_get( 'final_btn1_text' ) ); ?></a>
			<a class="oss-btn oss-btn--secondary" href="<?php echo esc_url( oss_home_get( 'final_btn2_url' ) ); ?>"><?php echo esc_html( oss_home_get( 'final_btn2_text' ) ); ?></a>
		</div>
	</div>
</section>

<?php get_footer(); ?>
