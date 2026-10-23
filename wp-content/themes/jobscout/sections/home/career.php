<?php defined( 'ABSPATH' ) || exit; $about_url = lam_home_destination( 'about' ); ?>
<section class="lam-home-career" aria-labelledby="lam-home-career-title" style="<?php echo esc_attr( lam_home_background( 'career' ) ); ?>">
    <div class="lam-home-career-inner">
        <h2 id="lam-home-career-title" class="lam-home-section-title">CAREER WITH US</h2>
        <p class="lam-home-career-copy">Plan Do See Global is a hospitality group founded in Japan and rooted in “Omotenashi”, the Japanese principle of<br class="lam-home-desktop-break"> selfless hospitality. We strive to deliver unforgettable and bespoke experiences, to understand local cultures like<br class="lam-home-desktop-break"> natives, to provide service that is warm but not intrusive, and to foresee our guests’ every need at all times.<br class="lam-home-desktop-break"> That is our sole mission and purpose.</p>
        <p class="lam-home-career-copy">We are experts in all stages of project development: concept, design, implementation and management.<br class="lam-home-desktop-break"> We love to find unique ways to create experiences that surprise and delight guests and customers.</p>
        <?php if ( $about_url ) : ?>
            <a class="lam-home-outline-button lam-home-about-button" href="<?php echo esc_url( $about_url ); ?>">MORE ABOUT US</a>
        <?php else : ?>
            <span class="lam-home-outline-button lam-home-about-button" aria-disabled="true">MORE ABOUT US</span>
        <?php endif; ?>
    </div>
</section>
