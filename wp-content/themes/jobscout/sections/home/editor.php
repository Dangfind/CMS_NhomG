<?php
/** Editable Home positions; no automatic data creation or updates. */
defined( 'ABSPATH' ) || exit;
add_action( 'add_meta_boxes', static function () {
    foreach ( array( 'post', 'job_listing' ) as $type ) {
        add_meta_box( 'lam-home-order', __( 'Home position', 'jobscout' ), 'lam_home_order_box', $type, 'side' );
    }
} );
function lam_home_order_box( $post ) {
    wp_nonce_field( 'lam_home_order', 'lam_home_order_nonce' );
    echo '<p><label for="lam-home-order">' . esc_html__( 'Position on Home (1–9999)', 'jobscout' ) . '</label></p>';
    echo '<input id="lam-home-order" type="number" name="lam_home_order" min="1" max="9999" value="' . esc_attr( get_post_meta( $post->ID, '_lam_home_order', true ) ) . '">';
    echo '<p>' . esc_html__( 'Lower numbers appear first. Leave empty for automatic featured/newest ordering. Only Home uses this setting.', 'jobscout' ) . '</p>';
}
add_action( 'save_post', static function ( $id ) {
    if ( ! isset( $_POST['lam_home_order_nonce'], $_POST['lam_home_order'] ) || ! is_string( $_POST['lam_home_order_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lam_home_order_nonce'] ) ), 'lam_home_order' ) ) return;
    if ( wp_is_post_autosave( $id ) || wp_is_post_revision( $id ) || ! current_user_can( 'edit_post', $id ) || ! in_array( get_post_type( $id ), array( 'post', 'job_listing' ), true ) ) return;
    if ( ! is_string( $_POST['lam_home_order'] ) ) return;
    $value = trim( wp_unslash( $_POST['lam_home_order'] ) );
    if ( '' === $value ) delete_post_meta( $id, '_lam_home_order' );
    elseif ( ctype_digit( $value ) && (int) $value >= 1 && (int) $value <= 9999 ) update_post_meta( $id, '_lam_home_order', (int) $value );
} );
