<?php
/** Home card; accepts a WP_Post for reuse through get_template_part(). */
defined( 'ABSPATH' ) || exit;
$job = $args['post'];
$url = get_permalink( $job );
$type = lam_home_taxonomy_names( $job->ID, 'job_listing_type' );
$category = lam_home_taxonomy_names( $job->ID, 'job_listing_category' );
$location = get_post_meta( $job->ID, '_job_location', true );
if ( ! $location ) $location = lam_home_taxonomy_names( $job->ID, 'job_listing_region' );
if ( ! $location ) $location = lam_home_taxonomy_names( $job->ID, 'job_listing_location' );
$summary = lam_home_job_summary( $job );
?>
<article class="lam-home-job-card">
    <div class="lam-home-job-heading">
        <a class="lam-home-logo-frame" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( get_the_title( $job ) ); ?>"><?php echo lam_home_job_logo( $job->ID ); // Escaped in helper. ?></a>
        <div class="lam-home-job-details">
            <h3 class="lam-home-job-title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $job ) ); ?></a></h3>
            <p class="lam-home-job-date">Created: <time datetime="<?php echo esc_attr( get_the_date( 'c', $job ) ); ?>"><?php echo esc_html( get_the_date( 'M j, Y', $job ) ); ?></time></p>
            <?php if ( $type || $category || $location ) : ?>
                <ul class="lam-home-job-meta"><?php foreach ( array_filter( array( $type, $category, $location ) ) as $value ) : ?><li><?php echo esc_html( $value ); ?></li><?php endforeach; ?></ul>
            <?php endif; ?>
        </div>
    </div>
    <?php if ( $summary ) : ?>
        <ul class="lam-home-job-summary"><?php foreach ( $summary as $line ) : ?><li><?php echo esc_html( $line ); ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
</article>
