<?php
/**
 * Template Name: News
 *
 * @package JobScout
 */

get_header();

// Hero image: featured image of page or fallback to contact-hero.jpg
$hero_image = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'full' ) : get_template_directory_uri() . '/images/contact-hero.jpg';

// Banner title: page title or fallback "FDS NEWS"
$banner_title = get_the_title();
if ( empty( $banner_title ) || is_home() ) {
    $banner_title = 'FDS NEWS';
}
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main nhomg-news-main">

        <!-- Hero Banner Fullbleed -->
        <section class="nhomg-fullbleed news-hero-banner" style="background-image: url('<?php echo esc_url( $hero_image ); ?>');">
            <div class="news-hero-overlay"></div>
            <div class="news-hero-content">
                <h1 class="news-hero-title"><?php echo esc_html( $banner_title ); ?></h1>
            </div>
        </section>

        <!-- Main Blog Entries Section -->
        <section class="nhomg-fullbleed news-entries-section">
            <div class="news-container">
                <h2 class="news-section-title"><?php esc_html_e( 'NEWEST BLOG ENTRIES', 'jobscout' ); ?></h2>

                <div class="news-grid">
                    <?php
                    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
                    $news_query = new WP_Query( array(
                        'post_type'      => 'post',
                        'post_status'    => 'publish',
                        'posts_per_page' => 8,
                        'paged'          => $paged,
                    ) );

                    if ( $news_query->have_posts() ) :
                        while ( $news_query->have_posts() ) : $news_query->the_post();
                            $post_thumb = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium' ) : get_template_directory_uri() . '/images/contact-hero.jpg';
                            ?>
                            <article id="post-<?php the_ID(); ?>" <?php post_class( 'news-card' ); ?>>
                                <div class="news-card-thumb">
                                    <a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                                        <img src="<?php echo esc_url( $post_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                                    </a>
                                </div>
                                <div class="news-card-body">
                                    <div class="news-card-content">
                                        <h3 class="news-card-title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h3>
                                        <p class="news-card-excerpt">
                                            <?php
                                            $excerpt = get_the_excerpt();
                                            if ( empty( trim( strip_tags( (string) $excerpt ) ) ) ) {
                                                $excerpt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
                                            } else {
                                                $excerpt = wp_trim_words( $excerpt, 18, '...' );
                                            }
                                            echo esc_html( $excerpt );
                                            ?>
                                        </p>
                                    </div>
                                    <div class="news-card-footer">
                                        <a href="<?php the_permalink(); ?>" class="news-read-more"><?php esc_html_e( 'Read More', 'jobscout' ); ?></a>
                                    </div>
                                </div>
                            </article>
                            <?php
                        endwhile;
                    else :
                        ?>
                        <p class="news-no-posts"><?php esc_html_e( 'No posts found.', 'jobscout' ); ?></p>
                        <?php
                    endif;
                    ?>
                </div>

                <?php if ( $news_query->have_posts() && $news_query->max_num_pages > 1 ) : ?>
                    <nav class="news-pagination" aria-label="<?php esc_attr_e( 'News pagination', 'jobscout' ); ?>">
                        <?php
                        echo paginate_links( array(
                            'total'     => $news_query->max_num_pages,
                            'current'   => $paged,
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                        ) );
                        ?>
                    </nav>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>

            </div>
        </section>

    </main>
</div>

<?php
get_footer();

