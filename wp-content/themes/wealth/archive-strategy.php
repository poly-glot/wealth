<?php
get_header();

get_template_part( 'template-parts/hero-page', null, array(
	'lead'    => (string) get_option( 'strategies_archive_intro' ),
	'title'   => get_option( 'strategies_archive_title' ) ?: 'Investment strategies',
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
