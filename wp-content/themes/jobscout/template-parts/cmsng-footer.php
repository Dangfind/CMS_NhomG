<?php
/** Shared newsletter and footer. @package JobScout */
// Form Newsletter gửi tới plugin "Newsletter" (nếu plugin đang bật).
$cmsng_newsletter_action = class_exists( 'NewsletterSubscription' ) ? admin_url( 'admin-ajax.php?action=tnp&na=s' ) : '';
$cmsng_socials = array( 'facebook' => array( 'Facebook', 'facebook-f' ), 'google' => array( 'Google', 'google' ), 'line' => array( 'LINE', 'line' ), 'twitter' => array( 'Twitter', 'twitter' ) );
?>
<footer id="colophon" class="cmsng-footer" itemscope itemtype="https://schema.org/WPFooter">
    <section class="cmsng-newsletter" aria-labelledby="cmsng-newsletter-title">
        <div class="cmsng-newsletter-inner">
            <h2 id="cmsng-newsletter-title" class="cmsng-newsletter-title">Subscribe To<br>Our Newsletter</h2>
            <form class="cmsng-newsletter-form" data-cmsng-newsletter aria-describedby="cmsng-newsletter-status"<?php if ( $cmsng_newsletter_action ) : ?> method="post" action="<?php echo esc_url( $cmsng_newsletter_action ); ?>"<?php endif; ?>>
                <?php if ( $cmsng_newsletter_action ) : ?><input type="hidden" name="nr" value="footer"><input type="hidden" name="nlang" value=""><?php endif; ?>
                <div class="cmsng-email-field">
                    <label class="screen-reader-text" for="cmsng-newsletter-email"><?php esc_html_e( 'Email address', 'jobscout' ); ?></label>
                    <i class="far fa-envelope cmsng-email-icon" aria-hidden="true"></i>
                    <input id="cmsng-newsletter-email" class="cmsng-email" type="email" name="<?php echo $cmsng_newsletter_action ? 'ne' : 'email'; ?>" autocomplete="email" placeholder="<?php esc_attr_e( 'Input your email address', 'jobscout' ); ?>" required>
                </div>
                <button class="cmsng-subscribe" type="submit" disabled>SUBSCRIBE</button>
                <p id="cmsng-newsletter-status" class="cmsng-newsletter-status" role="status" aria-live="polite" data-unavailable="<?php esc_attr_e( 'Newsletter signup is not available yet. Your email has not been saved or sent.', 'jobscout' ); ?>" hidden></p>
                <noscript><p class="cmsng-newsletter-status"><?php esc_html_e( 'Newsletter signup is not available yet. Your email has not been saved or sent.', 'jobscout' ); ?></p></noscript>
            </form>
        </div>
    </section>
    <div class="cmsng-footer-main">
        <div class="cmsng-footer-inner">
            <div class="cmsng-footer-brand"><?php cmsng_logo(); ?></div>
            <nav class="cmsng-footer-navigation" aria-label="<?php esc_attr_e( 'Footer navigation', 'jobscout' ); ?>">
                <?php wp_nav_menu( array(
                    'theme_location' => 'cmsng_footer',
                    'container' => false,
                    'menu_id' => 'cmsng-footer-menu',
                    'menu_class' => 'cmsng-footer-menu',
                    'fallback_cb' => 'cmsng_menu_fallback',
                    'depth' => 1,
                ) ); ?>
            </nav>
            <ul class="cmsng-socials" aria-label="<?php esc_attr_e( 'Social media', 'jobscout' ); ?>">
                <?php foreach ( $cmsng_socials as $cmsng_key => $cmsng_social ) :
                    $cmsng_url = esc_url( get_theme_mod( 'cmsng_social_' . $cmsng_key, '' ) );
                    $cmsng_url = '#' === substr( $cmsng_url, 0, 1 ) ? '' : $cmsng_url; ?>
                    <li>
                        <?php if ( $cmsng_url ) : ?>
                            <a class="cmsng-social cmsng-social-<?php echo esc_attr( $cmsng_key ); ?>" href="<?php echo esc_url( $cmsng_url ); ?>" aria-label="<?php echo esc_attr( $cmsng_social[0] ); ?>">
                                <?php if ( 'google' === $cmsng_key ) : ?>
                                    <img src="<?php echo esc_url( get_template_directory_uri() . '/images/google-g.svg' ); ?>" width="24" height="24" alt="" aria-hidden="true">
                                <?php else : ?>
                                    <i class="fab fa-<?php echo esc_attr( $cmsng_social[1] ); ?>" aria-hidden="true"></i>
                                <?php endif; ?>
                            </a>
                        <?php else : ?>
                            <span class="cmsng-social cmsng-social-<?php echo esc_attr( $cmsng_key ); ?>" aria-disabled="true" role="img" aria-label="<?php echo esc_attr( $cmsng_social[0] . ' — ' . __( 'link not configured', 'jobscout' ) ); ?>">
                                <?php if ( 'google' === $cmsng_key ) : ?>
                                    <img src="<?php echo esc_url( get_template_directory_uri() . '/images/google-g.svg' ); ?>" width="24" height="24" alt="" aria-hidden="true">
                                <?php else : ?>
                                    <i class="fab fa-<?php echo esc_attr( $cmsng_social[1] ); ?>" aria-hidden="true"></i>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</footer>
