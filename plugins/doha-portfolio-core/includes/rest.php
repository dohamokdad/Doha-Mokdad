<?php
/**
 * Read-only REST endpoint for the portfolio numbers: GET /wp-json/doha-portfolio/v1/stats
 *
 * Lets any front end (the theme, a static page, a mobile app) show the same counts
 * without duplicating the counting logic.
 *
 * @package Doha_Portfolio_Core
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'doha-portfolio/v1',
			'/stats',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => '__return_true', // Public, read-only, aggregate numbers only.
				'callback'            => function () {
					$response = rest_ensure_response( dpc_stats() );
					$response->header( 'Cache-Control', 'public, max-age=3600' );
					return $response;
				},
				'schema'              => function () {
					return array(
						'$schema'    => 'http://json-schema.org/draft-04/schema#',
						'title'      => 'portfolio-stats',
						'type'       => 'object',
						'properties' => array(
							'projects'  => array(
								'type'        => 'integer',
								'description' => 'Published projects.',
							),
							'countries' => array(
								'type'        => 'integer',
								'description' => 'Distinct client countries.',
							),
							'years'     => array(
								'type'        => 'string',
								'description' => 'Years of experience, as set in Settings → General.',
							),
						),
					);
				},
			)
		);
	}
);
