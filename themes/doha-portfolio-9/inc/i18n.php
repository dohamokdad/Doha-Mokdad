<?php
/**
 * English / Arabic. The visitor picks a language with the switch in the header (?lang=ar / ?lang=en).
 * Arabic turns the page right to left and translates the theme's own text. Text you type yourself
 * (in Elementor or on a project) is shown as written.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Current language: 'ar' or 'en'. Admin screens stay in English. */
function dp_lang() {
	static $lang = null;
	if ( null !== $lang ) { return $lang; }
	if ( is_admin() && ! wp_doing_ajax() ) { return 'en'; }
	$q = isset( $_GET['lang'] ) ? sanitize_key( wp_unslash( $_GET['lang'] ) ) : ''; // phpcs:ignore
	if ( in_array( $q, array( 'ar', 'en' ), true ) ) {
		$lang = $q;
	} else {
		$c    = isset( $_COOKIE['dp_lang'] ) ? sanitize_key( wp_unslash( $_COOKIE['dp_lang'] ) ) : '';
		$lang = in_array( $c, array( 'ar', 'en' ), true ) ? $c : 'en';
	}
	return $lang;
}
function dp_is_ar() { return 'ar' === dp_lang(); }

/* Remember the choice, and keep page caches from mixing languages. */
add_action( 'init', function () {
	if ( is_admin() ) { return; }
	if ( isset( $_GET['lang'] ) && in_array( $_GET['lang'], array( 'ar', 'en' ), true ) && ! headers_sent() ) { // phpcs:ignore
		setcookie( 'dp_lang', sanitize_key( $_GET['lang'] ), time() + YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN ); // phpcs:ignore
	}
	if ( dp_is_ar() || isset( $_GET['lang'] ) ) { // phpcs:ignore
		if ( ! defined( 'DONOTCACHEPAGE' ) ) { define( 'DONOTCACHEPAGE', true ); }
	}
}, 1 );

/* Arabic pages keep ?lang=ar on every internal link, so the language survives navigation and caching. */
add_filter( 'home_url', function ( $url, $path ) {
	if ( is_admin() || ! dp_is_ar() || false !== strpos( (string) $path, 'wp-json' ) || false !== strpos( $url, 'rest_route' ) ) { return $url; }
	return add_query_arg( 'lang', 'ar', $url );
}, 10, 2 );

add_filter( 'language_attributes', function ( $out ) {
	if ( is_admin() || ! dp_is_ar() ) { return $out; }
	$out = preg_replace( '/lang="[^"]*"/', 'lang="ar"', $out );
	$out = preg_replace( '/\sdir="[^"]*"/', '', $out );
	return $out . ' dir="rtl"';
} );
add_filter( 'body_class', function ( $c ) { if ( dp_is_ar() ) { $c[] = 'rtl'; $c[] = 'dp-ar'; } return $c; } );

/** URL of the current page in the other language. */
function dp_lang_switch_url() {
	$other = dp_is_ar() ? 'en' : 'ar';
	return add_query_arg( 'lang', $other );
}

/* ---------- Interface strings (the theme's __() calls) ---------- */
function dp_ar_strings() {
	return array(
		'%d WordPress websites for clients across the Gulf, Europe, North America and Asia. Hover a card to scroll the site, click to open the case study.' => '%d موقع ووردبريس لعملاء في الخليج وأوروبا وأمريكا الشمالية وآسيا. مرّر المؤشر فوق البطاقة لتصفّح الموقع، واضغط لفتح دراسة الحالة.',
		'%d WordPress websites for clients across the Gulf, Europe, North America and Asia. Hover a card to scroll the site, click to open it.' => '%d موقع ووردبريس لعملاء في الخليج وأوروبا وأمريكا الشمالية وآسيا. مرّر المؤشر فوق البطاقة لتصفّح الموقع، واضغط لفتحه.',
		'%d project'            => 'مشاريع: %d',
		'%d projects'           => 'مشاريع: %d',
		'%d projects.'          => 'عدد المشاريع: %d.',
		'All'                   => 'الكل',
		'All projects'          => 'كل المشاريع',
		'All, with filters'     => 'الكل مع الفلاتر',
		'As delivered'          => 'كما سُلّم',
		'Back to the home page' => 'العودة إلى الصفحة الرئيسية',
		'Based in'              => 'مكان الإقامة',
		'Built at'              => 'نُفّذ لدى',
		'By company'            => 'حسب الشركة',
		'Case study coming soon. Visit the live site to see the project.' => 'دراسة الحالة قريبًا. يمكنك زيارة الموقع للاطلاع على المشروع.',
		'Certificate viewer'    => 'عارض الشهادات',
		'Close'                 => 'إغلاق',
		'Companies'             => 'الشركات',
		'Country'               => 'البلد',
		'Download CV'           => 'تحميل السيرة الذاتية',
		'Education'             => 'التعليم',
		'Email'                 => 'البريد الإلكتروني',
		'Filter by type'        => 'تصفية حسب النوع',
		'Filter certificates by type' => 'تصفية الشهادات حسب النوع',
		'How it was delivered'  => 'مراحل التنفيذ',
		'Key features'          => 'أهم الميزات',
		'Languages'             => 'اللغات',
		'Menu'                  => 'القائمة',
		'More projects'         => 'مشاريع أخرى',
		'Next'                  => 'التالي',
		'No projects match your search.' => 'لا توجد مشاريع تطابق بحثك.',
		'Nothing found here.'   => 'لا يوجد شيء هنا.',
		'Open %s full size'     => 'عرض %s بالحجم الكامل',
		'Other'                 => 'أخرى',
		'Page builder'          => 'منشئ الصفحات',
		'Phone'                 => 'الهاتف',
		'Platform'              => 'المنصة',
		'Plugins'               => 'الإضافات',
		'Previous'              => 'السابق',
		'Primary'               => 'القائمة الرئيسية',
		'Project facts'         => 'معلومات المشروع',
		'Screenshots show the site as I delivered it%s. The live site has changed or gone offline since.' => 'تُظهر الصور الموقع كما سلّمته%s. تغيّر الموقع منذ ذلك الحين أو توقّف عن العمل.',
		' in %s'                => ' في %s',
		'Search by name, country or company' => 'ابحث بالاسم أو البلد أو الشركة',
		'Search projects'       => 'البحث في المشاريع',
		'Selected clients'      => 'عملاء مختارون',
		'Show all %d certificates' => 'عرض كل الشهادات (%d)',
		'Skip to content'       => 'انتقل إلى المحتوى',
		'Start a project'       => 'ابدأ مشروعك',
		'Services'              => 'الخدمات',
		'Technologies'          => 'التقنيات',
		'Theme'                 => 'القالب',
		'Type'                  => 'النوع',
		'UI and UX'             => 'واجهة وتجربة المستخدم',
		'View'                  => 'طريقة العرض',
		'View all %d'           => 'عرض الكل (%d)',
		'View all %d projects'  => 'عرض كل المشاريع (%d)',
		'View certificate: %s'  => 'عرض الشهادة: %s',
		'Visit %s'              => 'زيارة %s',
		'Work'                  => 'الأعمال',
		'Experience'            => 'الخبرات',
		'Skills'                => 'المهارات',
		'Certificates'          => 'الشهادات',
		'Switch light or dark mode' => 'التبديل بين الوضع الفاتح والداكن',
		'Language'              => 'اللغة',
	);
}
add_filter( 'gettext_doha-portfolio', function ( $t, $text ) {
	if ( ! dp_is_ar() ) { return $t; }
	$m = dp_ar_strings();
	return isset( $m[ $text ] ) ? $m[ $text ] : $t;
}, 10, 2 );
add_filter( 'ngettext_doha-portfolio', function ( $t, $single, $plural, $n ) {
	if ( ! dp_is_ar() ) { return $t; }
	$m   = dp_ar_strings();
	$key = 1 === (int) $n ? $single : $plural;
	return isset( $m[ $key ] ) ? $m[ $key ] : $t;
}, 10, 4 );

/* ---------- Theme content (CV text, terms, countries, dates) ---------- */
function dp_ar_months() {
	return array( 'January' => 'يناير', 'February' => 'فبراير', 'March' => 'مارس', 'April' => 'أبريل', 'May' => 'مايو', 'June' => 'يونيو', 'July' => 'يوليو', 'August' => 'أغسطس', 'September' => 'سبتمبر', 'October' => 'أكتوبر', 'November' => 'نوفمبر', 'December' => 'ديسمبر', 'Jan' => 'يناير', 'Feb' => 'فبراير', 'Mar' => 'مارس', 'Apr' => 'أبريل', 'Jun' => 'يونيو', 'Jul' => 'يوليو', 'Aug' => 'أغسطس', 'Sep' => 'سبتمبر', 'Oct' => 'أكتوبر', 'Nov' => 'نوفمبر', 'Dec' => 'ديسمبر' );
}

function dp_ar_dict() {
	static $d = null;
	if ( null !== $d ) { return $d; }
	$d = array(
		// Profile
		'Doha Mokdad' => 'ضحى مقداد',
		'Senior WordPress Developer' => 'مطوّرة ووردبريس أولى',
		'Senior WordPress developer. I build fast, reliable sites: custom themes, plugins and WooCommerce stores, cared for after launch.' => 'مطوّرة ووردبريس أولى. أبني مواقع سريعة وموثوقة: قوالب وإضافات مخصّصة ومتاجر WooCommerce، مع متابعتها بعد الإطلاق.',
		'Damascus, Syria. Open to remote and UAE roles.' => 'دمشق، سوريا. متاحة للعمل عن بُعد وفي الإمارات.',
		'Available for new projects, remote or in the UAE' => 'متاحة لمشاريع جديدة، عن بُعد أو في الإمارات',
		// Section titles
		'Selected websites' => 'مواقع مختارة',
		'Experience' => 'الخبرات',
		'15+ years, 240+ websites and clients in 19 countries, from Dubai agencies to schools in Japan.' => 'أكثر من 15 سنة، وأكثر من 240 موقع، وعملاء في 19 دولة، من وكالات دبي إلى مدارس في اليابان.',
		'Skills and education' => 'المهارات والتعليم',
		'Recognition' => 'التكريم',
		'Recognition from the teams I worked with.' => 'تقدير من الفرق التي عملت معها.',
		'Certificates' => 'الشهادات',
		'Degrees, courses and tests since 2009, newest first.' => 'شهادات جامعية ودورات واختبارات منذ 2009، الأحدث أولًا.',
		'Have a WordPress project in mind?' => 'لديك مشروع ووردبريس؟',
		'New builds, redesigns, WooCommerce stores, migrations, or a site that needs rescuing. Tell me about it.' => 'مواقع جديدة، أو إعادة تصميم، أو متاجر WooCommerce، أو نقل مواقع، أو موقع يحتاج إلى إنقاذ. أخبرني عنه.',
		'Start a project' => 'ابدأ مشروعك',
		'Download CV' => 'تحميل السيرة الذاتية',
		// Experience
		'WordPress Developer' => 'مطوّرة ووردبريس',
		'Senior Web Developer' => 'مطوّرة ويب أولى',
		'Short-term and contract roles' => 'أعمال قصيرة المدى وبعقود',
		'Hosting Technical Support' => 'دعم فني للاستضافة',
		'Information Systems Developer' => 'مطوّرة نظم معلومات',
		'Earlier experience' => 'خبرات سابقة',
		'Teaching and development' => 'التدريس والتطوير',
		'Freelance' => 'عمل حر',
		'Digital marketing agency' => 'وكالة تسويق رقمي',
		'Brand licensing agency' => 'وكالة ترخيص علامات تجارية',
		'Spacetoon Media Group' => 'مجموعة سبيستون الإعلامية',
		'Syrian Computer Society' => 'الجمعية العلمية السورية للمعلوماتية',
		'UCG, United Communication Group' => 'UCG، مجموعة الاتصالات المتحدة',
		'Present' => 'حتى الآن',
		'Remote' => 'عن بُعد',
		'Dubai, UAE' => 'دبي، الإمارات',
		'Syria' => 'سوريا',
		'Syria, Saudi Arabia' => 'سوريا، السعودية',
		'Build and maintain WordPress and Elementor Pro sites for Dubai and Gulf clients in real estate, events, logistics, holding companies and F&B.' => 'بناء مواقع ووردبريس و Elementor Pro وصيانتها لعملاء في دبي والخليج في قطاعات العقارات والفعاليات والخدمات اللوجستية والشركات القابضة والمطاعم.',
		'Developed a multi-client content-calendar platform with branded pages per client, a digital menu and an AI chatbot site for the agency.' => 'تطوير منصة تقويم محتوى لعدة عملاء بصفحات تحمل هوية كل عميل، ومنيو رقمي، وموقع مساعد ذكي (AI chatbot) للوكالة.',
		'Own QA, speed optimisation, site migrations and security, including cleaning up and hardening compromised client sites.' => 'مسؤولة عن ضمان الجودة وتحسين السرعة ونقل المواقع والحماية، ومنها تنظيف مواقع العملاء المخترقة وتحصينها.',
		'Converted Figma, PSD and Illustrator designs into pixel-perfect, responsive WordPress themes.' => 'تحويل تصاميم Figma و PSD و Illustrator إلى قوالب ووردبريس متجاوبة ومطابقة للتصميم بدقة.',
		'Built custom features in PHP (OOP), MySQL, JavaScript, jQuery and AJAX; extended sites with custom plugins and API integrations.' => 'برمجة ميزات مخصّصة بلغات PHP (OOP) و MySQL و JavaScript و jQuery و AJAX، وتوسيع المواقع بإضافات مخصّصة وربطها بواجهات API.',
		'Ran cross-browser, device and UI/UX testing before launch and improved existing systems with new features.' => 'اختبار التوافق مع المتصفحات والأجهزة وتجربة المستخدم قبل الإطلاق، وتطوير الأنظمة القائمة بميزات جديدة.',
		'Delivered WordPress projects end to end for clients in 12+ countries: discovery, design handoff, build, launch and support.' => 'تنفيذ مشاريع ووردبريس كاملة لعملاء في أكثر من 12 دولة: دراسة المتطلبات، واستلام التصميم، والبناء، والإطلاق، والدعم.',
		'Built WooCommerce stores, multi-vendor marketplaces and education platforms for schools and academies in the UK, Japan, Saudi Arabia and Turkey.' => 'بناء متاجر WooCommerce وأسواق متعددة البائعين ومنصات تعليمية لمدارس وأكاديميات في بريطانيا واليابان والسعودية وتركيا.',
		'Audited and repaired legacy code, fixed performance issues and advised on SEO best practice.' => 'مراجعة الشيفرات القديمة وإصلاحها، وحل مشاكل الأداء، وتقديم الاستشارات في أفضل ممارسات SEO.',
		'Fixed and improved the website, online store and mobile app; optimised navigation, collections, search and homepage.' => 'إصلاح الموقع والمتجر الإلكتروني وتطبيق الجوال وتحسينها، وتطوير التنقل والمجموعات والبحث والصفحة الرئيسية.',
		'Monitored analytics and conversion data and reported on performance with recommendations for growth.' => 'متابعة التحليلات وبيانات التحويل، وإعداد تقارير الأداء مع توصيات للنمو.',
		"Designed and built 60+ corporate websites for the Timgad Group's trading and manufacturing companies and other Dubai clients." => 'تصميم وبناء أكثر من 60 موقعًا لشركات التجارة والتصنيع في مجموعة Timgad ولعملاء آخرين في دبي.',
		'Gathered requirements, designed layouts, managed content and supported hosting: site transfers and backups.' => 'جمع المتطلبات، وتصميم الصفحات، وإدارة المحتوى، ودعم الاستضافة من نقل المواقع والنسخ الاحتياطي.',
		'Rebuilt the MicroSolutions website; developed services and client sites for NHTAM; built web services for Syriatel mobile apps; set up Zoho CRM and bilingual content for Zayer.' => 'إعادة بناء موقع MicroSolutions، وتطوير خدمات ومواقع لعملاء NHTAM، وبناء خدمات ويب لتطبيقات Syriatel، وإعداد Zoho CRM ومحتوى ثنائي اللغة لموقع زائر.',
		'Supported shared, VPS, dedicated and co-located hosting for government and VIP clients; managed plans, invoices and domains in PBAS and Plesk.' => 'دعم الاستضافة المشتركة و VPS والخوادم المخصّصة والمستضافة لعملاء حكوميين وكبار العملاء، وإدارة الباقات والفواتير والنطاقات عبر PBAS و Plesk.',
		'Built WordPress sites for UCG and group companies: ECC, FITOUT and Maqta Gateway.' => 'بناء مواقع ووردبريس لـ UCG وشركات المجموعة: ECC و FITOUT و بوابة المقطع.',
		'Administered and developed business applications; built the Game Power 7 website and distribution-centres portal.' => 'إدارة تطبيقات الأعمال وتطويرها، وبناء موقع Game Power 7 وبوابة مراكز التوزيع.',
		'Web Programming Trainer and ICDL instructor at Paradise Computer Center (Al Fardos); Informatics Teacher at two Damascus schools; ASP.NET Web Developer at DRC Company.' => 'مدرّبة برمجة ويب و ICDL في مركز الفردوس للحاسوب، ومدرّسة معلوماتية في مدرستين في دمشق، ومطوّرة ويب ASP.NET في شركة DRC.',
		// Skills
		'WordPress' => 'ووردبريس',
		'Front end' => 'الواجهة الأمامية',
		'Back end' => 'الواجهة الخلفية',
		'Operations' => 'التشغيل والصيانة',
		'Custom themes' => 'قوالب مخصّصة', 'Custom plugins' => 'إضافات مخصّصة', 'Multi-vendor marketplaces' => 'أسواق متعددة البائعين', 'LMS (LearnPress / Eduma)' => 'منصات تعليم LMS (LearnPress / Eduma)', 'Multilingual & RTL' => 'مواقع متعددة اللغات و RTL',
		'Responsive design' => 'تصميم متجاوب', 'Figma / PSD to WordPress' => 'تحويل Figma / PSD إلى ووردبريس',
		'Third-party integrations' => 'الربط مع خدمات خارجية',
		'Migrations' => 'نقل المواقع', 'Backups' => 'النسخ الاحتياطي', 'Performance & Core Web Vitals' => 'الأداء و Core Web Vitals', 'Malware cleanup & hardening' => 'تنظيف البرمجيات الخبيثة والتحصين', 'Domains & DNS' => 'النطاقات و DNS', 'Technical SEO' => 'SEO تقني',
		'BSc (Hons) Computer Science' => 'بكالوريوس علوم الحاسوب (مع مرتبة الشرف)',
		'University of Greenwich, 2010-2014, Second Class Honours' => 'جامعة غرينتش، 2010-2014، مرتبة الشرف الثانية',
		'BTEC Professional Diploma in Computing' => 'دبلوم BTEC المهني في الحوسبة',
		'Edexcel, 2010-2011' => 'Edexcel، 2010-2011',
		'Computer Software Engineering, Programming' => 'هندسة البرمجيات، برمجة',
		'Technical Computer College (TCC), 2008-2010' => 'المعهد التقاني للحاسوب، 2008-2010',
		'Arabic (native)' => 'العربية (اللغة الأم)', 'English (professional)' => 'الإنجليزية (مستوى مهني)',
		// Recognition
		'Employee of the Month' => 'موظفة الشهر',
		'ProITs, Web Development department' => 'ProITs، قسم تطوير الويب',
		'Great Job appreciation card' => 'بطاقة تقدير "عمل رائع"',
		'Syriatel Mobile Telecom. Probation appraisal: Successful Performer, 4.06 out of 5' => 'سيريتل. تقييم فترة التجربة: أداء ناجح، 4.06 من 5',
		// Certificate types
		'Degrees & diplomas' => 'شهادات ودبلومات', 'Development' => 'البرمجة والتطوير', 'Computer skills' => 'مهارات الحاسوب', 'Languages' => 'اللغات', 'Professional skills' => 'مهارات مهنية', 'Islamic studies' => 'دراسات إسلامية', 'Other' => 'أخرى',
		// Certificates
		'International Computer Driving Licence course' => 'دورة الرخصة الدولية لقيادة الحاسوب ICDL',
		'Internet Developer and PL/SQL, 60 hours, grade Very Good' => 'تطوير الإنترنت و PL/SQL، 60 ساعة، بتقدير جيد جدًا',
		'ICDL Certificate, syllabus 4.0' => 'شهادة ICDL، المنهاج 4.0',
		'Technical Diploma in Computer Engineering, Software Engineering' => 'الشهادة التقانية في هندسة الحاسوب، اختصاص هندسة البرمجيات',
		'Level 5 BTEC Professional Diploma in Computing' => 'دبلوم BTEC المهني في الحوسبة، المستوى 5',
		'Training of Trainers (ToT)' => 'تدريب المدرّبين (ToT)',
		'NLP Diploma' => 'دبلوم البرمجة اللغوية العصبية',
		'Diploma in Neuro Linguistic Programming' => 'دبلوم البرمجة اللغوية العصبية',
		'BSc (Hons) Computing, Second Class Honours' => 'بكالوريوس الحوسبة، مرتبة الشرف الثانية',
		'Speed Reading' => 'القراءة السريعة',
		'Bachelor in Information Technology (BIT)' => 'إجازة في تقانة المعلومات (BIT)',
		'PHP Tutorial course' => 'دورة PHP',
		'Learn Brackets, the open source text editor' => 'تعلّم محرر Brackets مفتوح المصدر',
		'Basic Computer Skills test, 67%' => 'اختبار مهارات الحاسوب الأساسية، 67%',
		'Microsoft Office Skills test, 53%' => 'اختبار مهارات Microsoft Office، 53%',
		'IQ test, score 85' => 'اختبار الذكاء، النتيجة 85',
		'Introduction to HTML5' => 'مقدمة في HTML5',
		"Memorisation of Al-Arba'in Al-Nawawiyya, Excellent (98%)" => 'حفظ متن الأربعين النووية، بتقدير ممتاز (98%)',
		'Child Mental Health' => 'الصحة النفسية للطفل',
		'Memorisation of Manzumat Al-Munira, 100%' => 'حفظ منظومة المنيرة، 100%',
		'Building Websites Using WordPress' => 'بناء المواقع باستخدام ووردبريس',
		'Dart 101, beginner level' => 'Dart 101، المستوى المبتدئ',
		'Advertising, two-day training workshop (5 hours)' => 'الإعلان، ورشة تدريبية ليومين (5 ساعات)',
		'Riyada House, Albarakah Association, Damascus' => 'بيت الريادة، جمعية البركة، دمشق',
		'Fiqh of Worship: the Book of Fasting, Good (80%)' => 'فقه العبادات: كتاب الصيام، بتقدير جيد (80%)',
		'Basic English 1: Elementary' => 'الإنجليزية الأساسية 1: المستوى الابتدائي',
		'Basic English 2: Pre-intermediate' => 'الإنجليزية الأساسية 2: ما قبل المتوسط',
		'English speaking level B1, Intermediate' => 'محادثة اللغة الإنجليزية، المستوى B1 المتوسط',
		'Change Center for Information Technology (CCIT), Damascus' => 'مركز التغيير لتقانة المعلومات (CCIT)، دمشق',
		'New Horizons Computer Learning Centers' => 'مراكز نيو هورايزنز لتعليم الحاسوب',
		'Russian Cultural Centre, Damascus' => 'المركز الثقافي الروسي، دمشق',
		'Technical Institute of Computer, Damascus University' => 'المعهد التقاني للحاسوب، جامعة دمشق',
		'Edexcel, through the Syrian Virtual University' => 'Edexcel، عبر الجامعة الافتراضية السورية',
		'Shabab programme, The Syria Trust for Development' => 'برنامج شباب، الأمانة السورية للتنمية',
		'University of Greenwich' => 'جامعة غرينتش',
		'Syrian Virtual University' => 'الجامعة الافتراضية السورية',
		'Udacity, One Million Arab Coders initiative' => 'Udacity، مبادرة مليون مبرمج عربي',
		"King's College London on FutureLearn" => 'كينغز كوليدج لندن عبر FutureLearn',
		'Bayt.com and ExpertRating' => 'بيت.كوم و ExpertRating',
		'University of Michigan on Coursera' => 'جامعة ميشيغان عبر Coursera',
		'Alif Lam Meem for Distance Learning' => 'ألف لام ميم للتعليم عن بُعد',
		'Edraak' => 'إدراك',
		'Huffaz Al-Mutoon platform and Tamayuz Center' => 'منصة حفاظ المتون ومركز تميّز',
		'Satr platform and Tuwaiq Academy' => 'منصة سطر وأكاديمية طويق',
		'Unlimited Horizons' => 'آفاق بلا حدود',
		// Countries
		'UAE' => 'الإمارات', 'Saudi Arabia' => 'السعودية', 'Qatar' => 'قطر', 'Kuwait' => 'الكويت', 'Oman' => 'عُمان', 'Lebanon' => 'لبنان', 'Turkey' => 'تركيا', 'Tunisia' => 'تونس', 'Afghanistan' => 'أفغانستان', 'Africa' => 'أفريقيا', 'Belgium' => 'بلجيكا', 'Canada' => 'كندا', 'Germany' => 'ألمانيا', 'Japan' => 'اليابان', 'Sweden' => 'السويد', 'UK' => 'بريطانيا', 'USA' => 'الولايات المتحدة',
		// Project types
		'Corporate & Business' => 'شركات وأعمال', 'E-commerce' => 'متاجر إلكترونية', 'Education & LMS' => 'تعليم ومنصات تعلّم', 'Hospitality, Food & Events' => 'ضيافة ومطاعم وفعاليات', 'Non-profit, Media & Health' => 'منظمات وإعلام وصحة', 'Personal & Creative' => 'شخصي وإبداعي', 'Real Estate & Construction' => 'عقارات ومقاولات', 'Tech & Startups' => 'تقنية وشركات ناشئة', 'Trading & Manufacturing' => 'تجارة وتصنيع',
		// Company descriptions
		'Senior WordPress Developer at a Dubai digital marketing agency. May 2025 - Present.' => 'مطوّرة ووردبريس أولى في وكالة تسويق رقمي في دبي. مايو 2025 - حتى الآن.',
		'Senior Web Developer, Dubai. Corporate websites for the Timgad Group and other UAE companies. 2015 - 2017.' => 'مطوّرة ويب أولى، دبي. مواقع شركات لمجموعة Timgad وشركات أخرى في الإمارات. 2015 - 2017.',
		'WordPress Developer for United Communication Group and its companies, Dubai. 2015.' => 'مطوّرة ووردبريس لمجموعة الاتصالات المتحدة وشركاتها، دبي. 2015.',
		'WordPress Developer for a Dubai brand licensing agency. 2023.' => 'مطوّرة ووردبريس لوكالة ترخيص علامات تجارية في دبي. 2023.',
		'Web Developer for a Saudi digital marketing agency. 2016 - 2017.' => 'مطوّرة ويب لوكالة تسويق رقمي سعودية. 2016 - 2017.',
		'Information Systems Developer at a Spacetoon Media Group company. 2013 - 2014.' => 'مطوّرة نظم معلومات في شركة تابعة لمجموعة سبيستون الإعلامية. 2013 - 2014.',
		'Senior WordPress Developer. Figma, PSD and Illustrator designs built as responsive WordPress themes. 2017 - 2026.' => 'مطوّرة ووردبريس أولى. تحويل تصاميم Figma و PSD و Illustrator إلى قوالب ووردبريس متجاوبة. 2017 - 2026.',
		'Web Developer at MicroSolutions, Damascus: the company website and client apps. 2016 - 2019.' => 'مطوّرة ويب في MicroSolutions، دمشق: موقع الشركة وتطبيقات لعملائها. 2016 - 2019.',
		'Direct clients across the Gulf, Europe, North America and Asia. 2009 - 2025.' => 'عملاء مباشرون في الخليج وأوروبا وأمريكا الشمالية وآسيا. 2009 - 2025.',
		'Services' => 'الخدمات',
		'What I can do for you' => 'كيف أساعدك',
		'Pick what you need, or tell me about the problem and I will suggest the right fix.' => 'اختر ما تحتاجه، أو أخبرني بالمشكلة وسأقترح الحل المناسب.',
		'New WordPress websites' => 'مواقع ووردبريس جديدة',
		'Business and corporate sites built from your Figma, PSD or brand, responsive, fast and easy for your team to edit with Elementor or Gutenberg.' => 'مواقع شركات وأعمال من تصميمك على Figma أو PSD أو من هويتك، متجاوبة وسريعة وسهلة التعديل لفريقك عبر Elementor أو Gutenberg.',
		'WooCommerce stores' => 'متاجر WooCommerce',
		'Online stores and multi-vendor marketplaces: products, payments, shipping, Arabic and English, set up to sell from day one.' => 'متاجر إلكترونية وأسواق متعددة البائعين: المنتجات والدفع والشحن، بالعربية والإنجليزية، جاهزة للبيع من اليوم الأول.',
		'Speed optimisation' => 'تسريع المواقع',
		'Slow site? I find what is holding it back and fix it: images, caching, scripts, hosting, Core Web Vitals.' => 'موقعك بطيء؟ أحدد السبب وأعالجه: الصور والتخزين المؤقت والسكربتات والاستضافة و Core Web Vitals.',
		'Hacked site recovery' => 'إصلاح المواقع المخترقة',
		'Malware cleanup, hardening and backups, so a compromised site is clean, secure and back online.' => 'تنظيف البرمجيات الخبيثة والتحصين والنسخ الاحتياطي، ليعود الموقع نظيفًا وآمنًا ويعمل من جديد.',
		'Migrations' => 'نقل المواقع',
		'Moving to new hosting, a new domain or onto WordPress from another platform, without losing content or rankings.' => 'النقل إلى استضافة أو نطاق جديد، أو إلى ووردبريس من منصة أخرى، دون خسارة المحتوى أو ترتيب البحث.',
		'Care and support' => 'الصيانة والدعم',
		'Monthly updates, fixes and small changes, with someone who knows your site and answers quickly.' => 'تحديثات شهرية وإصلاحات وتعديلات صغيرة، مع شخص يعرف موقعك ويرد بسرعة.',
		'Discuss your project' => 'ناقش مشروعك',
		'View my work' => 'شاهد أعمالي',
		'years' => 'سنة خبرة', 'websites' => 'موقع', 'countries' => 'دولة',
		'Available for new projects. I usually reply within one working day.' => 'متاحة لمشاريع جديدة، وأرد عادة خلال يوم عمل.',
		'Damascus, Syria. Available for remote projects and UAE-based roles.' => 'دمشق، سوريا. متاحة لمشاريع عن بُعد ولفرص عمل في الإمارات.',
		'Name' => 'الاسم', 'What do you need?' => 'ماذا تحتاج؟', 'Tell me about the project' => 'أخبرني عن المشروع', 'Send message' => 'إرسال',
		'New website' => 'موقع جديد', 'WooCommerce store' => 'متجر WooCommerce', 'Migration' => 'نقل موقع', 'Something else' => 'شيء آخر',
		'I reply within one working day with questions or a short plan and estimate.' => 'أرد خلال يوم عمل بأسئلة أو بخطة مختصرة وتقدير للتكلفة.',
		'Thank you. Your message is on its way, and I usually reply within one working day.' => 'شكرًا لك. وصلت رسالتك، وأرد عادة خلال يوم عمل.',
		'The message could not be sent. Please email me directly:' => 'تعذّر إرسال الرسالة. راسلني مباشرة على:',
		'A clear path from idea to launch' => 'طريق واضح من الفكرة إلى الإطلاق',
		'No surprises. You always know what is happening, what is next, and what it costs.' => 'بلا مفاجآت. تعرف دائمًا ما الذي يحدث، وما الخطوة التالية، وكم ستكلّف.',
		'Understand' => 'نفهم', 'Build' => 'نبني', 'Launch and care' => 'نطلق ونتابع',
		'A short call or message thread about your goals, audience, content and deadline. You get a clear scope and estimate before any work starts.' => 'مكالمة قصيرة أو رسائل حول أهدافك وجمهورك والمحتوى والموعد النهائي. تحصل على نطاق عمل واضح وتقدير للتكلفة قبل بدء أي عمل.',
		'Design handoff to a working site on a staging link you can follow live, with regular updates and your feedback built in.' => 'من التصميم إلى موقع يعمل على رابط تجريبي تتابعه مباشرة، مع تحديثات منتظمة وملاحظاتك في كل مرحلة.',
		'Testing on every device, speed and SEO checks, then launch. I stay on for fixes, updates and the next improvements.' => 'اختبار على كل الأجهزة، وفحص السرعة و SEO، ثم الإطلاق. وأبقى معك للإصلاحات والتحديثات والتحسينات القادمة.',
		'How I work' => 'طريقة العمل',
		'Learn more' => 'اعرف المزيد', 'Sound familiar?' => 'هل يبدو هذا مألوفًا؟', 'Who it is for' => 'لمن هذه الخدمة', 'What you get' => 'ماذا تحصل', 'Related work' => 'أعمال ذات صلة',
		'Questions' => 'أسئلة شائعة', 'Frequently asked questions' => 'الأسئلة الشائعة', 'What clients say' => 'ماذا يقول العملاء',
		'View case study' => 'عرض دراسة الحالة',
		'Verify online' => 'تحقّق من الشهادة',
		// Menu labels
		'Home' => 'الرئيسية', 'Resume' => 'السيرة الذاتية', 'Works' => 'الأعمال', 'Work' => 'الأعمال', 'Blog' => 'المدونة', 'Contacts' => 'تواصل', 'Contact' => 'تواصل', 'Skills' => 'المهارات', 'All projects' => 'كل المشاريع', 'About' => 'نبذة',
	);
	return $d;
}

/** Translate a piece of theme content in Arabic mode (exact match, or a date like "May 2025 - Present"). */
function dp_x( $s ) {
	if ( ! dp_is_ar() || ! is_string( $s ) || '' === trim( $s ) ) { return $s; }
	$d = dp_ar_dict();
	$k = trim( $s );
	if ( isset( $d[ $k ] ) ) { return $d[ $k ]; }
	if ( preg_match( '/^(?:(?:January|February|March|April|May|June|July|August|September|October|November|December|Present|\d{4})[\s,\-–]*)+$/', $k ) ) {
		return strtr( $k, dp_ar_months() + array( 'Present' => 'حتى الآن' ) );
	}
	return $s;
}

/* Terms (project types and companies) and menu labels in Arabic. */
add_filter( 'get_term', function ( $term ) {
	if ( is_admin() || ! dp_is_ar() || ! is_object( $term ) || ! in_array( $term->taxonomy, array( 'project_type', 'project_company' ), true ) ) { return $term; }
	$term              = clone $term;
	$term->name        = dp_x( html_entity_decode( $term->name, ENT_QUOTES ) );
	$term->description = dp_x( $term->description );
	return $term;
} );
add_filter( 'nav_menu_item_title', function ( $title ) { return dp_x( $title ); } );
add_filter( 'the_title', function ( $title, $id = 0 ) {
	if ( is_admin() || ! dp_is_ar() || ! $id || 'nav_menu_item' !== get_post_type( $id ) ) { return $title; }
	return dp_x( $title );
}, 10, 2 );
