<?php
/**
 * Newsletter và footer.
 *
 * @package JobScout
 */

defined('ABSPATH') || exit;
?>

<footer
    id="colophon"
    class="cmsng-footer"
    itemscope
    itemtype="https://schema.org/WPFooter"
>
    <section
        class="cmsng-newsletter"
        aria-labelledby="cmsng-newsletter-title"
    >
        <div class="cmsng-newsletter-inner">

            <h2
                id="cmsng-newsletter-title"
                class="cmsng-newsletter-title"
            >
                Subscribe To<br>Our Newsletter
            </h2>

            <div class="cmsng-newsletter-form cmsng-newsletter-cf7">
                <?php
                if (shortcode_exists('contact-form-7')) {
                    echo do_shortcode(
                        '[contact-form-7 id="faa0341" title="Newsletter"]'
                    );
                } else {
                    echo '<p>'
                        . esc_html__(
                            'Vui lòng kích hoạt Contact Form 7.',
                            'jobscout'
                        )
                        . '</p>';
                }
                ?>
            </div>

        </div>
    </section>

    <div class="cmsng-footer-main">
        <div class="cmsng-footer-inner">

            <div class="cmsng-footer-brand">
                <?php cmsng_logo(); ?>
            </div>

            <nav
                class="cmsng-footer-navigation"
                aria-label="<?php
                    esc_attr_e('Footer navigation', 'jobscout');
                ?>"
            >
                <?php
                wp_nav_menu([
                    'theme_location' => 'cmsng_footer',
                    'container'      => false,
                    'menu_id'        => 'cmsng-footer-menu',
                    'menu_class'     => 'cmsng-footer-menu',
                    'fallback_cb'    => 'cmsng_menu_fallback',
                    'depth'          => 1,
                ]);
                ?>
            </nav>

            <?php
            if (shortcode_exists('Sassy_Social_Share')) {
                echo do_shortcode('[Sassy_Social_Share]');
            }
            ?>

        </div>
    </div>
</footer>