<?php
/**
 * Job listing in the loop (Custom Grid Layout).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

global $post;
$job_salary   = get_post_meta( get_the_ID(), '_job_salary', true );
$job_featured = get_post_meta( get_the_ID(), '_featured', true );
$company_name = get_post_meta( get_the_ID(), '_company_name', true );

// Lấy lat/long an toàn
$lat  = isset( $post->geolocation_lat ) ? $post->geolocation_lat : '';
$long = isset( $post->geolocation_long ) ? $post->geolocation_long : '';

?>
<article class="job_listing custom-job-card" data-longitude="<?php echo esc_attr( $long ); ?>" data-latitude="<?php echo esc_attr( $lat ); ?>" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; background-color: #fff; display: flex; flex-direction: column; gap: 15px; position: relative; margin-bottom: 20px;">

    <!-- 1. PHẦN TRÊN: LOGO + TIÊU ĐỀ + THẺ TAG -->
    <div class="job-card-top" style="display: flex; gap: 15px; align-items: flex-start;">
        
        <!-- Logo công ty -->
        <figure class="company-logo" style="width: 70px; height: 70px; flex-shrink: 0; margin: 0; border: 1px solid #eee; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
            <?php 
                if ( function_exists( 'the_company_logo' ) ) {
                    the_company_logo( 'thumbnail' );
                } elseif ( has_post_thumbnail() ) {
                    the_post_thumbnail( 'thumbnail' );
                }
            ?>
        </figure>

        <!-- Tiêu đề & Tag thông tin -->
        <div class="job-title-wrap" style="flex-grow: 1;">
            
            <h2 class="entry-title" style="font-size: 16px; font-weight: bold; margin: 0 0 4px 0; text-transform: uppercase;">
                <a href="<?php the_permalink(); ?>" style="color: #1a202c; text-decoration: none;">
                    <?php 
                        if ( function_exists( 'wpjm_the_job_title' ) ) {
                            wpjm_the_job_title();
                        } else {
                            the_title();
                        }
                    ?>
                </a>
            </h2>
            
            <div class="posted-date" style="font-size: 12px; color: #a0aec0; margin-bottom: 8px;">
                Posted <?php echo get_the_date('M d, Y'); ?>
            </div>

            <!-- Các nút Tag (Fulltime, Category, Location) -->
            <div class="entry-meta" style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                
                <?php if ( get_option( 'job_manager_enable_types' ) && function_exists( 'wpjm_get_the_job_types' ) ) : ?>
                    <?php 
                        $types = wpjm_get_the_job_types(); 
                        if ( ! empty( $types ) ) : foreach ( $types as $jobtype ) : ?>
                            <span class="job-type <?php echo esc_attr( sanitize_title( $jobtype->slug ) ); ?>" style="background: #edf2f7; color: #4a5568; font-size: 11px; padding: 2px 8px; border-radius: 3px;">
                                <?php echo esc_html( $jobtype->name ); ?>
                            </span>
                        <?php endforeach; endif; 
                    ?>
                <?php endif; ?>

                <?php 
                // Danh mục công việc
                $terms = get_the_terms( get_the_ID(), 'job_listing_category' );
                if ( $terms && ! is_wp_error( $terms ) ) :
                    foreach ( $terms as $term ) : ?>
                        <span class="job-cat" style="background: #edf2f7; color: #4a5568; font-size: 11px; padding: 2px 8px; border-radius: 3px;">
                            <?php echo esc_html( $term->name ); ?>
                        </span>
                    <?php endforeach;
                endif; 
                ?>

                <!-- Địa điểm -->
                <span class="company-address" style="background: #edf2f7; color: #4a5568; font-size: 11px; padding: 2px 8px; border-radius: 3px;">
                    <?php 
                        if ( function_exists( 'the_job_location' ) ) {
                            the_job_location( false );
                        } else {
                            echo get_post_meta( get_the_ID(), '_job_location', true );
                        }
                    ?>
                </span>

            </div>      
        </div>
    </div>

    <!-- 2. PHẦN DƯỚI: MÔ TẢ NGẮN (BULLET POINTS) -->
    <div class="job-excerpt-list" style="font-size: 13px; color: #4a5568; line-height: 1.5; border-top: 1px dashed #e2e8f0; padding-top: 10px;">
        <?php 
            if ( has_excerpt() ) {
                the_excerpt();
            } else {
                echo wp_trim_words( get_the_content(), 25, '...' );
            }
        ?>
    </div>

    <?php if( $job_featured ){ ?>
        <div class="featured-label" style="position: absolute; top: 10px; right: 10px; background: #f6ad55; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 3px; font-weight: bold; text-transform: uppercase;"><?php esc_html_e( 'Featured', 'jobscout' ); ?></div>
    <?php } ?>

</article>