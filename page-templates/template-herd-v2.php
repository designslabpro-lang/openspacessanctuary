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

	oss2_page_hero( array(
		'eyebrow'        => oss_herd_get( 'hero_eyebrow' ),
		'title'          => oss_herd_get( 'hero_heading' ),
		'intro'          => oss_herd_get( 'hero_body' ),
		'image_id'       => (int) oss_herd_get( 'hero_image_id' ),
		'image_position' => oss_herd_get( 'hero_image_pos' ),
		'image_fit'      => oss_herd_get( 'hero_image_fit' ),
	) );
	?>

	<section class="oss-section oss-section--cream">
		<div class="oss-container">
			<div class="oss2-feature oss2-feature--reverse">
				<div class="oss2-feature__media">
					<?php echo oss_herd_image( 'herd_image_id', 'large', esc_attr__( 'A horse at Open Spaces Sanctuary', 'astra-child' ) ); ?>
				</div>
				<div>
					<span class="oss-eyebrow"><?php echo esc_html( oss_herd_get( 'herd_eyebrow' ) ); ?></span>
					<h2><?php echo esc_html( oss_herd_get( 'herd_heading' ) ); ?></h2>
					<?php foreach ( explode( "\n", oss_herd_get( 'herd_body' ) ) as $para ) : ?>
						<?php if ( trim( $para ) ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endif; ?>
					<?php endforeach; ?>
					<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( oss_herd_get( 'herd_btn_url' ) ); ?>"><?php echo esc_html( oss_herd_get( 'herd_btn_text' ) ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<?php
endwhile;

oss2_connect_panel();
get_footer();
