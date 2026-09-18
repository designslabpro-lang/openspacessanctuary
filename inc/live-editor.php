<?php
/**
 * Live Page Builder — bootstrap.
 *
 * Wires up the "Edit with Live Builder" row action, the full-screen editor
 * shell (an admin screen wrapping the real page in an iframe canvas), the
 * canvas mode on the front end, and — critically — loads builder assets ONLY
 * for a permitted, logged-in editor. Regular visitors get plain HTML and no
 * builder JS/CSS at all.
 */

defined( 'ABSPATH' ) || exit;

/**
 * True when the current front-end request is the editor's iframe canvas.
 */
function oss_lpb_is_canvas() {
	return isset( $_GET['live-builder-canvas'] ) && is_singular( 'page' );
}

/**
 * The admin URL of the editor shell for a given page.
 */
function oss_lpb_editor_url( $post_id ) {
	return admin_url( 'admin.php?page=oss-live-builder&post=' . (int) $post_id );
}

/* -------------------------------------------------------------------- */
/* Pages list: "Edit with Live Builder" row action                       */
/* -------------------------------------------------------------------- */

add_filter( 'page_row_actions', 'oss_lpb_row_action', 10, 2 );
function oss_lpb_row_action( $actions, $post ) {
	if ( 'page' === $post->post_type && oss_lpb_user_can( $post->ID ) ) {
		$actions['oss_live_builder'] = '<a href="' . esc_url( oss_lpb_editor_url( $post->ID ) ) . '">' . esc_html__( 'Edit with Live Builder', 'astra-child' ) . '</a>';
	}
	return $actions;
}

/* Admin-bar shortcut when viewing a builder page on the front end. */
add_action( 'admin_bar_menu', 'oss_lpb_admin_bar', 90 );
function oss_lpb_admin_bar( $bar ) {
	if ( is_page() && ! oss_lpb_is_canvas() ) {
		$id = get_queried_object_id();
		if ( $id && oss_lpb_user_can( $id ) && is_page_template( 'page-templates/template-live-builder.php' ) ) {
			$bar->add_node( array(
				'id'    => 'oss-live-builder',
				'title' => '✎ ' . __( 'Edit with Live Builder', 'astra-child' ),
				'href'  => oss_lpb_editor_url( $id ),
			) );
		}
	}
}

/* -------------------------------------------------------------------- */
/* Editor shell (hidden admin page)                                      */
/* -------------------------------------------------------------------- */

add_action( 'admin_menu', 'oss_lpb_register_editor_page' );
function oss_lpb_register_editor_page() {
	add_submenu_page( null, __( 'Live Page Editor', 'astra-child' ), __( 'Live Page Editor', 'astra-child' ), oss_lpb_capability(), 'oss-live-builder', 'oss_lpb_render_editor_shell' );
}

function oss_lpb_render_editor_shell() {
	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
	if ( ! $post_id || ! oss_lpb_user_can( $post_id ) ) {
		wp_die( esc_html__( 'You cannot edit this page with the Live Builder.', 'astra-child' ) );
	}
	$post        = get_post( $post_id );
	$canvas_url  = add_query_arg( 'live-builder-canvas', '1', get_permalink( $post_id ) );
	$is_builder  = 'page-templates/template-live-builder.php' === get_page_template_slug( $post_id );
	?>
	<div id="oss-lpb-app" class="oss-lpb-app" data-post="<?php echo esc_attr( $post_id ); ?>">
		<header class="oss-lpb-topbar">
			<div class="oss-lpb-topbar__brand">
				<span class="oss-lpb-logo">✎</span>
				<span class="oss-lpb-title"><?php esc_html_e( 'Live Page Editor', 'astra-child' ); ?></span>
				<span class="oss-lpb-page"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
			</div>
			<div class="oss-lpb-topbar__devices" role="group" aria-label="<?php esc_attr_e( 'Preview device', 'astra-child' ); ?>">
				<button type="button" class="oss-lpb-device is-active" data-device="desktop" title="Desktop">🖥</button>
				<button type="button" class="oss-lpb-device" data-device="tablet" title="Tablet">▭</button>
				<button type="button" class="oss-lpb-device" data-device="mobile" title="Mobile">▯</button>
			</div>
			<div class="oss-lpb-topbar__actions">
				<button type="button" class="oss-lpb-btn-ghost" id="oss-lpb-undo" disabled title="<?php esc_attr_e( 'Undo', 'astra-child' ); ?>">↶</button>
				<button type="button" class="oss-lpb-btn-ghost" id="oss-lpb-redo" disabled title="<?php esc_attr_e( 'Redo', 'astra-child' ); ?>">↷</button>
				<a class="oss-lpb-btn-ghost" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Preview', 'astra-child' ); ?></a>
				<span class="oss-lpb-status" id="oss-lpb-status" aria-live="polite"></span>
				<button type="button" class="oss-lpb-btn-primary" id="oss-lpb-save"><?php esc_html_e( 'Save', 'astra-child' ); ?></button>
				<a class="oss-lpb-btn-ghost" href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>"><?php esc_html_e( 'Close', 'astra-child' ); ?></a>
			</div>
		</header>
		<div class="oss-lpb-body">
			<aside class="oss-lpb-sidebar" id="oss-lpb-sidebar">
				<nav class="oss-lpb-tabs">
					<button type="button" class="oss-lpb-tab is-active" data-tab="inspector"><?php esc_html_e( 'Element', 'astra-child' ); ?></button>
					<button type="button" class="oss-lpb-tab" data-tab="sections"><?php esc_html_e( 'Sections', 'astra-child' ); ?></button>
					<button type="button" class="oss-lpb-tab" data-tab="globals"><?php esc_html_e( 'Globals', 'astra-child' ); ?></button>
				</nav>
				<div class="oss-lpb-panel" id="oss-lpb-panel">
					<p class="oss-lpb-hint"><?php esc_html_e( 'Click an element on the page to select it.', 'astra-child' ); ?></p>
				</div>
			</aside>
			<main class="oss-lpb-canvas-wrap">
				<?php if ( ! $is_builder ) : ?>
					<div class="oss-lpb-notice"><?php esc_html_e( 'This page does not use the Live Builder template yet, so there are no builder elements to edit. Assign the "Live Builder" template to this page (Page → Attributes → Template) to build it here.', 'astra-child' ); ?></div>
				<?php endif; ?>
				<div class="oss-lpb-canvas" data-device="desktop">
					<iframe id="oss-lpb-frame" class="oss-lpb-frame" src="<?php echo esc_url( $canvas_url ); ?>" title="<?php esc_attr_e( 'Live page canvas', 'astra-child' ); ?>"></iframe>
				</div>
			</main>
		</div>
	</div>
	<?php
}

/* -------------------------------------------------------------------- */
/* Asset loading — editor shell (admin) + canvas (front end)             */
/* -------------------------------------------------------------------- */

add_action( 'admin_enqueue_scripts', 'oss_lpb_enqueue_editor' );
function oss_lpb_enqueue_editor( $hook ) {
	if ( 'admin_page_oss-live-builder' !== $hook ) {
		return;
	}
	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
	$ver     = defined( 'OSS_CHILD_VERSION' ) ? OSS_CHILD_VERSION : '1';

	wp_enqueue_media();
	wp_enqueue_style( 'oss-lpb-editor', OSS_CHILD_URI . '/assets/css/live-editor.css', array(), $ver );
	wp_enqueue_style( 'oss-lpb-editor-responsive', OSS_CHILD_URI . '/assets/css/live-editor-responsive.css', array( 'oss-lpb-editor' ), $ver );

	foreach ( array( 'history', 'elements', 'ui', 'live-editor' ) as $slug ) {
		$handle = 'history' === $slug ? 'oss-lpb-history' : ( 'live-editor' === $slug ? 'oss-lpb-core' : 'oss-lpb-' . $slug );
		$file   = 'live-editor' === $slug ? 'live-editor.js' : 'live-editor-' . $slug . '.js';
		$deps   = 'live-editor' === $slug ? array( 'oss-lpb-history', 'oss-lpb-elements', 'oss-lpb-ui' ) : array();
		wp_enqueue_script( $handle, OSS_CHILD_URI . '/assets/js/' . $file, $deps, $ver, true );
	}

	wp_localize_script( 'oss-lpb-core', 'OSS_LPB', array(
		'postId'  => $post_id,
		'rest'    => esc_url_raw( rest_url( 'oss-lpb/v1/doc/' . $post_id ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
		'schema'  => oss_lpb_schema(),
	) );
}

/* Builder pages always load the small render stylesheet (front end + canvas),
   like any component CSS. No builder JS for normal visitors. */
add_action( 'wp_enqueue_scripts', 'oss_lpb_enqueue_render', 15 );
function oss_lpb_enqueue_render() {
	if ( is_page_template( 'page-templates/template-live-builder.php' ) ) {
		$ver = defined( 'OSS_CHILD_VERSION' ) ? OSS_CHILD_VERSION : '1';
		wp_enqueue_style( 'oss-lpb-render', OSS_CHILD_URI . '/assets/css/live-editor-render.css', array( 'oss-global' ), $ver );
	}
}

/* Canvas mode on the front end: outline/select assets, clean chrome. */
add_action( 'wp_enqueue_scripts', 'oss_lpb_enqueue_canvas', 20 );
function oss_lpb_enqueue_canvas() {
	if ( ! oss_lpb_is_canvas() ) {
		return;
	}
	$id = get_queried_object_id();
	if ( ! oss_lpb_user_can( $id ) ) {
		return; // Not a permitted editor: behave like a normal page view.
	}
	$ver = defined( 'OSS_CHILD_VERSION' ) ? OSS_CHILD_VERSION : '1';
	wp_enqueue_style( 'oss-lpb-canvas', OSS_CHILD_URI . '/assets/css/live-editor-canvas.css', array(), $ver );
	wp_enqueue_script( 'oss-lpb-canvas', OSS_CHILD_URI . '/assets/js/live-editor-canvas.js', array(), $ver, true );
}

/* In canvas mode, drop the admin bar and add a body flag for the outline CSS. */
add_filter( 'show_admin_bar', function ( $show ) {
	return oss_lpb_is_canvas() && oss_lpb_user_can( get_queried_object_id() ) ? false : $show;
} );
add_filter( 'body_class', function ( $classes ) {
	if ( oss_lpb_is_canvas() && oss_lpb_user_can( get_queried_object_id() ) ) {
		$classes[] = 'oss-lpb-canvas-mode';
	}
	return $classes;
} );
