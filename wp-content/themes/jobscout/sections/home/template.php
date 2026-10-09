<?php
/** Four Home sections; shared chrome is rendered only by the root templates. */
defined( 'ABSPATH' ) || exit;
$filters = lam_home_filters();
?>
<main id="main" class="lam-home">
    <?php get_template_part( 'sections/home/hero', null, array( 'filters' => $filters, 'locations' => lam_home_locations(), 'route' => lam_home_search_route() ) ); ?>
    <?php get_template_part( 'sections/home/jobs', null, array( 'filters' => $filters, 'filtered' => '' !== $filters['search_keywords'] || '' !== $filters['search_location'] ) ); ?>
    <?php get_template_part( 'sections/home/career' ); ?>
    <?php get_template_part( 'sections/home/news' ); ?>
</main>
