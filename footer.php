<?php
/**
 * Open Space Sanctuary — custom footer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
		</div><!-- .ast-container -->
		</div><!-- #content -->

		<footer id="colophon" class="oss-footer" role="contentinfo">

			<div class="oss-footer__newsletter">
				<div class="oss-container oss-footer__newsletter-inner">
					<div class="oss-footer__newsletter-copy">
						<h3><?php esc_html_e( 'Newsletter Signup', 'astra-child' ); ?></h3>
						<p><?php esc_html_e( 'Stories, events, and updates from the sanctuary — no more than once a month.', 'astra-child' ); ?></p>
					</div>
					<div class="oss-footer__newsletter-form">
						<?php echo do_shortcode( '[oss_newsletter_signup]' ); ?>
					</div>
				</div>
			</div>

			<div class="oss-container">
				<div class="oss-footer__grid">
					<div class="oss-footer__brand">
						<?php if ( has_custom_logo() ) : ?>
							<?php the_custom_logo(); ?>
						<?php else : ?>
							<p class="oss-footer__site-name"><?php bloginfo( 'name' ); ?></p>
						<?php endif; ?>
						<p class="oss-footer__tagline"><?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Healing Begins Here', 'astra-child' ) ); ?></p>

						<h3><?php esc_html_e( 'Mission Statement', 'astra-child' ); ?></h3>
						<p class="oss-footer__mission">
							<?php
							$mission = get_theme_mod( 'oss_mission_statement', '' );
							echo esc_html( $mission ? $mission : __( 'Open Space Sanctuary creates a safe, natural environment where people of all backgrounds experience healing through connection with horses.', 'astra-child' ) );
							?>
						</p>

						<p class="oss-footer__nonprofit"><?php echo esc_html( get_theme_mod( 'oss_nonprofit_info', __( 'Open Space Sanctuary is a 501(c)(3) nonprofit organization. EIN: 00-0000000. All donations are tax-deductible.', 'astra-child' ) ) ); ?></p>
					</div>

					<div class="oss-footer__col">
						<h3><?php esc_html_e( 'Quick Links', 'astra-child' ); ?></h3>
						<?php
						wp_nav_menu( array(
							'theme_location' => 'footer',
							'container'      => false,
							'menu_class'     => '',
							'fallback_cb'    => 'oss_child_default_menu_fallback',
						) );
						?>
					</div>

					<div class="oss-footer__col">
						<h3><?php esc_html_e( 'Get Involved', 'astra-child' ); ?></h3>
						<ul>
							<li><a href="<?php echo esc_url( get_post_type_archive_link( 'oss_program' ) ); ?>"><?php esc_html_e( 'Programs', 'astra-child' ); ?></a></li>
							<li><a href="<?php echo esc_url( get_theme_mod( 'oss_donate_url', home_url( '/contact/' ) ) ); ?>"><?php esc_html_e( 'Donate', 'astra-child' ); ?></a></li>
							<li><a href="<?php echo esc_url( get_theme_mod( 'oss_volunteer_url', home_url( '/contact/' ) ) ); ?>"><?php esc_html_e( 'Volunteer', 'astra-child' ); ?></a></li>
						</ul>

						<?php if ( oss_has_social_links() ) : ?>
							<h3 style="margin-top:2rem;"><?php esc_html_e( 'Social Media', 'astra-child' ); ?></h3>
							<?php oss_social_links(); ?>
						<?php endif; ?>
					</div>

					<div class="oss-footer__col">
						<h3><?php esc_html_e( 'Contact', 'astra-child' ); ?></h3>
						<ul>
							<li><?php echo esc_html( get_theme_mod( 'oss_address', '' ) ); ?></li>
							<li><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', get_theme_mod( 'oss_phone', '' ) ) ); ?>"><?php echo esc_html( get_theme_mod( 'oss_phone', '' ) ); ?></a></li>
							<li><a href="mailto:<?php echo esc_attr( get_theme_mod( 'oss_email', '' ) ); ?>"><?php echo esc_html( get_theme_mod( 'oss_email', '' ) ); ?></a></li>
							<li><?php echo esc_html( get_theme_mod( 'oss_hours', '' ) ); ?></li>
						</ul>
					</div>
				</div>

				<div class="oss-footer__bottom">
					<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'astra-child' ); ?></p>
					<ul class="oss-footer__bottom-links">
						<?php $privacy_id = get_option( 'wp_page_for_privacy_policy' ); ?>
						<?php if ( $privacy_id ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $privacy_id ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'astra-child' ); ?></a></li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</footer>

	</div><!-- #page -->
<?php wp_footer(); ?>
</body>
</html>
