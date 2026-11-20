<?php
/**
 * Project screenshots from a Google Drive folder.
 *
 * The Drive folder is the master copy: one image per project, named after the project's site
 * (roz.ae.jpg, atlas-data.sa-staging.jpg, or the project key for projects without a website).
 * The site copies each image once, in the few sizes it actually shows, and picks up replaced
 * files by itself every hour. Tools also shows which projects still have no image in Drive,
 * exports the screenshots the site already has (to put them in Drive), and frees hosting space.
 *
 * @package Doha_Portfolio
 */

defined( 'ABSPATH' ) || exit;

/** Image sizes kept for screenshots: admin thumbnail, card (800px wide, full length), social share. */
function dp_slim_sizes() { return array( 'thumbnail', 'dp-long', 'dp-large' ); }

function dp_drive_folder_id() {
	$v = trim( (string) get_option( 'dp_drive_folder', '' ) );
	if ( preg_match( '~folders/([A-Za-z0-9_-]{10,})~', $v, $m ) || preg_match( '~[?&]id=([A-Za-z0-9_-]{10,})~', $v, $m ) ) { return $m[1]; }
	return preg_match( '~^[A-Za-z0-9_-]{10,}$~', $v ) ? $v : '';
}
function dp_drive_key() { return trim( (string) get_option( 'dp_drive_api_key', '' ) ); }

/** The name a project's image must have in Drive (without extension). */
function dp_drive_name( $post_id ) { return strtolower( (string) dp_shot_key( dp_project_ref( $post_id ) ) ); }

function dp_drive_bridge() { return esc_url_raw( trim( (string) get_option( 'dp_drive_bridge', '' ) ) ); }

/** Shared secret between the site and the Drive helper script (made once, shown in the script). */
function dp_drive_secret() {
	$s = (string) get_option( 'dp_drive_secret', '' );
	if ( strlen( $s ) < 24 ) { $s = wp_generate_password( 32, false ); update_option( 'dp_drive_secret', $s, false ); }
	return $s;
}

/** Call the Drive helper (a Google Apps Script web app the owner of the folder deployed). */
function dp_drive_call( $action, $args = array(), $timeout = 60 ) {
	$url = dp_drive_bridge();
	if ( ! $url ) { return new WP_Error( 'dp_drive', 'No Drive helper.' ); }
	$res = wp_remote_get( add_query_arg( array_map( 'rawurlencode', array_merge( $args, array( 'action' => $action, 'secret' => dp_drive_secret() ) ) ), $url ), array( 'timeout' => $timeout, 'redirection' => 5 ) );
	if ( is_wp_error( $res ) ) { return $res; }
	$j = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $j ) ) {
		$said = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( preg_replace( '~<(script|style)[^>]*>.*?</\1>~si', '', (string) wp_remote_retrieve_body( $res ) ) ) ) );
		$msg  = __( 'The Drive helper did not answer. Check that it is deployed with access "Anyone".', 'doha-portfolio' );
		if ( false !== stripos( $said, 'doGet' ) ) { $msg = __( 'The Drive helper is deployed without the code. In Apps Script: paste the code, save, then Deploy → Manage deployments → edit → Version: New version → Deploy.', 'doha-portfolio' ); }
		return new WP_Error( 'dp_drive', $msg . ( $said ? ' (Google: ' . mb_substr( $said, 0, 160 ) . ')' : '' ) );
	}
	if ( ! empty( $j['error'] ) ) { return new WP_Error( 'dp_drive', 'Drive helper: ' . $j['error'] ); }
	return $j;
}

/**
 * Every image in the Drive folder: list of array( id, name, ver, modified ). Cached for 10 minutes.
 * Read through the Drive helper when there is one, else the Drive API (with a key), else the folder's public page.
 */
function dp_drive_files( $fresh = false ) {
	$folder = dp_drive_folder_id();
	if ( ! $folder ) { return new WP_Error( 'dp_drive', __( 'No Drive folder set.', 'doha-portfolio' ) ); }
	$ck = 'dp_drive_files_' . md5( $folder . dp_drive_key() . dp_drive_bridge() );
	if ( ! $fresh ) { $c = get_transient( $ck ); if ( is_array( $c ) ) { return $c; } }
	$out = array();
	if ( dp_drive_bridge() ) {
		$j = dp_drive_call( 'list', array( 'folder' => $folder ) );
		if ( is_wp_error( $j ) ) { return $j; }
		foreach ( (array) $j['files'] as $f ) {
			if ( empty( $f['mime'] ) || 0 !== strpos( $f['mime'], 'image/' ) ) { continue; }
			$out[] = array( 'id' => $f['id'], 'name' => $f['name'], 'ver' => $f['id'] . '@' . ( isset( $f['size'] ) ? $f['size'] : '' ), 'modified' => $f['modified'] );
		}
	} elseif ( dp_drive_key() ) {
		$token = '';
		do {
			$api = add_query_arg( array(
				'q'                         => rawurlencode( "'" . $folder . "' in parents and trashed=false" ),
				'fields'                    => rawurlencode( 'nextPageToken,files(id,name,md5Checksum,mimeType,modifiedTime)' ),
				'pageSize'                  => 1000,
				'supportsAllDrives'         => 'true',
				'includeItemsFromAllDrives' => 'true',
				'key'                       => rawurlencode( dp_drive_key() ),
				'pageToken'                 => $token ? rawurlencode( $token ) : false,
			), 'https://www.googleapis.com/drive/v3/files' );
			$res = wp_remote_get( $api, array( 'timeout' => 30 ) );
			if ( is_wp_error( $res ) ) { return $res; }
			$j = json_decode( (string) wp_remote_retrieve_body( $res ), true );
			if ( 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
				return new WP_Error( 'dp_drive', 'Google Drive: ' . ( isset( $j['error']['message'] ) ? $j['error']['message'] : 'HTTP ' . wp_remote_retrieve_response_code( $res ) ) );
			}
			foreach ( (array) ( isset( $j['files'] ) ? $j['files'] : array() ) as $f ) {
				if ( empty( $f['mimeType'] ) || 0 !== strpos( $f['mimeType'], 'image/' ) ) { continue; }
				$out[] = array( 'id' => $f['id'], 'name' => $f['name'], 'ver' => ! empty( $f['md5Checksum'] ) ? $f['md5Checksum'] : $f['id'], 'modified' => isset( $f['modifiedTime'] ) ? $f['modifiedTime'] : '' );
			}
			$token = isset( $j['nextPageToken'] ) ? $j['nextPageToken'] : '';
		} while ( $token );
	} else {
		$res = wp_remote_get( 'https://drive.google.com/embeddedfolderview?id=' . rawurlencode( $folder ), array( 'timeout' => 30 ) );
		if ( is_wp_error( $res ) ) { return $res; }
		$html = (string) wp_remote_retrieve_body( $res );
		if ( 200 !== (int) wp_remote_retrieve_response_code( $res ) || false === strpos( $html, 'flip-entry' ) ) {
			return new WP_Error( 'dp_drive', __( 'The Drive folder could not be read. Share it as "Anyone with the link can view", or set up the Drive helper.', 'doha-portfolio' ) );
		}
		if ( preg_match_all( '~id="entry-([A-Za-z0-9_-]+)".*?class="flip-entry-title">([^<]+)<~s', $html, $m, PREG_SET_ORDER ) ) {
			foreach ( $m as $e ) {
				$name = html_entity_decode( $e[2], ENT_QUOTES );
				if ( ! preg_match( '/\.(jpe?g|png|webp)$/i', $name ) ) { continue; }
				$out[] = array( 'id' => $e[1], 'name' => $name, 'ver' => $e[1], 'modified' => '' );
			}
		}
	}
	set_transient( $ck, $out, 10 * MINUTE_IN_SECONDS );
	return $out;
}

/** Projects in the form the matcher wants. */
function dp_drive_projects() {
	$out = array();
	foreach ( dp_get_projects() as $p ) {
		$url   = (string) get_post_meta( $p->ID, '_dp_url', true );
		$h     = $url ? wp_parse_url( $url, PHP_URL_HOST ) : '';
		$out[] = array( 'id' => $p->ID, 'title' => get_the_title( $p ), 'url' => $url, 'key' => dp_drive_name( $p->ID ), 'host' => $h ? preg_replace( '/^www\./', '', strtolower( $h ) ) : '' );
	}
	return $out;
}

/**
 * Which Drive image belongs to which project: the automatic match plus the choices made by hand
 * on Portfolio Tools (file id => project id, or 0 for "not a project screenshot").
 */
function dp_drive_map( $fresh = false ) {
	$files = dp_drive_files( $fresh );
	if ( is_wp_error( $files ) ) { return $files; }
	$manual = (array) get_option( 'dp_drive_manual', array() );
	$ocr    = (array) get_option( 'dp_drive_ocr', array() );
	foreach ( $files as &$f ) { if ( ! empty( $ocr[ $f['id'] ] ) ) { $f['text'] = $ocr[ $f['id'] ]; } }
	unset( $f );
	$auto   = array_values( array_filter( $files, function ( $f ) use ( $manual ) { return ! isset( $manual[ $f['id'] ] ); } ) );
	$map    = dp_drive_match( $auto, dp_drive_projects() );
	foreach ( $files as $f ) {
		if ( empty( $manual[ $f['id'] ] ) ) { continue; }
		$pid = (int) $manual[ $f['id'] ];
		if ( ! empty( $map['matched'][ $pid ] ) ) { $map['extras'][ $pid ][] = $map['matched'][ $pid ]; }
		$f['score'] = 110;
		$map['matched'][ $pid ] = $f;
	}
	$map['ignored'] = array_values( array_filter( $files, function ( $f ) use ( $manual ) { return isset( $manual[ $f['id'] ] ) && ! $manual[ $f['id'] ]; } ) );
	return $map;
}

/** The name a project's Drive image should have: "roz.ae.jpg". */
function dp_drive_target_name( $post_id, $file_name ) {
	$ext = strtolower( pathinfo( (string) $file_name, PATHINFO_EXTENSION ) );
	$ext = in_array( $ext, array( 'jpg', 'jpeg', 'png', 'webp' ), true ) ? ( 'jpeg' === $ext ? 'jpg' : $ext ) : 'jpg';
	return dp_drive_name( $post_id ) . '.' . $ext;
}

/** Download one Drive file. Returns the bytes or ''. */
function dp_drive_download( $file_id ) {
	if ( dp_drive_bridge() ) {
		$j = dp_drive_call( 'get', array( 'id' => $file_id ), 120 );
		return ( ! is_wp_error( $j ) && ! empty( $j['data'] ) ) ? (string) base64_decode( $j['data'] ) : ''; // phpcs:ignore
	}
	$url = dp_drive_key()
		? 'https://www.googleapis.com/drive/v3/files/' . rawurlencode( $file_id ) . '?alt=media&supportsAllDrives=true&key=' . rawurlencode( dp_drive_key() )
		: 'https://drive.usercontent.google.com/download?id=' . rawurlencode( $file_id ) . '&export=download&confirm=t';
	$res = wp_remote_get( $url, array( 'timeout' => 90, 'redirection' => 5 ) );
	if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) { return ''; }
	$body = (string) wp_remote_retrieve_body( $res );
	if ( strlen( $body ) < 2000 || false !== stripos( substr( $body, 0, 300 ), '<html' ) ) { return ''; }
	return $body;
}

/** Does this project have a new or changed image in Drive (that has not already failed)? */
function dp_drive_needs( $post_id, $map ) {
	if ( empty( $map['matched'][ $post_id ] ) ) { return false; }
	$ver = $map['matched'][ $post_id ]['ver'];
	if ( get_post_meta( $post_id, '_dp_drive_fail', true ) === $ver ) { return false; }
	return get_post_meta( $post_id, '_dp_drive_ver', true ) !== $ver || ! has_post_thumbnail( $post_id );
}

/**
 * Bring one project's image from Drive if it is new or changed there.
 * Returns 'saved', 'same' (already up to date), 'none' (no image in Drive) or 'error'.
 */
function dp_drive_sync_one( $post_id, $map = null ) {
	$map = null === $map ? dp_drive_map() : $map;
	if ( is_wp_error( $map ) ) { return 'error'; }
	if ( empty( $map['matched'][ $post_id ] ) ) { return 'none'; }
	$f = $map['matched'][ $post_id ];
	if ( get_post_meta( $post_id, '_dp_drive_ver', true ) === $f['ver'] && has_post_thumbnail( $post_id ) ) { return 'same'; }
	$body = dp_drive_download( $f['id'] );
	$aid  = $body ? dp_store_slim_shot( $post_id, $body, '_dp_drive' ) : 0;
	if ( ! $aid ) { update_post_meta( $post_id, '_dp_drive_fail', $f['ver'] ); return 'error'; } // not retried until the file changes
	update_post_meta( $post_id, '_dp_drive_ver', $f['ver'] );
	delete_post_meta( $post_id, '_dp_drive_fail' );
	return 'saved';
}

/**
 * The report: for every project, its Drive image (if any) and where its image on the site comes from.
 * status: drive (image in Drive) · live (no Drive image, the site has a live-site screenshot)
 *         · can_live (no image yet, has a website to screenshot) · offline (website did not answer)
 *         · manual (no website: needs an image in Drive) · upload (no Drive image, an image was added by hand).
 */
function dp_drive_report( $map = null ) {
	$map  = null === $map ? ( dp_drive_folder_id() ? dp_drive_map() : array( 'matched' => array(), 'extras' => array(), 'unmatched' => array(), 'ignored' => array() ) ) : $map;
	$ok   = ! is_wp_error( $map );
	$rows = array();
	foreach ( dp_get_projects() as $p ) {
		$tid  = (int) get_post_thumbnail_id( $p->ID );
		$url  = (string) get_post_meta( $p->ID, '_dp_url', true );
		$file = $ok && ! empty( $map['matched'][ $p->ID ] ) ? $map['matched'][ $p->ID ] : null;
		$src  = ! $tid ? 'none' : ( get_post_meta( $tid, '_dp_drive', true ) ? 'drive' : ( get_post_meta( $tid, '_dp_auto_shot', true ) ? 'live' : 'upload' ) );
		$off  = (int) get_post_meta( $p->ID, '_dp_offline', true );
		if ( $file ) {
			$status = 'drive';
		} elseif ( 'live' === $src ) {
			$status = 'live';
		} elseif ( 'upload' === $src || 'drive' === $src ) {
			$status = 'upload';
		} elseif ( ! $url || dp_is_changed( $p->ID ) ) {
			$status = 'manual';
		} elseif ( $off && $off > time() - WEEK_IN_SECONDS ) {
			$status = 'offline';
		} else {
			$status = 'can_live';
		}
		$rows[] = array(
			'id'      => $p->ID,
			'title'   => get_the_title( $p ),
			'url'     => $url,
			'target'  => dp_drive_name( $p->ID ),
			'file'    => $file ? $file['name'] : '',
			'renamed' => $file ? ( $file['name'] === dp_drive_target_name( $p->ID, $file['name'] ) ) : false,
			'via'     => $file && ! empty( $file['via'] ) ? $file['via'] : '',
			'status'  => $status,
			'on_site' => $src,
		);
	}
	return $rows;
}

function dp_drive_status_labels() {
	return array(
		'drive'    => __( 'Found in Drive', 'doha-portfolio' ),
		'live'     => __( 'Not in Drive: taken from the live site', 'doha-portfolio' ),
		'can_live' => __( 'Not in Drive: can be taken from the live site', 'doha-portfolio' ),
		'offline'  => __( 'Not in Drive: live site did not answer', 'doha-portfolio' ),
		'manual'   => __( 'Not in Drive: no live site, needs an image', 'doha-portfolio' ),
		'upload'   => __( 'Not in Drive: image added by hand on the site', 'doha-portfolio' ),
	);
}

/* ---------- Every hour: pick up new or replaced images from Drive ---------- */
add_action( 'init', function () {
	if ( dp_drive_folder_id() && ! wp_next_scheduled( 'dp_drive_cron' ) ) { wp_schedule_event( time() + 300, 'hourly', 'dp_drive_cron' ); }
} );
add_action( 'dp_drive_cron', function () {
	$map = dp_drive_map( true );
	if ( is_wp_error( $map ) ) { return; }
	$n = 0;
	foreach ( dp_get_projects() as $p ) {
		if ( ! dp_drive_needs( $p->ID, $map ) ) { continue; }
		dp_drive_sync_one( $p->ID, $map );
		if ( ++$n >= 6 ) { break; } // the rest next hour
	}
} );

/* ---------- Admin: settings, CSV, batched actions ---------- */
add_action( 'admin_post_dp_drive_settings', function () {
	check_admin_referer( 'dp_drive_settings' );
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Not allowed' ); }
	foreach ( array( 'dp_drive_folder', 'dp_drive_api_key', 'dp_drive_bridge' ) as $k ) {
		if ( isset( $_POST[ $k ] ) ) { update_option( $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ), false ); } // phpcs:ignore
	}
	wp_clear_scheduled_hook( 'dp_drive_cron' );
	wp_safe_redirect( admin_url( 'edit.php?post_type=project&page=dp-tools#dp-drive' ) );
	exit;
} );

/** Choices made by hand for Drive images the matcher could not place. */
add_action( 'admin_post_dp_drive_assign', function () {
	check_admin_referer( 'dp_drive_assign' );
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Not allowed' ); }
	$manual = (array) get_option( 'dp_drive_manual', array() );
	foreach ( (array) ( isset( $_POST['assign'] ) ? wp_unslash( $_POST['assign'] ) : array() ) as $fid => $pid ) { // phpcs:ignore
		$fid = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $fid );
		if ( '' === $fid || '' === (string) $pid ) { continue; }
		$manual[ $fid ] = 'skip' === $pid ? 0 : absint( $pid );
	}
	if ( ! empty( $_POST['reset'] ) ) { $manual = array(); } // phpcs:ignore
	update_option( 'dp_drive_manual', $manual, false );
	wp_safe_redirect( admin_url( 'edit.php?post_type=project&page=dp-tools#dp-drive-report' ) );
	exit;
} );

/** The report as a spreadsheet. */
add_action( 'admin_post_dp_drive_csv', function () {
	check_admin_referer( 'dp_drive_csv' );
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Not allowed' ); }
	$map = dp_drive_folder_id() ? dp_drive_map() : null;
	$rows = dp_drive_report( is_wp_error( $map ) ? null : $map );
	$lbl  = dp_drive_status_labels();
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=portfolio-screenshots-' . gmdate( 'Y-m-d' ) . '.csv' );
	$o = fopen( 'php://output', 'w' );
	fwrite( $o, "\xEF\xBB\xBF" ); // phpcs:ignore -- Excel opens UTF-8 correctly with a BOM
	fputcsv( $o, array( 'Project', 'Website', 'Status', 'Drive file now', 'Drive file name it should have' ) );
	foreach ( $rows as $r ) { fputcsv( $o, array( html_entity_decode( $r['title'], ENT_QUOTES, 'UTF-8' ), $r['url'], $lbl[ $r['status'] ], $r['file'], $r['target'] . '.jpg' ) ); }
	if ( $map && ! is_wp_error( $map ) ) {
		fputcsv( $o, array() );
		fputcsv( $o, array( 'Drive images that fit no project' ) );
		foreach ( $map['unmatched'] as $f ) { fputcsv( $o, array( $f['name'] ) ); }
	}
	fclose( $o ); // phpcs:ignore
	exit;
} );

/** 1200px wide JPEG, at most 9000px tall: what every screenshot is stored as. */
function dp_shot_prepare( $body ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	$tmp = wp_tempnam( 'dp-shot' );
	file_put_contents( $tmp, $body ); // phpcs:ignore
	$ed = wp_get_image_editor( $tmp );
	if ( is_wp_error( $ed ) ) { @unlink( $tmp ); return ''; } // phpcs:ignore
	$ed->set_quality( 82 );
	$sz = $ed->get_size();
	if ( $sz['width'] > 1200 ) { $ed->resize( 1200, 0 ); $sz = $ed->get_size(); }
	if ( $sz['height'] > 9000 ) { $ed->crop( 0, 0, $sz['width'], 9000 ); }
	$saved = $ed->save( $tmp . '.jpg', 'image/jpeg' );
	@unlink( $tmp ); // phpcs:ignore
	if ( is_wp_error( $saved ) ) { return ''; }
	$out = (string) file_get_contents( $saved['path'] ); // phpcs:ignore
	@unlink( $saved['path'] ); // phpcs:ignore
	return $out;
}

/** Store a screenshot as a slim attachment (only the sizes the site shows) and make it the project's image. */
function dp_store_slim_shot( $post_id, $body, $flag ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	$body = dp_shot_prepare( $body );
	if ( ! $body ) { return 0; }
	$name = dp_drive_name( $post_id );
	$up   = wp_upload_bits( sanitize_file_name( ( $name ? $name : 'project-' . $post_id ) . '-screenshot.jpg' ), null, $body );
	if ( ! empty( $up['error'] ) ) { return 0; }
	$only = function ( $sizes ) { return array_intersect_key( $sizes, array_flip( dp_slim_sizes() ) ); };
	add_filter( 'intermediate_image_sizes_advanced', $only, 99 );
	add_filter( 'big_image_size_threshold', '__return_false', 99 );
	$aid = wp_insert_attachment( array(
		'post_mime_type' => 'image/jpeg',
		'post_title'     => get_the_title( $post_id ) . ' website screenshot',
		'post_status'    => 'inherit',
	), $up['file'], $post_id );
	if ( $aid && ! is_wp_error( $aid ) ) { wp_update_attachment_metadata( $aid, wp_generate_attachment_metadata( $aid, $up['file'] ) ); }
	remove_filter( 'intermediate_image_sizes_advanced', $only, 99 );
	remove_filter( 'big_image_size_threshold', '__return_false', 99 );
	if ( ! $aid || is_wp_error( $aid ) ) { return 0; }
	update_post_meta( $aid, $flag, 1 );
	update_post_meta( $aid, '_wp_attachment_image_alt', get_the_title( $post_id ) . ' website' );
	$old = (int) get_post_thumbnail_id( $post_id );
	set_post_thumbnail( $post_id, $aid );
	if ( $old && $old !== $aid ) { dp_maybe_delete_shot( $old ); }
	delete_post_meta( $post_id, '_dp_offline' );
	return $aid;
}

/** Delete an old screenshot attachment if the theme made it (never one added by hand) and nothing else uses it. */
function dp_maybe_delete_shot( $att_id ) {
	$ours = get_post_meta( $att_id, '_dp_auto_shot', true ) || get_post_meta( $att_id, '_dp_drive', true )
		|| preg_match( '/ website screenshot$/', (string) get_the_title( $att_id ) );
	if ( ! $ours ) { return false; }
	global $wpdb;
	$used = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND meta_value = %s", (string) $att_id ) ); // phpcs:ignore
	return $used ? false : (bool) wp_delete_attachment( $att_id, true );
}

function dp_tools_ajax_check() {
	check_ajax_referer( 'dp_tools' );
	if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array( 'message' => 'Not allowed' ), 403 ); }
	if ( function_exists( 'set_time_limit' ) ) { @set_time_limit( 180 ); } // phpcs:ignore
}


/** Bring images from Drive, 3 projects per request. */
add_action( 'wp_ajax_dp_drive_sync', function () {
	dp_tools_ajax_check();
	$off = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0; // phpcs:ignore
	$map = dp_drive_map( 0 === $off );
	if ( is_wp_error( $map ) ) { wp_send_json_error( array( 'message' => $map->get_error_message() ) ); }
	$todo = array_values( array_filter( wp_list_pluck( dp_get_projects(), 'ID' ), function ( $id ) use ( $map ) { return dp_drive_needs( $id, $map ); } ) );
	$done = array( 'saved' => 0, 'error' => 0 );
	foreach ( array_slice( $todo, 0, 3 ) as $id ) {
		$r = dp_drive_sync_one( $id, $map );
		if ( isset( $done[ $r ] ) ) { $done[ $r ]++; }
	}
	wp_send_json_success( array( 'left' => max( 0, count( $todo ) - 3 ), 'saved' => $done['saved'], 'errors' => $done['error'] ) );
} );

/** Rename the Drive images to the project names ("roz.ae.jpg"), 15 per request. Needs the Drive helper. */
add_action( 'wp_ajax_dp_drive_rename', function () {
	dp_tools_ajax_check();
	if ( ! dp_drive_bridge() ) { wp_send_json_error( array( 'message' => __( 'Renaming needs the Drive helper (step 2).', 'doha-portfolio' ) ) ); }
	$map = dp_drive_map( empty( $_POST['more'] ) ); // phpcs:ignore
	if ( is_wp_error( $map ) ) { wp_send_json_error( array( 'message' => $map->get_error_message() ) ); }
	$taken = array();
	foreach ( dp_drive_files() as $f ) { $taken[ strtolower( $f['name'] ) ] = $f['id']; }
	$todo = array();
	foreach ( $map['matched'] as $pid => $f ) {
		$target = dp_drive_target_name( $pid, $f['name'] );
		if ( $f['name'] === $target ) { continue; }
		// Another image already has that name (a duplicate): leave both as they are.
		if ( isset( $taken[ strtolower( $target ) ] ) && $taken[ strtolower( $target ) ] !== $f['id'] ) { continue; }
		$todo[] = array( $f['id'], $target );
	}
	$ok = 0; $err = array();
	foreach ( array_slice( $todo, 0, 15 ) as $t ) {
		$r = dp_drive_call( 'rename', array( 'id' => $t[0], 'name' => $t[1] ) );
		if ( is_wp_error( $r ) ) { $err[] = $t[1] . ': ' . $r->get_error_message(); } else { $ok++; }
	}
	delete_transient( 'dp_drive_files_' . md5( dp_drive_folder_id() . dp_drive_key() . dp_drive_bridge() ) );
	$left = count( $todo ) - min( 15, count( $todo ) );
	wp_send_json_success( array( 'renamed' => $ok, 'errors' => $err, 'left' => $err ? 0 : $left ) );
} );

/** Read the text inside the images that fit no project (Drive OCR through the helper), 4 per request. */
add_action( 'wp_ajax_dp_drive_ocr', function () {
	dp_tools_ajax_check();
	if ( ! dp_drive_bridge() ) { wp_send_json_error( array( 'message' => __( 'Reading images needs the Drive helper (step 2).', 'doha-portfolio' ) ) ); }
	$map = dp_drive_map();
	if ( is_wp_error( $map ) ) { wp_send_json_error( array( 'message' => $map->get_error_message() ) ); }
	$ocr  = (array) get_option( 'dp_drive_ocr', array() );
	$todo = array_values( array_filter( $map['unmatched'], function ( $f ) use ( $ocr ) { return ! isset( $ocr[ $f['id'] ] ); } ) );
	$read = 0;
	foreach ( array_slice( $todo, 0, 4 ) as $f ) {
		$j = dp_drive_call( 'ocr', array( 'id' => $f['id'] ), 120 );
		if ( is_wp_error( $j ) ) { wp_send_json_error( array( 'message' => $j->get_error_message() ) ); }
		$ocr[ $f['id'] ] = isset( $j['text'] ) ? sanitize_textarea_field( mb_substr( (string) $j['text'], 0, 3000 ) ) : '';
		$read++;
	}
	update_option( 'dp_drive_ocr', $ocr, false );
	wp_send_json_success( array( 'read' => $read, 'left' => max( 0, count( $todo ) - $read ) ) );
} );

/** The Google Apps Script that lets the site list, read and rename the images in the folder. */
function dp_drive_script() {
	$secret = dp_drive_secret();
	$folder = dp_drive_folder_id();
	return "// Doha Portfolio — Drive helper. Lets dohamokdad.me list, read and rename the images in ONE folder.\n"
		. "const SECRET = '" . $secret . "';\n"
		. "const FOLDER_ID = '" . $folder . "';\n\n"
		. "function doGet(e) {\n"
		. "  const p = e.parameter || {};\n"
		. "  if (p.secret !== SECRET) return out({ error: 'Wrong secret' });\n"
		. "  const folder = DriveApp.getFolderById(FOLDER_ID);\n"
		. "  if (p.action === 'list') {\n"
		. "    const files = [], it = folder.getFiles();\n"
		. "    while (it.hasNext()) { const f = it.next(); files.push({ id: f.getId(), name: f.getName(), mime: f.getMimeType(), size: f.getSize(), modified: f.getLastUpdated().toISOString() }); }\n"
		. "    return out({ files: files });\n"
		. "  }\n"
		. "  const f = DriveApp.getFileById(p.id);\n"
		. "  if (!inFolder(f, folder)) return out({ error: 'File is not in the folder' });\n"
		. "  if (p.action === 'rename') { f.setName(String(p.name).replace(/[\\\\/]/g, '-')); return out({ ok: true }); }\n"
		. "  if (p.action === 'get') return out({ mime: f.getMimeType(), data: Utilities.base64Encode(f.getBlob().getBytes()) });\n"
		. "  if (p.action === 'ocr') return out(ocr(f));\n"
		. "  return out({ error: 'Unknown action' });\n"
		. "}\n\n"
		. "// Reads the text inside an image (logo, menu, e-mail…). Needs Services → Drive API turned on in this script.\n"
		. "function ocr(f) {\n"
		. "  if (typeof Drive === 'undefined') return { error: 'Turn on the Drive API service in the script (Services → + → Drive API)' };\n"
		. "  let blob = f.getBlob();\n"
		. "  if (f.getSize() > 1900000) { blob = UrlFetchApp.fetch('https://drive.google.com/thumbnail?sz=w1200&id=' + f.getId(), { headers: { Authorization: 'Bearer ' + ScriptApp.getOAuthToken() } }).getBlob(); }\n"
		. "  blob.setName('ocr-tmp');\n"
		. "  let doc;\n"
		. "  try { doc = Drive.Files.create({ name: 'ocr-tmp', mimeType: MimeType.GOOGLE_DOCS }, blob, { ocrLanguage: 'en' }); }\n"
		. "  catch (e) { doc = Drive.Files.insert({ title: 'ocr-tmp', mimeType: MimeType.GOOGLE_DOCS }, blob, { ocr: true }); }\n"
		. "  const text = DocumentApp.openById(doc.id).getBody().getText();\n"
		. "  DriveApp.getFileById(doc.id).setTrashed(true);\n"
		. "  return { text: text.slice(0, 4000) };\n"
		. "}\n\n"
		. "function inFolder(f, folder) { const ps = f.getParents(); while (ps.hasNext()) { if (ps.next().getId() === folder.getId()) return true; } return false; }\n"
		. "function out(o) { return ContentService.createTextOutput(JSON.stringify(o)).setMimeType(ContentService.MimeType.JSON); }\n";
}

/** Export the screenshots the site has and Drive does not, as zip files of 25 named the way Drive expects. */
add_action( 'wp_ajax_dp_drive_export', function () {
	dp_tools_ajax_check();
	if ( ! class_exists( 'ZipArchive' ) ) { wp_send_json_error( array( 'message' => 'ZipArchive is not available on this hosting.' ) ); }
	$part = isset( $_POST['part'] ) ? absint( $_POST['part'] ) : 0; // phpcs:ignore
	$map = dp_drive_folder_id() ? dp_drive_map() : array( 'matched' => array() );
	$map = is_wp_error( $map ) ? array( 'matched' => array() ) : $map;
	$ids = array();
	foreach ( dp_get_projects() as $p ) {
		if ( dp_drive_name( $p->ID ) && has_post_thumbnail( $p->ID ) && empty( $map['matched'][ $p->ID ] ) ) { $ids[] = $p->ID; }
	}
	$batch = array_slice( $ids, $part * 25, 25 );
	if ( ! $batch ) { wp_send_json_success( array( 'done' => true, 'total' => count( $ids ) ) ); }
	$u   = wp_upload_dir();
	$dir = trailingslashit( $u['basedir'] ) . 'dp-export';
	wp_mkdir_p( $dir );
	@file_put_contents( $dir . '/index.php', '<?php // Silence.' ); // phpcs:ignore
	$file = 'screenshots-' . wp_generate_password( 6, false ) . '-part-' . ( $part + 1 ) . '.zip';
	$zip  = new ZipArchive();
	if ( true !== $zip->open( $dir . '/' . $file, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) { wp_send_json_error( array( 'message' => 'Could not create the zip file.' ) ); }
	foreach ( $batch as $id ) {
		$tid  = get_post_thumbnail_id( $id );
		$path = function_exists( 'wp_get_original_image_path' ) ? wp_get_original_image_path( $tid ) : get_attached_file( $tid );
		if ( ! $path || ! file_exists( $path ) ) { continue; }
		$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		$zip->addFile( $path, dp_drive_name( $id ) . '.' . ( 'jpeg' === $ext ? 'jpg' : $ext ) );
	}
	$zip->close();
	wp_send_json_success( array( 'done' => false, 'url' => trailingslashit( $u['baseurl'] ) . 'dp-export/' . $file, 'part' => $part + 1, 'parts' => (int) ceil( count( $ids ) / 25 ), 'total' => count( $ids ) ) );
} );

/**
 * Free up hosting space, in steps:
 * 1) every project screenshot keeps only the sizes the site shows (removes 6–8 unused copies of each),
 * 2) screenshot attachments the theme made that no project uses any more are deleted,
 * 3) the old images-pack screenshot copies, export zips and upload leftovers are deleted.
 * Images added by hand and "as delivered" galleries are not touched.
 */
add_action( 'wp_ajax_dp_free_space', function () {
	dp_tools_ajax_check();
	global $wpdb;
	$step  = isset( $_POST['step'] ) ? sanitize_key( $_POST['step'] ) : 'sizes'; // phpcs:ignore
	$off   = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0; // phpcs:ignore
	$freed = 0;
	if ( 'sizes' === $step ) {
		$ids   = wp_list_pluck( dp_get_projects(), 'ID' );
		$batch = array_slice( $ids, $off, 4 );
		foreach ( $batch as $pid ) {
			$tid = (int) get_post_thumbnail_id( $pid );
			if ( ! $tid ) { continue; }
			dp_long_image_url( $tid ); // make sure the card size exists before others go
			$meta = wp_get_attachment_metadata( $tid );
			if ( ! is_array( $meta ) || empty( $meta['sizes'] ) ) { continue; }
			$orig = function_exists( 'wp_get_original_image_path' ) ? wp_get_original_image_path( $tid ) : get_attached_file( $tid );
			$osz  = $orig ? @getimagesize( $orig ) : false; // phpcs:ignore
			// A wide, long screenshot whose 800px card copy could not be made: leave its sizes alone.
			if ( empty( $meta['sizes']['dp-long'] ) && $osz && $osz[0] > 820 && $osz[1] / $osz[0] > 0.9 ) { continue; }
			$dir = dirname( get_attached_file( $tid ) );
			foreach ( $meta['sizes'] as $k => $s ) {
				if ( in_array( $k, dp_slim_sizes(), true ) || empty( $s['file'] ) ) { continue; }
				$f = $dir . '/' . $s['file'];
				if ( file_exists( $f ) ) { $freed += (int) filesize( $f ); @unlink( $f ); } // phpcs:ignore
				unset( $meta['sizes'][ $k ] );
			}
			wp_update_attachment_metadata( $tid, $meta );
		}
		$next = $off + 4;
		wp_send_json_success( array( 'step' => $next >= count( $ids ) ? 'orphans' : 'sizes', 'offset' => $next >= count( $ids ) ? 0 : $next, 'freed' => $freed, 'total' => count( $ids ), 'at' => min( $next, count( $ids ) ) ) );
	}
	if ( 'orphans' === $step ) {
		$rows = $wpdb->get_col( "SELECT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key IN ('_dp_auto_shot','_dp_drive') WHERE p.post_type = 'attachment' AND p.ID NOT IN (SELECT CAST(meta_value AS UNSIGNED) FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id') LIMIT 25" ); // phpcs:ignore
		foreach ( $rows as $aid ) {
			$f = get_attached_file( $aid );
			if ( $f && file_exists( $f ) ) { $freed += (int) filesize( $f ); }
			wp_delete_attachment( (int) $aid, true );
		}
		wp_send_json_success( array( 'step' => count( $rows ) < 25 ? 'files' : 'orphans', 'offset' => 0, 'freed' => $freed ) );
	}
	// files
	$u    = wp_upload_dir();
	$base = trailingslashit( $u['basedir'] );
	foreach ( array( 'doha-portfolio-data/screenshots', 'dp-export', 'dp-pack-tmp', 'doha-portfolio-data-tmp' ) as $rel ) {
		$dir = $base . $rel;
		if ( ! is_dir( $dir ) ) { continue; }
		$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
		foreach ( $it as $f ) {
			if ( $f->isDir() ) { @rmdir( $f->getPathname() ); continue; } // phpcs:ignore
			$freed += (int) $f->getSize();
			@unlink( $f->getPathname() ); // phpcs:ignore
		}
		@rmdir( $dir ); // phpcs:ignore
	}
	wp_send_json_success( array( 'step' => 'done', 'freed' => $freed ) );
} );

/** The "Screenshots from Google Drive" card on Portfolio Tools: setup, actions and the report. */
function dp_render_drive_card() {
	$folder = dp_drive_folder_id();
	$map    = $folder ? dp_drive_map() : null;
	$err    = is_wp_error( $map ) ? $map->get_error_message() : '';
	$rows   = dp_drive_report( $err ? null : $map );
	$lbl    = dp_drive_status_labels();
	$groups = array_fill_keys( array_keys( $lbl ), array() );
	foreach ( $rows as $r ) { $groups[ $r['status'] ][] = $r; }
	$to_rename = count( array_filter( $rows, function ( $r ) { return 'drive' === $r['status'] && ! $r['renamed']; } ) );
	$nonce     = wp_create_nonce( 'dp_tools' );
	$titles    = array();
	foreach ( $rows as $r ) { $titles[ $r['id'] ] = $r['title']; }
	?>
	<div class="card" id="dp-drive" style="max-width:960px">
		<h2><?php esc_html_e( 'Screenshots from Google Drive', 'doha-portfolio' ); ?></h2>
		<p><?php esc_html_e( 'Drive is the main copy of every project screenshot. The site finds which image belongs to which project (even "Opera Snapshot_…_roz.ae.png" or "Copy of …"), can rename them in Drive to the project name (roz.ae.jpg), copies each one once in light sizes, and checks the folder every hour.', 'doha-portfolio' ); ?></p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="dp_drive_settings">
			<?php wp_nonce_field( 'dp_drive_settings' ); ?>
			<h3>1. <?php esc_html_e( 'Drive folder', 'doha-portfolio' ); ?></h3>
			<p><input type="text" class="large-text" name="dp_drive_folder" value="<?php echo esc_attr( get_option( 'dp_drive_folder', '' ) ); ?>" placeholder="https://drive.google.com/drive/folders/…"></p>
			<?php if ( $folder ) : ?>
				<h3>2. <?php esc_html_e( 'Drive helper (lets the site rename files)', 'doha-portfolio' ); ?></h3>
				<?php if ( ! dp_drive_bridge() ) : ?>
					<ol>
						<li><?php esc_html_e( 'Signed in to the Google account that owns the folder, open script.google.com and click "New project".', 'doha-portfolio' ); ?></li>
						<li><?php esc_html_e( 'Delete what is there, paste the code below, and save.', 'doha-portfolio' ); ?></li>
						<li><?php esc_html_e( 'Deploy → New deployment → type "Web app". Execute as: Me. Who has access: Anyone. Deploy, and allow access when Google asks.', 'doha-portfolio' ); ?></li>
						<li><?php esc_html_e( 'Copy the "Web app URL" (it ends in /exec) into the field below and click Save.', 'doha-portfolio' ); ?></li>
					</ol>
				<?php else : ?>
					<p><strong><?php esc_html_e( 'Connected.', 'doha-portfolio' ); ?></strong> <?php esc_html_e( 'If you change the folder, update the folder id in the script too and deploy a new version.', 'doha-portfolio' ); ?></p>
				<?php endif; ?>
				<details<?php echo dp_drive_bridge() ? '' : ' open'; ?>><summary><?php esc_html_e( 'The code', 'doha-portfolio' ); ?></summary>
					<textarea readonly rows="14" class="large-text code" onclick="this.select()"><?php echo esc_textarea( dp_drive_script() ); ?></textarea>
				</details>
				<p><input type="url" class="large-text" name="dp_drive_bridge" value="<?php echo esc_attr( get_option( 'dp_drive_bridge', '' ) ); ?>" placeholder="https://script.google.com/macros/s/…/exec"></p>
			<?php endif; ?>
			<p><button class="button"><?php esc_html_e( 'Save', 'doha-portfolio' ); ?></button></p>
		</form>

		<?php if ( $folder ) : ?>
			<h3>3. <?php esc_html_e( 'Actions', 'doha-portfolio' ); ?></h3>
			<?php if ( $err ) : ?><p style="color:#b32d2e"><strong><?php echo esc_html( $err ); ?></strong></p><?php endif; ?>
			<p>
				<button class="button button-primary" id="dp-drive-rename" <?php disabled( ! dp_drive_bridge() || ! $to_rename ); ?>><?php echo esc_html( sprintf( __( 'Rename %d images in Drive', 'doha-portfolio' ), $to_rename ) ); ?></button>
				<button class="button button-primary" id="dp-drive-sync"><?php esc_html_e( 'Bring images from Drive to the site', 'doha-portfolio' ); ?></button>
				<button class="button" id="dp-drive-ocr" <?php disabled( ! dp_drive_bridge() || $err || ! $map['unmatched'] ); ?>><?php esc_html_e( 'Read the text inside unmatched images', 'doha-portfolio' ); ?></button>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dp_drive_csv' ), 'dp_drive_csv' ) ); ?>"><?php esc_html_e( 'Download the report (CSV)', 'doha-portfolio' ); ?></a>
			</p>
			<p id="dp-drive-status"></p>

			<?php if ( ! $err ) : ?>
			<h3 id="dp-drive-report">4. <?php esc_html_e( 'Report', 'doha-portfolio' ); ?></h3>
			<table class="widefat striped" style="max-width:640px">
				<tbody>
				<?php foreach ( $lbl as $k => $t ) : ?>
					<tr><td><?php echo esc_html( $t ); ?></td><td style="text-align:right"><strong><?php echo esc_html( count( $groups[ $k ] ) ); ?></strong></td></tr>
				<?php endforeach; ?>
					<tr><td><?php esc_html_e( 'Drive images that fit no project', 'doha-portfolio' ); ?></td><td style="text-align:right"><strong><?php echo esc_html( count( $map['unmatched'] ) ); ?></strong></td></tr>
				</tbody>
			</table>
			<?php if ( $groups['can_live'] ) : ?>
				<p><?php esc_html_e( 'The ones that can be taken from the live site: use "Save the next 25" under Website screenshots below.', 'doha-portfolio' ); ?></p>
			<?php endif; ?>

			<?php foreach ( $lbl as $k => $t ) : if ( ! $groups[ $k ] ) { continue; } ?>
				<details style="margin-top:10px">
					<summary><strong><?php echo esc_html( $t . ' (' . count( $groups[ $k ] ) . ')' ); ?></strong></summary>
					<table class="widefat striped" style="margin-top:6px">
						<thead><tr><th><?php esc_html_e( 'Project', 'doha-portfolio' ); ?></th><th><?php esc_html_e( 'Website', 'doha-portfolio' ); ?></th><th><?php echo 'drive' === $k ? esc_html__( 'Drive image', 'doha-portfolio' ) : esc_html__( 'Name to give its image in Drive', 'doha-portfolio' ); ?></th></tr></thead>
						<tbody>
						<?php foreach ( $groups[ $k ] as $r ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( get_edit_post_link( $r['id'] ) ); ?>"><?php echo esc_html( $r['title'] ); ?></a></td>
								<td><?php echo $r['url'] ? '<a href="' . esc_url( $r['url'] ) . '" target="_blank" rel="noopener">' . esc_html( preg_replace( '~^https?://(www\.)?~', '', untrailingslashit( $r['url'] ) ) ) . '</a>' : '—'; ?></td>
								<td><code><?php echo esc_html( 'drive' === $k ? $r['file'] : $r['target'] . '.jpg' ); ?></code><?php if ( ! empty( $r['via'] ) ) { echo ' <em>(' . esc_html__( 'found by the text inside the image', 'doha-portfolio' ) . ')</em>'; } ?><?php if ( 'drive' === $k && ! $r['renamed'] ) { echo ' → <code>' . esc_html( dp_drive_target_name( $r['id'], $r['file'] ) ) . '</code>'; } ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</details>
			<?php endforeach; ?>

			<?php if ( $map['unmatched'] || $map['ignored'] ) : ?>
				<details style="margin-top:10px" <?php echo $map['unmatched'] ? 'open' : ''; ?>>
					<summary><strong><?php echo esc_html( sprintf( __( 'Drive images that fit no project (%d): choose the project for each', 'doha-portfolio' ), count( $map['unmatched'] ) ) ); ?></strong></summary>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="dp_drive_assign">
						<?php wp_nonce_field( 'dp_drive_assign' ); ?>
						<table class="widefat striped" style="margin-top:6px">
							<thead><tr><th><?php esc_html_e( 'Drive image', 'doha-portfolio' ); ?></th><th><?php esc_html_e( 'Belongs to', 'doha-portfolio' ); ?></th></tr></thead>
							<tbody>
							<?php foreach ( $map['unmatched'] as $f ) : ?>
								<tr>
									<td><a href="<?php echo esc_url( 'https://drive.google.com/file/d/' . $f['id'] . '/view' ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $f['name'] ); ?></a></td>
									<td><select name="assign[<?php echo esc_attr( $f['id'] ); ?>]">
										<option value=""><?php esc_html_e( '— decide later —', 'doha-portfolio' ); ?></option>
										<option value="skip"><?php esc_html_e( 'Not a project screenshot', 'doha-portfolio' ); ?></option>
									</select></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
						<template id="dp-proj-opts"><optgroup label="<?php esc_attr_e( 'Projects with no image in Drive', 'doha-portfolio' ); ?>"><?php foreach ( $rows as $r ) { if ( 'drive' !== $r['status'] ) { echo '<option value="' . esc_attr( $r['id'] ) . '">' . esc_html( $r['title'] ) . '</option>'; } } ?></optgroup></template>
						<script>(function(){ var t = document.getElementById('dp-proj-opts'); document.querySelectorAll('#dp-drive select[name^="assign"]').forEach(function(s){ s.appendChild(t.content.cloneNode(true)); }); })();</script>
						<p><button class="button button-primary"><?php esc_html_e( 'Save choices', 'doha-portfolio' ); ?></button>
						<?php if ( $map['ignored'] || get_option( 'dp_drive_manual' ) ) : ?> <button class="button" name="reset" value="1"><?php esc_html_e( 'Forget all choices made by hand', 'doha-portfolio' ); ?></button><?php endif; ?></p>
					</form>
				</details>
			<?php endif; ?>

			<?php if ( $map['extras'] ) : ?>
				<details style="margin-top:10px">
					<summary><strong><?php echo esc_html( sprintf( __( 'Projects with more than one image in Drive (%d): the first is used', 'doha-portfolio' ), count( $map['extras'] ) ) ); ?></strong></summary>
					<table class="widefat striped" style="margin-top:6px"><tbody>
					<?php foreach ( $map['extras'] as $pid => $list ) : ?>
						<tr><td><?php echo esc_html( isset( $titles[ $pid ] ) ? $titles[ $pid ] : $pid ); ?></td><td><code><?php echo esc_html( $map['matched'][ $pid ]['name'] ); ?></code><br><small><?php echo esc_html( implode( ' · ', wp_list_pluck( $list, 'name' ) ) ); ?></small></td></tr>
					<?php endforeach; ?>
					</tbody></table>
				</details>
			<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>

		<h3><?php esc_html_e( 'More', 'doha-portfolio' ); ?></h3>
		<details style="margin:8px 0">
			<summary><strong><?php esc_html_e( 'Put the screenshots the site already has into Drive', 'doha-portfolio' ); ?></strong></summary>
			<p><?php esc_html_e( 'Downloads the screenshots the site has but Drive does not, in zip files of 25, already named the way Drive expects. Unzip them and drag the images into the Drive folder.', 'doha-portfolio' ); ?></p>
			<p><button class="button" id="dp-drive-export"><?php esc_html_e( 'Prepare the zip files', 'doha-portfolio' ); ?></button></p>
			<ul id="dp-drive-links"></ul>
		</details>
		<details style="margin:8px 0">
			<summary><strong><?php esc_html_e( 'Free up hosting space', 'doha-portfolio' ); ?></strong></summary>
			<p><?php esc_html_e( 'Keeps only the image sizes the site shows for each project screenshot, deletes old screenshots no project uses, and deletes the old images-pack screenshot copies. Images you added by hand, "as delivered" galleries, certificates and logos are not touched.', 'doha-portfolio' ); ?></p>
			<p><button class="button" id="dp-free-space"><?php esc_html_e( 'Free up space', 'doha-portfolio' ); ?></button></p>
		</details>
	</div>
	<script>
	(function(){
		var nonce = '<?php echo esc_js( $nonce ); ?>', st = document.getElementById('dp-drive-status') || document.createElement('p');
		if (!st.parentNode) document.getElementById('dp-drive').appendChild(st);
		function post(data){ var fd = new FormData(); fd.append('_ajax_nonce', nonce); for (var k in data) fd.append(k, data[k]); return fetch(ajaxurl, {method:'POST', body:fd, credentials:'same-origin'}).then(function(r){ return r.json(); }); }
		function mb(b){ return (b / 1048576).toFixed(1) + ' MB'; }
		function fail(btn){ return function(){ st.textContent = 'The server stopped answering. Click again to continue.'; btn.disabled = false; }; }
		var rn = document.getElementById('dp-drive-rename');
		if (rn) rn.addEventListener('click', function(e){
			e.preventDefault(); rn.disabled = true; var done = 0;
			(function next(more){
				post({action:'dp_drive_rename', more: more ? 1 : ''}).then(function(j){
					if (!j.success) { st.textContent = j.data && j.data.message ? j.data.message : 'Error'; rn.disabled = false; return; }
					done += j.data.renamed; st.textContent = 'Renamed in Drive: ' + done + (j.data.left ? ' · left: ' + j.data.left : '');
					if (j.data.errors && j.data.errors.length) { st.textContent += ' · ' + j.data.errors.join(' · '); rn.disabled = false; return; }
					if (j.data.left > 0) return next(true);
					st.innerHTML = '<strong>Done.</strong> ' + done + ' images renamed in Drive. Reloading the report…'; setTimeout(function(){ location.reload(); }, 1200);
				}).catch(fail(rn));
			})(false);
		});
		var oc = document.getElementById('dp-drive-ocr');
		if (oc) oc.addEventListener('click', function(e){
			e.preventDefault(); oc.disabled = true; var done = 0;
			(function next(){
				post({action:'dp_drive_ocr'}).then(function(j){
					if (!j.success) { st.textContent = j.data && j.data.message ? j.data.message : 'Error'; oc.disabled = false; return; }
					done += j.data.read; st.textContent = 'Images read: ' + done + ' · left: ' + j.data.left;
					if (j.data.left > 0 && j.data.read) return next();
					st.innerHTML = '<strong>Done.</strong> Reloading the report…'; setTimeout(function(){ location.reload(); }, 1000);
				}).catch(fail(oc));
			})();
		});
		var sync = document.getElementById('dp-drive-sync');
		if (sync) sync.addEventListener('click', function(e){
			e.preventDefault(); sync.disabled = true; var saved = 0, errors = 0;
			(function next(off){
				post({action:'dp_drive_sync', offset: off}).then(function(j){
					if (!j.success) { st.textContent = j.data && j.data.message ? j.data.message : 'Error'; sync.disabled = false; return; }
					saved += j.data.saved; errors += j.data.errors;
					st.textContent = 'Images brought from Drive: ' + saved + (errors ? ' (' + errors + ' could not be read)' : '') + ' · left: ' + j.data.left;
					if (j.data.left > 0 && (j.data.saved || j.data.errors)) return next(1);
					st.innerHTML = '<strong>Done.</strong> ' + saved + ' images brought from Drive' + (errors ? ', ' + errors + ' could not be read' : '') + '.'; sync.disabled = false;
				}).catch(fail(sync));
			})(0);
		});
		var ex = document.getElementById('dp-drive-export'), links = document.getElementById('dp-drive-links');
		if (ex) ex.addEventListener('click', function(e){
			e.preventDefault(); ex.disabled = true; links.innerHTML = '';
			(function next(part){
				post({action:'dp_drive_export', part: part}).then(function(j){
					if (!j.success) { st.textContent = j.data && j.data.message ? j.data.message : 'Error'; ex.disabled = false; return; }
					if (j.data.done) { st.textContent = j.data.total ? 'All zip files are ready.' : 'Nothing to export: every screenshot is already in Drive.'; ex.disabled = false; return; }
					var li = document.createElement('li'); li.innerHTML = '<a href="' + j.data.url + '">Part ' + j.data.part + ' of ' + j.data.parts + '</a>'; links.appendChild(li);
					st.textContent = 'Preparing zip ' + j.data.part + ' of ' + j.data.parts + '…';
					next(part + 1);
				}).catch(fail(ex));
			})(0);
		});
		var fs = document.getElementById('dp-free-space');
		if (fs) fs.addEventListener('click', function(e){
			e.preventDefault(); fs.disabled = true; var freed = 0;
			(function next(step, off){
				post({action:'dp_free_space', step: step, offset: off}).then(function(j){
					if (!j.success) { st.textContent = 'Error'; fs.disabled = false; return; }
					freed += j.data.freed || 0;
					st.textContent = (step === 'sizes' ? 'Slimming screenshots ' + j.data.at + ' / ' + j.data.total : step === 'orphans' ? 'Deleting unused screenshots' : 'Deleting old copies') + ' · freed so far: ' + mb(freed);
					if (j.data.step === 'done') { st.innerHTML = '<strong>Done.</strong> Freed ' + mb(freed) + '.'; fs.disabled = false; return; }
					next(j.data.step, j.data.offset);
				}).catch(fail(fs));
			})('sizes', 0);
		});
	})();
	</script>
	<?php
}
