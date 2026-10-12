<?php
/**
 * Template Name: Custom All Jobs Archive
 */

get_header(); ?>

<!-- CSS bổ sung để ẩn tiêu đề Archives: Jobs mặc định của theme và xử lý overflow -->
<style>
    .page-header, 
    header.page-header, 
    .archive-header,
    .post-type-archive-job_listing .page-header {
        display: none !important;
    }
    body {
        overflow-x: hidden; /* Tránh xuất hiện thanh cuộn ngang khi dùng 100vw */
    }
</style>

<!-- 1. BANNER CAREER WITH US (TRÀN FULL 100% CHIỀU NGANG MÀN HÌNH) -->
<div class="jobs-hero-banner" style="width: 100vw; position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('<?php echo get_template_directory_uri(); ?>/images/hinh_co_gai_cam_o.png') center/cover no-repeat; padding: 120px 20px; text-align: center; color: #fff;">
    <h1 style="color: #ffffff; font-size: 38px; font-weight: 700; text-transform: uppercase; letter-spacing: 3px; margin: 0;">CAREER WITH US</h1>
</div>

<!-- 2. KHUNG NỘI DUNG CHÍNH (ALL JOBS & LISTING) -->
<div class="jobs-page-container" style="max-width: 1140px; margin: 0 auto; padding: 50px 20px;">
    
    <!-- HEADER TIÊU ĐỀ ALL JOBS & BỘ LỌC SORT -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px;">
        <h2 style="font-size: 24px; font-weight: 800; text-transform: uppercase; margin: 0; color: #2d3748; letter-spacing: 1px;">ALL JOBS</h2>
        <div>
            <select style="padding: 8px 16px; border: 1px solid #e2e8f0; border-radius: 4px; color: #718096; font-size: 14px; background-color: #fff; cursor: pointer;">
                <option>Latest Jobs</option>
            </select>
        </div>
    </div>

    <main id="main" class="site-main">

    <?php if ( have_posts() ) : ?>

        <!-- GRID 2 CỘT HIỂN THỊ CÁC THẺ JOBS -->
        <div class="custom-job-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px;">
            <?php
            while ( have_posts() ) : the_post();

                get_template_part( 'job_manager/content', 'job_listing' );

            endwhile;
            ?>
        </div>

        <!-- NÚT LOAD MORE JOBS -->
        <div style="text-align: center; margin-top: 50px;">
            <button style="background: transparent; border: 1px solid #f26522; color: #f26522; padding: 12px 35px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-radius: 3px; cursor: pointer; transition: all 0.3s ease;">
                LOAD MORE JOBS
            </button>
        </div>

    <?php else : ?>

        <p style="text-align: center; color: #718096; padding: 40px 0;"><?php esc_html_e( 'No jobs found.', 'jobscout' ); ?></p>

    <?php endif; ?>

    </main>

</div>

<?php
// Gọi Footer mặc định của theme (đã có sẵn thanh Subscribe Newsletter)
get_footer();