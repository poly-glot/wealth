<?php
get_header();

get_template_part( 'template-parts/hero-page', null, array(
	'lead'    => 'We run four strategies and no more than we can manage well. Each draws on the same research and the same small team, and each has a clear limit on the assets it will take. All four are available as a segregated mandate or through a pooled fund for eligible investors, with every holding, cost and vote disclosed.',
	'title'   => 'Investment strategies',
	'variant' => 'tan',
) );

$strategies = get_posts( array(
	'order'          => 'ASC',
	'orderby'        => 'menu_order',
	'post_type'      => 'strategy',
	'posts_per_page' => -1,
) );
?>
<div class="section section--grey">
	<div class="container">
		<?php get_template_part( 'template-parts/loop-strategies', null, array( 'heading' => 'h2', 'posts' => $strategies, 'summary' => true ) ); ?>
	</div>
	<svg class="strategy-grid__motif motif motif--light" aria-hidden="true" focusable="false" viewBox="0 0 120 120"><use href="#mark" /></svg>
</div>
<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
