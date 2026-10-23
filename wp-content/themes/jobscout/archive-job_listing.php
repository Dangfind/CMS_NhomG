<?php
/**
 * Template Name: Custom All Jobs Archive
 */

get_header(); 

// Lấy giá trị sort từ URL (mặc định là 'date_desc' - Mới nhất)
$sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'date_desc';

?>

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
    /* Banner sát header như trang Home (bỏ lề trên 50px mặc định của theme) */
    .post-type-archive-job_listing .site-content {
        margin-top: 0;
    }
    /* Nền xám, thẻ trắng như ảnh thiết kế */
    body.post-type-archive-job_listing {
        background: #f2f2f2;
    }
    .jobs-hero-banner {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 360px;
    }
    @media (max-width: 767px) {
        .jobs-hero-banner { min-height: 220px; }
        .jobs-hero-banner h1 { font-size: 28px !important; }
    }
</style>

<!-- 1. BANNER CAREER WITH US (GỌI TỪ WIDGET) -->
<?php if ( is_active_sidebar( 'jobs-hero-banner-widget' ) ) : ?>
    <div class="jobs-hero-banner-widget-area" style="width: 100vw; position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; overflow: hidden;">
        <?php dynamic_sidebar( 'jobs-hero-banner-widget' ); ?>
    </div>
<?php else : ?>
    <!-- Mặc định nếu khách hàng chưa dán code vào Widget -->
    <div class="jobs-hero-banner" style="width: 100vw; position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('<?php echo get_template_directory_uri(); ?>/images/hinh_co_gai_cam_o.png') center/cover no-repeat; padding: 120px 20px; text-align: center; color: #fff;">
        <h1 style="color: #ffffff; font-size: 38px; font-weight: 700; text-transform: uppercase; letter-spacing: 3px; margin: 0;">CAREER WITH US</h1>
    </div>
<?php endif; ?>

<!-- 2. KHUNG NỘI DUNG CHÍNH (ALL JOBS & LISTING) -->
<div class="jobs-page-container" style="max-width: 1140px; margin: 0 auto; padding: 50px 20px;">
    
    <!-- HEADER TIÊU ĐỀ ALL JOBS & BỘ LỌC SORT -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px;">
        <h2 style="font-size: 24px; font-weight: 800; text-transform: uppercase; margin: 0; color: #2d3748; letter-spacing: 1px;">ALL JOBS</h2>
        <div>
            <!-- BỘ LỌC SẮP XẾP LATEST JOBS -->
            <select id="job-sort-select" style="padding: 8px 16px; border: 1px solid #e2e8f0; border-radius: 4px; color: #718096; font-size: 14px; background-color: #fff; cursor: pointer;">
                <option value="date_desc" <?php selected($sort, 'date_desc'); ?>>Latest Jobs</option>
                <option value="date_asc" <?php selected($sort, 'date_asc'); ?>>Oldest Jobs</option>
                <option value="title_asc" <?php selected($sort, 'title_asc'); ?>>Title (A-Z)</option>
            </select>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sortSelect = document.getElementById('job-sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('sort', this.value);
                // Reset về trang 1 khi đổi cách sắp xếp
                window.location.href = currentUrl.pathname + '?sort=' + this.value;
            });
        }
    });
    </script>

    <main id="main" class="site-main">

    <?php 
    // Xử lý query bài viết theo lựa chọn sắp xếp
    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
    $args = array(
        'post_type'      => 'job_listing',
        'post_status'    => 'publish',
        'posts_per_page' => get_option( 'posts_per_page', 10 ),
        'paged'          => $paged
    );

    if ($sort === 'date_asc') {
        $args['orderby'] = 'date';
        $args['order']   = 'ASC';
    } elseif ($sort === 'title_asc') {
        $args['orderby'] = 'title';
        $args['order']   = 'ASC';
    } else { // date_desc mặc định
        $args['orderby'] = 'date';
        $args['order']   = 'DESC';
    }

    $job_query = new WP_Query( $args );
    ?>

    <?php if ( $job_query->have_posts() ) : ?>

        <!-- GRID 2 CỘT HIỂN THỊ CÁC THẺ JOBS -->
        <div class="custom-job-grid nhomg-job-grid">
            <?php
            while ( $job_query->have_posts() ) : $job_query->the_post();

                // Dùng chung thẻ việc làm với mục OTHER JOBS (trang Job Detail) cho giống thiết kế.
                get_template_part( 'template-parts/content', 'job-card' );

            endwhile;
            ?>
        </div>

        <!-- NÚT LOAD MORE JOBS -->
        <?php if ( $job_query->max_num_pages > 1 ) : ?>
            <div style="text-align: center; margin-top: 50px;">
                <button id="load-more-jobs-btn" 
                        data-page="<?php echo $paged; ?>" 
                        data-max="<?php echo $job_query->max_num_pages; ?>"
                        style="background: transparent; border: 1px solid #f26522; color: #f26522; padding: 12px 35px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-radius: 3px; cursor: pointer; transition: all 0.3s ease;">
                    LOAD MORE JOBS
                </button>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const btn = document.getElementById('load-more-jobs-btn');
                if (!btn) return;

                btn.addEventListener('click', function() {
                    let currentPage = parseInt(btn.getAttribute('data-page'));
                    let maxPage = parseInt(btn.getAttribute('data-max'));
                    let nextPage = currentPage + 1;

                    btn.innerText = 'LOADING...';
                    btn.style.opacity = '0.6';

                    const urlParams = new URLSearchParams(window.location.search);
                    let sortVal = urlParams.get('sort') || 'date_desc';

                    let baseUrl = window.location.pathname;
                    if (!baseUrl.endsWith('/')) baseUrl += '/';
                    let nextUrl = baseUrl + 'page/' + nextPage + '/?sort=' + sortVal;

                    fetch(nextUrl)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newJobs = doc.querySelectorAll('.custom-job-grid > *');
                            const grid = document.querySelector('.custom-job-grid');

                            if (newJobs.length > 0 && grid) {
                                newJobs.forEach(job => grid.appendChild(job));
                                btn.setAttribute('data-page', nextPage);
                                btn.innerText = 'LOAD MORE JOBS';
                                btn.style.opacity = '1';

                                if (nextPage >= maxPage) {
                                    btn.style.display = 'none';
                                }
                            } else {
                                btn.style.display = 'none';
                            }
                        })
                        .catch(err => {
                            console.error('Error loading jobs:', err);
                            btn.innerText = 'LOAD MORE JOBS';
                            btn.style.opacity = '1';
                        });
                });
            });
            </script>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>

    <?php else : ?>

        <p style="text-align: center; color: #718096; padding: 40px 0;"><?php esc_html_e( 'No jobs found.', 'jobscout' ); ?></p>

    <?php endif; ?>

    </main>

</div>

<?php
get_footer();