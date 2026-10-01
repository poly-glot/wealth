<?php
$strategies   = $args['posts'] ?? array();
$heading      = $args['heading'] ?? 'h3';
$with_summary = ! empty( $args['summary'] );
?>
<ul class="strategy-grid">
	<?php foreach ( $strategies as $strategy ) : ?>
		<?php $title = get_the_title( $strategy ); ?>
		<li class="strategy-card strategy-card--<?php echo esc_attr( wealth_meta( 'tint', $strategy->ID ) ); ?>">
			<<?php echo tag_escape( $heading ); ?> class="strategy-card__title"><?php echo esc_html( $title ); ?></<?php echo tag_escape( $heading ); ?>>
			<?php if ( $with_summary ) : ?>
				<p class="strategy-card__summary"><?php echo esc_html( wealth_meta( 'summary', $strategy->ID ) ); ?></p>
			<?php endif; ?>
			<a class="strategy-card__link" href="<?php echo esc_url( get_permalink( $strategy ) ); ?>">
				Learn more<span class="visually-hidden"> about the <?php echo esc_html( $title ); ?></span>
				<svg class="icon icon--small" aria-hidden="true" focusable="false"><use href="#icon-arrow-right" /></svg>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
