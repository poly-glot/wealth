<?php
$documents = wealth_meta( 'documents' ) ?: array();

if ( ! $documents ) {
	return;
}
?>
<div>
	<h2 class="fact-list__title">Documents</h2>
	<ul class="doc-list">
		<?php foreach ( $documents as $document ) : ?>
			<li><a class="doc-list__link" href="#"><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-document" /></svg><?php echo esc_html( $document['label'] ?? '' ); ?><span class="visually-hidden">, <?php echo esc_html( get_the_title() ); ?> (sample document, not available on this demonstration site)</span></a></li>
		<?php endforeach; ?>
	</ul>
</div>
