<?php

/** Shared newsletter and footer. @package JobScout */
// Form Newsletter gửi tới plugin "Newsletter" (nếu plugin đang bật).
$cmsng_newsletter_action = class_exists('NewsletterSubscription') ? admin_url('admin-ajax.php?action=tnp&na=s') : '';
$cmsng_socials = array('facebook' => array('Facebook', 'facebook-f'), 'google' => array('Google', 'google'), 'line' => array('LINE', 'line'), 'twitter' => array('Twitter', 'twitter'));
?>
<footer id="colophon" class="cmsng-footer" itemscope itemtype="https://schema.org/WPFooter">
    <section class="cmsng-newsletter" aria-labelledby="cmsng-newsletter-title">
        <div class="cmsng-newsletter-inner">
            <h2 id="cmsng-newsletter-title" class="cmsng-newsletter-title">Subscribe To<br>Our Newsletter</h2>
            <form class="cmsng-newsletter-form" data-cmsng-newsletter aria-describedby="cmsng-newsletter-status" <?php if ($cmsng_newsletter_action) : ?> method="post" action="<?php echo esc_url($cmsng_newsletter_action); ?>" <?php endif; ?>>
                <?php if ($cmsng_newsletter_action) : ?><input type="hidden" name="nr" value="footer"><input type="hidden" name="nlang" value=""><?php endif; ?>
                <div class="cmsng-email-field">
                    <label class="screen-reader-text" for="cmsng-newsletter-email"><?php esc_html_e('Email address', 'jobscout'); ?></label>
                    <i class="far fa-envelope cmsng-email-icon" aria-hidden="true"></i>
                    <input id="cmsng-newsletter-email" class="cmsng-email" type="email" name="<?php echo $cmsng_newsletter_action ? 'ne' : 'email'; ?>" autocomplete="email" placeholder="<?php esc_attr_e('Input your email address', 'jobscout'); ?>" required>
                </div>
                <button class="cmsng-subscribe" type="submit" disabled>SUBSCRIBE</button>
                <p id="cmsng-newsletter-status" class="cmsng-newsletter-status" role="status" aria-live="polite" data-unavailable="<?php esc_attr_e('Newsletter signup is not available yet. Your email has not been saved or sent.', 'jobscout'); ?>" hidden></p>
                <noscript>
                    <p class="cmsng-newsletter-status"><?php esc_html_e('Newsletter signup is not available yet. Your email has not been saved or sent.', 'jobscout'); ?></p>
                </noscript>
            </form>
        </div>
    </section>
    <div class="cmsng-footer-main">
        <div class="cmsng-footer-inner">
            <div class="cmsng-footer-brand"><?php cmsng_logo(); ?></div>
            <nav class="cmsng-footer-navigation" aria-label="<?php esc_attr_e('Footer navigation', 'jobscout'); ?>">
                <?php wp_nav_menu(array(
                    'theme_location' => 'cmsng_footer',
                    'container' => false,
                    'menu_id' => 'cmsng-footer-menu',
                    'menu_class' => 'cmsng-footer-menu',
                    'fallback_cb' => 'cmsng_menu_fallback',
                    'depth' => 1,
                )); ?>
            </nav>
            <?php
            if (shortcode_exists('Sassy_Social_Share')) {
                echo do_shortcode('[Sassy_Social_Share]');
            }
            ?>
        </div>
    </div>
</footer>