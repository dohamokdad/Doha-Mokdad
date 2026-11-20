<?php
/**
 * Pages. Elementor pages render full width; others get a simple title + content layout.
 */
if ( dp_is_elementor_page( get_queried_object_id() ) ) {
	get_header();
	while ( have_posts() ) { the_post(); the_content(); }
	get_footer();
	return;
}
get_template_part( 'index' );
