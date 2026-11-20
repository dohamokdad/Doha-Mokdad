<?php
/**
 * All projects (also used for project type archives).
 */
get_header();
$dp_items = array();
while ( have_posts() ) { the_post(); $dp_items[] = get_post(); }
$title = is_tax() ? single_term_title( '', false ) : __( 'All projects', 'doha-portfolio' );
?>
<section class="page-hero">
	<div class="wrap">
				<h1><?php echo esc_html( $title ); ?></h1>
		<p class="lead"><?php echo esc_html( sprintf( __( '%d WordPress websites for clients across the Gulf, Europe, North America and Asia. Hover a card to scroll the site, click to open it.', 'doha-portfolio' ), count( $dp_items ) ) ); ?></p>
	</div>
</section>
<section class="section section-tight">
	<div class="wrap">
		<?php get_template_part( 'template-parts/project-grid', null, array( 'items' => $dp_items ) ); ?>
	</div>
</section>
<?php get_footer(); ?>
