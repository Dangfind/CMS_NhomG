<?php

/**
 * JobScout functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package JobScout
 */

$jobscout_theme_data = wp_get_theme();
if (!defined('JOBSCOUT_THEME_VERSION')) define('JOBSCOUT_THEME_VERSION', $jobscout_theme_data->get('Version'));
if (!defined('JOBSCOUT_THEME_NAME')) define('JOBSCOUT_THEME_NAME', $jobscout_theme_data->get('Name'));

/**
 * Implement Local Font Method functions.
 */
require get_template_directory() . '/inc/class-webfont-loader.php';

/**
 * Custom Functions.
 */
require get_template_directory() . '/inc/custom-functions.php';

/**
 * Standalone Functions.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Template Functions.
 */
require get_template_directory() . '/inc/template-functions.php';

/** Shared CMS_NhomG header, footer, navigation and settings. */
require get_template_directory() . '/inc/cmsng-chrome.php';

/** Home content only; shared header/footer remain unchanged. */
require get_template_directory() . '/sections/home/functions.php';

/**
 * Custom functions for selective refresh.
 */
require get_template_directory() . '/inc/partials.php';

if (jobscout_is_rara_theme_companion_activated()) :
	/**
	 * Modify filter hooks of RTC plugin.
	 */
	require get_template_directory() . '/inc/rtc-filters.php';
endif;

/**
 * Custom Controls
 */
require get_template_directory() . '/inc/custom-controls/custom-control.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer/customizer.php';

/**
 * Widgets
 */
require get_template_directory() . '/inc/widgets.php';

/**
 * Metabox
 */
require get_template_directory() . '/inc/metabox.php';

/**
 * Getting Started
 */
require get_template_directory() . '/inc/dashboard/dashboard.php';

/**
 * Plugin Recommendation
 */
require get_template_directory() . '/inc/tgmpa/recommended-plugins.php';

/**
 * Add theme compatibility function for woocommerce if active
 */
if (jobscout_is_woocommerce_activated()) {
	require get_template_directory() . '/inc/woocommerce-functions.php';
}

/**
 * Modify filter hooks of WP Job Manager plugin.
 */
if (jobscout_is_wp_job_manager_activated()) :
	require get_template_directory() . '/inc/wp-job-manager-filters.php';
endif;

/**
 * NhomG - Job Detail & Contact page.
 */
require get_template_directory() . '/inc/jobs.php';

/**
 * NhomG - About Us page (middle content area only).
 */
require get_template_directory() . '/inc/about.php';

/**
 * NhomG - News Detail page module.
 */
require get_template_directory() . '/news-detail/news-detail.php';

function nhomg_is_news_page()
{
	return is_page_template('page-news.php') || is_page_template('news.php') || is_page('news');
}

add_action('wp_enqueue_scripts', function () {
	if (nhomg_is_news_page()) {
		$css_file = get_template_directory() . '/news.css';
		$ver = file_exists($css_file) ? filemtime($css_file) : '1.0';
		wp_enqueue_style(
			'news-page',
			get_template_directory_uri() . '/news.css',
			array('jobscout'),
			$ver
		);
	}
});

add_filter('body_class', function ($classes) {
	if (nhomg_is_news_page()) {
		$classes   = array_diff($classes, array('rightsidebar', 'leftsidebar'));
		$classes[] = 'full-width';
		$classes[] = 'nhomg-news-page';
	}
	return $classes;
});

add_action('widgets_init', function () {
	register_sidebar([
		'name'          => 'Trang chá»§ - Career With Us',
		'id'            => 'nhomg-career',
		'description'   => 'Chá»‰nh sá»­a áº£nh ná»n vÃ  ná»™i dung Career With Us.',
		'before_widget' => '<div class="nhomg-career-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2>',
		'after_title'   => '</h2>',
	]);
});
// ÄÄƒng kÃ½ Widget area cho Jobs Hero Banner
function jobscout_register_custom_banner_widget()
{
	register_sidebar(array(
		'name'          => __('Jobs Hero Banner Widget', 'jobscout'),
		'id'            => 'jobs-hero-banner-widget',
		'description'   => __('Khu vá»±c hiá»ƒn thá»‹ banner Career With Us (DÃ¹ng Custom HTML Widget)', 'jobscout'),
		'before_widget' => '<div class="custom-hero-banner-wrapper">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="widget-title" style="display:none;">',
		'after_title'   => '</h2>',
	));
}
add_action('widgets_init', 'jobscout_register_custom_banner_widget');

/**
 * NhomG - News Page Customizer Settings.
 */
function nhomg_news_defaults()
{
	return array(
		'nhomg_news_banner_title'   => 'FDS NEWS',
		'news_section_title'        => 'NEWEST BLOG ENTRIES',
		'nhomg_news_posts_per_page' => '8',
		'nhomg_news_readmore'       => 'Read More',
		'nhomg_news_banner_image'   => '',
	);
}

function nhomg_news_mod($key)
{
	$defaults = nhomg_news_defaults();
	return get_theme_mod($key, isset($defaults[$key]) ? $defaults[$key] : '');
}

function nhomg_news_customize_register($wp_customize)
{
	// Tao muc News Page trong Customizer (priority 162 nam lien ke Contact Page 160 va About Page 161)
	$wp_customize->add_section('nhomg_news', array(
		'title'       => __('News Page', 'jobscout'),
		'description' => __('Content of pages using the "News" template.', 'jobscout'),
		'priority'    => 162,
	));

	$labels = array(
		'nhomg_news_banner_title'   => __('Banner title', 'jobscout'),
		'news_section_title'        => __('News Section Title', 'jobscout'),
		'nhomg_news_posts_per_page' => __('Posts per page', 'jobscout'),
		'nhomg_news_readmore'       => __('Read More button text', 'jobscout'),
	);

	foreach (nhomg_news_defaults() as $key => $default) {
		if ('nhomg_news_banner_image' === $key) {
			$wp_customize->add_setting($key, array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			));
			$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, $key, array(
				'label'   => __('Banner image', 'jobscout'),
				'section' => 'nhomg_news',
			)));
			continue;
		}

		$wp_customize->add_setting($key, array(
			'default'           => $default,
			'sanitize_callback' => 'sanitize_text_field',
		));

		$wp_customize->add_control($key, array(
			'label'   => isset($labels[$key]) ? $labels[$key] : $key,
			'section' => 'nhomg_news',
			'type'    => ('nhomg_news_posts_per_page' === $key) ? 'number' : 'text',
		));
	}
}
add_action('customize_register', 'nhomg_news_customize_register');
