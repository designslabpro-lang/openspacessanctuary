<?php
/**
 * Template Name: About (V2)
 *
 * About page in the V2 design language. Copy comes from inc/about-content.php
 * (Appearance → About Page); image slots fall back to the homepage's real
 * photos when no image has been chosen for the About page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$story_img = oss2_image_id_or( oss_about_get( 'story_image_id' ), oss2_first_hero_slide_id() );
$phil_img  = oss2_image_id_or( oss_about_get( 'philosophy_image_id' ), oss_home_get( 'power_image_id' ) );

oss2_page_hero( array(
	'eyebrow'  => oss_about_get( 'hero_eyebrow' ),
	'title'    => oss_about_get( 'hero_heading' ),
	'intro'    => oss_about_get( 'hero_body' ),
	'image_id' => oss2_image_id_or( oss_about_get( 'hero_image_id' ), oss_home_get( 'power_image_id' ) ),
) );
?>

<section class="oss-section oss-section--cream">
	<div class="oss-container">
		<div class="oss2-feature">
			<div class="oss2-feature__media">
				<?php echo oss2_image( $story_img, oss_about_get( 'story_heading' ) ); ?>
			</div>
			<div>
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
		<div class="oss2-feature oss2-feature--reverse">
			<div class="oss2-feature__media">
				<?php echo oss2_image( $phil_img, oss_about_get( 'philosophy_heading' ) ); ?>
				<blockquote class="oss2-feature__quote">&ldquo;<?php echo esc_html( oss_about_get( 'philosophy_quote' ) ); ?>&rdquo;</blockquote>
			</div>
			<div>
				<span class="oss-eyebrow"><?php echo esc_html( oss_about_get( 'philosophy_eyebrow' ) ); ?></span>
				<h2><?php echo esc_html( oss_about_get( 'philosophy_heading' ) ); ?></h2>
				<?php foreach ( explode( "\n", oss_about_get( 'philosophy_body' ) ) as $para ) : ?>
					<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<section class="oss-section oss-section--cream">
	<div class="oss-container">
		<div class="oss2-founder">
			<div class="oss2-founder__media">
				<?php echo oss_home_image( 'founder_image_id', 'large', esc_attr( oss_about_get( 'founder_name' ) ) ); ?>
			</div>
			<div>
				<span class="oss2-founder__mark" aria-hidden="true">&ldquo;</span>
				<span class="oss-eyebrow"><?php echo esc_html( oss_about_get( 'founder_heading' ) ); ?></span>
				<h2><?php echo esc_html( oss_about_get( 'founder_name' ) ); ?></h2>
				<?php foreach ( explode( "\n", oss_about_get( 'founder_body' ) ) as $para ) : ?>
					<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
				<?php endforeach; ?>
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
				<?php echo oss_home_image( 'final_image_id', 'large', esc_attr( oss_about_get( 'final_heading' ) ) ); ?>
			</div>
			<div class="oss2-close__text">
				<h2><?php echo esc_html( oss_about_get( 'final_heading' ) ); ?></h2>
				<div class="oss-divider"></div>
				<p><?php echo esc_html( oss_about_get( 'final_body' ) ); ?></p>
				<div class="oss2-close__actions">
					<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( oss_about_get( 'final_btn_url' ) ); ?>"><?php echo esc_html( oss_about_get( 'final_btn_text' ) ); ?></a>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
oss2_connect_panel();
get_footer();
