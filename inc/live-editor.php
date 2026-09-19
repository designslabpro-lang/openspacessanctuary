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

/**
 * Whether a page should render through the Live Builder. True when the page is
 * explicitly enabled (opt-in meta) OR — for backward compatibility — assigned
 * the Live Builder template. Everything else renders its normal design.
 */
function oss_lpb_is_builder_page( $post_id ) {
	$post_id = (int) $post_id;
	if ( ! $post_id ) {
		return false;
	}
	if ( get_post_meta( $post_id, OSS_LPB_ENABLED_META, true ) ) {
		return true;
	}
	return 'page-templates/template-live-builder.php' === get_page_template_slug( $post_id );
}

/**
 * Whether a page is builder-enabled via the opt-in meta (as opposed to having
 * the template assigned the old way). Used to offer a "turn off" control.
 */
function oss_lpb_is_enabled_via_meta( $post_id ) {
	return (bool) get_post_meta( (int) $post_id, OSS_LPB_ENABLED_META, true );
}

/**
 * Render an opt-in page through the builder template without the user having to
 * change Page → Attributes → Template. STRICT no-op unless the page carries the
 * explicit enable flag, so no existing page's design is ever affected.
 */
add_filter( 'template_include', 'oss_lpb_template_include', 99 );
function oss_lpb_template_include( $template ) {
	if ( ! is_singular( 'page' ) ) {
		return $template;
	}
	$id = get_queried_object_id();
	if ( ! $id || ! get_post_meta( $id, OSS_LPB_ENABLED_META, true ) ) {
		return $template; // Not enabled: leave the normal template untouched.
	}
	$builder = locate_template( 'page-templates/template-live-builder.php' );
	return $builder ? $builder : $template;
}

/* -------------------------------------------------------------------- */
/* Pages list: "Edit with Live Builder" row action                       */
/* -------------------------------------------------------------------- */

add_filter( 'page_row_actions', 'oss_lpb_row_action', 10, 2 );
function oss_lpb_row_action( $actions, $post ) {
	if ( 'page' === $post->post_type && oss_lpb_user_can( $post->ID ) ) {
		$actions['oss_live_builder'] = '<a href="' . esc_url( oss_lpb_editor_url( $post->ID ) ) . '">' . esc_html__( 'Edit with Live Builder', 'astra-child' ) . '</a>';
		if ( oss_lpb_is_enabled_via_meta( $post->ID ) ) {
			$actions['oss_live_builder'] .= ' <span aria-hidden="true">·</span> <a href="' . esc_url( oss_lpb_editor_url( $post->ID ) ) . '" title="' . esc_attr__( 'Live Builder is on for this page', 'astra-child' ) . '">' . esc_html__( '(builder on)', 'astra-child' ) . '</a>';
		}
	}
	return $actions;
}

/* Admin-bar shortcut when viewing a builder page on the front end. */
add_action( 'admin_bar_menu', 'oss_lpb_admin_bar', 90 );
function oss_lpb_admin_bar( $bar ) {
	if ( is_page() && ! oss_lpb_is_canvas() ) {
		$id = get_queried_object_id();
		if ( $id && oss_lpb_user_can( $id ) && oss_lpb_is_builder_page( $id ) ) {
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
	$is_builder    = oss_lpb_is_builder_page( $post_id );
	$enabled_meta  = oss_lpb_is_enabled_via_meta( $post_id );
	$page_template = get_page_template_slug( $post_id );
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
					<?php $has_custom = $page_template && 'page-templates/template-live-builder.php' !== $page_template; ?>
					<div class="oss-lpb-enable" id="oss-lpb-enable-panel">
						<h2 class="oss-lpb-enable__title"><?php esc_html_e( 'This page isn’t built with the Live Builder yet', 'astra-child' ); ?></h2>
						<p class="oss-lpb-enable__body"><?php esc_html_e( 'Turn on the Live Builder to edit this page visually. It stays off for every page until you enable it, so nothing changes for visitors until you do.', 'astra-child' ); ?></p>
						<?php if ( $has_custom ) : ?>
							<p class="oss-lpb-enable__warn">
								<?php
								printf(
									/* translators: %s: template file name */
									esc_html__( 'Heads up: this page currently uses a custom design (%s). Enabling the Live Builder replaces that design with builder content. You can turn it back off to restore the original design.', 'astra-child' ),
									'<code>' . esc_html( $page_template ) . '</code>'
								);
								?>
							</p>
						<?php endif; ?>
						<button type="button" class="oss-lpb-btn-primary" id="oss-lpb-enable"><?php esc_html_e( 'Enable Live Builder for this page', 'astra-child' ); ?></button>
					</div>
				<?php elseif ( $enabled_meta ) : ?>
					<div class="oss-lpb-disable" id="oss-lpb-disable-bar">
						<span><?php esc_html_e( 'Live Builder is on for this page.', 'astra-child' ); ?></span>
						<button type="button" class="oss-lpb-btn-ghost is-danger" id="oss-lpb-disable"><?php esc_html_e( 'Turn off (restore normal page)', 'astra-child' ); ?></button>
					</div>
				<?php endif; ?>
				<div class="oss-lpb-recover" id="oss-lpb-recover" hidden>
					<span class="oss-lpb-recover__msg"><?php esc_html_e( 'Recovered unsaved changes from your last session.', 'astra-child' ); ?></span>
					<span class="oss-lpb-recover__acts">
						<button type="button" class="oss-lpb-btn-ghost" id="oss-lpb-recover-keep"><?php esc_html_e( 'Keep editing', 'astra-child' ); ?></button>
						<button type="button" class="oss-lpb-btn-ghost is-danger" id="oss-lpb-recover-discard"><?php esc_html_e( 'Discard & revert to last saved', 'astra-child' ); ?></button>
					</span>
				</div>
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

	wp_enqueue_script( 'oss-lpb-history', OSS_CHILD_URI . '/assets/js/live-editor-history.js', array(), $ver, true );
	wp_enqueue_script( 'oss-lpb-globals', OSS_CHILD_URI . '/assets/js/live-editor-globals.js', array(), $ver, true );
	wp_enqueue_script( 'oss-lpb-elements', OSS_CHILD_URI . '/assets/js/live-editor-elements.js', array(), $ver, true );
	wp_enqueue_script( 'oss-lpb-fields', OSS_CHILD_URI . '/assets/js/live-editor-fields.js', array( 'oss-lpb-elements', 'oss-lpb-globals' ), $ver, true );
	wp_enqueue_script( 'oss-lpb-ui', OSS_CHILD_URI . '/assets/js/live-editor-ui.js', array( 'oss-lpb-elements', 'oss-lpb-fields', 'oss-lpb-globals' ), $ver, true );
	wp_enqueue_script( 'oss-lpb-core', OSS_CHILD_URI . '/assets/js/live-editor.js', array( 'oss-lpb-history', 'oss-lpb-globals', 'oss-lpb-elements', 'oss-lpb-fields', 'oss-lpb-ui' ), $ver, true );

	wp_localize_script( 'oss-lpb-core', 'OSS_LPB', array(
		'postId'      => $post_id,
		'rest'        => esc_url_raw( rest_url( 'oss-lpb/v1/doc/' . $post_id ) ),
		'restGlobals' => esc_url_raw( rest_url( 'oss-lpb/v1/globals' ) ),
		'restEnable'  => esc_url_raw( rest_url( 'oss-lpb/v1/enable/' . $post_id ) ),
		'nonce'       => wp_create_nonce( 'wp_rest' ),
		'schema'      => oss_lpb_schema(),
		'globals'     => oss_lpb_get_globals(),
		'canGlobals'  => oss_lpb_user_can_globals(),
		'isBuilder'   => oss_lpb_is_builder_page( $post_id ),
	) );
}

/* Builder pages always load the small render stylesheet (front end + canvas),
   like any component CSS. No builder JS for normal visitors. */
add_action( 'wp_enqueue_scripts', 'oss_lpb_enqueue_render', 15 );
function oss_lpb_enqueue_render() {
	if ( oss_lpb_is_builder_page( get_queried_object_id() ) ) {
		$ver = defined( 'OSS_CHILD_VERSION' ) ? OSS_CHILD_VERSION : '1';
		wp_enqueue_style( 'oss-lpb-render', OSS_CHILD_URI . '/assets/css/live-editor-render.css', array( 'oss-global' ), $ver );
	}
}

/* Global colors + typography: printed as :root vars + low-specificity inherit
   rules on every builder page (front end + canvas). Pure CSS, no visitor JS. */
add_action( 'wp_head', 'oss_lpb_print_globals_css', 5 );
function oss_lpb_print_globals_css() {
	if ( oss_lpb_is_builder_page( get_queried_object_id() ) ) {
		echo oss_lpb_globals_css(); // phpcs:ignore WordPress.Security.EscapeOutput -- values sanitized on save.
	}
}

/* Canvas mode on the front end: outline/select assets, clean chrome. */
add_action( 'wp_enqueue_scripts', 'oss_lpb_enqueue_canvas', 20 );
function oss_lpb_enqueue_canvas() {
	if ( ! oss_lpb_is_canvas() ) {
		return;
	}
	$id = get_queried_object_id();
	if ( ! oss_lpb_user_can( $id ) || ! oss_lpb_is_builder_page( $id ) ) {
		return; // Not a permitted editor, or not a builder page: normal view.
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
