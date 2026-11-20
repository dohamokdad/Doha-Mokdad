<?php
/**
 * Website screenshots: fetch each project's homepage screenshot (WordPress.com mShots)
 * and save it to the Media Library as the project's featured image.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Try to save a screenshot for one project.
 * @return string 'saved' | 'pending' (still being generated, try again) | 'error'
 */
function dp_save_screenshot( $post_id, $force = false ) {
	if ( ! $force && has_post_thumbnail( $post_id ) ) { return 'saved'; }
	// An image in the Google Drive folder always wins over a live screenshot.
	if ( function_exists( 'dp_drive_folder_id' ) && dp_drive_folder_id() ) {
		$dr = dp_drive_sync_one( $post_id );
		if ( 'saved' === $dr || ( 'same' === $dr && ! $force ) ) { return 'saved'; }
	}
	$url     = dp_project_ref( $post_id );
	if ( ! $url ) { return 'error'; }
	$bundled = dp_bundled_shot( $url );
	if ( ! $bundled && ! get_post_meta( $post_id, '_dp_url', true ) ) { return 'offline'; } // no website to capture
	$ext     = 'jpg';
	if ( $bundled ) {
		// Screenshot supplied with the theme (data/screenshots/).
		$body = file_get_contents( $bundled ); // phpcs:ignore
		$ext  = strtolower( pathinfo( $bundled, PATHINFO_EXTENSION ) );
	} elseif ( dp_shot_key_set() ) {
		// Full page, taken only after the page has loaded, been scrolled (lazy images, entrance animations) and settled.
		$body = dp_apiflash_shot( $url );
		if ( in_array( $body, array( 'pending', 'error' ), true ) ) { return $body; }
	} else {
		$res = wp_remote_get( dp_mshots_url( $url, 1200, true ), array( 'timeout' => 20, 'redirection' => 3 ) );
		if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) { return 'pending'; }
		$type = wp_remote_retrieve_header( $res, 'content-type' );
		$body = wp_remote_retrieve_body( $res );
		// While mShots is still rendering it returns a small GIF placeholder.
		if ( false === strpos( (string) $type, 'jpeg' ) || strlen( $body ) < 8000 ) { return 'pending'; }
	}
	// Stored slim: 1200px wide, only the sizes the site shows. The old automatic screenshot is deleted.
	return dp_store_slim_shot( $post_id, $body, '_dp_auto_shot' ) ? 'saved' : 'error';
}

/** ApiFlash access key (Portfolio Tools > Website screenshots). With a key, screenshots wait for the page to load. */
function dp_shot_key_set() { return '' !== trim( (string) get_option( 'dp_apiflash_key', '' ) ); }

/**
 * Full-page screenshot from ApiFlash, taken after the page has loaded and gone quiet, scrolled once to the bottom
 * so lazy images and scroll animations appear, then waited on. Returns the JPEG, or 'pending' / 'error'.
 */
function dp_apiflash_shot( $url ) {
	if ( function_exists( 'set_time_limit' ) ) { @set_time_limit( 150 ); } // phpcs:ignore
	// Show elements that page builders keep hidden until they scroll into view.
	$css = '.elementor-invisible,.wow,[data-aos],.e-animated,.animated{visibility:visible!important;opacity:1!important;transform:none!important;animation:none!important}'
		. '.elementor-motion-effects-element{transform:none!important}#preloader,.preloader,.loader-wrap,.page-loader{display:none!important}';
	$api = add_query_arg( array(
		'access_key'        => rawurlencode( trim( (string) get_option( 'dp_apiflash_key' ) ) ),
		'url'               => rawurlencode( $url ),
		'format'            => 'jpeg',
		'quality'           => 82,
		'width'             => 1440,
		'height'            => 900,
		'full_page'         => 'true',
		'scroll_page'       => 'true',
		'wait_until'        => 'network_idle',
		'delay'             => 5,
		'no_cookie_banners' => 'true',
		'no_ads'            => 'true',
		'fresh'             => 'true',
		'css'               => rawurlencode( $css ),
		'response_type'     => 'image',
	), 'https://api.apiflash.com/v1/urltoimage' );
	$res = wp_remote_get( $api, array( 'timeout' => 90 ) );
	if ( is_wp_error( $res ) ) { return 'pending'; }
	$code = (int) wp_remote_retrieve_response_code( $res );
	if ( 200 !== $code ) {
		update_option( 'dp_apiflash_last_error', $code . ': ' . wp_strip_all_tags( substr( (string) wp_remote_retrieve_body( $res ), 0, 200 ) ), false );
		return $code >= 500 || 429 === $code ? 'pending' : 'error';
	}
	$body = (string) wp_remote_retrieve_body( $res );
	if ( strlen( $body ) < 8000 ) { return 'pending'; }
	// Shrink to 1200px wide and cap very long pages, so files stay reasonable.
	require_once ABSPATH . 'wp-admin/includes/file.php';
	$tmp = wp_tempnam( 'dp-shot.jpg' );
	file_put_contents( $tmp, $body ); // phpcs:ignore
	$ed = wp_get_image_editor( $tmp );
	if ( ! is_wp_error( $ed ) ) {
		$ed->set_quality( 82 );
		$ed->resize( 1200, 0 );
		$sz = $ed->get_size();
		if ( $sz['height'] > 9000 ) { $ed->crop( 0, 0, $sz['width'], 9000 ); }
		$saved = $ed->save( $tmp . '.jpg', 'image/jpeg' );
		if ( ! is_wp_error( $saved ) ) { $body = (string) file_get_contents( $saved['path'] ); @unlink( $saved['path'] ); } // phpcs:ignore
	}
	@unlink( $tmp ); // phpcs:ignore
	delete_option( 'dp_apiflash_last_error' );
	return $body;
}

/** Does the site answer at all? (8 second limit; DNS errors, timeouts, 5xx and 404 count as offline.) */
function dp_site_reachable( $url ) {
	if ( ! $url ) { return false; }
	$args = array( 'timeout' => 8, 'redirection' => 3, 'sslverify' => false, 'user-agent' => 'Mozilla/5.0 (compatible; DohaPortfolio/1.0)' );
	$res  = wp_remote_head( $url, $args );
	if ( ! is_wp_error( $res ) && 405 === (int) wp_remote_retrieve_response_code( $res ) ) {
		$res = wp_remote_get( $url, $args + array( 'limit_response_size' => 2048 ) );
	}
	if ( is_wp_error( $res ) ) { return false; }
	$code = (int) wp_remote_retrieve_response_code( $res );
	return $code > 0 && $code < 500 && ! in_array( $code, array( 404, 410 ), true );
}

/* ---------- Admin page: Projects → Portfolio Tools ---------- */
add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=project', __( 'Portfolio Tools', 'doha-portfolio' ), __( 'Portfolio Tools', 'doha-portfolio' ), 'manage_options', 'dp-tools', 'dp_render_tools' );
} );

function dp_render_tools() {
	$all     = dp_get_projects();
	$missing = array();
	$offline = array();
	foreach ( $all as $p ) {
		if ( has_post_thumbnail( $p->ID ) || dp_is_changed( $p->ID ) ) { continue; }
		// Sites found offline in the last 7 days are skipped (they show their logo instead).
		$off = (int) get_post_meta( $p->ID, '_dp_offline', true );
		if ( $off && $off > time() - WEEK_IN_SECONDS ) { $offline[] = $p->ID; } else { $missing[] = $p->ID; }
	}
	// Screenshots taken automatically from the live site (not supplied in an images pack): can be taken again.
	$retake = array();
	foreach ( $all as $p ) {
		$tid = get_post_thumbnail_id( $p->ID );
		if ( $tid && get_post_meta( $tid, '_dp_auto_shot', true ) && ! dp_bundled_shot( dp_project_ref( $p->ID ) ) && ! dp_is_changed( $p->ID ) && get_post_meta( $p->ID, '_dp_url', true ) ) { $retake[] = $p->ID; }
	}
	$nonce = wp_create_nonce( 'dp_tools' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Portfolio Tools', 'doha-portfolio' ); ?></h1>

		<?php dp_render_drive_card(); ?>

		<div class="card" style="max-width:720px">
			<h2><?php esc_html_e( 'Website screenshots', 'doha-portfolio' ); ?></h2>
			<p><?php echo esc_html( sprintf( __( '%1$d of %2$d projects still need a screenshot.', 'doha-portfolio' ), count( $missing ), count( $all ) ) ); ?></p>
			<p><?php esc_html_e( 'Works in batches of 25 sites, 6 at a time. Each screenshot is saved as soon as it is ready, so you can stop at any time and continue later. Sites that do not open are detected in a few seconds and skipped; they show their logo (or your Featured Image) instead.', 'doha-portfolio' ); ?></p>
			<?php if ( $offline ) : ?><p><?php echo esc_html( sprintf( __( '%d sites were offline at the last check and are skipped for a week.', 'doha-portfolio' ), count( $offline ) ) ); ?></p><?php endif; ?>
			<p>
				<button class="button button-primary" id="dp-start" <?php disabled( empty( $missing ) ); ?>><?php esc_html_e( 'Save the next 25 screenshots', 'doha-portfolio' ); ?></button>
				<button class="button" id="dp-stop" style="display:none"><?php esc_html_e( 'Stop', 'doha-portfolio' ); ?></button>
				<?php if ( $offline ) : ?><button class="button" id="dp-offline" data-ids="<?php echo esc_attr( wp_json_encode( $offline ) ); ?>"><?php esc_html_e( 'Check offline sites again', 'doha-portfolio' ); ?></button><?php endif; ?>
			</p>
			<progress id="dp-progress" value="0" max="<?php echo esc_attr( max( 1, count( $missing ) ) ); ?>" style="width:100%;display:none"></progress>
			<p id="dp-status"></p>
			<hr>
			<h3><?php esc_html_e( 'Wait for the page to load', 'doha-portfolio' ); ?></h3>
			<?php if ( dp_shot_key_set() ) : ?>
				<p><strong><?php esc_html_e( 'On: screenshots are taken with ApiFlash, after each page has fully loaded, been scrolled to the bottom (so lazy images and scroll animations appear) and waited on for 5 seconds. Full page, 1200px wide.', 'doha-portfolio' ); ?></strong></p>
				<?php $dp_err = get_option( 'dp_apiflash_last_error' ); if ( $dp_err ) : ?><p style="color:#b32d2e"><?php echo esc_html( sprintf( __( 'Last ApiFlash error: %s', 'doha-portfolio' ), $dp_err ) ); ?></p><?php endif; ?>
			<?php else : ?>
				<p><?php esc_html_e( 'Off: screenshots come from WordPress.com mShots, which captures the page as soon as it opens, so images and animated sections can be missing. To take them only after the page has fully loaded, create a free account at apiflash.com (100 screenshots a month free) and paste your Access Key here.', 'doha-portfolio' ); ?></p>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="dp_save_shot_key">
				<?php wp_nonce_field( 'dp_save_shot_key' ); ?>
				<input type="text" class="regular-text" name="dp_apiflash_key" value="<?php echo esc_attr( get_option( 'dp_apiflash_key', '' ) ); ?>" placeholder="ApiFlash Access Key" autocomplete="off">
				<button class="button"><?php esc_html_e( 'Save', 'doha-portfolio' ); ?></button>
			</form>
			<?php if ( dp_shot_key_set() && $retake ) : ?>
				<p style="margin-top:12px"><?php echo esc_html( sprintf( __( '%d screenshots were taken earlier without waiting for the page. Retake them in batches of 25 (each retake uses one ApiFlash screenshot).', 'doha-portfolio' ), count( $retake ) ) ); ?></p>
				<p><button class="button button-primary" id="dp-retake" data-ids="<?php echo esc_attr( wp_json_encode( $retake ) ); ?>"><?php esc_html_e( 'Retake the next 25', 'doha-portfolio' ); ?></button></p>
			<?php endif; ?>
		</div>

		<?php
		$no_case = array();
		foreach ( $all as $p ) { if ( ! get_post_meta( $p->ID, '_dp_detected', true ) ) { $no_case[] = $p->ID; } }
		$all_ids = wp_list_pluck( $all, 'ID' );
		?>
		<div class="card" style="max-width:720px">
			<h2><?php esc_html_e( 'Case studies (theme, plugins, features, UI/UX, phases)', 'doha-portfolio' ); ?></h2>
			<p><?php echo esc_html( sprintf( __( '%1$d of %2$d projects have not been scanned yet.', 'doha-portfolio' ), count( $no_case ), count( $all ) ) ); ?></p>
			<p><?php esc_html_e( 'Visits each live website from your server, detects the WordPress theme, plugins and technologies, and drafts the overview, key features, UI/UX notes and delivery phases. Only empty fields are filled: anything you edited by hand stays as it is. Review the drafts on each project before sharing.', 'doha-portfolio' ); ?></p>
			<p>
				<button class="button button-primary" id="dp-case-start" data-ids="<?php echo esc_attr( wp_json_encode( $no_case ) ); ?>" <?php disabled( empty( $no_case ) ); ?>><?php esc_html_e( 'Build case studies', 'doha-portfolio' ); ?></button>
				<button class="button" id="dp-case-all" data-ids="<?php echo esc_attr( wp_json_encode( $all_ids ) ); ?>"><?php esc_html_e( 'Re-scan all (keeps your edits)', 'doha-portfolio' ); ?></button>
			</p>
			<progress id="dp-case-progress" value="0" max="1" style="width:100%;display:none"></progress>
			<p id="dp-case-status"></p>
		</div>
		<script>
		(function(){
			var nonce = '<?php echo esc_js( $nonce ); ?>', bar = document.getElementById('dp-case-progress'), st = document.getElementById('dp-case-status');
			function run(btn){
				var ids = JSON.parse(btn.getAttribute('data-ids') || '[]'); if (!ids.length) return;
				document.getElementById('dp-case-start').disabled = true; document.getElementById('dp-case-all').disabled = true;
				bar.style.display = 'block'; bar.max = ids.length; bar.value = 0;
				var i = 0, active = 0, done = 0, off = 0;
				function next(){
					if (i >= ids.length && active === 0) { st.innerHTML = '<strong>Done.</strong> ' + done + ' projects drafted' + (off ? ', ' + off + ' sites were offline (drafted from the project type only).' : '.'); return; }
					while (active < 3 && i < ids.length) {
						(function(id){
							active++;
							var fd = new FormData(); fd.append('action','dp_build_case'); fd.append('post', id); fd.append('_ajax_nonce', nonce);
							fetch(ajaxurl, {method:'POST', body:fd, credentials:'same-origin'}).then(function(r){return r.json();}).catch(function(){return {};}).then(function(j){
								active--; done++; bar.value = done;
								if (!(j && j.data && j.data.status === 'done')) off++;
								st.textContent = done + ' / ' + ids.length + ' scanned';
								next();
							});
						})(ids[i++]);
					}
				}
				next();
			}
			document.getElementById('dp-case-start').addEventListener('click', function(e){ e.preventDefault(); run(this); });
			document.getElementById('dp-case-all').addEventListener('click', function(e){ e.preventDefault(); run(this); });
		})();
		</script>

		<?php
		$wb = array();
		foreach ( $all as $p ) { if ( get_post_meta( $p->ID, '_dp_delivered', true ) && ! dp_gallery_ids( $p->ID ) ) { $wb[] = $p->ID; } }
		?>
		<div class="card" style="max-width:720px">
			<h2><?php esc_html_e( 'As delivered (Wayback Machine)', 'doha-portfolio' ); ?></h2>
			<p><?php esc_html_e( 'For sites the client has changed since you delivered them. Set a "Delivered" month on each of those projects, then click below: the archived copy closest to that date is captured and shown instead of today\'s site. You can also upload your own screenshots on each project.', 'doha-portfolio' ); ?></p>
			<p><?php echo esc_html( sprintf( __( '%d projects have a delivery date and no as-delivered screenshot yet.', 'doha-portfolio' ), count( $wb ) ) ); ?></p>
			<p><button class="button" id="dp-wb-start" data-ids="<?php echo esc_attr( wp_json_encode( $wb ) ); ?>" <?php disabled( empty( $wb ) ); ?>><?php esc_html_e( 'Capture archived versions', 'doha-portfolio' ); ?></button></p>
			<p id="dp-wb-status"></p>
		</div>
		<script>
		(function(){
			var b = document.getElementById('dp-wb-start'), st = document.getElementById('dp-wb-status'), nonce = '<?php echo esc_js( $nonce ); ?>';
			if (!b) return;
			b.addEventListener('click', function(e){
				e.preventDefault(); b.disabled = true;
				var queue = JSON.parse(b.getAttribute('data-ids') || '[]'), saved = 0, none = 0, round = 1;
				function pass(list){
					var pending = [], i = 0;
					(function next(){
						if (i >= list.length) {
							if (pending.length && round < 6) { round++; st.textContent = pending.length + ' still rendering, retrying in 30 seconds…'; setTimeout(function(){ pass(pending); }, 30000); }
							else { st.innerHTML = '<strong>Done.</strong> ' + saved + ' captured, ' + none + ' not found in the archive' + (pending.length ? ', ' + pending.length + ' did not finish (run again later).' : '.'); }
							return;
						}
						var id = list[i++], fd = new FormData(); fd.append('action','dp_wayback_bulk'); fd.append('post', id); fd.append('_ajax_nonce', nonce);
						fetch(ajaxurl, {method:'POST', body:fd, credentials:'same-origin'}).then(function(r){return r.json();}).catch(function(){return {};}).then(function(j){
							var s = j && j.data ? j.data.status : 'error';
							if (s === 'saved') saved++; else if (s === 'none') none++; else if (s === 'pending') pending.push(id);
							st.textContent = 'Round ' + round + ': ' + i + '/' + list.length + ' · ' + saved + ' captured'; next();
						});
					})();
				}
				pass(queue);
			});
		})();
		</script>

		<div class="card" style="max-width:720px">
			<h2>Pages</h2>
			<p>Fills the Resume, Works and Contact pages with the portfolio widgets (experience, skills, recognition, certificates, projects, contact) and adds "All projects" to the main menu. The previous content of each page is kept as a backup.</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="dp_fill_pages">
				<?php wp_nonce_field( 'dp_fill_pages' ); ?>
				<button class="button" onclick="return confirm('Replace the content of the Resume, Works and Contact pages with the portfolio widgets?');">Fill the pages again</button>
			</form>
		</div>

		<div class="card" style="max-width:720px">
			<h2><?php esc_html_e( 'Project list', 'doha-portfolio' ); ?></h2>
			<p><?php esc_html_e( 'Adds any project from the bundled list that is missing, and attaches any screenshots placed in the theme folder data/screenshots (named by domain, e.g. egt.group.jpg). Nothing is duplicated or overwritten.', 'doha-portfolio' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="dp_reimport">
				<?php wp_nonce_field( 'dp_reimport' ); ?>
				<button class="button"><?php esc_html_e( 'Import missing projects', 'doha-portfolio' ); ?></button>
			</form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px">
				<input type="hidden" name="action" value="dp_dedupe">
				<?php wp_nonce_field( 'dp_dedupe' ); ?>
				<button class="button"><?php esc_html_e( 'Remove duplicate projects', 'doha-portfolio' ); ?></button>
			</form>
		</div>
	</div>
	<script>
	(function(){
		var nonce = '<?php echo esc_js( $nonce ); ?>', BATCH = 25, PAR = <?php echo dp_shot_key_set() ? 2 : 6; ?>, MAXR = 3, WAIT = 20000, force = false;
		var left = <?php echo wp_json_encode( $missing ); ?>;
		var btn = document.getElementById('dp-start'), stop = document.getElementById('dp-stop'), off = document.getElementById('dp-offline');
		var bar = document.getElementById('dp-progress'), st = document.getElementById('dp-status');
		var stopped = false, saved = 0, offline = 0, total = left.length;
		function one(id, retry){
			var fd = new FormData(); fd.append('action','dp_fetch_shot'); fd.append('post', id); fd.append('_ajax_nonce', nonce); if (retry) fd.append('retry', '1'); if (force) fd.append('force', '1');
			return fetch(ajaxurl, {method:'POST', body:fd, credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){ return j && j.data ? j.data.status : 'error'; }).catch(function(){ return 'pending'; });
		}
		function pool(queue, retry, onEach){
			var i = 0, active = 0, pending = [];
			return new Promise(function(resolve){
				(function next(){
					if (stopped && active === 0) return resolve(pending);
					if (i >= queue.length && active === 0) return resolve(pending);
					while (!stopped && active < PAR && i < queue.length) {
						(function(id){
							active++;
							one(id, retry).then(function(s){ active--; if (s === 'pending') pending.push(id); onEach(s); next(); });
						})(queue[i++]);
					}
				})();
			});
		}
		function label(){ btn.textContent = left.length ? 'Continue: next ' + Math.min(BATCH, left.length) + ' (' + left.length + ' left)' : 'All done'; btn.disabled = !left.length; }
		function batch(queue, retry){
			var round = 1, checked = 0;
			function tick(s){ checked++; if (s === 'saved') { saved++; } else if (s === 'offline') { offline++; } bar.value = saved + offline; st.textContent = 'Checked ' + checked + ' · ' + saved + ' saved · ' + offline + ' offline (logo shown)'; }
			function go(q){
				return pool(q, retry || round > 1, tick).then(function(pending){
					if (pending.length && round < MAXR && !stopped) {
						round++; st.textContent = pending.length + ' sites are still rendering, checking again in 20 seconds…';
						return new Promise(function(r){ setTimeout(r, WAIT); }).then(function(){ return go(pending); });
					}
					return pending;
				});
			}
			return go(queue);
		}
		function start(queue, retry){
			stopped = false; btn.disabled = true; if (off) off.disabled = true; stop.style.display = ''; bar.style.display = 'block'; bar.max = Math.max(1, total);
			batch(queue, retry).then(function(pending){
				stop.style.display = 'none'; if (off) off.disabled = false;
				st.innerHTML = '<strong>' + (stopped ? 'Stopped.' : 'Batch finished.') + '</strong> ' + saved + ' saved, ' + offline + ' offline so far.' + (pending.length ? ' ' + pending.length + ' did not finish rendering; they will be tried again next time.' : '');
				left = left.concat(pending);
				label();
			});
		}
		if (btn) btn.addEventListener('click', function(e){ e.preventDefault(); force = false; var q = left.splice(0, BATCH); start(q, false); });
		if (stop) stop.addEventListener('click', function(e){ e.preventDefault(); stopped = true; stop.disabled = true; st.textContent = 'Stopping after the current sites…'; setTimeout(function(){ stop.disabled = false; }, 4000); });
		var re = document.getElementById('dp-retake');
		if (re) re.addEventListener('click', function(e){ e.preventDefault(); var all = JSON.parse(re.getAttribute('data-ids') || '[]'), q = all.splice(0, BATCH); re.setAttribute('data-ids', JSON.stringify(all)); force = true; total = q.length; saved = 0; offline = 0; bar.max = q.length; start(q, true); re.textContent = all.length ? 'Retake the next ' + Math.min(BATCH, all.length) + ' (' + all.length + ' left)' : 'All retaken'; if (!all.length) re.disabled = true; });
		if (off) off.addEventListener('click', function(e){ e.preventDefault(); var q = JSON.parse(off.getAttribute('data-ids') || '[]'); total += q.length; off.style.display = 'none'; start(q, false); });
	})();
	</script>
	<?php
}

add_action( 'wp_ajax_dp_fetch_shot', function () {
	check_ajax_referer( 'dp_tools' );
	if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array( 'status' => 'error' ) ); }
	$id = isset( $_POST['post'] ) ? absint( $_POST['post'] ) : 0;
	if ( ! $id || 'project' !== get_post_type( $id ) ) { wp_send_json_error( array( 'status' => 'error' ) ); }
	// First pass: a quick check that the site opens at all, so offline sites cost seconds, not minutes.
	if ( empty( $_POST['retry'] ) && ! dp_bundled_shot( dp_project_ref( $id ) ) ) {
		if ( ! dp_site_reachable( get_post_meta( $id, '_dp_url', true ) ) ) {
			update_post_meta( $id, '_dp_offline', time() );
			wp_send_json_success( array( 'status' => 'offline' ) );
		}
		delete_post_meta( $id, '_dp_offline' );
	}
	wp_send_json_success( array( 'status' => dp_save_screenshot( $id, ! empty( $_POST['force'] ) ) ) );
} );

add_action( 'admin_post_dp_save_shot_key', function () {
	check_admin_referer( 'dp_save_shot_key' );
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Not allowed' ); }
	update_option( 'dp_apiflash_key', sanitize_text_field( wp_unslash( isset( $_POST['dp_apiflash_key'] ) ? $_POST['dp_apiflash_key'] : '' ) ), false ); // phpcs:ignore
	delete_option( 'dp_apiflash_last_error' );
	wp_safe_redirect( admin_url( 'edit.php?post_type=project&page=dp-tools' ) );
	exit;
} );

add_action( 'admin_post_dp_reimport', function () {
	check_admin_referer( 'dp_reimport' );
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Not allowed' ); }
	set_transient( 'dp_import_notice', dp_import_projects(), 120 );
	dp_apply_bundled_cases();
	wp_safe_redirect( admin_url( 'edit.php?post_type=project&page=dp-tools' ) );
	exit;
} );

add_action( 'admin_post_dp_refresh_shot', function () {
	check_admin_referer( 'dp_refresh_shot' );
	$id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	if ( ! current_user_can( 'edit_post', $id ) ) { wp_die( 'Not allowed' ); }
	dp_save_screenshot( $id, true );
	wp_safe_redirect( get_edit_post_link( $id, 'url' ) );
	exit;
} );

add_action( 'admin_post_dp_dedupe', function () {
	check_admin_referer( 'dp_dedupe' );
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Not allowed' ); }
	set_transient( 'dp_dedupe_notice', dp_dedupe_projects(), 120 );
	wp_safe_redirect( admin_url( 'edit.php?post_type=project&page=dp-tools' ) );
	exit;
} );

/* ---------- Images pack: chunked upload (bypasses the hosting upload size limit) ---------- */
function dp_pack_tmp_dir() {
	$u = wp_upload_dir( null, false );
	$d = trailingslashit( $u['basedir'] ) . 'dp-pack-tmp';
	if ( ! is_dir( $d ) ) { wp_mkdir_p( $d ); @file_put_contents( $d . '/index.php', "<?php // Silence is golden.\n" ); } // phpcs:ignore
	return $d;
}

add_action( 'wp_ajax_dp_pack_chunk', function () {
	check_ajax_referer( 'dp_tools' );
	if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array( 'message' => 'Not allowed' ) ); }
	$id    = isset( $_POST['id'] ) ? preg_replace( '/[^a-z0-9]/', '', strtolower( (string) wp_unslash( $_POST['id'] ) ) ) : '';
	$index = isset( $_POST['index'] ) ? (int) $_POST['index'] : -1;
	$total = isset( $_POST['total'] ) ? (int) $_POST['total'] : 0;
	if ( ! $id || $index < 0 || $total < 1 || $index >= $total || empty( $_FILES['chunk']['tmp_name'] ) ) { wp_send_json_error( array( 'message' => 'Bad request' ) ); }
	$part = dp_pack_tmp_dir() . '/' . $id . '.part';
	$data = file_get_contents( $_FILES['chunk']['tmp_name'] ); // phpcs:ignore
	if ( false === $data ) { wp_send_json_error( array( 'message' => 'Could not read the uploaded piece' ) ); }
	// Pieces arrive in order; a retried piece is written at its own offset, so retries are safe.
	$fh = fopen( $part, 0 === $index ? 'wb' : 'cb' ); // phpcs:ignore
	if ( ! $fh ) { wp_send_json_error( array( 'message' => 'The uploads folder is not writable' ) ); }
	$size = isset( $_POST['size'] ) ? max( 65536, min( 4194304, (int) $_POST['size'] ) ) : 1048576;
	fseek( $fh, $index * $size );
	fwrite( $fh, $data ); // phpcs:ignore
	fclose( $fh ); // phpcs:ignore
	if ( $index < $total - 1 ) { wp_send_json_success( array( 'received' => $index + 1 ) ); }

	$n = dp_pack_install( $part );
	@unlink( $part ); // phpcs:ignore
	if ( is_wp_error( $n ) ) { wp_send_json_error( array( 'message' => $n->get_error_message() ) ); }
	wp_send_json_success( array( 'installed' => $n ) );
} );

/** Extract the pack into uploads/doha-portfolio-data. Only images and JSON are accepted. */
function dp_pack_install( $zip_file ) {
	if ( ! class_exists( 'ZipArchive' ) ) { return new WP_Error( 'zip', 'The server cannot open zip files (ZipArchive missing). Upload the folder with File Manager instead.' ); }
	$zip = new ZipArchive();
	if ( true !== $zip->open( $zip_file ) ) { return new WP_Error( 'zip', 'The file is not a valid zip. Please upload doha-portfolio-images.zip again.' ); }
	$u    = wp_upload_dir( null, false );
	$base = trailingslashit( $u['basedir'] );
	$n    = 0;
	for ( $i = 0; $i < $zip->numFiles; $i++ ) {
		$name = str_replace( '\\', '/', $zip->getNameIndex( $i ) );
		if ( 0 !== strpos( $name, 'doha-portfolio-data/' ) || false !== strpos( $name, '..' ) || '/' === substr( $name, -1 ) ) { continue; }
		if ( ! preg_match( '/\.(jpe?g|png|webp|json)$/i', $name ) ) { continue; }
		$dest = $base . $name;
		wp_mkdir_p( dirname( $dest ) );
		if ( false !== file_put_contents( $dest, $zip->getFromIndex( $i ) ) ) { $n++; } // phpcs:ignore
	}
	$zip->close();
	if ( ! $n ) { return new WP_Error( 'zip', 'No images were found in this zip. Make sure it is doha-portfolio-images.zip.' ); }
	return $n;
}

/** Apply the pack to projects in small steps (screenshots, galleries), so nothing times out. */
add_action( 'wp_ajax_dp_pack_apply', function () {
	check_ajax_referer( 'dp_tools' );
	if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array( 'message' => 'Not allowed' ) ); }
	$offset = isset( $_POST['offset'] ) ? max( 0, (int) $_POST['offset'] ) : 0;
	if ( 0 === $offset ) { dp_import_projects(); }
	$ids = get_posts( array( 'post_type' => 'project', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC' ) );
	dp_refresh_bundled_shots( array_slice( $ids, $offset, 8 ) );
	$next = min( count( $ids ), $offset + 8 );
	wp_send_json_success( array( 'next' => $next, 'total' => count( $ids ), 'done' => $next >= count( $ids ) ) );
} );
