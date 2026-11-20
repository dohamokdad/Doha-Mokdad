<?php
/**
 * Blocks. Each block lives in build/<name>/ (compiled from src/<name>/ by @wordpress/scripts)
 * and is registered from its block.json.
 *
 * @package Doha_Portfolio_Core
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		$dir = DPC_DIR . 'build/project-showcase';
		if ( file_exists( $dir . '/block.json' ) ) {
			$block = register_block_type( $dir );
			if ( $block && ! empty( $block->editor_script_handles ) ) {
				wp_set_script_translations( $block->editor_script_handles[0], 'doha-portfolio' );
			}
		}
	}
);

/**
 * Projects for the Project Showcase block, with a short-lived cache per set of options.
 *
 * @param array $args { count:int, type:string, featured:bool }.
 * @return int[] Post IDs.
 */
function dpc_showcase_ids( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'count'    => 4,
			'type'     => '',
			'featured' => true,
		)
	);
	$q    = array(
		'post_type'           => 'project',
		'post_status'         => 'publish',
		'posts_per_page'      => max( 1, min( 12, (int) $args['count'] ) ),
		'fields'              => 'ids',
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
		'meta_query'          => array(
		array(
		'key'     => '_thumbnail_id',
		'compare' => 'EXISTS',
			),
		), // phpcs:ignore WordPress.DB.SlowDBQuery
		'orderby'             => array(
	'menu_order' => 'ASC',
	'date'       => 'DESC',
	),
	);
	if ( $args['featured'] ) {
		$q['meta_query'][] = array(
			'key'   => '_dp_featured',
			'value' => '1',
		);
	}
	if ( $args['type'] ) {
		$q['tax_query'] = array(
			array(
				'taxonomy' => 'project_type',
				'field'    => 'slug',
				'terms'    => sanitize_title( $args['type'] ),
			),
		); // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	$key = 'dpc_sc_' . md5( wp_json_encode( $q ) . wp_cache_get_last_changed( 'posts' ) );
	$ids = get_transient( $key );
	if ( ! is_array( $ids ) ) {
		$ids = get_posts( $q );
		set_transient( $key, $ids, HOUR_IN_SECONDS );
	}
	return array_map( 'intval', $ids );
}
