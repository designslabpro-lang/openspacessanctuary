<?php
/**
 * Single Program — V2 layout. Content runs to the site's 1300px container.
 * The block-editor body is the Overview; the extra sections (At a Glance,
 * What You'll Experience, What You'll Gain) come from the Program Details
 * fields and only appear when they have content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$pid = get_the_ID();
	$m   = function ( $k ) use ( $pid ) { return trim( (string) get_post_meta( $pid, $k, true ) ); };
	$lines = function ( $k ) use ( $m ) { return array_values( array_filter( array_map( 'trim', explode( "\n", $m( $k ) ) ) ) ); };

	oss2_page_hero( array(
		'eyebrow'  => $m( 'oss_program_audience' ),
		'title'    => get_the_title(),
		'intro'    => $m( 'oss_program_tagline' ),
		'image_id' => get_post_thumbnail_id( $pid ),
	) );
	?>

	<section class="oss-section oss-section--cream">
		<div class="oss-container">
			<div class="oss-prose oss2-prose"><?php the_content(); ?></div>
		</div>
	</section>

	<?php
	$facts = array_filter( array(
		__( 'Best For', 'astra-child' )  => $m( 'oss_program_audience' ),
		__( 'Duration', 'astra-child' )  => $m( 'oss_program_duration' ),
		__( 'Format', 'astra-child' )    => $m( 'oss_program_format' ),
		__( 'Location', 'astra-child' )  => $m( 'oss_program_location' ),
	) );
	if ( $facts ) :
		?>
		<section class="oss-section oss-section--white">
			<div class="oss-container">
				<div class="oss-section-heading oss-section-heading--center" style="text-align:center;">
					<span class="oss-eyebrow"><?php esc_html_e( 'At a Glance', 'astra-child' ); ?></span>
				</div>
				<div class="oss-prog-facts">
					<?php foreach ( $facts as $label => $value ) : ?>
						<div class="oss-prog-fact">
							<span class="oss-prog-fact__label"><?php echo esc_html( $label ); ?></span>
							<span class="oss-prog-fact__value"><?php echo esc_html( $value ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$exp = $lines( 'oss_program_experience_items' );
	if ( $exp ) :
		?>
		<section class="oss-section oss-section--cream">
			<div class="oss-container">
				<div class="oss-section-heading">
					<span class="oss-eyebrow"><?php esc_html_e( 'The Program', 'astra-child' ); ?></span>
					<h2><?php echo esc_html( $m( 'oss_program_experience_heading' ) ? $m( 'oss_program_experience_heading' ) : __( "What You'll Experience", 'astra-child' ) ); ?></h2>
					<?php if ( $m( 'oss_program_experience_intro' ) ) : ?><p><?php echo esc_html( $m( 'oss_program_experience_intro' ) ); ?></p><?php endif; ?>
				</div>
				<ul class="oss-prog-checklist">
					<?php foreach ( $exp as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$out = $lines( 'oss_program_outcomes_items' );
	if ( $out ) :
		?>
		<section class="oss-section oss-section--white">
			<div class="oss-container">
				<div class="oss-section-heading">
					<span class="oss-eyebrow"><?php esc_html_e( 'The Outcome', 'astra-child' ); ?></span>
					<h2><?php echo esc_html( $m( 'oss_program_outcomes_heading' ) ? $m( 'oss_program_outcomes_heading' ) : __( "What You'll Gain", 'astra-child' ) ); ?></h2>
				</div>
				<ul class="oss-prog-checklist">
					<?php foreach ( $out as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<section class="oss-section oss-prog-cta">
		<div class="oss-container">
			<div class="oss-prog-cta__panel">
				<h2><?php esc_html_e( 'Interested in this program?', 'astra-child' ); ?></h2>
				<p><?php esc_html_e( 'Reach out and we will help you find the right next step.', 'astra-child' ); ?></p>
				<div class="oss-prog-cta__actions">
					<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Get In Touch', 'astra-child' ); ?></a>
					<a class="oss-btn oss-btn--secondary" href="<?php echo esc_url( get_post_type_archive_link( 'oss_program' ) ); ?>"><?php esc_html_e( 'All Programs', 'astra-child' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<?php
endwhile;

oss2_connect_panel();
get_footer();
