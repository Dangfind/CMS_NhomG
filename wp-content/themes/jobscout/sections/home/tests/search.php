<?php
/** php sections/home/tests/search.php — in-memory SQLite, NEVER the project DB. */
if ( 'cli' !== PHP_SAPI ) exit;
define( 'ABSPATH', dirname( __DIR__, 6 ) . '/' );
function add_action() {}
function add_filter() {}
function remove_filter() {}
function is_admin() { return false; }
function current_time( $format ) { return '2026-10-07'; }
function _get_meta_table( $type ) { return 'wp_postmeta'; }
function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) ); }
function apply_filters( $hook, $value ) { return $value; }
function apply_filters_ref_array( $hook, $args ) { return $args[0]; }
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function wp_unslash( $value ) { return stripslashes( $value ); }
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function strip_shortcodes( $value ) { return preg_replace( '/\[[^\]]*\]/', '', $value ); }
function wp_trim_words( $value, $limit, $more ) { $words = preg_split( '/\s+/', $value ); return implode( ' ', array_slice( $words, 0, $limit ) ) . ( count( $words ) > $limit ? $more : '' ); }
function esc_html__( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $value ) { return str_starts_with( (string) $value, 'javascript:' ) ? '' : (string) $value; }
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function get_the_title( $post ) { return isset( $post->post_title ) ? $post->post_title : 'Article'; }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function __( $value ) { return $value; }
function get_permalink( $post ) { return 'https://example.test/?page_id=' . ( is_object( $post ) ? $post->ID : $post ); }
function has_post_thumbnail() { return false; }
function get_post_meta( $id, $key ) {
    global $db;
    $statement = $db->prepare( 'SELECT meta_value FROM wp_postmeta WHERE post_id=? AND meta_key=?' );
    $statement->execute( array( $id, $key ) );
    return $statement->fetchColumn() ?: '';
}
class HomeTestQuery {
    private $args;
    public function __construct( $args ) { $this->args = $args; }
    public function get( $key ) { return isset( $this->args[ $key ] ) ? $this->args[ $key ] : ''; }
}
class HomeTestDB {
    public $posts = 'wp_posts';
    public $postmeta = 'wp_postmeta';
    public $term_relationships = 'wp_term_relationships';
    public $term_taxonomy = 'wp_term_taxonomy';
    public $terms = 'wp_terms';
    public function prepare( $sql, ...$values ) {
        global $db;
        if ( count( $values ) === 1 && is_array( $values[0] ) ) $values = $values[0];
        return preg_replace_callback( '/%[sd]/', static function ( $match ) use ( &$values, $db ) {
            $value = array_shift( $values );
            return '%d' === $match[0] ? (string) (int) $value : $db->quote( (string) $value );
        }, $sql );
    }
    public function esc_like( $value ) { return addcslashes( $value, '_%\\' ); }
}
require ABSPATH . 'wp-includes/class-wp-meta-query.php';
foreach ( array( 'attribute-token', 'span', 'decoder', 'tag-processor' ) as $class ) require ABSPATH . 'wp-includes/html-api/class-wp-html-' . $class . '.php';
require dirname( __DIR__ ) . '/functions.php';
$db = new PDO( 'sqlite::memory:' );
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
$wpdb = new HomeTestDB();
$db->exec( 'CREATE TABLE wp_posts (ID INTEGER, post_type TEXT, post_status TEXT, post_password TEXT, post_title TEXT, post_excerpt TEXT, post_content TEXT, post_date TEXT)' );
$db->exec( 'CREATE TABLE wp_postmeta (meta_id INTEGER PRIMARY KEY, post_id INTEGER, meta_key TEXT, meta_value TEXT)' );
$db->exec( 'CREATE TABLE wp_term_relationships (object_id INTEGER, term_taxonomy_id INTEGER)' );
$db->exec( 'CREATE TABLE wp_term_taxonomy (term_taxonomy_id INTEGER, term_id INTEGER, taxonomy TEXT)' );
$db->exec( 'CREATE TABLE wp_terms (term_id INTEGER, name TEXT)' );

function fixture( $id, $title, $date, $meta = array(), $status = 'publish', $type = 'job_listing', $password = '' ) {
    global $db;
    $db->prepare( 'INSERT INTO wp_posts VALUES (?, ?, ?, ?, ?, ?, ?, ?)' )->execute( array( $id, $type, $status, $password, $title, '', 'Hospitality role', $date ) );
    foreach ( $meta as $key => $value ) $db->prepare( 'INSERT INTO wp_postmeta(post_id,meta_key,meta_value) VALUES (?,?,?)' )->execute( array( $id, $key, $value ) );
}
fixture( 1, 'Hotel Manager', '2026-10-01', array( '_featured' => 1, '_job_location' => 'Tokyo', '_filled' => 0, '_job_expires' => '2026-10-30' ) );
fixture( 2, 'Housekeeper', '2026-10-06', array( '_company_name' => 'Maple', '_job_skills' => 'Japanese', '_job_location' => 'Osaka' ) );
fixture( 3, 'Bellman', '2026-10-07', array( '_job_location' => 'Tokyo' ) );
fixture( 4, 'Expired Manager', '2026-10-07', array( '_job_expires' => '2026-10-06', '_job_location' => 'Tokyo' ) );
fixture( 5, 'Filled Manager', '2026-10-07', array( '_filled' => 1, '_job_location' => 'Tokyo' ) );
fixture( 6, 'Draft Manager', '2026-10-07', array( '_job_location' => 'Tokyo' ), 'draft' );
fixture( 7, 'Future Manager', '2026-10-09', array( '_job_location' => 'Tokyo' ), 'future' );
fixture( 8, 'Manager news', '2026-10-07', array(), 'publish', 'post' );
fixture( 9, 'Cook', '2026-09-01' );
fixture( 10, 'Chef', '2026-09-02' );
fixture( 11, 'Receptionist', '2026-10-07 12:00:00', array( '_job_location' => 'Tokyo', '_job_expires' => '2026-10-07' ) );
fixture( 12, 'Protected Manager', '2026-10-07', array(), 'publish', 'job_listing', 'secret' );
$db->exec( "INSERT INTO wp_terms VALUES (1,'Kyoto'); INSERT INTO wp_term_taxonomy VALUES (1,1,'job_listing_region'); INSERT INTO wp_term_relationships VALUES (10,1)" );

function results( $keyword = '', $location = '' ) {
    global $db, $wpdb;
    $args = lam_home_job_args( array( 'search_keywords' => $keyword, 'search_location' => $location ) );
    $query = new HomeTestQuery( $args );
    // Use WordPress's actual meta query compiler, rather than reimplementing it.
    $meta = new WP_Meta_Query( $args['meta_query'] );
    $sql = $meta->get_sql( 'post', 'wp_posts', 'ID' );
    $clauses = lam_home_job_clauses( array( 'where' => " AND wp_posts.post_type='job_listing' AND wp_posts.post_status='publish' AND wp_posts.post_password=''" . $sql['where'] . lam_home_job_search( '', $query ), 'orderby' => '' ), $query );
    $statement = 'SELECT DISTINCT wp_posts.ID FROM wp_posts ' . $sql['join'] . ' WHERE 1=1' . $clauses['where'] . ' ORDER BY ' . $clauses['orderby'] . ' LIMIT ' . $args['posts_per_page'];
    // SQLite equivalent of MySQL DATE casts and LIKE escape semantics.
    $statement = preg_replace( '/CAST\(([^()]+) AS DATE\)/', 'date($1)', $statement );
    $statement = preg_replace( "/LIKE ('(?:[^']|'')*')/", "$0 ESCAPE '\\'", $statement );
    return array_map( 'intval', $db->query( $statement )->fetchAll( PDO::FETCH_COLUMN ) );
}
function check( $actual, $expected, $message ) {
    if ( $actual !== $expected ) throw new RuntimeException( $message . ': ' . json_encode( $actual ) );
    echo "PASS: $message\n";
}
check( results(), array( 1, 11, 3, 2, 10, 9 ), 'Six jobs; featured first, then newest; unavailable/news/password entries excluded' );
check( results( 'manager' ), array( 1 ), 'Keyword search' );
check( results( '', 'Tokyo' ), array( 1, 11, 3 ), 'Location search; expiry today is still available' );
check( results( 'manager', 'Tokyo' ), array( 1 ), 'Combined keyword and location' );
check( results( 'Maple' ), array( 2 ), 'Company name search' );
check( results( 'Japanese' ), array( 2 ), 'Skills search' );
check( results( '', 'Kyoto' ), array( 10 ), 'Region taxonomy search' );
check( results( 'unmatched' ), array(), 'No matches' );
check( results( "' OR 1=1 --" ), array(), 'SQL-like keyword stays data' );
check( results( '', "Tokyo' OR 1=1 --" ), array(), 'SQL-like location stays data' );
check( results( '100%' ), array(), 'Literal LIKE wildcard does not broaden search' );
$untouched = array( 'where' => 'original', 'orderby' => 'original' );
check( lam_home_job_clauses( $untouched, new HomeTestQuery( array() ) ), $untouched, 'Other page queries are untouched' );
check( lam_home_job_search( 'original', new HomeTestQuery( array( 's' => 'manager' ) ) ), 'original', 'Other news searches are untouched' );
$_GET = array( 'search_keywords' => array( 'invalid' ), 'search_location' => '<b>Tokyo</b>' );
check( lam_home_filters(), array( 'search_keywords' => '', 'search_location' => 'Tokyo' ), 'Malformed parameters rejected and text sanitized' );
$post = (object) array( 'post_excerpt' => '', 'post_content' => '<ul><li>One <b>line</b></li><li>Two</li><li>Three</li><li>Four</li></ul>' );
check( lam_home_job_summary( $post ), array( 'One line', 'Two', 'Three' ), 'Summary uses at most three sanitized bullet lines' );
check( str_contains( lam_home_job_logo( 9 ), 'lam-home-logo-empty' ), true, 'Missing company logo does not use a content photo' );
check( str_contains( lam_home_news_image( $post ), 'lam-home-image-empty' ), true, 'Missing news image retains a consistent frame' );
$post->post_content = '<p>Text</p><img src="https://example.test/first.jpg"><img src="https://example.test/second.jpg">';
check( str_contains( lam_home_news_image( $post ), 'first.jpg' ), true, 'First content image used when featured image is absent' );
$post->post_content = '<img><img src="javascript:alert(1)"><img src="https://example.test/safe.jpg">';
check( str_contains( lam_home_news_image( $post ), 'safe.jpg' ), true, 'Invalid image URLs are skipped' );
$post->ID = 100;
$post->post_title = str_repeat( 'Long title ', 40 ) . '<script>alert(1)</script>';
$args = array( 'post' => $post );
ob_start();
require dirname( __DIR__ ) . '/news-card.php';
$card = ob_get_clean();
check( str_contains( $card, '<script>' ), false, 'Long title is escaped in card and accessible labels' );
check( substr_count( $card, 'https://example.test/?page_id=100' ), 3, 'News image, title and Read More share the detail permalink' );

// Routing fixtures: no WordPress DB is loaded; WPJM availability is simulated.
function get_job_listings() {}
function absint( $value ) { return abs( (int) $value ); }
function get_theme_mod( $key, $default = '' ) { global $route_settings; return isset( $route_settings[ $key ] ) ? $route_settings[ $key ] : $default; }
function get_option( $key ) { global $route_settings; return isset( $route_settings[ $key ] ) ? $route_settings[ $key ] : false; }
function get_post( $id ) { global $route_pages; return isset( $route_pages[ $id ] ) ? $route_pages[ $id ] : null; }
function home_url( $path ) { return 'https://example.test' . $path; }
function has_shortcode( $content, $shortcode ) { return str_contains( $content, '[' . $shortcode . ']' ); }
function taxonomy_exists( $key ) { global $route_taxonomy; return $route_taxonomy; }
function wp_parse_url( $url, $part ) { return parse_url( $url, $part ); }
function wp_parse_str( $value, &$output ) { parse_str( $value, $output ); }
$route_settings = array();
$route_pages = array();
$route_taxonomy = false;
check( lam_home_search_route()['url'], 'https://example.test/#lam-home-jobs', 'Missing Jobs page falls back to Home results' );
$route_settings['job_manager_jobs_page_id'] = 42;
$route_pages[42] = (object) array( 'ID' => 42, 'post_status' => 'publish', 'post_content' => '[jobs]' );
$route = lam_home_search_route();
check( $route['url'], 'https://example.test/?page_id=42', 'Published WPJM Jobs page receives search' );
check( $route['hidden'], array( 'page_id' => '42' ), 'Plain permalink routing survives GET form submission' );
$route_taxonomy = true;
check( lam_home_search_route()['url'], 'https://example.test/#lam-home-jobs', 'Region searches stay in the capable Home handler' );
$route_taxonomy = false;
$route_pages[42]->post_content = 'No jobs shortcode';
check( lam_home_search_route()['url'], 'https://example.test/#lam-home-jobs', 'Unimplemented Jobs page does not swallow search' );

// Demo inserts operate only on this in-memory fixture. Guards throw before writes.
function wp_slash( $value ) { return $value; }
function get_posts( $args ) {
    global $db;
    $statement = $db->prepare( 'SELECT post_id FROM wp_postmeta WHERE meta_key=? AND meta_value=?' );
    $statement->execute( array( $args['meta_key'], $args['meta_value'] ) );
    return $statement->fetchAll( PDO::FETCH_COLUMN );
}
function wp_insert_post( $record ) {
    global $db;
    $id = (int) $db->query( 'SELECT MAX(ID)+1 FROM wp_posts' )->fetchColumn();
    fixture( $id, $record['post_title'], '2026-10-07', $record['meta_input'], $record['post_status'], $record['post_type'] );
    return $id;
}
function current_user_can() { global $demo_permission; return $demo_permission; }
function check_admin_referer() { throw new RuntimeException( '403' ); }
function wp_die( $message, $title = '', $args = array() ) { throw new RuntimeException( (string) $args['response'] ); }
require dirname( __DIR__ ) . '/demo.php';
$record = array( 'post_type' => 'job_listing', 'post_title' => 'Demo original' );
$first = lam_home_demo_insert( 'idempotent', $record );
check( $first > 0, true, 'Administrator seed can create a fixture record' );
$record['post_title'] = 'Must not overwrite';
check( lam_home_demo_insert( 'idempotent', $record ), 0, 'Repeated seed skips existing record' );
check( $db->query( 'SELECT post_title FROM wp_posts WHERE ID=' . $first )->fetchColumn(), 'Demo original', 'Seed preserves existing content' );
foreach ( array( array( 'GET', true, '405' ), array( 'POST', false, '403' ), array( 'POST', true, '403' ) ) as $case ) {
    $_SERVER['REQUEST_METHOD'] = $case[0];
    $demo_permission = $case[1];
    try { lam_home_demo_create(); throw new RuntimeException( 'Guard did not fire' ); }
    catch ( RuntimeException $error ) { check( $error->getMessage(), $case[2], 'Demo guard: ' . $case[0] . ', permission=' . (int) $case[1] ); }
}
echo "All Home search/data checks passed; project database was not opened.\n";
