<?php
$insights = $args['posts'] ?? array();
$authors  = wealth_posts_by_id( array_map( fn ( WP_Post $insight ) => (int) wealth_meta( 'author_profile', $insight->ID ), $insights ), 'team_member' );
?>
<ul class="insight-grid">
	<?php foreach ( $insights as $insight ) : ?>
		<li class="insight-card">
			<h2 class="insight-card__title"><a href="<?php echo esc_url( get_permalink( $insight ) ); ?>"><?php echo esc_html( get_the_title( $insight ) ); ?></a></h2>
			<?php
			get_template_part( 'template-parts/insight-meta', null, array(
				'author' => $authors[ (int) wealth_meta( 'author_profile', $insight->ID ) ] ?? null,
				'class'  => 'insight-card__meta',
				'post'   => $insight,
			) );
			?>
			<?php echo get_the_post_thumbnail( $insight, 'card', array( 'class' => 'insight-card__image', 'alt' => '', 'loading' => 'lazy' ) ); ?>
			<p class="insight-card__excerpt"><?php echo esc_html( get_the_excerpt( $insight ) ); ?></p>
		</li>
	<?php endforeach; ?>
</ul>
