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
			<?php $file_url = wp_get_attachment_url( (int) ( $document['file'] ?? 0 ) ); ?>
			<li><a class="doc-list__link" href="<?php echo esc_url( $file_url ?: '#' ); ?>"><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-document" /></svg><?php echo esc_html( $document['label'] ?? '' ); ?><?php if ( ! $file_url ) : ?><span class="visually-hidden">, <?php echo esc_html( get_the_title() ); ?> (sample document, not available on this demonstration site)</span><?php endif; ?></a></li>
		<?php endforeach; ?>
	</ul>
</div>
