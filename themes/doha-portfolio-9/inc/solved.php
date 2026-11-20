<?php
/**
 * "Problems I solved": short real cases (problem, what was done, result), and links to
 * freelance profiles (Upwork, Mostaql) set in Customize → Packages & prices.
 *
 * Client names are left out of the security cases on purpose: a client's hack is not ours to publish.
 *
 * @package Doha_Portfolio
 */

defined( 'ABSPATH' ) || exit;

function dp_solved_items() {
	return array(
		array(
			'icon'    => 'shield-check',
			'tag'     => array( 'en' => 'Security', 'ar' => 'الحماية' ),
			'title'   => array( 'en' => 'Malware across many sites on one hosting account', 'ar' => 'برمجيات خبيثة في مواقع كثيرة على حساب استضافة واحد' ),
			'problem' => array( 'en' => 'A shared hosting account with many client WordPress sites kept getting reinfected, and the plan had no SSH access.', 'ar' => 'حساب استضافة مشترك فيه مواقع ووردبريس كثيرة لعملاء يعود إليه الاختراق كل مرة، والخطة بدون وصول SSH.' ),
			'did'     => array( 'en' => 'Wrote PHP scanning and cleanup tools that run from the web root, cleaned every site, found the shared entry point and closed it, then hardened each site.', 'ar' => 'كتبت أدوات فحص وتنظيف بـ PHP تعمل من مجلد الموقع، ونظّفت كل المواقع، ووجدت نقطة الدخول المشتركة وأغلقتها، ثم حصّنت كل موقع.' ),
			'result'  => array( 'en' => 'Every site cleaned without rebuilding and without losing content.', 'ar' => 'نُظّفت كل المواقع دون إعادة بنائها ودون خسارة أي محتوى.' ),
		),
		array(
			'icon'    => 'shopping-cart',
			'tag'     => array( 'en' => 'Security · WooCommerce', 'ar' => 'الحماية · WooCommerce' ),
			'title'   => array( 'en' => 'A hacked online store, back to selling', 'ar' => 'متجر إلكتروني مخترق، يعود للبيع' ),
			'problem' => array( 'en' => 'A bilingual WooCommerce store in Qatar was hacked, and the client could not pay again for the premium plugins it relied on.', 'ar' => 'متجر WooCommerce ثنائي اللغة في قطر تعرّض للاختراق، والعميل لا يستطيع شراء الإضافات المدفوعة التي يعتمد عليها مرة أخرى.' ),
			'did'     => array( 'en' => 'Cleaned the database and plugins, then replaced the paid ones with free alternatives: product grids on WooCommerce itself, Polylang instead of WPML.', 'ar' => 'نظّفت قاعدة البيانات والإضافات، ثم استبدلت المدفوعة ببدائل مجانية: شبكات المنتجات من WooCommerce نفسه، وPolylang بدل WPML.' ),
			'result'  => array( 'en' => 'A clean store with the same design and products, in both languages, and no licence costs.', 'ar' => 'متجر نظيف بنفس التصميم والمنتجات، باللغتين، ودون أي تكلفة تراخيص.' ),
		),
		array(
			'icon'    => 'lightning',
			'tag'     => array( 'en' => 'Speed', 'ar' => 'السرعة' ),
			'title'   => array( 'en' => 'Faster pages without breaking the design', 'ar' => 'صفحات أسرع دون أن ينكسر التصميم' ),
			'problem' => array( 'en' => 'The usual one-click speed settings broke sliders and carousels, and one site waited seconds before sending the first byte.', 'ar' => 'إعدادات التسريع المعتادة بنقرة واحدة كانت تكسر السلايدرات، وموقع كان ينتظر ثواني قبل أن يرسل أول بايت.' ),
			'did'     => array( 'en' => 'Traced it to a page cache that was not writing behind the CDN, and replaced "remove unused CSS" with asynchronous CSS plus a safelist for the components that need it.', 'ar' => 'تتبّعت السبب إلى تخزين مؤقت للصفحات لا يعمل خلف الـ CDN، واستبدلت "إزالة الـ CSS غير المستخدم" بتحميل CSS غير متزامن مع قائمة استثناء للعناصر التي تحتاجه.' ),
			'result'  => array( 'en' => 'Cached pages and a lighter load, with every slider still working.', 'ar' => 'صفحات مخزّنة وتحميل أخف، وكل السلايدرات تعمل كما هي.' ),
		),
		array(
			'icon'    => 'arrows-left-right',
			'tag'     => array( 'en' => 'Migration', 'ar' => 'النقل' ),
			'title'   => array( 'en' => 'From a coded site to WordPress, pixel for pixel', 'ar' => 'من موقع مبرمج إلى ووردبريس، بنفس الشكل تمامًا' ),
			'problem' => array( 'en' => 'An events company in Dubai had a Next.js site nobody on their team could edit.', 'ar' => 'شركة فعاليات في دبي لديها موقع Next.js لا يستطيع أحد في فريقها تعديله.' ),
			'did'     => array( 'en' => 'Rebuilt every page in Elementor with one custom plugin for the video hero, scroll effects, carousels and the portfolio, then moved the domain over.', 'ar' => 'أعدت بناء كل الصفحات بـ Elementor مع إضافة واحدة خاصة لفيديو الواجهة وتأثيرات التمرير والسلايدرات ومعرض الأعمال، ثم نقلت النطاق.' ),
			'result'  => array( 'en' => 'The same look, now edited by the client from the dashboard.', 'ar' => 'نفس الشكل، والعميل يعدّل موقعه الآن بنفسه من لوحة التحكم.' ),
			'url'     => 'https://magnificaevent.com',
		),
		array(
			'icon'    => 'browser',
			'tag'     => array( 'en' => 'Marketplace', 'ar' => 'منصة متعددة البائعين' ),
			'title'   => array( 'en' => 'A wedding services marketplace for the Gulf', 'ar' => 'منصة خدمات أعراس للخليج' ),
			'problem' => array( 'en' => 'Wedding vendors needed to sell services with deposits and booking dates, in Arabic first, with a mobile app on top.', 'ar' => 'مزوّدو خدمات الأعراس يحتاجون بيع خدماتهم بعربون وتواريخ حجز، بالعربية أولًا، مع تطبيق جوال فوقها.' ),
			'did'     => array( 'en' => 'Planned it in four versions, from one vendor to many, and built it on WooCommerce with Arabic as the default language, installment payments, and an API for the app.', 'ar' => 'خطّطتها على أربع نسخ، من بائع واحد إلى عدة بائعين، وبنيتها على WooCommerce بالعربية لغةً أساسية، مع الدفع بالتقسيط، وواجهة برمجية للتطبيق.' ),
			'result'  => array( 'en' => 'A live platform that grows in stages instead of one risky launch.', 'ar' => 'منصة تعمل وتكبر على مراحل بدل إطلاق واحد محفوف بالمخاطر.' ),
			'url'     => 'https://mtmevents.ae',
		),
		array(
			'icon'    => 'code',
			'tag'     => array( 'en' => 'Custom plugin', 'ar' => 'إضافة خاصة' ),
			'title'   => array( 'en' => 'A content calendar for every client', 'ar' => 'تقويم محتوى لكل عميل' ),
			'problem' => array( 'en' => 'An agency was sending clients their monthly social media plan in scattered files and Drive links.', 'ar' => 'وكالة كانت ترسل لعملائها خطة السوشال ميديا الشهرية في ملفات وروابط درايف متفرقة.' ),
			'did'     => array( 'en' => 'Built a WordPress plugin that gives each client a private page in their own brand colours, with every post, reel and story on its day.', 'ar' => 'بنيت إضافة ووردبريس تعطي كل عميل صفحة خاصة بألوان علامته، فيها كل بوست وريل وستوري في يومه.' ),
			'result'  => array( 'en' => 'Clients review the whole month from one link before anything goes live.', 'ar' => 'العميل يراجع الشهر كاملًا من رابط واحد قبل نشر أي شيء.' ),
			'url'     => 'https://calendar.echovision.ae',
		),
		array(
			'icon'    => 'code',
			'wide'    => true,
			'tag'     => array( 'en' => 'Motion · Framer', 'ar' => 'موشن · Framer' ),
			'title'   => array( 'en' => 'Bringing a brand symbol to life: the EchoVision whale', 'ar' => 'إحياء رمز علامة تجارية: حوت EchoVision' ),
			'problem' => array( 'en' => 'The agency\'s logo is a line-art whale that stands for deep listening, but on the site it was a still image that told no story.', 'ar' => 'شعار الوكالة حوت مرسوم بالخطوط يرمز للإصغاء العميق، لكنه كان على الموقع صورة ثابتة لا تحكي شيئًا.' ),
			'did'     => array( 'en' => 'Developed an interactive underwater scene in Framer as a custom React code component: the whale swims through light rays and water drawn on canvas, visitors drag and zoom to look around, and it pauses for people who prefer reduced motion. Also built a WebGPU soft-body "plush whale" study.', 'ar' => 'طوّرت مشهدًا تفاعليًا تحت الماء في Framer كمكوّن React مخصص: الحوت يسبح بين أشعة الضوء والماء المرسومة على canvas، والزائر يسحب ويقرّب ليتجوّل، ويتوقف تلقائيًا لمن يفضّل تقليل الحركة. وبنيت معه تجربة WebGPU لحوت "قطني" ناعم يتفاعل مع اللمس.' ),
			'result'  => array( 'en' => 'The symbol now tells the brand story on its own, and the team edits the texts and colours from Framer without touching code.', 'ar' => 'صار الرمز يحكي قصة العلامة بنفسه، والفريق يعدّل النصوص والألوان من Framer بدون لمس الكود.' ),
			'url'     => 'https://brilliant-core-485530.framer.app',
			'image'   => 'https://framerusercontent.com/images/Kc9lo7CmpWXB3JKlbr22T5T0sIs.webp',
		),
	);
}

/** A picture for a case: its own image, else the screenshot of the matching project. */
function dp_solved_image( $it ) {
	if ( ! empty( $it['image'] ) ) { return $it['image']; }
	if ( empty( $it['url'] ) ) { return ''; }
	$ids = get_posts( array( 'post_type' => 'project', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_dp_key', 'meta_value' => dp_shot_key( $it['url'] ), 'no_found_rows' => true ) ); // phpcs:ignore
	if ( ! $ids || ! has_post_thumbnail( $ids[0] ) ) { return ''; }
	return (string) get_the_post_thumbnail_url( $ids[0], 'dp-large' );
}

function dp_render_solved( $a = array() ) {
	if ( 'no' === get_theme_mod( 'dp_solved_show', 'yes' ) && empty( $a['force'] ) ) { return; }
	$title = ! empty( $a['title'] ) ? $a['title'] : array( 'en' => 'Problems I have solved', 'ar' => 'مشاكل حللتها' );
	$intro = ! empty( $a['intro'] ) ? $a['intro'] : array( 'en' => 'Real cases from client work: what was wrong, what I did, and how it ended.', 'ar' => 'حالات حقيقية من شغلي مع العملاء: ما المشكلة، ماذا فعلت، وكيف انتهت.' );
	$L = array(
		'problem' => array( 'en' => 'The problem', 'ar' => 'المشكلة' ),
		'did'     => array( 'en' => 'What I did', 'ar' => 'ماذا فعلت' ),
		'result'  => array( 'en' => 'The result', 'ar' => 'النتيجة' ),
	);
	?>
	<section id="solved" class="section section-alt" data-anim="solved">
		<div class="wrap">
			<header class="section-head">
				<h2><?php echo esc_html( dp_t( $title ) ); ?></h2>
				<p class="side-intro"><?php echo esc_html( dp_t( $intro ) ); ?></p>
			</header>
			<div class="solved">
				<?php foreach ( dp_solved_items() as $it ) : $shot = dp_solved_image( $it ); ?>
					<article class="solved-item<?php echo empty( $it['wide'] ) ? '' : ' solved-wide'; ?>">
						<div class="solved-visual<?php echo $shot ? ' has-shot' : ''; ?>" aria-hidden="true">
							<?php if ( $shot ) : ?><img src="<?php echo esc_url( $shot ); ?>" alt="" loading="lazy" decoding="async"><?php else : ?><span class="solved-icon"><?php echo dp_icon( $it['icon'], 34 ); // phpcs:ignore ?></span><?php endif; ?>
							<span class="solved-tag"><?php echo esc_html( dp_t( $it['tag'] ) ); ?></span>
						</div>
						<div class="solved-body">
							<h3><?php echo esc_html( dp_t( $it['title'] ) ); ?></h3>
							<p class="solved-result"><?php echo dp_icon( 'check', 18 ); // phpcs:ignore ?> <span><?php echo esc_html( dp_t( $it['result'] ) ); ?></span></p>
							<details class="solved-more">
								<summary><?php echo esc_html( dp_t( array( 'en' => 'How I solved it', 'ar' => 'كيف حللتها' ) ) ); ?> <?php echo dp_icon( 'caret-down', 14 ); // phpcs:ignore ?></summary>
								<dl>
									<?php foreach ( array( 'problem', 'did' ) as $k ) : ?>
										<div class="solved-row"><dt><?php echo esc_html( dp_t( $L[ $k ] ) ); ?></dt><dd><?php echo esc_html( dp_t( $it[ $k ] ) ); ?></dd></div>
									<?php endforeach; ?>
								</dl>
							</details>
							<?php if ( ! empty( $it['url'] ) ) : ?><a class="solved-link" href="<?php echo esc_url( $it['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( preg_replace( '~^https?://~', '', $it['url'] ) ); ?> <?php echo dp_icon( 'arrow-up-right', 14 ); // phpcs:ignore ?></a><?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/** Freelance profiles (Upwork, Mostaql) with an optional rating line. */
function dp_freelance_profiles() {
	$out = array();
	foreach ( array( 'upwork' => 'Upwork', 'mostaql' => 'Mostaql · مستقل' ) as $k => $label ) {
		$url = trim( (string) get_theme_mod( 'dp_' . $k . '_url', '' ) );
		if ( ! $url ) { continue; }
		$out[] = array( 'key' => $k, 'label' => $label, 'url' => $url, 'rating' => trim( (string) get_theme_mod( 'dp_' . $k . '_rating', '' ) ) );
	}
	return $out;
}

function dp_render_freelance_profiles() {
	$p = dp_freelance_profiles();
	if ( ! $p ) { return; }
	?>
	<div class="hire">
		<p class="hire-title"><?php echo esc_html( dp_t( array( 'en' => 'Or hire me on', 'ar' => 'أو وظّفني عبر' ) ) ); ?></p>
		<div class="hire-list">
			<?php foreach ( $p as $f ) : ?>
				<a class="hire-item" href="<?php echo esc_url( $f['url'] ); ?>" target="_blank" rel="noopener">
					<strong><?php echo esc_html( $f['label'] ); ?></strong>
					<?php if ( $f['rating'] ) : ?><span class="hire-rating"><?php echo dp_icon( 'star', 14 ); // phpcs:ignore ?> <bdi dir="ltr"><?php echo esc_html( $f['rating'] ); ?></bdi></span><?php endif; ?>
					<?php echo dp_icon( 'arrow-up-right', 14 ); // phpcs:ignore ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

add_action( 'customize_register', function ( $wp ) {
	$wp->add_setting( 'dp_solved_show', array( 'default' => 'yes', 'sanitize_callback' => 'sanitize_key' ) );
	$wp->add_control( 'dp_solved_show', array( 'section' => 'dp_plans', 'type' => 'select', 'label' => __( 'Show "Problems I have solved"', 'doha-portfolio' ), 'choices' => array( 'yes' => __( 'Yes', 'doha-portfolio' ), 'no' => __( 'No', 'doha-portfolio' ) ) ) );
	foreach ( array( 'upwork' => 'Upwork', 'mostaql' => 'Mostaql' ) as $k => $label ) {
		$wp->add_setting( 'dp_' . $k . '_url', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
		$wp->add_control( 'dp_' . $k . '_url', array( 'section' => 'dp_plans', 'type' => 'url', 'label' => sprintf( __( '%s profile link (shown under the contact form)', 'doha-portfolio' ), $label ) ) );
		$wp->add_setting( 'dp_' . $k . '_rating', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'dp_' . $k . '_rating', array( 'section' => 'dp_plans', 'label' => sprintf( __( '%s rating, e.g. "5.0 · 12 reviews" (optional)', 'doha-portfolio' ), $label ) ) );
	}
}, 20 );
