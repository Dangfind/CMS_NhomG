<?php
/** Home data, routing and assets. Does not register another job post type. */
defined( 'ABSPATH' ) || exit;

/** Called only by front-page.php, before the shared header opens content. */
function lam_home_prepare_layout() {
    remove_action( 'jobscout_content', 'jobscout_content_start' );
    remove_action( 'jobscout_before_footer', 'jobscout_content_end', 20 );
    add_action( 'jobscout_content', 'lam_home_content_start' );
}
function lam_home_content_start() {
    // The shared jobscout_page_end callback closes this existing outer wrapper.
    echo '<div id="acc-content">';
}
function lam_home_enqueue() {
    if ( is_front_page() ) {
        $file = '/sections/home/home.css';
        wp_enqueue_style( 'lam-home', get_template_directory_uri() . $file, array( 'jobscout' ), filemtime( get_template_directory() . $file ) );
    }
}
add_action( 'wp_enqueue_scripts', 'lam_home_enqueue', 30 );

function lam_home_filters() {
    $filters = array();
    foreach ( array( 'search_keywords', 'search_location' ) as $key ) {
        $value = isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
        $filters[ $key ] = function_exists( 'mb_substr' ) ? mb_substr( $value, 0, 200 ) : substr( $value, 0, 200 );
    }
    return $filters;
}
function lam_home_available_meta() {
    return array(
        'relation' => 'AND',
        array( 'relation' => 'OR', array( 'key' => '_filled', 'compare' => 'NOT EXISTS' ), array( 'key' => '_filled', 'value' => '1', 'compare' => '!=' ) ),
        array( 'relation' => 'OR',
            array( 'key' => '_job_expires', 'compare' => 'NOT EXISTS' ),
            array( 'key' => '_job_expires', 'value' => '', 'compare' => '=' ),
            array( 'key' => '_job_expires', 'value' => current_time( 'Y-m-d' ), 'compare' => '>=', 'type' => 'DATE' ),
        ),
    );
}
function lam_home_job_args( $filters = array(), $limit = 6 ) {
    return array(
        'post_type' => 'job_listing', 'post_status' => 'publish', 'has_password' => false,
        'posts_per_page' => $limit, 'ignore_sticky_posts' => true, 'no_found_rows' => true,
        'orderby' => array( 'date' => 'DESC', 'ID' => 'DESC' ), 'meta_query' => lam_home_available_meta(),
        's' => isset( $filters['search_keywords'] ) ? $filters['search_keywords'] : '',
        'lam_home_jobs' => true,
        'lam_home_location' => isset( $filters['search_location'] ) ? $filters['search_location'] : '',
    );
}
/** Prepared, read-only clauses apply exclusively to the marked Home job query. */
function lam_home_job_clauses( $clauses, $query ) {
    if ( ! $query->get( 'lam_home_jobs' ) ) return $clauses;
    global $wpdb;
    $location = $query->get( 'lam_home_location' );
    if ( $location ) {
        $clauses['where'] .= $wpdb->prepare(
            " AND (EXISTS (SELECT 1 FROM {$wpdb->postmeta} AS lam_location WHERE lam_location.post_id = {$wpdb->posts}.ID AND lam_location.meta_key IN ('_job_location', 'geolocation_formatted_address', 'geolocation_state_long') AND lam_location.meta_value LIKE %s) OR EXISTS (SELECT 1 FROM {$wpdb->term_relationships} AS lam_rel INNER JOIN {$wpdb->term_taxonomy} AS lam_tax ON lam_tax.term_taxonomy_id = lam_rel.term_taxonomy_id INNER JOIN {$wpdb->terms} AS lam_term ON lam_term.term_id = lam_tax.term_id WHERE lam_rel.object_id = {$wpdb->posts}.ID AND lam_tax.taxonomy IN ('job_listing_region', 'job_listing_location') AND lam_term.name = %s))",
            '%' . $wpdb->esc_like( $location ) . '%', $location
        );
    }
    // Keeps jobs with no _featured field; a subquery prevents duplicate cards.
    $clauses['orderby'] = "COALESCE((SELECT MAX(CAST(lam_feature.meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} AS lam_feature WHERE lam_feature.post_id = {$wpdb->posts}.ID AND lam_feature.meta_key = '_featured'), 0) DESC, {$wpdb->posts}.post_date DESC, {$wpdb->posts}.ID DESC";
    return $clauses;
}
/** Fallback for an existing job_listing provider without WP Job Manager. */
function lam_home_job_search( $search, $query ) {
    if ( ! $query->get( 'lam_home_jobs' ) || ! $query->get( 's' ) ) return $search;
    global $wpdb;
    $conditions = array();
    foreach ( preg_split( '/\s+/u', trim( $query->get( 's' ) ), -1, PREG_SPLIT_NO_EMPTY ) as $term ) {
        $like = '%' . $wpdb->esc_like( $term ) . '%';
        $conditions[] = $wpdb->prepare(
            "({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_excerpt LIKE %s OR {$wpdb->posts}.post_content LIKE %s OR EXISTS (SELECT 1 FROM {$wpdb->postmeta} AS lam_keyword WHERE lam_keyword.post_id = {$wpdb->posts}.ID AND lam_keyword.meta_key IN ('_company_name', '_company_tagline', '_job_skills') AND lam_keyword.meta_value LIKE %s) OR EXISTS (SELECT 1 FROM {$wpdb->term_relationships} AS lam_key_rel INNER JOIN {$wpdb->term_taxonomy} AS lam_key_tax ON lam_key_tax.term_taxonomy_id = lam_key_rel.term_taxonomy_id INNER JOIN {$wpdb->terms} AS lam_key_term ON lam_key_term.term_id = lam_key_tax.term_id WHERE lam_key_rel.object_id = {$wpdb->posts}.ID AND lam_key_tax.taxonomy IN ('job_listing_category', 'job_listing_skill', 'job_listing_tag') AND lam_key_term.name LIKE %s))",
            $like, $like, $like, $like, $like
        );
    }
    return $conditions ? ' AND (' . implode( ' AND ', $conditions ) . ')' : $search;
}
function lam_home_searchable_meta( $keys ) {
    $keys[] = '_job_skills';
    return array_unique( $keys );
}
function lam_home_jobs( $filters = array(), $limit = 6 ) {
    if ( ! post_type_exists( 'job_listing' ) ) return new WP_Query();
    $args = lam_home_job_args( $filters, $limit );
    add_filter( 'posts_clauses', 'lam_home_job_clauses', 20, 2 );
    try {
        if ( function_exists( 'get_job_listings' ) ) {
            $adapt = static function ( $query_args ) use ( $args ) {
                $query_args['meta_query'] = array( 'relation' => 'AND', isset( $query_args['meta_query'] ) ? $query_args['meta_query'] : array(), lam_home_available_meta() );
                $query_args['lam_home_jobs'] = true;
                $query_args['lam_home_location'] = $args['lam_home_location'];
                $query_args['no_found_rows'] = true;
                return $query_args;
            };
            add_filter( 'get_job_listings_query_args', $adapt );
            add_filter( 'job_listing_searchable_meta_keys', 'lam_home_searchable_meta' );
            try {
                return get_job_listings( array( 'search_keywords' => $args['s'], 'search_location' => '', 'posts_per_page' => $limit, 'post_status' => array( 'publish' ), 'orderby' => 'featured' ) );
            } finally {
                remove_filter( 'get_job_listings_query_args', $adapt );
                remove_filter( 'job_listing_searchable_meta_keys', 'lam_home_searchable_meta' );
            }
        }
        add_filter( 'posts_search', 'lam_home_job_search', 20, 2 );
        try { return new WP_Query( $args ); }
        finally { remove_filter( 'posts_search', 'lam_home_job_search', 20 ); }
    } finally { remove_filter( 'posts_clauses', 'lam_home_job_clauses', 20 ); }
}
/** Select options come only from published, currently available jobs. */
function lam_home_locations() {
    if ( ! post_type_exists( 'job_listing' ) ) return array();
    $args = lam_home_job_args( array(), -1 );
    unset( $args['lam_home_jobs'], $args['lam_home_location'] );
    $args['fields'] = 'ids';
    $query = new WP_Query( $args );
    $locations = array();
    if ( $query->posts ) {
        update_meta_cache( 'post', $query->posts );
        foreach ( $query->posts as $id ) {
            $location = trim( get_post_meta( $id, '_job_location', true ) );
            if ( ! $location ) $location = trim( get_post_meta( $id, 'geolocation_formatted_address', true ) );
            if ( ! $location ) $location = trim( get_post_meta( $id, 'geolocation_state_long', true ) );
            if ( $location ) $locations[ $location ] = $location;
        }
        foreach ( array( 'job_listing_region', 'job_listing_location' ) as $taxonomy ) {
            if ( ! taxonomy_exists( $taxonomy ) ) continue;
            $terms = wp_get_object_terms( $query->posts, $taxonomy );
            if ( ! is_wp_error( $terms ) ) {
                foreach ( $terms as $term ) $locations[ $term->name ] = $term->name;
            }
        }
    }
    natcasesort( $locations );
    return $locations;
}
/** Hand off only to a published page that actually contains the WPJM jobs shortcode. */
function lam_home_search_route() {
    $url = home_url( '/' ) . '#lam-home-jobs';
    $page_ids = array_unique( array_filter( array( absint( get_theme_mod( 'cmsng_page_jobs', 0 ) ), absint( get_option( 'job_manager_jobs_page_id' ) ) ) ) );
    if ( function_exists( 'get_job_listings' ) && ! taxonomy_exists( 'job_listing_region' ) && ! taxonomy_exists( 'job_listing_location' ) ) {
        foreach ( $page_ids as $page_id ) {
            $page = get_post( $page_id );
            if ( $page && 'publish' === $page->post_status && has_shortcode( $page->post_content, 'jobs' ) ) {
                $url = get_permalink( $page );
                break;
            }
        }
    }
    $hidden = array();
    $query_string = wp_parse_url( $url, PHP_URL_QUERY );
    if ( $query_string ) wp_parse_str( $query_string, $hidden );
    return array( 'url' => $url, 'hidden' => $hidden );
}
function lam_home_destination( $section ) {
    $url = function_exists( 'cmsng_section_url' ) ? cmsng_section_url( $section ) : '';
    return $url ? $url : '';
}
function lam_home_taxonomy_names( $id, $taxonomy ) {
    if ( ! taxonomy_exists( $taxonomy ) ) return '';
    $names = wp_get_post_terms( $id, $taxonomy, array( 'fields' => 'names' ) );
    return is_wp_error( $names ) ? '' : implode( ', ', $names );
}
function lam_home_job_logo( $id ) {
    $logo = get_post_meta( $id, '_company_logo', true );
    if ( is_numeric( $logo ) ) return wp_get_attachment_image( absint( $logo ), 'thumbnail', false, array( 'class' => 'lam-home-company-logo', 'loading' => 'lazy' ) );
    if ( is_string( $logo ) && esc_url( $logo ) ) return '<img class="lam-home-company-logo" src="' . esc_url( $logo ) . '" alt="' . esc_attr( get_post_meta( $id, '_company_name', true ) ) . '" loading="lazy" width="120" height="120">';
    return '<span class="lam-home-logo-empty"><span class="screen-reader-text">' . esc_html__( 'Company logo unavailable', 'jobscout' ) . '</span></span>';
}
function lam_home_job_summary( $post ) {
    $source = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
    preg_match_all( '/<li\b[^>]*>(.*?)<\/li>/is', $source, $matches );
    $lines = $matches[1];
    if ( ! $lines ) {
        $source = preg_replace( '/<\/(?:p|div|h[1-6])>|<br\s*\/?\s*>/i', "\n", $source );
        $lines = preg_split( '/[\r\n]+/', wp_strip_all_tags( strip_shortcodes( $source ) ) );
    }
    $lines = array_values( array_filter( array_map( static function ( $line ) { return trim( wp_strip_all_tags( strip_shortcodes( $line ) ) ); }, $lines ) ) );
    return array_map( static function ( $line ) { return wp_trim_words( $line, 18, '…' ); }, array_slice( $lines, 0, 3 ) );
}
function lam_home_news_image( $post ) {
    if ( has_post_thumbnail( $post ) ) return get_the_post_thumbnail( $post, 'medium_large', array( 'class' => 'lam-home-news-image', 'loading' => 'lazy' ) );
    $url = '';
    if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
        $processor = new WP_HTML_Tag_Processor( $post->post_content );
        while ( $processor->next_tag( 'IMG' ) ) {
            $url = esc_url( (string) $processor->get_attribute( 'src' ) );
            if ( $url ) break;
        }
    }
    if ( $url ) return '<img class="lam-home-news-image" src="' . esc_url( $url ) . '" alt="' . esc_attr( get_the_title( $post ) ) . '" loading="lazy" width="200" height="200">';
    return '<span class="lam-home-news-image lam-home-image-empty"><span class="screen-reader-text">' . esc_html__( 'Image unavailable', 'jobscout' ) . '</span></span>';
}
function lam_home_news_excerpt( $post ) {
    return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_excerpt ? $post->post_excerpt : $post->post_content ) ), 22, '…' );
}
function lam_home_background( $key ) {
    $url = esc_url_raw( get_theme_mod( 'lam_home_' . $key . '_image', '' ) );
    if ( ! $url && in_array( $key, array( 'hero', 'career' ), true ) ) {
        foreach ( array( 'jpg', 'jpeg', 'webp', 'png' ) as $extension ) {
            $file = '/sections/home/assets/' . $key . '.' . $extension;
            if ( is_file( get_template_directory() . $file ) ) {
                $url = get_template_directory_uri() . $file;
                break;
            }
        }
    }
    return $url ? 'background-image: linear-gradient(rgba(0,0,0,.48), rgba(0,0,0,.48)), url("' . str_replace( array( '\\', '"', "\n", "\r" ), array( '\\\\', '\\"', '', '' ), $url ) . '");' : '';
}
function lam_home_customize( $wp_customize ) {
    $wp_customize->add_section( 'lam_home', array( 'title' => __( 'Home — Content', 'jobscout' ), 'priority' => 36, 'description' => __( 'Upload the original reference images. Home content only; shared chrome is unchanged.', 'jobscout' ) ) );
    foreach ( array( 'hero' => 'Banner image — Japanese landscape', 'career' => 'Career image — cherry blossoms' ) as $key => $label ) {
        $setting = 'lam_home_' . $key . '_image';
        $wp_customize->add_setting( $setting, array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
        $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $setting, array( 'section' => 'lam_home', 'label' => $label ) ) );
    }
}
add_action( 'customize_register', 'lam_home_customize' );

if ( is_admin() ) require __DIR__ . '/demo.php';
