<?php
get_header();

get_template_part( 'template-parts/hero-page', null, array(
	'title'   => get_the_archive_title() ?: get_bloginfo( 'name' ),
	'variant' => 'slate',
) );

get_template_part( 'template-parts/insights-index' );
get_template_part( 'template-parts/cta-band' );
get_footer();
