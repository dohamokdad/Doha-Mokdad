<?php
/**
 * One-time import of the bundled projects list (data/projects.json).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Import projects that don't exist yet (matched by URL). Returns number created. */
function dp_import_projects() {
	$file = DP_DIR . '/data/projects.json';
	if ( ! file_exists( $file ) ) { return 0; }
	$items = json_decode( file_get_contents( $file ), true ); // phpcs:ignore
	if ( ! is_array( $items ) ) { return 0; }
	// Projects added later through an images pack (uploads/doha-portfolio-data/projects-extra.json).
	$extra_file = dp_data_path( 'projects-extra.json' );
	if ( $extra_file && 0 !== strpos( $extra_file, DP_DIR ) ) {
		$extra = json_decode( (string) file_get_contents( $extra_file ), true ); // phpcs:ignore
		if ( is_array( $extra ) ) {
			foreach ( $extra as &$e ) { $e['_extra'] = 1; }
			unset( $e );
			$items = array_merge( $items, $extra );
		}
	}

	// Existing URLs, so re-running never duplicates.
	global $wpdb;
	$existing = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_dp_url'" );
	$existing = array_map( 'dp_url_key', $existing );
	foreach ( $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_dp_key'" ) as $k ) { $existing[] = 'key:' . $k; }

	$created = 0;
	foreach ( $items as $i => $p ) {
		$url  = untrailingslashit( esc_url_raw( isset( $p['url'] ) ? $p['url'] : '' ) );
		$pkey = $url ? '' : sanitize_title( isset( $p['key'] ) ? $p['key'] : $p['name'] );
		$ref  = $url ? $url : $pkey;
		// A project first added without a website (by key) that now has one: give the existing project its URL.
		if ( $url && ! empty( $p['key'] ) && ! in_array( dp_url_key( $url ), $existing, true ) ) {
			$byk = get_posts( array( 'post_type' => 'project', 'meta_key' => '_dp_key', 'meta_value' => sanitize_title( $p['key'] ), 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => 1 ) );
			if ( $byk ) {
				update_post_meta( $byk[0], '_dp_url', $url );
				delete_post_meta( $byk[0], '_dp_key' );
				delete_post_meta( $byk[0], '_dp_bundled_shot' );
				$existing[] = dp_url_key( $url );
			}
		}
		if ( in_array( $url ? dp_url_key( $url ) : 'key:' . $pkey, $existing, true ) ) {
			if ( ! empty( $p['_extra'] ) ) {
				// Projects listed in an images pack keep their company, country and type in sync with the pack.
				$fx = get_posts( array( 'post_type' => 'project', 'meta_key' => $url ? '_dp_url' : '_dp_key', 'meta_value' => $ref, 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => 1 ) );
				if ( $fx ) {
					if ( ! empty( $p['employer'] ) && get_post_meta( $fx[0], '_dp_employer', true ) !== $p['employer'] ) { update_post_meta( $fx[0], '_dp_employer', sanitize_text_field( $p['employer'] ) ); dp_sync_company( $fx[0] ); }
					if ( isset( $p['country'] ) && '' !== $p['country'] ) { update_post_meta( $fx[0], '_dp_country', sanitize_text_field( $p['country'] ) ); }
					if ( ! empty( $p['category'] ) ) { wp_set_object_terms( $fx[0], $p['category'], 'project_type' ); }
					// A summary from the pack fills an empty overview only; text written in the editor is kept.
					if ( ! empty( $p['summary'] ) && '' === trim( (string) get_post_meta( $fx[0], '_dp_summary', true ) ) ) { update_post_meta( $fx[0], '_dp_summary', sanitize_textarea_field( $p['summary'] ) ); }
				}
			}
			if ( dp_bundled_shot( $ref ) ) {
				$found = get_posts( array( 'post_type' => 'project', 'meta_key' => $url ? '_dp_url' : '_dp_key', 'meta_value' => $ref, 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => 1 ) );
				if ( $found && ! has_post_thumbnail( $found[0] ) ) { dp_save_screenshot( $found[0] ); }
			}
			continue;
		}

		$id = wp_insert_post( array(
			'post_type'   => 'project',
			'post_status' => 'publish',
			'post_title'  => wp_strip_all_tags( $p['name'] ),
			'menu_order'  => ! empty( $p['featured'] ) ? $i : 1000 + $i,
		) );
		if ( ! $id || is_wp_error( $id ) ) { continue; }

		update_post_meta( $id, '_dp_url', $url );
		if ( $pkey ) { update_post_meta( $id, '_dp_key', $pkey ); }
		if ( ! empty( $p['delivered'] ) ) { update_post_meta( $id, '_dp_delivered', sanitize_text_field( $p['delivered'] ) ); }
		if ( ! empty( $p['summary'] ) ) { update_post_meta( $id, '_dp_summary', sanitize_textarea_field( $p['summary'] ) ); }
		update_post_meta( $id, '_dp_country', sanitize_text_field( $p['country'] ) );
		update_post_meta( $id, '_dp_employer', sanitize_text_field( $p['employer'] ) );
		update_post_meta( $id, '_dp_featured', ! empty( $p['featured'] ) ? '1' : '0' );
		dp_sync_company( $id );
		if ( ! empty( $p['category'] ) ) {
			wp_set_object_terms( $id, $p['category'], 'project_type' );
		}
		if ( dp_bundled_shot( $ref ) ) {
			dp_save_screenshot( $id );
			update_post_meta( $id, '_dp_bundled_shot', md5_file( dp_bundled_shot( $ref ) ) );
		}
		dp_apply_bundled_delivered( $id );
		$existing[] = $url ? dp_url_key( $url ) : 'key:' . $pkey;
		$created++;
	}
	return $created;
}

/**
 * "As delivered" screenshots bundled in data/delivered/{domain}/ (for sites that are offline or changed).
 * Fills the gallery, and updates it when the bundle changes, but never touches a gallery added by hand.
 */
function dp_apply_bundled_delivered( $post_id ) {
	$host    = dp_shot_key( dp_project_ref( $post_id ) );
	$dir     = $host ? dp_data_path( 'delivered/' . $host ) : '';
	$files   = ( $dir && is_dir( $dir ) ) ? glob( $dir . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE ) : array();
	$files   = $files ? $files : array();
	sort( $files );
	$sig     = $files ? md5( implode( '|', array_map( 'md5_file', $files ) ) ) : '';
	$gallery = dp_gallery_ids( $post_id );

	if ( $gallery ) {
		// Is the current gallery entirely ours (from a bundle)? Images added by hand are never removed.
		foreach ( $gallery as $gid ) {
			$ours = get_post_meta( $gid, '_dp_bundle_img', true ) || ' as delivered' === substr( get_the_title( $gid ), -13 );
			if ( ! $ours ) { return false; }
		}
		if ( get_post_meta( $post_id, '_dp_bundle_sig', true ) === $sig && $sig ) { return false; }
		foreach ( $gallery as $gid ) { wp_delete_attachment( $gid, true ); }
		delete_post_meta( $post_id, '_dp_gallery' );
		delete_post_meta( $post_id, '_dp_changed' );
		delete_post_meta( $post_id, '_dp_bundle_sig' );
	}
	if ( ! $files ) { return (bool) $gallery; }

	$ids = array();
	foreach ( $files as $i => $f ) {
		$ext = strtolower( pathinfo( $f, PATHINFO_EXTENSION ) );
		$id  = dp_store_image( $post_id, file_get_contents( $f ), $ext, get_the_title( $post_id ) . ' as delivered', $host . '-delivered-' . ( $i + 1 ) ); // phpcs:ignore
		if ( $id ) { update_post_meta( $id, '_dp_bundle_img', 1 ); $ids[] = $id; }
	}
	if ( ! $ids ) { return false; }
	update_post_meta( $post_id, '_dp_gallery', implode( ',', $ids ) );
	update_post_meta( $post_id, '_dp_changed', '1' );
	update_post_meta( $post_id, '_dp_bundle_sig', $sig );
	$mf   = dp_data_path( 'delivered/delivered.json' );
	$meta = $mf ? json_decode( (string) file_get_contents( $mf ), true ) : array(); // phpcs:ignore
	if ( ! get_post_meta( $post_id, '_dp_delivered', true ) && ! empty( $meta[ $host ]['date'] ) ) {
		update_post_meta( $post_id, '_dp_delivered', $meta[ $host ]['date'] );
	}
	return true;
}

/** Replace automatic screenshots with the ones bundled in data/screenshots/ (once per bundled file). */
function dp_refresh_bundled_shots( $ids = null ) {
	$n = 0;
	if ( null === $ids ) { $ids = get_posts( array( 'post_type' => 'project', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ); }
	foreach ( $ids as $pid ) {
		$file = dp_bundled_shot( dp_project_ref( $pid ) );
		$thumb = get_post_thumbnail_id( $pid );
		// Only replace automatic screenshots, never an image set by hand.
		if ( $file && ( ! $thumb || get_post_meta( $thumb, '_dp_auto_shot', true ) ) ) {
			$sum = md5_file( $file );
			if ( get_post_meta( $pid, '_dp_bundled_shot', true ) !== $sum && 'saved' === dp_save_screenshot( $pid, true ) ) {
				update_post_meta( $pid, '_dp_bundled_shot', $sum );
				$n++;
			}
		}
		if ( dp_apply_bundled_delivered( $pid ) ) { $n++; }
	}
	return $n;
}

/** Normalised key for a URL (no scheme, no www, no trailing slash) used to spot duplicates. */
function dp_url_key( $url ) {
	$url = strtolower( trim( (string) $url ) );
	$url = preg_replace( '#^https?://#', '', $url );
	$url = preg_replace( '#^www\.#', '', $url );
	return untrailingslashit( $url );
}

/**
 * Remove duplicate projects (same website). Keeps the copy that has a screenshot,
 * otherwise the oldest. Returns the number removed.
 */
function dp_dedupe_projects() {
	$ids    = get_posts( array( 'post_type' => 'project', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC' ) );
	$groups = array();
	foreach ( $ids as $id ) {
		$key = dp_url_key( get_post_meta( $id, '_dp_url', true ) );
		if ( '' === $key ) { $key = get_post_meta( $id, '_dp_key', true ) ? 'key:' . get_post_meta( $id, '_dp_key', true ) : 'title:' . strtolower( get_the_title( $id ) ); }
		$groups[ $key ][] = $id;
	}
	$removed = 0;
	foreach ( $groups as $group ) {
		if ( count( $group ) < 2 ) { continue; }
		$keep = $group[0];
		foreach ( $group as $id ) {
			if ( has_post_thumbnail( $id ) ) { $keep = $id; break; }
		}
		foreach ( $group as $id ) {
			if ( $id === $keep ) { continue; }
			$thumb = get_post_thumbnail_id( $id );
			if ( $thumb && $thumb !== get_post_thumbnail_id( $keep ) && get_post_meta( $thumb, '_dp_auto_shot', true ) ) {
				wp_delete_attachment( $thumb, true );
			}
			wp_delete_post( $id, true );
			$removed++;
		}
	}
	return $removed;
}

/* Run automatically the first time the theme is activated (locked so it can never run twice at once). */
add_action( 'after_switch_theme', function () {
	if ( ! get_option( 'dp_projects_imported' ) && add_option( 'dp_import_lock', time(), '', 'no' ) ) {
		update_option( 'dp_projects_imported', time() );
		$n = dp_import_projects();
		dp_apply_bundled_cases();
		set_transient( 'dp_import_notice', $n, 120 );
		delete_option( 'dp_import_lock' );
	}
	flush_rewrite_rules();
} );

/* One-time upgrade for sites that already have the theme: clean up duplicates. */
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$v = get_option( 'dp_data_version', '0' );
	if ( version_compare( $v, '2', '<' ) ) {
		$removed = dp_dedupe_projects();
		if ( $removed ) { set_transient( 'dp_dedupe_notice', $removed, 300 ); }
	}
	if ( version_compare( $v, '3', '<' ) ) {
		// Replace long dashes in project titles with plain punctuation.
		foreach ( get_posts( array( 'post_type' => 'project', 'post_status' => 'any', 'posts_per_page' => -1 ) ) as $p ) {
			$t = str_replace( array( ' – ', ' — ', '–', '—' ), array( ': ', ': ', '-', '-' ), $p->post_title );
			if ( $t !== $p->post_title ) { wp_update_post( array( 'ID' => $p->ID, 'post_title' => $t ) ); }
		}
	}
	if ( version_compare( $v, '6', '<' ) ) {
		foreach ( get_posts( array( 'post_type' => 'project', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $pid ) { dp_sync_company( $pid ); }
		flush_rewrite_rules();
	}
	if ( version_compare( $v, '5', '<' ) ) {
		dp_backfill_auto_flags();
		dp_apply_bundled_cases();
	}
	if ( version_compare( $v, '7', '<' ) ) {
		// v7: projects added to data/projects.json, Sketchat Studio company, Recognition section on the Home page.
		$n = dp_import_projects();
		if ( $n ) { set_transient( 'dp_import_notice', $n, 120 ); }
		if ( function_exists( 'dp_elementor_add_recognition' ) ) { dp_elementor_add_recognition(); }
		flush_rewrite_rules();
		update_option( 'dp_data_version', '7' );
	}
	if ( version_compare( $v, '8', '<' ) ) {
		// v8: Certificates section on the Home page, after Recognition.
		if ( function_exists( 'dp_elementor_insert_section' ) ) { dp_elementor_insert_section( 'dp-certificates', 'certificates', 'dp-contact' ); }
		update_option( 'dp_data_version', '8' );
	}
	if ( version_compare( $v, '9', '<' ) ) {
		// v9: four more projects, bundled screenshots for 11 sites, Game Power 7 "as delivered" galleries.
		$n = dp_import_projects();
		if ( $n ) { set_transient( 'dp_import_notice', $n, 120 ); }
		dp_refresh_bundled_shots();
		update_option( 'dp_data_version', '9' );
	}
	if ( version_compare( $v, '10', '<' ) ) {
		// v10: correct Game Power 7 game names and galleries, 11 more bundled screenshots, logos for sites without one.
		$names = array(
			'rappelz.gamepower7.com' => array( 'Adrenaline Online (Rappelz Arabic)', 'Amal Al-Shuoob (Rappelz Arabic)' ),
			'allods.gamepower7.com'  => array( 'Amal Al-Shuoob (Allods Online Arabic)', 'Allods Online Arabic' ),
		);
		foreach ( get_posts( array( 'post_type' => 'project', 'post_status' => 'any', 'posts_per_page' => -1 ) ) as $p ) {
			$h = dp_host( get_post_meta( $p->ID, '_dp_url', true ) );
			if ( isset( $names[ $h ] ) && $names[ $h ][0] === $p->post_title ) {
				wp_update_post( array( 'ID' => $p->ID, 'post_title' => $names[ $h ][1] ) );
			}
		}
		dp_import_projects();
		dp_refresh_bundled_shots();
		update_option( 'dp_data_version', '10' );
	}
	if ( version_compare( $v, '11', '<' ) ) {
		// v11: Palma Cosmetics and Faihaa Store, six more bundled screenshots.
		dp_import_projects();
		dp_refresh_bundled_shots();
		update_option( 'dp_data_version', '11' );
	}
	if ( version_compare( $v, '12', '<' ) ) {
		// v12: fill the Resume / Works / Contact pages with the theme widgets, add "All projects" to the menu.
		$done = dp_fill_pages();
		if ( $done ) { set_transient( 'dp_pages_notice', $done, 300 ); }
		update_option( 'dp_data_version', '12' );
	}
	if ( version_compare( $v, '13', '<' ) ) {
		// v13: updated social profiles (Instagram, YouTube, Facebook only), new project.
		set_theme_mod( 'dp_socials', dp_defaults()['socials'] );
		dp_import_projects();
		update_option( 'dp_data_version', '13' );
	}
	if ( version_compare( $v, '14', '<' ) ) {
		// v14: projects without a website (DiGi, Super Mom, Unlimited Services), MicroSolutions,
		// RC7Pro and SPIRE moved to Sketchat Studio (their footers credit the studio).
		foreach ( get_posts( array( 'post_type' => 'project', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $pid ) {
			if ( in_array( dp_url_key( get_post_meta( $pid, '_dp_url', true ) ), array( 'rc7pro.com', 'spire-db.co.uk' ), true ) ) {
				update_post_meta( $pid, '_dp_employer', 'Sketchat Studio' );
				dp_sync_company( $pid );
			}
		}
		dp_import_projects();
		dp_refresh_bundled_shots();
		update_option( 'dp_data_version', '14' );
	}
	if ( version_compare( $v, '15', '<' ) ) {
		// v15: MicroSolutions company group.
		foreach ( get_posts( array( 'post_type' => 'project', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $pid ) {
			if ( 'microsolutions.sy' === dp_url_key( get_post_meta( $pid, '_dp_url', true ) ) ) { update_post_meta( $pid, '_dp_employer', 'MicroSolutions' ); dp_sync_company( $pid ); }
		}
		dp_import_projects();
		update_option( 'dp_data_version', '15' );
	}
	if ( version_compare( $v, '16', '<' ) ) {
		// v16: Services section on the Home page, after the hero.
		if ( function_exists( 'dp_elementor_insert_section' ) ) { dp_elementor_insert_section( 'dp-services', 'services', 'dp-projects' ); }
		update_option( 'dp_data_version', '16' );
	}
	if ( version_compare( $v, '17', '<' ) ) {
		// v17: "How I work" section on the Home page, before Experience.
		if ( function_exists( 'dp_elementor_insert_section' ) ) { dp_elementor_insert_section( 'dp-process', 'process', 'dp-experience' ); }
		update_option( 'dp_data_version', '17' );
	}
	if ( version_compare( $v, '18', '<' ) ) {
		// v18: service pages, "Why work with me", FAQ and an (empty, hidden) testimonials section.
		$made = dp_create_service_pages();
		if ( $made ) { set_transient( 'dp_pages_notice', $made, 300 ); }
		if ( function_exists( 'dp_elementor_insert_section' ) ) {
			dp_elementor_insert_section( 'dp-why', 'why', 'dp-process' );
			dp_elementor_insert_section( 'dp-faq', 'faq', 'dp-contact' );
			dp_elementor_insert_section( 'dp-testimonials', 'testimonials', 'dp-faq' );
		}
		flush_rewrite_rules();
		update_option( 'dp_data_version', '18' );
	}
	if ( version_compare( $v, '19', '<' ) ) {
		// v19: LinkedIn, WhatsApp and Telegram in the social links; "All projects" and "Blog" out of the menu.
		$soc = (string) get_theme_mod( 'dp_socials', '' );
		$add = array( 'https://www.linkedin.com/in/doha-mokdad', 'https://wa.me/963938037719', 'https://t.me/+963938037719' );
		if ( '' === trim( $soc ) ) { set_theme_mod( 'dp_socials', dp_defaults()['socials'] ); }
		else {
			$have = strtolower( $soc );
			$new  = array();
			foreach ( $add as $u ) { if ( false === strpos( $have, dp_host( $u ) ) ) { $new[] = $u; } }
			if ( $new ) { set_theme_mod( 'dp_socials', implode( "\n", $new ) . "\n" . trim( $soc ) ); }
		}
		dp_menu_remove_extras();
		update_option( 'dp_data_version', '19' );
	}
	if ( version_compare( $v, '20', '<' ) ) {
		// Packages & prices, after the services on the Elementor home page.
		if ( function_exists( 'dp_elementor_insert_section' ) ) { dp_elementor_insert_section( 'dp-plans', 'plans', 'dp-projects' ); }
		update_option( 'dp_data_version', '20' );
	}
	if ( version_compare( $v, '21', '<' ) ) {
		// Problems I solved, after the projects.
		if ( function_exists( 'dp_elementor_insert_section' ) ) { dp_elementor_insert_section( 'dp-solved', 'solved', 'dp-why' ); }
		update_option( 'dp_data_version', '21' );
	}
	if ( version_compare( $v, '22', '<' ) ) {
		// Again, on the page really set as the home page (8.4.0 only looked at the page the theme built).
		if ( function_exists( 'dp_elementor_insert_section' ) ) {
			dp_elementor_insert_section( 'dp-plans', 'plans', 'dp-projects' );
			dp_elementor_insert_section( 'dp-solved', 'solved', 'dp-why' );
		}
		update_option( 'dp_data_version', '22' );
	}
	if ( version_compare( $v, '23', '<' ) ) {
		// New home order, lighter on text.
		if ( function_exists( 'dp_elementor_reorder_home' ) ) { dp_elementor_reorder_home(); }
		update_option( 'dp_data_version', '23' );
	}
} );

add_action( 'admin_notices', function () {
	$n = get_transient( 'dp_dedupe_notice' );
	if ( false === $n ) { return; }
	delete_transient( 'dp_dedupe_notice' );
	printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( sprintf( __( 'Removed %d duplicate projects.', 'doha-portfolio' ), (int) $n ) ) );
} );

add_action( 'admin_notices', function () {
	$n = get_transient( 'dp_import_notice' );
	if ( false === $n ) { return; }
	delete_transient( 'dp_import_notice' );
	$tools = admin_url( 'edit.php?post_type=project&page=dp-tools' );
	printf(
		'<div class="notice notice-success is-dismissible"><p><strong>%s</strong> %s <a href="%s">%s</a></p></div>',
		esc_html( sprintf( __( '%d projects imported.', 'doha-portfolio' ), (int) $n ) ),
		esc_html__( 'Next step: save the website screenshots to your media library.', 'doha-portfolio' ),
		esc_url( $tools ),
		esc_html__( 'Open Portfolio Tools →', 'doha-portfolio' )
	);
} );
