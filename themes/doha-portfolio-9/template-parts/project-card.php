<?php
/**
 * Project card. Expects $args['item'] (WP_Post) and optional $args['size'].
 */
$dp_p    = $args['item'];
$size    = isset( $args['size'] ) ? $args['size'] : 'dp-tall';
$url     = get_post_meta( $dp_p->ID, '_dp_url', true );
$country = get_post_meta( $dp_p->ID, '_dp_country', true );
$emp     = get_post_meta( $dp_p->ID, '_dp_employer', true );
$terms   = get_the_terms( $dp_p->ID, 'project_type' );
$type    = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
$logo    = dp_use_logo( $dp_p->ID );
$img     = $logo ? '' : dp_project_image( $dp_p->ID, $size );
$span    = isset( $args['span'] ) ? (int) $args['span'] : 0;
$search  = strtolower( get_the_title( $dp_p ) . ' ' . dp_host( $url ) . ' ' . $country . ' ' . ( $type ? $type->name : '' ) . ' ' . $emp );
?>
<article class="card"<?php echo $span ? ' data-span="' . $span . '"' : ''; ?> data-flip-id="p<?php echo (int) $dp_p->ID; ?>" data-type="<?php echo esc_attr( $type ? $type->slug : '' ); ?>" data-search="<?php echo esc_attr( $search ); ?>">
	<a class="card-link" href="<?php echo esc_url( get_permalink( $dp_p ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $dp_p ) ); ?>">
		<div class="bezel"><div class="browser">
			<div class="browser-bar" aria-hidden="true"><i></i><i></i><i></i><span><?php echo esc_html( $url ? dp_host( $url ) : get_the_title( $dp_p ) ); ?></span></div>
			<?php if ( $logo ) : ?>
			<div class="shot is-logo" style="--plate:<?php echo esc_attr( $logo['bg'] ); ?>">
				<img src="<?php echo esc_url( $logo['url'] ); ?>" alt="<?php echo esc_attr( get_the_title( $dp_p ) . ' logo' ); ?>" loading="lazy" decoding="async">
			</div>
			<?php else : ?>
			<div class="shot">
				<?php if ( $img ) : ?>
					<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( get_the_title( $dp_p ) . ' website screenshot' ); ?>" loading="lazy" decoding="async" width="800" height="1600">
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div><span class="card-view" aria-hidden="true"><?php echo esc_html( dp_x( 'View case study' ) ); ?> <?php echo dp_icon( 'arrow-up-right', 14 ); // phpcs:ignore ?></span></div>
		<div class="card-body">
			<h3><span><?php echo esc_html( get_the_title( $dp_p ) ); ?></span> <span class="arrow"><?php echo dp_icon( 'arrow-up-right', 15 ); // phpcs:ignore ?></span></h3>
			<p class="meta">
				<?php if ( $type ) : ?><span><?php echo esc_html( $type->name ); ?></span><?php endif; ?>
				<?php if ( $country ) : ?><span><?php echo esc_html( dp_x( $country ) ); ?></span><?php endif; ?>
				<?php if ( $emp ) : ?><span class="meta-co"><?php echo esc_html( dp_x( $emp ) ); ?></span><?php endif; ?>
				<?php if ( dp_is_changed( $dp_p->ID ) ) : ?><span class="badge-delivered"><?php echo esc_html( trim( __( 'As delivered', 'doha-portfolio' ) . ' ' . dp_delivered_label( $dp_p->ID ) ) ); ?></span><?php endif; ?>
			</p>
		</div>
	</a>
</article>
