<?php
/**
 * Projects built at one company.
 */
get_header();
$dp_items = array();
while ( have_posts() ) { the_post(); $dp_items[] = get_post(); }
$dp_term = get_queried_object();
?>
<section class="page-hero">
	<div class="wrap">
		<p class="back-link"><a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>"><?php echo dp_icon( 'arrow-left' ); // phpcs:ignore ?> <?php esc_html_e( 'All projects', 'doha-portfolio' ); ?></a></p>
		<h1><?php echo esc_html( $dp_term->name ); ?></h1>
		<p class="lead"><?php echo esc_html( $dp_term->description ? $dp_term->description : sprintf( __( '%d projects.', 'doha-portfolio' ), count( $dp_items ) ) ); ?></p>
		<p class="muted"><?php echo esc_html( sprintf( _n( '%d project', '%d projects', count( $dp_items ), 'doha-portfolio' ), count( $dp_items ) ) ); ?></p>
	</div>
</section>
<section class="section section-tight">
	<div class="wrap">
		<?php get_template_part( 'template-parts/project-grid', null, array( 'items' => $dp_items ) ); ?>
	</div>
</section>
<?php get_footer(); ?>
