<?php
/**
 * Basic SEO when no SEO plugin is active: meta description, canonical, Open Graph, Twitter card,
 * hreflang for English/Arabic, and Person + ProfessionalService schema.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function dp_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

function dp_seo_description() {
	if ( is_page() ) { $sd = apply_filters( 'dp_service_description', '' ); if ( $sd ) { return $sd; } }
	if ( is_singular( 'project' ) ) {
		$s = trim( (string) get_post_meta( get_the_ID(), '_dp_summary', true ) );
		if ( $s ) { return wp_trim_words( $s, 30, '' ); }
		return sprintf( '%s, a WordPress website by %s.', get_the_title(), dp_opt( 'name' ) );
	}
	if ( is_post_type_archive( 'project' ) || is_tax() ) {
		return sprintf( '%d WordPress websites by %s for clients across the Gulf, Europe, North America and Asia.', wp_count_posts( 'project' )->publish, dp_opt( 'name' ) );
	}
	return dp_opt( 'name' ) . ', ' . dp_opt( 'role' ) . '. ' . dp_opt( 'tagline' );
}

add_filter( 'pre_get_document_title', function ( $t ) {
	if ( dp_seo_plugin_active() || ! is_front_page() ) { return $t; }
	return dp_opt( 'name' ) . ' | ' . ( dp_is_ar() ? dp_opt( 'role' ) : 'Senior WordPress Developer: websites, WooCommerce, speed and security' );
} );

add_action( 'wp_head', function () {
	$url = is_singular() ? get_permalink() : ( is_front_page() ? home_url( '/' ) : ( is_post_type_archive( 'project' ) ? get_post_type_archive_link( 'project' ) : '' ) );
	$url = $url ? remove_query_arg( 'lang', $url ) : '';
	if ( $url ) {
		echo '<link rel="alternate" hreflang="en" href="' . esc_url( $url ) . '">' . "\n";
		echo '<link rel="alternate" hreflang="ar" href="' . esc_url( add_query_arg( 'lang', 'ar', $url ) ) . '">' . "\n";
		echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $url ) . '">' . "\n";
	}
	if ( dp_seo_plugin_active() ) { return; }
	$desc  = dp_seo_description();
	$title = wp_get_document_title();
	$img   = is_singular( 'project' ) ? dp_project_image( get_the_ID(), 'dp-large' ) : DP_URI . '/assets/img/site-icon.png';
	if ( $url && ! is_singular() ) { echo '<link rel="canonical" href="' . esc_url( dp_is_ar() ? add_query_arg( 'lang', 'ar', $url ) : $url ) . '">' . "\n"; }
	echo '<meta property="og:type" content="' . ( is_singular( 'project' ) ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( $url ) { echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n"; }
	echo '<meta property="og:site_name" content="' . esc_attr( dp_opt( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:locale" content="' . ( dp_is_ar() ? 'ar_AR' : 'en_US' ) . '">' . "\n";
	if ( $img ) { echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n"; }
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

	if ( is_front_page() ) {
		$same = array( dp_opt( 'linkedin' ) );
		foreach ( dp_social_links() as $l ) { $same[] = $l['url']; }
		$person = array(
			'@type'       => 'Person',
			'@id'         => home_url( '/#person' ),
			'name'        => dp_opt( 'name' ),
			'jobTitle'    => 'Senior WordPress Developer',
			'url'         => home_url( '/' ),
			'email'       => 'mailto:' . dp_opt( 'email' ),
			'image'       => DP_URI . '/assets/img/site-icon.png',
			'sameAs'      => array_values( array_filter( array_unique( $same ) ) ),
			'knowsAbout'  => array( 'WordPress', 'WooCommerce', 'Elementor', 'PHP', 'Website speed optimisation', 'WordPress security' ),
			'address'     => array( '@type' => 'PostalAddress', 'addressLocality' => 'Damascus', 'addressCountry' => 'SY' ),
		);
		$service = array(
			'@type'       => 'ProfessionalService',
			'@id'         => home_url( '/#service' ),
			'name'        => dp_opt( 'name' ) . ', WordPress development',
			'url'         => home_url( '/' ),
			'founder'     => array( '@id' => home_url( '/#person' ) ),
			'areaServed'  => array( 'AE', 'SA', 'QA', 'KW', 'OM', 'Worldwide' ),
			'serviceType' => array( 'WordPress website development', 'WooCommerce development', 'WordPress speed optimisation', 'Hacked WordPress site recovery', 'WordPress migration', 'WordPress maintenance' ),
			'image'       => DP_URI . '/assets/img/site-icon.png',
		);
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => array( $person, $service ) ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}, 5 );


/**
 * The Arabic version (?lang=ar) is a page of its own: give it a self-referencing canonical,
 * so Google indexes it next to the English page instead of reporting it as a duplicate
 * ("Alternate page with proper canonical tag"). Covers WordPress core, Rank Math and Yoast.
 */
function dp_canonical_lang( $url ) {
	if ( ! $url || ! function_exists( 'dp_is_ar' ) || ! dp_is_ar() ) { return $url; }
	return add_query_arg( 'lang', 'ar', remove_query_arg( 'lang', $url ) );
}
add_filter( 'get_canonical_url', 'dp_canonical_lang', 20 );
add_filter( 'rank_math/frontend/canonical', 'dp_canonical_lang', 20 );
add_filter( 'rank_math/opengraph/url', 'dp_canonical_lang', 20 );
add_filter( 'wpseo_canonical', 'dp_canonical_lang', 20 );
add_filter( 'wpseo_opengraph_url', 'dp_canonical_lang', 20 );
