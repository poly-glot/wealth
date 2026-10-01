<?php
$approach = wealth_meta( 'approach' ) ?: array();

if ( ! $approach ) {
	return;
}
?>
<section aria-labelledby="approach-title">
	<h2 class="title section-heading" id="approach-title">How we invest</h2>
	<ol class="approach-list">
		<?php foreach ( $approach as $step ) : ?>
			<li class="approach-list__item">
				<h3 class="approach-list__title"><?php echo esc_html( $step['title'] ?? '' ); ?></h3>
				<p><?php echo esc_html( $step['text'] ?? '' ); ?></p>
			</li>
		<?php endforeach; ?>
	</ol>
</section>
