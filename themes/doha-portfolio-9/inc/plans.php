<?php
/**
 * Packages with starting prices, a white-label offer for agencies, and WhatsApp buttons
 * that open a chat with the package or service already written in the message.
 *
 * Prices and texts are edited in Customize → Packages & prices (or in the Elementor "Packages" widget).
 *
 * @package Doha_Portfolio
 */

defined( 'ABSPATH' ) || exit;

/** WhatsApp number from the social links (wa.me/…), digits only. */
function dp_wa_number() {
	foreach ( preg_split( '/[\r\n]+/', (string) dp_opt( 'socials' ) ) as $url ) {
		if ( preg_match( '~(?:wa\.me/|whatsapp\.com/send\?phone=)\+?(\d{7,15})~', $url, $m ) ) { return $m[1]; }
	}
	return '';
}

/** Link that opens WhatsApp with $text already typed. '' when there is no WhatsApp number. */
function dp_wa_link( $text ) {
	$n = dp_wa_number();
	return $n ? 'https://wa.me/' . $n . '?text=' . rawurlencode( $text ) : '';
}

/** Default packages. A price of '' shows "Ask for a quote". */
function dp_plans_defaults() {
	return array(
		array(
			'key'      => 'starter',
			'name'     => array( 'en' => 'Starter website', 'ar' => 'موقع انطلاقة' ),
			'for'      => array( 'en' => 'A clear, fast site for a small business or a person.', 'ar' => 'موقع واضح وسريع لنشاط صغير أو شخص.' ),
			'price'    => '450',
			'time'     => array( 'en' => '1 to 2 weeks', 'ar' => 'من أسبوع إلى أسبوعين' ),
			'features' => array(
				array( 'en' => 'Up to 5 pages, mobile-first', 'ar' => 'حتى 5 صفحات، مصمّم للجوال أولًا' ),
				array( 'en' => 'Contact form and WhatsApp button', 'ar' => 'نموذج تواصل وزر واتساب' ),
				array( 'en' => 'Basic SEO and Google Search Console', 'ar' => 'تهيئة أساسية لمحركات البحث، وربط Google Search Console' ),
				array( 'en' => 'Speed and security setup', 'ar' => 'ضبط السرعة والحماية' ),
				array( 'en' => '2 rounds of changes, 2 weeks of support', 'ar' => 'جولتا تعديل، ودعم لمدة أسبوعين' ),
			),
			'featured' => false,
		),
		array(
			'key'      => 'business',
			'name'     => array( 'en' => 'Business website', 'ar' => 'موقع شركة' ),
			'for'      => array( 'en' => 'A complete company site, in English and Arabic.', 'ar' => 'موقع شركة متكامل بالعربية والإنجليزية.' ),
			'price'    => '850',
			'time'     => array( 'en' => '2 to 4 weeks', 'ar' => 'من أسبوعين إلى أربعة' ),
			'features' => array(
				array( 'en' => 'Up to 12 pages, custom design', 'ar' => 'حتى 12 صفحة، بتصميم خاص' ),
				array( 'en' => 'Two languages with full right-to-left support', 'ar' => 'لغتان مع دعم كامل للعربية من اليمين لليسار' ),
				array( 'en' => 'Services, projects or blog you can edit yourself', 'ar' => 'خدمات أو مشاريع أو مدوّنة تعدّلها بنفسك' ),
				array( 'en' => 'On-page SEO and speed optimization', 'ar' => 'تهيئة الصفحات لمحركات البحث وتحسين السرعة' ),
				array( 'en' => 'Training video, 1 month of support', 'ar' => 'فيديو تدريبي، ودعم لمدة شهر' ),
			),
			'featured' => true,
		),
		array(
			'key'      => 'store',
			'name'     => array( 'en' => 'Online store or e-learning platform', 'ar' => 'متجر إلكتروني أو منصة تعليم عن بعد' ),
			'for'      => array( 'en' => 'A WooCommerce store ready to sell, or a platform for courses and students.', 'ar' => 'متجر WooCommerce جاهز للبيع، أو منصة للدورات والطلاب.' ),
			'price'    => '1200-1500',
			'time'     => array( 'en' => '4 to 6 weeks', 'ar' => 'من أربعة إلى ستة أسابيع' ),
			'features' => array(
				array( 'en' => 'Store: products, filters, payment and shipping', 'ar' => 'المتجر: المنتجات والفلاتر والدفع والشحن' ),
				array( 'en' => 'E-learning: courses, lessons, quizzes and certificates', 'ar' => 'التعليم: الدورات والدروس والاختبارات والشهادات' ),
				array( 'en' => 'Customer or student accounts and dashboards', 'ar' => 'حسابات ولوحات للعملاء أو الطلاب' ),
				array( 'en' => 'Online payment, order and enrolment emails', 'ar' => 'الدفع الإلكتروني ورسائل الطلبات والتسجيل' ),
				array( 'en' => 'Two languages, training video, 1 month of support', 'ar' => 'لغتان، فيديو تدريبي، ودعم لمدة شهر' ),
			),
			'featured' => false,
		),
	);
}

/** Packages after the Customizer's edits (prices; texts stay bilingual unless replaced). */
function dp_plans() {
	$plans = dp_plans_defaults();
	foreach ( $plans as &$p ) {
		$price = get_theme_mod( 'dp_plan_' . $p['key'] . '_price', null );
		if ( null !== $price ) { $p['price'] = trim( (string) $price ); }
		$time = trim( (string) get_theme_mod( 'dp_plan_' . $p['key'] . '_time', '' ) );
		if ( '' !== $time ) { $p['time'] = $time; }
	}
	unset( $p );
	return $plans;
}

function dp_plans_currency() { return trim( (string) get_theme_mod( 'dp_plans_currency', '$' ) ); }

function dp_plan_price_html( $price ) {
	if ( '' === trim( (string) $price ) ) { return '<span class="plan-quote">' . esc_html( dp_t( array( 'en' => 'Ask for a quote', 'ar' => 'اطلب عرض سعر' ) ) ) . '</span>'; }
	// A range ("1200-1500") shows both ends; one number shows "from".
	if ( preg_match( '/^\s*(\d[\d,.]*)\s*[-–]\s*(\d[\d,.]*)\s*$/u', (string) $price, $m ) ) {
		$c = dp_plans_currency();
		return '<span class="plan-amount plan-range">' . esc_html( $c . number_format_i18n( (float) str_replace( ',', '', $m[1] ) ) . ' – ' . $c . number_format_i18n( (float) str_replace( ',', '', $m[2] ) ) ) . '</span>';
	}
	return '<span class="plan-from">' . esc_html( dp_t( array( 'en' => 'from', 'ar' => 'يبدأ من' ) ) ) . '</span> <span class="plan-amount">' . esc_html( dp_plans_currency() . number_format_i18n( (float) str_replace( ',', '', $price ) ) ) . '</span>';
}

/** The packages section, with the agency offer under it. */
function dp_render_plans( $a = array() ) {
	if ( 'no' === get_theme_mod( 'dp_plans_show', 'yes' ) && empty( $a['force'] ) ) { return; }
	$plans = ! empty( $a['items'] ) ? $a['items'] : dp_plans();
	$title = ! empty( $a['title'] ) ? $a['title'] : array( 'en' => 'Clear packages, clear prices', 'ar' => 'باقات واضحة وأسعار واضحة' );
	$intro = ! empty( $a['intro'] ) ? $a['intro'] : array( 'en' => 'Starting prices for the most common projects. Every project is quoted exactly after a short call.', 'ar' => 'أسعار تبدأ منها أكثر المشاريع شيوعًا. يُحدَّد سعر كل مشروع بدقة بعد مكالمة قصيرة.' );
	$agency_rate = trim( (string) get_theme_mod( 'dp_agency_rate', '15' ) );
	?>
	<section id="plans" class="section" data-anim="plans">
		<div class="wrap">
			<header class="section-head">
				<h2><?php echo esc_html( dp_t( $title ) ); ?></h2>
				<p class="side-intro"><?php echo esc_html( dp_t( $intro ) ); ?></p>
			</header>
			<div class="plans">
				<?php foreach ( $plans as $p ) :
					$name = dp_t( $p['name'] );
					$wa   = dp_wa_link( sprintf( dp_t( array( 'en' => 'Hi Doha, I am interested in the "%s" package. ', 'ar' => 'مرحبا ضحى، مهتم بباقة "%s". ' ) ), $name ) );
					?>
					<article class="plan<?php echo ! empty( $p['featured'] ) ? ' is-featured' : ''; ?>">
						<?php if ( ! empty( $p['featured'] ) ) : ?><span class="plan-badge"><?php echo esc_html( dp_t( array( 'en' => 'Most chosen', 'ar' => 'الأكثر طلبًا' ) ) ); ?></span><?php endif; ?>
						<h3><?php echo esc_html( $name ); ?></h3>
						<p class="plan-for"><?php echo esc_html( dp_t( $p['for'] ) ); ?></p>
						<p class="plan-price"><?php echo dp_plan_price_html( $p['price'] ); // phpcs:ignore ?></p>
						<p class="plan-time"><?php echo dp_icon( 'clock', 16 ); // phpcs:ignore ?> <?php echo esc_html( dp_t( $p['time'] ) ); ?></p>
						<ul class="plan-list">
							<?php foreach ( (array) $p['features'] as $f ) : ?><li><?php echo dp_icon( 'check', 16 ); // phpcs:ignore ?> <span><?php echo esc_html( dp_t( $f ) ); ?></span></li><?php endforeach; ?>
						</ul>
						<div class="plan-actions">
							<?php if ( $wa ) : ?>
								<a class="btn<?php echo ! empty( $p['featured'] ) ? '' : ' btn-ghost'; ?>" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><?php echo dp_icon( 'whatsapp', 18 ); // phpcs:ignore ?><span><?php echo esc_html( dp_t( array( 'en' => 'Ask on WhatsApp', 'ar' => 'اسأل على واتساب' ) ) ); ?></span></a>
							<?php endif; ?>
							<a class="plan-link" href="#contact"><?php echo esc_html( dp_t( array( 'en' => 'or send the details', 'ar' => 'أو أرسل التفاصيل' ) ) ); ?></a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>

			<div class="agency">
				<div class="agency-text">
					<span class="agency-tag"><?php echo esc_html( dp_t( array( 'en' => 'For agencies', 'ar' => 'للوكالات' ) ) ); ?></span>
					<h3><?php echo esc_html( dp_t( array( 'en' => 'Your WordPress developer, under your name', 'ar' => 'مطوّرة ووردبريس لفريقك، باسم وكالتك' ) ) ); ?></h3>
					<p><?php echo esc_html( dp_t( array( 'en' => 'I have built and maintained sites for agencies in the UAE, Saudi Arabia and Syria for years: from Figma or a brief to a finished site, delivered under your brand, with no contact with your client unless you want it.', 'ar' => 'أبني وأصون المواقع لوكالات في الإمارات والسعودية وسوريا منذ سنوات: من تصميم Figma أو فكرة إلى موقع جاهز، يُسلَّم باسم علامتك، ودون أي تواصل مع عميلك إلا إذا أردت.' ) ) ); ?></p>
					<ul class="agency-list">
						<li><?php echo dp_icon( 'check', 16 ); // phpcs:ignore ?> <?php echo esc_html( dp_t( array( 'en' => 'White-label: your name, your client', 'ar' => 'العمل باسمك: عميلك يبقى عميلك' ) ) ); ?></li>
						<li><?php echo dp_icon( 'check', 16 ); // phpcs:ignore ?> <?php echo esc_html( dp_t( array( 'en' => 'Elementor, custom themes, WooCommerce, fixes', 'ar' => 'Elementor، قوالب خاصة، WooCommerce، إصلاحات' ) ) ); ?></li>
						<li><?php echo dp_icon( 'check', 16 ); // phpcs:ignore ?> <?php echo esc_html( dp_t( array( 'en' => 'Hourly or per project, UTC+3', 'ar' => 'بالساعة أو بالمشروع، بتوقيت UTC+3' ) ) ); ?></li>
					</ul>
				</div>
				<div class="agency-cta">
					<?php if ( '' !== $agency_rate ) : ?>
						<p class="plan-price"><span class="plan-from"><?php echo esc_html( dp_t( array( 'en' => 'from', 'ar' => 'يبدأ من' ) ) ); ?></span> <span class="plan-amount"><?php echo esc_html( dp_plans_currency() . $agency_rate ); ?></span><span class="plan-per"> / <?php echo esc_html( dp_t( array( 'en' => 'hour', 'ar' => 'ساعة' ) ) ); ?></span></p>
					<?php endif; ?>
					<?php $dp_awa = dp_wa_link( dp_t( array( 'en' => 'Hi Doha, I run an agency and would like to work with you. ', 'ar' => 'مرحبا ضحى، عندي وكالة وحابب نشتغل سوا. ' ) ) ); if ( $dp_awa ) : ?>
						<a class="btn" href="<?php echo esc_url( $dp_awa ); ?>" target="_blank" rel="noopener"><?php echo dp_icon( 'whatsapp', 18 ); // phpcs:ignore ?><span><?php echo esc_html( dp_t( array( 'en' => 'Talk on WhatsApp', 'ar' => 'تواصل على واتساب' ) ) ); ?></span></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/* ---------- Customizer: Packages & prices ---------- */
add_action( 'customize_register', function ( $wp ) {
	$wp->add_section( 'dp_plans', array( 'title' => __( 'Packages & prices', 'doha-portfolio' ), 'priority' => 38, 'description' => __( 'Starting prices on the home page. Leave a price empty to show "Ask for a quote". A range like 1200-1500 shows both prices. Texts are bilingual and change with the site language.', 'doha-portfolio' ) ) );
	$wp->add_setting( 'dp_plans_show', array( 'default' => 'yes', 'sanitize_callback' => 'sanitize_key' ) );
	$wp->add_control( 'dp_plans_show', array( 'section' => 'dp_plans', 'type' => 'select', 'label' => __( 'Show the packages section', 'doha-portfolio' ), 'choices' => array( 'yes' => __( 'Yes', 'doha-portfolio' ), 'no' => __( 'No', 'doha-portfolio' ) ) ) );
	$wp->add_setting( 'dp_plans_currency', array( 'default' => '$', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp->add_control( 'dp_plans_currency', array( 'section' => 'dp_plans', 'label' => __( 'Currency sign', 'doha-portfolio' ) ) );
	foreach ( dp_plans_defaults() as $p ) {
		$wp->add_setting( 'dp_plan_' . $p['key'] . '_price', array( 'default' => $p['price'], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'dp_plan_' . $p['key'] . '_price', array( 'section' => 'dp_plans', 'label' => sprintf( __( '%s: starting price', 'doha-portfolio' ), $p['name']['en'] ) ) );
		$wp->add_setting( 'dp_plan_' . $p['key'] . '_time', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp->add_control( 'dp_plan_' . $p['key'] . '_time', array( 'section' => 'dp_plans', 'label' => sprintf( __( '%1$s: delivery time (empty = "%2$s")', 'doha-portfolio' ), $p['name']['en'], $p['time']['en'] ) ) );
	}
	$wp->add_setting( 'dp_agency_rate', array( 'default' => '15', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp->add_control( 'dp_agency_rate', array( 'section' => 'dp_plans', 'label' => __( 'Agencies: hourly rate (empty to hide)', 'doha-portfolio' ) ) );
} );
