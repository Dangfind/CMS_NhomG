<?php
/**
 * NhomG - Trang quản lý việc làm trong WP Admin (phần 5)
 *
 * - Màn hình sửa Job dùng trình soạn thảo cổ điển để form "Job Detail" hiện đầy đủ.
 * - Danh sách All Jobs: thêm cột logo, công ty, địa điểm, đánh giá, ảnh, cách ứng tuyển.
 * - Bộ lọc theo Job Type / Job Category, sắp xếp, tìm theo công ty & địa điểm.
 * - Widget "Jobs overview" trên Dashboard.
 *
 * @package JobScout
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/*--------------------------------------------------------------
# Màn hình sửa Job
--------------------------------------------------------------*/

/** Dùng trình soạn thảo cổ điển: form Job Detail hiện ngay dưới tiêu đề thay vì bị thu gọn dưới đáy. */
function nhomg_jobs_disable_block_editor( $use, $post_type ) {
    return 'job_listing' === $post_type ? false : $use;
}
add_filter( 'use_block_editor_for_post_type', 'nhomg_jobs_disable_block_editor', 10, 2 );

/** Gợi ý ô tiêu đề. */
function nhomg_jobs_title_placeholder( $text, $post ) {
    return 'job_listing' === $post->post_type ? __( 'Job title, e.g. Hotel Manager', 'jobscout' ) : $text;
}
add_filter( 'enter_title_here', 'nhomg_jobs_title_placeholder', 10, 2 );

/** Thông báo sau khi lưu: "Job ..." thay vì "Post ...". */
function nhomg_jobs_updated_messages( $messages ) {
    $post = get_post();
    $link = $post ? ' <a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html__( 'View job', 'jobscout' ) . '</a>' : '';
    $preview = $post ? ' <a target="_blank" href="' . esc_url( get_preview_post_link( $post ) ) . '">' . esc_html__( 'Preview job', 'jobscout' ) . '</a>' : '';

    $messages['job_listing'] = array(
        0  => '',
        1  => __( 'Job updated.', 'jobscout' ) . $link,
        4  => __( 'Job updated.', 'jobscout' ),
        6  => __( 'Job published.', 'jobscout' ) . $link,
        7  => __( 'Job saved.', 'jobscout' ),
        8  => __( 'Job submitted.', 'jobscout' ) . $preview,
        9  => __( 'Job scheduled.', 'jobscout' ),
        10 => __( 'Job draft updated.', 'jobscout' ) . $preview,
    );
    return $messages;
}
add_filter( 'post_updated_messages', 'nhomg_jobs_updated_messages' );

/*--------------------------------------------------------------
# Danh sách All Jobs: cột
--------------------------------------------------------------*/
function nhomg_jobs_columns( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        if ( 'title' === $key ) {
            $new['nhomg_logo'] = __( 'Logo', 'jobscout' );
            $new[ $key ]       = __( 'Job', 'jobscout' );
            $new['nhomg_company']  = __( 'Company', 'jobscout' );
            $new['nhomg_location'] = __( 'Location', 'jobscout' );
            continue;
        }
        if ( 'date' === $key ) {
            $new['nhomg_rating'] = __( 'Rating', 'jobscout' );
            $new['nhomg_photos'] = __( 'Photos', 'jobscout' );
            $new['nhomg_apply']  = __( 'Apply via', 'jobscout' );
        }
        $new[ $key ] = $label;
    }
    return $new;
}
add_filter( 'manage_job_listing_posts_columns', 'nhomg_jobs_columns' );

function nhomg_jobs_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'nhomg_logo':
            if ( has_post_thumbnail( $post_id ) ) {
                echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">' . get_the_post_thumbnail( $post_id, array( 48, 48 ) ) . '</a>';
            } else {
                echo '<span class="nhomg-col-empty" title="' . esc_attr__( 'No logo', 'jobscout' ) . '">—</span>';
            }
            break;

        case 'nhomg_company':
        case 'nhomg_location':
            $value = get_post_meta( $post_id, 'nhomg_company' === $column ? '_company_name' : '_job_location', true );
            echo $value ? esc_html( $value ) : '<span class="nhomg-col-empty">—</span>';
            break;

        case 'nhomg_rating':
            $rating = get_post_meta( $post_id, '_nhomg_rating', true );
            if ( '' === $rating ) {
                echo '<span class="nhomg-col-empty">—</span>';
                break;
            }
            $full = (int) round( (float) $rating );
            echo '<span class="nhomg-col-stars" aria-hidden="true">' . str_repeat( '★', $full ) . str_repeat( '☆', 5 - $full ) . '</span> ';
            echo '<strong>' . esc_html( number_format_i18n( (float) $rating, 1 ) ) . '</strong>';
            break;

        case 'nhomg_photos':
            $count = count( nhomg_job_photo_ids( $post_id ) );
            echo $count ? (int) $count : '<span class="nhomg-col-empty">0</span>';
            break;

        case 'nhomg_apply':
            $application = trim( (string) get_post_meta( $post_id, '_application', true ) );
            if ( $application && is_email( $application ) ) {
                echo '<span class="dashicons dashicons-email-alt"></span> ' . esc_html( $application );
            } elseif ( $application && filter_var( $application, FILTER_VALIDATE_URL ) ) {
                echo '<span class="dashicons dashicons-admin-links"></span> <a href="' . esc_url( $application ) . '" target="_blank" rel="noopener">' . esc_html__( 'External URL', 'jobscout' ) . '</a>';
            } else {
                echo '<span class="dashicons dashicons-phone"></span> ' . esc_html__( 'Contact page', 'jobscout' );
            }
            break;
    }
}
add_action( 'manage_job_listing_posts_custom_column', 'nhomg_jobs_column_content', 10, 2 );

function nhomg_jobs_sortable_columns( $columns ) {
    $columns['nhomg_company']  = 'nhomg_company';
    $columns['nhomg_location'] = 'nhomg_location';
    $columns['nhomg_rating']   = 'nhomg_rating';
    return $columns;
}
add_filter( 'manage_edit-job_listing_sortable_columns', 'nhomg_jobs_sortable_columns' );

/** Sắp xếp theo cột meta. */
function nhomg_jobs_admin_orderby( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() || 'job_listing' !== $query->get( 'post_type' ) ) return;

    $map = array(
        'nhomg_company'  => array( '_company_name', 'meta_value' ),
        'nhomg_location' => array( '_job_location', 'meta_value' ),
        'nhomg_rating'   => array( '_nhomg_rating', 'meta_value_num' ),
    );
    $orderby = $query->get( 'orderby' );
    if ( is_string( $orderby ) && isset( $map[ $orderby ] ) ) {
        $query->set( 'meta_key', $map[ $orderby ][0] );
        $query->set( 'orderby', $map[ $orderby ][1] );
    }
}
add_action( 'pre_get_posts', 'nhomg_jobs_admin_orderby' );

/*--------------------------------------------------------------
# Bộ lọc & tìm kiếm
--------------------------------------------------------------*/
function nhomg_jobs_filters( $post_type ) {
    if ( 'job_listing' !== $post_type ) return;

    $taxonomies = array(
        'job_listing_type'     => __( 'All job types', 'jobscout' ),
        'job_listing_category' => __( 'All categories', 'jobscout' ),
    );
    foreach ( $taxonomies as $taxonomy => $all_label ) {
        if ( ! taxonomy_exists( $taxonomy ) ) continue;
        wp_dropdown_categories( array(
            'taxonomy'        => $taxonomy,
            'name'            => $taxonomy,
            'value_field'     => 'slug',
            'show_option_all' => $all_label,
            'selected'        => isset( $_GET[ $taxonomy ] ) ? sanitize_title( wp_unslash( $_GET[ $taxonomy ] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
            'hierarchical'    => true,
            'show_count'      => true,
            'hide_empty'      => false,
        ) );
    }
}
add_action( 'restrict_manage_posts', 'nhomg_jobs_filters' );

/** "Tất cả" trong dropdown gửi giá trị 0: bỏ qua để không lọc rỗng. */
function nhomg_jobs_ignore_empty_filters( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() || 'job_listing' !== $query->get( 'post_type' ) ) return;
    foreach ( array( 'job_listing_type', 'job_listing_category' ) as $taxonomy ) {
        if ( '0' === (string) $query->get( $taxonomy ) ) $query->set( $taxonomy, '' );
    }
}
add_action( 'pre_get_posts', 'nhomg_jobs_ignore_empty_filters', 5 );

/**
 * Theme gốc ép post_status = publish khi lọc theo Job Type (dành cho trang ngoài).
 * Trong admin trả lại trạng thái người dùng chọn để không mất bản nháp.
 */
function nhomg_jobs_restore_admin_status( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() || 'job_listing' !== $query->get( 'post_type' ) ) return;
    $status = isset( $_GET['post_status'] ) ? sanitize_key( wp_unslash( $_GET['post_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
    $query->set( 'post_status', $status );
}
add_action( 'pre_get_posts', 'nhomg_jobs_restore_admin_status', 20 );

/** Ô tìm kiếm tìm cả trong tên công ty và địa điểm. */
function nhomg_jobs_admin_search( $search, $query ) {
    global $wpdb;
    if ( ! is_admin() || ! $query->is_main_query() || 'job_listing' !== $query->get( 'post_type' ) ) return $search;

    $term = $query->get( 's' );
    if ( '' === $term || '' === $search ) return $search;

    $like = '%' . $wpdb->esc_like( $term ) . '%';
    $meta = $wpdb->prepare(
        "EXISTS ( SELECT 1 FROM {$wpdb->postmeta} nhomg_pm WHERE nhomg_pm.post_id = {$wpdb->posts}.ID AND nhomg_pm.meta_key IN ( '_company_name', '_job_location' ) AND nhomg_pm.meta_value LIKE %s )",
        $like
    );
    // $search có dạng " AND ((...))": thêm điều kiện OR vào trong.
    return preg_replace( '/^\s*AND\s*\(/', ' AND ( ' . $meta . ' OR ', $search, 1 );
}
add_filter( 'posts_search', 'nhomg_jobs_admin_search', 10, 2 );

/*--------------------------------------------------------------
# Dashboard
--------------------------------------------------------------*/
function nhomg_jobs_dashboard_setup() {
    if ( ! current_user_can( 'edit_posts' ) ) return;
    wp_add_dashboard_widget( 'nhomg_jobs_overview', __( 'Jobs overview', 'jobscout' ), 'nhomg_jobs_dashboard_widget' );
}
add_action( 'wp_dashboard_setup', 'nhomg_jobs_dashboard_setup' );

function nhomg_jobs_dashboard_widget() {
    $counts = wp_count_posts( 'job_listing' );
    $list   = admin_url( 'edit.php?post_type=job_listing' );
    $stats  = array(
        array( __( 'Published', 'jobscout' ), (int) $counts->publish, $list . '&post_status=publish' ),
        array( __( 'Drafts', 'jobscout' ), (int) $counts->draft, $list . '&post_status=draft' ),
        array( __( 'Pending', 'jobscout' ), (int) $counts->pending, $list . '&post_status=pending' ),
    );

    echo '<ul class="nhomg-dash-stats">';
    foreach ( $stats as $stat ) {
        echo '<li><a href="' . esc_url( $stat[2] ) . '"><strong>' . (int) $stat[1] . '</strong><span>' . esc_html( $stat[0] ) . '</span></a></li>';
    }
    echo '</ul>';

    $latest = get_posts( array( 'post_type' => 'job_listing', 'post_status' => array( 'publish', 'draft', 'pending' ), 'numberposts' => 5 ) );
    if ( $latest ) {
        echo '<h3 class="nhomg-dash-title">' . esc_html__( 'Latest jobs', 'jobscout' ) . '</h3><ul class="nhomg-dash-latest">';
        foreach ( $latest as $job ) {
            $company = get_post_meta( $job->ID, '_company_name', true );
            echo '<li><a href="' . esc_url( get_edit_post_link( $job->ID ) ) . '">' . esc_html( get_the_title( $job ) ) . '</a>';
            if ( $company ) echo ' <span class="nhomg-dash-muted">— ' . esc_html( $company ) . '</span>';
            if ( 'publish' !== $job->post_status ) echo ' <span class="nhomg-dash-status">' . esc_html( get_post_status_object( $job->post_status )->label ) . '</span>';
            echo '</li>';
        }
        echo '</ul>';
    }

    echo '<p class="nhomg-dash-actions">';
    echo '<a class="button button-primary" href="' . esc_url( admin_url( 'post-new.php?post_type=job_listing' ) ) . '">' . esc_html__( 'Add New Job', 'jobscout' ) . '</a> ';
    echo '<a class="button" href="' . esc_url( $list ) . '">' . esc_html__( 'Manage jobs', 'jobscout' ) . '</a> ';
    echo '<a class="button" href="' . esc_url( nhomg_all_jobs_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'View on site', 'jobscout' ) . '</a>';
    echo '</p>';
}

/** Thêm số việc làm vào ô "At a Glance". */
function nhomg_jobs_glance( $items ) {
    $count = (int) wp_count_posts( 'job_listing' )->publish;
    /* translators: %s: number of jobs */
    $text    = sprintf( _n( '%s Job', '%s Jobs', $count, 'jobscout' ), number_format_i18n( $count ) );
    $items[] = '<a class="nhomg-glance-jobs" href="' . esc_url( admin_url( 'edit.php?post_type=job_listing' ) ) . '">' . esc_html( $text ) . '</a>';
    return $items;
}
add_filter( 'dashboard_glance_items', 'nhomg_jobs_glance' );

/*--------------------------------------------------------------
# CSS cho admin
--------------------------------------------------------------*/
function nhomg_jobs_admin_css() {
    $screen = get_current_screen();
    if ( ! $screen || ! in_array( $screen->id, array( 'edit-job_listing', 'job_listing', 'dashboard' ), true ) ) return;
    ?>
    <style>
        .post-type-job_listing .column-nhomg_logo { width: 60px; }
        .post-type-job_listing .column-nhomg_logo img { width: 48px; height: 48px; object-fit: contain; border: 1px solid #dcdcde; background: #fff; }
        .post-type-job_listing .column-nhomg_rating { width: 120px; }
        .post-type-job_listing .column-nhomg_photos { width: 60px; text-align: center; }
        .post-type-job_listing .column-nhomg_apply .dashicons { color: #ea751e; font-size: 16px; width: 16px; height: 16px; vertical-align: text-top; }
        .nhomg-col-stars { color: #ea751e; letter-spacing: 1px; }
        .nhomg-col-empty { color: #a7aaad; }
        .nhomg-metabox__group { margin: 24px -12px 8px; padding: 10px 12px; background: #f6f7f7; border-top: 1px solid #dcdcde; border-bottom: 1px solid #dcdcde; font-size: 14px; }
        .nhomg-metabox__group:first-child { margin-top: -6px; border-top: 0; }
        .nhomg-gallery-preview img { margin: 0 4px 4px 0; border: 1px solid #dcdcde; }
        .nhomg-dash-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin: 0 0 12px; }
        .nhomg-dash-stats a { display: block; padding: 10px; text-align: center; text-decoration: none; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 4px; }
        .nhomg-dash-stats strong { display: block; font-size: 22px; line-height: 1.2; color: #ea751e; }
        .nhomg-dash-stats span { color: #50575e; }
        .nhomg-dash-title { margin: 0 0 6px; font-weight: 600; }
        .nhomg-dash-latest { margin: 0 0 12px; }
        .nhomg-dash-muted { color: #646970; }
        .nhomg-dash-status { padding: 1px 6px; font-size: 11px; background: #f0f0f1; border-radius: 3px; }
        #dashboard_right_now .nhomg-glance-jobs:before { content: "\f338"; }
    </style>
    <?php
}
add_action( 'admin_head', 'nhomg_jobs_admin_css' );
