<?php
/** Shared header markup. @package JobScout */
$cmsng_recruiting = get_theme_mod( 'cmsng_recruiting', 'RECRUITING' );
$cmsng_submit_url = cmsng_submit_job_url();
$cmsng_submit_label = get_theme_mod( 'post_job_label', '' );
$cmsng_submit_label = $cmsng_submit_label ? $cmsng_submit_label : 'SUBMIT JOB';
?>
<header id="masthead" class="cmsng-header" itemscope itemtype="https://schema.org/WPHeader">
    <div class="container cmsng-header-inner">
        <div class="cmsng-brand">
            <?php cmsng_logo(); ?>
            <?php if ( $cmsng_recruiting ) : ?>
                <span class="cmsng-recruiting"><?php echo esc_html( $cmsng_recruiting ); ?></span>
            <?php endif; ?>
        </div>
        <button class="cmsng-menu-toggle" type="button" aria-expanded="false" aria-controls="cmsng-header-navigation" hidden>
            <span class="cmsng-menu-bars" aria-hidden="true"></span>
            <span class="screen-reader-text"><?php esc_html_e( 'Toggle navigation', 'jobscout' ); ?></span>
        </button>
        <nav id="cmsng-header-navigation" class="cmsng-header-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'jobscout' ); ?>">
            <?php wp_nav_menu( array(
                'theme_location' => 'primary',
                'container' => false,
                'menu_id' => 'cmsng-primary-menu',
                'menu_class' => 'cmsng-menu',
                'fallback_cb' => 'cmsng_menu_fallback',
            ) ); ?>
            <?php if ( $cmsng_submit_url ) : ?>
                <a class="cmsng-submit-job" href="<?php echo esc_url( $cmsng_submit_url ); ?>"><?php echo esc_html( $cmsng_submit_label ); ?></a>
            <?php else : ?>
                <span class="cmsng-submit-job cmsng-unconfigured" aria-disabled="true"><?php echo esc_html( $cmsng_submit_label ); ?></span>
            <?php endif; ?>
        </nav>
    </div>
</header>
