<?php
/**
 * NhomG - Job Detail & Contact page (phần 5)
 *
 * - Dùng post type `job_listing` (tương thích WP Job Manager). Nếu plugin chưa
 *   được cài thì theme tự đăng ký post type + taxonomy để vẫn hoạt động.
 * - Meta box nhập các phần của trang chi tiết việc làm.
 * - Customizer cho nội dung trang Contact.
 *
 * @package JobScout
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'NHOMG_JOBS_VERSION', '1.0.1' );

/*--------------------------------------------------------------
# Post type & taxonomy (fallback khi chưa có WP Job Manager)
--------------------------------------------------------------*/
function nhomg_register_job_post_type() {
    if ( ! post_type_exists( 'job_listing' ) ) {
        register_post_type( 'job_listing', array(
            'labels' => array(
                'name'          => __( 'Jobs', 'jobscout' ),
                'singular_name' => __( 'Job', 'jobscout' ),
                'add_new_item'  => __( 'Add New Job', 'jobscout' ),
                'edit_item'     => __( 'Edit Job', 'jobscout' ),
                'all_items'     => __( 'All Jobs', 'jobscout' ),
                'menu_name'     => __( 'Jobs', 'jobscout' ),
            ),
            'public'       => true,
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-businessman',
            'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
            'has_archive'  => 'jobs',
            'rewrite'      => array( 'slug' => 'job', 'with_front' => false ),
        ) );
    }

    if ( ! taxonomy_exists( 'job_listing_type' ) ) {
        register_taxonomy( 'job_listing_type', 'job_listing', array(
            'labels'            => array(
                'name'          => __( 'Job Types', 'jobscout' ),
                'singular_name' => __( 'Job Type', 'jobscout' ),
            ),
            'hierarchical'      => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'job-type' ),
        ) );
    }

    if ( ! taxonomy_exists( 'job_listing_category' ) ) {
        register_taxonomy( 'job_listing_category', 'job_listing', array(
            'labels'            => array(
                'name'          => __( 'Job Categories', 'jobscout' ),
                'singular_name' => __( 'Job Category', 'jobscout' ),
            ),
            'hierarchical'      => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'job-category' ),
        ) );
    }

    // Flush rewrite rules một lần sau khi đăng ký để link /job/... hoạt động.
    if ( get_option( 'nhomg_jobs_rewrite_version' ) !== NHOMG_JOBS_VERSION ) {
        flush_rewrite_rules( false );
        update_option( 'nhomg_jobs_rewrite_version', NHOMG_JOBS_VERSION );
    }
}
add_action( 'init', 'nhomg_register_job_post_type', 99 );

/*--------------------------------------------------------------
# Helpers
--------------------------------------------------------------*/

/**
 * Các field meta của một việc làm. Field có `wpjm => true` đã có sẵn trong
 * WP Job Manager nên chỉ hiển thị trong meta box khi plugin chưa cài.
 */
function nhomg_job_fields() {
    return array(
        '_company_name'          => array( 'label' => __( 'Company name', 'jobscout' ), 'type' => 'text', 'wpjm' => true ),
        '_job_location'          => array( 'label' => __( 'Location (short, e.g. Ho Chi Minh City)', 'jobscout' ), 'type' => 'text', 'wpjm' => true ),
        '_application'           => array( 'label' => __( 'Application email or URL', 'jobscout' ), 'type' => 'text', 'wpjm' => true ),
        '_company_website'       => array( 'label' => __( 'Company website', 'jobscout' ), 'type' => 'url', 'wpjm' => true ),
        '_nhomg_overview'        => array( 'label' => __( 'Overview about Company', 'jobscout' ), 'type' => 'editor' ),
        '_nhomg_key_skills'      => array( 'label' => __( 'Our Key Skills', 'jobscout' ), 'type' => 'editor' ),
        '_nhomg_why_love'        => array( 'label' => __( 'Why You\'ll Love Working Here', 'jobscout' ), 'type' => 'editor' ),
        '_nhomg_location_detail' => array( 'label' => __( 'Location (detail)', 'jobscout' ), 'type' => 'editor' ),
        '_nhomg_highlights'      => array( 'label' => __( 'Highlights (one per line, shown on job cards)', 'jobscout' ), 'type' => 'textarea' ),
        '_nhomg_rating'          => array( 'label' => __( 'Staff rating (0 - 5)', 'jobscout' ), 'type' => 'number' ),
        '_nhomg_photos'          => array( 'label' => __( 'Company photos', 'jobscout' ), 'type' => 'gallery' ),
    );
}

/** Link trang "All Jobs" (phần 4). Ưu tiên page của nhóm, sau đó tới archive. */
function nhomg_all_jobs_url() {
    $url = '';
    foreach ( array( 'all-jobs', 'jobs' ) as $slug ) {
        $page = get_page_by_path( $slug );
        if ( $page && 'publish' === $page->post_status ) {
            $url = get_permalink( $page );
            break;
        }
    }
    if ( ! $url && ( $page_id = (int) get_option( 'job_manager_jobs_page_id' ) ) ) {
        $url = get_permalink( $page_id );
    }
    if ( ! $url ) {
        $url = get_post_type_archive_link( 'job_listing' );
    }
    return apply_filters( 'nhomg_all_jobs_url', $url );
}

/** Link trang Contact (page dùng template Contact Us). */
function nhomg_contact_url() {
    $pages = get_pages( array(
        'meta_key'   => '_wp_page_template',
        'meta_value' => 'templates/contact.php',
        'number'     => 1,
    ) );
    if ( $pages ) return get_permalink( $pages[0] );

    $page = get_page_by_path( 'contact' );
    return $page ? get_permalink( $page ) : home_url( '/' );
}

/** Link ứng tuyển: email -> mailto, URL -> URL, không có -> trang Contact. */
function nhomg_job_apply_url( $post_id = null ) {
    $post_id     = $post_id ? $post_id : get_the_ID();
    $application = trim( (string) get_post_meta( $post_id, '_application', true ) );

    if ( $application && is_email( $application ) ) {
        /* translators: %s: job title */
        $subject = sprintf( __( 'Apply for: %s', 'jobscout' ), get_the_title( $post_id ) );
        return 'mailto:' . antispambot( $application ) . '?subject=' . rawurlencode( $subject );
    }
    if ( $application && filter_var( $application, FILTER_VALIDATE_URL ) ) {
        return $application;
    }
    return nhomg_contact_url();
}

/** Các tag hiển thị dưới tiêu đề: loại việc, danh mục, địa điểm. */
function nhomg_job_tags( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $tags    = array();

    foreach ( array( 'job_listing_type', 'job_listing_category' ) as $tax ) {
        if ( ! taxonomy_exists( $tax ) ) continue;
        $terms = get_the_terms( $post_id, $tax );
        if ( $terms && ! is_wp_error( $terms ) ) {
            $tags[] = $terms[0]->name;
        }
    }

    $location = get_post_meta( $post_id, '_job_location', true );
    if ( $location ) $tags[] = $location;

    return $tags;
}

/** Danh sách "highlights" cho thẻ việc làm, fallback về excerpt. */
function nhomg_job_highlights( $post_id = null, $limit = 3 ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $raw     = (string) get_post_meta( $post_id, '_nhomg_highlights', true );
    $items   = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) );

    if ( ! $items && has_excerpt( $post_id ) ) {
        $items = array( get_the_excerpt( $post_id ) );
    }
    return array_slice( $items, 0, $limit );
}

/** Danh sách ID ảnh công ty. */
function nhomg_job_photo_ids( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $ids     = array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, '_nhomg_photos', true ) ) );
    return array_values( array_filter( $ids, 'wp_attachment_is_image' ) );
}

/** In logo công ty (ảnh đại diện của job), fallback là tên công ty. */
function nhomg_job_logo( $post_id = null ) {
    $post_id = $post_id ? $post_id : get_the_ID();
    echo '<figure class="nhomg-job-logo">';
    if ( has_post_thumbnail( $post_id ) ) {
        echo get_the_post_thumbnail( $post_id, 'medium', array( 'alt' => esc_attr( get_post_meta( $post_id, '_company_name', true ) ) ) );
    } else {
        $name = get_post_meta( $post_id, '_company_name', true );
        echo '<span class="nhomg-job-logo__text">' . esc_html( $name ? $name : get_the_title( $post_id ) ) . '</span>';
    }
    echo '</figure>';
}

/** In 5 ngôi sao theo điểm (hỗ trợ nửa sao). */
function nhomg_rating_stars( $rating ) {
    $rating = max( 0, min( 5, (float) $rating ) );
    $star   = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.5L12 17.3l-5.9 3.2 1.3-6.5L2.5 9.4l6.6-.8z"/></svg>';

    echo '<span class="nhomg-stars" role="img" aria-label="' . esc_attr( sprintf( __( 'Rated %s out of 5', 'jobscout' ), number_format_i18n( $rating, 1 ) ) ) . '">';
    for ( $i = 1; $i <= 5; $i++ ) {
        $fill = max( 0, min( 1, $rating - ( $i - 1 ) ) );
        $fill = round( $fill * 2 ) / 2; // làm tròn tới nửa sao
        echo '<span class="nhomg-star" style="--fill:' . esc_attr( $fill * 100 ) . '%">' . $star . '<span class="nhomg-star__fill">' . $star . '</span></span>'; // phpcs:ignore
    }
    echo '</span>';
}

/** Lấy các việc làm liên quan: cùng danh mục trước, thiếu thì bù việc mới nhất. */
function nhomg_related_jobs( $post_id, $count = 6 ) {
    $base = array(
        'post_type'           => 'job_listing',
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );
    $ids  = array();

    if ( taxonomy_exists( 'job_listing_category' ) ) {
        $terms = wp_get_post_terms( $post_id, 'job_listing_category', array( 'fields' => 'ids' ) );
        if ( $terms && ! is_wp_error( $terms ) ) {
            $ids = get_posts( $base + array(
                'fields'         => 'ids',
                'posts_per_page' => $count,
                'post__not_in'   => array( $post_id ),
                'tax_query'      => array( array( 'taxonomy' => 'job_listing_category', 'terms' => $terms ) ),
            ) );
        }
    }

    if ( count( $ids ) < $count ) {
        $ids = array_merge( $ids, get_posts( $base + array(
            'fields'         => 'ids',
            'posts_per_page' => $count - count( $ids ),
            'post__not_in'   => array_merge( array( $post_id ), $ids ),
        ) ) );
    }

    if ( ! $ids ) return new WP_Query( array( 'post__in' => array( 0 ) ) );

    return new WP_Query( $base + array(
        'post__in'       => $ids,
        'orderby'        => 'post__in',
        'posts_per_page' => count( $ids ),
    ) );
}

/*--------------------------------------------------------------
# Meta box
--------------------------------------------------------------*/
function nhomg_add_job_metabox() {
    add_meta_box( 'nhomg-job-detail', __( 'Job Detail (NhomG)', 'jobscout' ), 'nhomg_render_job_metabox', 'job_listing', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'nhomg_add_job_metabox' );

function nhomg_render_job_metabox( $post ) {
    wp_nonce_field( 'nhomg_save_job', 'nhomg_job_nonce' );
    $wpjm = jobscout_is_wp_job_manager_activated();

    echo '<div class="nhomg-metabox">';
    foreach ( nhomg_job_fields() as $key => $field ) {
        if ( $wpjm && ! empty( $field['wpjm'] ) ) continue;

        $value = get_post_meta( $post->ID, $key, true );
        $id    = 'nhomg' . $key;
        echo '<p><label for="' . esc_attr( $id ) . '"><strong>' . esc_html( $field['label'] ) . '</strong></label></p>';

        switch ( $field['type'] ) {
            case 'editor':
                wp_editor( $value, $id, array( 'textarea_name' => $key, 'textarea_rows' => 6, 'media_buttons' => false ) );
                break;
            case 'textarea':
                echo '<textarea class="widefat" rows="4" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '">' . esc_textarea( $value ) . '</textarea>';
                break;
            case 'number':
                echo '<input type="number" min="0" max="5" step="0.1" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
                break;
            case 'gallery':
                echo '<input type="hidden" class="nhomg-gallery-input" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
                echo '<span class="nhomg-gallery-preview">';
                foreach ( nhomg_job_photo_ids( $post->ID ) as $img_id ) {
                    echo wp_get_attachment_image( $img_id, array( 60, 60 ) );
                }
                echo '</span><br /><button type="button" class="button nhomg-gallery-select">' . esc_html__( 'Select photos', 'jobscout' ) . '</button> ';
                echo '<button type="button" class="button-link nhomg-gallery-clear">' . esc_html__( 'Clear', 'jobscout' ) . '</button>';
                break;
            default:
                echo '<input type="' . esc_attr( $field['type'] ) . '" class="widefat" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
        }
    }
    echo '<p class="description">' . esc_html__( 'Company logo = Featured image. Job type / category are set in the taxonomy boxes.', 'jobscout' ) . '</p>';
    echo '</div>';
}

function nhomg_save_job_metabox( $post_id ) {
    if ( ! isset( $_POST['nhomg_job_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nhomg_job_nonce'] ), 'nhomg_save_job' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $wpjm = jobscout_is_wp_job_manager_activated();
    foreach ( nhomg_job_fields() as $key => $field ) {
        if ( ( $wpjm && ! empty( $field['wpjm'] ) ) || ! isset( $_POST[ $key ] ) ) continue;
        $raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

        switch ( $field['type'] ) {
            case 'editor':   $value = wp_kses_post( $raw ); break;
            case 'textarea': $value = sanitize_textarea_field( $raw ); break;
            case 'number':   $value = '' === $raw ? '' : (string) max( 0, min( 5, (float) $raw ) ); break;
            case 'url':      $value = esc_url_raw( $raw ); break;
            case 'gallery':  $value = implode( ',', array_filter( array_map( 'absint', explode( ',', $raw ) ) ) ); break;
            default:         $value = sanitize_text_field( $raw );
        }
        update_post_meta( $post_id, $key, $value );
    }
}
add_action( 'save_post_job_listing', 'nhomg_save_job_metabox' );

function nhomg_admin_scripts( $hook ) {
    $screen = get_current_screen();
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! $screen || 'job_listing' !== $screen->post_type ) return;
    wp_enqueue_media();
    wp_enqueue_script( 'nhomg-admin-job', get_template_directory_uri() . '/js/nhomg-admin-job.js', array( 'jquery' ), NHOMG_JOBS_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'nhomg_admin_scripts' );

/*--------------------------------------------------------------
# Front-end assets & body class
--------------------------------------------------------------*/
function nhomg_is_part5_page() {
    return is_singular( 'job_listing' ) || is_page_template( 'templates/contact.php' );
}

function nhomg_enqueue_scripts() {
    if ( ! nhomg_is_part5_page() ) return;
    wp_enqueue_style( 'nhomg-font', 'https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap', array(), null );
    wp_enqueue_style( 'nhomg-job-contact', get_template_directory_uri() . '/css/nhomg-job-contact.css', array( 'nhomg-font' ), NHOMG_JOBS_VERSION );
    if ( is_singular( 'job_listing' ) ) {
        wp_enqueue_script( 'nhomg-job-detail', get_template_directory_uri() . '/js/nhomg-job-detail.js', array(), NHOMG_JOBS_VERSION, true );
        wp_localize_script( 'nhomg-job-detail', 'nhomgJob', array(
            'copied' => __( 'Link copied!', 'jobscout' ),
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'nhomg_enqueue_scripts', 20 );

/** Hai trang này dùng layout riêng, bỏ class sidebar của theme. */
function nhomg_body_class( $classes ) {
    if ( ! nhomg_is_part5_page() ) return $classes;
    $classes   = array_diff( $classes, array( 'rightsidebar', 'leftsidebar' ) );
    $classes[] = 'full-width';
    $classes[] = is_singular( 'job_listing' ) ? 'nhomg-job-detail-page' : 'nhomg-contact-page';
    return $classes;
}
add_filter( 'body_class', 'nhomg_body_class', 99 );

/** Tắt banner header mặc định của JobScout trên trang chi tiết việc làm. */
function nhomg_disable_job_banner( $value ) {
    return is_singular( 'job_listing' ) ? false : $value;
}
add_filter( 'theme_mod_ed_job_banner', 'nhomg_disable_job_banner' );

/*--------------------------------------------------------------
# Customizer: nội dung trang Contact
--------------------------------------------------------------*/
function nhomg_contact_defaults() {
    return array(
        'nhomg_contact_title'          => __( 'Contact Us', 'jobscout' ),
        'nhomg_contact_hq_title'       => __( 'Our Headquarters Address', 'jobscout' ),
        'nhomg_contact_hq_address'     => '60 Nguyen Van Thu, Ward Đa Kao, District 1, Ho Chi Minh City, Viet Nam',
        'nhomg_contact_emp_title'      => __( 'For Employers', 'jobscout' ),
        'nhomg_contact_emp_text'       => __( 'Call our Sales Hotline', 'jobscout' ),
        'nhomg_contact_hcm_label'      => __( 'Ho Chi Minh', 'jobscout' ),
        'nhomg_contact_hcm_phone'      => '(+84) 28 1234 5678',
        'nhomg_contact_hn_label'       => __( 'Ha Noi', 'jobscout' ),
        'nhomg_contact_hn_phone'       => '(+84) 24 1234 5678',
        'nhomg_contact_emp_note'       => __( "Request a call from one of our\nCustomer Love Account Managers\nWe're ready to help you grow!", 'jobscout' ),
        'nhomg_contact_seek_title'     => __( 'For Jobseekers', 'jobscout' ),
        'nhomg_contact_facebook_url'   => 'https://www.facebook.com/',
        'nhomg_contact_blog_url'       => '',
        'nhomg_contact_call_label'     => __( 'Call us at', 'jobscout' ),
        'nhomg_contact_call_phone'     => '(+84) 28 8765 4321',
    );
}

function nhomg_contact_mod( $key ) {
    $defaults = nhomg_contact_defaults();
    return get_theme_mod( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

function nhomg_customize_register( $wp_customize ) {
    $wp_customize->add_section( 'nhomg_contact', array(
        'title'       => __( 'Contact Page', 'jobscout' ),
        'description' => __( 'Content of pages using the "Contact Us" template. The banner uses the page\'s featured image.', 'jobscout' ),
        'priority'    => 160,
    ) );

    $labels = array(
        'nhomg_contact_title'        => __( 'Banner title', 'jobscout' ),
        'nhomg_contact_hq_title'     => __( 'Headquarters heading', 'jobscout' ),
        'nhomg_contact_hq_address'   => __( 'Headquarters address', 'jobscout' ),
        'nhomg_contact_emp_title'    => __( 'Employers heading', 'jobscout' ),
        'nhomg_contact_emp_text'     => __( 'Employers text', 'jobscout' ),
        'nhomg_contact_hcm_label'    => __( 'Office 1 name', 'jobscout' ),
        'nhomg_contact_hcm_phone'    => __( 'Office 1 phone', 'jobscout' ),
        'nhomg_contact_hn_label'     => __( 'Office 2 name', 'jobscout' ),
        'nhomg_contact_hn_phone'     => __( 'Office 2 phone', 'jobscout' ),
        'nhomg_contact_emp_note'     => __( 'Employers note', 'jobscout' ),
        'nhomg_contact_seek_title'   => __( 'Jobseekers heading', 'jobscout' ),
        'nhomg_contact_facebook_url' => __( 'Facebook page URL', 'jobscout' ),
        'nhomg_contact_blog_url'     => __( 'Blog posts URL (empty = News page)', 'jobscout' ),
        'nhomg_contact_call_label'   => __( 'Jobseekers call label', 'jobscout' ),
        'nhomg_contact_call_phone'   => __( 'Jobseekers phone', 'jobscout' ),
    );

    foreach ( nhomg_contact_defaults() as $key => $default ) {
        $is_url  = '_url' === substr( $key, -4 );
        $is_note = 'nhomg_contact_emp_note' === $key;
        $wp_customize->add_setting( $key, array(
            'default'           => $default,
            'sanitize_callback' => $is_url ? 'esc_url_raw' : ( $is_note ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
        ) );
        $wp_customize->add_control( $key, array(
            'label'   => $labels[ $key ],
            'section' => 'nhomg_contact',
            'type'    => $is_url ? 'url' : ( $is_note ? 'textarea' : 'text' ),
        ) );
    }
}
add_action( 'customize_register', 'nhomg_customize_register' );

/** Link "blog posts" trên trang Contact: setting > trang News > trang chủ. */
function nhomg_contact_blog_url() {
    $url = nhomg_contact_mod( 'nhomg_contact_blog_url' );
    if ( $url ) return $url;
    $news = get_page_by_path( 'news' );
    if ( $news ) return get_permalink( $news );
    $posts_page = (int) get_option( 'page_for_posts' );
    return $posts_page ? get_permalink( $posts_page ) : home_url( '/' );
}
