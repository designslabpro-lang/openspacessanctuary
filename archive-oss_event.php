<?php
/**
 * Events archive — scalable, admin adds events from Events → Add New.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$oss_slides = array_values( array_filter( array_map( 'intval', (array) oss_home_get( 'hero_slide_ids' ) ) ) );

// Event and retreat types listed in the client's content document.
$oss_event_types = array(
	__( 'Upcoming Events', 'astra-child' ),
	__( "Women's Retreats", 'astra-child' ),
	__( 'Open House', 'astra-child' ),
	__( "Horseman's Symposium", 'astra-child' ),
	__( 'Workshops', 'astra-child' ),
);
$oss_chips = '<ul class="oss2-chips">';
foreach ( $oss_event_types as $type ) {
	$oss_chips .= '<li>' . esc_html( $type ) . '</li>';
}
$oss_chips .= '</ul>';

oss2_page_hero( array(
	'eyebrow'   => __( 'Join Us', 'astra-child' ),
	'title'     => __( 'Events & Retreats', 'astra-child' ),
	'intro'     => get_theme_mod( 'oss_events_intro', 'From open houses to community workshops, here is what is happening at the sanctuary.' ),
	'image_id'  => isset( $oss_slides[1] ) ? $oss_slides[1] : oss2_first_hero_slide_id(),
	'after'     => $oss_chips,
) );
?>

<section class="oss-section oss-section--cream oss2-programs-section">
	<div class="oss-container">
		<?php echo do_shortcode( '[oss_events_grid limit="-1"]' ); ?>
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

<?php
oss2_connect_panel();
get_footer();
?>
