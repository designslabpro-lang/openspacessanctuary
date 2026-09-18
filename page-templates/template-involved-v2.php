<?php
/**
 * Template Name: Get Involved (V2)
 *
 * The page's own title and intro come from the native editor. The five
 * ways to get involved are the sub-pages listed in the client's content
 * document (no descriptions were supplied, so each is a titled card that
 * leads to the contact page). The donate section reuses the approved
 * "Help Us Change Lives" copy from the homepage content store.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$icons = oss_home_icon_library();
$ways  = array(
	array( 'icon' => 'hands',   'label' => __( 'Volunteer', 'astra-child' ) ),
	array( 'icon' => 'people',  'label' => __( 'Become a Partner', 'astra-child' ) ),
	array( 'icon' => 'star',    'label' => __( 'Host a Fundraiser', 'astra-child' ) ),
	array( 'icon' => 'shield',  'label' => __( 'Corporate Sponsors', 'astra-child' ) ),
	array( 'icon' => 'compass', 'label' => __( 'Internships', 'astra-child' ) ),
);

while ( have_posts() ) :
	the_post();

	oss2_page_hero( array(
		'eyebrow'  => __( 'Get Involved', 'astra-child' ),
		'title'    => get_the_title(),
		'intro'    => get_the_content(),
		'image_id' => oss_home_get( 'donate_image_id' ),
	) );
	?>

	<section class="oss-section oss2-serve-section" style="text-align:center;">
		<div class="oss-container">
			<div class="oss-section-heading oss-section-heading--center">
				<span class="oss-eyebrow"><?php esc_html_e( 'Get Involved', 'astra-child' ); ?></span>
				<h2><?php esc_html_e( 'Ways to Get Involved', 'astra-child' ); ?></h2>
			</div>
			<div class="oss2-serve-grid">
				<?php foreach ( $ways as $i => $way ) : ?>
					<a class="oss2-serve-card oss2-serve-card--c<?php echo esc_attr( ( $i % 5 ) + 1 ); ?>" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
						<span class="oss2-serve-card__icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><?php echo isset( $icons[ $way['icon'] ] ) ? $icons[ $way['icon'] ] : $icons['compass']; // phpcs:ignore -- trusted static SVG path library. ?></svg>
						</span>
						<span class="oss2-serve-card__label"><?php echo esc_html( $way['label'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="oss-section oss-section--white oss2-give">
		<span class="oss2-give__circles oss2-give__circles--tl" aria-hidden="true"><span></span><span></span><span></span></span>
		<span class="oss2-give__circles oss2-give__circles--br" aria-hidden="true"><span></span><span></span><span></span></span>
		<div class="oss-container oss2-give__grid">
			<div class="oss2-give__media">
				<?php echo oss_home_image( 'power_image_id', 'large', esc_attr( oss_home_get( 'donate_heading' ) ) ); ?>
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

	<?php
endwhile;

oss2_connect_panel();
get_footer();
