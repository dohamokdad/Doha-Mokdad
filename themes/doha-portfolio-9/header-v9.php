<?php
/**
 * Header for design 9 pages.
 *
 * @package Doha_Portfolio
 */
$dp9_ar   = dp_is_ar();
$dp9_home = home_url( '/' );
$dp9_nav  = array(
	array( '#work', array( 'en' => 'Work', 'ar' => 'الأعمال' ) ),
	array( '#services', array( 'en' => 'Services', 'ar' => 'الخدمات' ) ),
	array( '#solved', array( 'en' => 'Case studies', 'ar' => 'حالات عملية' ) ),
	array( '#journey', array( 'en' => 'About', 'ar' => 'عنّي' ) ),
);
?><!doctype html>
<html <?php language_attributes(); ?> class="dp9">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#09140f">
<?php if ( ! dp_seo_plugin_active() ) : ?><meta name="description" content="<?php echo esc_attr( dp_seo_description() ); ?>"><?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'dp9-body' ); ?>>
<?php wp_body_open(); ?>
<a class="dp9-skip" href="#main"><?php echo esc_html( dp_t( array( 'en' => 'Skip to content', 'ar' => 'انتقل إلى المحتوى' ) ) ); ?></a>
<header class="dp9-header">
	<div class="dp9-wrap dp9-bar">
		<a class="dp9-brand" href="<?php echo esc_url( $dp9_home ); ?>">
			<img src="<?php echo esc_url( DP_URI . '/assets/img/logo-mark-dark.png' ); ?>" alt="" width="99" height="26">
			<span><?php echo esc_html( dp_opt( 'name' ) ); ?></span>
		</a>
		<nav id="dp9-nav" class="dp9-nav" aria-label="<?php echo esc_attr( dp_t( array( 'en' => 'Main', 'ar' => 'القائمة الرئيسية' ) ) ); ?>">
			<ul>
				<?php foreach ( $dp9_nav as $item ) : ?>
					<li><a href="<?php echo esc_url( is_front_page() ? $item[0] : $dp9_home . $item[0] ); ?>"><?php echo esc_html( dp_t( $item[1] ) ); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<a class="dp9-lang" href="<?php echo esc_url( dp_lang_switch_url() ); ?>" hreflang="<?php echo $dp9_ar ? 'en' : 'ar'; ?>" lang="<?php echo $dp9_ar ? 'en' : 'ar'; ?>"><?php echo $dp9_ar ? 'English' : 'العربية'; ?></a>
			<a class="dp9-btn is-ghost is-sm" href="<?php echo esc_url( is_front_page() ? '#contact' : $dp9_home . '#contact' ); ?>"><?php echo esc_html( dp_t( array( 'en' => 'Let’s Talk', 'ar' => 'لنتحدث' ) ) ); ?></a>
		</nav>
		<button class="dp9-menu" type="button" aria-expanded="false" aria-controls="dp9-nav"><span></span><span></span><span class="dp9-sr"><?php echo esc_html( dp_t( array( 'en' => 'Menu', 'ar' => 'القائمة' ) ) ); ?></span></button>
	</div>
</header>
