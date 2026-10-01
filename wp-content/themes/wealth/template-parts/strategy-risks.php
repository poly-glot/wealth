<?php
$risks = (string) wealth_meta( 'risks' );

if ( ! $risks ) {
	return;
}
?>
<section aria-labelledby="risks-title">
	<h2 class="title section-heading" id="risks-title">Key risks</h2>
	<div class="prose">
		<?php echo wp_kses_post( wealth_paragraphs( $risks ) ); ?>
	</div>
</section>
