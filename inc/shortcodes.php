<?php
/**
 * Scalable, Elementor-friendly shortcodes.
 * Drop these into an Elementor "Shortcode" widget (or any WP content area)
 * so grids stay dynamic — adding a Program/Event/Post automatically appears,
 * with no theme editing required.
 */

defined( 'ABSPATH' ) || exit;

function oss_child_programs_grid_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'limit' => 3 ), $atts, 'oss_programs_grid' );
	$q = new WP_Query( array(
		'post_type'      => 'oss_program',
		'posts_per_page' => (int) $atts['limit'],
		'post_status'    => 'publish',
	) );

	if ( ! $q->have_posts() ) {
		return '<p class="oss-empty-state">' . esc_html__( 'Add your first program from Programs → Add New.', 'astra-child' ) . '</p>';
	}

	ob_start();
	echo '<div class="oss-card-grid">';
	while ( $q->have_posts() ) {
		$q->the_post();
		$audience = get_post_meta( get_the_ID(), 'oss_program_audience', true );
		?>
		<article class="oss-card">
			<a class="oss-card__image" href="<?php the_permalink(); ?>">
				<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?>
			</a>
			<div class="oss-card__body">
				<?php if ( $audience ) : ?><div class="oss-card__meta"><?php echo esc_html( $audience ); ?></div><?php endif; ?>
				<h3 class="oss-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<div class="oss-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></div>
				<a class="oss-card__link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Learn More', 'astra-child' ); ?></a>
			</div>
		</article>
		<?php
	}
	echo '</div>';
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'oss_programs_grid', 'oss_child_programs_grid_shortcode' );

function oss_child_events_grid_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'limit' => 3 ), $atts, 'oss_events_grid' );
	$q = new WP_Query( array(
		'post_type'      => 'oss_event',
		'posts_per_page' => (int) $atts['limit'],
		'post_status'    => 'publish',
		'meta_key'       => 'oss_event_date',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
	) );

	if ( ! $q->have_posts() ) {
		return '<p class="oss-empty-state">' . esc_html__( 'Add your first event from Events → Add New.', 'astra-child' ) . '</p>';
	}

	ob_start();
	echo '<div class="oss-card-grid">';
	while ( $q->have_posts() ) {
		$q->the_post();
		$date = get_post_meta( get_the_ID(), 'oss_event_date', true );
		$time = get_post_meta( get_the_ID(), 'oss_event_time', true );
		$loc  = get_post_meta( get_the_ID(), 'oss_event_location', true );
		$reg  = get_post_meta( get_the_ID(), 'oss_event_registration', true );
		?>
		<article class="oss-card">
			<a class="oss-card__image" href="<?php the_permalink(); ?>">
				<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?>
			</a>
			<div class="oss-card__body">
				<h3 class="oss-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<ul class="oss-event-card__details">
					<?php if ( $date ) : ?><li><strong><?php esc_html_e( 'Date:', 'astra-child' ); ?></strong> <?php echo esc_html( gmdate( 'F j, Y', strtotime( $date ) ) ); ?></li><?php endif; ?>
					<?php if ( $time ) : ?><li><strong><?php esc_html_e( 'Time:', 'astra-child' ); ?></strong> <?php echo esc_html( $time ); ?></li><?php endif; ?>
					<?php if ( $loc ) : ?><li><strong><?php esc_html_e( 'Location:', 'astra-child' ); ?></strong> <?php echo esc_html( $loc ); ?></li><?php endif; ?>
				</ul>
				<a class="oss-card__link" href="<?php echo esc_url( $reg ? $reg : get_permalink() ); ?>"><?php esc_html_e( 'Register', 'astra-child' ); ?></a>
			</div>
		</article>
		<?php
	}
	echo '</div>';
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'oss_events_grid', 'oss_child_events_grid_shortcode' );

function oss_child_blog_grid_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'limit' => 3 ), $atts, 'oss_blog_grid' );
	$q = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => (int) $atts['limit'],
		'post_status'    => 'publish',
	) );

	if ( ! $q->have_posts() ) {
		return '<p class="oss-empty-state">' . esc_html__( 'Publish your first post from Posts → Add New.', 'astra-child' ) . '</p>';
	}

	ob_start();
	echo '<div class="oss-card-grid">';
	while ( $q->have_posts() ) {
		$q->the_post();
		?>
		<article class="oss-card">
			<a class="oss-card__image" href="<?php the_permalink(); ?>">
				<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?>
			</a>
			<div class="oss-card__body">
				<div class="oss-card__meta"><?php echo esc_html( get_the_date() ); ?></div>
				<h3 class="oss-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<div class="oss-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></div>
				<a class="oss-card__link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'astra-child' ); ?></a>
			</div>
		</article>
		<?php
	}
	echo '</div>';
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'oss_blog_grid', 'oss_child_blog_grid_shortcode' );

/**
 * Contact info block — reads Customizer settings so it stays editable
 * from Appearance → Customize → Open Space Sanctuary Settings.
 */
function oss_child_contact_info_shortcode() {
	ob_start();
	?>
	<div class="oss-contact-info">
		<dl>
			<dt><?php esc_html_e( 'Address', 'astra-child' ); ?></dt>
			<dd><?php echo esc_html( get_theme_mod( 'oss_address', '' ) ); ?></dd>
			<dt><?php esc_html_e( 'Phone', 'astra-child' ); ?></dt>
			<dd><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', get_theme_mod( 'oss_phone', '' ) ) ); ?>"><?php echo esc_html( get_theme_mod( 'oss_phone', '' ) ); ?></a></dd>
			<dt><?php esc_html_e( 'Email', 'astra-child' ); ?></dt>
			<dd><a href="mailto:<?php echo esc_attr( get_theme_mod( 'oss_email', '' ) ); ?>"><?php echo esc_html( get_theme_mod( 'oss_email', '' ) ); ?></a></dd>
			<dt><?php esc_html_e( 'Hours', 'astra-child' ); ?></dt>
			<dd><?php echo esc_html( get_theme_mod( 'oss_hours', '' ) ); ?></dd>
		</dl>
		<?php oss_social_links(); ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'oss_contact_info', 'oss_child_contact_info_shortcode' );

/**
 * Contact form container. Uses whichever form plugin is installed;
 * falls back to a styled, editable placeholder if none is active yet.
 * Replace by editing the Elementor "Shortcode" widget on the Contact page,
 * or simply install a form plugin and this will pick it up automatically.
 */
function oss_child_contact_form_shortcode() {
	if ( shortcode_exists( 'contact-form-7' ) ) {
		return do_shortcode( '[contact-form-7 id="" title="Contact form"]' );
	}
	if ( function_exists( 'wpforms' ) ) {
		return '<p class="oss-empty-state">' . esc_html__( 'WPForms is active — replace this widget with the [wpforms id="X"] shortcode for your form.', 'astra-child' ) . '</p>';
	}
	if ( class_exists( 'GFForms' ) ) {
		return '<p class="oss-empty-state">' . esc_html__( 'Gravity Forms is active — replace this widget with the [gravityform id="X"] shortcode for your form.', 'astra-child' ) . '</p>';
	}
	ob_start();
	?>
	<div class="oss-contact-form-panel__placeholder">
		<p><strong><?php esc_html_e( 'Contact form goes here.', 'astra-child' ); ?></strong></p>
		<p><?php esc_html_e( 'Edit this page with Elementor and replace this Shortcode widget with an Elementor Form widget, or install Contact Form 7 / WPForms / Gravity Forms and drop in their shortcode. No theme code changes needed.', 'astra-child' ); ?></p>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'oss_contact_form', 'oss_child_contact_form_shortcode' );
