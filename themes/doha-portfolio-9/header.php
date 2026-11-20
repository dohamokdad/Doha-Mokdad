<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>try{var t=localStorage.getItem('dp-theme');if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-theme',t);}}catch(e){}</script>
<?php if ( ! dp_seo_plugin_active() ) : ?><meta name="description" content="<?php echo esc_attr( dp_seo_description() ); ?>"><?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main"><?php esc_html_e( 'Skip to content', 'doha-portfolio' ); ?></a>
<div class="scroll-progress" aria-hidden="true"></div>
<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) : ?>
<header class="site-header">
	<div class="wrap nav">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php
			// Light-mode logo: Customizer site logo, or the bundled dark mark.
			// Dark-mode logo: "Logo for dark mode" from the Customizer, or the bundled light mark.
			$logo_id   = get_theme_mod( 'custom_logo' );
			$logo_src  = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
			$logo_src  = $logo_src ? $logo_src : DP_URI . '/assets/img/logo-mark.png';
			$logo_dark = get_theme_mod( 'dp_logo_dark' );
			$logo_dark = $logo_dark ? $logo_dark : DP_URI . '/assets/img/logo-mark-light.png';
			?>
			<img class="brand-logo brand-logo-light" src="<?php echo esc_url( $logo_src ); ?>" alt="" width="114" height="30">
			<img class="brand-logo brand-logo-dark" src="<?php echo esc_url( $logo_dark ); ?>" alt="" width="114" height="30">
			<span class="brand-name"><?php echo esc_html( dp_opt( 'name' ) ); ?><small><?php echo esc_html( dp_opt( 'role' ) ); ?></small></span>
		</a>
		<div class="nav-tools nav-tools-mobile">
			<?php dp_render_nav_tools(); ?>
		</div>
		<button class="nav-toggle" aria-expanded="false" aria-controls="primary-nav"><span></span><span></span><span class="sr"><?php esc_html_e( 'Menu', 'doha-portfolio' ); ?></span></button>
		<nav id="primary-nav" class="nav-links" aria-label="<?php esc_attr_e( 'Primary', 'doha-portfolio' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'depth' => 1 ) );
			} else {
				echo '<ul>';
				echo '<li><a href="' . esc_url( home_url( '/#work' ) ) . '">' . esc_html__( 'Work', 'doha-portfolio' ) . '</a></li>';
				echo '<li><a href="' . esc_url( home_url( '/#services' ) ) . '">' . esc_html__( 'Services', 'doha-portfolio' ) . '</a></li>';
				echo '<li><a href="' . esc_url( home_url( '/#experience' ) ) . '">' . esc_html__( 'Experience', 'doha-portfolio' ) . '</a></li>';
				echo '<li><a href="' . esc_url( home_url( '/#skills' ) ) . '">' . esc_html__( 'Skills', 'doha-portfolio' ) . '</a></li>';
				echo '</ul>';
			}
			?>
			<div class="nav-tools nav-tools-desktop"><?php dp_render_nav_tools(); ?></div>
			<a class="btn btn-sm" href="<?php echo esc_url( home_url( '/#contact' ) ); ?>"><?php esc_html_e( 'Start a project', 'doha-portfolio' ); ?></a>
		</nav>
	</div>
</header>
<?php endif; ?>
<main id="main">
