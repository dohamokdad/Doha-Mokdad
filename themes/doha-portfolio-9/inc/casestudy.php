<?php
/**
 * Project case studies: theme, plugins, technologies, features, UI/UX and delivery phases.
 *
 * - Editable per project (meta box "Case study").
 * - "Build case studies" (Portfolio Tools) visits each live site from this server, detects the
 *   WordPress theme, plugins and front-end technologies, and writes a first draft of every section.
 *   It only fills EMPTY fields, so anything you edit by hand is never overwritten.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Case-study fields: key => [label, help, rows]. List fields hold one item per line. */
function dp_case_fields() {
	return array(
		'_dp_summary'  => array( __( 'Overview', 'doha-portfolio' ), __( 'Two or three sentences: who the client is and what the site does.', 'doha-portfolio' ), 4 ),
		'_dp_theme'    => array( __( 'Theme', 'doha-portfolio' ), __( 'e.g. WoodMart (child theme)', 'doha-portfolio' ), 1 ),
		'_dp_builder'  => array( __( 'Page builder', 'doha-portfolio' ), __( 'e.g. Elementor Pro, WPBakery, Gutenberg', 'doha-portfolio' ), 1 ),
		'_dp_plugins'  => array( __( 'Plugins (one per line)', 'doha-portfolio' ), '', 6 ),
		'_dp_tech'     => array( __( 'Technologies (one per line)', 'doha-portfolio' ), __( 'e.g. PHP, jQuery, GSAP, Google Maps API', 'doha-portfolio' ), 5 ),
		'_dp_features' => array( __( 'Key features (one per line)', 'doha-portfolio' ), '', 6 ),
		'_dp_uiux'     => array( __( 'UI / UX (one per line)', 'doha-portfolio' ), '', 5 ),
		'_dp_phases'   => array( __( 'Delivery phases (one per line, "Title: details")', 'doha-portfolio' ), '', 6 ),
	);
}

function dp_case_list( $post_id, $key ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\n/', (string) get_post_meta( $post_id, $key, true ) ) ) ) );
}

function dp_has_case( $post_id ) {
	foreach ( array_keys( dp_case_fields() ) as $k ) {
		if ( trim( (string) get_post_meta( $post_id, $k, true ) ) !== '' ) { return true; }
	}
	return false;
}

/* ---------- Meta box ---------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'dp_case', __( 'Case study (shown on the project page)', 'doha-portfolio' ), 'dp_render_case_box', 'project', 'normal', 'high' );
} );

function dp_render_case_box( $post ) {
	wp_nonce_field( 'dp_save_case', 'dp_case_nonce' );
	echo '<p class="description">' . esc_html__( 'Leave a field empty and "Build case studies" in Portfolio Tools will draft it from the live site. Once you change a field here it is yours: scans never overwrite it.', 'doha-portfolio' ) . '</p>';
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( dp_case_fields() as $key => $f ) {
		$val = get_post_meta( $post->ID, $key, true );
		echo '<tr><th scope="row"><label for="' . esc_attr( $key ) . '">' . esc_html( $f[0] ) . '</label></th><td>';
		if ( 1 === $f[2] ) {
			echo '<input type="text" class="widefat" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '">';
		} else {
			echo '<textarea class="widefat" rows="' . (int) $f[2] . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . esc_textarea( $val ) . '</textarea>';
		}
		if ( $f[1] ) { echo '<p class="description">' . esc_html( $f[1] ) . '</p>'; }
		echo '</td></tr>';
	}
	$det = get_post_meta( $post->ID, '_dp_detected', true );
	if ( $det ) { echo '<tr><th>' . esc_html__( 'Last scan', 'doha-portfolio' ) . '</th><td>' . esc_html( $det ) . '</td></tr>'; }
	echo '</tbody></table>';
}

add_action( 'save_post_project', function ( $post_id ) {
	if ( ! isset( $_POST['dp_case_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dp_case_nonce'] ) ), 'dp_save_case' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	$auto = (array) get_post_meta( $post_id, '_dp_auto', true );
	foreach ( array_keys( dp_case_fields() ) as $key ) {
		if ( ! isset( $_POST[ $key ] ) ) { continue; }
		$new = sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) );
		if ( trim( $new ) !== trim( (string) get_post_meta( $post_id, $key, true ) ) ) {
			$auto = array_diff( $auto, array( $key ) ); // edited by hand: never replace it again
		}
		update_post_meta( $post_id, $key, $new );
	}
	update_post_meta( $post_id, '_dp_auto', array_values( $auto ) );
} );

/* ---------- Known plugin names ---------- */
function dp_plugin_names() {
	return array(
		'elementor' => 'Elementor', 'elementor-pro' => 'Elementor Pro', 'woocommerce' => 'WooCommerce', 'js_composer' => 'WPBakery Page Builder',
		'revslider' => 'Slider Revolution', 'LayerSlider' => 'LayerSlider', 'layerslider' => 'LayerSlider', 'master-slider' => 'Master Slider', 'smart-slider-3' => 'Smart Slider 3',
		'contact-form-7' => 'Contact Form 7', 'wpforms-lite' => 'WPForms', 'wpforms' => 'WPForms', 'gravityforms' => 'Gravity Forms', 'ninja-forms' => 'Ninja Forms', 'fluentform' => 'Fluent Forms', 'forminator' => 'Forminator',
		'wordpress-seo' => 'Yoast SEO', 'seo-by-rank-math' => 'Rank Math SEO', 'all-in-one-seo-pack' => 'All in One SEO',
		'sitepress-multilingual-cms' => 'WPML', 'wpml-string-translation' => 'WPML String Translation', 'woocommerce-multilingual' => 'WooCommerce Multilingual', 'polylang' => 'Polylang', 'translatepress-multilingual' => 'TranslatePress', 'gtranslate' => 'GTranslate',
		'litespeed-cache' => 'LiteSpeed Cache', 'wp-rocket' => 'WP Rocket', 'w3-total-cache' => 'W3 Total Cache', 'wp-optimize' => 'WP-Optimize', 'autoptimize' => 'Autoptimize', 'wp-fastest-cache' => 'WP Fastest Cache', 'sg-cachepress' => 'SiteGround Optimizer',
		'wordfence' => 'Wordfence Security', 'better-wp-security' => 'Solid Security', 'really-simple-ssl' => 'Really Simple SSL',
		'dokan-lite' => 'Dokan Multivendor', 'dokan-pro' => 'Dokan Pro', 'wc-vendors' => 'WC Vendors', 'wc-multivendor-marketplace' => 'WCFM Marketplace', 'wc-frontend-manager' => 'WCFM Frontend Manager', 'dc-woocommerce-multi-vendor' => 'MultiVendorX',
		'learnpress' => 'LearnPress', 'tutor' => 'Tutor LMS', 'sfwd-lms' => 'LearnDash', 'lifterlms' => 'LifterLMS', 'masterstudy-lms-learning-management-system' => 'MasterStudy LMS',
		'essential-addons-for-elementor-lite' => 'Essential Addons for Elementor', 'elementskit-lite' => 'ElementsKit', 'happy-elementor-addons' => 'Happy Addons', 'the-plus-addons-for-elementor-page-builder' => 'The Plus Addons', 'premium-addons-for-elementor' => 'Premium Addons', 'jet-engine' => 'JetEngine', 'jet-elements' => 'JetElements', 'jet-menu' => 'JetMenu', 'jet-woo-builder' => 'JetWooBuilder', 'ultimate-elementor' => 'Ultimate Addons for Elementor', 'header-footer-elementor' => 'Elementor Header & Footer Builder', 'unlimited-elements-for-elementor' => 'Unlimited Elements',
		'mailchimp-for-wp' => 'Mailchimp for WP', 'jetpack' => 'Jetpack', 'akismet' => 'Akismet', 'google-site-kit' => 'Site Kit by Google', 'duracelltomi-google-tag-manager' => 'GTM4WP',
		'wp-whatsapp-chat' => 'WhatsApp Chat', 'creame-whatsapp-me' => 'Joinchat (WhatsApp)', 'click-to-chat-for-whatsapp' => 'Click to Chat (WhatsApp)',
		'instagram-feed' => 'Smash Balloon Instagram Feed', 'custom-facebook-feed' => 'Smash Balloon Facebook Feed', 'wp-google-maps' => 'WP Go Maps',
		'woocommerce-currency-switcher' => 'WOOCS Currency Switcher', 'woo-variation-swatches' => 'Variation Swatches', 'yith-woocommerce-wishlist' => 'YITH Wishlist', 'yith-woocommerce-compare' => 'YITH Compare', 'yith-woocommerce-quick-view' => 'YITH Quick View', 'woo-smart-wishlist' => 'WPC Smart Wishlist',
		'bookly-responsive-appointment-booking-tool' => 'Bookly', 'ameliabooking' => 'Amelia Booking', 'woocommerce-bookings' => 'WooCommerce Bookings', 'the-events-calendar' => 'The Events Calendar',
		'cookie-law-info' => 'CookieYes', 'complianz-gdpr' => 'Complianz', 'wpdiscuz' => 'wpDiscuz', 'bbpress' => 'bbPress', 'buddypress' => 'BuddyPress',
		'popup-maker' => 'Popup Maker', 'tablepress' => 'TablePress', 'wp-pagenavi' => 'WP-PageNavi', 'redux-framework' => 'Redux Framework', 'kirki' => 'Kirki', 'advanced-custom-fields' => 'Advanced Custom Fields', 'advanced-custom-fields-pro' => 'ACF Pro', 'custom-post-type-ui' => 'Custom Post Type UI',
		'woodmart-core' => 'WoodMart Core', 'thim-core' => 'Thim Core (Eduma)', 'envato-market' => 'Envato Market', 'ti-woocommerce-wishlist' => 'TI Wishlist', 'cf7-conditional-fields' => 'Conditional Fields for CF7', 'loco-translate' => 'Loco Translate', 'svg-support' => 'SVG Support', 'safe-svg' => 'Safe SVG', 'wp-mail-smtp' => 'WP Mail SMTP', 'updraftplus' => 'UpdraftPlus', 'all-in-one-wp-migration' => 'All-in-One WP Migration', 'duplicator' => 'Duplicator', 'classic-editor' => 'Classic Editor', 'insert-headers-and-footers' => 'WPCode', 'hesabpay-gateway' => 'HesabPay Gateway', 'woocommerce-payments' => 'WooPayments', 'woocommerce-gateway-stripe' => 'Stripe Gateway', 'woocommerce-paypal-payments' => 'PayPal Payments', 'tabby-checkout' => 'Tabby', 'tamara-checkout' => 'Tamara',
	);
}

function dp_plugin_label( $slug ) {
	$map = dp_plugin_names();
	if ( isset( $map[ $slug ] ) ) { return $map[ $slug ]; }
	return ucwords( trim( preg_replace( '/[-_]+/', ' ', $slug ) ) );
}

/* ---------- Detection ---------- */
function dp_http_get( $url ) {
	$res = wp_remote_get( $url, array(
		'timeout'     => 25,
		'redirection' => 5,
		'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126 Safari/537.36',
		'headers'     => array( 'Accept' => 'text/html,application/xhtml+xml,text/css,*/*' ),
	) );
	if ( is_wp_error( $res ) ) { return array( 'error' => $res->get_error_message(), 'code' => 0, 'body' => '' ); }
	return array( 'error' => '', 'code' => (int) wp_remote_retrieve_response_code( $res ), 'body' => (string) wp_remote_retrieve_body( $res ) );
}

/** Scan a live site. Returns an array of findings (never throws). */
function dp_detect_stack( $url ) {
	$out = array( 'ok' => false, 'themes' => array(), 'plugins' => array(), 'generators' => array(), 'tech' => array(), 'signals' => array(), 'headings' => array(), 'langs' => array(), 'error' => '' );
	$page = dp_http_get( $url );
	if ( $page['error'] || $page['code'] >= 400 || '' === $page['body'] ) {
		$out['error'] = $page['error'] ? $page['error'] : 'HTTP ' . $page['code'];
		return $out;
	}
	$html = $page['body'];
	$out['ok'] = true;
	$base = trailingslashit( preg_replace( '#^(https?://[^/]+).*$#i', '$1', $url ) );

	// Themes (with real names from style.css).
	if ( preg_match_all( '#/wp-content/themes/([A-Za-z0-9._-]+)/#', $html, $m ) ) {
		foreach ( array_unique( $m[1] ) as $slug ) {
			$css  = dp_http_get( $base . 'wp-content/themes/' . $slug . '/style.css' );
			$name = '';
			$tpl  = '';
			if ( ! $css['error'] && $css['code'] < 400 ) {
				if ( preg_match( '/Theme Name:\s*(.+)/i', $css['body'], $n ) ) { $name = trim( wp_strip_all_tags( $n[1] ) ); }
				if ( preg_match( '/Template:\s*([A-Za-z0-9._-]+)/i', $css['body'], $t ) ) { $tpl = trim( $t[1] ); }
			}
			$out['themes'][ $slug ] = array( 'name' => $name ? $name : ucwords( str_replace( array( '-', '_' ), ' ', $slug ) ), 'parent' => $tpl );
		}
	}
	// Plugins.
	if ( preg_match_all( '#/wp-content/plugins/([A-Za-z0-9._-]+)/#', $html, $m ) ) {
		foreach ( array_unique( $m[1] ) as $slug ) { $out['plugins'][ $slug ] = dp_plugin_label( $slug ); }
	}
	// Generator tags.
	if ( preg_match_all( '#<meta[^>]+name=["\']generator["\'][^>]+content=["\']([^"\']+)["\']#i', $html, $m ) ) {
		$out['generators'] = array_values( array_unique( array_map( 'trim', $m[1] ) ) );
	}
	$has = function ( $re ) use ( $html ) { return (bool) preg_match( $re, $html ); };

	// Front-end technologies.
	$tech = array(
		'jQuery'               => '#jquery(\.min)?\.js#i',
		'GSAP'                 => '#gsap(\.min)?\.js|ScrollTrigger#i',
		'Swiper'               => '#swiper(-bundle)?(\.min)?\.(js|css)#i',
		'Owl Carousel'         => '#owl\.carousel#i',
		'Slick'                => '#slick(\.min)?\.js#i',
		'Bootstrap'            => '#bootstrap(\.min)?\.(css|js)#i',
		'Font Awesome'         => '#font-?awesome#i',
		'AOS (scroll animations)' => '#aos(\.min)?\.(js|css)#i',
		'Lottie'               => '#lottie#i',
		'Google Fonts'         => '#fonts\.googleapis\.com#i',
		'Google Maps'          => '#maps\.googleapis\.com|google\.com/maps/embed#i',
		'Google Analytics'     => '#gtag\(|google-analytics\.com|googletagmanager\.com/gtag#i',
		'Google Tag Manager'   => '#googletagmanager\.com/gtm\.js|GTM-[A-Z0-9]+#',
		'Meta Pixel'           => '#connect\.facebook\.net/[^"\']*fbevents\.js#i',
		'Google reCAPTCHA'     => '#recaptcha#i',
		'Schema.org structured data' => '#application/ld\+json#i',
		'YouTube embeds'       => '#youtube\.com/embed|youtube-nocookie#i',
		'Cloudflare'           => '#cdnjs\.cloudflare\.com|cloudflare#i',
		'REST API'             => '#/wp-json/#i',
	);
	foreach ( $tech as $label => $re ) { if ( $has( $re ) ) { $out['tech'][] = $label; } }

	// Language / direction.
	if ( preg_match( '#<html[^>]*\blang=["\']([a-zA-Z_-]+)#i', $html, $l ) ) { $out['langs'][] = strtolower( substr( $l[1], 0, 2 ) ); }
	if ( preg_match_all( '#hreflang=["\']([a-zA-Z_-]+)["\']#i', $html, $hl ) ) {
		foreach ( $hl[1] as $code ) { if ( 'x-default' !== strtolower( $code ) ) { $out['langs'][] = strtolower( substr( $code, 0, 2 ) ); } }
	}
	$out['langs'] = array_values( array_unique( $out['langs'] ) );

	// Signals used for writing features / UI-UX.
	$sig = array(
		'rtl'        => $has( '#<html[^>]*dir=["\']rtl#i' ) || $has( '#\brtl\.css#i' ),
		'shop'       => isset( $out['plugins']['woocommerce'] ) || $has( '#woocommerce#i' ),
		'cart'       => $has( '#/cart/|add_to_cart|wc-cart#i' ),
		'vendors'    => (bool) array_intersect( array_keys( $out['plugins'] ), array( 'dokan-lite', 'dokan-pro', 'wc-vendors', 'wc-multivendor-marketplace', 'wc-frontend-manager', 'dc-woocommerce-multi-vendor' ) ) || $has( '#/store-listing|/vendor/|/vendors/#i' ),
		'lms'        => (bool) array_intersect( array_keys( $out['plugins'] ), array( 'learnpress', 'tutor', 'sfwd-lms', 'lifterlms', 'masterstudy-lms-learning-management-system', 'thim-core' ) ) || $has( '#/courses?/#i' ),
		'multilang'  => count( $out['langs'] ) > 1 || (bool) array_intersect( array_keys( $out['plugins'] ), array( 'sitepress-multilingual-cms', 'polylang', 'translatepress-multilingual', 'gtranslate' ) ),
		'currency'   => isset( $out['plugins']['woocommerce-currency-switcher'] ) || $has( '#currency-switcher|wcml-currency#i' ),
		'forms'      => (bool) array_intersect( array_keys( $out['plugins'] ), array( 'contact-form-7', 'wpforms-lite', 'wpforms', 'gravityforms', 'ninja-forms', 'fluentform', 'forminator' ) ) || $has( '#elementor-form|<form[^>]+(contact|enquir|quote)#i' ),
		'whatsapp'   => $has( '#wa\.me/|api\.whatsapp\.com|whatsapp#i' ),
		'phone'      => $has( '#href=["\']tel:#i' ),
		'email'      => $has( '#href=["\']mailto:#i' ),
		'booking'    => (bool) array_intersect( array_keys( $out['plugins'] ), array( 'bookly-responsive-appointment-booking-tool', 'ameliabooking', 'woocommerce-bookings', 'the-events-calendar' ) ) || $has( '#\bbook(ing)? (now|online|a )#i' ),
		'slider'     => isset( $out['plugins']['revslider'] ) || isset( $out['plugins']['LayerSlider'] ) || isset( $out['plugins']['smart-slider-3'] ) || $has( '#swiper|owl-carousel|slick-slider|rev_slider#i' ),
		'video'      => $has( '#<video|youtube\.com/embed|vimeo\.com#i' ),
		'blog'       => $has( '#/blog/|/news/|elementor-posts|recent-posts|wp-block-latest-posts|class=["\'][^"\']*\bblog-#i' ),
		'search'     => $has( '#type=["\']search["\']|role=["\']search["\']#i' ),
		'gallery'    => $has( '#lightbox|fancybox|elementor-gallery|gallery-item|wp-block-gallery#i' ),
		'maps'       => in_array( 'Google Maps', $out['tech'], true ),
		'newsletter' => isset( $out['plugins']['mailchimp-for-wp'] ) || $has( '#newsletter|subscribe#i' ),
		'sticky'     => $has( '#sticky#i' ),
		'social'     => $has( '#instagram\.com/|facebook\.com/|linkedin\.com/#i' ),
		'elementor'  => isset( $out['plugins']['elementor'] ) || $has( '#elementor#i' ),
		'wpbakery'   => isset( $out['plugins']['js_composer'] ) || $has( '#WPBakery|vc_row#i' ),
		'gutenberg'  => $has( '#wp-block-#i' ),
		'divi'       => $has( '#et_pb_|Divi#' ),
		'seo'        => (bool) array_intersect( array_keys( $out['plugins'] ), array( 'wordpress-seo', 'seo-by-rank-math', 'all-in-one-seo-pack' ) ) || $has( '#yoast|rank-math|rankmath#i' ),
		'cache'      => (bool) array_intersect( array_keys( $out['plugins'] ), array( 'litespeed-cache', 'wp-rocket', 'w3-total-cache', 'wp-optimize', 'autoptimize', 'wp-fastest-cache', 'sg-cachepress' ) ) || $has( '#litespeed|wp-rocket|wpo-minify#i' ),
	);
	$out['signals'] = array_keys( array_filter( $sig ) );

	// Homepage section headings (h2), cleaned.
	if ( preg_match_all( '#<h2[^>]*>(.*?)</h2>#is', $html, $h ) ) {
		foreach ( $h[1] as $t ) {
			$t = trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( $t ), ENT_QUOTES, 'UTF-8' ) ) );
			if ( $t && mb_strlen( $t ) <= 60 && ! in_array( $t, $out['headings'], true ) ) { $out['headings'][] = $t; }
			if ( count( $out['headings'] ) >= 7 ) { break; }
		}
	}
	return $out;
}

/* ---------- Writing the draft ---------- */
function dp_lang_name( $code ) {
	$n = array( 'en' => 'English', 'ar' => 'Arabic', 'fr' => 'French', 'de' => 'German', 'tr' => 'Turkish', 'ja' => 'Japanese', 'sv' => 'Swedish', 'fa' => 'Persian', 'ps' => 'Pashto', 'ru' => 'Russian', 'es' => 'Spanish', 'it' => 'Italian', 'nl' => 'Dutch', 'ur' => 'Urdu' );
	return isset( $n[ $code ] ) ? $n[ $code ] : strtoupper( $code );
}

function dp_category_profile( $type ) {
	$p = array(
		'E-commerce'                 => array( 'an online store', array( 'Product catalogue with categories, filters and product pages', 'Cart, checkout and order emails' ), 'Store' ),
		'Education & LMS'            => array( 'an education and e-learning website', array( 'Programmes and course pages', 'Admissions and enquiry flow' ), 'Learning' ),
		'Real Estate & Construction' => array( 'a real-estate and construction website', array( 'Project and property showcases', 'Enquiry forms that reach the sales team' ), 'Listings' ),
		'Hospitality, Food & Events' => array( 'a hospitality and events website', array( 'Menus, services or event showcases', 'Booking and enquiry calls to action' ), 'Showcase' ),
		'Corporate & Business'       => array( 'a corporate website', array( 'Company profile, services and sectors', 'Clear contact and enquiry routes' ), 'Corporate' ),
		'Trading & Manufacturing'    => array( 'a corporate website for a trading and manufacturing company', array( 'Company profile and product lines', 'Contact details and location' ), 'Corporate' ),
		'Tech & Startups'            => array( 'a technology company website', array( 'Product and service pages', 'Lead capture and contact flow' ), 'Product' ),
		'Personal & Creative'        => array( 'a personal brand and portfolio website', array( 'Portfolio and gallery of work', 'About and contact pages' ), 'Portfolio' ),
		'Non-profit, Media & Health' => array( 'a non-profit, media and community website', array( 'Mission, programmes and news', 'Donation, volunteering or contact routes' ), 'Community' ),
	);
	return isset( $p[ $type ] ) ? $p[ $type ] : $p['Corporate & Business'];
}

/** Build draft sections from a scan. Returns key => text. */
function dp_draft_case( $post_id, $scan ) {
	$title   = get_the_title( $post_id );
	$country = get_post_meta( $post_id, '_dp_country', true );
	$emp     = get_post_meta( $post_id, '_dp_employer', true );
	$terms   = get_the_terms( $post_id, 'project_type' );
	$type    = ( $terms && ! is_wp_error( $terms ) ) ? html_entity_decode( $terms[0]->name, ENT_QUOTES, 'UTF-8' ) : '';
	$prof    = dp_category_profile( $type );
	$s       = array_flip( (array) $scan['signals'] );
	$plugins = (array) $scan['plugins'];
	$out     = array();

	// Theme.
	if ( $scan['themes'] ) {
		$names = array();
		$child = '';
		foreach ( $scan['themes'] as $slug => $t ) {
			if ( $t['parent'] ) { $child = $t['name']; if ( isset( $scan['themes'][ $t['parent'] ] ) ) { continue; } $names[] = ucwords( str_replace( '-', ' ', $t['parent'] ) ); }
			else { $names[] = $t['name']; }
		}
		$names = array_values( array_unique( $names ) );
		$out['_dp_theme'] = implode( ', ', $names ) . ( $child ? ' (child theme: ' . $child . ')' : '' );
	}

	// Builder.
	$builder = '';
	if ( isset( $plugins['elementor-pro'] ) ) { $builder = 'Elementor Pro'; }
	elseif ( isset( $s['elementor'] ) ) { $builder = 'Elementor'; }
	elseif ( isset( $s['wpbakery'] ) ) { $builder = 'WPBakery Page Builder'; }
	elseif ( isset( $s['divi'] ) ) { $builder = 'Divi Builder'; }
	elseif ( isset( $s['gutenberg'] ) ) { $builder = 'Gutenberg (block editor)'; }
	if ( $builder ) { $out['_dp_builder'] = $builder; }

	// Plugins & tech.
	$skip = array( 'elementor', 'elementor-pro', 'js_composer' );
	$pl   = array();
	foreach ( $plugins as $slug => $label ) { if ( ! in_array( $slug, $skip, true ) ) { $pl[] = $label; } }
	if ( $pl ) { $out['_dp_plugins'] = implode( "\n", array_values( array_unique( $pl ) ) ); }
	$tech = array_merge( array( 'WordPress', 'PHP', 'MySQL', 'HTML5', 'CSS3', 'JavaScript' ), (array) $scan['tech'] );
	$out['_dp_tech'] = implode( "\n", array_values( array_unique( $tech ) ) );

	// Overview.
	$langs = array_map( 'dp_lang_name', (array) $scan['langs'] );
	$built = $builder ? ' with ' . $builder : '';
	$theme = ! empty( $out['_dp_theme'] ) ? ' on the ' . preg_replace( '/ \(child theme.*$/', '', $out['_dp_theme'] ) . ' theme' : '';
	$where = $country ? ( in_array( $country, array( 'UAE', 'USA', 'UK' ), true ) ? 'the ' . $country : $country ) : 'the region';
	$sum   = sprintf( '%1$s is %2$s, built for a client in %3$s on WordPress%4$s%5$s.', $title, $prof[0], $where, $theme, $built );
	if ( count( $langs ) > 1 ) { $sum .= ' The site runs in ' . implode( ' and ', $langs ) . '.'; }
	elseif ( isset( $s['rtl'] ) ) { $sum .= ' It is written in Arabic with a right-to-left layout.'; }
	if ( $emp && 'Freelance' !== $emp ) { $sum .= ' Delivered as part of my work at ' . $emp . '.'; }
	$out['_dp_summary'] = $sum;

	// Features.
	$f = array();
	if ( isset( $s['vendors'] ) ) { $f[] = 'Multi-vendor marketplace: vendor registration, vendor stores and dashboards'; }
	if ( isset( $s['shop'] ) ) { $f[] = 'WooCommerce store: catalogue, product pages, cart and checkout'; }
	if ( isset( $s['currency'] ) ) { $f[] = 'Multi-currency pricing'; }
	if ( isset( $s['lms'] ) ) { $f[] = 'Course catalogue with lessons and student accounts'; }
	if ( isset( $s['booking'] ) ) { $f[] = 'Online booking and appointment requests'; }
	if ( isset( $s['multilang'] ) && count( $langs ) > 1 ) { $f[] = 'Multilingual content (' . implode( ', ', $langs ) . ') with a language switcher'; }
	if ( isset( $s['forms'] ) ) { $f[] = 'Enquiry and contact forms'; }
	if ( isset( $s['whatsapp'] ) ) { $f[] = 'WhatsApp click-to-chat'; }
	if ( isset( $s['slider'] ) ) { $f[] = 'Hero slider and carousels'; }
	if ( isset( $s['gallery'] ) ) { $f[] = 'Image galleries / portfolio with lightbox'; }
	if ( isset( $s['video'] ) ) { $f[] = 'Embedded video'; }
	if ( isset( $s['blog'] ) ) { $f[] = 'News / blog section'; }
	if ( isset( $s['maps'] ) ) { $f[] = 'Google Maps location'; }
	if ( isset( $s['newsletter'] ) ) { $f[] = 'Newsletter sign-up'; }
	if ( isset( $s['seo'] ) ) { $f[] = 'On-page SEO setup (' . ( isset( $plugins['seo-by-rank-math'] ) ? 'Rank Math' : ( isset( $plugins['wordpress-seo'] ) ? 'Yoast SEO' : 'SEO plugin' ) ) . ')'; }
	if ( isset( $s['cache'] ) ) { $f[] = 'Caching and speed optimisation'; }
	if ( isset( $plugins['wordfence'] ) ) { $f[] = 'Security hardening with Wordfence'; }
	if ( count( $f ) < 3 ) { $f = array_merge( $prof[1], $f ); }
	$out['_dp_features'] = implode( "\n", array_slice( array_values( array_unique( $f ) ), 0, 8 ) );

	// UI / UX.
	$u = array( 'Responsive layout checked on mobile, tablet and desktop' );
	if ( isset( $s['rtl'] ) ) { $u[] = 'Right-to-left Arabic layout with mirrored navigation and Arabic typography'; }
	if ( count( $langs ) > 1 ) { $u[] = 'Language switcher that keeps visitors on the same page'; }
	if ( $scan['headings'] ) { $u[] = 'Homepage flow: ' . implode( ' / ', array_slice( $scan['headings'], 0, 6 ) ); }
	$cta = array();
	if ( isset( $s['phone'] ) ) { $cta[] = 'call'; }
	if ( isset( $s['whatsapp'] ) ) { $cta[] = 'WhatsApp'; }
	if ( isset( $s['email'] ) ) { $cta[] = 'email'; }
	if ( isset( $s['forms'] ) ) { $cta[] = 'enquiry form'; }
	if ( $cta ) { $u[] = 'Contact always one tap away: ' . implode( ', ', $cta ); }
	if ( isset( $s['sticky'] ) ) { $u[] = 'Sticky header so navigation stays reachable while scrolling'; }
	if ( isset( $s['shop'] ) ) { $u[] = 'Short path from product to checkout'; }
	if ( isset( $s['search'] ) ) { $u[] = 'Site search'; }
	if ( $builder && false !== strpos( $builder, 'Elementor' ) ) { $u[] = 'Built in reusable Elementor sections so the client can edit content without code'; }
	$out['_dp_uiux'] = implode( "\n", array_slice( $u, 0, 7 ) );

	// Delivery phases (the process used, tuned by project type).
	$build = isset( $s['shop'] ) ? 'Store build: WooCommerce setup, products, categories, payments and shipping' : ( isset( $s['lms'] ) ? 'Platform build: courses, lessons, user roles and enrolment' : 'Build: pages, navigation, forms and content entry' );
	$ph = array(
		'Discovery: goals, audience, sitemap and content checklist with the client',
		'Design: ' . ( ! empty( $out['_dp_theme'] ) ? preg_replace( '/ \(child theme.*$/', '', $out['_dp_theme'] ) . ' theme set up and styled to the brand' : 'theme selection and brand styling' ) . ( isset( $s['rtl'] ) ? ', including the RTL version' : '' ),
		$build,
		'Integrations: ' . ( $pl ? implode( ', ', array_slice( array_values( array_unique( $pl ) ), 0, 4 ) ) : 'forms, analytics and social links' ),
		'QA: mobile and cross-browser testing, speed and SEO basics',
		'Launch: go-live, client training and handover',
	);
	$out['_dp_phases'] = implode( "\n", $ph );

	return $out;
}

/** Scan + fill empty fields for one project. Returns status string. */
function dp_build_case( $post_id, $force_scan = false ) {
	$url = get_post_meta( $post_id, '_dp_url', true );
	if ( ! $url ) { return 'error'; }
	$scan = dp_detect_stack( $url );
	update_post_meta( $post_id, '_dp_scan', wp_json_encode( $scan ) );
	update_post_meta( $post_id, '_dp_detected', gmdate( 'Y-m-d H:i' ) . ' UTC' . ( $scan['ok'] ? '' : ' (site not reachable: ' . $scan['error'] . ')' ) );
	if ( ! $scan['ok'] ) {
		// Still write a category-based draft so the page is not empty.
		$scan = array_merge( $scan, array( 'themes' => array(), 'plugins' => array(), 'tech' => array(), 'signals' => array(), 'langs' => array(), 'headings' => array() ) );
	}
	$auto = (array) get_post_meta( $post_id, '_dp_auto', true );
	foreach ( dp_draft_case( $post_id, $scan ) as $key => $val ) {
		$cur = trim( (string) get_post_meta( $post_id, $key, true ) );
		// Fill empty fields, and refresh fields that were drafted automatically before.
		if ( '' !== trim( $val ) && ( '' === $cur || in_array( $key, $auto, true ) ) ) {
			update_post_meta( $post_id, $key, $val );
			$auto[] = $key;
		}
	}
	update_post_meta( $post_id, '_dp_auto', array_values( array_unique( $auto ) ) );
	return $scan['ok'] ? 'done' : 'offline';
}

/* ---------- AJAX for Portfolio Tools ---------- */
add_action( 'wp_ajax_dp_build_case', function () {
	check_ajax_referer( 'dp_tools' );
	if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array( 'status' => 'error' ) ); }
	$id = isset( $_POST['post'] ) ? absint( $_POST['post'] ) : 0;
	if ( ! $id || 'project' !== get_post_type( $id ) ) { wp_send_json_error( array( 'status' => 'error' ) ); }
	wp_send_json_success( array( 'status' => dp_build_case( $id ) ) );
} );

/** Apply hand-written case studies bundled in data/projects.json (fills empty fields only). */
function dp_apply_bundled_cases() {
	$file = DP_DIR . '/data/projects.json';
	if ( ! file_exists( $file ) ) { return 0; }
	$items = json_decode( file_get_contents( $file ), true ); // phpcs:ignore
	$n     = 0;
	foreach ( (array) $items as $p ) {
		if ( empty( $p['case'] ) ) { continue; }
		$found = get_posts( array( 'post_type' => 'project', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_dp_url' ) );
		foreach ( $found as $id ) {
			if ( dp_url_key( get_post_meta( $id, '_dp_url', true ) ) !== dp_url_key( $p['url'] ) ) { continue; }
			$auto = (array) get_post_meta( $id, '_dp_auto', true );
			foreach ( $p['case'] as $k => $v ) {
				$key = '_dp_' . $k;
				$val = is_array( $v ) ? implode( "\n", $v ) : (string) $v;
				// Hand-written text replaces empty or auto-drafted fields, never your own edits.
				if ( '' === trim( (string) get_post_meta( $id, $key, true ) ) || in_array( $key, $auto, true ) ) {
					update_post_meta( $id, $key, $val );
					$auto = array_diff( $auto, array( $key ) );
				}
			}
			update_post_meta( $id, '_dp_auto', array_values( $auto ) );
			$n++;
		}
	}
	return $n;
}

/** v4 sites: work out which fields were auto-drafted (they still match the stored scan's draft). */
function dp_backfill_auto_flags() {
	$ids = get_posts( array( 'post_type' => 'project', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_dp_scan' ) );
	foreach ( $ids as $id ) {
		if ( metadata_exists( 'post', $id, '_dp_auto' ) ) { continue; }
		$scan = json_decode( (string) get_post_meta( $id, '_dp_scan', true ), true );
		if ( ! is_array( $scan ) ) { continue; }
		if ( empty( $scan['ok'] ) ) {
			$scan = array_merge( $scan, array( 'themes' => array(), 'plugins' => array(), 'tech' => array(), 'signals' => array(), 'langs' => array(), 'headings' => array() ) );
		}
		$auto = array();
		foreach ( dp_draft_case( $id, $scan ) as $key => $val ) {
			if ( trim( (string) get_post_meta( $id, $key, true ) ) === trim( $val ) ) { $auto[] = $key; }
		}
		update_post_meta( $id, '_dp_auto', $auto );
	}
}
