<?php
get_header();

get_template_part( 'template-parts/hero-page', null, array(
	'lead'    => (string) get_option( 'people_archive_intro' ),
	'title'   => get_option( 'people_archive_title' ) ?: 'People',
	'variant' => 'tan',
) );

$people = get_posts( array(
	'order'          => 'ASC',
	'orderby'        => 'menu_order',
	'post_type'      => 'team_member',
	'posts_per_page' => -1,
) );
?>
<div class="section">
	<div class="container container--narrow">
		<?php get_template_part( 'template-parts/loop-people', null, array( 'heading' => 'h2', 'posts' => $people ) ); ?>
	</div>
</div>
<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
