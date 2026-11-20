<?php
/**
 * Page sections. Each section has defaults (the CV content) and a render function.
 * Used by the Elementor widgets and by the fallback home page when Elementor is off.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Default content for every section. */
function dp_section_defaults( $section ) {
	switch ( $section ) {
		case 'hero':
			return array(
				'eyebrow'   => '',
				'name'      => dp_opt( 'name' ),
				'tagline'   => dp_opt( 'tagline' ),
				'btn1_text' => 'Discuss your project',
				'btn1_url'  => '#contact',
				'btn2_text' => 'View my work',
				'btn2_url'  => '#work',
				'status'    => 'Available for new projects. I usually reply within one working day.',
				'showcase'  => 'yes',
				'marquee'   => 'yes',
			);
		case 'projects':
			return array(
				'eyebrow'      => '',
				'title'        => 'Selected websites',
				'source'       => 'featured',
				'type'         => '',
				'limit'        => 24,
				'show_filters' => 'yes',
				'show_more'    => 'yes',
				'more_text'    => '',
				'layout'       => 'showcase',
				'per_company'  => 3,
			);
		case 'services':
			return array(
				'eyebrow' => '',
				'title'   => 'What I can do for you',
				'intro'   => 'Pick what you need, or tell me about the problem and I will suggest the right fix.',
				'items'   => array(
					array( 'icon' => 'code', 'title' => 'New WordPress websites', 'text' => 'Business and corporate sites built from your Figma, PSD or brand, responsive, fast and easy for your team to edit with Elementor or Gutenberg.' ),
					array( 'icon' => 'shopping-cart', 'title' => 'WooCommerce stores', 'text' => 'Online stores and multi-vendor marketplaces: products, payments, shipping, Arabic and English, set up to sell from day one.' ),
					array( 'icon' => 'lightning', 'title' => 'Speed optimisation', 'text' => 'Slow site? I find what is holding it back and fix it: images, caching, scripts, hosting, Core Web Vitals.' ),
					array( 'icon' => 'shield-check', 'title' => 'Hacked site recovery', 'text' => 'Malware cleanup, hardening and backups, so a compromised site is clean, secure and back online.' ),
					array( 'icon' => 'arrows-left-right', 'title' => 'Migrations', 'text' => 'Moving to new hosting, a new domain or onto WordPress from another platform, without losing content or rankings.' ),
					array( 'icon' => 'lifebuoy', 'title' => 'Care and support', 'text' => 'Monthly updates, fixes and small changes, with someone who knows your site and answers quickly.' ),
				),
			);
		case 'process':
			return array(
				'eyebrow' => '',
				'title'   => 'A clear path from idea to launch',
				'intro'   => 'No surprises. You always know what is happening, what is next, and what it costs.',
				'items'   => array(
					array( 'title' => 'Understand', 'text' => 'A short call or message thread about your goals, audience, content and deadline. You get a clear scope and estimate before any work starts.' ),
					array( 'title' => 'Build', 'text' => 'Design handoff to a working site on a staging link you can follow live, with regular updates and your feedback built in.' ),
					array( 'title' => 'Launch and care', 'text' => 'Testing on every device, speed and SEO checks, then launch. I stay on for fixes, updates and the next improvements.' ),
				),
			);
		case 'experience':
			return array(
				'eyebrow' => '',
				'title'   => 'Experience',
				'intro'   => '15+ years, 240+ websites and clients in 19 countries, from Dubai agencies to schools in Japan.',
				'items'   => array(
					array( 'role' => 'Senior WordPress Developer', 'company' => 'EchoVision', 'note' => 'Digital marketing agency', 'dates' => 'May 2025 - Present', 'location' => 'Dubai, UAE', 'bullets' => "Build and maintain WordPress and Elementor Pro sites for Dubai and Gulf clients in real estate, events, logistics, holding companies and F&B.\nDeveloped a multi-client content-calendar platform with branded pages per client, a digital menu and an AI chatbot site for the agency.\nOwn QA, speed optimisation, site migrations and security, including cleaning up and hardening compromised client sites." ),
					array( 'role' => 'Senior WordPress Developer', 'company' => 'Sketchat Studio', 'note' => '', 'dates' => 'Mar 2017 - Jan 2026', 'location' => 'Syria', 'bullets' => "Converted Figma, PSD and Illustrator designs into pixel-perfect, responsive WordPress themes.\nBuilt custom features in PHP (OOP), MySQL, JavaScript, jQuery and AJAX; extended sites with custom plugins and API integrations.\nRan cross-browser, device and UI/UX testing before launch and improved existing systems with new features." ),
					array( 'role' => 'Senior WordPress Developer', 'company' => 'Freelance', 'note' => '', 'dates' => 'Jan 2009 - May 2025', 'location' => 'Remote', 'bullets' => "Delivered WordPress projects end to end for clients in 12+ countries: discovery, design handoff, build, launch and support.\nBuilt WooCommerce stores, multi-vendor marketplaces and education platforms for schools and academies in the UK, Japan, Saudi Arabia and Turkey.\nAudited and repaired legacy code, fixed performance issues and advised on SEO best practice." ),
					array( 'role' => 'WordPress Developer', 'company' => 'Markettcom', 'note' => 'Brand licensing agency', 'dates' => 'Mar 2023 - May 2023', 'location' => 'Dubai, UAE', 'bullets' => "Fixed and improved the website, online store and mobile app; optimised navigation, collections, search and homepage.\nMonitored analytics and conversion data and reported on performance with recommendations for growth." ),
					array( 'role' => 'Senior Web Developer', 'company' => 'ProITs', 'note' => '', 'dates' => 'Sep 2015 - May 2017', 'location' => 'Dubai, UAE', 'bullets' => "Designed and built 60+ corporate websites for the Timgad Group's trading and manufacturing companies and other Dubai clients.\nGathered requirements, designed layouts, managed content and supported hosting: site transfers and backups." ),
					array( 'role' => 'Short-term and contract roles', 'company' => 'MicroSolutions, NHTAM, Syriatel, Zayer', 'note' => '', 'dates' => '2016 - 2019', 'location' => 'Syria, Saudi Arabia', 'bullets' => 'Rebuilt the MicroSolutions website; developed services and client sites for NHTAM; built web services for Syriatel mobile apps; set up Zoho CRM and bilingual content for Zayer.' ),
					array( 'role' => 'Hosting Technical Support', 'company' => 'Syrian Computer Society', 'note' => '', 'dates' => 'Jul 2014 - Oct 2015', 'location' => 'Syria', 'bullets' => 'Supported shared, VPS, dedicated and co-located hosting for government and VIP clients; managed plans, invoices and domains in PBAS and Plesk.' ),
					array( 'role' => 'WordPress Developer', 'company' => 'UCG, United Communication Group', 'note' => '', 'dates' => 'May 2015', 'location' => 'Dubai, UAE', 'bullets' => 'Built WordPress sites for UCG and group companies: ECC, FITOUT and Maqta Gateway.' ),
					array( 'role' => 'Information Systems Developer', 'company' => 'Game Power 7', 'note' => 'Spacetoon Media Group', 'dates' => 'Aug 2013 - Mar 2014', 'location' => 'Syria', 'bullets' => 'Administered and developed business applications; built the Game Power 7 website and distribution-centres portal.' ),
					array( 'role' => 'Earlier experience', 'company' => 'Teaching and development', 'note' => '', 'dates' => '2009 - 2013', 'location' => 'Syria, Saudi Arabia', 'bullets' => 'Web Programming Trainer and ICDL instructor at Paradise Computer Center (Al Fardos); Informatics Teacher at two Damascus schools; ASP.NET Web Developer at DRC Company.' ),
				),
			);
		case 'skills':
			return array(
				'eyebrow'   => '',
				'title'     => 'Skills and education',
				'groups'    => array(
					array( 'title' => 'WordPress', 'tags' => 'Custom themes, Custom plugins, Elementor Pro, Gutenberg, ACF, WooCommerce, Multi-vendor marketplaces, LMS (LearnPress / Eduma), Multilingual & RTL' ),
					array( 'title' => 'Front end', 'tags' => 'HTML5, CSS3 / Sass, JavaScript (ES6+), jQuery, AJAX, Responsive design, Figma / PSD to WordPress' ),
					array( 'title' => 'Back end', 'tags' => 'PHP (OOP), MySQL, REST API, Third-party integrations' ),
					array( 'title' => 'Operations', 'tags' => 'Migrations, Backups, Performance & Core Web Vitals, Malware cleanup & hardening, cPanel / Plesk, Domains & DNS, Technical SEO' ),
				),
				'education' => array(
					array( 'degree' => 'BSc (Hons) Computer Science', 'details' => 'University of Greenwich, 2010-2014, Second Class Honours' ),
					array( 'degree' => 'BTEC Professional Diploma in Computing', 'details' => 'Edexcel, 2010-2011' ),
					array( 'degree' => 'Computer Software Engineering, Programming', 'details' => 'Technical Computer College (TCC), 2008-2010' ),
				),
				'languages' => 'Arabic (native), English (professional)',
			);
		case 'recognition':
			$img = DP_URI . '/assets/img/recognition/';
			return array(
				'eyebrow' => '',
				'title'   => 'Recognition',
				'intro'   => 'Recognition from the teams I worked with.',
				'items'   => array(
					array( 'title' => 'Employee of the Month', 'org' => 'ProITs, Web Development department', 'date' => 'February 2016', 'image' => array( 'url' => $img . 'proits-employee-of-the-month-2016.jpg' ) ),
					array( 'title' => 'Great Job appreciation card', 'org' => 'Syriatel Mobile Telecom. Probation appraisal: Successful Performer, 4.06 out of 5', 'date' => 'October 2016', 'image' => array( 'url' => $img . 'syriatel-great-job-2016.jpg' ) ),
				),
					'also'    => '',
			);
		case 'certificates':
			return array(
				'eyebrow' => '',
				'title'   => 'Certificates',
				'intro'   => 'Degrees, courses and tests since 2009, newest first.',
				'items'   => dp_certificates_data(),
				'visible' => 8,
			);
		case 'contact':
			return array(
				'eyebrow'  => '',
				'title'    => 'Have a WordPress project in mind?',
				'text'     => 'New builds, redesigns, WooCommerce stores, migrations, or a site that needs rescuing. Tell me about it.',
				'email'    => dp_opt( 'email' ),
				'phone'    => dp_opt( 'phone' ),
				'linkedin' => dp_opt( 'linkedin' ),
				'location' => dp_opt( 'location' ),
			);
	}
	return array();
}

/**
 * Company logos for the Experience timeline, matched by name.
 * A logo in the images pack (doha-portfolio-data/companies/{slug}.png|jpg|webp) replaces the bundled one,
 * so new or better logos can be added without a theme update.
 */
function dp_company_logo_map() {
	return array(
		'echovision'               => array( 'EchoVision', '#08111f' ),
		'sketchat-studio'          => array( 'Sketchat Studio', '#fffbe6' ),
		'freelance'                => array( 'Freelance', '' ),
		'markettcom'               => array( 'Markettcom', '', 'M' ),
		'proits'                   => array( 'ProITs', '', 'PI' ),
		'microsolutions'           => array( 'MicroSolutions', '#ffffff' ),
		'nhtam'                    => array( 'NHTAM', '', 'NHTAM' ),
		'syriatel'                 => array( 'Syriatel', '#e42320' ),
		'zayer'                    => array( 'Zayer', '#ffffff' ),
		'syrian-computer-society'  => array( 'Syrian Computer Society', '', 'SCS' ),
		'ucg'                      => array( 'UCG, United Communication Group', '', 'UCG' ),
		'game-power-7'             => array( 'Game Power 7', '#ffffff' ),
		'paradise-computer-center' => array( 'Teaching and development', '#ffffff' ),
	);
}

function dp_company_logo( $slug ) {
	$map = dp_company_logo_map();
	if ( ! isset( $map[ $slug ] ) ) { return null; }
	$bg = $map[ $slug ][1] ? $map[ $slug ][1] : '#ffffff';
	foreach ( array( 'png', 'jpg', 'webp' ) as $ext ) {
		if ( function_exists( 'dp_data_path' ) && file_exists( dp_data_path( 'companies/' . $slug . '.' . $ext ) ) ) {
			return array( 'url' => dp_data_url( 'companies/' . $slug . '.' . $ext ), 'bg' => $bg );
		}
	}
	if ( 'freelance' === $slug ) { return array( 'url' => DP_URI . '/assets/img/logo-mark.png', 'dark' => DP_URI . '/assets/img/logo-mark-light.png', 'bg' => '' ); }
	if ( file_exists( DP_DIR . '/assets/img/companies/' . $slug . '.png' ) ) {
		return array( 'url' => DP_URI . '/assets/img/companies/' . $slug . '.png', 'bg' => $bg );
	}
	return array( 'url' => '', 'bg' => '', 'initials' => isset( $map[ $slug ][2] ) ? $map[ $slug ][2] : $map[ $slug ][0] );
}

function dp_company_logos_html( $company ) {
	$company = trim( (string) $company );
	$map     = dp_company_logo_map();
	$slugs   = array();
	foreach ( $map as $slug => $m ) { if ( 0 === strcasecmp( $m[0], $company ) ) { $slugs[] = $slug; } }
	if ( ! $slugs ) {
		foreach ( array_map( 'trim', explode( ',', $company ) ) as $part ) {
			foreach ( $map as $slug => $m ) { if ( 0 === strcasecmp( $m[0], $part ) || 0 === strcasecmp( $slug, sanitize_title( $part ) ) ) { $slugs[] = $slug; } }
		}
	}
	$slugs = array_slice( array_unique( $slugs ), 0, 4 );
	if ( ! $slugs ) { return ''; }
	$out = '';
	foreach ( $slugs as $slug ) {
		$l = dp_company_logo( $slug );
		if ( ! $l ) { continue; }
		$name = $map[ $slug ][0];
		if ( ! empty( $l['url'] ) ) {
			$style = $l['bg'] ? ' style="--plate:' . esc_attr( $l['bg'] ) . '"' : '';
			$out  .= '<span class="co-logo' . ( $l['bg'] ? '' : ' is-bare' ) . '"' . $style . ' title="' . esc_attr( $name ) . '">';
			$out  .= '<img src="' . esc_url( $l['url'] ) . '" alt="' . esc_attr( $name . ' logo' ) . '" loading="lazy" decoding="async"' . ( ! empty( $l['dark'] ) ? ' class="logo-light"' : '' ) . '>';
			if ( ! empty( $l['dark'] ) ) { $out .= '<img src="' . esc_url( $l['dark'] ) . '" alt="" loading="lazy" decoding="async" class="logo-dark">'; }
			$out  .= '</span>';
		} else {
			$ini = $l['initials'];
			$out  .= '<span class="co-logo is-mono" title="' . esc_attr( $name ) . '" aria-hidden="true">' . esc_html( $ini ) . '</span>';
		}
	}
	return $out ? '<span class="co-logos">' . $out . '</span>' : '';
}

/** Phosphor icon (regular weight) as inline SVG. */
function dp_icon( $name, $size = 16 ) {
	$paths = dp_icon_paths();
	if ( ! isset( $paths[ $name ] ) ) { return ''; }
	return '<svg class="icon" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/** Trailing icon nested in its own circle (button-in-button). */
function dp_btn_icon( $name ) {
	return '<span class="btn-ico" aria-hidden="true">' . dp_icon( $name, 15 ) . '</span>';
}

function dp_args( $section, $args ) {
	$d = dp_section_defaults( $section );
	foreach ( $d as $k => $v ) {
		if ( ! isset( $args[ $k ] ) || '' === $args[ $k ] || ( is_array( $v ) && empty( $args[ $k ] ) ) ) {
			$args[ $k ] = $v;
		}
	}
	return $args;
}

function dp_csv( $s ) {
	return array_filter( array_map( 'trim', explode( ',', (string) $s ) ) );
}

/* ------------------------------------------------------------------ */

function dp_render_hero( $a = array() ) {
	$a     = dp_args( 'hero', $a );
	if ( function_exists( 'dp_render_hero_v2' ) ) { dp_render_hero_v2( $a ); return; }
	$feat  = dp_get_projects( true );
	$names = array();
	if ( 'yes' === $a['marquee'] ) { foreach ( $feat as $p ) { $names[] = get_the_title( $p ); } }
	$shots = array();
	if ( 'yes' === $a['showcase'] ) {
		// The 3 newest projects that have a screenshot: every new project takes the first place automatically.
		$shots = get_posts( array(
			'post_type'      => 'project',
			'post_status'    => 'publish',
			'posts_per_page' => 3,
			'orderby'        => array( 'date' => 'DESC', 'ID' => 'DESC' ),
			'meta_query'     => array( array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ) ), // phpcs:ignore
			'no_found_rows'  => true,
		) );
		if ( count( $shots ) < 3 ) {
			$with  = array_values( array_filter( $feat, function ( $p ) { return has_post_thumbnail( $p ); } ) );
			$shots = array_slice( count( $with ) >= 3 ? $with : $feat, 0, 3 );
		}
	}
	?>
	<section class="hero" data-anim="hero">
		<div class="hero-bg" aria-hidden="true"><span class="hero-grid"></span><span class="blob b1"></span><span class="blob b2"></span></div>
		<div class="wrap hero-inner<?php echo $shots ? '' : ' no-showcase'; ?>">
			<div class="hero-text">
				<?php if ( $a['eyebrow'] ) : ?><p class="hero-kicker"><?php echo esc_html( dp_x( $a['eyebrow'] ) ); ?></p><?php endif; ?>
				<h1 class="hero-title" data-split="chars"><?php echo esc_html( dp_x( $a['name'] ) ); ?></h1>
				<p class="lead" data-a="fade"><?php echo esc_html( dp_x( $a['tagline'] ) ); ?></p>
				<div class="actions" data-a="fade">
					<?php if ( $a['btn1_text'] ) : ?><a class="btn magnetic" href="<?php echo esc_url( $a['btn1_url'] ); ?>"><span><?php echo esc_html( dp_x( $a['btn1_text'] ) ); ?></span><?php echo dp_btn_icon( 'arrow-right' ); // phpcs:ignore ?></a><?php endif; ?>
					<?php if ( $a['btn2_text'] ) : ?><a class="btn btn-ghost magnetic" href="<?php echo esc_url( $a['btn2_url'] ); ?>" target="_blank" rel="noopener"><span><?php echo esc_html( dp_x( $a['btn2_text'] ) ); ?></span><?php echo dp_btn_icon( 0 === strpos( $a['btn2_url'], '#' ) ? 'caret-down' : 'download' ); // phpcs:ignore ?></a><?php endif; ?>
				</div>
				<ul class="hero-stats" data-a="fade"><li><strong data-count="15" data-suffix="+">15+</strong> <?php echo esc_html( dp_x( 'years' ) ); ?></li><li><strong data-count="<?php echo (int) wp_count_posts( 'project' )->publish; ?>"><?php echo (int) wp_count_posts( 'project' )->publish; ?></strong> <?php echo esc_html( dp_x( 'websites' ) ); ?></li><li><strong data-count="<?php echo (int) dp_country_count(); ?>"><?php echo (int) dp_country_count(); ?></strong> <?php echo esc_html( dp_x( 'countries' ) ); ?></li></ul>
					<?php if ( $a['status'] && '-' !== trim( $a['status'] ) ) : ?><p class="hero-status" data-a="fade"><span class="pulse" aria-hidden="true"></span><?php echo esc_html( dp_x( $a['status'] ) ); ?></p><?php endif; ?>
			</div>
			<?php if ( $shots ) : ?>
				<div class="showcase" data-a="showcase">
					<?php foreach ( $shots as $i => $p ) : $u = get_post_meta( $p->ID, '_dp_url', true ); ?>
						<a class="show-card s<?php echo (int) $i; ?>" href="<?php echo esc_url( get_permalink( $p ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $p ) ); ?>">
							<span class="bezel"><span class="browser">
								<span class="browser-bar" aria-hidden="true"><i></i><i></i><i></i><span><?php echo esc_html( dp_host( $u ) ); ?></span></span>
								<span class="shot"><img src="<?php echo esc_url( dp_project_image( $p->ID ) ); ?>" alt="" width="800" height="1600" <?php echo $i ? 'loading="lazy"' : 'fetchpriority="high"'; ?>></span>
							</span></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php if ( $names ) : ?>
		<section class="marquee" aria-label="<?php esc_attr_e( 'Selected clients', 'doha-portfolio' ); ?>">
			<div class="marquee-track">
				<?php for ( $r = 0; $r < 2; $r++ ) : ?>
					<ul <?php echo $r ? 'aria-hidden="true"' : ''; ?>><?php foreach ( $names as $n ) : ?><li><?php echo esc_html( dp_x( $n ) ); ?></li><?php endforeach; ?></ul>
				<?php endfor; ?>
			</div>
		</section>
	<?php endif; ?>
	<?php
}

function dp_render_projects( $a = array() ) {
	$a   = dp_args( 'projects', $a );
	$all = dp_get_projects();
	if ( 'featured' === $a['source'] ) {
		$items = dp_get_projects( true );
		if ( ! $items ) { $items = $all; }
	} elseif ( 'type' === $a['source'] && $a['type'] ) {
		$items = array_values( array_filter( $all, function ( $p ) use ( $a ) { return has_term( $a['type'], 'project_type', $p ); } ) );
	} else {
		$items = $all;
	}
	$limit = (int) $a['limit'];
	if ( $limit > 0 ) { $items = array_slice( $items, 0, $limit ); }
	if ( 'showcase' === $a['layout'] && function_exists( 'dp_render_projects_showcase' ) ) { dp_render_projects_showcase( $a, $items, $all ); return; }
	$archive = get_post_type_archive_link( 'project' );
	$more    = $a['more_text'] ? $a['more_text'] : sprintf( __( 'View all %d projects', 'doha-portfolio' ), count( $all ) );
	?>
	<section id="work" class="section" data-anim="projects">
		<div class="wrap">
			<header class="section-head">
				<?php if ( $a['eyebrow'] ) : ?><p class="section-kicker"><?php echo esc_html( dp_x( $a['eyebrow'] ) ); ?></p><?php endif; ?>
				<h2><?php echo esc_html( dp_x( $a['title'] ) ); ?></h2>
				<p class="work-total"><?php
				$dp_n = count( $all );
				$dp_c = function_exists( 'dp_country_count' ) ? dp_country_count() : 0;
				/* translators: 1: number of projects, 2: number of countries */
				echo esc_html( 'ar' === dp_lang() ? sprintf( '%1$d مشروعًا لعملاء في %2$d دولة', $dp_n, $dp_c ) : sprintf( '%1$d projects for clients in %2$d countries', $dp_n, $dp_c ) );
				?></p>
			</header>
			<?php
			if ( 'company' === $a['layout'] ) {
				get_template_part( 'template-parts/company-groups', null, array( 'items' => 'featured' === $a['source'] ? $all : $items, 'per' => (int) $a['per_company'] ) );
			} else {
				get_template_part( 'template-parts/project-grid', null, array( 'items' => $items, 'filters' => 'yes' === $a['show_filters'], 'bento' => true ) );
			}
			?>
			<?php if ( 'yes' === $a['show_more'] ) : ?>
				<p class="center"><a class="btn btn-ghost magnetic" href="<?php echo esc_url( $archive ); ?>"><span><?php echo esc_html( dp_x( $more ) ); ?></span><?php echo dp_btn_icon( 'arrow-right' ); // phpcs:ignore ?></a></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

function dp_render_services( $a = array() ) {
	$a = dp_args( 'services', $a );
	if ( function_exists( 'dp_render_services_v2' ) ) { dp_render_services_v2( $a ); return; }
	?>
	<section id="services" class="section section-alt" data-anim="services">
		<div class="wrap">
			<header class="section-head">
				<h2><?php echo esc_html( dp_x( $a['title'] ) ); ?></h2>
				<?php if ( $a['intro'] ) : ?><p class="side-intro"><?php echo esc_html( dp_x( $a['intro'] ) ); ?></p><?php endif; ?>
			</header>
			<div class="services">
				<?php foreach ( (array) $a['items'] as $it ) : ?>
					<?php
					$dp_surl = '';
					foreach ( dp_service_pages() as $dp_sslug => $dp_s ) { if ( $dp_s['card'] === $it['title'] ) { $dp_surl = dp_service_url( $dp_sslug ); } }
					?>
					<article class="service<?php echo $dp_surl ? ' has-link' : ''; ?>">
						<span class="service-ico" aria-hidden="true"><?php echo dp_icon( ! empty( $it['icon'] ) ? $it['icon'] : 'check', 24 ); // phpcs:ignore ?></span>
						<h3><?php if ( $dp_surl ) : ?><a href="<?php echo esc_url( $dp_surl ); ?>"><?php endif; ?><?php echo esc_html( dp_x( $it['title'] ) ); ?><?php if ( $dp_surl ) : ?></a><?php endif; ?></h3>
						<p><?php echo esc_html( dp_x( $it['text'] ) ); ?></p>
						<?php if ( $dp_surl ) : ?><span class="service-more" aria-hidden="true"><?php echo esc_html( dp_x( 'Learn more' ) ); ?> <?php echo dp_icon( 'arrow-right', 14 ); // phpcs:ignore ?></span><?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
			<p class="services-cta"><a class="btn magnetic" href="#contact"><span><?php echo esc_html( dp_x( 'Discuss your project' ) ); ?></span><?php echo dp_btn_icon( 'arrow-right' ); // phpcs:ignore ?></a></p>
		</div>
	</section>
	<?php
}

function dp_render_process( $a = array() ) {
	$a = dp_args( 'process', $a );
	if ( function_exists( 'dp_render_process_v2' ) ) { dp_render_process_v2( $a ); return; }
	?>
	<section id="process" class="section process" data-anim="process">
		<div class="wrap">
			<header class="section-head process-head">
				<h2><?php echo esc_html( dp_x( $a['title'] ) ); ?></h2>
				<?php if ( $a['intro'] ) : ?><p class="side-intro"><?php echo esc_html( dp_x( $a['intro'] ) ); ?></p><?php endif; ?>
			</header>
			<ol class="steps">
				<?php foreach ( (array) $a['items'] as $n => $it ) : ?>
					<li class="step">
						<span class="step-num" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span>
						<h3><?php echo esc_html( dp_x( $it['title'] ) ); ?></h3>
						<p><?php echo esc_html( dp_x( $it['text'] ) ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<?php if ( function_exists( 'dp_why_items' ) ) : ?>
				<ul class="why-chips" aria-label="<?php echo esc_attr( dp_t( array( 'en' => 'Why work with me', 'ar' => 'لماذا تعمل معي' ) ) ); ?>">
					<?php foreach ( dp_why_items() as $w ) : ?><li><?php echo dp_icon( 'check', 15 ); // phpcs:ignore ?> <span><?php echo esc_html( dp_t( $w['title'] ) ); ?></span></li><?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

function dp_render_experience( $a = array() ) {
	$a = dp_args( 'experience', $a );
	// Older saved copies of the intro carry outdated totals: use the current one.
	if ( preg_match( '/^15\+ years, \d+\+ websites and clients in \d+ countries/', (string) $a['intro'] ) ) {
		$a['intro'] = dp_section_defaults( 'experience' )['intro'];
	}
	?>
	<section id="experience" class="section section-alt" data-anim="experience">
		<div class="wrap two-col">
			<header class="section-side">
				<?php if ( $a['eyebrow'] ) : ?><p class="section-kicker"><?php echo esc_html( dp_x( $a['eyebrow'] ) ); ?></p><?php endif; ?>
				<h2><?php echo esc_html( dp_x( $a['title'] ) ); ?></h2>
				<?php if ( $a['intro'] ) : ?><p class="side-intro"><?php echo esc_html( dp_x( $a['intro'] ) ); ?></p><?php endif; ?>
			</header>
			<div class="timeline-wrap"><span class="timeline-progress" aria-hidden="true"></span>
			<ol class="timeline">
				<?php foreach ( array_values( (array) $a['items'] ) as $ei => $e ) : ?>
					<li>
						<div class="tl-head">
							<h3><?php echo esc_html( dp_x( $e['role'] ) ); ?> <span class="tl-co"><?php echo dp_company_logos_html( $e['company'] ); // phpcs:ignore ?><span class="tl-co-name"><?php echo esc_html( dp_x( $e['company'] ) ); ?></span></span></h3>
							<p class="tl-date"><?php echo esc_html( dp_x( $e['dates'] ) ); ?><?php if ( ! empty( $e['location'] ) ) : ?><br><span><?php echo esc_html( dp_x( $e['location'] ) ); ?></span><?php endif; ?></p>
						</div>
						<?php if ( ! empty( $e['note'] ) ) : ?><p class="muted small"><?php echo esc_html( dp_x( $e['note'] ) ); ?></p><?php endif; ?>
						<?php $bullets = array_filter( array_map( 'trim', preg_split( '/\r\n|\n/', (string) $e['bullets'] ) ) ); ?>
						<?php
						// Keep the timeline light: the first line for the latest roles, the rest one click away.
						$show = $ei < 3 ? array_slice( $bullets, 0, 1 ) : array();
						$more = array_slice( $bullets, count( $show ) );
						?>
						<?php if ( $show ) : ?><ul><?php foreach ( $show as $b ) : ?><li><?php echo esc_html( dp_x( $b ) ); ?></li><?php endforeach; ?></ul><?php endif; ?>
						<?php if ( $more ) : ?><details class="tl-more"><summary><?php echo esc_html( dp_t( array( 'en' => 'More details', 'ar' => 'تفاصيل أكثر' ) ) ); ?> <?php echo dp_icon( 'caret-down', 13 ); // phpcs:ignore ?></summary><ul><?php foreach ( $more as $b ) : ?><li><?php echo esc_html( dp_x( $b ) ); ?></li><?php endforeach; ?></ul></details><?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
			</div>
		</div>
	</section>
	<?php
}

function dp_render_skills( $a = array() ) {
	$a = dp_args( 'skills', $a );
	?>
	<section id="skills" class="section" data-anim="skills">
		<div class="wrap">
			<header class="section-head">
				<?php if ( $a['eyebrow'] ) : ?><p class="section-kicker"><?php echo esc_html( dp_x( $a['eyebrow'] ) ); ?></p><?php endif; ?>
				<h2><?php echo esc_html( dp_x( $a['title'] ) ); ?></h2>
			</header>
			<div class="bento">
				<?php foreach ( (array) $a['groups'] as $i => $g ) : ?>
					<div class="tile<?php echo 0 === $i ? ' tile-lead' : ''; ?>">
						<h3><?php echo esc_html( dp_x( $g['title'] ) ); ?></h3>
						<ul class="tags"><?php foreach ( dp_csv( $g['tags'] ) as $t ) : ?><li><?php echo esc_html( dp_x( $t ) ); ?></li><?php endforeach; ?></ul>
					</div>
				<?php endforeach; ?>
				<?php if ( ! empty( $a['education'] ) ) : ?>
					<div class="tile tile-dark">
						<h3><?php esc_html_e( 'Education', 'doha-portfolio' ); ?></h3>
						<ul class="edu"><?php foreach ( (array) $a['education'] as $ed ) : ?><li><strong><?php echo esc_html( dp_x( $ed['degree'] ) ); ?></strong><span><?php echo esc_html( dp_x( $ed['details'] ) ); ?></span></li><?php endforeach; ?></ul>
					</div>
				<?php endif; ?>
				<?php if ( $a['languages'] ) : ?>
					<div class="tile tile-tint">
						<h3><?php esc_html_e( 'Languages', 'doha-portfolio' ); ?></h3>
						<ul class="langs"><?php foreach ( dp_csv( $a['languages'] ) as $l ) : ?><li><?php echo esc_html( dp_x( $l ) ); ?></li><?php endforeach; ?></ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

function dp_render_recognition( $a = array() ) {
	$a = dp_args( 'recognition', $a );
	?>
	<section id="recognition" class="section section-alt" data-anim="recognition">
		<div class="wrap">
			<header class="section-head">
				<?php if ( $a['eyebrow'] ) : ?><p class="section-kicker"><?php echo esc_html( dp_x( $a['eyebrow'] ) ); ?></p><?php endif; ?>
				<h2><?php echo esc_html( dp_x( $a['title'] ) ); ?></h2>
				<?php if ( $a['intro'] ) : ?><p class="side-intro"><?php echo esc_html( dp_x( $a['intro'] ) ); ?></p><?php endif; ?>
			</header>
			<div class="awards">
				<?php foreach ( (array) $a['items'] as $it ) :
					foreach ( array( 'title', 'org', 'date' ) as $dp_f ) { if ( isset( $it[ $dp_f ] ) ) { $it[ $dp_f ] = dp_x( $it[ $dp_f ] ); } }
					$src = is_array( $it['image'] ) ? ( isset( $it['image']['url'] ) ? $it['image']['url'] : '' ) : (string) $it['image'];
					?>
					<figure class="award">
						<?php if ( $src ) : ?>
							<a class="bezel award-img" href="<?php echo esc_url( $src ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( sprintf( __( 'Open %s full size', 'doha-portfolio' ), $it['title'] ) ); ?>">
								<span class="award-frame"><img src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( $it['title'] . ', ' . $it['org'] ); ?>" loading="lazy" decoding="async"></span>
								<span class="award-zoom" aria-hidden="true"><?php echo dp_icon( 'magnify', 18 ); // phpcs:ignore ?></span>
							</a>
						<?php endif; ?>
						<figcaption>
							<strong><?php echo esc_html( dp_x( $it['title'] ) ); ?></strong>
							<span><?php echo esc_html( dp_x( $it['org'] ) ); ?></span>
							<?php if ( ! empty( $it['date'] ) ) : ?><time><?php echo esc_html( dp_x( $it['date'] ) ); ?></time><?php endif; ?>
						</figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
			<?php if ( $a['also'] && '-' !== trim( $a['also'] ) ) : ?><p class="awards-also"><?php echo dp_icon( 'certificate', 18 ); // phpcs:ignore ?><?php echo esc_html( dp_x( $a['also'] ) ); ?></p><?php endif; ?>
		</div>
	</section>
	<?php
	dp_lightbox_markup();
}

/** Certificates: newest first, filter by type, open any certificate full size. */
function dp_render_certificates( $a = array() ) {
	$a     = dp_args( 'certificates', $a );
	$items = array();
	foreach ( (array) $a['items'] as $it ) {
		$img = isset( $it['image'] ) ? $it['image'] : '';
		$src = is_array( $img ) ? ( isset( $img['url'] ) ? $img['url'] : '' ) : (string) $img;
		// Older saved URLs pointed inside the theme; images now come from the images pack.
		if ( $src && false !== strpos( $src, '/assets/img/certificates/' ) ) { $src = dp_data_url( 'certificates/' . basename( $src ) ); }
		$th  = ! empty( $it['thumb'] ) ? $it['thumb'] : '';
		if ( $th && false !== strpos( $th, '/assets/img/certificates/' ) ) { $th = dp_data_url( 'certificates/' . basename( $th ) ); }
		if ( ! $th && is_array( $img ) && ! empty( $img['id'] ) ) { $th = wp_get_attachment_image_url( (int) $img['id'], 'medium' ); }
		if ( ! $th ) { $th = $src; }
		$dp_links     = function_exists( 'dp_cert_verify_links' ) ? dp_cert_verify_links() : array();
		$dp_t         = isset( $it['title'] ) ? $it['title'] : '';
		$it['verify'] = ! empty( $it['verify'] ) ? $it['verify'] : ( isset( $dp_links[ $dp_t ] ) ? $dp_links[ $dp_t ] : '' );
		foreach ( array( 'title', 'org', 'date', 'type' ) as $dp_f ) { if ( isset( $it[ $dp_f ] ) ) { $it[ $dp_f ] = dp_x( $it[ $dp_f ] ); } }
		$it['src']   = $src;
		$it['th']    = $th;
		$it['sort']  = ! empty( $it['sort'] ) ? $it['sort'] : '0000';
		$it['type']  = ! empty( $it['type'] ) ? $it['type'] : __( 'Other', 'doha-portfolio' );
		$items[] = $it;
	}
	usort( $items, function ( $x, $y ) { return strcmp( $y['sort'], $x['sort'] ); } );
	$types = array();
	foreach ( $items as $it ) { $types[ $it['type'] ] = isset( $types[ $it['type'] ] ) ? $types[ $it['type'] ] + 1 : 1; }
	$rank = array_flip( array_map( 'dp_x', array( 'Degrees & diplomas', 'Development', 'Computer skills', 'Languages', 'Professional skills', 'Islamic studies' ) ) );
	uksort( $types, function ( $x, $y ) use ( $rank ) {
		$rx = isset( $rank[ $x ] ) ? $rank[ $x ] : 99;
		$ry = isset( $rank[ $y ] ) ? $rank[ $y ] : 99;
		return $rx === $ry ? strcmp( $x, $y ) : $rx - $ry;
	} );
	$visible = max( 0, (int) $a['visible'] );
	$more    = $visible && count( $items ) > $visible;
	?>
	<section id="certificates" class="section" data-anim="certificates">
		<div class="wrap">
			<header class="section-head">
				<?php if ( $a['eyebrow'] ) : ?><p class="section-kicker"><?php echo esc_html( dp_x( $a['eyebrow'] ) ); ?></p><?php endif; ?>
				<h2><?php echo esc_html( dp_x( $a['title'] ) ); ?></h2>
				<?php if ( $a['intro'] ) : ?><p class="side-intro"><?php echo esc_html( dp_x( $a['intro'] ) ); ?></p><?php endif; ?>
			</header>
			<div class="certs-wrap" data-certs data-visible="<?php echo (int) $visible; ?>">
				<div class="chips" role="group" aria-label="<?php esc_attr_e( 'Filter certificates by type', 'doha-portfolio' ); ?>">
					<button class="chip is-active" data-filter="*" aria-pressed="true"><?php esc_html_e( 'All', 'doha-portfolio' ); ?> <em><?php echo count( $items ); ?></em></button>
					<?php foreach ( $types as $t => $n ) : ?>
						<button class="chip" data-filter="<?php echo esc_attr( sanitize_title( $t ) ); ?>" aria-pressed="false"><?php echo esc_html( dp_x( $t ) ); ?> <em><?php echo (int) $n; ?></em></button>
					<?php endforeach; ?>
				</div>
				<ol class="certs<?php echo $more ? ' is-collapsed' : ''; ?>">
					<?php foreach ( $items as $i => $it ) : ?>
						<li class="cert" data-type="<?php echo esc_attr( sanitize_title( $it['type'] ) ); ?>"<?php echo ( $more && $i >= $visible ) ? ' data-extra' : ''; ?>>
							<?php if ( ! $it['th'] ) : ?><span class="cert-thumb bezel cert-thumb-empty" aria-hidden="true"><?php echo dp_icon( 'certificate', 28 ); // phpcs:ignore ?></span><?php else : ?>
							<button class="cert-thumb bezel" type="button" data-full="<?php echo esc_url( $it['src'] ); ?>" data-caption="<?php echo esc_attr( $it['title'] . ' · ' . $it['org'] . ' · ' . $it['date'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View certificate: %s', 'doha-portfolio' ), $it['title'] ) ); ?>">
								<img src="<?php echo esc_url( $it['th'] ); ?>" alt="" width="360" height="270" loading="lazy" decoding="async">
							</button>
							<?php endif; ?>
							<div class="cert-text">
								<h3><?php echo esc_html( dp_x( $it['title'] ) ); ?></h3>
								<p><?php echo esc_html( dp_x( $it['org'] ) ); ?></p>
							</div>
							<div class="cert-meta">
								<time><?php echo esc_html( dp_x( $it['date'] ) ); ?></time>
								<span class="cert-type"><?php echo esc_html( dp_x( $it['type'] ) ); ?></span>
								<?php if ( ! empty( $it['verify'] ) ) : ?><a class="cert-verify" href="<?php echo esc_url( $it['verify'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( sprintf( '%s: %s', dp_x( 'Verify online' ), $it['title'] ) ); ?>"><?php echo esc_html( dp_x( 'Verify online' ) ); ?> <?php echo dp_icon( 'arrow-up-right', 12 ); // phpcs:ignore ?></a><?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
				<?php if ( $more ) : ?>
					<p class="certs-more"><button class="btn btn-ghost" type="button" data-certs-more><span><?php echo esc_html( sprintf( __( 'Show all %d certificates', 'doha-portfolio' ), count( $items ) ) ); ?></span><?php echo dp_btn_icon( 'caret-down' ); // phpcs:ignore ?></button></p>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	dp_lightbox_markup();
}

/** One shared lightbox (native dialog) for certificates and awards. */
function dp_lightbox_markup() {
	static $done = false;
	if ( $done ) { return; }
	$done = true;
	?>
	<dialog class="lightbox" aria-label="<?php esc_attr_e( 'Certificate viewer', 'doha-portfolio' ); ?>">
		<figure>
			<img alt="">
			<figcaption></figcaption>
		</figure>
		<button class="lb-btn lb-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'doha-portfolio' ); ?>"><?php echo dp_icon( 'close', 20 ); // phpcs:ignore ?></button>
		<button class="lb-btn lb-prev" type="button" aria-label="<?php esc_attr_e( 'Previous', 'doha-portfolio' ); ?>"><?php echo dp_icon( 'caret-left', 20 ); // phpcs:ignore ?></button>
		<button class="lb-btn lb-next" type="button" aria-label="<?php esc_attr_e( 'Next', 'doha-portfolio' ); ?>"><?php echo dp_icon( 'caret-right', 20 ); // phpcs:ignore ?></button>
	</dialog>
	<?php
}

function dp_render_contact( $a = array() ) {
	$a = dp_args( 'contact', $a );
	?>
	<section id="contact" class="section contact" data-anim="contact">
		<div class="contact-glow" aria-hidden="true"></div>
		<div class="wrap contact-inner">
			<div>
				<?php if ( $a['eyebrow'] ) : ?><p class="section-kicker"><?php echo esc_html( dp_x( $a['eyebrow'] ) ); ?></p><?php endif; ?>
				<h2><?php echo esc_html( dp_x( $a['title'] ) ); ?></h2>
				<p class="contact-text"><?php echo esc_html( dp_x( $a['text'] ) ); ?></p>
				<?php dp_contact_form(); ?>
				<?php dp_render_socials( 'socials socials-contact' ); ?>
				<?php if ( function_exists( 'dp_render_freelance_profiles' ) ) { dp_render_freelance_profiles(); } ?>
			</div>
			<dl class="contact-list">
				<?php if ( $a['email'] ) : ?><div><dt><?php echo dp_icon( 'envelope', 17 ); // phpcs:ignore ?><?php esc_html_e( 'Email', 'doha-portfolio' ); ?></dt><dd><a href="mailto:<?php echo esc_attr( $a['email'] ); ?>"><?php echo esc_html( dp_x( $a['email'] ) ); ?></a></dd></div><?php endif; ?>
				<?php if ( $a['phone'] ) : ?><div><dt><?php echo dp_icon( 'phone', 17 ); // phpcs:ignore ?><?php esc_html_e( 'Phone', 'doha-portfolio' ); ?></dt><dd><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $a['phone'] ) ); ?>"><?php echo esc_html( dp_x( $a['phone'] ) ); ?></a></dd></div><?php endif; ?>
				<?php if ( $a['linkedin'] ) : ?><div><dt><?php echo dp_icon( 'linkedin', 17 ); // phpcs:ignore ?>LinkedIn</dt><dd><a href="<?php echo esc_url( $a['linkedin'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( preg_replace( '#^https?://(www\.)?#', '', $a['linkedin'] ) ); ?></a></dd></div><?php endif; ?>
				<?php if ( ! empty( $a['location'] ) ) : ?><div><dt><?php echo dp_icon( 'pin', 17 ); // phpcs:ignore ?><?php esc_html_e( 'Based in', 'doha-portfolio' ); ?></dt><dd><?php echo esc_html( dp_x( $a['location'] ) ); ?></dd></div><?php endif; ?>
			</dl>
		</div>
	</section>
	<?php
}
