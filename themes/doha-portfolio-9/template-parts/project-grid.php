<?php
/**
 * Filterable grid of projects. $args['items'] = array of WP_Post.
 */
$dp_items   = $args['items'];
$dp_filters = ! isset( $args['filters'] ) || $args['filters'];
$dp_bento   = ! empty( $args['bento'] );
$types = get_terms( array( 'taxonomy' => 'project_type', 'hide_empty' => true ) );
// Count only the projects shown in this grid.
$counts = array();
foreach ( $dp_items as $dp_p ) {
	$ts = get_the_terms( $dp_p->ID, 'project_type' );
	if ( $ts && ! is_wp_error( $ts ) ) { $counts[ $ts[0]->slug ] = ( isset( $counts[ $ts[0]->slug ] ) ? $counts[ $ts[0]->slug ] : 0 ) + 1; }
}
if ( ! is_wp_error( $types ) ) {
	$types = array_filter( $types, function ( $t ) use ( $counts ) { return ! empty( $counts[ $t->slug ] ); } );
}
?>
<div class="filters" data-grid<?php echo $dp_filters ? '' : ' hidden'; ?>>
	<div class="chips" role="group" aria-label="<?php esc_attr_e( 'Filter by type', 'doha-portfolio' ); ?>">
		<button class="chip is-active" data-filter="*"><?php esc_html_e( 'All', 'doha-portfolio' ); ?> <em><?php echo count( $dp_items ); ?></em></button>
		<?php if ( ! is_wp_error( $types ) ) : foreach ( $types as $t ) : ?>
			<button class="chip" data-filter="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?> <em><?php echo (int) $counts[ $t->slug ]; ?></em></button>
		<?php endforeach; endif; ?>
	</div>
	<label class="search">
		<span class="sr"><?php esc_html_e( 'Search projects', 'doha-portfolio' ); ?></span>
		<input type="search" placeholder="<?php esc_attr_e( 'Search by name, country or company', 'doha-portfolio' ); ?>" data-search-input>
	</label>
</div>
<div class="grid<?php echo $dp_bento ? ' is-bento' : ''; ?>" data-grid-items>
	<?php
	// Bento rhythm on a 12-column grid: wide + narrow, three equal, narrow + wide. main.js re-applies it after filtering.
	$dp_rhythm = array( 7, 5, 4, 4, 4, 5, 7 );
	foreach ( $dp_items as $dp_i => $dp_p ) {
		get_template_part( 'template-parts/project-card', null, array( 'item' => $dp_p, 'span' => $dp_bento ? $dp_rhythm[ $dp_i % 7 ] : 0 ) );
	}
	?>
</div>
<p class="empty" hidden><?php esc_html_e( 'No projects match your search.', 'doha-portfolio' ); ?></p>
