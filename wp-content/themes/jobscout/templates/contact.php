<?php
/**
 * Template Name: Contact Us
 *
 * Nội dung chỉnh trong Appearance > Customize > Contact Page.
 * Ảnh banner = ảnh đại diện (featured image) của page.
 *
 * @package JobScout
 */
get_header();

$hero_image = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'full' ) : get_template_directory_uri() . '/images/contact-hero.jpg';
$emp_note   = nhomg_contact_mod( 'nhomg_contact_emp_note' );
$offices    = array(
    array( nhomg_contact_mod( 'nhomg_contact_hcm_label' ), nhomg_contact_mod( 'nhomg_contact_hcm_phone' ) ),
    array( nhomg_contact_mod( 'nhomg_contact_hn_label' ), nhomg_contact_mod( 'nhomg_contact_hn_phone' ) ),
);
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main nhomg-contact">

			<?php while ( have_posts() ) : the_post(); ?>

				<section class="nhomg-fullbleed nhomg-contact-hero" style="background-image:url(<?php echo esc_url( $hero_image ); ?>)">
					<h1 class="nhomg-contact-hero__title"><?php echo esc_html( nhomg_contact_mod( 'nhomg_contact_title' ) ); ?></h1>
				</section>

				<section class="nhomg-contact-hq">
					<h2><?php echo esc_html( nhomg_contact_mod( 'nhomg_contact_hq_title' ) ); ?></h2>
					<address><?php echo esc_html( nhomg_contact_mod( 'nhomg_contact_hq_address' ) ); ?></address>
				</section>

				<section class="nhomg-fullbleed nhomg-contact-info">
					<div class="nhomg-contact-info__inner">
						<div class="nhomg-contact-col">
							<h2><?php echo esc_html( nhomg_contact_mod( 'nhomg_contact_emp_title' ) ); ?></h2>
							<p><?php echo esc_html( nhomg_contact_mod( 'nhomg_contact_emp_text' ) ); ?></p>

							<?php foreach ( $offices as $office ) :
								if ( ! $office[1] ) continue; ?>
								<h3><?php echo esc_html( $office[0] ); ?></h3>
								<p class="nhomg-phone"><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $office[1] ) ); ?>"><?php echo esc_html( $office[1] ); ?></a></p>
							<?php endforeach; ?>

							<?php if ( $emp_note ) : ?>
								<p class="nhomg-contact-note"><?php echo nl2br( esc_html( $emp_note ) ); ?></p>
							<?php endif; ?>
						</div>

						<div class="nhomg-contact-col">
							<h2><?php echo esc_html( nhomg_contact_mod( 'nhomg_contact_seek_title' ) ); ?></h2>
							<p>
								<?php
								printf(
									/* translators: %s: Facebook link */
									esc_html__( 'Ask a question on our %s page', 'jobscout' ),
									'<a href="' . esc_url( nhomg_contact_mod( 'nhomg_contact_facebook_url' ) ) . '" target="_blank" rel="noopener">Facebook</a>'
								);
								?>
								<br />
								<?php
								printf(
									/* translators: %s: blog posts link */
									esc_html__( 'Read our %s on interview and CV tips', 'jobscout' ),
									'<a href="' . esc_url( nhomg_contact_blog_url() ) . '">' . esc_html__( 'blog posts', 'jobscout' ) . '</a>'
								);
								?>
							</p>

							<?php if ( $call_phone = nhomg_contact_mod( 'nhomg_contact_call_phone' ) ) : ?>
								<h3><?php echo esc_html( nhomg_contact_mod( 'nhomg_contact_call_label' ) ); ?></h3>
								<p class="nhomg-phone"><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $call_phone ) ); ?>"><?php echo esc_html( $call_phone ); ?></a></p>
							<?php endif; ?>
						</div>
					</div>
				</section>

				<?php if ( get_the_content() ) : // Ví dụ: shortcode form liên hệ. ?>
					<section class="nhomg-contact-extra entry-content">
						<?php the_content(); ?>
					</section>
				<?php endif; ?>

			<?php endwhile; ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
