<?php $sections = $args['sections'] ?? array(); ?>
<nav class="with-aside__aside" aria-label="On this page">
	<h2 class="legal__toc-title">On this page</h2>
	<ul class="legal__toc-list">
		<?php foreach ( $sections as $section ) : ?>
			<li><a href="<?php echo esc_url( '#' . $section['id'] ); ?>"><?php echo wp_kses_post( $section['heading'] ); ?></a></li>
		<?php endforeach; ?>
	</ul>
</nav>
