<?php
/**
 * Template Name: Meet the Herd (V2)
 *
 * The page's own title and intro come from the native editor; the herd
 * section reuses the approved "Meet Our Horses" copy from the homepage
 * content store. "Sponsor a Horse" is one of the approved site buttons.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	// Second slideshow photo, so the hero doesn't repeat the pasture shot
	// used in the herd section right below it.
	$oss_slides = array_values( array_filter( array_map( 'intval', (array) oss_home_get( 'hero_slide_ids' ) ) ) );
	oss2_page_hero( array(
		'eyebrow'  => __( 'Our Herd', 'astra-child' ),
		'title'    => get_the_title(),
		'intro'    => get_the_content(),
		'image_id' => isset( $oss_slides[1] ) ? $oss_slides[1] : oss2_first_hero_slide_id(),
	) );
	?>

	<section class="oss-section oss-section--cream">
		<div class="oss-container">
			<div class="oss2-feature oss2-feature--reverse">
				<div class="oss2-feature__media">
					<?php echo oss_home_image( 'horses_image_id', 'large', esc_attr__( 'A horse at Open Spaces Sanctuary', 'astra-child' ) ); ?>
				</div>
				<div>
					<span class="oss-eyebrow"><?php esc_html_e( 'The Herd', 'astra-child' ); ?></span>
					<h2><?php echo esc_html( oss_home_get( 'horses_heading' ) ); ?></h2>
					<?php foreach ( explode( "\n", oss_home_get( 'horses_body' ) ) as $para ) : ?>
						<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
					<?php endforeach; ?>
					<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Sponsor a Horse', 'astra-child' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<?php
endwhile;

oss2_connect_panel();
get_footer();
