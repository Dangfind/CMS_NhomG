<?php

/**
 * Template Name: About Us
 *
 * Nội dung phần giữa trang About (giữa header và footer dùng chung).
 * Chỉnh nội dung trong Appearance > Customize > About Page.
 * Ảnh banner = ảnh đại diện (featured image) của page.
 *
 * @package JobScout
 */

get_header();

$hero_image  = nhomg_about_hero_image();
$intro_image = nhomg_about_image('intro');
$info_image  = nhomg_about_image('info');
$facts       = nhomg_about_facts();
$columns    = array(
	array(nhomg_about_mod('nhomg_about_vision_title'),  nhomg_about_mod('nhomg_about_vision_text')),
	array(nhomg_about_mod('nhomg_about_mission_title'), nhomg_about_mod('nhomg_about_mission_text')),
	array(nhomg_about_mod('nhomg_about_value_title'),   nhomg_about_mod('nhomg_about_value_text')),
);
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main nhomg-about">

		<?php while (have_posts()) : the_post(); ?>

			<section class="nhomg-fullbleed nhomg-about-hero" <?php echo $hero_image ? ' style="--nhomg-about-hero-image:url(' . esc_url($hero_image) . ')"' : ''; ?>>
				<h1 class="nhomg-about-hero__title"><?php echo nl2br(esc_html(nhomg_about_mod('nhomg_about_title'))); ?></h1>
			</section>

			<!-- Khoảng trắng ngăn cách giữa banner và khối "About Us". -->
			<div class="nhomg-about-gap" aria-hidden="true"></div>

			<?php if (nhomg_about_mod('nhomg_about_heading') || array_filter(wp_list_pluck($columns, 0))) : ?>
				<section class="nhomg-about-intro">
					<div class="nhomg-about-intro__inner">
						<?php if (nhomg_about_mod('nhomg_about_heading')) : ?>
							<h2 class="nhomg-about-heading nhomg-about-heading--center"><?php echo esc_html(nhomg_about_mod('nhomg_about_heading')); ?></h2>
						<?php endif; ?>

						<div class="nhomg-about-intro__grid">
							<?php if ($intro_image) : ?>
								<figure class="nhomg-about-intro__image">
									<img src="<?php echo esc_url($intro_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
								</figure>
							<?php endif; ?>

							<div class="nhomg-about-intro__text">
								<?php foreach ($columns as $column) :
									if (! $column[0] && ! $column[1]) continue; ?>
									<div class="nhomg-about-item">
										<?php if ($column[0]) : ?>
											<h3><?php echo esc_html($column[0]); ?></h3>
										<?php endif; ?>
										<?php if ($column[1]) : ?>
											<p><?php echo nl2br(esc_html($column[1])); ?></p>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php if (nhomg_about_mod('nhomg_about_what_heading') || nhomg_about_mod('nhomg_about_what_text') || nhomg_about_mod('nhomg_about_what_text2')) : ?>
				<section class="nhomg-about-body">
					<div class="nhomg-about-body__inner">
						<?php if (nhomg_about_mod('nhomg_about_what_heading')) : ?>
							<h2 class="nhomg-about-what-heading"><?php echo esc_html(nhomg_about_mod('nhomg_about_what_heading')); ?></h2>
						<?php endif; ?>
						<?php if (nhomg_about_mod('nhomg_about_what_text')) : ?>
							<p><?php echo nl2br(esc_html(nhomg_about_mod('nhomg_about_what_text'))); ?></p>
						<?php endif; ?>
						<?php if (nhomg_about_mod('nhomg_about_what_text2')) : ?>
							<p><?php echo nl2br(esc_html(nhomg_about_mod('nhomg_about_what_text2'))); ?></p>
						<?php endif; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ($facts) : ?>
				<section class="nhomg-fullbleed nhomg-about-info">
					<div class="nhomg-about-info__inner">
						<div class="nhomg-about-info__text">
							<dl class="nhomg-about-facts">
								<?php foreach ($facts as $fact) : ?>
									<div class="nhomg-about-fact">
										<dt><?php echo esc_html($fact['label']); ?></dt>
										<dd><?php echo esc_html($fact['value']); ?></dd>
									</div>
								<?php endforeach; ?>
							</dl>
						</div>

						<?php if ($info_image) : ?>
							<figure class="nhomg-about-info__image">
								<img src="<?php echo esc_url($info_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
							</figure>
						<?php endif; ?>
					</div>
				</section>
			<?php endif; ?>

		<?php endwhile; ?>

	</main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
