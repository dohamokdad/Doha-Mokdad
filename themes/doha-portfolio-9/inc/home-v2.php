<?php
/**
 * Home page v2 (8.6): editorial hero with a laptop showing real project screenshots,
 * a featured-work mosaic, a services strip and a "better way" process.
 *
 * @package Doha_Portfolio
 */

defined( 'ABSPATH' ) || exit;

/** The newest projects with a screenshot (every new project takes the first place). */
function dp_recent_shots( $n = 3, $skip = array() ) {
	$q = get_posts( array(
		'post_type'      => 'project',
		'post_status'    => 'publish',
		'posts_per_page' => $n + count( $skip ),
		'orderby'        => array( 'date' => 'DESC', 'ID' => 'DESC' ),
		'meta_query'     => array( array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ) ), // phpcs:ignore
		'no_found_rows'  => true,
	) );
	$q = array_values( array_filter( $q, function ( $p ) use ( $skip ) { return ! in_array( $p->ID, $skip, true ); } ) );
	return array_slice( $q, 0, $n );
}

/** A laptop drawn in CSS, showing one or more project screenshots in turn. */
function dp_laptop( $posts, $class = '' ) {
	if ( ! $posts ) { return; }
	$n = count( $posts );
	?>
	<div class="laptop <?php echo esc_attr( $class ); ?>" style="--n:<?php echo (int) $n; ?>" aria-hidden="true">
		<div class="laptop-lid"><div class="laptop-screen">
			<?php foreach ( $posts as $i => $p ) : $u = get_post_meta( $p->ID, '_dp_url', true ); ?>
				<figure class="laptop-slide<?php echo $n > 1 ? ' is-cycling' : ''; ?>" style="--i:<?php echo (int) $i; ?>">
					<span class="laptop-bar"><i></i><i></i><i></i><span><?php echo esc_html( dp_host( $u ) ); ?></span></span>
					<img src="<?php echo esc_url( dp_project_image( $p->ID, 'dp-large' ) ); ?>" alt="" <?php echo $i ? 'loading="lazy"' : 'fetchpriority="high"'; ?> decoding="async">
				</figure>
			<?php endforeach; ?>
		</div></div>
		<div class="laptop-base"><span></span></div>
	</div>
	<?php
}

/** "I'm Doha Mokdad, ..." from the saved name and tagline, without repeating the job title. */
function dp_hero_lead( $a ) {
	$tag = trim( (string) dp_x( $a['tagline'] ) );
	$tag = preg_replace( '/^(senior wordpress developer|مطوّرة ووردبريس أولى|مطورة ووردبريس أولى)[.،,]\s*/iu', '', $tag );
	$me  = dp_t( array( 'en' => "I'm %s, a senior WordPress developer.", 'ar' => 'أنا %s، مطوّرة ووردبريس أولى.' ) );
	return sprintf( $me, dp_x( $a['name'] ) ) . ' ' . $tag;
}

function dp_render_hero_v2( $a ) {
	$shots = 'yes' === $a['showcase'] ? dp_recent_shots( 3 ) : array();
	$total = (int) wp_count_posts( 'project' )->publish;
	$head  = ! empty( $a['headline'] ) ? $a['headline'] : '';
	if ( ! $head ) {
		$head = dp_t( array( 'en' => 'Websites built|to perform.', 'ar' => 'مواقع مبنية|لتحقق النتائج.' ) );
	}
	$lines = explode( '|', dp_x( $head ) );
	$trust = array(
		array( 'star', '15+', dp_t( array( 'en' => 'Years of experience', 'ar' => 'سنة خبرة' ) ) ),
		array( 'browser', $total, dp_t( array( 'en' => 'Websites delivered', 'ar' => 'موقعًا منجزًا' ) ) ),
		array( 'pin', (int) dp_country_count(), dp_t( array( 'en' => 'Countries', 'ar' => 'دولة' ) ) ),
		array( 'lifebuoy', dp_t( array( 'en' => 'Ongoing', 'ar' => 'دعم' ) ), dp_t( array( 'en' => 'Support after launch', 'ar' => 'مستمر بعد الإطلاق' ) ) ),
	);
	?>
	<section class="hero hero-v2" data-anim="hero">
		<div class="hero-bg" aria-hidden="true"><span class="hero-arc"></span><span class="hero-glow"></span></div>
		<div class="wrap hero-v2-inner<?php echo $shots ? '' : ' no-showcase'; ?>">
			<div class="hero-text">
				<p class="hero-badge" data-a="fade"><span class="pulse" aria-hidden="true"></span><?php echo esc_html( $a['eyebrow'] ? dp_x( $a['eyebrow'] ) : dp_t( array( 'en' => 'Senior WordPress Developer', 'ar' => 'مطوّرة ووردبريس أولى' ) ) ); ?></p>
				<h1 class="hero-headline" data-a="fade">
					<?php foreach ( $lines as $i => $l ) : ?><span class="<?php echo $i === count( $lines ) - 1 && count( $lines ) > 1 ? 'hl-accent' : ''; ?>"><?php echo esc_html( trim( $l ) ); ?></span><?php endforeach; ?>
				</h1>
				<p class="lead" data-a="fade"><?php echo esc_html( dp_hero_lead( $a ) ); ?></p>
				<div class="actions" data-a="fade">
					<a class="btn btn-accent magnetic" href="#work"><span><?php echo esc_html( dp_t( array( 'en' => 'View my work', 'ar' => 'شاهد أعمالي' ) ) ); ?></span><?php echo dp_btn_icon( 'arrow-right' ); // phpcs:ignore ?></a>
					<?php if ( $a['btn2_text'] && 0 !== strpos( (string) $a['btn2_url'], '#work' ) ) : ?>
						<a class="btn btn-ghost magnetic" href="<?php echo esc_url( $a['btn2_url'] ); ?>" <?php echo 0 === strpos( (string) $a['btn2_url'], '#' ) ? '' : 'target="_blank" rel="noopener"'; ?>><span><?php echo esc_html( dp_x( $a['btn2_text'] ) ); ?></span><?php echo dp_btn_icon( 0 === strpos( (string) $a['btn2_url'], '#' ) ? 'caret-down' : 'download' ); // phpcs:ignore ?></a>
					<?php else : ?>
						<a class="btn btn-ghost magnetic" href="#contact"><span><?php echo esc_html( dp_t( array( 'en' => "Let's talk", 'ar' => 'لنتحدث' ) ) ); ?></span><?php echo dp_btn_icon( 'arrow-right' ); // phpcs:ignore ?></a>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( $shots ) : ?><div class="hero-visual" data-a="fade"><?php dp_laptop( $shots, 'laptop-hero' ); ?></div><?php endif; ?>
		</div>
		<div class="wrap">
			<ul class="hero-trust" data-a="fade">
				<?php foreach ( $trust as $t ) : ?>
					<li><span class="trust-ico"><?php echo dp_icon( $t[0], 26 ); // phpcs:ignore ?></span><span><strong><?php echo esc_html( $t[1] ); ?></strong><small><?php echo esc_html( $t[2] ); ?></small></span></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

function dp_project_type_name( $id ) {
	$t = get_the_terms( $id, 'project_type' );
	return ( $t && ! is_wp_error( $t ) ) ? dp_x( $t[0]->name ) : '';
}

/** Featured work: a big card and two stacked ones, then the next names as quick links. */
function dp_render_projects_showcase( $a, $items, $all ) {
	$with = array_values( array_filter( $items, function ( $p ) { return has_post_thumbnail( $p ); } ) );
	if ( count( $with ) < 3 ) { $with = array_values( array_filter( $all, function ( $p ) { return has_post_thumbnail( $p ); } ) ); }
	$top  = array_slice( $with, 0, 3 );
	$ids  = wp_list_pluck( $top, 'ID' );
	$next = array_slice( array_values( array_filter( $items, function ( $p ) use ( $ids ) { return ! in_array( $p->ID, $ids, true ); } ) ), 0, 8 );
	$archive = get_post_type_archive_link( 'project' );
	$more    = $a['more_text'] ? dp_x( $a['more_text'] ) : dp_t( array( 'en' => 'View all %d projects', 'ar' => 'كل المشاريع (%d)' ) );
	?>
	<section id="work" class="section work-v2" data-anim="projects">
		<div class="wrap work-v2-inner">
			<header class="work-side">
				<p class="section-kicker kicker-line"><?php echo esc_html( $a['eyebrow'] ? dp_x( $a['eyebrow'] ) : dp_t( array( 'en' => 'Featured projects', 'ar' => 'مشاريع مختارة' ) ) ); ?></p>
				<h2><?php echo esc_html( dp_x( $a['title'] ) ); ?></h2>
				<p class="side-intro"><?php echo esc_html( sprintf( dp_t( array( 'en' => '%1$d websites for clients in %2$d countries. A few recent ones:', 'ar' => '%1$d موقعًا لعملاء في %2$d دولة. هذه بعض أحدثها:' ) ), count( $all ), (int) dp_country_count() ) ); ?></p>
				<?php if ( 'yes' === $a['show_more'] ) : ?><a class="btn btn-ghost btn-sm magnetic" href="<?php echo esc_url( $archive ); ?>"><span><?php echo esc_html( sprintf( $more, count( $all ) ) ); ?></span><?php echo dp_btn_icon( 'arrow-right' ); // phpcs:ignore ?></a><?php endif; ?>
			</header>
			<div class="work-mosaic">
				<?php foreach ( $top as $i => $p ) : $u = get_post_meta( $p->ID, '_dp_url', true ); ?>
					<a class="card mosaic-item m<?php echo (int) $i; ?>" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
						<span class="shot"><img src="<?php echo esc_url( dp_project_image( $p->ID ) ); ?>" alt="<?php echo esc_attr( get_the_title( $p ) ); ?>" loading="lazy" decoding="async"></span>
						<span class="mosaic-cap"><span><strong><?php echo esc_html( dp_x( get_the_title( $p ) ) ); ?></strong><small><?php echo esc_html( implode( ' · ', array_filter( array( dp_project_type_name( $p->ID ), dp_host( $u ) ) ) ) ); ?></small></span><span class="mosaic-go"><?php echo dp_icon( 'arrow-up-right', 18 ); // phpcs:ignore ?></span></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if ( $next ) : ?>
			<div class="wrap work-next">
				<span class="work-next-label"><?php echo esc_html( dp_t( array( 'en' => 'Also built:', 'ar' => 'ومن أعمالي أيضًا:' ) ) ); ?></span>
				<?php foreach ( $next as $p ) : ?><a href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php echo esc_html( dp_x( get_the_title( $p ) ) ); ?></a><?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
	<?php
}

/** Services as one strip of icons and names; each opens its own page. */
function dp_render_services_v2( $a ) {
	?>
	<section id="services" class="section services-v2" data-anim="services">
		<div class="wrap services-v2-inner">
			<header class="work-side">
				<p class="section-kicker kicker-line"><?php echo esc_html( $a['eyebrow'] ? dp_x( $a['eyebrow'] ) : dp_t( array( 'en' => 'What I do', 'ar' => 'ماذا أقدّم' ) ) ); ?></p>
				<h2><?php echo esc_html( dp_x( $a['title'] ) ); ?></h2>
				<?php if ( $a['intro'] ) : ?><p class="side-intro"><?php echo esc_html( dp_x( $a['intro'] ) ); ?></p><?php endif; ?>
				<a class="btn btn-ghost btn-sm magnetic" href="#contact"><span><?php echo esc_html( dp_x( 'Discuss your project' ) ); ?></span><?php echo dp_btn_icon( 'arrow-right' ); // phpcs:ignore ?></a>
			</header>
			<ul class="svc-strip">
				<?php foreach ( (array) $a['items'] as $it ) :
					$url = '';
					foreach ( dp_service_pages() as $slug => $s ) { if ( $s['card'] === $it['title'] ) { $url = dp_service_url( $slug ); } }
					$tag = $url ? 'a' : 'div';
					?>
					<li><<?php echo $tag; // phpcs:ignore ?> class="svc"<?php echo $url ? ' href="' . esc_url( $url ) . '"' : ''; ?> title="<?php echo esc_attr( dp_x( $it['text'] ) ); ?>">
						<span class="svc-ico"><?php echo dp_icon( ! empty( $it['icon'] ) ? $it['icon'] : 'check', 34 ); // phpcs:ignore ?></span>
						<span class="svc-name"><?php echo esc_html( dp_x( $it['title'] ) ); ?></span>
						<?php if ( $url ) : ?><span class="svc-more"><?php echo dp_icon( 'arrow-right', 14 ); // phpcs:ignore ?></span><?php endif; ?>
					</<?php echo $tag; // phpcs:ignore ?>></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

/** "A better way": a laptop, the promise, and the steps as a numbered list. */
function dp_render_process_v2( $a ) {
	$shot = dp_recent_shots( 1, wp_list_pluck( dp_recent_shots( 3 ), 'ID' ) );
	?>
	<section id="process" class="section process-v2" data-anim="process">
		<div class="process-v2-bg" aria-hidden="true"></div>
		<div class="wrap process-v2-inner<?php echo $shot ? '' : ' no-visual'; ?>">
			<?php if ( $shot ) : ?><div class="process-visual"><?php dp_laptop( $shot, 'laptop-tilt' ); ?></div><?php endif; ?>
			<div class="process-copy">
				<p class="section-kicker kicker-line"><?php echo esc_html( $a['eyebrow'] ? dp_x( $a['eyebrow'] ) : dp_t( array( 'en' => 'Why work with me', 'ar' => 'لماذا تعمل معي' ) ) ); ?></p>
				<h2><?php echo esc_html( dp_x( $a['title'] ) ); ?></h2>
				<?php if ( $a['intro'] ) : ?><p class="side-intro"><?php echo esc_html( dp_x( $a['intro'] ) ); ?></p><?php endif; ?>
				<a class="btn btn-ghost btn-sm magnetic" href="#contact"><span><?php echo esc_html( dp_t( array( 'en' => "Let's work together", 'ar' => 'لنعمل معًا' ) ) ); ?></span><?php echo dp_btn_icon( 'arrow-right' ); // phpcs:ignore ?></a>
			</div>
			<ol class="steps-v">
				<?php foreach ( (array) $a['items'] as $n => $it ) : ?>
					<li><span class="steps-v-num"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span><span><strong><?php echo esc_html( dp_x( $it['title'] ) ); ?></strong><small><?php echo esc_html( dp_first_sentence( dp_x( $it['text'] ) ) ); ?></small></span></li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>
	<?php
}

/** The first sentence of a text, for short labels. */
function dp_first_sentence( $t ) {
	$t = trim( (string) $t );
	return preg_match( '/^(.+?[.!?؟])(\s|$)/u', $t, $m ) ? $m[1] : $t;
}
