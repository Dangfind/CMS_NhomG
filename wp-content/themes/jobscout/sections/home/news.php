<?php
defined( 'ABSPATH' ) || exit;
$news = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'has_password' => false, 'posts_per_page' => 4, 'ignore_sticky_posts' => true, 'orderby' => array( 'date' => 'DESC', 'ID' => 'DESC' ), 'no_found_rows' => true ) );
?>
<section class="lam-home-news" aria-labelledby="lam-home-news-title">
    <div class="lam-home-container">
        <h2 id="lam-home-news-title" class="lam-home-section-title">NEWEST BLOG ENTRIES</h2>
        <?php if ( $news->have_posts() ) : ?>
            <div class="lam-home-news-grid">
                <?php while ( $news->have_posts() ) : $news->the_post();
                    get_template_part( 'sections/home/news-card', null, array( 'post' => get_post() ) );
                endwhile; ?>
            </div>
        <?php else : ?>
            <p class="lam-home-empty"><?php esc_html_e( 'There are no blog entries yet. Please check back soon.', 'jobscout' ); ?></p>
        <?php endif; wp_reset_postdata(); ?>
    </div>
</section>
