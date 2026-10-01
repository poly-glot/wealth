<?php
$label    = $args['label'] ?? 'All insights';
$insights = array();

while ( have_posts() ) {
	the_post();

	$insights[] = get_post();
}
?>
<section class="section" aria-label="<?php echo esc_attr( $label ); ?>">
	<div class="container">
		<?php if ( $insights ) : ?>
			<?php get_template_part( 'template-parts/loop-insights', null, array( 'posts' => $insights ) ); ?>
		<?php else : ?>
			<p>No insights found.</p>
		<?php endif; ?>
	</div>
</section>
