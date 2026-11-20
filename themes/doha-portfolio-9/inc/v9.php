<?php
/**
 * Design 9 (Premium Dark, logo green): the home page, rendered by the theme only.
 * Its own header, footer, stylesheet and script; no GSAP, no Elementor on this page.
 * Later phases move the other pages over the same way (see dp9_on()).
 *
 * @package Doha_Portfolio
 */

defined( 'ABSPATH' ) || exit;

require __DIR__ . '/v9-icons.php';

/** Pages that use design 9. Phase 1: the home page. */
function dp9_on() {
	return is_front_page() && apply_filters( 'dp9_home', true );
}

/* ---------- This theme next to the old one ----------
 * Theme 9 is a separate theme folder, so the old theme stays installed as a backup.
 * Until it has settings of its own, it reads the old theme's Customizer settings (name, socials, prices…),
 * which also makes Appearance → Themes → Live Preview show the real content.
 */
add_filter( 'default_option_theme_mods_' . get_option( 'stylesheet' ), 'dp9_inherit_mods' );
add_filter( 'default_option_theme_mods_doha-portfolio-9', 'dp9_inherit_mods' );
function dp9_inherit_mods( $default ) {
	$old = get_option( 'theme_mods_doha-portfolio' );
	return is_array( $old ) ? $old : $default;
}
add_action( 'after_switch_theme', function () {
	if ( ! get_option( 'theme_mods_doha-portfolio-9' ) && is_array( get_option( 'theme_mods_doha-portfolio' ) ) ) {
		update_option( 'theme_mods_doha-portfolio-9', get_option( 'theme_mods_doha-portfolio' ) );
	}
} );

/* ---------- Assets ---------- */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! dp9_on() ) { return; }
	foreach ( array( 'dp-main', 'wp-block-library', 'classic-theme-styles', 'global-styles', 'elementor-frontend', 'elementor-post-' . get_queried_object_id() ) as $h ) { wp_dequeue_style( $h ); }
	foreach ( array( 'dp-main', 'gsap', 'gsap-scrolltrigger', 'gsap-flip' ) as $h ) { wp_dequeue_script( $h ); }
	wp_enqueue_style( 'dp9', DP_URI . '/assets/css/v9.css', array(), DP_VERSION );
	wp_enqueue_script( 'dp9', DP_URI . '/assets/js/v9.js', array(), DP_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
}, 50 );

add_action( 'wp_head', function () {
	if ( ! dp9_on() ) { return; }
	echo '<link rel="preload" href="' . esc_url( DP_URI . '/assets/fonts/Outfit-Latin.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	echo '<link rel="preload" href="' . esc_url( DP_URI . '/assets/img/v9/scene-hero.jpg' ) . '" as="image" fetchpriority="high">' . "\n";
}, 1 );

/* ---------- Data ---------- */

/** The site's numbers, from the Doha Portfolio Core plugin (one source for every page). */
function dp9_stats() {
	if ( function_exists( 'dpc_stats' ) ) { return dpc_stats(); }
	$c = wp_count_posts( 'project' );
	return array( 'projects' => isset( $c->publish ) ? (int) $c->publish : 0, 'countries' => (int) dp_country_count(), 'years' => (string) dp_opt( 'years' ) );
}

/** Featured projects that have a screenshot, newest first: the work shown on the home page. */
function dp9_featured( $n = 4 ) {
	$with = array_values( array_filter( dp_get_projects( true ), function ( $p ) { return has_post_thumbnail( $p ); } ) );
	if ( count( $with ) < $n ) {
		$ids  = wp_list_pluck( $with, 'ID' );
		$more = array_filter( dp_get_projects(), function ( $p ) use ( $ids ) { return has_post_thumbnail( $p ) && ! in_array( $p->ID, $ids, true ); } );
		$with = array_merge( $with, array_values( $more ) );
	}
	return array_slice( $with, 0, $n );
}

/**
 * The top of a full-page screenshot, cropped to a wide frame (1600×1000 by default), made once and kept.
 * Big project images then show the first screen of the site, never the middle of the page.
 */
function dp9_top_image( $post_id, $w = 1600, $h = 1000 ) {
	static $made = 0;
	$att = get_post_thumbnail_id( $post_id );
	if ( ! $att ) { return dp_project_image( $post_id, 'dp-large' ); }
	$key  = 'dp9-top-' . $w . 'x' . $h;
	$meta = wp_get_attachment_metadata( $att );
	if ( is_array( $meta ) && ! empty( $meta['sizes'][ $key ] ) ) { return (string) wp_get_attachment_image_url( $att, $key ); }
	$fallback = (string) wp_get_attachment_image_url( $att, 'large' );
	if ( $made >= 3 || get_post_meta( $att, '_dp9_top_failed', true ) || ! is_array( $meta ) ) { return $fallback; }
	$orig = function_exists( 'wp_get_original_image_path' ) ? wp_get_original_image_path( $att ) : get_attached_file( $att );
	if ( ! $orig || ! file_exists( $orig ) ) { return $fallback; }
	$made++;
	$ed = wp_get_image_editor( $orig );
	if ( is_wp_error( $ed ) || ! method_exists( $ed, 'make_subsize' ) ) { update_post_meta( $att, '_dp9_top_failed', 1 ); return $fallback; }
	$ed->set_quality( 80 );
	$sub = $ed->make_subsize( array( 'width' => $w, 'height' => $h, 'crop' => array( 'center', 'top' ) ) );
	if ( is_wp_error( $sub ) || empty( $sub['file'] ) ) { update_post_meta( $att, '_dp9_top_failed', 1 ); return $fallback; }
	$meta['sizes'][ $key ] = $sub;
	wp_update_attachment_metadata( $att, $meta );
	return (string) wp_get_attachment_image_url( $att, $key );
}

/** Long screenshots (800px wide) for the laptop screens: the newest projects first. */
function dp9_screens( $n = 3, $skip = array() ) {
	$out = array();
	foreach ( dp_recent_shots( $n + 2, $skip ) as $p ) {
		$src = dp_long_image_url( get_post_thumbnail_id( $p->ID ) );
		if ( ! $src ) { $src = dp_project_image( $p->ID ); }
		if ( $src ) { $out[] = array( 'id' => $p->ID, 'src' => $src, 'title' => get_the_title( $p ), 'host' => dp_host( get_post_meta( $p->ID, '_dp_url', true ) ) ); }
		if ( count( $out ) >= $n ) { break; }
	}
	return $out;
}

function dp9_journey() {
	return apply_filters( 'dp9_journey', array(
		array( 'dates' => '2009 — 2025', 'role' => array( 'en' => 'Freelance WordPress Developer', 'ar' => 'مطوّرة ووردبريس مستقلة' ), 'where' => array( 'en' => 'Clients across the Gulf, Europe and Asia', 'ar' => 'عملاء في الخليج وأوروبا وآسيا' ) ),
		array( 'dates' => '2015 — 2017', 'role' => array( 'en' => 'Senior Web Developer', 'ar' => 'مطوّرة ويب أولى' ), 'where' => array( 'en' => 'ProITs · Dubai', 'ar' => 'ProITs · دبي' ) ),
		array( 'dates' => '2017 — 2026', 'role' => array( 'en' => 'Senior WordPress Developer', 'ar' => 'مطوّرة ووردبريس أولى' ), 'where' => array( 'en' => 'Sketchat Studio · Damascus', 'ar' => 'Sketchat Studio · دمشق' ) ),
		array( 'dates' => array( 'en' => '2025 — Now', 'ar' => '2025 — الآن' ), 'role' => array( 'en' => 'Senior WordPress Developer', 'ar' => 'مطوّرة ووردبريس أولى' ), 'where' => array( 'en' => 'EchoVision · Dubai', 'ar' => 'EchoVision · دبي' ), 'now' => true ),
	) );
}

function dp9_services() {
	$out = array();
	foreach ( dp_service_pages() as $slug => $s ) {
		$map   = array( 'code' => 'wordpress', 'shopping-cart' => 'cart', 'lightning' => 'lightning', 'shield-check' => 'shield', 'arrows-left-right' => 'migrate', 'lifebuoy' => 'lifebuoy' );
		$ico   = isset( $s['icon'], $map[ $s['icon'] ] ) ? $map[ $s['icon'] ] : 'code';
		$out[] = array( 'title' => dp_t( $s['title'] ), 'url' => dp_service_url( $slug ), 'icon' => $ico );
	}
	return $out;
}

/* ---------- Pieces ---------- */

function dp9_arrow() {
	return '<svg class="dp9-arr" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
}

/**
 * A photographed laptop with live project screenshots on its screen.
 * $quad: the screen corners in % of the photo (top-left, top-right, bottom-right, bottom-left);
 * v9.js maps a 16:10 "screen" onto them and scrolls each screenshot from the top, one after another.
 */
function dp9_scene( $img, $w, $h, $quad, $screens, $class = '', $alt = '' ) {
	?>
	<figure class="dp9-scene <?php echo esc_attr( $class ); ?>" data-quad="<?php echo esc_attr( $quad ); ?>">
		<img class="dp9-scene-img" src="<?php echo esc_url( DP_URI . '/assets/img/v9/' . $img ); ?>" width="<?php echo (int) $w; ?>" height="<?php echo (int) $h; ?>" alt="<?php echo esc_attr( $alt ); ?>" <?php echo 'scene-hero.jpg' === $img ? 'fetchpriority="high"' : 'loading="lazy"'; ?> decoding="async">
		<?php if ( $screens ) : ?>
			<div class="dp9-screen" aria-hidden="true">
				<?php foreach ( $screens as $i => $s ) : ?>
					<div class="dp9-slide<?php echo 0 === $i ? ' is-on' : ''; ?>" data-title="<?php echo esc_attr( $s['title'] ); ?>"><img src="<?php echo esc_url( $s['src'] ); ?>" alt="" <?php echo $i ? 'loading="lazy"' : ''; ?> decoding="async"></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</figure>
	<?php
}

function dp9_skyline() {
	?>
	<svg class="dp9-skyline" aria-hidden="true" viewBox="0 0 1440 230" preserveAspectRatio="xMidYMax meet" fill="currentColor" stroke="url(#dp9-sky)" stroke-width="1" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke">
		<defs><linearGradient id="dp9-sky" gradientUnits="userSpaceOnUse" x1="0" y1="20" x2="0" y2="220"><stop offset="0" stop-color="#a8efcf" stop-opacity=".9"/><stop offset="1" stop-color="#3ccf98" stop-opacity=".25"/></linearGradient></defs>
		<path d="M0 220H1440" stroke="rgba(60,207,152,.35)"/>
		<path d="M52 220V78h16v142M52 78l8-28 8 28M50 110h20"/>
		<path d="M96 220v-64h154v64z"/><path d="M112 156q61-94 122 0z"/><path d="M173 94V70m-5 0a5 5 0 1 0 10 0"/>
		<path d="M128 220v-34a10 10 0 0 1 20 0v34m50 0v-34a10 10 0 0 1 20 0v34"/>
		<path d="M278 220V84h16v136M278 84l8-26 8 26M276 116h20"/>
		<path d="M340 220q4-50 12-92m0 0q-26-6-44 10m44-10q22-16 44-6m-44 6q-8-22-30-32m30 32q16-22 36-26m-36 26q4-26-2-40"/>
		<path d="M410 220v-50h60v50zm72 0v-36h48v36z"/>
		<path d="M540 220v-40h40v40z"/>
		<path d="M600 220V96h14v124M600 96l7-24 7 24M598 128h18"/>
		<path d="M640 220v-56h120v56z"/><path d="M652 164q48-74 96 0z"/><path d="M700 112V94m-5 0a5 5 0 1 0 10 0"/>
		<path d="M664 220v-28a9 9 0 0 1 18 0v28m36 0v-28a9 9 0 0 1 18 0v28"/>
		<path d="M790 220v-92h96v92z"/><path d="M816 220v-48a22 22 0 0 1 44 0v48"/><path d="M790 146h96"/>
		<path d="M1000 220v-80h46v80zm52 0V112h38v108z"/>
		<path d="M1110 220l6-150 8-30 4-34 4 34 8 30 6 150z"/>
		<path d="M1160 220v-70h40v70z"/>
		<path d="M940 220q4-48 12-88m0 0q-26-6-44 10m44-10q22-16 44-6m-44 6q-8-22-30-32m30 32q16-22 36-26"/>
		<path d="M1236 220v-60h136v60z"/><path d="M1250 160q54-78 108 0z"/><path d="M1304 106V86m-5 0a5 5 0 1 0 10 0"/>
		<path d="M1390 220V92h14v128M1390 92l7-26 7 26M1388 124h18"/>
	</svg>
	<?php
}

/** The contact form in design 9 (same handler, spam checks and messages as the classic form). */
function dp9_contact_form() {
	$sent  = isset( $_GET['dp_sent'] ) ? sanitize_key( $_GET['dp_sent'] ) : ''; // phpcs:ignore
	$email = dp_opt( 'email' );
	$needs = array(
		'New website'          => array( 'en' => 'New website', 'ar' => 'موقع جديد' ),
		'WooCommerce store'    => array( 'en' => 'Online store', 'ar' => 'متجر إلكتروني' ),
		'E-learning'           => array( 'en' => 'E-learning', 'ar' => 'منصة تعليمية' ),
		'Speed or security'    => array( 'en' => 'Speed or security', 'ar' => 'السرعة أو الحماية' ),
		'Care and support'     => array( 'en' => 'Care plan', 'ar' => 'صيانة ودعم' ),
	);
	?>
	<form class="dp9-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>#contact">
		<?php if ( 'ok' === $sent ) : ?>
			<p class="dp9-note is-ok" role="status"><?php echo esc_html( dp_t( array( 'en' => 'Thank you. Your message is on its way — I reply within one working day.', 'ar' => 'شكرًا لك. وصلت رسالتك، وسأرد خلال يوم عمل واحد.' ) ) ); ?></p>
		<?php elseif ( 'err' === $sent ) : ?>
			<p class="dp9-note is-err" role="alert"><?php echo esc_html( dp_t( array( 'en' => 'The message could not be sent. Please email me directly:', 'ar' => 'تعذّر إرسال الرسالة. راسلني مباشرة على:' ) ) ); ?> <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
		<?php endif; ?>
		<input type="hidden" name="action" value="dp_contact">
		<input type="hidden" name="dp_t" value="<?php echo esc_attr( time() ); ?>">
		<input type="hidden" name="dp_back" value="<?php echo esc_url( remove_query_arg( 'dp_sent' ) ); ?>">
		<?php wp_nonce_field( 'dp_contact', 'dp_nonce' ); ?>
		<p class="dp9-hp" aria-hidden="true"><label>Website <input type="text" name="dp_website" tabindex="-1" autocomplete="off"></label></p>
		<label class="dp9-field"><span><?php echo esc_html( dp_t( array( 'en' => 'Your name', 'ar' => 'الاسم' ) ) ); ?></span><input type="text" name="dp_name" required maxlength="100" autocomplete="name"></label>
		<label class="dp9-field"><span><?php echo esc_html( dp_t( array( 'en' => 'Email', 'ar' => 'البريد الإلكتروني' ) ) ); ?></span><input type="email" name="dp_email" required maxlength="150" autocomplete="email" dir="ltr"></label>
		<fieldset class="dp9-needs">
			<legend><?php echo esc_html( dp_t( array( 'en' => 'I need', 'ar' => 'أحتاج' ) ) ); ?></legend>
			<?php $first = true; foreach ( $needs as $val => $label ) : ?>
				<label><input type="radio" name="dp_topic" value="<?php echo esc_attr( $val ); ?>" <?php checked( $first ); ?>><span><?php echo esc_html( dp_t( $label ) ); ?></span></label>
			<?php $first = false; endforeach; ?>
		</fieldset>
		<label class="dp9-field"><span><?php echo esc_html( dp_t( array( 'en' => 'About the project', 'ar' => 'عن المشروع' ) ) ); ?></span><textarea name="dp_message" rows="3" required maxlength="5000"></textarea></label>
		<button class="dp9-btn" type="submit"><?php echo esc_html( dp_t( array( 'en' => 'Send message', 'ar' => 'أرسل الرسالة' ) ) ); ?> <?php echo dp9_arrow(); // phpcs:ignore ?></button>
	</form>
	<?php
}

/* ---------- The home page ---------- */

function dp9_home() {
	$stats  = dp9_stats();
	$work   = dp9_featured( 4 );
	$hero   = dp9_screens( 3 );
	$desk   = dp9_screens( 1, wp_list_pluck( $hero, 'id' ) );
	$wa     = function_exists( 'dp_wa_number' ) ? dp_wa_number() : '';
	$email  = dp_opt( 'email' );
	$name   = dp_opt( 'name' );
	$parts  = preg_split( '/\s+/', trim( $name ), 2 );
	$ar     = dp_is_ar();
	$archive = get_post_type_archive_link( 'project' );
	?>
	<main id="main" class="dp9-main">

	<section class="dp9-hero">
		<div class="dp9-glow dp9-glow-hero" aria-hidden="true"></div>
		<div class="dp9-wrap dp9-hero-grid">
			<div class="dp9-hero-text">
				<p class="dp9-badge dp9-in"><span class="dp9-dot" aria-hidden="true"></span><?php echo esc_html( dp_t( array( 'en' => 'Available for new projects', 'ar' => 'متاحة لمشاريع جديدة' ) ) ); ?></p>
				<h1 class="dp9-name dp9-in">
					<span><?php echo esc_html( $parts[0] ); ?></span>
					<?php if ( ! empty( $parts[1] ) ) : ?><span class="dp9-accent"><?php echo esc_html( $parts[1] ); ?></span><?php endif; ?>
					<span class="dp9-sr"> — <?php echo esc_html( dp_opt( 'role' ) ); ?></span>
				</h1>
				<p class="dp9-claim dp9-in"><?php echo esc_html( dp_t( array( 'en' => 'Building websites that work as hard as you do.', 'ar' => 'أبني مواقع تعمل بجدّ مثلك تمامًا.' ) ) ); ?></p>
				<span class="dp9-rule dp9-in" aria-hidden="true"></span>
				<p class="dp9-lead dp9-in"><?php echo esc_html( dp_t( array( 'en' => 'Senior WordPress developer. Custom themes, plugins and WooCommerce stores for companies in the UAE, the Gulf and beyond — fast, secure and cared for after launch.', 'ar' => 'مطوّرة ووردبريس أولى. قوالب وإضافات مخصّصة ومتاجر WooCommerce لشركات في الإمارات والخليج وخارجها — سريعة وآمنة ومع متابعة بعد الإطلاق.' ) ) ); ?></p>
				<div class="dp9-actions dp9-in">
					<a class="dp9-btn" href="#work"><?php echo esc_html( dp_t( array( 'en' => 'View my work', 'ar' => 'شاهد أعمالي' ) ) ); ?> <?php echo dp9_arrow(); // phpcs:ignore ?></a>
					<a class="dp9-btn is-ghost" href="#contact"><?php echo esc_html( dp_t( array( 'en' => 'Start a project', 'ar' => 'ابدأ مشروعك' ) ) ); ?></a>
				</div>
			</div>
			<div class="dp9-hero-visual dp9-in">
				<?php dp9_scene( 'scene-hero.jpg', 1400, 758, '45.13,16.89 87.97,8.44 82.85,79.56 38.99,75.78', $hero, 'is-hero', dp_t( array( 'en' => 'A laptop showing recent websites built by Doha Mokdad', 'ar' => 'حاسوب محمول يعرض مواقع حديثة من تنفيذ ضحى مقداد' ) ) ); ?>
				<?php if ( $hero ) : ?><p class="dp9-now" aria-hidden="true"><span><?php echo esc_html( dp_t( array( 'en' => 'Now showing', 'ar' => 'يُعرض الآن' ) ) ); ?></span> <strong data-now><?php echo esc_html( $hero[0]['title'] ); ?></strong></p><?php endif; ?>
			</div>
		</div>
		<ul class="dp9-wrap dp9-facts dp9-in">
			<li><?php echo dp9_icon( 'trophy', 30 ); // phpcs:ignore ?><span><strong><?php echo esc_html( $stats['years'] ); ?></strong><?php echo esc_html( dp_t( array( 'en' => 'Years experience', 'ar' => 'سنة خبرة' ) ) ); ?></span></li>
			<li><?php echo dp9_icon( 'wordpress', 28 ); // phpcs:ignore ?><span><strong><?php echo esc_html( $stats['projects'] ); ?></strong><?php echo esc_html( dp_t( array( 'en' => 'Websites delivered', 'ar' => 'موقعًا منجزًا' ) ) ); ?></span></li>
			<li><?php echo dp9_icon( 'globe', 30 ); // phpcs:ignore ?><span><strong><?php echo esc_html( $stats['countries'] ); ?></strong><?php echo esc_html( dp_t( array( 'en' => 'Countries', 'ar' => 'دولة' ) ) ); ?></span></li>
			<li><?php echo dp9_icon( 'headset', 30 ); // phpcs:ignore ?><span><strong><?php echo esc_html( dp_t( array( 'en' => 'Ongoing', 'ar' => 'دعم' ) ) ); ?></strong><?php echo esc_html( dp_t( array( 'en' => 'Support after launch', 'ar' => 'مستمر بعد الإطلاق' ) ) ); ?></span></li>
		</ul>
	</section>

	<?php if ( $work ) : ?>
	<section id="work" class="dp9-section dp9-work">
		<div class="dp9-wrap">
			<div class="dp9-head">
				<h2 class="dp9-h2"><?php echo esc_html( dp_t( array( 'en' => 'Selected', 'ar' => 'أعمال' ) ) ); ?> <span class="dp9-accent"><?php echo esc_html( dp_t( array( 'en' => 'work', 'ar' => 'مختارة' ) ) ); ?></span></h2>
				<?php if ( $archive ) : ?><a class="dp9-link" href="<?php echo esc_url( $archive ); ?>"><?php echo esc_html( sprintf( dp_t( array( 'en' => 'All %d projects', 'ar' => 'كل المشاريع (%d)' ) ), $stats['projects'] ) ); ?> <?php echo dp9_arrow(); // phpcs:ignore ?></a><?php endif; ?>
			</div>
			<div class="dp9-projects">
				<?php foreach ( $work as $i => $p ) :
					$type = function_exists( 'dp_project_type_name' ) ? dp_project_type_name( $p->ID ) : '';
					$ctry = get_post_meta( $p->ID, '_dp_country', true );
					$meta = implode( ' · ', array_filter( array( $type, $ctry ? dp_x( $ctry ) : '' ) ) );
					?>
					<a class="dp9-project<?php echo $i < 2 ? ' is-wide' : ''; ?>" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
						<span class="dp9-project-pic"><img src="<?php echo esc_url( dp9_top_image( $p->ID ) ); ?>" alt="<?php echo esc_attr( sprintf( dp_t( array( 'en' => '%s website', 'ar' => 'موقع %s' ) ), get_the_title( $p ) ) ); ?>" loading="lazy" decoding="async" width="1600" height="1000"></span>
						<span class="dp9-project-cap">
							<span class="dp9-num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
							<span class="dp9-project-name"><?php echo esc_html( dp_x( get_the_title( $p ) ) ); ?></span>
							<?php if ( $meta ) : ?><span class="dp9-project-meta"><?php echo esc_html( $meta ); ?></span><?php endif; ?>
							<span class="dp9-go" aria-hidden="true"><?php echo dp9_arrow(); // phpcs:ignore ?></span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<section id="services" class="dp9-section dp9-services">
		<div class="dp9-wrap dp9-svc">
			<div class="dp9-svc-head">
				<p class="dp9-kicker"><?php echo esc_html( dp_t( array( 'en' => 'What I do', 'ar' => 'ماذا أقدّم' ) ) ); ?></p>
				<h2 class="dp9-h2"><?php if ( dp_is_ar() ) : ?><span class="dp9-strong">خدماتي</span><?php else : ?>My <span class="dp9-strong">services</span><?php endif; ?></h2>
				<p class="dp9-svc-lead"><?php echo esc_html( dp_t( array( 'en' => 'From the first page to years of care after launch — end-to-end WordPress work.', 'ar' => 'من أول صفحة إلى سنوات من المتابعة بعد الإطلاق — عمل ووردبريس متكامل.' ) ) ); ?></p>
				<p class="dp9-price"><span><?php echo esc_html( dp_t( array( 'en' => 'Websites from', 'ar' => 'المواقع تبدأ من' ) ) ); ?></span> <strong dir="ltr">AED 1,650</strong> <span class="dp9-muted" dir="ltr">≈ $450</span></p>
				<p class="dp9-muted dp9-price-note"><?php echo esc_html( dp_t( array( 'en' => 'Stores, platforms and agency work by quote.', 'ar' => 'المتاجر والمنصات وعمل الوكالات بعرض سعر.' ) ) ); ?></p>
			</div>
			<ul class="dp9-svc-grid">
				<?php foreach ( dp9_services() as $s ) : ?>
					<li><a href="<?php echo esc_url( $s['url'] ? $s['url'] : '#contact' ); ?>"><span class="dp9-svc-ico"><?php echo dp9_icon( $s['icon'], 34 ); // phpcs:ignore ?></span><span class="dp9-svc-name"><?php echo esc_html( $s['title'] ); ?></span><?php echo dp9_arrow(); // phpcs:ignore ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<?php if ( function_exists( 'dp_solved_items' ) ) : $cases = dp_solved_items(); ?>
	<section id="solved" class="dp9-section dp9-solved">
		<div class="dp9-wrap">
			<div class="dp9-head">
				<div>
					<p class="dp9-kicker"><?php echo esc_html( dp_t( array( 'en' => 'Problems I’ve solved', 'ar' => 'مشاكل حللتها' ) ) ); ?></p>
					<h2 class="dp9-h2"><?php echo esc_html( dp_t( array( 'en' => 'When a site breaks,', 'ar' => 'عندما يتعطّل الموقع،' ) ) ); ?> <span class="dp9-strong"><?php echo esc_html( dp_t( array( 'en' => 'I find out why.', 'ar' => 'أعرف السبب.' ) ) ); ?></span></h2>
				</div>
			</div>
			<div class="dp9-cases">
				<?php foreach ( array_slice( $cases, 0, 3 ) as $n => $c ) : ?>
					<details class="dp9-case">
						<summary>
							<span class="dp9-num"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span>
							<span class="dp9-case-main"><span class="dp9-case-tag"><?php echo esc_html( dp_t( $c['tag'] ) ); ?></span><span class="dp9-case-title"><?php echo esc_html( dp_t( $c['title'] ) ); ?></span><span class="dp9-case-result"><?php echo esc_html( dp_t( $c['result'] ) ); ?></span></span>
							<span class="dp9-plus" aria-hidden="true"></span>
						</summary>
						<div class="dp9-case-body">
							<p><strong><?php echo esc_html( dp_t( array( 'en' => 'The problem', 'ar' => 'المشكلة' ) ) ); ?></strong> <?php echo esc_html( dp_t( $c['problem'] ) ); ?></p>
							<p><strong><?php echo esc_html( dp_t( array( 'en' => 'What I did', 'ar' => 'ما فعلته' ) ) ); ?></strong> <?php echo esc_html( dp_t( $c['did'] ) ); ?></p>
						</div>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<section class="dp9-section dp9-process">
		<div class="dp9-wrap dp9-split is-media">
			<?php dp9_scene( 'scene-desk.jpg', 1200, 672, '37.67,32.59 65.83,34.23 60.25,75.15 32.33,71.88', $desk, 'is-desk' ); ?>
			<div>
				<p class="dp9-kicker"><?php echo esc_html( dp_t( array( 'en' => 'How I work', 'ar' => 'طريقة العمل' ) ) ); ?></p>
				<h2 class="dp9-h2"><?php echo esc_html( dp_t( array( 'en' => 'A calm, clear path', 'ar' => 'طريق هادئ وواضح' ) ) ); ?> <span class="dp9-strong"><?php echo esc_html( dp_t( array( 'en' => 'to launch.', 'ar' => 'حتى الإطلاق.' ) ) ); ?></span></h2>
				<ol class="dp9-steps">
					<?php
					$steps = array(
						array( array( 'en' => 'Discover', 'ar' => 'نفهم' ), array( 'en' => 'your goals, scope and price', 'ar' => 'أهدافك والنطاق والسعر' ) ),
						array( array( 'en' => 'Plan', 'ar' => 'نخطّط' ), array( 'en' => 'pages, features, approach', 'ar' => 'الصفحات والميزات وطريقة التنفيذ' ) ),
						array( array( 'en' => 'Build', 'ar' => 'نبني' ), array( 'en' => 'on a live staging link', 'ar' => 'على رابط تجريبي تتابعه مباشرة' ) ),
						array( array( 'en' => 'Launch & care', 'ar' => 'نطلق ونتابع' ), array( 'en' => 'I stay on after', 'ar' => 'وأبقى معك بعدها' ) ),
					);
					foreach ( $steps as $n => $st ) :
						?>
						<li><span class="dp9-num"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span><span><strong><?php echo esc_html( dp_t( $st[0] ) ); ?></strong> <em>— <?php echo esc_html( dp_t( $st[1] ) ); ?></em></span></li>
					<?php endforeach; ?>
				</ol>
			</div>
		</div>
	</section>

	<section id="journey" class="dp9-section dp9-journey">
		<div class="dp9-wrap">
			<div class="dp9-head">
				<div>
					<p class="dp9-kicker"><?php echo esc_html( dp_t( array( 'en' => 'My journey', 'ar' => 'مسيرتي' ) ) ); ?></p>
					<h2 class="dp9-h2"><?php echo esc_html( dp_t( array( 'en' => 'Fifteen years,', 'ar' => 'خمسة عشر عامًا،' ) ) ); ?> <span class="dp9-strong"><?php echo esc_html( dp_t( array( 'en' => 'one craft.', 'ar' => 'وحرفة واحدة.' ) ) ); ?></span></h2>
				</div>
				<?php $cv = dp_opt( 'cv_url' ); if ( $cv ) : ?><a class="dp9-link" href="<?php echo esc_url( $cv ); ?>" target="_blank" rel="noopener"><?php echo esc_html( dp_t( array( 'en' => 'Download CV', 'ar' => 'حمّل السيرة الذاتية' ) ) ); ?> <?php echo dp9_arrow(); // phpcs:ignore ?></a><?php endif; ?>
			</div>
			<ol class="dp9-timeline">
				<?php foreach ( dp9_journey() as $j ) : ?>
					<li class="<?php echo empty( $j['now'] ) ? '' : 'is-now'; ?>">
						<span class="dp9-tl-dot" aria-hidden="true"></span>
						<span class="dp9-tl-date"><?php echo esc_html( is_array( $j['dates'] ) ? dp_t( $j['dates'] ) : $j['dates'] ); ?></span>
						<span class="dp9-tl-role"><?php echo esc_html( dp_t( $j['role'] ) ); ?></span>
						<span class="dp9-tl-where"><?php echo esc_html( dp_t( $j['where'] ) ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<section id="contact" class="dp9-section dp9-end">
		<div class="dp9-glow dp9-glow-end" aria-hidden="true"></div>
		<div class="dp9-wrap dp9-end-grid">
			<div class="dp9-end-inner">
				<p class="dp9-kicker"><?php echo esc_html( dp_t( array( 'en' => 'Have a project in mind?', 'ar' => 'لديك مشروع؟' ) ) ); ?></p>
				<h2 class="dp9-end-title"><?php echo esc_html( dp_t( array( 'en' => 'Let’s build', 'ar' => 'لنبنِ معًا' ) ) ); ?> <span class="dp9-accent"><?php echo esc_html( dp_t( array( 'en' => 'something great.', 'ar' => 'شيئًا مميزًا.' ) ) ); ?></span></h2>
				<p class="dp9-end-lead"><?php echo esc_html( dp_t( array( 'en' => 'Tell me what you need. I reply within one working day, with a clear next step and an honest price.', 'ar' => 'أخبرني بما تحتاجه. أرد خلال يوم عمل واحد، بخطوة واضحة وسعر صادق.' ) ) ); ?></p>
				<ul class="dp9-reach">
					<?php if ( $email ) : ?><li><i class="dp9-ring-ico"><?php echo dp9_icon( 'mail', 22 ); // phpcs:ignore ?></i><span><?php echo esc_html( dp_t( array( 'en' => 'Email', 'ar' => 'البريد' ) ) ); ?></span><a href="mailto:<?php echo esc_attr( $email ); ?>" dir="ltr"><?php echo esc_html( $email ); ?></a></li><?php endif; ?>
					<?php if ( $wa ) : ?><li><i class="dp9-ring-ico"><?php echo dp9_icon( 'whatsapp', 22 ); // phpcs:ignore ?></i><span>WhatsApp</span><a href="<?php echo esc_url( 'https://wa.me/' . $wa ); ?>" dir="ltr">+<?php echo esc_html( $wa ); ?></a></li><?php endif; ?>
					<?php $li = dp_opt( 'linkedin' ); if ( $li ) : ?><li><i class="dp9-ring-ico"><?php echo dp9_icon( 'linkedin', 22 ); // phpcs:ignore ?></i><span>LinkedIn</span><a href="<?php echo esc_url( $li ); ?>" target="_blank" rel="noopener" dir="ltr"><?php echo esc_html( preg_replace( '#^https?://(www\.)?#', '', untrailingslashit( $li ) ) ); ?></a></li><?php endif; ?>
				</ul>
				<p class="dp9-where"><span class="dp9-dot" aria-hidden="true"></span><?php echo esc_html( dp_t( array( 'en' => 'Based in Damascus · working with UAE & GCC clients · UTC+3', 'ar' => 'مقيمة في دمشق · أعمل مع عملاء في الإمارات والخليج · UTC+3' ) ) ); ?></p>
			</div>
			<div id="message" class="dp9-form-wrap">
				<p class="dp9-form-title"><?php echo esc_html( dp_t( array( 'en' => 'Write a message', 'ar' => 'اكتب رسالة' ) ) ); ?></p>
				<?php dp9_contact_form(); ?>
			</div>
		</div>
	</section>

	</main>
	<?php
}
