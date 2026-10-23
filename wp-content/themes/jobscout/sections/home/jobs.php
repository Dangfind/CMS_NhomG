<?php
defined( 'ABSPATH' ) || exit;
$jobs = lam_home_jobs( $args['filters'] );
$jobs_url = lam_home_destination( 'jobs' );
?>
<section id="lam-home-jobs" class="lam-home-jobs" aria-labelledby="lam-home-jobs-title" tabindex="-1">
    <div class="lam-home-container">
        <h2 id="lam-home-jobs-title" class="lam-home-section-title">TOP JOBS</h2>
        <?php if ( $args['filtered'] ) : ?>
            <p class="lam-home-results-status"><a href="<?php echo esc_url( home_url( '/' ) . '#lam-home-jobs' ); ?>"><?php esc_html_e( 'Clear filters', 'jobscout' ); ?></a></p>
        <?php endif; ?>
        <?php if ( $jobs->have_posts() ) : ?>
            <div class="lam-home-job-grid">
                <?php while ( $jobs->have_posts() ) : $jobs->the_post();
                    get_template_part( 'sections/home/job-card', null, array( 'post' => get_post() ) );
                endwhile; ?>
            </div>
        <?php else : ?>
            <p class="lam-home-empty" role="status"><?php echo $args['filtered'] ? esc_html__( 'No jobs match your search. Try another keyword or location, or clear the filters.', 'jobscout' ) : esc_html__( 'There are no open jobs at the moment. Please check back soon.', 'jobscout' ); ?></p>
        <?php endif; wp_reset_postdata(); ?>
        <div class="lam-home-more-wrap">
            <?php if ( $jobs_url ) : ?>
                <a class="lam-home-outline-button" href="<?php echo esc_url( $jobs_url ); ?>">VIEW MORE JOBS</a>
            <?php else : ?>
                <span class="lam-home-outline-button" aria-disabled="true">VIEW MORE JOBS</span>
            <?php endif; ?>
        </div>
    </div>
</section>
