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
			<div class="oss-container">
				<div class="oss-footer__grid">
					<div class="oss-footer__brand">
						<?php if ( has_custom_logo() ) : ?>
							<?php the_custom_logo(); ?>
						<?php else : ?>
							<p class="oss-footer__site-name"><?php bloginfo( 'name' ); ?></p>
						<?php endif; ?>
						<p class="oss-footer__tagline"><?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Healing Begins Here', 'astra-child' ) ); ?></p>
					</div>

					<div class="oss-footer__col">
						<?php if ( is_active_sidebar( 'oss-footer-2' ) ) : ?>
							<?php dynamic_sidebar( 'oss-footer-2' ); ?>
						<?php else : ?>
							<h3><?php esc_html_e( 'Quick Links', 'astra-child' ); ?></h3>
							<?php
							wp_nav_menu( array(
								'theme_location' => 'footer',
								'container'      => false,
								'menu_class'     => '',
								'fallback_cb'    => 'oss_child_default_menu_fallback',
							) );
							?>
						<?php endif; ?>
					</div>

					<div class="oss-footer__col">
						<?php if ( is_active_sidebar( 'oss-footer-3' ) ) : ?>
							<?php dynamic_sidebar( 'oss-footer-3' ); ?>
						<?php else : ?>
							<h3><?php esc_html_e( 'Contact', 'astra-child' ); ?></h3>
							<ul>
								<li><?php echo esc_html( get_theme_mod( 'oss_address', '' ) ); ?></li>
								<li><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', get_theme_mod( 'oss_phone', '' ) ) ); ?>"><?php echo esc_html( get_theme_mod( 'oss_phone', '' ) ); ?></a></li>
								<li><a href="mailto:<?php echo esc_attr( get_theme_mod( 'oss_email', '' ) ); ?>"><?php echo esc_html( get_theme_mod( 'oss_email', '' ) ); ?></a></li>
								<li><?php echo esc_html( get_theme_mod( 'oss_hours', '' ) ); ?></li>
							</ul>
						<?php endif; ?>
					</div>

					<div class="oss-footer__col">
						<?php if ( is_active_sidebar( 'oss-footer-4' ) ) : ?>
							<?php dynamic_sidebar( 'oss-footer-4' ); ?>
						<?php else : ?>
							<h3><?php esc_html_e( 'Follow', 'astra-child' ); ?></h3>
							<?php oss_social_links(); ?>
						<?php endif; ?>
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
