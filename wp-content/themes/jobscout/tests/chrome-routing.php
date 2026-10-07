<?php
/** Isolated routing checks: php tests/chrome-routing.php (no WordPress/database). */
if ( 'cli' !== PHP_SAPI ) {
    exit;
}

$mods = array();
$options = array();
$pages = array();
$statuses = array();
$context = array();
function add_action() {}
function add_filter() {}
function get_theme_mod( $key, $default = false ) { global $mods; return isset( $mods[ $key ] ) ? $mods[ $key ] : $default; }
function get_option( $key ) { global $options; return isset( $options[ $key ] ) ? $options[ $key ] : false; }
function absint( $value ) { return abs( (int) $value ); }
function home_url( $path ) { return 'https://example.test' . $path; }
function get_post_status( $id ) { global $statuses; return isset( $statuses[ $id ] ) ? $statuses[ $id ] : false; }
function get_permalink( $id ) { return 'https://example.test/?page_id=' . $id; }
function get_page_by_path( $slug ) { global $pages; return isset( $pages[ $slug ] ) ? $pages[ $slug ] : null; }
function post_type_exists( $type ) { global $context; return ! empty( $context['job_type'] ); }
function get_post_type_archive_link( $type ) { return 'https://example.test/jobs/'; }
function is_home() { global $context; return ! empty( $context['home'] ); }
function is_front_page() { global $context; return ! empty( $context['front'] ); }
function is_singular( $type ) { global $context; return isset( $context['singular'] ) && $type === $context['singular']; }
function is_category() { global $context; return ! empty( $context['category'] ); }
function is_tag() { return false; }
function is_author() { return false; }
function is_date() { return false; }
function is_post_type_archive( $type ) { global $context; return isset( $context['archive'] ) && $type === $context['archive']; }
function is_tax( $types ) { global $context; return isset( $context['tax'] ) && in_array( $context['tax'], $types, true ); }
function untrailingslashit( $url ) { return rtrim( $url, '/\\' ); }
function esc_url( $url ) { return 0 === strpos( $url, 'javascript:' ) ? '' : $url; }

require dirname( __DIR__ ) . '/inc/cmsng-chrome.php';

function check( $condition, $message ) {
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
    echo "PASS: $message\n";
}
function item( $id, $url, $classes = array(), $parent = 0 ) {
    return (object) array( 'ID' => $id, 'url' => $url, 'classes' => $classes, 'menu_item_parent' => $parent, 'current' => false );
}

check( '' === cmsng_section_url( 'jobs' ), 'Missing jobs page has no invented URL' );
check( '' === cmsng_submit_job_url(), 'Missing submit page has no placeholder URL' );
$mods['post_job_url'] = '#';
check( '' === cmsng_submit_job_url(), 'Legacy hash CTA is rejected' );
$mods['post_job_url'] = 'https://careers.example.test/submit';
check( $mods['post_job_url'] === cmsng_submit_job_url(), 'Configured CTA is preserved' );
$mods['cmsng_page_jobs'] = 12;
$statuses[12] = 'draft';
check( '' === cmsng_section_url( 'jobs' ), 'Draft pages are excluded' );
$statuses[12] = 'publish';
check( get_permalink( 12 ) === cmsng_section_url( 'jobs' ), 'Published configured jobs page resolves' );
check( ! cmsng_same_url( get_permalink( 12 ), get_permalink( 13 ) ), 'Plain permalink query strings remain distinct' );
check( cmsng_same_url( 'https://example.test/jobs', 'https://example.test/jobs/' ), 'Trailing slashes do not affect matching' );

$args = (object) array( 'theme_location' => 'primary' );
$context = array( 'singular' => 'job_listing' );
$items = array( item( 1, home_url( '/' ), array( 'current_page_parent' ) ), item( 2, get_permalink( 12 ) ) );
$result = cmsng_menu_section_state( $items, $args );
check( in_array( 'cmsng-current-section', $result[1]->classes, true ), 'Job detail highlights JOBS' );
check( ! in_array( 'current_page_parent', $result[0]->classes, true ), 'Job detail does not highlight HOME' );
check( 'location' === cmsng_menu_link_state( array(), $result[1], $args )['aria-current'], 'Section link has accessible current-location state' );
$result = cmsng_menu_section_state( array( item( 3, '/parent' ), item( 4, get_permalink( 12 ), array(), 3 ) ), $args );
check( in_array( 'cmsng-current-section', $result[0]->classes, true ), 'Nested JOBS link highlights parent' );
$options['page_for_posts'] = 21;
$statuses[21] = 'publish';
$context = array( 'singular' => 'post' );
$result = cmsng_menu_section_state( array( item( 5, get_permalink( 21 ) ), item( 6, get_permalink( 12 ) ) ), $args );
check( in_array( 'cmsng-current-section', $result[0]->classes, true ) && ! in_array( 'cmsng-current-section', $result[1]->classes, true ), 'News detail highlights NEWS only' );
$args->theme_location = 'cmsng_footer';
$context = array( 'category' => true );
$result = cmsng_menu_section_state( array( item( 7, get_permalink( 21 ) ) ), $args );
check( in_array( 'cmsng-current-section', $result[0]->classes, true ), 'Footer BLOG follows news context' );
$options = array( 'show_on_front' => 'posts' );
$result = cmsng_menu_section_state( array( item( 8, home_url( '/' ), array( 'current_page_parent' ) ), item( 9, home_url( '/' ), array( 'cmsng-section-news' ) ) ), $args );
check( ! in_array( 'cmsng-current-section', $result[0]->classes, true ) && in_array( 'cmsng-current-section', $result[1]->classes, true ), 'Shared HOME/NEWS URL uses explicit semantic class' );
$context = array( 'archive' => 'job_listing', 'job_type' => true );
check( cmsng_jobs_context(), 'Jobs archive is in JOBS section' );
$context = array( 'tax' => 'job_listing_type' );
check( cmsng_jobs_context(), 'Job taxonomy is in JOBS section' );
echo "All routing checks passed.\n";
