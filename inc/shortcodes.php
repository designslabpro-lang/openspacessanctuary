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
 * from Appearance → Customize → Open Spaces Sanctuary Settings.
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
		<p><?php esc_html_e( 'Install Contact Form 7, WPForms, or Gravity Forms and this space fills in automatically — no page builder or theme code changes needed.', 'astra-child' ); ?></p>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'oss_contact_form', 'oss_child_contact_form_shortcode' );

/**
 * Newsletter signup. Uses a mailing-list plugin's shortcode if one is
 * installed (Mailchimp for WP, Newsletter, etc.); otherwise shows a
 * styled placeholder so the footer layout is complete pending a real
 * integration. No email addresses are ever collected by theme code.
 */
function oss_child_newsletter_signup_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'button'    => 'Subscribe',
		'show_name' => '0',
	), $atts, 'oss_newsletter_signup' );

	if ( shortcode_exists( 'mc4wp_form' ) ) {
		return do_shortcode( '[mc4wp_form]' );
	}
	if ( shortcode_exists( 'newsletter' ) ) {
		return do_shortcode( '[newsletter]' );
	}
	ob_start();
	?>
	<form class="oss-newsletter-form" onsubmit="return false;">
		<?php if ( '1' === $atts['show_name'] ) : ?>
			<label class="screen-reader-text" for="oss-newsletter-name"><?php esc_html_e( 'First name', 'astra-child' ); ?></label>
			<input type="text" id="oss-newsletter-name" placeholder="<?php esc_attr_e( 'First Name', 'astra-child' ); ?>" required>
		<?php endif; ?>
		<label class="screen-reader-text" for="oss-newsletter-email"><?php esc_html_e( 'Email address', 'astra-child' ); ?></label>
		<input type="email" id="oss-newsletter-email" placeholder="<?php esc_attr_e( 'Email Address', 'astra-child' ); ?>" required>
		<button type="submit" class="oss-btn oss-btn--on-sage oss-btn--sm"><?php echo esc_html( $atts['button'] ); ?></button>
	</form>
	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<p class="oss-newsletter-form__note"><?php esc_html_e( 'Admin note: connect a mailing list plugin (Mailchimp for WP, Newsletter, etc.) to activate this form — no theme changes required. Only visible to admins.', 'astra-child' ); ?></p>
	<?php endif; ?>
	<?php
	return ob_get_clean();
}
add_shortcode( 'oss_newsletter_signup', 'oss_child_newsletter_signup_shortcode' );

/**
 * Who We Serve — icon card grid. Content is fixed (per the source brief,
 * this list must not be edited/invented), but rendered as real markup
 * instead of a plain text blob so it can carry a distinct icon per
 * audience and a richer card treatment.
 */
function oss_child_who_we_serve_shortcode() {
	$icons = array(
		'shield'  => '<path d="M12 3l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V6l7-3z"/><path d="M9 12l2 2 4-4"/>',
		'star'    => '<path d="M12 3l2.6 5.6 6.1.7-4.5 4.2 1.2 6-5.4-3-5.4 3 1.2-6-4.5-4.2 6.1-.7z"/>',
		'heart'   => '<path d="M12 20s-7-4.4-9.5-8.8C.8 8 2 4.5 5.5 4c2-.3 3.7.8 4.5 2.3C10.8 4.8 12.5 3.7 14.5 4 18 4.5 19.2 8 17.5 11.2 15 15.6 12 20 12 20z"/>',
		'hands'   => '<path d="M8 13V6a1.5 1.5 0 0 1 3 0v5"/><path d="M11 11V4.5a1.5 1.5 0 0 1 3 0V11"/><path d="M14 11.5V6a1.5 1.5 0 0 1 3 0v8c0 3.3-2.7 6-6 6h-1c-2 0-3.3-.7-4.5-2L4 15.5c-.8-.8-.8-2 0-2.7.7-.7 1.8-.7 2.5 0L8 14"/>',
		'ribbon'  => '<circle cx="12" cy="7" r="4"/><path d="M9.5 10.5L6 21l6-3 6 3-3.5-10.5"/>',
		'bloom'   => '<path d="M12 12c0-3 1.5-5 4-6-1 2.5-1 4.5 0 6-1.5 1-3.5 1-4 0z"/><path d="M12 12c0-3-1.5-5-4-6 1 2.5 1 4.5 0 6 1.5 1 3.5 1 4 0z"/><path d="M12 12c2.5 1.2 4 3 4 5.5-2.5-.3-4-1.5-4-3.5"/><path d="M12 12c-2.5 1.2-4 3-4 5.5 2.5-.3 4-1.5 4-3.5"/><circle cx="12" cy="12" r="1.4"/><path d="M12 17.5V21"/>',
		'compass' => '<circle cx="12" cy="12" r="9"/><path d="M15 9l-2 5-5 2 2-5z"/>',
	);

	$items = array(
		array( 'icon' => 'shield', 'label' => __( 'Veterans and Military Families', 'astra-child' ) ),
		array( 'icon' => 'star', 'label' => __( 'First Responders', 'astra-child' ) ),
		array( 'icon' => 'heart', 'label' => __( 'Survivors of Trauma', 'astra-child' ) ),
		array( 'icon' => 'hands', 'label' => __( 'Caregivers', 'astra-child' ) ),
		array( 'icon' => 'ribbon', 'label' => __( 'Cancer Patients and Individuals Facing Health Challenges', 'astra-child' ) ),
		array( 'icon' => 'bloom', 'label' => __( 'Women Seeking Empowerment', 'astra-child' ) ),
		array( 'icon' => 'compass', 'label' => __( 'Individuals, Families, and Professionals looking to grow, heal, and reconnect', 'astra-child' ) ),
	);

	ob_start();
	?>
	<div class="oss-who-grid">
		<?php foreach ( $items as $item ) : ?>
			<div class="oss-who-card">
				<span class="oss-who-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><?php echo $icons[ $item['icon'] ]; // phpcs:ignore -- static, trusted inline SVG paths defined above. ?></svg></span>
				<span class="oss-who-card__label"><?php echo esc_html( $item['label'] ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'oss_who_we_serve', 'oss_child_who_we_serve_shortcode' );

/**
 * Stories of Hope — testimonial carousel. Wording is fixed (per source
 * brief); this only changes presentation from a static 3-column row to
 * a swipeable/clickable carousel with dot navigation. Degrades to a
 * horizontally scrollable row if JavaScript is unavailable.
 */
function oss_child_testimonials_shortcode() {
	$testimonials = array(
		array(
			'quote' => __( "Coming to the ranch was truly a game changer for me... After transitioning out of the military, I've often felt disconnected. Today I left with a sense of lightness and hope.", 'astra-child' ),
			'name'  => __( 'Doug B.', 'astra-child' ),
		),
		array(
			'quote' => __( "Donna truly 'gets it.' Every lesson leaves me feeling seen and heard. She has helped me better understand myself while creating a safe, supportive environment for healing.", 'astra-child' ),
			'name'  => __( 'Linda H.', 'astra-child' ),
		),
		array(
			'quote' => __( 'Today I realized I need to accept my mom where she is each day rather than holding onto expectations. That breakthrough changed everything for me.', 'astra-child' ),
			'name'  => __( 'Alex R.', 'astra-child' ),
		),
	);

	ob_start();
	?>
	<div class="oss-testimonial-carousel" data-oss-carousel>
		<div class="oss-testimonial-carousel__track">
			<?php foreach ( $testimonials as $t ) : ?>
				<div class="oss-testimonial-carousel__slide">
					<div class="oss-quote oss-on-dark oss-quote--card">
						<blockquote><?php echo esc_html( $t['quote'] ); ?></blockquote>
						<cite>&mdash; <?php echo esc_html( $t['name'] ); ?></cite>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="oss-testimonial-carousel__controls">
			<button type="button" class="oss-testimonial-carousel__arrow" data-oss-carousel-prev aria-label="<?php esc_attr_e( 'Previous story', 'astra-child' ); ?>">&larr;</button>
			<div class="oss-testimonial-carousel__dots" data-oss-carousel-dots></div>
			<button type="button" class="oss-testimonial-carousel__arrow" data-oss-carousel-next aria-label="<?php esc_attr_e( 'Next story', 'astra-child' ); ?>">&rarr;</button>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'oss_testimonials', 'oss_child_testimonials_shortcode' );
