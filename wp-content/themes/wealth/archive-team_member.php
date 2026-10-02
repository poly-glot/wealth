<?php
get_header();

get_template_part( 'template-parts/hero-page', null, array(
	'lead'    => 'Wealth is owned by the people who work here, and the people who manage your capital are the people you meet. Our investment team and our executive team sit on one floor in St James\'s. Between them they have spent more than a century in markets, and none of them is in a hurry to leave.',
	'title'   => 'People',
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
