<?php
/**
 * News Detail Module Loader
 * Handles assets and hooks for single post news detail.
 *
 * @package JobScout
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Enqueue assets for single post news detail */
function nhomg_news_detail_assets() {
    if ( is_singular( 'post' ) ) {
        $dir = get_template_directory();
        $uri = get_template_directory_uri();

        // Enqueue news.css for the .news-card shared grid styles
        $news_css = $dir . '/news.css';
        $news_ver = file_exists( $news_css ) ? filemtime( $news_css ) : '1.0';
        wp_enqueue_style( 'news-page', $uri . '/news.css', array( 'jobscout' ), $news_ver );

        // Enqueue news-detail.css
        $nd_css = $dir . '/news-detail/news-detail.css';
        $nd_ver = file_exists( $nd_css ) ? filemtime( $nd_css ) : '1.0';
        wp_enqueue_style( 'news-detail', $uri . '/news-detail/news-detail.css', array( 'jobscout', 'news-page' ), $nd_ver );

        // Enqueue news-detail.js
        $nd_js  = $dir . '/news-detail/news-detail.js';
        $nd_jver = file_exists( $nd_js ) ? filemtime( $nd_js ) : '1.0';
        wp_enqueue_script( 'news-detail', $uri . '/news-detail/news-detail.js', array(), $nd_jver, true );
    }
}
add_action( 'wp_enqueue_scripts', 'nhomg_news_detail_assets', 20 );

/** Add body class to single post to make it full width and remove sidebar */
function nhomg_news_detail_body_class( $classes ) {
    if ( is_singular( 'post' ) ) {
        $classes   = array_diff( $classes, array( 'rightsidebar', 'leftsidebar' ) );
        $classes[] = 'full-width';
        $classes[] = 'nhomg-news-detail-page';
    }
    return $classes;
}
add_filter( 'body_class', 'nhomg_news_detail_body_class', 99 );

