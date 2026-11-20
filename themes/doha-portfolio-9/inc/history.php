<?php
/**
 * "As delivered" screenshots: show a project the way it looked when you handed it over,
 * even if the client has changed the live site since.
 *
 * Two sources:
 *  1. Your own screenshots (upload them in the project's "As delivered" box).
 *  2. The Wayback Machine (archive.org): finds the archived copy closest to the delivery date
 *     and captures a full-page screenshot of it.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function dp_gallery_ids( $post_id ) {
	return array_values( array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, '_dp_gallery', true ) ) ) ) );
}

function dp_is_changed( $post_id ) {
	return '1' === get_post_meta( $post_id, '_dp_changed', true ) && dp_gallery_ids( $post_id );
}

/** "2019-05" → "May 2019"; "2019" → "2019". */
function dp_delivered_label( $post_id ) {
	$d = trim( (string) get_post_meta( $post_id, '_dp_delivered', true ) );
	if ( preg_match( '/^(\d{4})-(\d{2})$/', $d, $m ) ) { return dp_x( gmdate( 'F Y', gmmktime( 0, 0, 0, (int) $m[2], 1, (int) $m[1] ) ) ); }
	return $d;
}

/* ---------- Meta box ---------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'dp_history', __( 'As delivered (screenshots from when you built it)', 'doha-portfolio' ), 'dp_render_history_box', 'project', 'normal', 'high' );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && 'project' === get_post_type() ) { wp_enqueue_media(); }
} );

function dp_render_history_box( $post ) {
	wp_nonce_field( 'dp_save_history', 'dp_history_nonce' );
	$ids = dp_gallery_ids( $post->ID );
	?>
	<p class="description"><?php esc_html_e( 'If the client changed the content or images after delivery, add screenshots of your version here. They replace the live screenshot on the card and the project page.', 'doha-portfolio' ); ?></p>
	<p>
		<label><strong><?php esc_html_e( 'Delivered', 'doha-portfolio' ); ?></strong><br>
		<input type="month" name="_dp_delivered" value="<?php echo esc_attr( get_post_meta( $post->ID, '_dp_delivered', true ) ); ?>"></label>
		&nbsp; <label><input type="checkbox" name="_dp_changed" value="1" <?php checked( get_post_meta( $post->ID, '_dp_changed', true ), '1' ); ?>> <?php esc_html_e( 'The client has changed the live site since delivery (show my screenshots instead)', 'doha-portfolio' ); ?></label>
	</p>
	<input type="hidden" name="_dp_gallery" id="dp-gallery-ids" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">
	<ul id="dp-gallery" style="display:flex;flex-wrap:wrap;gap:10px;margin:12px 0">
		<?php foreach ( $ids as $id ) : ?>
			<li data-id="<?php echo (int) $id; ?>" style="position:relative;margin:0"><?php echo wp_get_attachment_image( $id, 'medium', false, array( 'style' => 'width:120px;height:90px;object-fit:cover;object-position:top;border-radius:6px;border:1px solid #dcdcde' ) ); ?><button type="button" class="dp-remove button-link" aria-label="<?php esc_attr_e( 'Remove', 'doha-portfolio' ); ?>" style="position:absolute;top:2px;right:4px;color:#b32d2e;font-size:18px;text-decoration:none">×</button></li>
		<?php endforeach; ?>
	</ul>
	<p>
		<button type="button" class="button" id="dp-gallery-add"><?php esc_html_e( 'Add my screenshots', 'doha-portfolio' ); ?></button>
		<button type="button" class="button" id="dp-wayback" data-post="<?php echo (int) $post->ID; ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dp_wayback' ) ); ?>"><?php esc_html_e( 'Find it on the Wayback Machine', 'doha-portfolio' ); ?></button>
		<span id="dp-wayback-status" style="margin-left:8px"></span>
	</p>
	<p class="description"><?php esc_html_e( 'Tip: full-page screenshots look best (the cards scroll through them on hover). The first image is used on the card. Save the project after changes.', 'doha-portfolio' ); ?></p>
	<script>
	(function($){
		var $ids = $('#dp-gallery-ids'), $list = $('#dp-gallery');
		function sync(){ $ids.val($list.children('li').map(function(){ return $(this).data('id'); }).get().join(',')); }
		function add(id, url){ $list.append('<li data-id="'+id+'" style="position:relative;margin:0"><img src="'+url+'" style="width:120px;height:90px;object-fit:cover;object-position:top;border-radius:6px;border:1px solid #dcdcde"><button type="button" class="dp-remove button-link" style="position:absolute;top:2px;right:4px;color:#b32d2e;font-size:18px;text-decoration:none">×</button></li>'); sync(); }
		$list.on('click', '.dp-remove', function(){ $(this).closest('li').remove(); sync(); });
		var frame;
		$('#dp-gallery-add').on('click', function(e){
			e.preventDefault();
			if (!frame) {
				frame = wp.media({ title: 'As delivered screenshots', button: { text: 'Add' }, library: { type: 'image' }, multiple: 'add' });
				frame.on('select', function(){ frame.state().get('selection').each(function(a){ a = a.toJSON(); var u = (a.sizes && a.sizes.medium) ? a.sizes.medium.url : a.url; add(a.id, u); }); $('input[name="_dp_changed"]').prop('checked', true); });
			}
			frame.open();
		});
		$('#dp-wayback').on('click', function(e){
			e.preventDefault();
			var $b = $(this), $s = $('#dp-wayback-status'), tries = 0;
			var date = $('input[name="_dp_delivered"]').val();
			$b.prop('disabled', true); $s.text('Searching the archive…');
			(function go(){
				$.post(ajaxurl, { action: 'dp_wayback', post: $b.data('post'), date: date, _ajax_nonce: $b.data('nonce') }).done(function(r){
					var d = r && r.data ? r.data : {};
					if (d.status === 'saved') { add(d.id, d.thumb); $('input[name="_dp_changed"]').prop('checked', true); $s.html('Added the archived version from <strong>' + d.when + '</strong>. Save the project to keep it.'); $b.prop('disabled', false); }
					else if (d.status === 'pending' && tries < 8) { tries++; $s.text('Found a copy from ' + d.when + '. Capturing the screenshot, retry ' + tries + '/8…'); setTimeout(go, 15000); }
					else if (d.status === 'none') { $s.text('No archived copy found for this site. Add your own screenshots instead.'); $b.prop('disabled', false); }
					else { $s.text(d.message || 'Could not capture it now. Try again later.'); $b.prop('disabled', false); }
				}).fail(function(){ $s.text('Request failed. Try again.'); $b.prop('disabled', false); });
			})();
		});
	})(jQuery);
	</script>
	<?php
}

add_action( 'save_post_project', function ( $post_id ) {
	if ( ! isset( $_POST['dp_history_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dp_history_nonce'] ) ), 'dp_save_history' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	$date = isset( $_POST['_dp_delivered'] ) ? sanitize_text_field( wp_unslash( $_POST['_dp_delivered'] ) ) : '';
	update_post_meta( $post_id, '_dp_delivered', preg_match( '/^\d{4}(-\d{2})?$/', $date ) ? $date : '' );
	update_post_meta( $post_id, '_dp_changed', isset( $_POST['_dp_changed'] ) ? '1' : '0' );
	$ids = isset( $_POST['_dp_gallery'] ) ? array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['_dp_gallery'] ) ) ) ) ) : array();
	update_post_meta( $post_id, '_dp_gallery', implode( ',', $ids ) );
} );

/* ---------- Wayback Machine ---------- */

/** Find the archived snapshot closest to a date. Returns [url, timestamp] or null. */
function dp_wayback_find( $url, $date ) {
	$ts = preg_match( '/^(\d{4})-(\d{2})$/', (string) $date, $m ) ? $m[1] . $m[2] . '15' : ( preg_match( '/^\d{4}$/', (string) $date ) ? $date . '0615' : '' );
	$api = 'https://archive.org/wayback/available?url=' . rawurlencode( preg_replace( '#^https?://#', '', $url ) ) . ( $ts ? '&timestamp=' . $ts : '' );
	$res = wp_remote_get( $api, array( 'timeout' => 25 ) );
	if ( is_wp_error( $res ) ) { return new WP_Error( 'wayback', $res->get_error_message() ); }
	$j = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( empty( $j['archived_snapshots']['closest']['available'] ) ) { return null; }
	$c = $j['archived_snapshots']['closest'];
	// "if_" shows the archived page without the Wayback toolbar.
	$snap = preg_replace( '#/web/(\d+)/#', '/web/$1if_/', $c['url'], 1 );
	return array( 'url' => set_url_scheme( $snap, 'https' ), 'timestamp' => $c['timestamp'] );
}

/** Save image bytes as an attachment for a project. Returns attachment ID or 0. */
function dp_store_image( $post_id, $body, $ext, $title, $slug ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$up = wp_upload_bits( sanitize_file_name( $slug . '.' . $ext ), null, $body );
	if ( ! empty( $up['error'] ) ) { return 0; }
	$id = wp_insert_attachment( array( 'post_mime_type' => 'jpg' === $ext ? 'image/jpeg' : 'image/' . $ext, 'post_title' => $title, 'post_status' => 'inherit' ), $up['file'], $post_id );
	if ( ! $id || is_wp_error( $id ) ) { return 0; }
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $up['file'] ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	return $id;
}

/** Capture the archived version of a project. Returns ['status'=>saved|pending|none|error, ...]. */
function dp_wayback_capture( $post_id, $date = null ) {
	$url  = get_post_meta( $post_id, '_dp_url', true );
	$date = null === $date ? get_post_meta( $post_id, '_dp_delivered', true ) : $date;
	if ( ! $url ) { return array( 'status' => 'error', 'message' => 'No website URL on this project.' ); }
	$snap = get_transient( 'dp_wb_' . $post_id . '_' . md5( (string) $date ) );
	if ( ! $snap ) {
		$snap = dp_wayback_find( $url, $date );
		if ( is_wp_error( $snap ) ) { return array( 'status' => 'error', 'message' => 'The Wayback Machine did not respond: ' . $snap->get_error_message() ); }
		if ( ! $snap ) { return array( 'status' => 'none' ); }
		set_transient( 'dp_wb_' . $post_id . '_' . md5( (string) $date ), $snap, HOUR_IN_SECONDS );
	}
	$ts   = $snap['timestamp'];
	$when = date_i18n( 'F Y', mktime( 0, 0, 0, (int) substr( $ts, 4, 2 ), 1, (int) substr( $ts, 0, 4 ) ) );
	$res  = wp_remote_get( dp_mshots_url( $snap['url'], 1200, true ), array( 'timeout' => 45 ) );
	if ( is_wp_error( $res ) ) { return array( 'status' => 'pending', 'when' => $when ); }
	$type = (string) wp_remote_retrieve_header( $res, 'content-type' );
	$body = wp_remote_retrieve_body( $res );
	if ( false === strpos( $type, 'jpeg' ) || strlen( $body ) < 8000 ) { return array( 'status' => 'pending', 'when' => $when ); }
	$id = dp_store_image( $post_id, $body, 'jpg', get_the_title( $post_id ) . ' as delivered (' . $when . ')', dp_host( $url ) . '-' . substr( $ts, 0, 6 ) . '-archive' );
	if ( ! $id ) { return array( 'status' => 'error', 'message' => 'Could not save the image.' ); }
	$ids   = dp_gallery_ids( $post_id );
	$ids[] = $id;
	update_post_meta( $post_id, '_dp_gallery', implode( ',', array_unique( $ids ) ) );
	update_post_meta( $post_id, '_dp_changed', '1' );
	if ( ! get_post_meta( $post_id, '_dp_delivered', true ) ) { update_post_meta( $post_id, '_dp_delivered', substr( $ts, 0, 4 ) . '-' . substr( $ts, 4, 2 ) ); }
	return array( 'status' => 'saved', 'id' => $id, 'thumb' => wp_get_attachment_image_url( $id, 'medium' ), 'when' => $when );
}

add_action( 'wp_ajax_dp_wayback', function () {
	check_ajax_referer( 'dp_wayback' );
	$id = isset( $_POST['post'] ) ? absint( $_POST['post'] ) : 0;
	if ( ! $id || ! current_user_can( 'edit_post', $id ) ) { wp_send_json_error( array( 'status' => 'error' ) ); }
	$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
	wp_send_json_success( dp_wayback_capture( $id, $date ) );
} );

/* Bulk: Portfolio Tools → projects marked "changed" that have no screenshots yet. */
add_action( 'wp_ajax_dp_wayback_bulk', function () {
	check_ajax_referer( 'dp_tools' );
	$id = isset( $_POST['post'] ) ? absint( $_POST['post'] ) : 0;
	if ( ! $id || ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array( 'status' => 'error' ) ); }
	wp_send_json_success( dp_wayback_capture( $id ) );
} );
