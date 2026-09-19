<?php
/**
 * Astra Child Theme functions — Open Spaces Sanctuary
 */

defined( 'ABSPATH' ) || exit;

define( 'OSS_CHILD_VERSION', '1.20.0' );
define( 'OSS_CHILD_DIR', get_stylesheet_directory() );
define( 'OSS_CHILD_URI', get_stylesheet_directory_uri() );

require_once OSS_CHILD_DIR . '/inc/setup.php';
require_once OSS_CHILD_DIR . '/inc/enqueue.php';
require_once OSS_CHILD_DIR . '/inc/customizer.php';
require_once OSS_CHILD_DIR . '/inc/elementor.php';
require_once OSS_CHILD_DIR . '/inc/cpt-events.php';
require_once OSS_CHILD_DIR . '/inc/cpt-programs.php';
require_once OSS_CHILD_DIR . '/inc/shortcodes.php';
require_once OSS_CHILD_DIR . '/inc/helpers.php';
require_once OSS_CHILD_DIR . '/inc/homepage-content.php';
require_once OSS_CHILD_DIR . '/inc/homepage-content-admin.php';
require_once OSS_CHILD_DIR . '/inc/about-content.php';
require_once OSS_CHILD_DIR . '/inc/about-content-admin.php';
require_once OSS_CHILD_DIR . '/inc/contact-content.php';
require_once OSS_CHILD_DIR . '/inc/contact-content-admin.php';
require_once OSS_CHILD_DIR . '/inc/v2-helpers.php';
require_once OSS_CHILD_DIR . '/inc/events-content.php';
require_once OSS_CHILD_DIR . '/inc/events-content-admin.php';
require_once OSS_CHILD_DIR . '/inc/live-editor-security.php';
require_once OSS_CHILD_DIR . '/inc/live-editor-render.php';
require_once OSS_CHILD_DIR . '/inc/live-editor-api.php';
require_once OSS_CHILD_DIR . '/inc/live-editor.php';
