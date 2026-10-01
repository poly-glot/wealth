<?php
get_header();

while ( have_posts() ) {
	the_post();

	get_template_part( 'template-parts/bio' );
	get_template_part( 'template-parts/cta-band' );
}

get_footer();
