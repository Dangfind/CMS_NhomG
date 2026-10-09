<?php
/**
 * News Detail Content Template Part
 * Follows mockup in 6-news detail.png
 *
 * @package JobScout
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$post_id    = get_the_ID();
$post_thumb = has_post_thumbnail( $post_id ) 
    ? get_the_post_thumbnail_url( $post_id, 'medium' ) 
    : get_template_directory_uri() . '/images/contact-hero.jpg';

$categories = get_the_category( $post_id );
$news_url   = function_exists( 'cmsng_section_url' ) ? cmsng_section_url( 'news' ) : home_url( '/news/' );
if ( ! $news_url ) {
    $news_url = home_url( '/news/' );
}
?>

<div class="news-detail-wrapper">
    <div class="news-detail-container">

        <!-- Breadcrumb -->
        <nav class="news-detail-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'jobscout' ); ?>">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'jobscout' ); ?></a>
            <span class="separator">/</span>
            <a href="<?php echo esc_url( $news_url ); ?>"><?php esc_html_e( 'All News', 'jobscout' ); ?></a>
            <span class="separator">/</span>
            <span class="current"><?php esc_html_e( 'News Detail', 'jobscout' ); ?></span>
        </nav>

        <!-- Post Header Card -->
        <header class="news-detail-header-card">
            <div class="news-detail-header-left">
                <div class="news-detail-header-thumb">
                    <img src="<?php echo esc_url( $post_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                </div>
                <div class="news-detail-header-info">
                    <h1 class="news-detail-title"><?php the_title(); ?></h1>
                    <p class="news-detail-date">
                        <?php echo esc_html( sprintf( __( 'Posted: %s', 'jobscout' ), get_the_date( 'M j, Y' ) ) ); ?>
                    </p>
                    <div class="news-detail-pills">
                        <?php 
                        if ( ! empty( $categories ) ) :
                            foreach ( $categories as $cat ) :
                                ?>
                                <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="news-detail-pill">
                                    <?php echo esc_html( $cat->name ); ?>
                                </a>
                                <?php
                            endforeach;
                        else :
                            ?>
                            <span class="news-detail-pill"><?php esc_html_e( 'Category Name', 'jobscout' ); ?></span>
                        <?php endif; ?>
                        
                        <?php
                        $location = get_post_meta( $post_id, '_location', true );
                        if ( $location ) :
                            ?>
                            <span class="news-detail-pill"><?php echo esc_html( $location ); ?></span>
                        <?php else : ?>
                            <span class="news-detail-pill"><?php esc_html_e( 'Ho Chi Minh City', 'jobscout' ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="news-detail-header-right">
                <button type="button" class="news-detail-share-btn">
                    <i class="fas fa-share-alt" aria-hidden="true"></i>
                    <span><?php esc_html_e( 'SHARE', 'jobscout' ); ?></span>
                </button>
            </div>
        </header>

        <!-- Post Content Card -->
        <article id="post-<?php the_ID(); ?>" class="news-detail-content-box entry-content">
            <?php
            $raw_content = get_the_content();
            if ( ! empty( trim( strip_tags( $raw_content ) ) ) ) {
                the_content();
            } else {
                // Fallback content đẹp mắt theo đúng mẫu 6-news detail.png khi bài viết chưa soạn nội dung
                ?>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Faucibus lectus tristique massa gravida vel elementum, mi. Sit scelerisque at amet leo. In volutpat turpis dolor, at. Vivamus volutpat in nunc, porttitor dui. Ut placerat aenean accumsan a, aenean lacus eu. Aliquet urna, habitasse elit lorem id enim quam. Eu varius nulla nullam dignissim massa tempor, massa tortor. Eget auctor nulla maecenas ac tortor.</p>
                <p>Ornare faucibus sed vitae dolor eu eu faucibus leo enim. Tincidunt quisque sed netus nibh pharetra. Gravida venenatis, lobortis id mi. Metus, ultrices duis pellentesque aliquet amet cras blandit. Aliquet purus quam laoreet ipsum pretium. Odio quis eu nunc, diam habitant nunc massa sed. Placerat arcu quis est, magna facilisis amet. Dignissim vel adipiscing elit facilisi praesent platea. Nulla posuere feugiat turpis magna etiam. Non nec felis eu praesent cras.</p>
                
                <img src="<?php echo esc_url( get_template_directory_uri() . '/images/banner-image.jpg' ); ?>" alt="<?php the_title_attribute(); ?>" />

                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Faucibus lectus tristique massa gravida vel elementum, mi. Sit scelerisque at amet leo. In volutpat turpis dolor, at. Vivamus volutpat in nunc, porttitor dui. Ut placerat aenean accumsan a, aenean lacus eu. Aliquet urna, habitasse elit lorem id enim quam. Eu varius nulla nullam dignissim massa tempor, massa tortor. Eget auctor nulla maecenas ac tortor.</p>
                <p>Ornare faucibus sed vitae dolor eu eu faucibus leo enim. Tincidunt quisque sed netus nibh pharetra. Gravida venenatis, lobortis id mi. Metus, ultrices duis pellentesque aliquet amet cras blandit. Aliquet purus quam laoreet ipsum pretium. Odio quis eu nunc, diam habitant nunc massa sed. Placerat arcu quis est, magna facilisis amet. Dignissim vel adipiscing elit facilisi praesent platea. Nulla posuere feugiat turpis magna etiam. Non nec felis eu praesent cras.</p>
                
                <img src="<?php echo esc_url( get_template_directory_uri() . '/images/contact-hero.jpg' ); ?>" alt="<?php the_title_attribute(); ?>" />

                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Faucibus lectus tristique massa gravida vel elementum, mi. Sit scelerisque at amet leo. In volutpat turpis dolor, at. Vivamus volutpat in nunc, porttitor dui. Ut placerat aenean accumsan a, aenean lacus eu. Aliquet urna, habitasse elit lorem id enim quam. Eu varius nulla nullam dignissim massa tempor, massa tortor. Eget auctor nulla maecenas ac tortor.</p>
                <?php
            }
            ?>
        </article>

    </div><!-- .news-detail-container -->

    <!-- NEWEST BLOG ENTRIES (Recent Posts Grid) -->
    <section class="news-detail-recent-section">
        <h2 class="news-detail-recent-title"><?php esc_html_e( 'NEWEST BLOG ENTRIES', 'jobscout' ); ?></h2>

        <div class="news-detail-recent-grid">
            <?php
            $recent_query = new WP_Query( array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 6,
                'post__not_in'   => array( $post_id ),
            ) );

            if ( $recent_query->have_posts() ) :
                while ( $recent_query->have_posts() ) : $recent_query->the_post();
                    $r_thumb = has_post_thumbnail() 
                        ? get_the_post_thumbnail_url( get_the_ID(), 'medium' ) 
                        : get_template_directory_uri() . '/images/contact-hero.jpg';
                    ?>
                    <article class="news-card">
                        <div class="news-card-thumb">
                            <a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                                <img src="<?php echo esc_url( $r_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                            </a>
                        </div>
                        <div class="news-card-body">
                            <div class="news-card-content">
                                <h3 class="news-card-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <p class="news-card-excerpt">
                                    <?php
                                    $r_excerpt = get_the_excerpt();
                                    if ( empty( trim( strip_tags( (string) $r_excerpt ) ) ) ) {
                                        $r_excerpt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna.';
                                    } else {
                                        $r_excerpt = wp_trim_words( $r_excerpt, 16, '...' );
                                    }
                                    echo esc_html( $r_excerpt );
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
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </section>

</div><!-- .news-detail-wrapper -->

