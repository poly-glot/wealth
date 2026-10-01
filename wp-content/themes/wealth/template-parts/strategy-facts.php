<?php
$facts = wealth_meta( 'facts' ) ?: array();

if ( ! $facts ) {
	return;
}
?>
<div class="fact-list">
	<h2 class="fact-list__title">At a glance</h2>
	<dl>
		<?php foreach ( $facts as $fact ) : ?>
			<div class="fact-list__item"><dt><?php echo esc_html( $fact['label'] ?? '' ); ?></dt><dd><?php echo esc_html( $fact['value'] ?? '' ); ?></dd></div>
		<?php endforeach; ?>
	</dl>
</div>
