<?php
/**
 * Single project: case study.
 */
get_header();
while ( have_posts() ) :
	the_post();
	$pid      = get_the_ID();
	$url      = get_post_meta( $pid, '_dp_url', true );
	$country  = get_post_meta( $pid, '_dp_country', true );
	$emp      = get_post_meta( $pid, '_dp_employer', true );
	$terms    = get_the_terms( $pid, 'project_type' );
	$type     = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	$prev     = get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );
	$summary  = trim( (string) get_post_meta( $pid, '_dp_summary', true ) );
	$theme    = trim( (string) get_post_meta( $pid, '_dp_theme', true ) );
	$builder  = trim( (string) get_post_meta( $pid, '_dp_builder', true ) );
	$plugins  = dp_case_list( $pid, '_dp_plugins' );
	$tech     = dp_case_list( $pid, '_dp_tech' );
	$features = dp_case_list( $pid, '_dp_features' );
	$uiux     = dp_case_list( $pid, '_dp_uiux' );
	$phases   = dp_case_list( $pid, '_dp_phases' );
	$content  = trim( get_the_content() );
	$changed  = dp_is_changed( $pid );
	$gallery  = $changed ? dp_gallery_ids( $pid ) : array();
	$when     = dp_delivered_label( $pid );
	?>
	<section class="page-hero project-hero">
		<div class="wrap">
			<p class="back-link"><a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>"><?php echo dp_icon( 'arrow-left' ); // phpcs:ignore ?> <?php esc_html_e( 'All projects', 'doha-portfolio' ); ?></a></p>
			<h1><?php the_title(); ?></h1>
			<?php if ( $summary ) : ?><p class="lead project-lead"><?php echo esc_html( $summary ); ?></p><?php endif; ?>
			<?php if ( $changed ) : ?><p class="delivered-note"><?php echo esc_html( sprintf( __( 'Screenshots show the site as I delivered it%s. The live site has changed or gone offline since.', 'doha-portfolio' ), $when ? sprintf( __( ' in %s', 'doha-portfolio' ), $when ) : '' ) ); ?></p><?php endif; ?>
			<?php if ( $url ) : ?>
				<p class="project-cta"><a class="btn magnetic" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><span><?php echo esc_html( sprintf( __( 'Visit %s', 'doha-portfolio' ), dp_host( $url ) ) ); ?></span><?php echo dp_btn_icon( 'arrow-up-right' ); // phpcs:ignore ?></a></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="section section-tight">
		<div class="wrap">
			<div class="bezel bezel-lg"><div class="browser browser-lg" data-scroll-shot>
				<div class="browser-bar" aria-hidden="true"><i></i><i></i><i></i><span><?php echo esc_html( $url ? dp_host( $url ) : get_the_title() ); ?></span></div>
				<?php $dp_logo = dp_use_logo( $pid ); ?>
				<?php if ( $dp_logo ) : ?>
					<div class="shot is-logo is-logo-lg" style="--plate:<?php echo esc_attr( $dp_logo['bg'] ); ?>"><img src="<?php echo esc_url( $dp_logo['url'] ); ?>" alt="<?php echo esc_attr( get_the_title() . ' logo' ); ?>"></div>
				<?php else : ?>
					<div class="shot"><img src="<?php echo esc_url( dp_project_image( $pid, 'full' ) ); ?>" alt="<?php echo esc_attr( get_the_title() . ' website screenshot' ); ?>" width="1200" height="2400"></div>
				<?php endif; ?>
			</div></div>

			<?php if ( count( $gallery ) > 1 ) : ?>
				<div class="delivered-gallery">
					<?php foreach ( array_slice( $gallery, 1 ) as $gid ) : ?>
						<a class="browser" href="<?php echo esc_url( wp_get_attachment_image_url( $gid, 'full' ) ); ?>" target="_blank" rel="noopener"><span class="shot"><?php echo wp_get_attachment_image( $gid, 'dp-tall', false, array( 'loading' => 'lazy' ) ); ?></span></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="case">
				<aside class="case-side" aria-label="<?php esc_attr_e( 'Project facts', 'doha-portfolio' ); ?>">
					<dl class="case-facts">
						<?php if ( $type ) : ?><div><dt><?php esc_html_e( 'Type', 'doha-portfolio' ); ?></dt><dd><a href="<?php echo esc_url( get_term_link( $type ) ); ?>"><?php echo esc_html( $type->name ); ?></a></dd></div><?php endif; ?>
						<?php if ( $country ) : ?><div><dt><?php esc_html_e( 'Country', 'doha-portfolio' ); ?></dt><dd><?php echo esc_html( dp_x( $country ) ); ?></dd></div><?php endif; ?>
						<?php if ( $emp ) : $dp_ct = get_the_terms( $pid, 'project_company' ); ?><div><dt><?php esc_html_e( 'Built at', 'doha-portfolio' ); ?></dt><dd><?php if ( $dp_ct && ! is_wp_error( $dp_ct ) ) : ?><a href="<?php echo esc_url( get_term_link( $dp_ct[0] ) ); ?>"><?php echo esc_html( dp_x( $emp ) ); ?></a><?php else : echo esc_html( dp_x( $emp ) ); endif; ?></dd></div><?php endif; ?>
						<div><dt><?php esc_html_e( 'Platform', 'doha-portfolio' ); ?></dt><dd>WordPress</dd></div>
						<?php if ( $theme ) : ?><div><dt><?php esc_html_e( 'Theme', 'doha-portfolio' ); ?></dt><dd><?php echo esc_html( $theme ); ?></dd></div><?php endif; ?>
						<?php if ( $builder ) : ?><div><dt><?php esc_html_e( 'Page builder', 'doha-portfolio' ); ?></dt><dd><?php echo esc_html( $builder ); ?></dd></div><?php endif; ?>
					</dl>
					<?php if ( $plugins ) : ?>
						<h2 class="case-label"><?php esc_html_e( 'Plugins', 'doha-portfolio' ); ?></h2>
						<ul class="chips-static"><?php foreach ( $plugins as $x ) : ?><li><?php echo esc_html( $x ); ?></li><?php endforeach; ?></ul>
					<?php endif; ?>
					<?php if ( $tech ) : ?>
						<h2 class="case-label"><?php esc_html_e( 'Technologies', 'doha-portfolio' ); ?></h2>
						<ul class="chips-static"><?php foreach ( $tech as $x ) : ?><li><?php echo esc_html( $x ); ?></li><?php endforeach; ?></ul>
					<?php endif; ?>
				</aside>

				<div class="case-main">
					<?php if ( $features ) : ?>
						<section class="case-block">
							<h2><?php esc_html_e( 'Key features', 'doha-portfolio' ); ?></h2>
							<ul class="feature-list"><?php foreach ( $features as $x ) : ?><li><?php echo esc_html( $x ); ?></li><?php endforeach; ?></ul>
						</section>
					<?php endif; ?>
					<?php if ( $uiux ) : ?>
						<section class="case-block">
							<h2><?php esc_html_e( 'UI and UX', 'doha-portfolio' ); ?></h2>
							<ul class="feature-list"><?php foreach ( $uiux as $x ) : ?><li><?php echo esc_html( $x ); ?></li><?php endforeach; ?></ul>
						</section>
					<?php endif; ?>
					<?php if ( $phases ) : ?>
						<section class="case-block">
							<h2><?php esc_html_e( 'How it was delivered', 'doha-portfolio' ); ?></h2>
							<ol class="phases">
								<?php foreach ( $phases as $x ) :
									$parts = array_map( 'trim', explode( ':', $x, 2 ) ); ?>
									<li><strong><?php echo esc_html( $parts[0] ); ?></strong><?php if ( ! empty( $parts[1] ) ) : ?><span><?php echo esc_html( ucfirst( $parts[1] ) ); ?></span><?php endif; ?></li>
								<?php endforeach; ?>
							</ol>
						</section>
					<?php endif; ?>
					<?php if ( $content ) : ?>
						<section class="case-block prose-inline"><?php the_content(); ?></section>
					<?php endif; ?>
					<?php if ( ! $features && ! $uiux && ! $phases && ! $content ) : ?>
						<p class="muted"><?php esc_html_e( 'Case study coming soon. Visit the live site to see the project.', 'doha-portfolio' ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<nav class="pager" aria-label="<?php esc_attr_e( 'More projects', 'doha-portfolio' ); ?>">
				<?php if ( $prev ) : ?><a href="<?php echo esc_url( get_permalink( $prev ) ); ?>"><?php echo dp_icon( 'arrow-left' ); // phpcs:ignore ?> <?php echo esc_html( get_the_title( $prev ) ); ?></a><?php else : ?><span></span><?php endif; ?>
				<?php if ( $next ) : ?><a href="<?php echo esc_url( get_permalink( $next ) ); ?>"><?php echo esc_html( get_the_title( $next ) ); ?> <?php echo dp_icon( 'arrow-right' ); // phpcs:ignore ?></a><?php endif; ?>
			</nav>
		</div>
	</section>
	<?php
endwhile;
get_footer();
