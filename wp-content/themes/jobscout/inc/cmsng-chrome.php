<?php
/**
 * Shared site chrome. Keep JobScout's content and page wrapper hooks intact.
 *
 * @package JobScout
 */

function cmsng_setup_chrome() {
    register_nav_menu( 'cmsng_footer', __( 'CMS_NhomG Footer', 'jobscout' ) );
    remove_action( 'jobscout_before_header', 'jobscout_responsive_header', 15 );
    remove_action( 'jobscout_header', 'jobscout_header', 20 );
    add_action( 'jobscout_header', 'cmsng_render_header', 20 );
    remove_action( 'jobscout_footer', 'jobscout_footer_start', 20 );
    remove_action( 'jobscout_footer', 'jobscout_footer_top', 30 );
    remove_action( 'jobscout_footer', 'jobscout_footer_bottom', 40 );
    remove_action( 'jobscout_footer', 'jobscout_footer_end', 50 );
    add_action( 'jobscout_footer', 'cmsng_render_footer', 20 );
}
add_action( 'after_setup_theme', 'cmsng_setup_chrome', 20 );

function cmsng_render_header() {
    get_template_part( 'template-parts/cmsng', 'header' );
}

function cmsng_render_footer() {
    get_template_part( 'template-parts/cmsng', 'footer' );
}

function cmsng_enqueue_chrome() {
    $dir = get_template_directory();
    $uri = get_template_directory_uri();
    wp_enqueue_style( 'cmsng-chrome', $uri . '/css/cmsng-chrome.css', array( 'jobscout' ), filemtime( $dir . '/css/cmsng-chrome.css' ) );
    wp_enqueue_script( 'cmsng-chrome', $uri . '/js/cmsng-chrome.js', array(), filemtime( $dir . '/js/cmsng-chrome.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'cmsng_enqueue_chrome', 20 );

/** Resolve real published pages only; never invent links to absent pages. */
function cmsng_section_url( $section ) {
    if ( 'home' === $section ) {
        return home_url( '/' );
    }
    $page_id = absint( get_theme_mod( 'cmsng_page_' . $section, 0 ) );
    if ( ! $page_id && 'news' === $section ) {
        $page_id = absint( get_option( 'page_for_posts' ) );
    }
    if ( ! $page_id && 'jobs' === $section ) {
        $page_id = absint( get_option( 'job_manager_jobs_page_id' ) );
    }
    if ( ! $page_id && 'submit' === $section ) {
        $page_id = absint( get_option( 'job_manager_submit_job_form_page_id' ) );
    }
    if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
        return get_permalink( $page_id );
    }
    $slugs = array(
        'jobs' => array( 'jobs', 'all-jobs' ),
        'news' => array( 'news', 'blog' ),
        'about' => array( 'about', 'about-us' ),
        'contact' => array( 'contact', 'contact-us' ),
        'companies' => array( 'companies' ),
        'submit' => array( 'submit-job', 'post-a-job', 'post-job' ),
    );
    foreach ( isset( $slugs[ $section ] ) ? $slugs[ $section ] : array() as $slug ) {
        $page = get_page_by_path( $slug );
        if ( $page && 'publish' === $page->post_status ) {
            return get_permalink( $page );
        }
    }
    if ( 'news' === $section && 'posts' === get_option( 'show_on_front' ) ) {
        return home_url( '/' );
    }
    if ( 'jobs' === $section && post_type_exists( 'job_listing' ) ) {
        return get_post_type_archive_link( 'job_listing' );
    }
    return '';
}

function cmsng_submit_job_url() {
    $url = trim( get_theme_mod( 'post_job_url', '' ) );
    return $url && '#' !== substr( $url, 0, 1 ) ? esc_url( $url ) : cmsng_section_url( 'submit' );
}

function cmsng_news_context() {
    return is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_author() || is_date() || is_page_template( 'page-news.php' ) || is_page_template( 'news.php' ) || is_page( 'news' );
}

function cmsng_jobs_context() {
    return is_singular( 'job_listing' ) || is_post_type_archive( 'job_listing' ) || is_tax( array( 'job_listing_category', 'job_listing_type', 'job_listing_region' ) );
}

/** Compare complete destinations, including query strings on plain permalinks. */
function cmsng_same_url( $first, $second ) {
    return $first && $second && untrailingslashit( $first ) === untrailingslashit( $second );
}

/** Give assigned WordPress menus the correct section state on detail pages. */
function cmsng_menu_section_state( $items, $args ) {
    if ( ! in_array( $args->theme_location, array( 'primary', 'cmsng_footer' ), true ) ) {
        return $items;
    }
    $section = cmsng_jobs_context() ? 'jobs' : ( cmsng_news_context() ? 'news' : '' );
    $url = $section ? cmsng_section_url( $section ) : '';
    if ( ! $url ) {
        return $items;
    }
    $active_ids = array();
    $shared_front_url = 'news' === $section && cmsng_same_url( $url, home_url( '/' ) );
    foreach ( $items as $item ) {
        $explicit_section = in_array( 'cmsng-section-' . $section, $item->classes, true );
        // Identical HOME/NEWS URLs need an explicit semantic menu class.
        // Do not guess the section from a translated menu label.
        $matches = $explicit_section || ( ! $shared_front_url && cmsng_same_url( $item->url, $url ) );
        if ( ! is_front_page() && cmsng_same_url( $item->url, home_url( '/' ) ) && ! $matches ) {
            $item->classes = array_diff( $item->classes, array( 'current-menu-item', 'current-menu-parent', 'current-menu-ancestor', 'current_page_item', 'current_page_parent', 'current_page_ancestor' ) );
            $item->current = false;
        }
        if ( $matches ) {
            $item->classes[] = 'cmsng-current-section';
            $active_ids[] = (int) $item->ID;
        }
    }
    // A nested section link also highlights its top-level parent.
    foreach ( array_reverse( $items ) as $item ) {
        if ( in_array( (int) $item->ID, $active_ids, true ) && $item->menu_item_parent ) {
            $active_ids[] = (int) $item->menu_item_parent;
        }
    }
    foreach ( $items as $item ) {
        if ( in_array( (int) $item->ID, $active_ids, true ) ) {
            $item->classes[] = 'cmsng-current-section';
        }
    }
    return $items;
}
add_filter( 'wp_nav_menu_objects', 'cmsng_menu_section_state', 10, 2 );

function cmsng_menu_link_state( $attributes, $item, $args ) {
    if ( in_array( $args->theme_location, array( 'primary', 'cmsng_footer' ), true ) && in_array( 'cmsng-current-section', $item->classes, true ) && empty( $attributes['aria-current'] ) ) {
        $attributes['aria-current'] = 'location';
    }
    return $attributes;
}
add_filter( 'nav_menu_link_attributes', 'cmsng_menu_link_state', 10, 3 );

/** Ordered fallback while menus are unassigned. Missing pages have no fake href. */
function cmsng_menu_fallback( $args ) {
    $footer = 'cmsng_footer' === $args['theme_location'];
    $sections = $footer
        ? array( 'jobs' => 'JOBS', 'companies' => 'COMPANIES', 'news' => 'BLOG', 'about' => 'ABOUT', 'contact' => 'CONTACT' )
        : array( 'home' => 'HOME', 'jobs' => 'JOBS', 'news' => 'NEWS', 'about' => 'ABOUT', 'contact' => 'CONTACT' );
    echo '<ul id="' . esc_attr( $args['menu_id'] ) . '" class="' . esc_attr( $args['menu_class'] ) . '">';
    foreach ( $sections as $section => $label ) {
        $url = cmsng_section_url( $section );
        $section_active = ( 'jobs' === $section && cmsng_jobs_context() ) || ( 'news' === $section && cmsng_news_context() && ! is_front_page() );
        $page_active = ( 'home' === $section && is_front_page() ) || ( is_page() && cmsng_same_url( $url, get_permalink( get_queried_object_id() ) ) );
        $active = $url && ( $section_active || $page_active );
        echo '<li class="menu-item' . ( $active ? ' cmsng-current-section' : '' ) . '">';
        if ( $url ) {
            echo '<a href="' . esc_url( $url ) . '"' . ( $active ? ' aria-current="' . ( $page_active ? 'page' : 'location' ) . '"' : '' ) . '>' . esc_html( $label ) . '</a>';
        } else {
            echo '<span class="cmsng-unconfigured" aria-disabled="true">' . esc_html( $label ) . '</span>';
        }
        echo '</li>';
    }
    echo '</ul>';
}

/** Use the original uploaded logo; do not approximate it with site-title text. */
function cmsng_logo() {
    if ( has_custom_logo() ) {
        the_custom_logo();
    } else {
        echo '<a class="cmsng-logo-missing" href="' . esc_url( home_url( '/' ) ) . '"><span class="screen-reader-text">' . esc_html( get_bloginfo( 'name' ) ) . '</span></a>';
    }
}

function cmsng_customize_chrome( $wp_customize ) {
    $wp_customize->add_section( 'cmsng_chrome', array(
        'title' => __( 'CMS_NhomG — Header & Footer', 'jobscout' ),
        'description' => __( 'Assign Primary and CMS_NhomG Footer menus in Menus. Set the original logo in Site Identity. Empty social URLs are displayed as inactive icons. Newsletter storage/delivery is not configured.', 'jobscout' ),
        'priority' => 35,
    ) );
    $wp_customize->add_setting( 'cmsng_recruiting', array( 'default' => 'RECRUITING', 'sanitize_callback' => 'sanitize_text_field' ) );
    $wp_customize->add_control( 'cmsng_recruiting', array( 'section' => 'cmsng_chrome', 'label' => __( 'Text below header logo (clear if included in logo)', 'jobscout' ), 'type' => 'text' ) );
    foreach ( array( 'jobs' => 'JOBS', 'news' => 'NEWS / BLOG', 'about' => 'ABOUT', 'contact' => 'CONTACT', 'companies' => 'COMPANIES', 'submit' => 'SUBMIT JOB' ) as $key => $label ) {
        $name = 'cmsng_page_' . $key;
        $wp_customize->add_setting( $name, array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
        $wp_customize->add_control( $name, array( 'section' => 'cmsng_chrome', 'label' => $label . ' ' . __( 'page', 'jobscout' ), 'type' => 'dropdown-pages' ) );
    }
    foreach ( array( 'facebook' => 'Facebook', 'google' => 'Google', 'line' => 'LINE', 'twitter' => 'Twitter' ) as $key => $label ) {
        $name = 'cmsng_social_' . $key;
        $wp_customize->add_setting( $name, array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
        $wp_customize->add_control( $name, array( 'section' => 'cmsng_chrome', 'label' => $label . ' URL', 'type' => 'url' ) );
    }
    // Existing settings remain the single source for the CTA and copyright.
    foreach ( array( 'post_job_label', 'footer_copyright' ) as $name ) {
        $setting = $wp_customize->get_setting( $name );
        if ( $setting ) {
            $setting->transport = 'refresh';
            if ( 'post_job_label' === $name ) {
                $setting->default = 'SUBMIT JOB';
            }
        }
    }
    $control = $wp_customize->get_control( 'post_job_label' );
    if ( $control ) {
        $control->description = __( 'Leave empty to use SUBMIT JOB.', 'jobscout' );
    }
    if ( isset( $wp_customize->selective_refresh ) ) {
        $wp_customize->selective_refresh->remove_partial( 'post_job_label' );
        $wp_customize->selective_refresh->remove_partial( 'footer_copyright' );
    }
}
add_action( 'customize_register', 'cmsng_customize_chrome', 30 );
