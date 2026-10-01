<a class="<?php echo empty( $args['light'] ) ? 'chevron' : 'chevron chevron--light'; ?>" href="<?php echo esc_url( $args['href'] ?? '#main' ); ?>">
	<svg class="chevron__icon" aria-hidden="true" focusable="false"><use href="#icon-chevron" /></svg>
	<span class="visually-hidden"><?php echo esc_html( $args['label'] ?? '' ); ?></span>
</a>
