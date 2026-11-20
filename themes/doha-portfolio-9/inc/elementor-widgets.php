<?php
/**
 * Elementor widgets for each section. Defaults come from dp_section_defaults(),
 * so every widget shows the CV content out of the box and is fully editable.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

abstract class DP_Widget_Base extends Widget_Base {
	protected $section = '';

	public function get_categories() { return array( 'doha-portfolio' ); }
	/* Output depends on the visitor's language, so Elementor must not cache it. */
	protected function is_dynamic_content(): bool { return true; }
	public function get_keywords() { return array( 'doha', 'portfolio', 'cv', $this->section ); }

	protected function d( $key ) {
		$d = dp_section_defaults( $this->section );
		return isset( $d[ $key ] ) ? $d[ $key ] : '';
	}

	protected function text( $key, $label, $type = Controls_Manager::TEXT ) {
		$this->add_control( $key, array(
			'label'       => $label,
			'type'        => $type,
			'default'     => $this->d( $key ),
			'label_block' => true,
			'dynamic'     => array( 'active' => true ),
		) );
	}

	protected function heading_controls() {
		$this->text( 'eyebrow', __( 'Small label (optional)', 'doha-portfolio' ) );
		$this->text( 'title', __( 'Title', 'doha-portfolio' ), Controls_Manager::TEXTAREA );
	}

	/** Settings with switchers turned into 'yes' / 'no'. */
	protected function args( $switchers = array() ) {
		$s = $this->get_settings_for_display();
		foreach ( $switchers as $k ) { $s[ $k ] = ( isset( $s[ $k ] ) && 'yes' === $s[ $k ] ) ? 'yes' : 'no'; }
		return $s;
	}
}

/* ---------------- Hero ---------------- */
class DP_Widget_Hero extends DP_Widget_Base {
	protected $section = 'hero';
	public function get_name() { return 'dp-hero'; }
	public function get_title() { return __( 'Portfolio Hero', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-header'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Hero', 'doha-portfolio' ) ) );
		$this->text( 'eyebrow', __( 'Small label above the name (optional)', 'doha-portfolio' ) );
		$this->text( 'name', __( 'Name (big title)', 'doha-portfolio' ) );
		$this->text( 'tagline', __( 'Intro', 'doha-portfolio' ), Controls_Manager::TEXTAREA );
		$this->text( 'btn1_text', __( 'Button 1 text', 'doha-portfolio' ) );
		$this->text( 'btn1_url', __( 'Button 1 link', 'doha-portfolio' ) );
		$this->text( 'btn2_text', __( 'Button 2 text', 'doha-portfolio' ) );
		$this->text( 'btn2_url', __( 'Button 2 link', 'doha-portfolio' ) );
		$this->text( 'status', __( 'Availability line (type - to hide)', 'doha-portfolio' ) );
		$this->add_control( 'showcase', array(
			'label'        => __( 'Show 3 project screenshots', 'doha-portfolio' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );
		$this->add_control( 'marquee', array(
			'label'        => __( 'Moving client names strip', 'doha-portfolio' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );
		$this->end_controls_section();
	}

	protected function render() { dp_render_hero( $this->args( array( 'marquee', 'showcase' ) ) ); }
}

/* ---------------- Projects ---------------- */
class DP_Widget_Projects extends DP_Widget_Base {
	protected $section = 'projects';
	public function get_name() { return 'dp-projects'; }
	public function get_title() { return __( 'Projects Grid', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-gallery-grid'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Projects', 'doha-portfolio' ) ) );
		$this->heading_controls();
		$types = array( '' => __( 'Choose a type', 'doha-portfolio' ) );
		$terms = get_terms( array( 'taxonomy' => 'project_type', 'hide_empty' => false ) );
		if ( ! is_wp_error( $terms ) ) { foreach ( $terms as $t ) { $types[ $t->slug ] = $t->name; } }
		$this->add_control( 'source', array(
			'label'   => __( 'Show', 'doha-portfolio' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'featured',
			'options' => array(
				'featured' => __( 'Featured projects (★)', 'doha-portfolio' ),
				'all'      => __( 'All projects', 'doha-portfolio' ),
				'type'     => __( 'One project type', 'doha-portfolio' ),
			),
		) );
		$this->add_control( 'type', array(
			'label'     => __( 'Project type', 'doha-portfolio' ),
			'type'      => Controls_Manager::SELECT,
			'options'   => $types,
			'default'   => '',
			'condition' => array( 'source' => 'type' ),
		) );
		$this->add_control( 'layout', array(
			'label'   => __( 'Layout', 'doha-portfolio' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'showcase',
			'options' => array( 'showcase' => __( 'Featured mosaic (3 big + names)', 'doha-portfolio' ), 'grid' => __( 'One grid', 'doha-portfolio' ), 'company' => __( 'Grouped by company', 'doha-portfolio' ) ),
		) );
		$this->add_control( 'per_company', array( 'label' => __( 'Projects per company', 'doha-portfolio' ), 'type' => Controls_Manager::NUMBER, 'default' => 3, 'min' => 1, 'condition' => array( 'layout' => 'company' ) ) );
		$this->add_control( 'limit', array( 'label' => __( 'Maximum projects (0 = no limit)', 'doha-portfolio' ), 'type' => Controls_Manager::NUMBER, 'default' => 24, 'min' => 0 ) );
		$this->add_control( 'show_filters', array( 'label' => __( 'Filters & search', 'doha-portfolio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->add_control( 'show_more', array( 'label' => __( '"View all" links', 'doha-portfolio' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->text( 'more_text', __( 'Button text (empty = "View all N projects")', 'doha-portfolio' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$a = $this->args( array( 'show_filters', 'show_more' ) );
		$a['limit'] = isset( $a['limit'] ) ? (int) $a['limit'] : 24;
		if ( 0 === $a['limit'] ) { $a['limit'] = -1; }
		dp_render_projects( $a );
	}
}

/* ---------------- Services ---------------- */
class DP_Widget_Services extends DP_Widget_Base {
	protected $section = 'services';
	public function get_name() { return 'dp-services'; }
	public function get_title() { return __( 'Services', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-apps'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Services', 'doha-portfolio' ) ) );
		$this->heading_controls();
		$this->text( 'intro', __( 'Intro', 'doha-portfolio' ), Controls_Manager::TEXTAREA );
		$r = new Repeater();
		$r->add_control( 'icon', array( 'label' => __( 'Icon', 'doha-portfolio' ), 'type' => Controls_Manager::SELECT, 'default' => 'code', 'options' => array( 'code' => 'Code', 'shopping-cart' => 'Store', 'lightning' => 'Speed', 'shield-check' => 'Security', 'arrows-left-right' => 'Migration', 'lifebuoy' => 'Support', 'search' => 'Search' ) ) );
		$r->add_control( 'title', array( 'label' => __( 'Title', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Service', 'label_block' => true ) );
		$r->add_control( 'text', array( 'label' => __( 'Text', 'doha-portfolio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->add_control( 'items', array( 'label' => __( 'Services', 'doha-portfolio' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'default' => $this->d( 'items' ), 'title_field' => '{{{ title }}}' ) );
		$this->end_controls_section();
	}

	protected function render() { dp_render_services( $this->args() ); }
}

/* ---------------- Process ---------------- */
class DP_Widget_Process extends DP_Widget_Base {
	protected $section = 'process';
	public function get_name() { return 'dp-process'; }
	public function get_title() { return __( 'How I Work', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-flow'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Process', 'doha-portfolio' ) ) );
		$this->heading_controls();
		$this->text( 'intro', __( 'Intro', 'doha-portfolio' ), Controls_Manager::TEXTAREA );
		$r = new Repeater();
		$r->add_control( 'title', array( 'label' => __( 'Step', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Step', 'label_block' => true ) );
		$r->add_control( 'text', array( 'label' => __( 'Text', 'doha-portfolio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->add_control( 'items', array( 'label' => __( 'Steps', 'doha-portfolio' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'default' => $this->d( 'items' ), 'title_field' => '{{{ title }}}' ) );
		$this->end_controls_section();
	}

	protected function render() { dp_render_process( $this->args() ); }
}

/* ---------------- Why work with me ---------------- */
class DP_Widget_Why extends DP_Widget_Base {
	protected $section = 'why';
	public function get_name() { return 'dp-why'; }
	public function get_title() { return __( 'Why Work With Me', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-check-circle'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Why work with me', 'doha-portfolio' ) ) );
		$this->add_control( 'note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => 'Leave the list empty to show the default six points (English and Arabic). Add items to replace them.' ) );
		$this->add_control( 'title', array( 'label' => __( 'Title', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$r = new Repeater();
		$r->add_control( 'title', array( 'label' => __( 'Point', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '', 'label_block' => true ) );
		$r->add_control( 'text', array( 'label' => __( 'Text', 'doha-portfolio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->add_control( 'items', array( 'label' => __( 'Points', 'doha-portfolio' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'default' => array(), 'title_field' => '{{{ title }}}' ) );
		$this->end_controls_section();
	}
	protected function render() { $s = $this->get_settings_for_display(); dp_render_why( array( 'title' => $s['title'], 'items' => array_values( array_filter( (array) $s['items'], function ( $i ) { return ! empty( $i['title'] ); } ) ) ) ); }
}

/* ---------------- Packages ---------------- */
class DP_Widget_Plans extends DP_Widget_Base {
	protected $section = 'plans';
	public function get_name() { return 'dp-plans'; }
	public function get_title() { return __( 'Packages & Prices', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-price-table'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Packages', 'doha-portfolio' ) ) );
		$this->add_control( 'note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => 'Prices, delivery times and the agency rate are set in Appearance → Customize → Packages & prices. Texts are bilingual.' ) );
		$this->add_control( 'title', array( 'label' => __( 'Title (optional)', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'intro', array( 'label' => __( 'Intro (optional)', 'doha-portfolio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->end_controls_section();
	}
	protected function render() { $s = $this->get_settings_for_display(); dp_render_plans( array( 'title' => $s['title'], 'intro' => $s['intro'] ) ); }
}

/* ---------------- Problems solved ---------------- */
class DP_Widget_Solved extends DP_Widget_Base {
	protected $section = 'solved';
	public function get_name() { return 'dp-solved'; }
	public function get_title() { return __( 'Problems I Solved', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-check-circle-o'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Problems I solved', 'doha-portfolio' ) ) );
		$this->add_control( 'note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => 'Six real cases, in English and Arabic. Hide the section in Customize → Packages & prices.' ) );
		$this->add_control( 'title', array( 'label' => __( 'Title (optional)', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'intro', array( 'label' => __( 'Intro (optional)', 'doha-portfolio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->end_controls_section();
	}
	protected function render() { $s = $this->get_settings_for_display(); dp_render_solved( array( 'title' => $s['title'], 'intro' => $s['intro'] ) ); }
}

/* ---------------- FAQ ---------------- */
class DP_Widget_Faq extends DP_Widget_Base {
	protected $section = 'faq';
	public function get_name() { return 'dp-faq'; }
	public function get_title() { return __( 'FAQ', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-help-o'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'FAQ', 'doha-portfolio' ) ) );
		$this->add_control( 'note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => 'Leave the list empty to show the default questions (English and Arabic). Add questions to replace them.' ) );
		$this->add_control( 'title', array( 'label' => __( 'Title', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$r = new Repeater();
		$r->add_control( 'q', array( 'label' => __( 'Question', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '', 'label_block' => true ) );
		$r->add_control( 'a', array( 'label' => __( 'Answer', 'doha-portfolio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->add_control( 'items', array( 'label' => __( 'Questions', 'doha-portfolio' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'default' => array(), 'title_field' => '{{{ q }}}' ) );
		$this->end_controls_section();
	}
	protected function render() { $s = $this->get_settings_for_display(); dp_render_faq( array( 'title' => $s['title'], 'items' => array_values( array_filter( (array) $s['items'], function ( $i ) { return ! empty( $i['q'] ); } ) ) ) ); }
}

/* ---------------- Testimonials ---------------- */
class DP_Widget_Testimonials extends DP_Widget_Base {
	protected $section = 'testimonials';
	public function get_name() { return 'dp-testimonials'; }
	public function get_title() { return __( 'Testimonials', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-testimonial'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Testimonials', 'doha-portfolio' ) ) );
		$this->add_control( 'note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => 'This section stays hidden until you add at least one testimonial.' ) );
		$this->add_control( 'title', array( 'label' => __( 'Title', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$r = new Repeater();
		$r->add_control( 'quote', array( 'label' => __( 'Quote', 'doha-portfolio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
		$r->add_control( 'name', array( 'label' => __( 'Name', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$r->add_control( 'role', array( 'label' => __( 'Role and company', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'items', array( 'label' => __( 'Testimonials', 'doha-portfolio' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'default' => array(), 'title_field' => '{{{ name }}}' ) );
		$this->end_controls_section();
	}
	protected function render() { $s = $this->get_settings_for_display(); dp_render_testimonials( array( 'title' => $s['title'], 'items' => $s['items'] ) ); }
}

/* ---------------- Service page ---------------- */
class DP_Widget_Service_Page extends DP_Widget_Base {
	protected $section = 'service';
	public function get_name() { return 'dp-service-page'; }
	public function get_title() { return __( 'Service Page', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-single-page'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Service', 'doha-portfolio' ) ) );
		$opts = array();
		foreach ( dp_service_pages() as $k => $v ) { $opts[ $k ] = $v['title']['en']; }
		$this->add_control( 'service', array( 'label' => __( 'Service', 'doha-portfolio' ), 'type' => Controls_Manager::SELECT, 'default' => key( $opts ), 'options' => $opts ) );
		$this->end_controls_section();
	}
	protected function render() { dp_render_service_page( $this->get_settings_for_display() ); }
}

/* ---------------- Experience ---------------- */
class DP_Widget_Experience extends DP_Widget_Base {
	protected $section = 'experience';
	public function get_name() { return 'dp-experience'; }
	public function get_title() { return __( 'Experience Timeline', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-time-line'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Experience', 'doha-portfolio' ) ) );
		$this->heading_controls();
		$this->text( 'intro', __( 'Intro', 'doha-portfolio' ), Controls_Manager::TEXTAREA );
		$r = new Repeater();
		$r->add_control( 'role', array( 'label' => __( 'Role', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => 'WordPress Developer', 'label_block' => true ) );
		$r->add_control( 'company', array( 'label' => __( 'Company', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Company', 'label_block' => true ) );
		$r->add_control( 'note', array( 'label' => __( 'Note (optional)', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$r->add_control( 'dates', array( 'label' => __( 'Dates', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '2024 - Present' ) );
		$r->add_control( 'location', array( 'label' => __( 'Location', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$r->add_control( 'bullets', array( 'label' => __( 'Points (one per line)', 'doha-portfolio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '', 'rows' => 6 ) );
		$this->add_control( 'items', array(
			'label'       => __( 'Jobs', 'doha-portfolio' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $r->get_controls(),
			'default'     => $this->d( 'items' ),
			'title_field' => '{{{ role }}}, {{{ company }}}',
		) );
		$this->end_controls_section();
	}

	protected function render() { dp_render_experience( $this->args() ); }
}

/* ---------------- Skills ---------------- */
class DP_Widget_Skills extends DP_Widget_Base {
	protected $section = 'skills';
	public function get_name() { return 'dp-skills'; }
	public function get_title() { return __( 'Skills & Education', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-skill-bar'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Skills', 'doha-portfolio' ) ) );
		$this->heading_controls();
		$r = new Repeater();
		$r->add_control( 'title', array( 'label' => __( 'Group', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Group' ) );
		$r->add_control( 'tags', array( 'label' => __( 'Skills (comma separated)', 'doha-portfolio' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->add_control( 'groups', array( 'label' => __( 'Skill groups', 'doha-portfolio' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'default' => $this->d( 'groups' ), 'title_field' => '{{{ title }}}' ) );
		$e = new Repeater();
		$e->add_control( 'degree', array( 'label' => __( 'Degree', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Degree', 'label_block' => true ) );
		$e->add_control( 'details', array( 'label' => __( 'School · years', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '', 'label_block' => true ) );
		$this->add_control( 'education', array( 'label' => __( 'Education', 'doha-portfolio' ), 'type' => Controls_Manager::REPEATER, 'fields' => $e->get_controls(), 'default' => $this->d( 'education' ), 'title_field' => '{{{ degree }}}' ) );
		$this->text( 'languages', __( 'Languages (comma separated)', 'doha-portfolio' ) );
		$this->end_controls_section();
	}

	protected function render() { dp_render_skills( $this->args() ); }
}

/* ---------------- Recognition ---------------- */
class DP_Widget_Recognition extends DP_Widget_Base {
	protected $section = 'recognition';
	public function get_name() { return 'dp-recognition'; }
	public function get_title() { return __( 'Recognition', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-star'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Recognition', 'doha-portfolio' ) ) );
		$this->heading_controls();
		$this->text( 'intro', __( 'Intro', 'doha-portfolio' ), Controls_Manager::TEXTAREA );
		$r = new Repeater();
		$r->add_control( 'image', array( 'label' => __( 'Certificate / award image', 'doha-portfolio' ), 'type' => Controls_Manager::MEDIA ) );
		$r->add_control( 'title', array( 'label' => __( 'Title', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Award', 'label_block' => true ) );
		$r->add_control( 'org', array( 'label' => __( 'Given by', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '', 'label_block' => true ) );
		$r->add_control( 'date', array( 'label' => __( 'Date', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'items', array( 'label' => __( 'Items', 'doha-portfolio' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'default' => $this->d( 'items' ), 'title_field' => '{{{ title }}}' ) );
		$this->text( 'also', __( 'Extra line (type - to hide)', 'doha-portfolio' ) );
		$this->end_controls_section();
	}

	protected function render() { dp_render_recognition( $this->args() ); }
}

/* ---------------- Certificates ---------------- */
class DP_Widget_Certificates extends DP_Widget_Base {
	protected $section = 'certificates';
	public function get_name() { return 'dp-certificates'; }
	public function get_title() { return __( 'Certificates', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-document-file'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Certificates', 'doha-portfolio' ) ) );
		$this->heading_controls();
		$this->text( 'intro', __( 'Intro', 'doha-portfolio' ), Controls_Manager::TEXTAREA );
		$this->add_control( 'visible', array( 'label' => __( 'Show before "Show all" (0 = all)', 'doha-portfolio' ), 'type' => Controls_Manager::NUMBER, 'default' => 8, 'min' => 0 ) );
		$r = new Repeater();
		$r->add_control( 'image', array( 'label' => __( 'Certificate image', 'doha-portfolio' ), 'type' => Controls_Manager::MEDIA ) );
		$r->add_control( 'title', array( 'label' => __( 'Title', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => 'Certificate', 'label_block' => true ) );
		$r->add_control( 'org', array( 'label' => __( 'Issued by', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '', 'label_block' => true ) );
		$r->add_control( 'date', array( 'label' => __( 'Date shown', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$r->add_control( 'sort', array( 'label' => __( 'Date for sorting (YYYY-MM-DD)', 'doha-portfolio' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$r->add_control( 'type', array( 'label' => __( 'Type', 'doha-portfolio' ), 'type' => Controls_Manager::SELECT, 'default' => 'Development', 'options' => array(
			'Degrees & diplomas'  => 'Degrees & diplomas',
			'Development'         => 'Development',
			'Computer skills'     => 'Computer skills',
			'Languages'           => 'Languages',
			'Professional skills' => 'Professional skills',
			'Islamic studies'     => 'Islamic studies',
		) ) );
		$r->add_control( 'thumb', array( 'label' => __( 'Thumbnail URL (optional)', 'doha-portfolio' ), 'type' => Controls_Manager::HIDDEN, 'default' => '' ) );
		$this->add_control( 'items', array( 'label' => __( 'Certificates (sorted by date automatically)', 'doha-portfolio' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'default' => $this->d( 'items' ), 'title_field' => '{{{ sort }}} {{{ title }}}' ) );
		$this->end_controls_section();
	}

	protected function render() { dp_render_certificates( $this->args() ); }
}

/* ---------------- Contact ---------------- */
class DP_Widget_Contact extends DP_Widget_Base {
	protected $section = 'contact';
	public function get_name() { return 'dp-contact'; }
	public function get_title() { return __( 'Contact Block', 'doha-portfolio' ); }
	public function get_icon() { return 'eicon-mail'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Contact', 'doha-portfolio' ) ) );
		$this->heading_controls();
		$this->text( 'text', __( 'Text', 'doha-portfolio' ), Controls_Manager::TEXTAREA );
		$this->text( 'email', __( 'Email', 'doha-portfolio' ) );
		$this->text( 'phone', __( 'Phone', 'doha-portfolio' ) );
		$this->text( 'linkedin', __( 'LinkedIn URL', 'doha-portfolio' ) );
		$this->text( 'location', __( 'Based in', 'doha-portfolio' ) );
		$this->end_controls_section();
	}

	protected function render() { dp_render_contact( $this->args() ); }
}
