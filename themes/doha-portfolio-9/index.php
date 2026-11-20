<?php
/**
 * Fallback template for pages and posts.
 */
get_header();
?>
<section class="page-hero">
	<div class="wrap">
		<h1><?php echo esc_html( is_singular() ? get_the_title() : wp_get_document_title() ); ?></h1>
	</div>
</section>
<section class="section section-tight">
	<div class="wrap prose">
		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				if ( ! is_singular() ) {
					echo '<h2><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2>';
					the_excerpt();
				} else {
					the_content();
				}
			}
		} else {
			echo '<p>' . esc_html__( 'Nothing found here.', 'doha-portfolio' ) . ' <a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Back to the home page', 'doha-portfolio' ) . '</a></p>';
		}
		?>
	</div>
</section>
<?php
get_footer();
