<?php
/**
 * Service pages (one per service), "Why work with me", FAQ and Testimonials.
 * Content is bilingual: each field has an English and an Arabic version.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Pick the right language from an array( 'en' => ..., 'ar' => ... ) or return a plain string. */
function dp_t( $v ) {
	if ( is_array( $v ) && isset( $v['en'] ) ) { return dp_is_ar() && ! empty( $v['ar'] ) ? $v['ar'] : $v['en']; }
	return $v;
}

/** Service pages. Slug => content. */
function dp_service_pages() {
	return array(
		'wordpress-development' => array(
			'icon'  => 'code',
			'card'  => 'New WordPress websites',
			'title' => array( 'en' => 'WordPress website development', 'ar' => 'تطوير مواقع ووردبريس' ),
			'lead'  => array( 'en' => 'A fast, reliable business website built from your design or brand, that your team can update without calling a developer.', 'ar' => 'موقع أعمال سريع وموثوق، مبني من تصميمك أو هويتك، يستطيع فريقك تحديثه دون الحاجة إلى مطوّر.' ),
			'problem' => array( 'en' => 'Your current site is slow, hard to edit or no longer reflects the business. Or you have a design ready and need someone who builds it exactly, on every screen size.', 'ar' => 'موقعك الحالي بطيء أو صعب التعديل أو لم يعد يعبّر عن نشاطك. أو لديك تصميم جاهز وتحتاج من ينفّذه بدقة على كل أحجام الشاشات.' ),
			'get'   => array(
				array( 'en' => 'Pixel-accurate build from Figma, PSD or Illustrator, or a design based on your brand', 'ar' => 'تنفيذ دقيق من Figma أو PSD أو Illustrator، أو تصميم مبني على هويتك' ),
				array( 'en' => 'Elementor Pro or Gutenberg, set up so your team can edit pages safely', 'ar' => 'Elementor Pro أو Gutenberg، مُعدّ بحيث يعدّل فريقك الصفحات بأمان' ),
				array( 'en' => 'Custom post types, ACF fields and custom features in PHP when the site needs them', 'ar' => 'أنواع محتوى مخصّصة وحقول ACF وميزات برمجية بلغة PHP عند الحاجة' ),
				array( 'en' => 'Arabic and English with full right-to-left support', 'ar' => 'العربية والإنجليزية مع دعم كامل للكتابة من اليمين إلى اليسار' ),
				array( 'en' => 'Speed, SEO basics, contact forms and analytics ready at launch', 'ar' => 'السرعة وأساسيات SEO ونماذج التواصل والتحليلات جاهزة عند الإطلاق' ),
			),
			'for'   => array( 'en' => 'Companies, agencies that need a reliable developer (white-label included), schools and organisations.', 'ar' => 'الشركات، والوكالات التي تحتاج مطوّرًا موثوقًا (بما في ذلك العمل باسم الوكالة)، والمدارس والمؤسسات.' ),
			'type'  => 'Corporate & Business',
			'faq'   => array(
				array( 'q' => array( 'en' => 'How long does a website take?', 'ar' => 'كم يستغرق بناء الموقع؟' ), 'a' => array( 'en' => 'A company website usually takes 2 to 4 weeks once the content and design are ready. Larger sites are planned in stages.', 'ar' => 'يستغرق موقع الشركة عادة من أسبوعين إلى أربعة أسابيع بعد جاهزية المحتوى والتصميم. المواقع الأكبر تُقسّم إلى مراحل.' ) ),
				array( 'q' => array( 'en' => 'Can I edit the site myself afterwards?', 'ar' => 'هل يمكنني تعديل الموقع بنفسي لاحقًا؟' ), 'a' => array( 'en' => 'Yes. Pages are built so you can change text, images and sections yourself, and I show you how before launch.', 'ar' => 'نعم. تُبنى الصفحات بحيث تعدّل النصوص والصور والأقسام بنفسك، وأشرح لك طريقة ذلك قبل الإطلاق.' ) ),
				array( 'q' => array( 'en' => 'Do you work with agencies?', 'ar' => 'هل تعملين مع الوكالات؟' ), 'a' => array( 'en' => 'Yes, including white-label work under the agency name.', 'ar' => 'نعم، ومنها العمل باسم الوكالة (white-label).' ) ),
			),
		),
		'woocommerce-development' => array(
			'icon'  => 'shopping-cart',
			'card'  => 'WooCommerce stores',
			'title' => array( 'en' => 'WooCommerce store development', 'ar' => 'تطوير متاجر WooCommerce' ),
			'lead'  => array( 'en' => 'An online store that is easy to shop and easy to run: products, payments, shipping and orders, in Arabic and English.', 'ar' => 'متجر إلكتروني سهل للتسوّق وسهل للإدارة: المنتجات والدفع والشحن والطلبات، بالعربية والإنجليزية.' ),
			'problem' => array( 'en' => 'You want to sell online, move a store to WooCommerce, or fix a store that is slow, confusing or losing orders at checkout.', 'ar' => 'تريد البيع أونلاين، أو نقل متجرك إلى WooCommerce، أو إصلاح متجر بطيء أو مربك أو يخسر الطلبات عند الدفع.' ),
			'get'   => array(
				array( 'en' => 'Store setup: products, variations, categories, stock and coupons', 'ar' => 'إعداد المتجر: المنتجات والخيارات والتصنيفات والمخزون والكوبونات' ),
				array( 'en' => 'Payment gateways and shipping rules for your market', 'ar' => 'بوابات الدفع وقواعد الشحن المناسبة لسوقك' ),
				array( 'en' => 'Multi-vendor marketplaces when you have several sellers', 'ar' => 'أسواق متعددة البائعين عندما يكون لديك عدة بائعين' ),
				array( 'en' => 'A clear product page and checkout that work well on mobile', 'ar' => 'صفحة منتج وعملية دفع واضحتان وتعملان جيدًا على الجوال' ),
				array( 'en' => 'Training so your team can add products and handle orders', 'ar' => 'تدريب فريقك على إضافة المنتجات وإدارة الطلبات' ),
			),
			'for'   => array( 'en' => 'Shops, brands and distributors in the Gulf and beyond, and agencies building stores for their clients.', 'ar' => 'المتاجر والعلامات التجارية والموزعون في الخليج وخارجه، والوكالات التي تبني متاجر لعملائها.' ),
			'type'  => 'E-commerce',
			'faq'   => array(
				array( 'q' => array( 'en' => 'Can you move my existing store to WooCommerce?', 'ar' => 'هل يمكنك نقل متجري الحالي إلى WooCommerce؟' ), 'a' => array( 'en' => 'Yes. Products, customers and orders can be moved, and old links are redirected so you keep your search rankings.', 'ar' => 'نعم. يمكن نقل المنتجات والعملاء والطلبات، مع تحويل الروابط القديمة للحفاظ على ترتيبك في البحث.' ) ),
				array( 'q' => array( 'en' => 'Which payment gateways do you support?', 'ar' => 'ما بوابات الدفع التي تدعمينها؟' ), 'a' => array( 'en' => 'Any gateway that has a WooCommerce integration. We choose the one that works in your country and for your bank.', 'ar' => 'أي بوابة لها تكامل مع WooCommerce. نختار الأنسب لبلدك وبنكك.' ) ),
			),
		),
		'speed-optimization' => array(
			'icon'  => 'lightning',
			'card'  => 'Speed optimisation',
			'title' => array( 'en' => 'WordPress speed optimisation', 'ar' => 'تسريع مواقع ووردبريس' ),
			'lead'  => array( 'en' => 'A faster site keeps visitors, ranks better and sells more. I find what is slowing yours down and fix it.', 'ar' => 'الموقع الأسرع يحتفظ بالزوار ويتصدّر البحث ويبيع أكثر. أحدد ما يبطئ موقعك وأعالجه.' ),
			'problem' => array( 'en' => 'Pages take seconds to load, PageSpeed scores are red, or the site slows down when traffic grows.', 'ar' => 'الصفحات تستغرق ثواني لتظهر، أو نتائج PageSpeed حمراء، أو يتباطأ الموقع مع ازدياد الزيارات.' ),
			'get'   => array(
				array( 'en' => 'A short report of what is slow and why, before any change', 'ar' => 'تقرير مختصر بما هو بطيء وسببه، قبل أي تعديل' ),
				array( 'en' => 'Image compression and modern formats, lazy loading', 'ar' => 'ضغط الصور وصيغ حديثة وتحميل كسول' ),
				array( 'en' => 'Caching, script and font clean-up, fewer plugins', 'ar' => 'التخزين المؤقت وتنظيف السكربتات والخطوط وتقليل الإضافات' ),
				array( 'en' => 'Core Web Vitals (LCP, CLS, INP) measured before and after', 'ar' => 'قياس Core Web Vitals (LCP و CLS و INP) قبل وبعد' ),
				array( 'en' => 'Hosting advice if the server is the bottleneck', 'ar' => 'نصيحة بخصوص الاستضافة إذا كان الخادم هو السبب' ),
			),
			'for'   => array( 'en' => 'Any WordPress or WooCommerce site that feels slow, especially stores and sites that rely on Google traffic.', 'ar' => 'أي موقع ووردبريس أو WooCommerce بطيء، وخاصة المتاجر والمواقع التي تعتمد على زيارات Google.' ),
			'type'  => '',
			'faq'   => array(
				array( 'q' => array( 'en' => 'Will the design change?', 'ar' => 'هل سيتغير التصميم؟' ), 'a' => array( 'en' => 'No. The work is behind the scenes; the site looks the same, only faster.', 'ar' => 'لا. العمل يتم خلف الكواليس، ويبقى الموقع كما هو لكن أسرع.' ) ),
				array( 'q' => array( 'en' => 'Do you work on a copy first?', 'ar' => 'هل تعملين على نسخة أولًا؟' ), 'a' => array( 'en' => 'Yes. Changes are tested on a staging copy and a full backup is taken before anything goes live.', 'ar' => 'نعم. تُختبر التعديلات على نسخة تجريبية، وتُؤخذ نسخة احتياطية كاملة قبل نشر أي شيء.' ) ),
			),
		),
		'security-malware-recovery' => array(
			'icon'  => 'shield-check',
			'card'  => 'Hacked site recovery',
			'title' => array( 'en' => 'WordPress security and malware recovery', 'ar' => 'حماية ووردبريس وإصلاح المواقع المخترقة' ),
			'lead'  => array( 'en' => 'If your site was hacked, redirects to spam or was flagged by Google, I clean it, close the hole and harden it so it stays clean.', 'ar' => 'إذا اختُرق موقعك أو صار يحوّل إلى مواقع مزعجة أو أشارت إليه Google كموقع خطر، أنظّفه وأغلق الثغرة وأحصّنه ليبقى نظيفًا.' ),
			'problem' => array( 'en' => 'Strange redirects, unknown admin users, spam pages in Google, a hosting suspension or a browser warning.', 'ar' => 'تحويلات غريبة، أو مستخدمو إدارة مجهولون، أو صفحات مزعجة في Google، أو إيقاف من الاستضافة، أو تحذير من المتصفح.' ),
			'get'   => array(
				array( 'en' => 'Full malware cleanup of files and database', 'ar' => 'تنظيف كامل للبرمجيات الخبيثة من الملفات وقاعدة البيانات' ),
				array( 'en' => 'Finding how they got in, and closing it', 'ar' => 'تحديد طريقة الاختراق وإغلاقها' ),
				array( 'en' => 'Updates, password and user clean-up, two-factor login', 'ar' => 'التحديثات وتنظيف المستخدمين وكلمات المرور وتسجيل الدخول بخطوتين' ),
				array( 'en' => 'Off-site backups and monitoring', 'ar' => 'نسخ احتياطية خارجية ومراقبة' ),
				array( 'en' => 'Help with Google Search Console and blacklist reviews', 'ar' => 'المساعدة في Google Search Console وطلبات الإزالة من القوائم السوداء' ),
			),
			'for'   => array( 'en' => 'Site owners dealing with a hack now, and anyone who wants to prevent one.', 'ar' => 'أصحاب المواقع الذين يواجهون اختراقًا الآن، وكل من يريد منع حدوثه.' ),
			'type'  => '',
			'faq'   => array(
				array( 'q' => array( 'en' => 'How fast can you start?', 'ar' => 'متى يمكنك البدء؟' ), 'a' => array( 'en' => 'Hacked sites are treated as urgent. Send me the details and I reply the same working day.', 'ar' => 'المواقع المخترقة لها أولوية. أرسل التفاصيل وأرد في يوم العمل نفسه.' ) ),
				array( 'q' => array( 'en' => 'Will I lose my content?', 'ar' => 'هل سأخسر المحتوى؟' ), 'a' => array( 'en' => 'The goal is to keep all your real content and remove only what the attacker added.', 'ar' => 'الهدف الحفاظ على كل محتواك الحقيقي وإزالة ما أضافه المخترق فقط.' ) ),
			),
		),
		'wordpress-migration' => array(
			'icon'  => 'arrows-left-right',
			'card'  => 'Migrations',
			'title' => array( 'en' => 'WordPress migration', 'ar' => 'نقل مواقع ووردبريس' ),
			'lead'  => array( 'en' => 'Move to a new host, a new domain or onto WordPress from another platform, without losing content, emails or search rankings.', 'ar' => 'انقل موقعك إلى استضافة أو نطاق جديد، أو إلى ووردبريس من منصة أخرى، دون خسارة المحتوى أو البريد أو ترتيب البحث.' ),
			'problem' => array( 'en' => 'Your hosting is slow or expensive, you are changing domain, or your old site is built on a platform you can no longer maintain.', 'ar' => 'استضافتك بطيئة أو مكلفة، أو تغيّر النطاق، أو موقعك القديم مبني على منصة لم يعد بالإمكان صيانتها.' ),
			'get'   => array(
				array( 'en' => 'A migration plan with a clear switch-over time', 'ar' => 'خطة نقل بموعد تحويل واضح' ),
				array( 'en' => 'Files, database, media and users moved and checked', 'ar' => 'نقل الملفات وقاعدة البيانات والوسائط والمستخدمين والتحقق منها' ),
				array( 'en' => '301 redirects so old links keep working', 'ar' => 'تحويلات 301 لتبقى الروابط القديمة تعمل' ),
				array( 'en' => 'DNS, SSL and email settings handled', 'ar' => 'إعداد DNS و SSL والبريد الإلكتروني' ),
			),
			'for'   => array( 'en' => 'Businesses changing hosting or domain, and sites moving to WordPress.', 'ar' => 'الشركات التي تغيّر الاستضافة أو النطاق، والمواقع المنتقلة إلى ووردبريس.' ),
			'type'  => '',
			'faq'   => array(
				array( 'q' => array( 'en' => 'Will the site go down?', 'ar' => 'هل سيتوقف الموقع؟' ), 'a' => array( 'en' => 'The new copy is prepared and tested first, so the switch takes minutes and is planned at a quiet time.', 'ar' => 'تُجهَّز النسخة الجديدة وتُختبر أولًا، فيستغرق التحويل دقائق ويتم في وقت هادئ.' ) ),
			),
		),
		'wordpress-maintenance' => array(
			'icon'  => 'lifebuoy',
			'card'  => 'Care and support',
			'title' => array( 'en' => 'WordPress maintenance and support', 'ar' => 'صيانة ودعم ووردبريس' ),
			'lead'  => array( 'en' => 'Updates, backups, fixes and small changes every month, from someone who knows your site and answers quickly.', 'ar' => 'تحديثات ونسخ احتياطية وإصلاحات وتعديلات صغيرة كل شهر، من شخص يعرف موقعك ويرد بسرعة.' ),
			'problem' => array( 'en' => 'Nobody is looking after the site: plugins are out of date, backups are uncertain, and small changes wait for weeks.', 'ar' => 'لا أحد يتابع الموقع: الإضافات غير محدّثة، والنسخ الاحتياطية غير مؤكدة، والتعديلات الصغيرة تنتظر أسابيع.' ),
			'get'   => array(
				array( 'en' => 'WordPress, theme and plugin updates tested before they go live', 'ar' => 'تحديثات ووردبريس والقالب والإضافات مع اختبارها قبل النشر' ),
				array( 'en' => 'Off-site backups and uptime and security monitoring', 'ar' => 'نسخ احتياطية خارجية ومراقبة التوفر والحماية' ),
				array( 'en' => 'Hours for content changes and small improvements', 'ar' => 'ساعات لتعديلات المحتوى والتحسينات الصغيرة' ),
				array( 'en' => 'A short monthly summary of what was done', 'ar' => 'ملخص شهري قصير بما تم إنجازه' ),
			),
			'for'   => array( 'en' => 'Businesses that rely on their site and agencies that want a dependable maintenance partner.', 'ar' => 'الشركات التي تعتمد على موقعها، والوكالات التي تريد شريك صيانة موثوقًا.' ),
			'type'  => '',
			'faq'   => array(
				array( 'q' => array( 'en' => 'Do you maintain sites you did not build?', 'ar' => 'هل تصونين مواقع لم تبنيها؟' ), 'a' => array( 'en' => 'Yes. I start with a short review of the site, then take it over.', 'ar' => 'نعم. أبدأ بمراجعة قصيرة للموقع ثم أتولى صيانته.' ) ),
			),
		),
	);
}

/** URL of a service page, if it exists. */
function dp_service_url( $slug ) {
	static $cache = array();
	if ( ! isset( $cache[ $slug ] ) ) {
		$p              = get_page_by_path( $slug );
		$cache[ $slug ] = ( $p && 'publish' === $p->post_status ) ? get_permalink( $p ) : '';
	}
	return $cache[ $slug ];
}

/** Service page body (used by the "Service Page" Elementor widget). */
function dp_render_service_page( $a = array() ) {
	$all  = dp_service_pages();
	$slug = ! empty( $a['service'] ) && isset( $all[ $a['service'] ] ) ? $a['service'] : key( $all );
	$s    = $all[ $slug ];
	$items = array();
	if ( $s['type'] ) {
		$items = array_values( array_filter( dp_get_projects(), function ( $p ) use ( $s ) { return has_term( sanitize_title( $s['type'] ), 'project_type', $p ) || has_term( $s['type'], 'project_type', $p ); } ) );
	}
	if ( ! $items ) { $items = dp_get_projects( true ); }
	$items = array_slice( array_values( array_filter( $items, 'has_post_thumbnail' ) ), 0, 6 );
	?>
	<section class="page-hero service-hero">
		<div class="wrap">
			<p class="back-link"><a href="<?php echo esc_url( home_url( '/#services' ) ); ?>"><?php echo dp_icon( 'arrow-left' ); // phpcs:ignore ?> <?php echo esc_html( dp_x( 'Services' ) ); ?></a></p>
			<span class="service-ico service-ico-lg" aria-hidden="true"><?php echo dp_icon( $s['icon'], 28 ); // phpcs:ignore ?></span>
			<h1><?php echo esc_html( dp_t( $s['title'] ) ); ?></h1>
			<p class="lead"><?php echo esc_html( dp_t( $s['lead'] ) ); ?></p>
			<p class="actions"><a class="btn magnetic" href="#contact"><span><?php echo esc_html( dp_x( 'Discuss your project' ) ); ?></span><?php echo dp_btn_icon( 'arrow-right' ); // phpcs:ignore ?></a>
			<?php $dp_wa = function_exists( 'dp_wa_link' ) ? dp_wa_link( sprintf( dp_t( array( 'en' => 'Hi Doha, I need help with: %s. ', 'ar' => 'مرحبا ضحى، بدي مساعدة بـ: %s. ' ) ), dp_t( $s['title'] ) ) ) : ''; if ( $dp_wa ) : ?>
				<a class="btn btn-ghost" href="<?php echo esc_url( $dp_wa ); ?>" target="_blank" rel="noopener"><?php echo dp_icon( 'whatsapp', 18 ); // phpcs:ignore ?><span><?php echo esc_html( dp_t( array( 'en' => 'Ask on WhatsApp', 'ar' => 'اسأل على واتساب' ) ) ); ?></span></a>
			<?php endif; ?></p>
		</div>
	</section>
	<section class="section section-tight">
		<div class="wrap service-body">
			<div class="service-col">
				<h2 class="case-label-lg"><?php echo esc_html( dp_x( 'Sound familiar?' ) ); ?></h2>
				<p class="prose-inline"><?php echo esc_html( dp_t( $s['problem'] ) ); ?></p>
				<h2 class="case-label-lg"><?php echo esc_html( dp_x( 'Who it is for' ) ); ?></h2>
				<p class="prose-inline"><?php echo esc_html( dp_t( $s['for'] ) ); ?></p>
			</div>
			<div class="service-col">
				<h2 class="case-label-lg"><?php echo esc_html( dp_x( 'What you get' ) ); ?></h2>
				<ul class="feature-list"><?php foreach ( $s['get'] as $g ) : ?><li><?php echo esc_html( dp_t( $g ) ); ?></li><?php endforeach; ?></ul>
			</div>
		</div>
	</section>
	<?php if ( $items ) : ?>
		<section class="section section-alt">
			<div class="wrap">
				<header class="section-head"><h2><?php echo esc_html( dp_x( 'Related work' ) ); ?></h2></header>
				<div class="grid">
					<?php foreach ( $items as $dp_p ) { get_template_part( 'template-parts/project-card', null, array( 'item' => $dp_p ) ); } ?>
				</div>
				<p class="center"><a class="btn btn-ghost" href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>"><span><?php echo esc_html( dp_x( 'All projects' ) ); ?></span><?php echo dp_btn_icon( 'arrow-right' ); // phpcs:ignore ?></a></p>
			</div>
		</section>
	<?php endif; ?>
	<?php
	dp_render_faq( array( 'title' => 'Questions', 'items' => $s['faq'] ) );
}

/* ---------- Why work with me ---------- */
function dp_why_items() {
	return array(
		array( 'title' => array( 'en' => '15+ years on real projects', 'ar' => 'أكثر من 15 سنة في مشاريع حقيقية' ), 'text' => array( 'en' => 'Over 240 websites for companies, agencies, schools and stores in 19 countries.', 'ar' => 'أكثر من 240 موقع لشركات ووكالات ومدارس ومتاجر في 19 دولة.' ) ),
		array( 'title' => array( 'en' => 'You talk to the developer', 'ar' => 'تتواصل مع المطوّرة مباشرة' ), 'text' => array( 'en' => 'No account managers in between. The person you brief is the person who builds.', 'ar' => 'دون وسطاء. الشخص الذي تشرح له هو نفسه من ينفّذ.' ) ),
		array( 'title' => array( 'en' => 'Design, code and performance', 'ar' => 'التصميم والبرمجة والأداء' ), 'text' => array( 'en' => 'I care how the site looks, how it is built and how fast it loads.', 'ar' => 'أهتم بشكل الموقع وطريقة بنائه وسرعة تحميله معًا.' ) ),
		array( 'title' => array( 'en' => 'I fix, not only build', 'ar' => 'أصلح ولا أكتفي بالبناء' ), 'text' => array( 'en' => 'Old, complex or hacked sites are welcome. Many of my projects started as rescues.', 'ar' => 'المواقع القديمة أو المعقدة أو المخترقة مرحّب بها، والكثير من مشاريعي بدأت كعمليات إنقاذ.' ) ),
		array( 'title' => array( 'en' => 'Arabic and English', 'ar' => 'العربية والإنجليزية' ), 'text' => array( 'en' => 'Bilingual, right-to-left sites done properly, and communication in both languages.', 'ar' => 'مواقع ثنائية اللغة ومن اليمين إلى اليسار بشكل صحيح، وتواصل باللغتين.' ) ),
		array( 'title' => array( 'en' => 'I stay after launch', 'ar' => 'أبقى معك بعد الإطلاق' ), 'text' => array( 'en' => 'Backups before every change, testing on a staging copy, and support when you need it.', 'ar' => 'نسخة احتياطية قبل كل تعديل، واختبار على نسخة تجريبية، ودعم عندما تحتاجه.' ) ),
	);
}

function dp_render_why( $a = array() ) {
	$title = ! empty( $a['title'] ) ? $a['title'] : array( 'en' => 'Why work with me', 'ar' => 'لماذا تعمل معي' );
	$items = ! empty( $a['items'] ) ? $a['items'] : dp_why_items();
	?>
	<section id="why" class="section" data-anim="why">
		<div class="wrap">
			<header class="section-head"><h2><?php echo esc_html( dp_t( $title ) ); ?></h2></header>
			<div class="why">
				<?php foreach ( $items as $n => $it ) : ?>
					<div class="why-item">
						<span class="step-num" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span>
						<h3><?php echo esc_html( dp_t( $it['title'] ) ); ?></h3>
						<p><?php echo esc_html( dp_t( $it['text'] ) ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/* ---------- FAQ (with FAQPage schema) ---------- */
function dp_faq_items() {
	return array(
		array( 'q' => array( 'en' => 'Do you work with agencies?', 'ar' => 'هل تعملين مع الوكالات؟' ), 'a' => array( 'en' => 'Yes. I work directly with companies and also with agencies, including white-label work under the agency name.', 'ar' => 'نعم. أعمل مباشرة مع الشركات، ومع الوكالات أيضًا، ومنها العمل باسم الوكالة.' ) ),
		array( 'q' => array( 'en' => 'Do you offer support after launch?', 'ar' => 'هل تقدّمين دعمًا بعد الإطلاق؟' ), 'a' => array( 'en' => 'Yes. You can book fixes as you need them, or a monthly care plan with updates, backups and small changes.', 'ar' => 'نعم. يمكنك طلب الإصلاحات عند الحاجة، أو خطة صيانة شهرية تشمل التحديثات والنسخ الاحتياطية والتعديلات الصغيرة.' ) ),
		array( 'q' => array( 'en' => 'Can you fix a hacked WordPress site?', 'ar' => 'هل تصلحين مواقع ووردبريس المخترقة؟' ), 'a' => array( 'en' => 'Yes. I clean the malware, find how the attacker got in, close it and harden the site.', 'ar' => 'نعم. أنظّف البرمجيات الخبيثة، وأحدد طريقة الاختراق وأغلقها، وأحصّن الموقع.' ) ),
		array( 'q' => array( 'en' => 'Can you move my site without losing data?', 'ar' => 'هل يمكن نقل موقعي دون خسارة البيانات؟' ), 'a' => array( 'en' => 'Yes. The new copy is tested first, old links are redirected, and the switch is planned at a quiet time.', 'ar' => 'نعم. تُختبر النسخة الجديدة أولًا، وتُحوّل الروابط القديمة، ويتم التحويل في وقت هادئ.' ) ),
		array( 'q' => array( 'en' => 'How long does a project take?', 'ar' => 'كم يستغرق المشروع؟' ), 'a' => array( 'en' => 'A company website usually takes 2 to 4 weeks once content is ready; stores and larger platforms are planned in stages.', 'ar' => 'يستغرق موقع الشركة عادة من أسبوعين إلى أربعة بعد جاهزية المحتوى، والمتاجر والمنصات الأكبر تُقسّم إلى مراحل.' ) ),
		array( 'q' => array( 'en' => 'Do you work remotely? Which time zones?', 'ar' => 'هل تعملين عن بُعد؟ وما المناطق الزمنية؟' ), 'a' => array( 'en' => 'Yes, fully remote. I am on UTC+3, which overlaps the whole working day in the Gulf and most of it in Europe.', 'ar' => 'نعم، عن بُعد بالكامل. توقيتي UTC+3، وهو يغطي يوم العمل كاملًا في الخليج ومعظمه في أوروبا.' ) ),
	);
}

function dp_render_faq( $a = array() ) {
	$items = ! empty( $a['items'] ) ? $a['items'] : dp_faq_items();
	$title = ! empty( $a['title'] ) ? $a['title'] : 'Frequently asked questions';
	$ld    = array();
	?>
	<section id="faq" class="section" data-anim="faq">
		<div class="wrap faq-wrap">
			<header class="section-head"><h2><?php echo esc_html( dp_x( dp_t( $title ) ) ); ?></h2></header>
			<div class="faq">
				<?php foreach ( $items as $it ) :
					$q    = dp_t( $it['q'] );
					$ans  = dp_t( $it['a'] );
					$ld[] = array( '@type' => 'Question', 'name' => $q, 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $ans ) );
					?>
					<details class="faq-item">
						<summary><span><?php echo esc_html( $q ); ?></span><span class="faq-plus" aria-hidden="true"></span></summary>
						<p><?php echo esc_html( $ans ); ?></p>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<script type="application/ld+json"><?php echo wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $ld ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
	<?php
}

/* ---------- Testimonials (shown only when at least one is added) ---------- */
function dp_render_testimonials( $a = array() ) {
	$items = array_values( array_filter( (array) ( isset( $a['items'] ) ? $a['items'] : array() ), function ( $t ) { return ! empty( $t['quote'] ); } ) );
	if ( ! $items ) { return; }
	?>
	<section id="testimonials" class="section section-alt" data-anim="testimonials">
		<div class="wrap">
			<header class="section-head"><h2><?php echo esc_html( ! empty( $a['title'] ) ? $a['title'] : dp_x( 'What clients say' ) ); ?></h2></header>
			<div class="quotes">
				<?php foreach ( $items as $t ) : ?>
					<figure class="quote">
						<blockquote><p><?php echo esc_html( $t['quote'] ); ?></p></blockquote>
						<figcaption><strong><?php echo esc_html( $t['name'] ); ?></strong><?php if ( ! empty( $t['role'] ) ) : ?><span><?php echo esc_html( $t['role'] ); ?></span><?php endif; ?></figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/* ---------- Create the service pages (once; never overwrites an edited page) ---------- */
function dp_create_service_pages() {
	if ( ! function_exists( 'dp_el_section' ) ) { return array(); }
	$made = array();
	foreach ( dp_service_pages() as $slug => $s ) {
		if ( get_page_by_path( $slug ) ) { continue; }
		$sec = dp_el_section( 'dp-service-page', 'svc-' . $slug );
		$sec['elements'][0]['elements'][0]['settings'] = array( 'service' => $slug );
		$data = array( $sec, dp_el_section( 'dp-contact', 'svc-c-' . $slug ) );
		$id   = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $s['title']['en'], 'post_name' => $slug ) );
		if ( ! $id || is_wp_error( $id ) ) { continue; }
		update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
		update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		update_post_meta( $id, '_dp_service', $slug );
		$made[] = $s['title']['en'];
	}
	return $made;
}

/* Service page titles in Arabic, and the document title. */
add_filter( 'the_title', function ( $title, $id = 0 ) {
	if ( is_admin() || ! $id || ! dp_is_ar() ) { return $title; }
	$slug = get_post_meta( $id, '_dp_service', true );
	$all  = dp_service_pages();
	return ( $slug && isset( $all[ $slug ] ) ) ? $all[ $slug ]['title']['ar'] : $title;
}, 9, 2 );
add_filter( 'single_post_title', function ( $title, $post ) {
	if ( ! dp_is_ar() || ! $post ) { return $title; }
	$slug = get_post_meta( $post->ID, '_dp_service', true );
	$all  = dp_service_pages();
	return ( $slug && isset( $all[ $slug ] ) ) ? $all[ $slug ]['title']['ar'] : $title;
}, 10, 2 );
add_filter( 'dp_service_description', function ( $d ) {
	if ( ! is_page() ) { return $d; }
	$slug = get_post_meta( get_queried_object_id(), '_dp_service', true );
	$all  = dp_service_pages();
	return ( $slug && isset( $all[ $slug ] ) ) ? dp_t( $all[ $slug ]['lead'] ) : $d;
} );
