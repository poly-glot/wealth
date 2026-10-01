<?php
$people  = $args['posts'] ?? array();
$heading = $args['heading'] ?? 'h3';
?>
<ul class="people-grid">
	<?php foreach ( $people as $person ) : ?>
		<?php $name = get_the_title( $person ); ?>
		<li class="person-card">
			<?php
			wealth_portrait( $person->ID, 'portrait', array(
				'alt'     => 'Portrait of ' . $name,
				'class'   => 'person-card__image',
				'loading' => 'lazy',
			) );
			?>
			<div class="person-card__caption">
				<<?php echo tag_escape( $heading ); ?> class="person-card__name">
					<a href="<?php echo esc_url( get_permalink( $person ) ); ?>"><?php echo esc_html( $name ); ?><svg class="icon icon--small" aria-hidden="true" focusable="false"><use href="#icon-arrow-right" /></svg></a>
				</<?php echo tag_escape( $heading ); ?>>
				<p class="person-card__role"><?php echo esc_html( wealth_meta( 'role', $person->ID ) ); ?></p>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
