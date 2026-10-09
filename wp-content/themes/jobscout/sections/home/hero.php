<?php
defined( 'ABSPATH' ) || exit;
$filters = $args['filters'];
$locations = $args['locations'];
$route = $args['route'];
$selected_location = $filters['search_location'];
if ( ! isset( $_GET['search_location'] ) && isset( $locations['Tokyo'] ) ) $selected_location = 'Tokyo';
?>
<section class="lam-home-hero" aria-labelledby="lam-home-title" style="<?php echo esc_attr( lam_home_background( 'hero' ) ); ?>">
    <div class="lam-home-hero-inner">
        <h1 id="lam-home-title" class="lam-home-title">FIND YOUR DREAM JOBS</h1>
        <p class="lam-home-intro">The secret behind our company is simple: to always put ourselves in the other person’s shoes-employee, guest or<br class="lam-home-desktop-break"> customer. This allows us to see the world through their eyes, anticipate their needs and better understand their feelings.</p>
        <form class="lam-home-search" method="get" action="<?php echo esc_url( $route['url'] ); ?>" role="search" aria-label="<?php esc_attr_e( 'Search jobs', 'jobscout' ); ?>">
            <?php foreach ( $route['hidden'] as $key => $value ) : if ( is_scalar( $value ) ) : ?>
                <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>">
            <?php endif; endforeach; ?>
            <div class="lam-home-search-field lam-home-keyword-field">
                <label class="screen-reader-text" for="lam-home-keywords"><?php esc_html_e( 'Keywords', 'jobscout' ); ?></label>
                <i class="fas fa-search lam-home-field-icon" aria-hidden="true"></i>
                <input class="lam-home-search-input" id="lam-home-keywords" name="search_keywords" type="search" maxlength="200" value="<?php echo esc_attr( $filters['search_keywords'] ); ?>" placeholder="Search for jobs, companies, skills">
            </div>
            <div class="lam-home-search-field lam-home-location-field">
                <label class="screen-reader-text" for="lam-home-location"><?php esc_html_e( 'Location', 'jobscout' ); ?></label>
                <i class="fas fa-map-marker-alt lam-home-field-icon" aria-hidden="true"></i>
                <select class="lam-home-location" id="lam-home-location" name="search_location">
                    <option value=""><?php esc_html_e( 'All locations', 'jobscout' ); ?></option>
                    <?php if ( $filters['search_location'] && ! isset( $locations[ $filters['search_location'] ] ) ) : ?>
                        <option value="<?php echo esc_attr( $filters['search_location'] ); ?>" selected><?php echo esc_html( $filters['search_location'] ); ?></option>
                    <?php endif; ?>
                    <?php foreach ( $locations as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $selected_location, $value ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="lam-home-search-submit" type="submit">SEARCH JOB</button>
        </form>
    </div>
</section>
