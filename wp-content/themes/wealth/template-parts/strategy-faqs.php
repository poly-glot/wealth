<?php
$faqs = wealth_meta( 'faqs' ) ?: array();

if ( ! $faqs ) {
	return;
}
?>
<div class="faq">
	<h2 class="title faq__title">Questions investors ask</h2>
	<?php foreach ( $faqs as $faq ) : ?>
		<details class="faq__item">
			<summary class="faq__question"><?php echo esc_html( $faq['question'] ?? '' ); ?></summary>
			<div class="faq__answer prose"><p><?php echo esc_html( $faq['answer'] ?? '' ); ?></p></div>
		</details>
	<?php endforeach; ?>
</div>
