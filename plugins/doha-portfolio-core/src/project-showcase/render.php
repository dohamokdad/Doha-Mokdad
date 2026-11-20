<?php
/**
 * Front-end markup of the Project Showcase block (dynamic, so it always shows current projects).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner content (unused).
 * @var WP_Block $block      Block instance.
 *
 * @package Doha_Portfolio_Core
 */

defined( 'ABSPATH' ) || exit;

$dpc_ids = dpc_showcase_ids(
	array(
		'count'    => $attributes['count'] ?? 4,
		'type'     => $attributes['projectType'] ?? '',
		'featured' => ! empty( $attributes['featuredOnly'] ),
	)
);

if ( ! $dpc_ids ) {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		printf( '<p %s>%s</p>', get_block_wrapper_attributes(), esc_html__( 'No projects with a screenshot match these settings yet.', 'doha-portfolio' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core escapes wrapper attributes.
	}
	return;
}

$dpc_cols = max( 1, min( 4, (int) ( $attributes['columns'] ?? 2 ) ) );
$dpc_crop = ( $attributes['imageCrop'] ?? 'top' ) === 'center' ? 'center' : 'top';
$dpc_wrap = get_block_wrapper_attributes(
	array(
		'class' => 'dpc-showcase is-crop-' . $dpc_crop,
		'style' => '--dpc-cols:' . $dpc_cols,
	)
);
?>
<ul <?php echo $dpc_wrap; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<?php foreach ( $dpc_ids as $dpc_i => $dpc_id ) : ?>
		<?php
		$dpc_types   = get_the_terms( $dpc_id, 'project_type' );
		$dpc_type    = ( $dpc_types && ! is_wp_error( $dpc_types ) ) ? $dpc_types[0]->name : '';
		$dpc_country = (string) get_post_meta( $dpc_id, '_dp_country', true );
		$dpc_meta    = implode( ' · ', array_filter( array( $dpc_type, $dpc_country ) ) );
		?>
		<li class="dpc-showcase__item">
			<a class="dpc-showcase__link" href="<?php echo esc_url( get_permalink( $dpc_id ) ); ?>">
				<span class="dpc-showcase__media">
					<?php
					echo get_the_post_thumbnail(
						$dpc_id,
						'large',
						array(
							'loading'  => $dpc_i < 2 ? 'eager' : 'lazy',
							'decoding' => 'async',
							'alt'      => sprintf( /* translators: %s: project name */ __( '%s website', 'doha-portfolio' ), get_the_title( $dpc_id ) ),
						)
					);
					?>
				</span>
				<span class="dpc-showcase__title"><?php echo esc_html( get_the_title( $dpc_id ) ); ?></span>
				<?php if ( ! empty( $attributes['showMeta'] ) && $dpc_meta ) : ?>
					<span class="dpc-showcase__meta"><?php echo esc_html( $dpc_meta ); ?></span>
				<?php endif; ?>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
