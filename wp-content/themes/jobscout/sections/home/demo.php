<?php
/** Optional administrator-triggered data; never executed by a Home visit. */
defined( 'ABSPATH' ) || exit;

function lam_home_demo_menu() {
    add_management_page( 'Home sample data', 'Home sample data', 'manage_options', 'lam-home-demo', 'lam_home_demo_screen' );
}
add_action( 'admin_menu', 'lam_home_demo_menu' );

function lam_home_demo_screen() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <div class="wrap">
        <h1>Home sample data</h1>
        <p>Create 6 published sample jobs and 4 sample blog entries for Home. Existing sample records are skipped, including trashed records. No real records, pages, menus or shared settings are modified.</p>
        <p>The original company logos and photographs are not included. Add them through WordPress after creating the records. Jobs use the existing job_listing post type; install/activate your project's WP Job Manager provider first.</p>
        <?php if ( isset( $_GET['lam_home_demo_done'] ) ) : ?><div class="notice notice-success"><p>Sample data operation completed. Existing records were preserved.</p></div><?php endif; ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="lam_home_demo">
            <?php wp_nonce_field( 'lam_home_demo' ); ?>
            <?php submit_button( 'Create sample data', 'primary', 'submit', true, post_type_exists( 'job_listing' ) ? array() : array( 'disabled' => 'disabled' ) ); ?>
        </form>
    </div>
    <?php
}

function lam_home_demo_insert( $key, $record, $meta = array() ) {
    $existing = get_posts( array( 'post_type' => $record['post_type'], 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'meta_key' => '_lam_home_demo_key', 'meta_value' => $key, 'fields' => 'ids', 'posts_per_page' => 1, 'no_found_rows' => true ) );
    if ( $existing ) return 0;
    $record['post_name'] = 'lam-home-demo-' . $key;
    $record['post_status'] = 'publish';
    $record['meta_input'] = array_merge( $meta, array( '_lam_home_demo_key' => $key ) );
    return wp_insert_post( wp_slash( $record ), true );
}

function lam_home_demo_create() {
    if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) wp_die( 'Use the sample data form.', '', array( 'response' => 405 ) );
    if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'publish_posts' ) ) wp_die( 'Insufficient permissions.', '', array( 'response' => 403 ) );
    check_admin_referer( 'lam_home_demo' );
    $type = get_post_type_object( 'job_listing' );
    if ( ! $type || ! current_user_can( $type->cap->publish_posts ) ) wp_die( 'Activate the existing job_listing provider and check publishing permissions first.', '', array( 'response' => 400 ) );
    $jobs = array(
        'hotel-manager' => array( 'Hotel Manager', 'The Soodoh' ),
        'general-manager' => array( 'General Manager - Leading Hotel Chain', 'Fortune Garden' ),
        'banquet-manager' => array( 'Banquet Manager', 'Shikaku' ),
        'bellman' => array( 'Bellman', 'The Saigon House' ),
        'chief-operating-officer' => array( 'Chief Operating Officer Hotel/ Resort Chain', 'Pho Thin' ),
        'loss-prevention-officer' => array( 'Loss Prevention Officer', 'Nowhere' ),
    );
    $errors = false;
    foreach ( $jobs as $key => $job ) {
        $id = lam_home_demo_insert( $key, array( 'post_type' => 'job_listing', 'post_title' => $job[0], 'post_content' => '<ul><li>Be responsible for the effective operational management of the hotel</li><li>Excellent salary bonuses &amp; recognition activities</li><li>Foreign language allowance (up to 500USD/ month)</li></ul>' ), array( '_company_name' => $job[1], '_job_location' => 'Ho Chi Minh City', '_featured' => in_array( $key, array( 'hotel-manager', 'general-manager' ), true ) ? 1 : 0, '_filled' => 0, '_job_expires' => wp_date( 'Y-m-d', time() + 90 * DAY_IN_SECONDS ) ) );
        if ( is_wp_error( $id ) ) { $errors = true; continue; }
        if ( $id ) {
            if ( taxonomy_exists( 'job_listing_type' ) && is_wp_error( wp_set_object_terms( $id, 'Fulltime', 'job_listing_type' ) ) ) $errors = true;
            if ( taxonomy_exists( 'job_listing_category' ) && is_wp_error( wp_set_object_terms( $id, 'Hospitality', 'job_listing_category' ) ) ) $errors = true;
        }
    }
    foreach ( array( 'Project Development', 'Restaurant & Hotel Management And Operations', 'Hospitality Consulting', 'Venue And Interior Design' ) as $title ) {
        $id = lam_home_demo_insert( sanitize_title( $title ), array( 'post_type' => 'post', 'post_title' => $title, 'post_excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'post_content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>' ) );
        if ( is_wp_error( $id ) ) $errors = true;
    }
    if ( $errors ) wp_die( 'Some sample records could not be created. Existing records were preserved. You may retry; completed records will be skipped.' );
    wp_safe_redirect( add_query_arg( array( 'page' => 'lam-home-demo', 'lam_home_demo_done' => 1 ), admin_url( 'tools.php' ) ) );
    exit;
}
add_action( 'admin_post_lam_home_demo', 'lam_home_demo_create' );
