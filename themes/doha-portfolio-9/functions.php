<?php
/**
 * Doha Mokdad Portfolio – theme bootstrap.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'DP_VERSION', '9.0.0' );
define( 'DP_DIR', get_template_directory() );
define( 'DP_URI', get_template_directory_uri() );

require DP_DIR . '/inc/i18n.php';
require DP_DIR . '/inc/icons.php';
require DP_DIR . '/inc/certificates-data.php';
require DP_DIR . '/inc/projects.php';
require DP_DIR . '/inc/importer.php';
require DP_DIR . '/inc/screenshots.php';
require DP_DIR . '/inc/drive-match.php';
require DP_DIR . '/inc/drive.php';
require DP_DIR . '/inc/customizer.php';
require DP_DIR . '/inc/sections.php';
require DP_DIR . '/inc/casestudy.php';
require DP_DIR . '/inc/history.php';
require DP_DIR . '/inc/elementor.php';
require DP_DIR . '/inc/pages.php';
require DP_DIR . '/inc/contact.php';
require DP_DIR . '/inc/service-pages.php';
require DP_DIR . '/inc/plans.php';
require DP_DIR . '/inc/solved.php';
require DP_DIR . '/inc/home-v2.php';
require DP_DIR . '/inc/seo.php';
require DP_DIR . '/inc/v9.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo' );
	add_image_size( 'dp-card', 800, 500, true );
	add_image_size( 'dp-tall', 800, 1600, false );
	add_image_size( 'dp-long', 800, 0, false ); // full-length screenshot, 800px wide, for hover-scroll cards
	add_image_size( 'dp-large', 1600, 1000, true );
	register_nav_menus( array( 'primary' => __( 'Primary menu', 'doha-portfolio' ) ) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'dp-main', DP_URI . '/assets/css/main.css', array(), DP_VERSION );
	$cdn = 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/';
	wp_enqueue_script( 'gsap', $cdn . 'gsap.min.js', array(), '3.12.5', true );
	wp_enqueue_script( 'gsap-scrolltrigger', $cdn . 'ScrollTrigger.min.js', array( 'gsap' ), '3.12.5', true );
	wp_enqueue_script( 'gsap-flip', $cdn . 'Flip.min.js', array( 'gsap' ), '3.12.5', true );
	wp_enqueue_script( 'dp-main', DP_URI . '/assets/js/main.js', array( 'gsap', 'gsap-scrolltrigger', 'gsap-flip' ), DP_VERSION, true );
} );

/* Self-hosted Geist (variable): preload the sans face so the hero title renders in it on first paint. */
add_action( 'wp_head', function () {
	if ( function_exists( 'dp9_on' ) && dp9_on() ) { return; }
	echo '<link rel="preload" href="' . esc_url( DP_URI . '/assets/fonts/Geist-Variable.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
}, 1 );

/* Favicon from the bundled logo until a Site Icon is set in the Customizer. */
add_action( 'wp_head', function () {
	if ( ! has_site_icon() ) {
		echo '<link rel="icon" type="image/png" href="' . esc_url( DP_URI . '/assets/img/site-icon.png' ) . '">' . "\n";
		echo '<link rel="apple-touch-icon" href="' . esc_url( DP_URI . '/assets/img/site-icon.png' ) . '">' . "\n";
	}
	echo '<meta name="theme-color" content="#0f7a61">' . "\n";
} );

/**
 * Image data (screenshots, as-delivered galleries, logos, certificates) lives in the "images pack",
 * installed to wp-content/uploads/doha-portfolio-data so theme updates never remove it.
 * The theme's own data/ folder is checked as a fallback.
 */
function dp_data_roots() {
	static $roots = null;
	if ( null === $roots ) {
		$u     = wp_upload_dir( null, false );
		$roots = array(
			array( trailingslashit( $u['basedir'] ) . 'doha-portfolio-data', set_url_scheme( trailingslashit( $u['baseurl'] ) . 'doha-portfolio-data' ) ),
			array( DP_DIR . '/data', DP_URI . '/data' ),
		);
	}
	return $roots;
}
function dp_data_path( $rel ) {
	foreach ( dp_data_roots() as $r ) { if ( file_exists( $r[0] . '/' . $rel ) ) { return $r[0] . '/' . $rel; } }
	return '';
}
function dp_data_url( $rel ) {
	foreach ( dp_data_roots() as $r ) { if ( file_exists( $r[0] . '/' . $rel ) ) { return $r[1] . '/' . $rel; } }
	return '';
}

/** A project's reference for bundled images: its website URL, or its key for projects without a website. */
function dp_project_ref( $post_id ) {
	$u = (string) get_post_meta( $post_id, '_dp_url', true );
	return '' !== $u ? $u : (string) get_post_meta( $post_id, '_dp_key', true );
}

/** Bundled screenshot for a URL: screenshots/{domain}.jpg|png|webp (www. ignored). */
/**
 * File name (without extension) for a project's bundled images: the host, plus the path when the
 * project lives in a sub-folder (atlas-data.sa/staging -> atlas-data.sa-staging), so it never
 * picks up the main site's screenshot.
 */
function dp_shot_key( $ref ) {
	$host = dp_host( $ref );
	if ( ! $host || $host === $ref ) { return $host; }
	$path = trim( (string) wp_parse_url( $ref, PHP_URL_PATH ), '/' );
	return $path ? $host . '-' . sanitize_title( str_replace( '/', '-', $path ) ) : $host;
}

function dp_bundled_shot( $url ) {
	$host = dp_shot_key( $url );
	if ( ! $host ) { return ''; }
	foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
		$f = dp_data_path( 'screenshots/' . $host . '.' . $ext );
		if ( $f ) { return $f; }
	}
	return '';
}

/** Theme option helper (Customizer values with defaults). */
function dp_opt( $key ) {
	$defaults = dp_defaults();
	$v = get_theme_mod( 'dp_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
	if ( function_exists( 'dp_is_ar' ) && dp_is_ar() && in_array( $key, array( 'name', 'role', 'tagline', 'location' ), true ) ) {
		$ar = get_theme_mod( 'dp_' . $key . '_ar', '' );
		return '' !== $ar ? $ar : dp_x( $v );
	}
	return $v;
}

function dp_defaults() {
	return array(
		'name'     => 'Doha Mokdad',
		'role'     => 'Senior WordPress Developer',
		'tagline'  => 'Senior WordPress developer. I build fast, reliable sites: custom themes, plugins and WooCommerce stores, cared for after launch.',
		'location' => 'Damascus, Syria. Available for remote projects and UAE-based roles.',
		'email'    => 'douha.sunshine@gmail.com',
		'phone'    => '+963 938 037 719',
		'linkedin' => 'https://www.linkedin.com/in/doha-mokdad',
		'years'    => '15+',
		'cv_url'   => DP_URI . '/assets/Doha_Mokdad_CV.pdf',
		'socials'  => "https://www.linkedin.com/in/doha-mokdad\nhttps://wa.me/963938037719\nhttps://t.me/+963938037719\nhttps://www.instagram.com/mokdad.doha/\nhttps://www.youtube.com/@dohamokdad6880\nhttps://www.facebook.com/dohamokdad",
	);
}

/** Social profiles from the Customizer list (one URL per line), with a label and icon per network. */
function dp_social_links() {
	$map   = array(
		'dribbble.com'  => array( 'dribbble', 'Dribbble' ),
		'instagram.com' => array( 'instagram', 'Instagram' ),
		'youtube.com'   => array( 'youtube', 'YouTube' ),
		'vimeo.com'     => array( 'vimeo', 'Vimeo' ),
		'facebook.com'  => array( 'facebook', 'Facebook' ),
		'twitter.com'   => array( 'x', 'X (Twitter)' ),
		'x.com'         => array( 'x', 'X (Twitter)' ),
		'linkedin.com'  => array( 'linkedin', 'LinkedIn' ),
		'wa.me'         => array( 'whatsapp', 'WhatsApp' ),
		'api.whatsapp.com' => array( 'whatsapp', 'WhatsApp' ),
		'whatsapp.com'  => array( 'whatsapp', 'WhatsApp' ),
		't.me'          => array( 'telegram', 'Telegram' ),
		'telegram.me'   => array( 'telegram', 'Telegram' ),
	);
	$out = array();
	foreach ( preg_split( '/[\r\n]+/', (string) dp_opt( 'socials' ) ) as $url ) {
		$url = trim( $url );
		if ( ! $url ) { continue; }
		$host = dp_host( $url );
		$net  = isset( $map[ $host ] ) ? $map[ $host ] : array( 'arrow-up-right', $host );
		$out[] = array( 'url' => $url, 'icon' => $net[0], 'label' => $net[1] );
	}
	return $out;
}

/** Row of social icon links. */
function dp_render_socials( $class = 'socials' ) {
	$links = dp_social_links();
	if ( ! $links ) { return; }
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $links as $l ) {
		echo '<li><a href="' . esc_url( $l['url'] ) . '" target="_blank" rel="noopener me" aria-label="' . esc_attr( $l['label'] ) . '" title="' . esc_attr( $l['label'] ) . '">' . dp_icon( $l['icon'], 20 ) . '</a></li>'; // phpcs:ignore
	}
	echo '</ul>';
}

/** Screenshot URL for a project: featured image if present, otherwise a live mShots preview (tall, for hover-scroll). */
function dp_project_image( $post_id, $size = 'dp-tall' ) {
	// Client changed the site after delivery: show the as-delivered screenshot.
	if ( function_exists( 'dp_is_changed' ) && dp_is_changed( $post_id ) ) {
		$g   = dp_gallery_ids( $post_id );
		$src = wp_get_attachment_image_url( $g[0], $size );
		if ( $src ) { return $src; }
	}
	if ( has_post_thumbnail( $post_id ) ) {
		if ( 'dp-tall' === $size ) {
			$long = dp_long_image_url( get_post_thumbnail_id( $post_id ) );
			if ( $long ) { return $long; }
		}
		// Full size: the uploaded original, not WordPress's "-scaled" copy (which squeezes long pages to a narrow strip).
		if ( 'full' === $size && function_exists( 'wp_get_original_image_url' ) ) {
			$orig = wp_get_original_image_url( get_post_thumbnail_id( $post_id ) );
			if ( $orig ) { return $orig; }
		}
		$src = get_the_post_thumbnail_url( $post_id, $size );
		if ( $src ) { return $src; }
	}
	$url = get_post_meta( $post_id, '_dp_url', true );
	if ( ! $url ) { return ''; }
	return dp_mshots_url( $url, in_array( $size, array( 'dp-large', 'full' ), true ) ? 1200 : 800, true );
}

/**
 * Card image for a screenshot: the whole page at 800px wide (the old 'dp-tall' size squeezed long pages
 * to a narrow, blurry strip). Made on demand for screenshots saved before this size existed, a few per page view.
 */
function dp_long_image_url( $att_id ) {
	static $made = 0;
	if ( ! $att_id ) { return ''; }
	$meta = wp_get_attachment_metadata( $att_id );
	if ( ! is_array( $meta ) || empty( $meta['width'] ) ) { return ''; }
	if ( ! empty( $meta['sizes']['dp-long'] ) ) { return (string) wp_get_attachment_image_url( $att_id, 'dp-long' ); }
	$orig = function_exists( 'wp_get_original_image_path' ) ? wp_get_original_image_path( $att_id ) : get_attached_file( $att_id );
	$size = $orig ? @getimagesize( $orig ) : false; // phpcs:ignore
	if ( ! $size ) { return ''; }
	// Short or narrow images: the original itself is the best card image.
	if ( $size[0] <= 820 || $size[1] / $size[0] <= 0.9 ) {
		return $size[1] / $size[0] > 0.9 ? (string) wp_get_original_image_url( $att_id ) : '';
	}
	if ( $made >= 4 || get_post_meta( $att_id, '_dp_long_failed', true ) ) { return (string) wp_get_original_image_url( $att_id ); }
	$made++;
	$editor = wp_get_image_editor( $orig );
	if ( is_wp_error( $editor ) || ! method_exists( $editor, 'make_subsize' ) ) { update_post_meta( $att_id, '_dp_long_failed', 1 ); return (string) wp_get_original_image_url( $att_id ); }
	$editor->set_quality( 82 );
	$sub = $editor->make_subsize( array( 'width' => 800, 'height' => 0, 'crop' => false ) );
	if ( is_wp_error( $sub ) || empty( $sub['file'] ) ) { update_post_meta( $att_id, '_dp_long_failed', 1 ); return (string) wp_get_original_image_url( $att_id ); }
	$meta['sizes']['dp-long'] = $sub;
	wp_update_attachment_metadata( $att_id, $meta );
	return (string) wp_get_attachment_image_url( $att_id, 'dp-long' );
}

/** Bundled logo for a project (data/logos/{domain}.png), used when there is no screenshot. Returns ['url','bg'] or null. */
function dp_project_logo( $post_id ) {
	static $map = null;
	if ( null === $map ) {
		$file = dp_data_path( 'logos/logos.json' );
		$map  = $file ? json_decode( (string) file_get_contents( $file ), true ) : array(); // phpcs:ignore
		if ( ! is_array( $map ) ) { $map = array(); }
	}
	$host = dp_host( dp_project_ref( $post_id ) );
	if ( ! $host || empty( $map[ $host ] ) ) { return null; }
	$url = dp_data_url( 'logos/' . $map[ $host ]['file'] );
	return $url ? array( 'url' => $url, 'bg' => $map[ $host ]['bg'] ) : null;
}

/** Show the logo card instead of a live capture: no saved screenshot and no as-delivered gallery. */
function dp_use_logo( $post_id ) {
	if ( has_post_thumbnail( $post_id ) || ( function_exists( 'dp_is_changed' ) && dp_is_changed( $post_id ) ) ) { return null; }
	return dp_project_logo( $post_id );
}

/** WordPress.com mShots screenshot URL. Tall = full-page style capture (1440×2880 viewport). */
function dp_mshots_url( $url, $w = 1200, $tall = false ) {
	if ( $tall ) {
		return 'https://s0.wp.com/mshots/v1/' . rawurlencode( $url ) . '?w=' . $w . '&h=' . ( $w * 2 ) . '&vpw=1440&vph=2880';
	}
	return 'https://s0.wp.com/mshots/v1/' . rawurlencode( $url ) . '?w=' . $w . '&h=' . (int) round( $w * 0.625 );
}

function dp_host( $url ) {
	$h = wp_parse_url( $url, PHP_URL_HOST );
	return $h ? preg_replace( '/^www\./', '', $h ) : $url;
}

/** Language switch + light/dark switch shown in the header. */
function dp_render_nav_tools() {
	$ar = function_exists( 'dp_is_ar' ) && dp_is_ar();
	echo '<a class="tool-btn lang-switch" href="' . esc_url( dp_lang_switch_url() ) . '" hreflang="' . ( $ar ? 'en' : 'ar' ) . '" lang="' . ( $ar ? 'en' : 'ar' ) . '" title="' . esc_attr__( 'Language', 'doha-portfolio' ) . '">' . ( $ar ? 'EN' : 'عربي' ) . '</a>';
	echo '<button class="tool-btn theme-toggle" type="button" aria-label="' . esc_attr__( 'Switch light or dark mode', 'doha-portfolio' ) . '" title="' . esc_attr__( 'Switch light or dark mode', 'doha-portfolio' ) . '"><span class="i-sun">' . dp_icon( 'sun', 19 ) . '</span><span class="i-moon">' . dp_icon( 'moon', 19 ) . '</span></button>'; // phpcs:ignore
}

/** Number of countries the published projects come from (cached for a day). */
function dp_country_count() {
	if ( function_exists( 'dpc_stats' ) ) { $s = dpc_stats(); return (int) $s['countries']; }
	$n = get_transient( 'dp_country_count' );
	if ( false !== $n ) { return (int) $n; }
	global $wpdb;
	$n = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT m.meta_value) FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id WHERE m.meta_key = '_dp_country' AND m.meta_value NOT IN ('', 'Africa') AND p.post_type = 'project' AND p.post_status = 'publish'" );
	$n = max( $n, 1 );
	set_transient( 'dp_country_count', $n, DAY_IN_SECONDS );
	return $n;
}
add_action( 'save_post_project', function () { delete_transient( 'dp_country_count' ); } );
