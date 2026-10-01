<?php
get_header();

$posts_page = get_post( (int) get_option( 'page_for_posts' ) );

get_template_part( 'template-parts/hero-page', null, array(
	'lead'    => $posts_page ? (string) wealth_meta( 'subtitle', $posts_page->ID ) : '',
	'title'   => $posts_page ? get_the_title( $posts_page ) : 'Insights',
	'variant' => 'slate',
) );

get_template_part( 'template-parts/insights-index' );
get_template_part( 'template-parts/cta-band' );
get_footer();
