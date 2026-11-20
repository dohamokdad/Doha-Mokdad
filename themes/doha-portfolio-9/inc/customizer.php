<?php
/**
 * Customizer: Appearance → Customize → Profile & Contact.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'dp_profile', array(
		'title'    => __( 'Profile & Contact', 'doha-portfolio' ),
		'priority' => 25,
	) );
	$fields = array(
		'name'     => array( __( 'Name', 'doha-portfolio' ), 'text' ),
		'role'     => array( __( 'Job title', 'doha-portfolio' ), 'text' ),
		'tagline'  => array( __( 'Intro sentence', 'doha-portfolio' ), 'textarea' ),
		'years'    => array( __( 'Years of experience (e.g. 15+)', 'doha-portfolio' ), 'text' ),
		'location' => array( __( 'Location line', 'doha-portfolio' ), 'text' ),
		'email'    => array( __( 'Email', 'doha-portfolio' ), 'email' ),
		'phone'    => array( __( 'Phone / WhatsApp', 'doha-portfolio' ), 'text' ),
		'linkedin' => array( __( 'LinkedIn URL', 'doha-portfolio' ), 'url' ),
		'cv_url'   => array( __( 'CV (PDF) link', 'doha-portfolio' ), 'url' ),
		'socials'  => array( __( 'Social profiles (one URL per line)', 'doha-portfolio' ), 'textarea' ),
		'name_ar'     => array( 'Name (Arabic)', 'text' ),
		'role_ar'     => array( 'Job title (Arabic)', 'text' ),
		'tagline_ar'  => array( 'Intro sentence (Arabic)', 'textarea' ),
		'location_ar' => array( 'Location line (Arabic)', 'text' ),
	);
	$defaults = dp_defaults();
	foreach ( $fields as $key => $f ) {
		$wp_customize->add_setting( 'dp_' . $key, array(
			'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
			'sanitize_callback' => in_array( $f[1], array( 'url' ), true ) ? 'esc_url_raw' : ( 'textarea' === $f[1] ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
		) );
		$wp_customize->add_control( 'dp_' . $key, array(
			'label'   => $f[0],
			'section' => 'dp_profile',
			'type'    => $f[1],
		) );
	}
	// Logo shown in dark mode (the site logo in Site Identity is used in light mode).
	$wp_customize->add_setting( 'dp_logo_dark', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'dp_logo_dark', array(
		'label'       => __( 'Logo for dark mode', 'doha-portfolio' ),
		'description' => __( 'A light version of your logo. Leave empty to use the light logo that ships with the theme.', 'doha-portfolio' ),
		'section'     => 'title_tagline',
		'priority'    => 9,
	) ) );
} );
