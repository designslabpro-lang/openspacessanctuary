<?php
/**
 * Homepage section registry + ordered renderer.
 *
 * The V2 homepage is composed of a fixed set of bespoke sections. This file
 * lets the client reorder them (Appearance → Homepage Content, drag or Up/Down)
 * without touching their content: the chosen order is stored in
 * oss_home_content['section_order'] and template-home-v2.php renders each
 * section by key in that order.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The homepage sections, in their default order. The 'id' matches each admin
 * panel's <details> id so the editor UI and the front-end stay in sync.
 */
function oss_home_sections_list() {
	return array(
		'hero'     => array( 'id' => 'oss-section-hero', 'label' => __( 'Hero', 'astra-child' ) ),
		'power'    => array( 'id' => 'oss-section-the-healing-power-of-horses', 'label' => __( 'The Healing Power of Horses', 'astra-child' ) ),
		'serve'    => array( 'id' => 'oss-section-who-we-serve', 'label' => __( 'Who We Serve', 'astra-child' ) ),
		'programs' => array( 'id' => 'oss-section-our-programs', 'label' => __( 'Our Programs', 'astra-child' ) ),
		'horses'   => array( 'id' => 'oss-section-meet-our-horses', 'label' => __( 'Meet Our Horses', 'astra-child' ) ),
		'founder'  => array( 'id' => 'oss-section-our-founder', 'label' => __( 'Our Founder', 'astra-child' ) ),
		'stories'  => array( 'id' => 'oss-section-stories-of-hope', 'label' => __( 'Stories of Hope', 'astra-child' ) ),
		'donate'   => array( 'id' => 'oss-section-help-us-change-lives', 'label' => __( 'Help Us Change Lives', 'astra-child' ) ),
		'connect'  => array( 'id' => 'oss-section-stay-connected', 'label' => __( 'Stay Connected', 'astra-child' ) ),
		'final'    => array( 'id' => 'oss-section-final-cta', 'label' => __( 'Final CTA', 'astra-child' ) ),
	);
}

function oss_home_default_order() {
	return array_keys( oss_home_sections_list() );
}

/**
 * Sanitize a single homepage field value by its key. Shared by the duplicate
 * store so a copied section's content is cleaned with the same rules as the
 * originals.
 */
function oss_home_sanitize_value( $key, $value ) {
	$kses = array( 'hero_body', 'power_body1', 'power_body2', 'power_quote', 'serve_intro', 'programs_intro', 'horses_body', 'founder_body', 'donate_body', 'connect_body', 'final_body' );

	if ( in_array( $key, $kses, true ) ) {
		return wp_kses_post( wp_unslash( $value ) );
	}
	if ( 'serve_items' === $key ) {
		$items = array();
		foreach ( (array) $value as $row ) {
			$items[] = array(
				'icon'  => isset( $row['icon'] ) ? sanitize_key( $row['icon'] ) : 'compass',
				'label' => isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '',
			);
		}
		return $items;
	}
	if ( 'testimonials' === $key ) {
		$rows = array();
		foreach ( (array) $value as $row ) {
			$q = isset( $row['quote'] ) ? sanitize_textarea_field( wp_unslash( $row['quote'] ) ) : '';
			$n = isset( $row['name'] ) ? sanitize_text_field( wp_unslash( $row['name'] ) ) : '';
			if ( '' === $q && '' === $n ) {
				continue;
			}
			$rows[] = array( 'quote' => $q, 'name' => $n, 'image_id' => isset( $row['image_id'] ) ? absint( $row['image_id'] ) : 0 );
		}
		return $rows;
	}
	if ( 'hero_slide_ids' === $key ) {
		$ids = array();
		foreach ( (array) $value as $id ) {
			$id = absint( $id );
			if ( $id ) {
				$ids[] = $id;
			}
		}
		return $ids;
	}
	if ( false !== strpos( $key, '_image_id' ) ) {
		return absint( $value );
	}
	if ( false !== strpos( $key, '_url' ) ) {
		return esc_url_raw( trim( (string) $value ) );
	}
	if ( '_layout' === substr( $key, -7 ) ) {
		return in_array( $value, array( '1', '2' ), true ) ? $value : '1';
	}
	if ( is_array( $value ) ) {
		return array_map( 'sanitize_text_field', $value );
	}
	// Caption keeps line breaks; everything else is single-line.
	if ( false !== strpos( (string) $value, "\n" ) ) {
		return sanitize_textarea_field( wp_unslash( $value ) );
	}
	return sanitize_text_field( wp_unslash( $value ) );
}

/**
 * Sanitize the whole duplicate store: valid type + cleaned per-field overrides.
 */
function oss_home_sanitize_dups( $raw ) {
	$valid_types = array_keys( oss_home_sections_list() );
	$existing    = oss_home_dups(); // Current stored duplicates (pre-save).
	$out         = array();
	foreach ( (array) $raw as $id => $inst ) {
		if ( ! is_array( $inst ) ) {
			continue;
		}
		$id = sanitize_key( $id );
		if ( '' === $id ) {
			continue;
		}
		$type = isset( $inst['type'] ) ? sanitize_key( $inst['type'] ) : '';
		if ( ! in_array( $type, $valid_types, true ) ) {
			continue;
		}
		// Start from the stored copy so fields not present in this submission
		// (e.g. testimonials/slideshow lists that the editor doesn't expose)
		// are preserved rather than dropped.
		$clean = ( isset( $existing[ $id ] ) && is_array( $existing[ $id ] ) ) ? $existing[ $id ] : array();
		$clean['type'] = $type;
		foreach ( $inst as $k => $v ) {
			if ( 'type' === $k ) {
				continue;
			}
			$k = sanitize_key( $k );
			if ( '' === $k ) {
				continue;
			}
			$clean[ $k ] = oss_home_sanitize_value( $k, $v );
		}
		$out[ $id ] = $clean;
	}
	return $out;
}

/**
 * Duplicate section instances: id => array( 'type' => <section key>, ...overrides ).
 */
function oss_home_dups() {
	$dups = oss_home_get( 'dups' );
	return is_array( $dups ) ? $dups : array();
}

/**
 * Built-in section keys the client hid from the homepage (content kept).
 */
function oss_home_removed() {
	$r = oss_home_get( 'removed' );
	if ( ! is_array( $r ) ) {
		return array();
	}
	return array_values( array_intersect( $r, oss_home_default_order() ) );
}

/**
 * The saved section order, validated. Entries are either an original section
 * key or a duplicate-instance id. Unknown entries are dropped; any missing
 * originals and any not-yet-placed duplicates are appended, so a section can
 * never silently disappear.
 */
function oss_home_section_order() {
	$default = oss_home_default_order();
	$dupids  = array_keys( oss_home_dups() );
	$removed = oss_home_removed();
	$valid   = array_merge( $default, $dupids );
	$saved   = oss_home_get( 'section_order' );
	if ( ! is_array( $saved ) || ! $saved ) {
		$saved = $default;
	}
	$order = array();
	foreach ( $saved as $k ) {
		if ( in_array( $k, $removed, true ) ) {
			continue;
		}
		if ( in_array( $k, $valid, true ) && ! in_array( $k, $order, true ) ) {
			$order[] = $k;
		}
	}
	foreach ( $default as $k ) {
		if ( ! in_array( $k, $order, true ) && ! in_array( $k, $removed, true ) ) {
			$order[] = $k;
		}
	}
	foreach ( $dupids as $k ) {
		if ( ! in_array( $k, $order, true ) ) {
			$order[] = $k;
		}
	}
	return $order;
}

/**
 * Resolve a section-order entry to its render arguments. Returns array(
 * section_key, context ) where context is null for an original or the
 * duplicate's stored content array for a duplicate instance.
 */
function oss_home_resolve_entry( $entry ) {
	$dups = oss_home_dups();
	if ( isset( $dups[ $entry ] ) && is_array( $dups[ $entry ] ) ) {
		$type = isset( $dups[ $entry ]['type'] ) ? $dups[ $entry ]['type'] : '';
		if ( array_key_exists( $type, oss_home_sections_list() ) ) {
			return array( $type, $dups[ $entry ] );
		}
		return array( '', null );
	}
	return array( $entry, null );
}

/**
 * Render one homepage section by key. Markup moved verbatim from the template.
 */
function oss_home_render_section( $key, $icons = array(), $hero_slide_ids = array(), $ctx = null ) {
	// For a duplicate instance, its own slide list overrides the page default.
	if ( is_array( $ctx ) && array_key_exists( 'hero_slide_ids', $ctx ) ) {
		$hero_slide_ids = array_values( array_filter( array_map( 'intval', (array) $ctx['hero_slide_ids'] ) ) );
	}
	switch ( $key ) {

		case 'hero':
			?>
			<header class="oss2-hero">
				<div class="oss-container">
					<div class="oss2-hero__panel">
						<div class="oss2-hero__panel-inner">
						<span class="oss-eyebrow oss2-hero__eyebrow"><?php echo esc_html( oss_home_f( $ctx,  'hero_eyebrow' ) ); ?></span>
						<h1><?php echo esc_html( oss_home_f( $ctx,  'hero_heading' ) ); ?></h1>
						<div class="oss2-hero__body">
							<?php echo wpautop( wp_kses_post( oss_home_f( $ctx,  'hero_body' ) ) ); ?>
						</div>
						<div class="oss2-hero__actions">
							<a class="oss-btn oss-btn--on-sage" href="<?php echo esc_url( oss_home_f( $ctx,  'hero_btn1_url' ) ); ?>"><?php echo esc_html( oss_home_f( $ctx,  'hero_btn1_text' ) ); ?></a>
							<a class="oss-btn oss-btn--secondary" href="<?php echo esc_url( oss_home_f( $ctx,  'hero_btn2_url' ) ); ?>" style="border-color:var(--oss-bg);color:var(--oss-bg);"><?php echo esc_html( oss_home_f( $ctx,  'hero_btn2_text' ) ); ?></a>
						</div>
						<?php if ( oss_home_f( $ctx,  'hero_trust' ) ) : ?><p class="oss2-hero__trust"><?php echo esc_html( oss_home_f( $ctx,  'hero_trust' ) ); ?></p><?php endif; ?>
						</div>
					</div>
					<div class="oss2-hero__photo">
						<div class="oss-hero__slides" data-oss-hero-slides data-oss-autoplay="6500">
							<?php foreach ( $hero_slide_ids as $i => $slide_id ) :
								$slide_url = wp_get_attachment_image_url( $slide_id, 'full' );
								if ( ! $slide_url ) {
									continue;
								}
								?>
								<div class="oss-hero__slide<?php echo 0 === $i ? ' is-active' : ''; ?>" style="background-image:url('<?php echo esc_url( $slide_url ); ?>');"></div>
							<?php endforeach; ?>
						</div>
						<span class="oss2-hero__badge">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s7-7.4 7-12.5A7 7 0 0 0 5 9.5C5 14.6 12 22 12 22z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
							<?php esc_html_e( 'Ocala, Florida', 'astra-child' ); ?>
						</span>
						<?php if ( count( $hero_slide_ids ) > 1 ) : ?>
							<div class="oss2-hero__dots">
								<?php foreach ( $hero_slide_ids as $i => $slide_id ) : ?>
									<button type="button" class="oss2-hero__dot<?php echo 0 === $i ? ' is-active' : ''; ?>" data-oss-carousel-dot aria-label="<?php echo esc_attr( sprintf( __( 'Go to photo %d', 'astra-child' ), $i + 1 ) ); ?>"></button>
								<?php endforeach; ?>
							</div>
							<div class="oss2-hero__nav">
								<button type="button" class="oss2-hero__nav-btn" data-oss-carousel-prev aria-label="<?php esc_attr_e( 'Previous photo', 'astra-child' ); ?>">&larr;</button>
								<button type="button" class="oss2-hero__nav-btn" data-oss-carousel-next aria-label="<?php esc_attr_e( 'Next photo', 'astra-child' ); ?>">&rarr;</button>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</header>
			<?php
			break;

		case 'power':
			?>
			<section class="oss-section oss-section--cream">
				<div class="oss-container">
					<?php $power_style2 = ( '2' === (string) oss_home_f( $ctx,  'power_layout' ) ); ?>
					<div class="oss2-feature<?php echo $power_style2 ? ' oss2-feature--textlead' : ''; ?>">
						<div class="oss2-feature__media">
							<?php echo oss_home_img_ctx( $ctx,  'power_image_id', 'large', 'Open Spaces Sanctuary' ); ?>
							<blockquote class="oss2-feature__quote">&ldquo;<?php echo oss_rich_inline( oss_home_f( $ctx,  'power_quote' ) ); ?>&rdquo;</blockquote>
						</div>
						<div>
							<span class="oss-eyebrow"><?php echo esc_html( oss_home_f( $ctx,  'power_eyebrow' ) ); ?></span>
							<h2><?php echo esc_html( oss_home_f( $ctx,  'power_heading' ) ); ?></h2>
							<?php
							echo oss_rich( oss_home_f( $ctx,  'power_body1' ) );
							echo oss_rich( oss_home_f( $ctx,  'power_body2' ) );
							?>
						</div>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'serve':
			$serve_style2 = ( '2' === (string) oss_home_f( $ctx,  'serve_layout' ) );
			?>
			<section class="oss-section oss2-serve-section<?php echo $serve_style2 ? ' oss2-serve-section--alt' : ''; ?>"<?php echo $serve_style2 ? '' : ' style="text-align:center;"'; ?>>
				<div class="oss-container">
					<div class="oss-section-heading<?php echo $serve_style2 ? '' : ' oss-section-heading--center'; ?>">
						<span class="oss-eyebrow"><?php esc_html_e( 'Who We Serve', 'astra-child' ); ?></span>
						<h2><?php echo esc_html( oss_home_f( $ctx,  'serve_heading' ) ); ?></h2>
						<?php echo oss_rich( oss_home_f( $ctx,  'serve_intro' ) ); ?>
					</div>
					<div class="oss2-serve-grid<?php echo $serve_style2 ? ' oss2-serve-grid--rows' : ''; ?>">
						<?php foreach ( oss_home_f( $ctx,  'serve_items' ) as $i => $item ) : ?>
							<div class="oss2-serve-card oss2-serve-card--c<?php echo esc_attr( ( $i % 5 ) + 1 ); ?>">
								<span class="oss2-serve-card__icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><?php echo isset( $icons[ $item['icon'] ] ) ? $icons[ $item['icon'] ] : $icons['compass']; // phpcs:ignore -- trusted static SVG path library. ?></svg>
								</span>
								<span class="oss2-serve-card__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
								<span class="oss2-serve-card__label"><?php echo esc_html( $item['label'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
					<p><?php echo esc_html( oss_home_f( $ctx,  'serve_closing' ) ); ?></p>
				</div>
			</section>
			<?php
			break;

		case 'programs':
			$programs_style2 = ( '2' === (string) oss_home_f( $ctx,  'programs_layout' ) );
			?>
			<section class="oss-section oss-section--cream oss2-programs-section<?php echo $programs_style2 ? ' oss2-programs-section--alt' : ''; ?>">
				<div class="oss-container">
					<div class="oss-section-heading<?php echo $programs_style2 ? '' : ' oss-section-heading--center'; ?>"<?php echo $programs_style2 ? '' : ' style="text-align:center;margin-left:auto;margin-right:auto;"'; ?>>
						<span class="oss-eyebrow"><?php esc_html_e( 'What We Offer', 'astra-child' ); ?></span>
						<h2><?php echo esc_html( oss_home_f( $ctx,  'programs_heading' ) ); ?></h2>
						<?php echo oss_rich( oss_home_f( $ctx,  'programs_intro' ) ); ?>
					</div>
					<?php echo do_shortcode( '[oss_programs_grid limit="4"]' ); ?>
					<p style="text-align:<?php echo $programs_style2 ? 'left' : 'center'; ?>;margin-top:2.5rem;"><a class="oss-btn oss-btn--secondary" href="<?php echo esc_url( home_url( '/programs/' ) ); ?>"><?php esc_html_e( 'View All Programs', 'astra-child' ); ?></a></p>
				</div>
			</section>
			<?php
			break;

		case 'horses':
			$horses_style2 = ( '2' === (string) oss_home_f( $ctx,  'horses_layout' ) );
			?>
			<section class="oss2-herd<?php echo $horses_style2 ? ' oss2-herd--alt' : ''; ?>" style="<?php $horses_bg = (int) oss_home_f( $ctx,  'horses_image_id' ); if ( $horses_bg ) { echo 'background-image:url(' . esc_url( wp_get_attachment_image_url( $horses_bg, 'full' ) ) . ');'; } ?>">
				<div class="oss-container oss2-herd__grid">
					<div class="oss2-herd__panel">
						<span class="oss-eyebrow"><?php esc_html_e( 'The Herd', 'astra-child' ); ?></span>
						<h2><?php echo esc_html( oss_home_f( $ctx,  'horses_heading' ) ); ?></h2>
						<?php echo oss_rich( oss_home_f( $ctx,  'horses_body' ) ); ?>
						<a class="oss-btn oss-btn--on-sage" href="<?php echo esc_url( home_url( '/meet-the-herd/' ) ); ?>"><?php echo esc_html( oss_home_f( $ctx,  'horses_sub' ) ); ?></a>
					</div>
					<div class="oss2-herd__spacer" aria-hidden="true"></div>
				</div>
			</section>
			<?php
			break;

		case 'founder':
			?>
			<section class="oss-section oss-section--white">
				<div class="oss-container">
					<?php $founder_style2 = ( '2' === (string) oss_home_f( $ctx,  'founder_layout' ) ); ?>
					<div class="oss2-founder<?php echo $founder_style2 ? ' oss2-founder--alt' : ''; ?>">
						<div class="oss2-founder__media">
							<?php echo oss_home_img_ctx( $ctx,  'founder_image_id', 'large', esc_attr( oss_home_f( $ctx,  'founder_name' ) ) ); ?>
						</div>
						<div>
							<span class="oss2-founder__mark" aria-hidden="true">&ldquo;</span>
							<span class="oss-eyebrow"><?php echo esc_html( oss_home_f( $ctx,  'founder_heading' ) ); ?></span>
							<h2><?php echo esc_html( oss_home_f( $ctx,  'founder_name' ) ); ?></h2>
							<?php echo oss_rich( oss_home_f( $ctx,  'founder_body' ) ); ?>
							<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php echo esc_html( oss_home_f( $ctx,  'founder_btn' ) ); ?></a>
						</div>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'stories':
			$stories_style2 = ( '2' === (string) oss_home_f( $ctx,  'stories_layout' ) );
			?>
			<section class="oss-section oss-section--sage oss2-stories<?php echo $stories_style2 ? ' oss2-stories--alt' : ''; ?>" style="text-align:center;">
				<span class="oss2-stories__circles oss2-stories__circles--tl" aria-hidden="true"><span></span><span></span><span></span></span>
				<span class="oss2-stories__circles oss2-stories__circles--br" aria-hidden="true"><span></span><span></span><span></span></span>
				<div class="oss-container">
					<span class="oss-eyebrow"><?php esc_html_e( 'Stories of Hope', 'astra-child' ); ?></span>
					<h2><?php echo esc_html( oss_home_f( $ctx,  'stories_heading' ) ); ?></h2>
					<div class="oss2-stories__grid">
						<?php foreach ( oss_home_f( $ctx,  'testimonials' ) as $story ) : ?>
							<div class="oss2-stories__card">
								<p class="oss2-stories__quote">&ldquo;<?php echo esc_html( $story['quote'] ); ?>&rdquo;</p>
								<div class="oss2-stories__who">
									<?php
									$story_img = ! empty( $story['image_id'] ) ? (int) $story['image_id'] : 0;
									if ( $story_img && wp_get_attachment_image_src( $story_img, 'thumbnail' ) ) {
										echo wp_get_attachment_image( $story_img, 'thumbnail', false, array( 'class' => 'oss2-stories__avatar', 'alt' => esc_attr( $story['name'] ) ) );
									}
									?>
									<p class="oss2-stories__name">&mdash; <?php echo esc_html( $story['name'] ); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'donate':
			$donate_style2 = ( '2' === (string) oss_home_f( $ctx,  'donate_layout' ) );
			?>
			<section class="oss-section oss-section--white oss2-give<?php echo $donate_style2 ? ' oss2-give--alt' : ''; ?>">
				<span class="oss2-give__circles oss2-give__circles--tl" aria-hidden="true"><span></span><span></span><span></span></span>
				<span class="oss2-give__circles oss2-give__circles--br" aria-hidden="true"><span></span><span></span><span></span></span>
				<div class="oss-container oss2-give__grid">
					<div class="oss2-give__media">
						<?php echo oss_home_img_ctx( $ctx,  'donate_image_id', 'large', esc_attr( oss_home_f( $ctx,  'donate_heading' ) ) ); ?>
					</div>
					<div class="oss2-give__text">
						<span class="oss-eyebrow"><?php esc_html_e( 'Support the Sanctuary', 'astra-child' ); ?></span>
						<h2><?php echo esc_html( oss_home_f( $ctx,  'donate_heading' ) ); ?></h2>
						<?php echo oss_rich( oss_home_f( $ctx,  'donate_body' ) ); ?>
						<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( oss_home_f( $ctx,  'donate_btn_url' ) ); ?>"><?php echo esc_html( oss_home_f( $ctx,  'donate_btn' ) ); ?></a>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'connect':
			?>
			<section class="oss-section oss-section--cream">
				<div class="oss-container">
					<?php $connect_style2 = ( '2' === (string) oss_home_f( $ctx,  'connect_layout' ) ); ?>
					<div class="oss2-connect<?php echo $connect_style2 ? ' oss2-connect--alt' : ''; ?>">
						<div class="oss2-connect__text">
							<span class="oss-eyebrow"><?php esc_html_e( 'Newsletter', 'astra-child' ); ?></span>
							<h2><?php echo esc_html( oss_home_f( $ctx,  'connect_heading' ) ); ?></h2>
							<?php echo oss_rich( oss_home_f( $ctx,  'connect_body' ) ); ?>
						</div>
						<div class="oss2-connect__form">
							<?php echo oss_connect_form_html(); ?>
						</div>
					</div>
				</div>
			</section>
			<?php
			break;

		case 'final':
			?>
			<section class="oss-section oss2-close-section">
				<span class="oss2-close__circles oss2-close__circles--tl" aria-hidden="true"><span></span><span></span><span></span></span>
				<span class="oss2-close__circles oss2-close__circles--br" aria-hidden="true"><span></span><span></span><span></span></span>
				<div class="oss-container">
					<?php $final_style2 = ( '2' === (string) oss_home_f( $ctx,  'final_layout' ) ); ?>
					<div class="oss2-close<?php echo $final_style2 ? ' oss2-close--alt' : ''; ?>">
						<div class="oss2-close__media">
							<?php echo oss_home_img_ctx( $ctx,  'final_image_id', 'large', esc_attr( oss_home_f( $ctx,  'final_heading' ) ) ); ?>
						</div>
						<div class="oss2-close__text">
							<span class="oss-eyebrow"><?php esc_html_e( 'Welcome', 'astra-child' ); ?></span>
							<h2><?php echo esc_html( oss_home_f( $ctx,  'final_heading' ) ); ?></h2>
							<div class="oss-divider"></div>
							<?php echo oss_rich( oss_home_f( $ctx,  'final_body' ) ); ?>
							<p class="oss2-close__sub"><?php echo esc_html( oss_home_f( $ctx,  'final_sub' ) ); ?></p>
							<div class="oss2-close__actions">
								<a class="oss-btn oss-btn--primary" href="<?php echo esc_url( oss_home_f( $ctx,  'final_btn1_url' ) ); ?>"><?php echo esc_html( oss_home_f( $ctx,  'final_btn1_text' ) ); ?></a>
								<a class="oss-btn oss-btn--secondary" href="<?php echo esc_url( oss_home_f( $ctx,  'final_btn2_url' ) ); ?>"><?php echo esc_html( oss_home_f( $ctx,  'final_btn2_text' ) ); ?></a>
							</div>
						</div>
					</div>
				</div>
			</section>
			<?php
			break;
	}
}
