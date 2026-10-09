<?php
/**
 * CLI-only repair of the existing reference content.
 * Dry run: php sections/home/tools/repair-reference.php
 * Apply:   php sections/home/tools/repair-reference.php --apply
 * Resolves records/assets by slug/filename; never deletes duplicate posts.
 */
if ( PHP_SAPI !== 'cli' ) { http_response_code( 404 ); exit; }
require dirname( __DIR__, 6 ) . '/wp-load.php';
function lam_repair_post( $type, $slug ) {
    $posts = get_posts( array( 'post_type' => $type, 'post_status' => 'publish', 'name' => $slug, 'numberposts' => -1 ) );
    if ( ! $posts ) throw new RuntimeException( 'Missing post: ' . $slug );
    // When slugs collide, select the record that the real permalink opens.
    $id = url_to_postid( get_permalink( $posts[0] ) );
    $post = get_post( $id );
    if ( ! $post || $post->post_type !== $type || $post->post_name !== $slug ) throw new RuntimeException( 'Ambiguous permalink: ' . $slug );
    return $post;
}
function lam_repair_image( $filename ) {
    $ids = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_wp_attached_file', 'value' => '/' . $filename, 'compare' => 'LIKE' ) ) ) );
    $ids = array_values( array_filter( $ids, static function ( $id ) use ( $filename ) {
        return basename( get_post_meta( $id, '_wp_attached_file', true ) ) === $filename && wp_attachment_is_image( $id ) && is_file( get_attached_file( $id ) );
    } ) );
    if ( count( $ids ) !== 1 ) throw new RuntimeException( 'Missing/ambiguous attachment: ' . $filename );
    return $ids[0];
}
$job_slugs = array( 'hotel-manager', 'general-manager-leading-hotel-chain', 'banquet-manager', 'bellman', 'chief-operating-officer-hotel-resort-chain', 'loss-prevention-officer' );
$news_images = array(
    'project-development' => 'project-development.png',
    'restaurant-hotel-management-and-operations' => 'restaurant-hotel-management.png',
    'hospitality-consulting' => 'hospitality-consulting.png',
    'venue-and-interior-design' => 'venue-interior-design.png',
);
$excerpt = lam_repair_post( 'post', 'project-development' )->post_excerpt;
if ( ! trim( $excerpt ) ) throw new RuntimeException( 'Missing reference excerpt; no replacement text will be invented.' );
$plan = array();
foreach ( $job_slugs as $index => $slug ) {
    $post = lam_repair_post( 'job_listing', $slug );
    if ( ! has_post_thumbnail( $post ) || ! trim( get_post_meta( $post->ID, '_nhomg_highlights', true ) ) ) throw new RuntimeException( 'Missing job logo/highlights: ' . $slug );
    $plan[] = array( 'id' => $post->ID, 'slug' => $slug, 'order' => $index + 1 );
}
$index = 0;
foreach ( $news_images as $slug => $filename ) {
    $post = lam_repair_post( 'post', $slug );
    $plan[] = array( 'id' => $post->ID, 'slug' => $slug, 'order' => ++$index, 'thumbnail' => lam_repair_image( $filename ), 'excerpt' => trim( $post->post_excerpt ) ? $post->post_excerpt : $excerpt );
}
$apply = in_array( '--apply', $argv, true );
if ( $apply ) {
    $backup = array( 'created_utc' => gmdate( 'c' ), 'records' => array() );
    foreach ( $plan as $record ) {
        $post = get_post( $record['id'] );
        $backup['records'][] = array( 'ID' => $post->ID, 'post_title' => $post->post_title, 'post_excerpt' => $post->post_excerpt, 'home_order' => get_post_meta( $post->ID, '_lam_home_order', false ), 'thumbnail' => get_post_meta( $post->ID, '_thumbnail_id', false ) );
    }
    $backup_path = tempnam( sys_get_temp_dir(), 'cms-nhomg-home-' );
    if ( ! $backup_path || false === file_put_contents( $backup_path, wp_json_encode( $backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ) ) throw new RuntimeException( 'Backup failed; no data was changed.' );
    foreach ( $plan as $record ) {
        update_post_meta( $record['id'], '_lam_home_order', $record['order'] );
        if ( isset( $record['thumbnail'] ) ) {
            set_post_thumbnail( $record['id'], $record['thumbnail'] );
            if ( get_post_field( 'post_excerpt', $record['id'] ) !== $record['excerpt'] ) {
                $result = wp_update_post( wp_slash( array( 'ID' => $record['id'], 'post_excerpt' => $record['excerpt'] ) ), true );
                if ( is_wp_error( $result ) ) throw new RuntimeException( $result->get_error_message() );
            }
        }
    }
    echo 'Backup: ' . $backup_path . PHP_EOL;
}
echo wp_json_encode( array( 'applied' => $apply, 'plan' => $plan ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
