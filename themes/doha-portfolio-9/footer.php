</main>
<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) : ?>
<footer class="site-footer">
	<div class="wrap footer-inner">
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( dp_opt( 'name' ) ); ?>, <?php echo esc_html( dp_opt( 'role' ) ); ?></p>
		<?php dp_render_socials( 'socials socials-footer' ); ?>
		<p class="footer-links">
			<a href="mailto:<?php echo esc_attr( dp_opt( 'email' ) ); ?>"><?php esc_html_e( 'Email', 'doha-portfolio' ); ?></a>
			<a href="<?php echo esc_url( dp_opt( 'linkedin' ) ); ?>" target="_blank" rel="noopener">LinkedIn</a>
			<a href="<?php echo esc_url( dp_opt( 'cv_url' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Download CV', 'doha-portfolio' ); ?></a>
		</p>
	</div>
</footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
