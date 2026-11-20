<?php
/**
 * Footer for design 9 pages: brand, links and social icons in one calm row.
 *
 * @package Doha_Portfolio
 */
$dp9_home = is_front_page() ? '' : home_url( '/' );
$dp9_ar   = dp_is_ar();
$dp9_name = dp_opt( 'name' );
$dp9_soc  = array( 'linkedin.com' => 'linkedin', 'wa.me' => 'whatsapp', 't.me' => 'telegram', 'instagram.com' => 'instagram', 'youtube.com' => 'youtube', 'facebook.com' => 'facebook' );
$dp9_links = array();
foreach ( preg_split( '/[\r\n]+/', (string) dp_opt( 'socials' ) ) as $u ) {
	$u = trim( $u ); $h = $u ? dp_host( $u ) : '';
	if ( $h && isset( $dp9_soc[ $h ] ) ) { $dp9_links[] = array( $u, $dp9_soc[ $h ], ucfirst( $dp9_soc[ $h ] ) ); }
}
?>
<footer class="dp9-footer">
	<div class="dp9-wrap">
		<div class="dp9-foot-row">
			<a class="dp9-foot-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_url( DP_URI . '/assets/img/logo-mark-dark.png' ); ?>" alt="" width="99" height="26" loading="lazy">
				<span><strong><?php echo esc_html( $dp9_name ); ?></strong><small><?php echo esc_html( dp_t( array( 'en' => 'Senior WordPress Developer', 'ar' => 'مطوّرة ووردبريس أولى' ) ) ); ?></small></span>
			</a>
			<nav class="dp9-foot-nav" aria-label="<?php echo esc_attr( dp_t( array( 'en' => 'Footer', 'ar' => 'روابط' ) ) ); ?>">
				<a href="<?php echo esc_url( $dp9_home . '#work' ); ?>"><?php echo esc_html( dp_t( array( 'en' => 'Work', 'ar' => 'الأعمال' ) ) ); ?></a>
				<a href="<?php echo esc_url( $dp9_home . '#services' ); ?>"><?php echo esc_html( dp_t( array( 'en' => 'Services', 'ar' => 'الخدمات' ) ) ); ?></a>
				<a href="<?php echo esc_url( $dp9_home . '#solved' ); ?>"><?php echo esc_html( dp_t( array( 'en' => 'Case studies', 'ar' => 'حالات عملية' ) ) ); ?></a>
				<a href="<?php echo esc_url( $dp9_home . '#journey' ); ?>"><?php echo esc_html( dp_t( array( 'en' => 'About', 'ar' => 'عنّي' ) ) ); ?></a>
				<a href="<?php echo esc_url( $dp9_home . '#contact' ); ?>"><?php echo esc_html( dp_t( array( 'en' => 'Contact', 'ar' => 'تواصل' ) ) ); ?></a>
				<a href="<?php echo esc_url( dp_lang_switch_url() ); ?>" hreflang="<?php echo $dp9_ar ? 'en' : 'ar'; ?>" lang="<?php echo $dp9_ar ? 'en' : 'ar'; ?>"><?php echo $dp9_ar ? 'English' : 'العربية'; ?></a>
			</nav>
			<?php if ( $dp9_links ) : ?>
				<ul class="dp9-socials">
					<?php foreach ( $dp9_links as $l ) : ?>
						<li><a href="<?php echo esc_url( $l[0] ); ?>" target="_blank" rel="noopener me" aria-label="<?php echo esc_attr( $l[2] ); ?>" title="<?php echo esc_attr( $l[2] ); ?>"><?php echo dp9_icon( $l[1], 18 ); // phpcs:ignore ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<p class="dp9-copy">© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $dp9_name ); ?>. <?php echo esc_html( dp_t( array( 'en' => 'All rights reserved.', 'ar' => 'جميع الحقوق محفوظة.' ) ) ); ?></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
