<?php
/**
 * Template part: thẻ việc làm (dùng ở "Other Jobs", có thể tái sử dụng cho All Jobs).
 *
 * @package JobScout
 */
?>
<article class="nhomg-box nhomg-job-card">
	<div class="nhomg-job-card__head">
		<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php nhomg_job_logo(); ?></a>
		<div class="nhomg-job-card__info">
			<h3 class="nhomg-job-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<p class="nhomg-job-date">
				<?php esc_html_e( 'Created:', 'jobscout' ); ?>
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></time>
			</p>
			<?php if ( $tags = nhomg_job_tags() ) : ?>
				<ul class="nhomg-tags">
					<?php foreach ( $tags as $tag ) echo '<li>' . esc_html( $tag ) . '</li>'; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( $highlights = nhomg_job_highlights() ) : ?>
		<ul class="nhomg-job-card__highlights">
			<?php foreach ( $highlights as $item ) echo '<li>' . esc_html( $item ) . '</li>'; ?>
		</ul>
	<?php endif; ?>
</article>
