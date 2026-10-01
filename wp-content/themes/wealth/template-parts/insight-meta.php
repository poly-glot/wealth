<?php
$insight     = $args['post'];
$author      = $args['author'] ?? null;
$link_author = ! empty( $args['link_author'] );
$category    = get_the_category( $insight->ID )[0] ?? null;
?>
<p class="<?php echo esc_attr( $args['class'] ?? '' ); ?>">
	<?php if ( $category ) : ?>
		<span class="eyebrow"><?php echo esc_html( $category->name ); ?></span>
	<?php endif; ?>
	<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $insight ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y', $insight ) ); ?></time>
	<?php if ( $author && $link_author ) : ?>
		<span>By <a href="<?php echo esc_url( get_permalink( $author ) ); ?>"><?php echo esc_html( get_the_title( $author ) ); ?></a></span>
	<?php elseif ( $author ) : ?>
		<span>By <?php echo esc_html( get_the_title( $author ) ); ?></span>
	<?php endif; ?>
	<span><?php echo esc_html( wealth_reading_time( $insight ) ); ?></span>
</p>
