<?php
/**
 * All projects: grouped by company (default) or one filterable grid.
 */
get_header();
$dp_items = array();
while ( have_posts() ) { the_post(); $dp_items[] = get_post(); }
$dp_view  = ( isset( $_GET['view'] ) && 'all' === $_GET['view'] ) ? 'all' : 'company'; // phpcs:ignore
$dp_base  = get_post_type_archive_link( 'project' );
?>
<section class="page-hero">
	<div class="wrap">
		<h1><?php esc_html_e( 'All projects', 'doha-portfolio' ); ?></h1>
		<p class="lead"><?php echo esc_html( sprintf( __( '%d WordPress websites for clients across the Gulf, Europe, North America and Asia. Hover a card to scroll the site, click to open the case study.', 'doha-portfolio' ), count( $dp_items ) ) ); ?></p>
		<div class="view-tabs" role="tablist" aria-label="<?php esc_attr_e( 'View', 'doha-portfolio' ); ?>">
			<a role="tab" aria-selected="<?php echo 'company' === $dp_view ? 'true' : 'false'; ?>" class="<?php echo 'company' === $dp_view ? 'is-active' : ''; ?>" href="<?php echo esc_url( $dp_base ); ?>"><?php esc_html_e( 'By company', 'doha-portfolio' ); ?></a>
			<a role="tab" aria-selected="<?php echo 'all' === $dp_view ? 'true' : 'false'; ?>" class="<?php echo 'all' === $dp_view ? 'is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'view', 'all', $dp_base ) ); ?>"><?php esc_html_e( 'All, with filters', 'doha-portfolio' ); ?></a>
		</div>
	</div>
</section>
<section class="section section-tight">
	<div class="wrap">
		<?php
		if ( 'all' === $dp_view ) {
			get_template_part( 'template-parts/project-grid', null, array( 'items' => $dp_items ) );
		} else {
			get_template_part( 'template-parts/company-groups', null, array( 'items' => $dp_items, 'per' => 6 ) );
		}
		?>
	</div>
</section>
<?php get_footer(); ?>
