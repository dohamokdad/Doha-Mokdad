<?php
/**
 * Projects grouped by the company they were built at.
 * $args['items'] = WP_Post[], $args['per'] = cards shown per company before "View all".
 */
$dp_groups = dp_companies_with_projects( $args['items'] );
$dp_per    = isset( $args['per'] ) ? (int) $args['per'] : 6;
?>
<nav class="company-index" aria-label="<?php esc_attr_e( 'Companies', 'doha-portfolio' ); ?>">
	<?php foreach ( $dp_groups as $g ) : ?>
		<a href="#company-<?php echo esc_attr( $g['term']->slug ); ?>"><?php echo esc_html( $g['term']->name ); ?> <em><?php echo count( $g['items'] ); ?></em></a>
	<?php endforeach; ?>
</nav>
<?php foreach ( $dp_groups as $g ) :
	$t     = $g['term'];
	$count = count( $g['items'] );
	$show  = $dp_per > 0 ? array_slice( $g['items'], 0, $dp_per ) : $g['items'];
	?>
	<section class="company-group" id="company-<?php echo esc_attr( $t->slug ); ?>">
		<header class="company-head">
			<div>
				<h2><?php echo esc_html( $t->name ); ?> <span class="company-count"><?php echo esc_html( sprintf( _n( '%d project', '%d projects', $count, 'doha-portfolio' ), $count ) ); ?></span></h2>
				<?php if ( $t->description ) : ?><p><?php echo esc_html( $t->description ); ?></p><?php endif; ?>
			</div>
			<?php if ( $count > count( $show ) ) : ?>
				<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( get_term_link( $t ) ); ?>"><span><?php echo esc_html( sprintf( __( 'View all %d', 'doha-portfolio' ), $count ) ); ?></span><?php echo dp_btn_icon( 'arrow-right' ); // phpcs:ignore ?></a>
			<?php endif; ?>
		</header>
		<div class="grid">
			<?php foreach ( $show as $dp_p ) { get_template_part( 'template-parts/project-card', null, array( 'item' => $dp_p ) ); } ?>
		</div>
	</section>
<?php endforeach; ?>
