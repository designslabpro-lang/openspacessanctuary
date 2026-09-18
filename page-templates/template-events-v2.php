<?php
/**
 * Template Name: Events & Retreats (V2)
 *
 * Timeline of events from the theme's own content store
 * (Appearance → Events). All information is shown here on the one page —
 * there are no single event pages.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$oss_slides = array_values( array_filter( array_map( 'intval', (array) oss_home_get( 'hero_slide_ids' ) ) ) );
$oss_chips  = '<ul class="oss2-chips">';
foreach ( oss_events_types() as $type ) {
	$oss_chips .= '<li>' . esc_html( $type ) . '</li>';
}
$oss_chips .= '</ul>';

oss2_page_hero( array(
	'eyebrow'  => __( 'Join Us', 'astra-child' ),
	'title'    => oss_events_get( 'heading' ),
	'intro'    => oss_events_get( 'intro' ),
	'image_id' => isset( $oss_slides[1] ) ? $oss_slides[1] : oss2_first_hero_slide_id(),
	'after'    => $oss_chips,
) );

$oss_events = oss_events_sorted();
?>

<section class="oss-section oss-section--cream">
	<div class="oss-container">
		<?php if ( ! $oss_events ) : ?>
			<p class="oss-empty-state" style="text-align:center;"><?php esc_html_e( 'Upcoming events will be posted here.', 'astra-child' ); ?></p>
		<?php else : ?>
			<ol class="oss2-timeline">
				<?php foreach ( $oss_events as $ev ) :
					$ts     = ! empty( $ev['date'] ) ? strtotime( $ev['date'] ) : 0;
					$img_id = ! empty( $ev['image_id'] ) ? (int) $ev['image_id'] : 0;
					$has_im = $img_id && wp_get_attachment_image_src( $img_id, 'medium_large' );
					?>
					<li class="oss2-timeline__item">
						<div class="oss2-timeline__date">
							<?php if ( $ts ) : ?>
								<span class="oss2-timeline__day"><?php echo esc_html( date_i18n( 'd', $ts ) ); ?></span>
								<span class="oss2-timeline__mon"><?php echo esc_html( date_i18n( 'M Y', $ts ) ); ?></span>
							<?php else : ?>
								<span class="oss2-timeline__mon"><?php esc_html_e( 'TBA', 'astra-child' ); ?></span>
							<?php endif; ?>
						</div>
						<article class="oss2-timeline__card<?php echo $has_im ? '' : ' oss2-timeline__card--noimg'; ?>">
							<?php if ( $has_im ) : ?>
								<div class="oss2-timeline__img"><?php echo wp_get_attachment_image( $img_id, 'medium_large', false, array( 'alt' => esc_attr( $ev['title'] ) ) ); ?></div>
							<?php endif; ?>
							<div class="oss2-timeline__body">
								<?php if ( ! empty( $ev['type'] ) ) : ?><span class="oss2-timeline__type"><?php echo esc_html( $ev['type'] ); ?></span><?php endif; ?>
								<h3><?php echo esc_html( $ev['title'] ); ?></h3>
								<ul class="oss2-timeline__meta">
									<?php if ( $ts ) : ?><li><?php echo esc_html( date_i18n( 'l, F j, Y', $ts ) ); ?></li><?php endif; ?>
									<?php if ( ! empty( $ev['time'] ) ) : ?><li><?php echo esc_html( $ev['time'] ); ?></li><?php endif; ?>
									<?php if ( ! empty( $ev['location'] ) ) : ?><li><?php echo esc_html( $ev['location'] ); ?></li><?php endif; ?>
								</ul>
								<?php if ( ! empty( $ev['description'] ) ) : ?><p><?php echo esc_html( $ev['description'] ); ?></p><?php endif; ?>
								<?php if ( ! empty( $ev['link'] ) ) : ?>
									<a class="oss-btn oss-btn--primary oss-btn--sm" href="<?php echo esc_url( $ev['link'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Registration', 'astra-child' ); ?></a>
								<?php endif; ?>
							</div>
						</article>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>
</section>

<?php
oss2_connect_panel();
get_footer();
