<?php
/** Home news card; accepts a WP_Post without changing the main query. */
defined( 'ABSPATH' ) || exit;
$article = $args['post'];
$url = get_permalink( $article );
?>
<article class="lam-home-news-card">
    <a class="lam-home-news-image-link" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( get_the_title( $article ) ); ?>"><?php echo lam_home_news_image( $article ); // Escaped in helper. ?></a>
    <div class="lam-home-news-copy">
        <h3 class="lam-home-news-card-title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $article ) ); ?></a></h3>
        <p class="lam-home-news-excerpt"><?php echo esc_html( lam_home_news_excerpt( $article ) ); ?></p>
        <a class="lam-home-read-more" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Read more: %s', 'jobscout' ), get_the_title( $article ) ) ); ?>">Read More</a>
    </div>
</article>
