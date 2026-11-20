<?php
/**
 * Elementor integration: widgets, theme locations, and an Elementor-built Home page.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function dp_elementor_active() {
	return did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' );
}

/** Is this post built with Elementor? */
function dp_is_elementor_page( $post_id ) {
	if ( ! $post_id || ! dp_elementor_active() ) { return false; }
	$doc = \Elementor\Plugin::$instance->documents->get( $post_id );
	return $doc && $doc->is_built_with_elementor();
}

/* ---------- Widgets ---------- */
add_action( 'elementor/elements/categories_registered', function ( $manager ) {
	$manager->add_category( 'doha-portfolio', array( 'title' => __( 'Doha Portfolio', 'doha-portfolio' ), 'icon' => 'fa fa-code' ) );
} );

add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
	require_once DP_DIR . '/inc/elementor-widgets.php';
	foreach ( array( 'DP_Widget_Hero', 'DP_Widget_Services', 'DP_Widget_Plans', 'DP_Widget_Projects', 'DP_Widget_Solved', 'DP_Widget_Why', 'DP_Widget_Process', 'DP_Widget_Experience', 'DP_Widget_Faq', 'DP_Widget_Testimonials', 'DP_Widget_Service_Page', 'DP_Widget_Skills', 'DP_Widget_Recognition', 'DP_Widget_Certificates', 'DP_Widget_Contact' ) as $cls ) {
		$widgets_manager->register( new $cls() );
	}
} );

/* Elementor Theme Builder (Pro) can replace the header / footer. */
add_action( 'elementor/theme/register_locations', function ( $manager ) {
	$manager->register_all_core_location();
} );

/** One full-width Elementor section holding a single widget. */
function dp_el_section( $widget, $key ) {
	return array(
		'id'       => substr( md5( 's' . $key ), 0, 7 ),
		'elType'   => 'section',
		'settings' => array( 'layout' => 'full_width', 'gap' => 'no', 'stretch_section' => '' ),
		'elements' => array(
			array(
				'id'       => substr( md5( 'c' . $key ), 0, 7 ),
				'elType'   => 'column',
				'settings' => array( '_column_size' => 100, '_inline_size' => null ),
				'elements' => array(
					array(
						'id'         => substr( md5( 'w' . $key ), 0, 7 ),
						'elType'     => 'widget',
						'widgetType' => $widget,
						'settings'   => new stdClass(),
						'elements'   => array(),
					),
				),
				'isInner'  => false,
			),
		),
		'isInner'  => false,
	);
}

/* ---------- Setup: Elementor options + Home page ---------- */
function dp_elementor_setup() {
	if ( ! dp_elementor_active() ) { return; }

	// Keep the theme's own fonts and colours (don't let Elementor's default kit override them).
	update_option( 'elementor_disable_color_schemes', 'yes' );
	update_option( 'elementor_disable_typography_schemes', 'yes' );
	$cpt = get_option( 'elementor_cpt_support', array( 'page', 'post' ) );
	if ( ! in_array( 'project', (array) $cpt, true ) ) {
		$cpt[] = 'project';
		update_option( 'elementor_cpt_support', $cpt );
	}

	if ( get_option( 'dp_home_page_id' ) && get_post( get_option( 'dp_home_page_id' ) ) ) { return; }
	if ( ! add_option( 'dp_home_lock', time(), '', 'no' ) ) { return; }

	$data = array();
	foreach ( dp_home_order() as $w ) { $data[] = dp_el_section( $w, substr( $w, 3 ) ); }

	$page_id = wp_insert_post( array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'post_title'  => 'Home',
		'post_name'   => 'home',
	) );
	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $page_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
		update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		update_option( 'dp_home_page_id', $page_id );

		// Use it as the home page unless another page is already chosen.
		if ( 'page' !== get_option( 'show_on_front' ) || ! get_option( 'page_on_front' ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $page_id );
		}
	}
	delete_option( 'dp_home_lock' );
}
add_action( 'after_switch_theme', 'dp_elementor_setup', 20 );
add_action( 'admin_init', function () {
	if ( dp_elementor_active() && current_user_can( 'manage_options' ) && ! get_option( 'dp_home_page_id' ) ) {
		dp_elementor_setup();
	}
} );

/* Suggest installing Elementor. */
add_action( 'admin_notices', function () {
	if ( dp_elementor_active() || ! current_user_can( 'install_plugins' ) ) { return; }
	$url = admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' );
	printf(
		'<div class="notice notice-info"><p><strong>%s</strong> %s <a href="%s">%s</a></p></div>',
		esc_html__( 'Doha Portfolio:', 'doha-portfolio' ),
		esc_html__( 'Install and activate the free Elementor plugin to edit the home page visually. The page is created for you automatically.', 'doha-portfolio' ),
		esc_url( $url ),
		esc_html__( 'Install Elementor →', 'doha-portfolio' )
	);
} );

/* Quick link to edit the home page with Elementor. */
add_action( 'admin_notices', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! dp_elementor_active() || ! $screen || 'project_page_dp-tools' !== $screen->id ) { return; }
	$home = (int) get_option( 'dp_home_page_id' );
	if ( ! $home ) { return; }
	$edit = admin_url( 'post.php?post=' . $home . '&action=elementor' );
	printf( '<div class="notice notice-info"><p>%s <a class="button button-primary" href="%s">%s</a></p></div>', esc_html__( 'Your home page is built with Elementor.', 'doha-portfolio' ), esc_url( $edit ), esc_html__( 'Edit Home with Elementor', 'doha-portfolio' ) );
} );

/** Add the Recognition section to an existing theme-built Home page (before Contact), once. */
function dp_elementor_add_recognition() {
	$page_id = (int) get_option( 'dp_home_page_id' );
	if ( ! $page_id || ! get_post( $page_id ) ) { return; }
	$raw  = get_post_meta( $page_id, '_elementor_data', true );
	$data = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
	if ( ! is_array( $data ) || false !== strpos( wp_json_encode( $data ), 'dp-recognition' ) ) { return; }
	$at = count( $data );
	foreach ( $data as $i => $sec ) {
		if ( false !== strpos( wp_json_encode( $sec ), '"dp-contact"' ) ) { $at = $i; break; }
	}
	array_splice( $data, $at, 0, array( dp_el_section( 'dp-recognition', 'recognition' ) ) );
	update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
	delete_post_meta( $page_id, '_elementor_element_cache' );
	if ( dp_elementor_active() && isset( \Elementor\Plugin::$instance->files_manager ) ) { \Elementor\Plugin::$instance->files_manager->clear_cache(); }
	clean_post_cache( $page_id );
	do_action( 'litespeed_purge_all' );
	if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
	return true;
}

/** Insert a theme section into the theme-built Home page before another widget, once. */
function dp_elementor_insert_section( $widget, $key, $before = 'dp-contact' ) {
	// The page actually shown as the home page first (Settings → Reading), then the one the theme built.
	$page_id = 0;
	foreach ( array( (int) get_option( 'page_on_front' ), (int) get_option( 'dp_home_page_id' ) ) as $id ) {
		if ( $id && get_post( $id ) && get_post_meta( $id, '_elementor_data', true ) ) { $page_id = $id; break; }
	}
	if ( ! $page_id ) { return false; }
	$raw  = get_post_meta( $page_id, '_elementor_data', true );
	$data = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
	if ( ! is_array( $data ) || false !== strpos( wp_json_encode( $data ), '"' . $widget . '"' ) ) { return; }
	$at = count( $data );
	foreach ( $data as $i => $sec ) {
		if ( false !== strpos( wp_json_encode( $sec ), '"' . $before . '"' ) ) { $at = $i; break; }
	}
	array_splice( $data, $at, 0, array( dp_el_section( $widget, $key ) ) );
	update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
	delete_post_meta( $page_id, '_elementor_element_cache' );
	if ( dp_elementor_active() && isset( \Elementor\Plugin::$instance->files_manager ) ) { \Elementor\Plugin::$instance->files_manager->clear_cache(); }
}

/** The home page order: work first, then what I do, proof, prices, then the details. */
function dp_home_order() {
	return array( 'dp-hero', 'dp-projects', 'dp-services', 'dp-solved', 'dp-process', 'dp-plans', 'dp-experience', 'dp-skills', 'dp-certificates', 'dp-recognition', 'dp-testimonials', 'dp-faq', 'dp-contact' );
}

/** Put the theme's sections of the Elementor home page in dp_home_order(); anything else stays after the section it followed. */
function dp_elementor_reorder_home() {
	$page_id = 0;
	foreach ( array( (int) get_option( 'page_on_front' ), (int) get_option( 'dp_home_page_id' ) ) as $id ) {
		if ( $id && get_post( $id ) && get_post_meta( $id, '_elementor_data', true ) ) { $page_id = $id; break; }
	}
	if ( ! $page_id ) { return false; }
	$raw  = get_post_meta( $page_id, '_elementor_data', true );
	$data = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
	if ( ! is_array( $data ) || ! $data ) { return false; }
	$order = array_flip( dp_home_order() );
	$rank  = -1; $keyed = array();
	foreach ( $data as $i => $sec ) {
		$json = wp_json_encode( $sec );
		// "Why work with me" now lives inside "How I work" as a short list.
		if ( false !== strpos( $json, '"widgetType":"dp-why"' ) ) { continue; }
		foreach ( $order as $w => $r ) {
			if ( false !== strpos( $json, '"widgetType":"' . $w . '"' ) ) { $rank = $r; break; }
		}
		$keyed[] = array( $rank, $i, $sec );
	}
	usort( $keyed, function ( $a, $b ) { return $a[0] === $b[0] ? $a[1] - $b[1] : $a[0] - $b[0]; } );
	$new = wp_list_pluck( $keyed, 2 );
	if ( $new === $data ) { return true; }
	update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $new ) ) );
	delete_post_meta( $page_id, '_elementor_element_cache' );
	if ( dp_elementor_active() && isset( \Elementor\Plugin::$instance->files_manager ) ) { \Elementor\Plugin::$instance->files_manager->clear_cache(); }
	clean_post_cache( $page_id );
	if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
	return true;
}
