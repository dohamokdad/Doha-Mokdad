<?php
/**
 * Fills the site's Resume / Works / Contact pages with the theme's Elementor widgets
 * (their old widgets came from a previous theme and no longer render), and adds
 * "All projects" to the main menu.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Which layout a page should get, from its slug. */
function dp_page_kind( $slug ) {
	if ( preg_match( '/^(resume|cv|about)/', $slug ) ) { return 'resume'; }
	if ( preg_match( '/^(works?|portfolio|projects)(-|$)/', $slug ) ) { return 'works'; }
	if ( preg_match( '/^contacts?(-|$)/', $slug ) ) { return 'contact'; }
	return '';
}

function dp_page_layout( $kind ) {
	$w = function ( $widget, $key, $settings = array() ) {
		$s = dp_el_section( $widget, $key );
		if ( $settings ) { $s['elements'][0]['elements'][0]['settings'] = $settings; }
		return $s;
	};
	switch ( $kind ) {
		case 'resume':
			return array( $w( 'dp-experience', 'r-exp' ), $w( 'dp-skills', 'r-skills' ), $w( 'dp-recognition', 'r-rec' ), $w( 'dp-certificates', 'r-certs', array( 'visible' => 0 ) ), $w( 'dp-contact', 'r-contact' ) );
		case 'works':
			return array( $w( 'dp-projects', 'w-projects', array( 'source' => 'all', 'layout' => 'company', 'per_company' => 6, 'limit' => 0, 'show_more' => '', 'show_filters' => 'yes' ) ), $w( 'dp-contact', 'w-contact' ) );
		case 'contact':
			return array( $w( 'dp-contact', 'c-contact' ) );
	}
	return array();
}

/** Fill matching pages that have no theme widgets yet. $force refills them anyway. Returns the titles filled. */
function dp_fill_pages( $force = false ) {
	$done  = array();
	$front = (int) get_option( 'page_on_front' );
	foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => array( 'publish', 'draft', 'private' ), 'posts_per_page' => -1 ) ) as $p ) {
		if ( $p->ID === $front || (int) get_option( 'dp_home_page_id' ) === $p->ID ) { continue; }
		$kind = dp_page_kind( $p->post_name );
		if ( ! $kind ) { continue; }
		$raw = (string) get_post_meta( $p->ID, '_elementor_data', true );
		if ( ! $force && false !== strpos( $raw, '"dp-' ) ) { continue; }
		if ( $raw && ! get_post_meta( $p->ID, '_dp_old_elementor_data', true ) ) {
			update_post_meta( $p->ID, '_dp_old_elementor_data', wp_slash( $raw ) ); // backup of the previous content
		}
		update_post_meta( $p->ID, '_elementor_edit_mode', 'builder' );
		update_post_meta( $p->ID, '_elementor_template_type', 'wp-page' );
		update_post_meta( $p->ID, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
		update_post_meta( $p->ID, '_elementor_data', wp_slash( wp_json_encode( dp_page_layout( $kind ) ) ) );
		update_post_meta( $p->ID, '_wp_page_template', 'default' );
		delete_post_meta( $p->ID, '_elementor_element_cache' );
		delete_post_meta( $p->ID, '_elementor_css' );
		$title = trim( preg_replace( '/\s*\(\s*elementor\s*\)\s*$/i', '', $p->post_title ) );
		if ( $title !== $p->post_title ) { wp_update_post( array( 'ID' => $p->ID, 'post_title' => $title ) ); }
		$done[] = $title;
	}
	if ( $done && dp_elementor_active() && isset( \Elementor\Plugin::$instance->files_manager ) ) { \Elementor\Plugin::$instance->files_manager->clear_cache(); }
	return $done;
}

/** Add "All projects" to the main menu (after Works) if it is not there yet. */
function dp_menu_add_all_projects() {
	$locs = get_nav_menu_locations();
	$menu = ! empty( $locs['primary'] ) ? (int) $locs['primary'] : 0;
	if ( ! $menu ) { return false; }
	$items   = wp_get_nav_menu_items( $menu );
	$archive = get_post_type_archive_link( 'project' );
	$after   = 0;
	foreach ( (array) $items as $it ) {
		if ( ( 'post_type_archive' === $it->type && 'project' === $it->object ) || false !== strpos( (string) $it->url, 'post_type=project' ) || untrailingslashit( (string) $it->url ) === untrailingslashit( $archive ) ) { return false; }
		if ( preg_match( '/^(works?|portfolio)$/i', trim( $it->title ) ) ) { $after = (int) $it->menu_order; }
	}
	$pos = $after ? $after + 1 : count( (array) $items ) + 1;
	foreach ( (array) $items as $it ) {
		if ( (int) $it->menu_order >= $pos && ! $it->menu_item_parent ) {
			wp_update_post( array( 'ID' => $it->ID, 'menu_order' => (int) $it->menu_order + 1 ) );
		}
	}
	return (bool) wp_update_nav_menu_item( $menu, 0, array(
		'menu-item-title'    => 'All projects',
		'menu-item-type'     => 'post_type_archive',
		'menu-item-object'   => 'project',
		'menu-item-status'   => 'publish',
		'menu-item-position' => $pos,
	) );
}

/* Tools button: fill the pages again (keeps a backup of whatever was there). */
add_action( 'admin_post_dp_fill_pages', function () {
	check_admin_referer( 'dp_fill_pages' );
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Not allowed' ); }
	$done = dp_fill_pages( true );
	set_transient( 'dp_pages_notice', $done, 120 );
	wp_safe_redirect( admin_url( 'edit.php?post_type=project&page=dp-tools' ) );
	exit;
} );
add_action( 'admin_notices', function () {
	$done = get_transient( 'dp_pages_notice' );
	if ( false === $done ) { return; }
	delete_transient( 'dp_pages_notice' );
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $done ? 'Pages filled with the portfolio widgets: ' . implode( ', ', $done ) . '. Edit them any time with Elementor.' : 'No Resume, Works or Contact pages were found.' ) . '</p></div>';
} );

/** Remove "All projects" and "Blog" from the main menu (the Works page already lists every project). */
function dp_menu_remove_extras() {
	$removed = 0;
	foreach ( wp_get_nav_menus() as $menu ) {
		foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $it ) {
			$t = strtolower( trim( wp_strip_all_tags( $it->title ) ) );
			$is_archive = ( 'post_type_archive' === $it->type && 'project' === $it->object ) || false !== strpos( (string) $it->url, 'post_type=project' );
			$is_blog    = in_array( $t, array( 'blog', 'المدونة', 'all projects', 'كل المشاريع' ), true );
			if ( $is_archive || $is_blog ) { wp_delete_post( $it->ID, true ); $removed++; }
		}
	}
	return $removed;
}
