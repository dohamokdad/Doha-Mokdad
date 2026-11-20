<?php
/**
 * Home page. Theme 9 renders it itself (design 9): no Elementor, no page content.
 * The older paths below stay only for a site that turns design 9 off with the dp9_home filter.
 */
if ( apply_filters( 'dp9_home', true ) ) {
	get_header( 'v9' );
	dp9_home();
	get_footer( 'v9' );
	return;
}
get_header();

if ( is_page() && dp_is_elementor_page( get_queried_object_id() ) ) {
	while ( have_posts() ) { the_post(); the_content(); }
} elseif ( is_page() && trim( get_post_field( 'post_content', get_queried_object_id() ) ) ) {
	while ( have_posts() ) { the_post(); echo '<div class="wrap prose page-content">'; the_content(); echo '</div>'; }
} else {
	dp_render_hero();
	dp_render_projects();
	dp_render_services();
	dp_render_solved();
	dp_render_process();
	dp_render_plans();
	dp_render_experience();
	dp_render_skills();
	dp_render_certificates();
	dp_render_recognition();
	dp_render_faq();
	dp_render_contact();
}

get_footer();
