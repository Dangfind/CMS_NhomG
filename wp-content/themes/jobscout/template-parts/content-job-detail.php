<?php
/**
 * Template part: trang chi tiết việc làm (Job Detail).
 *
 * @package JobScout
 */

$job_id    = get_the_ID();
$rating    = get_post_meta( $job_id, '_nhomg_rating', true );
$photo_ids = nhomg_job_photo_ids( $job_id );
$apply_url = nhomg_job_apply_url( $job_id );
$share_url = get_permalink();
$title     = get_the_title();

$sections = array(
    '_nhomg_overview'        => __( 'Overview about Company', 'jobscout' ),
    '_nhomg_key_skills'      => __( 'Our Key Skills', 'jobscout' ),
    '_nhomg_why_love'        => __( 'Why You\'ll Love Working Here', 'jobscout' ),
    '_nhomg_location_detail' => __( 'Location', 'jobscout' ),
);
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'nhomg-job-detail' ); ?>>

	<nav class="nhomg-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'jobscout' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'jobscout' ); ?></a>
		<span class="sep">/</span>
		<a href="<?php echo esc_url( nhomg_all_jobs_url() ); ?>"><?php esc_html_e( 'All Jobs', 'jobscout' ); ?></a>
		<span class="sep">/</span>
		<span aria-current="page"><?php esc_html_e( 'Job Detail', 'jobscout' ); ?></span>
	</nav>

	<header class="nhomg-box nhomg-job-head">
		<?php nhomg_job_logo( $job_id ); ?>

		<div class="nhomg-job-head__info">
			<h1 class="nhomg-job-title"><?php the_title(); ?></h1>
			<p class="nhomg-job-date">
				<?php esc_html_e( 'Created:', 'jobscout' ); ?>
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></time>
			</p>
			<?php if ( $tags = nhomg_job_tags( $job_id ) ) : ?>
				<ul class="nhomg-tags">
					<?php foreach ( $tags as $tag ) echo '<li>' . esc_html( $tag ) . '</li>'; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="nhomg-job-head__actions">
			<div class="nhomg-share">
				<button type="button" class="nhomg-btn nhomg-btn--outline-dark nhomg-share__toggle" aria-expanded="false" aria-controls="nhomg-share-menu">
					<?php esc_html_e( 'Share', 'jobscout' ); ?>
				</button>
				<ul class="nhomg-share__menu" id="nhomg-share-menu" hidden>
					<li><a target="_blank" rel="noopener" href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $share_url ) ); ?>">Facebook</a></li>
					<li><a target="_blank" rel="noopener" href="<?php echo esc_url( 'https://twitter.com/intent/tweet?url=' . rawurlencode( $share_url ) . '&text=' . rawurlencode( $title ) ); ?>">Twitter</a></li>
					<li><a target="_blank" rel="noopener" href="<?php echo esc_url( 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $share_url ) ); ?>">LinkedIn</a></li>
					<li><button type="button" class="nhomg-share__copy" data-url="<?php echo esc_url( $share_url ); ?>"><?php esc_html_e( 'Copy link', 'jobscout' ); ?></button></li>
				</ul>
			</div>
			<a class="nhomg-btn nhomg-btn--outline" href="<?php echo esc_url( $apply_url ); ?>"<?php echo 0 === strpos( $apply_url, 'http' ) && false === strpos( $apply_url, home_url() ) ? ' target="_blank" rel="noopener"' : ''; ?>>
				<?php esc_html_e( 'Apply Job', 'jobscout' ); ?>
			</a>
		</div>
	</header>

	<div class="nhomg-job-body">
		<div class="nhomg-box nhomg-job-content">
			<?php
			$has_section = false;
			foreach ( $sections as $key => $heading ) {
				$content = get_post_meta( $job_id, $key, true );
				if ( ! $content ) continue;
				$has_section = true;
				echo '<section class="nhomg-job-section">';
				echo '<h2>' . esc_html( $heading ) . '</h2>';
				echo wp_kses_post( wpautop( $content ) );
				echo '</section>';
			}

			// Nội dung soạn trong editor (mô tả công việc) hiển thị sau các phần trên.
			if ( get_the_content() ) {
				echo '<section class="nhomg-job-section entry-content">';
				if ( $has_section ) echo '<h2>' . esc_html__( 'Job Description', 'jobscout' ) . '</h2>';
				the_content();
				echo '</section>';
			}
			?>
		</div>

		<aside class="nhomg-job-aside">
			<?php if ( '' !== $rating ) : ?>
				<div class="nhomg-box nhomg-widget">
					<h2 class="nhomg-widget__title"><?php esc_html_e( 'Staff Rating', 'jobscout' ); ?></h2>
					<div class="nhomg-rating">
						<?php nhomg_rating_stars( $rating ); ?>
						<span class="nhomg-rating__value"><?php echo esc_html( number_format_i18n( (float) $rating, 1 ) ); ?></span>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $photo_ids ) : ?>
				<div class="nhomg-box nhomg-widget">
					<h2 class="nhomg-widget__title"><?php esc_html_e( 'Company Photos', 'jobscout' ); ?></h2>
					<div class="nhomg-photos">
						<?php foreach ( $photo_ids as $i => $photo_id ) :
							$full = wp_get_attachment_image_url( $photo_id, 'large' ); ?>
							<a class="nhomg-photos__item" href="<?php echo esc_url( $full ); ?>" data-index="<?php echo (int) $i; ?>"<?php echo $i ? ' hidden' : ''; ?>>
								<?php if ( 0 === $i ) {
									echo wp_get_attachment_image( $photo_id, 'medium_large' );
									if ( count( $photo_ids ) > 1 ) {
										echo '<span class="nhomg-photos__more">+' . (int) ( count( $photo_ids ) - 1 ) . '</span>';
									}
								} ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</aside>
	</div>

	<?php
	$related = nhomg_related_jobs( $job_id, 6 );
	if ( $related->have_posts() ) : ?>
		<section class="nhomg-other-jobs">
			<h2 class="nhomg-section-title"><?php esc_html_e( 'Other Jobs', 'jobscout' ); ?></h2>
			<div class="nhomg-job-grid">
				<?php while ( $related->have_posts() ) : $related->the_post();
					get_template_part( 'template-parts/content', 'job-card' );
				endwhile;
				wp_reset_postdata(); ?>
			</div>
		</section>
	<?php endif; ?>

</article>
