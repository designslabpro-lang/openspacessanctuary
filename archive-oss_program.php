<?php
/**
 * Programs archive — scalable, admin adds programs from Programs → Add New.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

oss2_page_hero( array(
	'eyebrow'   => __( 'What We Offer', 'astra-child' ),
	'title'     => __( 'Our Programs', 'astra-child' ),
	'intro'     => get_theme_mod( 'oss_programs_intro', 'Equine-assisted programs designed for connection, confidence, and healing — for every stage of the journey.' ),
	'image_id'  => oss_home_get( 'power_image_id' ),
) );
?>

<section class="oss-section oss-section--cream oss2-programs-section">
	<div class="oss-container">
		<?php echo do_shortcode( '[oss_programs_grid limit="-1"]' ); ?>
	</div>
</section>

<section class="oss-section oss-section--sage oss-cta">
	<div class="oss-container">
		<h2><?php esc_html_e( 'Not sure which program is right for you?', 'astra-child' ); ?></h2>
		<p><?php esc_html_e( 'Reach out and our team will help you find the best fit.', 'astra-child' ); ?></p>
		<div class="oss-cta__actions">
			<a class="oss-btn oss-btn--on-sage" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Get In Touch', 'astra-child' ); ?></a>
		</div>
	</div>
</section>

<?php
oss2_connect_panel();
get_footer();
?>
