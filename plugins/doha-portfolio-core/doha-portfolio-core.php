<?php
/**
 * Plugin Name: Doha Portfolio Core
 * Description: Projects (post type, types, companies), the site's numbers, a REST endpoint and a Project Showcase block — kept separate from the theme so they survive any redesign.
 * Version: 1.1.0
 * Author: Doha Mokdad
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Text Domain: doha-portfolio
 *
 * @package Doha_Portfolio_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'DPC_VERSION', '1.1.0' );
define( 'DPC_DIR', plugin_dir_path( __FILE__ ) );

require DPC_DIR . 'includes/rest.php';
require DPC_DIR . 'includes/blocks.php';

/* ---------- Content model ---------- */
function dpc_register() {
	register_post_type(
		'project',
		array(
			'labels'        => array(
				'name'          => __( 'Projects', 'doha-portfolio' ),
				'singular_name' => __( 'Project', 'doha-portfolio' ),
				'add_new_item'  => __( 'Add New Project', 'doha-portfolio' ),
				'edit_item'     => __( 'Edit Project', 'doha-portfolio' ),
				'all_items'     => __( 'All Projects', 'doha-portfolio' ),
			),
			'public'        => true,
			'has_archive'   => 'projects',
			'rewrite'       => array( 'slug' => 'project' ),
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 5,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);

	register_taxonomy(
		'project_type',
		'project',
		array(
			'labels'            => array(
				'name'          => __( 'Project Types', 'doha-portfolio' ),
				'singular_name' => __( 'Project Type', 'doha-portfolio' ),
			),
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'project-type' ),
		)
	);

	register_taxonomy(
		'project_company',
		'project',
		array(
			'labels'            => array(
				'name'          => __( 'Companies', 'doha-portfolio' ),
				'singular_name' => __( 'Company', 'doha-portfolio' ),
				'menu_name'     => __( 'Companies', 'doha-portfolio' ),
			),
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'meta_box_cb'       => false,
			'rewrite'           => array( 'slug' => 'company' ),
		)
	);

	// Case-study fields, readable by the REST API and any theme.
	foreach ( array( '_dp_url', '_dp_country', '_dp_employer', '_dp_summary', '_dp_featured', '_dp_offline', '_dp_role', '_dp_tech', '_dp_challenge', '_dp_solution', '_dp_result', '_dp_year' ) as $key ) {
		register_post_meta(
			'project',
			$key,
			array(
				'type'          => 'string',
				'single'        => true,
				'show_in_rest'  => false,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' ); },
			)
		);
	}
}
add_action( 'init', 'dpc_register', 5 );

/* ---------- The site's numbers: one source for every page ---------- */

/**
 * Projects published, countries served and years of experience.
 * Years come from Settings → Doha Portfolio (default 15+), the rest are counted.
 *
 * @return array{projects:int,countries:int,years:string}
 */
function dpc_stats() {
	$s = get_transient( 'dpc_stats' );
	if ( is_array( $s ) ) {
		return $s; }
	global $wpdb;
	$countries = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT m.meta_value) FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id WHERE m.meta_key = '_dp_country' AND m.meta_value NOT IN ('', 'Africa') AND p.post_type = 'project' AND p.post_status = 'publish'" );
	$counts    = wp_count_posts( 'project' );
	$s         = array(
		'projects'  => isset( $counts->publish ) ? (int) $counts->publish : 0,
		'countries' => max( 1, $countries ),
		'years'     => (string) get_option( 'dpc_years', '15+' ),
	);
	set_transient( 'dpc_stats', $s, DAY_IN_SECONDS );
	return $s;
}

function dpc_flush_stats() {
	delete_transient( 'dpc_stats' );
	delete_transient( 'dp_country_count' ); }
add_action( 'save_post_project', 'dpc_flush_stats' );
add_action( 'deleted_post', 'dpc_flush_stats' );
add_action( 'trashed_post', 'dpc_flush_stats' );
add_action( 'update_option_dpc_years', 'dpc_flush_stats' );

/* Years of experience: one small setting under Settings → General. */
add_action(
	'admin_init',
	function () {
		register_setting(
			'general',
			'dpc_years',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '15+',
			)
		);
		add_settings_field(
			'dpc_years',
			__( 'Years of experience (portfolio)', 'doha-portfolio' ),
			function () {
				printf( '<input name="dpc_years" id="dpc_years" type="text" class="small-text" value="%s"> <p class="description">%s</p>', esc_attr( get_option( 'dpc_years', '15+' ) ), esc_html__( 'Shown on the portfolio next to the counted projects and countries.', 'doha-portfolio' ) );
			},
			'general'
		);
	}
);

register_activation_hook(
	__FILE__,
	function () {
		dpc_register();
		flush_rewrite_rules();
	}
);
