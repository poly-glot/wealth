<?php
get_header();

get_template_part( 'template-parts/hero-page', null, array(
	'lead'    => 'Results for “' . get_search_query() . '”',
	'title'   => 'Search results',
	'variant' => 'slate',
) );

get_template_part( 'template-parts/insights-index', null, array( 'label' => 'Search results' ) );
get_template_part( 'template-parts/cta-band' );
get_footer();
