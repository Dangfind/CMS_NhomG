<?php

/**
 * NhomG - About Us page (middle content area only).
 *
 * Hero, "About Us" columns and "Company Info" facts are content of the page,
 * rendered between the shared header and footer. Values are stored in the
 * Customizer (real, editable content). Company facts that describe the site
 * itself are read from the live database where possible (published jobs and
 * posts already in the CMS), never invented at render time.
 *
 * @package JobScout
 */

if (! defined('ABSPATH')) exit;

/*--------------------------------------------------------------
# Content defaults (editable in Customize > About Page)
--------------------------------------------------------------*/

function nhomg_about_defaults()
{
    return array(
        // Hero banner
        'nhomg_about_title'        => __("SHARE \"OMOTENASHI\"\nWITH THE WORLD", 'jobscout'),
        // About Us section
        'nhomg_about_heading'      => __('ABOUT US', 'jobscout'),
        'nhomg_about_vision_title' => __('Our Vision', 'jobscout'),
        'nhomg_about_vision_text'  => __('Create hotels and restaurants around the world that offer memorable experiences while building a lasting, positive relationship together with our guests, partners, team members and communities.', 'jobscout'),
        'nhomg_about_mission_title' => __('Ours Mission', 'jobscout'),
        'nhomg_about_mission_text' => __('Share "Omotenashi" with the world', 'jobscout'),
        'nhomg_about_value_title'  => __('Our Core Value', 'jobscout'),
        'nhomg_about_value_text'   => __("\"If I were the guest...\"\nTo provide guests with the hospitality you would want to receive as a guest.", 'jobscout'),
        // Company info section
        'nhomg_about_info_heading' => __('Company Info', 'jobscout'),
        'nhomg_about_established'  => __('April 1993', 'jobscout'),
        'nhomg_about_head_office'  => __('Marunouchi 2-1-1, Chiyoda, Tokyo', 'jobscout'),
        'nhomg_about_capital'      => __('100,000,000 JPY', 'jobscout'),
        'nhomg_about_ceo'          => __('Yutaka Noda', 'jobscout'),
        'nhomg_about_employees'    => __('Full time: 830 / Total: 1,600', 'jobscout'),
        // "What we do" block: orange heading + paragraphs.
        'nhomg_about_what_heading' => __('Hotels, restaurants, banquets/weddings management', 'jobscout'),
        'nhomg_about_what_text'    => __('We are experts in all stages of project development: concept, design, implementation and management. Our team of professionals seeks to create spaces and experiences that surprise and delight guests, and to understand local cultures like natives.', 'jobscout'),
        'nhomg_about_what_text2'   => __('We love to find unique ways to deliver unforgettable and bespoke experiences, providing service that is warm but never intrusive, and foreseeing our guests\' every need at all times.', 'jobscout'),
        // Bundled reference images (Customizer can override each).
        'nhomg_about_banner_image' => '',
        'nhomg_about_intro_image'  => '',
        'nhomg_about_info_image'   => '',
    );
}

/**
 * Resolve a bundled About image: Customizer override first, then the shipped
 * file in the theme's images/ folder (extension-agnostic). Returns '' when no
 * asset exists so the caller can skip the <img> instead of showing a broken one.
 */
function nhomg_about_image($key)
{
    $override = esc_url_raw(get_theme_mod('nhomg_about_' . $key . '_image', ''));
    if ($override) {
        return $override;
    }
    $files = array(
        'banner' => 'about_us_a1',
        'intro'  => 'about_us_a2',
        'info'   => 'about_us_a3',
    );
    if (! isset($files[$key])) {
        return '';
    }
    foreach (array('jpg', 'jpeg', 'png', 'webp') as $extension) {
        $file = '/images/' . $files[$key] . '.' . $extension;
        if (is_file(get_template_directory() . $file)) {
            return get_template_directory_uri() . $file;
        }
    }
    return '';
}

/** Read an About page value; returns '' when the administrator left it empty. */
function nhomg_about_mod($key)
{
    $defaults = nhomg_about_defaults();
    return get_theme_mod($key, isset($defaults[$key]) ? $defaults[$key] : '');
}

/** Company facts in the order shown by the design. Empty rows are not printed. */
function nhomg_about_facts()
{
    $rows = array(
        'nhomg_about_established' => __('Established since', 'jobscout'),
        'nhomg_about_head_office' => __('Head Office', 'jobscout'),
        'nhomg_about_capital'     => __('Capital', 'jobscout'),
        'nhomg_about_ceo'         => __('CEO', 'jobscout'),
        'nhomg_about_employees'   => __('Number of Employees', 'jobscout'),
    );
    $facts = array();
    foreach ($rows as $key => $label) {
        $value = trim((string) nhomg_about_mod($key));
        if ('' !== $value) {
            $facts[] = array('label' => $label, 'value' => $value);
        }
    }
    return $facts;
}

/*--------------------------------------------------------------
# Live data from the CMS (no fake numbers)
--------------------------------------------------------------*/

/**
 * Published jobs and the distinct companies they belong to. Counts come from
 * the real database, so the About page reflects current openings.
 */
function nhomg_about_stats()
{
    if (! post_type_exists('job_listing')) {
        return array('jobs' => 0, 'companies' => 0);
    }
    $ids = get_posts(array(
        'post_type'        => 'job_listing',
        'post_status'      => 'publish',
        'has_password'     => false,
        'posts_per_page'   => -1,
        'fields'           => 'ids',
        'no_found_rows'    => true,
        'suppress_filters' => false,
    ));
    $companies = array();
    foreach ($ids as $id) {
        $name = trim((string) get_post_meta($id, '_company_name', true));
        if ($name) $companies[$name] = true;
    }
    return array('jobs' => count($ids), 'companies' => count($companies));
}

/** Banner image: page featured image first, then the shipped banner asset. */
function nhomg_about_hero_image()
{
    if (has_post_thumbnail()) {
        return get_the_post_thumbnail_url(null, 'full');
    }
    return nhomg_about_image('banner');
}

/*--------------------------------------------------------------
# Front-end assets & body class
--------------------------------------------------------------*/

function nhomg_about_is_page()
{
    return is_page_template('templates/about.php');
}

function nhomg_about_enqueue()
{
    if (! nhomg_about_is_page()) return;
    $dir = get_template_directory();
    $uri = get_template_directory_uri();
    // Shared palette (:root variables) and font live in the job/contact bundle.
    wp_enqueue_style('nhomg-font', 'https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap', array(), null);
    wp_enqueue_style('nhomg-job-contact', $uri . '/css/nhomg-job-contact.css', array('nhomg-font'), NHOMG_JOBS_VERSION);
    $file = '/css/nhomg-about.css';
    $ver  = file_exists($dir . $file) ? filemtime($dir . $file) : NHOMG_JOBS_VERSION;
    wp_enqueue_style('nhomg-about', $uri . $file, array('nhomg-job-contact'), $ver);
}
add_action('wp_enqueue_scripts', 'nhomg_about_enqueue', 25);

/** About uses its own full-width layout: drop the theme sidebar classes. */
function nhomg_about_body_class($classes)
{
    if (! nhomg_about_is_page()) return $classes;
    $classes   = array_diff($classes, array('rightsidebar', 'leftsidebar'));
    $classes[] = 'full-width';
    $classes[] = 'nhomg-about-page';
    return $classes;
}
add_filter('body_class', 'nhomg_about_body_class', 99);

/*--------------------------------------------------------------
# Customizer
--------------------------------------------------------------*/

function nhomg_about_customize_register($wp_customize)
{
    $wp_customize->add_section('nhomg_about', array(
        'title'       => __('About Page', 'jobscout'),
        'description' => __('Content of the page using the "About Us" template. The banner uses the page\'s featured image. Leave a field empty to hide its row.', 'jobscout'),
        'priority'    => 161,
    ));

    $labels = array(
        'nhomg_about_title'         => __('Banner title', 'jobscout'),
        'nhomg_about_heading'       => __('About Us heading', 'jobscout'),
        'nhomg_about_vision_title'  => __('Vision title', 'jobscout'),
        'nhomg_about_vision_text'   => __('Vision text', 'jobscout'),
        'nhomg_about_mission_title' => __('Mission title', 'jobscout'),
        'nhomg_about_mission_text'  => __('Mission text', 'jobscout'),
        'nhomg_about_value_title'   => __('Core Value title', 'jobscout'),
        'nhomg_about_value_text'    => __('Core Value text', 'jobscout'),
        'nhomg_about_info_heading'  => __('Company Info heading', 'jobscout'),
        'nhomg_about_established'   => __('Established since', 'jobscout'),
        'nhomg_about_head_office'   => __('Head Office', 'jobscout'),
        'nhomg_about_capital'       => __('Capital', 'jobscout'),
        'nhomg_about_ceo'           => __('CEO', 'jobscout'),
        'nhomg_about_employees'     => __('Number of Employees', 'jobscout'),
        'nhomg_about_what_heading'  => __('"What we do" heading', 'jobscout'),
        'nhomg_about_what_text'     => __('"What we do" paragraph 1', 'jobscout'),
        'nhomg_about_what_text2'    => __('"What we do" paragraph 2', 'jobscout'),
    );

    $textarea = array(
        'nhomg_about_vision_text',
        'nhomg_about_mission_text',
        'nhomg_about_value_text',
        'nhomg_about_what_text',
        'nhomg_about_what_text2',
    );

    $images = array(
        'nhomg_about_banner_image' => __('Banner image', 'jobscout'),
        'nhomg_about_intro_image'  => __('About Us image', 'jobscout'),
        'nhomg_about_info_image'   => __('Company Info image', 'jobscout'),
    );

    foreach (nhomg_about_defaults() as $key => $default) {
        if (isset($images[$key])) {
            $wp_customize->add_setting($key, array(
                'default'           => '',
                'sanitize_callback' => 'esc_url_raw',
            ));
            $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, $key, array(
                'label'   => $images[$key],
                'section' => 'nhomg_about',
            )));
            continue;
        }
        $is_textarea = in_array($key, $textarea, true);
        $wp_customize->add_setting($key, array(
            'default'           => $default,
            'sanitize_callback' => $is_textarea ? 'sanitize_textarea_field' : 'sanitize_text_field',
        ));
        $wp_customize->add_control($key, array(
            'label'   => $labels[$key],
            'section' => 'nhomg_about',
            'type'    => $is_textarea ? 'textarea' : 'text',
        ));
    }
}
add_action('customize_register', 'nhomg_about_customize_register');
