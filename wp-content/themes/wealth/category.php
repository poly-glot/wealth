<?php
get_header();

get_template_part( 'template-parts/hero-page', null, array(
	'lead'    => 'Insights filed under this category',
	'title'   => single_cat_title( '', false ),
	'variant' => 'slate',
) );

get_template_part( 'template-parts/insights-index', null, array( 'label' => 'Insights in ' . single_cat_title( '', false ) ) );
get_template_part( 'template-parts/cta-band' );
get_footer();
