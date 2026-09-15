<?php
/**
 * Open Space Sanctuary — custom header.
 * Mirrors Astra's head/body scaffold (so astra_* hooks + body_class still fire)
 * but renders a fully custom, brand-designed navigation bar.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<?php astra_html_before(); ?>
<html <?php language_attributes(); ?>>
<head>
<?php astra_head_top(); ?>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
<?php astra_head_bottom(); ?>
</head>

<body <?php astra_schema_body(); ?> <?php body_class(); ?>>
<?php astra_body_top(); ?>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'astra-child' ); ?></a>

<div id="page" class="hfeed site">

	<?php
	$oss_is_front = is_front_page();
	$oss_header_class = 'oss-header' . ( $oss_is_front ? ' is-transparent' : '' );
	?>
	<header id="masthead" class="<?php echo esc_attr( $oss_header_class ); ?>" role="banner">
		<div class="oss-container oss-header__inner">
			<div class="oss-header__brand">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<p class="site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
					</p>
					<?php if ( get_bloginfo( 'description' ) ) : ?>
						<span class="site-description"><?php bloginfo( 'description' ); ?></span>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<nav class="oss-header__nav" aria-label="<?php esc_attr_e( 'Primary Navigation', 'astra-child' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => '',
					'fallback_cb'    => 'oss_child_default_menu_fallback',
				) );
				?>
			</nav>

			<div class="oss-header__actions">
				<?php oss_header_cta(); ?>
				<button class="oss-menu-toggle" aria-label="<?php esc_attr_e( 'Open menu', 'astra-child' ); ?>" aria-expanded="false" aria-controls="oss-mobile-panel">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>
	</header>

	<div class="oss-mobile-overlay"></div>
	<div class="oss-mobile-panel" id="oss-mobile-panel">
		<button class="oss-mobile-panel__close" aria-label="<?php esc_attr_e( 'Close menu', 'astra-child' ); ?>">&times;</button>
		<nav aria-label="<?php esc_attr_e( 'Mobile Navigation', 'astra-child' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => '',
				'fallback_cb'    => 'oss_child_default_menu_fallback',
			) );
			?>
		</nav>
		<div style="margin-top:2rem;">
			<?php oss_header_cta(); ?>
		</div>
	</div>

	<div id="content" class="site-content">
		<div class="ast-container">
