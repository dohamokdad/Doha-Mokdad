<?php
/**
 * Projects post type, taxonomy, meta box and admin columns.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'init', function () {
	// The Doha Portfolio Core plugin owns the content model; the theme only fills in when it is not active.
	if ( defined( 'DPC_VERSION' ) ) { return; }
	register_post_type( 'project', array(
		'labels'       => array(
			'name'          => __( 'Projects', 'doha-portfolio' ),
			'singular_name' => __( 'Project', 'doha-portfolio' ),
			'add_new_item'  => __( 'Add New Project', 'doha-portfolio' ),
			'edit_item'     => __( 'Edit Project', 'doha-portfolio' ),
			'all_items'     => __( 'All Projects', 'doha-portfolio' ),
		),
		'public'       => true,
		'has_archive'  => 'projects',
		'rewrite'      => array( 'slug' => 'project' ),
		'menu_icon'    => 'dashicons-portfolio',
		'menu_position'=> 5,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest' => true,
	) );

	register_taxonomy( 'project_type', 'project', array(
		'labels'            => array(
			'name'          => __( 'Project Types', 'doha-portfolio' ),
			'singular_name' => __( 'Project Type', 'doha-portfolio' ),
		),
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'project-type' ),
	) );

	register_taxonomy( 'project_company', 'project', array(
		'labels'            => array(
			'name'          => __( 'Companies', 'doha-portfolio' ),
			'singular_name' => __( 'Company', 'doha-portfolio' ),
			'menu_name'     => __( 'Companies', 'doha-portfolio' ),
			'description'   => __( 'Shown under the company name, e.g. role and years.', 'doha-portfolio' ),
		),
		'hierarchical'      => false,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'meta_box_cb'       => false, // set via the "Built at" field
		'rewrite'           => array( 'slug' => 'company' ),
	) );
} );

/** Company defaults: name => [order, description]. */
function dp_company_defaults() {
	return array(
		'EchoVision'   => array( 1, 'Senior WordPress Developer at a Dubai digital marketing agency. May 2025 - Present.' ),
		'ProITs'       => array( 2, 'Senior Web Developer, Dubai. Corporate websites for the Timgad Group and other UAE companies. 2015 - 2017.' ),
		'UCG'          => array( 3, 'WordPress Developer for United Communication Group and its companies, Dubai. 2015.' ),
		'Markettcom'   => array( 4, 'WordPress Developer for a Dubai brand licensing agency. 2023.' ),
		'NHTAM'        => array( 5, 'Web Developer for a Saudi digital marketing agency. 2016 - 2017.' ),
		'MicroSolutions' => array( 8, 'Web Developer at MicroSolutions, Damascus: the company website and client apps. 2016 - 2019.' ),
		'Sketchat Studio' => array( 7, 'Senior WordPress Developer. Figma, PSD and Illustrator designs built as responsive WordPress themes. 2017 - 2026.' ),
		'Game Power 7' => array( 6, 'Information Systems Developer at a Spacetoon Media Group company. 2013 - 2014.' ),
		'Freelance'    => array( 9, 'Direct clients across the Gulf, Europe, North America and Asia. 2009 - 2025.' ),
	);
}

/** Make sure a company term exists (with default description/order) and return its slug. */
function dp_company_term( $name ) {
	$name = trim( (string) $name );
	if ( '' === $name ) { $name = 'Freelance'; }
	$t = term_exists( $name, 'project_company' );
	if ( ! $t ) {
		$d = dp_company_defaults();
		$t = wp_insert_term( $name, 'project_company', array( 'description' => isset( $d[ $name ] ) ? $d[ $name ][1] : '' ) );
		if ( is_wp_error( $t ) ) { return ''; }
		update_term_meta( $t['term_id'], 'dp_order', isset( $d[ $name ] ) ? $d[ $name ][0] : 8 );
	}
	return (int) ( is_array( $t ) ? $t['term_id'] : $t );
}

/** Keep the company taxonomy in sync with the "Built at" field. */
function dp_sync_company( $post_id ) {
	$tid = dp_company_term( get_post_meta( $post_id, '_dp_employer', true ) );
	if ( $tid ) { wp_set_object_terms( $post_id, array( $tid ), 'project_company' ); }
}

/** Companies in display order, each with its projects. */
function dp_companies_with_projects( $items ) {
	$groups = array();
	foreach ( $items as $p ) {
		$ts = get_the_terms( $p->ID, 'project_company' );
		if ( ! $ts || is_wp_error( $ts ) ) { continue; }
		$t = $ts[0];
		if ( ! isset( $groups[ $t->term_id ] ) ) { $groups[ $t->term_id ] = array( 'term' => $t, 'items' => array(), 'order' => (int) get_term_meta( $t->term_id, 'dp_order', true ) ); }
		$groups[ $t->term_id ]['items'][] = $p;
	}
	uasort( $groups, function ( $a, $b ) { return $a['order'] === $b['order'] ? count( $b['items'] ) - count( $a['items'] ) : $a['order'] - $b['order']; } );
	return array_values( $groups );
}

/* Company order field on the Companies screen. */
add_action( 'project_company_edit_form_fields', function ( $term ) {
	$o = get_term_meta( $term->term_id, 'dp_order', true );
	echo '<tr class="form-field"><th scope="row"><label for="dp_order">' . esc_html__( 'Display order', 'doha-portfolio' ) . '</label></th><td><input type="number" name="dp_order" id="dp_order" value="' . esc_attr( $o ) . '"><p class="description">' . esc_html__( 'Lower numbers show first on the projects page.', 'doha-portfolio' ) . '</p></td></tr>';
} );
add_action( 'edited_project_company', function ( $term_id ) {
	if ( isset( $_POST['dp_order'] ) && current_user_can( 'manage_categories' ) ) { update_term_meta( $term_id, 'dp_order', (int) $_POST['dp_order'] ); } // phpcs:ignore
} );

/* ---------- Meta box ---------- */
function dp_meta_fields() {
	return array(
		'_dp_url'      => __( 'Website URL', 'doha-portfolio' ),
		'_dp_country'  => __( 'Country', 'doha-portfolio' ),
		'_dp_employer' => __( 'Built at (EchoVision, ProITs, Freelance…)', 'doha-portfolio' ),
	);
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'dp_project_details', __( 'Project details', 'doha-portfolio' ), 'dp_render_meta_box', 'project', 'side', 'high' );
} );

function dp_render_meta_box( $post ) {
	wp_nonce_field( 'dp_save_project', 'dp_project_nonce' );
	foreach ( dp_meta_fields() as $key => $label ) {
		printf(
			'<p><label for="%1$s"><strong>%2$s</strong></label><br><input type="text" class="widefat" id="%1$s" name="%1$s" value="%3$s"></p>',
			esc_attr( $key ), esc_html( $label ), esc_attr( get_post_meta( $post->ID, $key, true ) )
		);
	}
	printf(
		'<p><label><input type="checkbox" name="_dp_featured" value="1" %s> %s</label></p>',
		checked( get_post_meta( $post->ID, '_dp_featured', true ), '1', false ),
		esc_html__( 'Featured on the home page', 'doha-portfolio' )
	);
	$url = get_post_meta( $post->ID, '_dp_url', true );
	if ( $url ) {
		echo '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dp_refresh_shot&post=' . $post->ID ), 'dp_refresh_shot' ) ) . '">' . esc_html__( 'Refresh screenshot', 'doha-portfolio' ) . '</a></p>';
	}
}

add_action( 'save_post_project', function ( $post_id ) {
	if ( ! isset( $_POST['dp_project_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dp_project_nonce'] ) ), 'dp_save_project' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	foreach ( array_keys( dp_meta_fields() ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$val = wp_unslash( $_POST[ $key ] );
			$val = ( '_dp_url' === $key ) ? esc_url_raw( $val ) : sanitize_text_field( $val );
			update_post_meta( $post_id, $key, $val );
		}
	}
	update_post_meta( $post_id, '_dp_featured', isset( $_POST['_dp_featured'] ) ? '1' : '0' );
	dp_sync_company( $post_id );
} );

/* ---------- Admin columns ---------- */
add_filter( 'manage_project_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		if ( 'title' === $k ) { $new['dp_shot'] = __( 'Screenshot', 'doha-portfolio' ); }
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['dp_url']      = __( 'Website', 'doha-portfolio' );
			$new['dp_country']  = __( 'Country', 'doha-portfolio' );
			$new['dp_featured'] = '★';
		}
	}
	unset( $new['date'] );
	return $new;
} );

add_action( 'manage_project_posts_custom_column', function ( $col, $post_id ) {
	switch ( $col ) {
		case 'dp_shot':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 96, 60 ), array( 'style' => 'width:96px;height:auto;border-radius:4px' ) );
			} else {
				echo '<span style="color:#b32d2e">' . esc_html__( 'Pending', 'doha-portfolio' ) . '</span>';
			}
			break;
		case 'dp_url':
			$u = get_post_meta( $post_id, '_dp_url', true );
			if ( $u ) { echo '<a href="' . esc_url( $u ) . '" target="_blank" rel="noopener">' . esc_html( dp_host( $u ) ) . '</a>'; }
			break;
		case 'dp_country':
			echo esc_html( get_post_meta( $post_id, '_dp_country', true ) );
			break;
		case 'dp_featured':
			echo '1' === get_post_meta( $post_id, '_dp_featured', true ) ? '★' : '';
			break;
	}
}, 10, 2 );

/* Show all projects on the archive, featured first then menu order. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && ( $q->is_post_type_archive( 'project' ) || $q->is_tax( array( 'project_type', 'project_company' ) ) ) ) {
		$q->set( 'posts_per_page', -1 );
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
} );

/** Shared query for all projects (featured first). */
function dp_get_projects( $featured_only = false ) {
	$args = array(
		'post_type'      => 'project',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'no_found_rows'  => true,
	);
	if ( $featured_only ) {
		$args['meta_query'] = array( array( 'key' => '_dp_featured', 'value' => '1' ) );
	}
	return get_posts( $args );
}
