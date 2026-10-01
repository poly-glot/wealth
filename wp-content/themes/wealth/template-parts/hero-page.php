<?php
$variant = $args['variant'] ?? 'tan';
$title   = $args['title'] ?? get_the_title();
$lead    = $args['lead'] ?? '';
?>
<header class="page-hero page-hero--<?php echo esc_attr( $variant ); ?>">
	<div class="page-hero__inner container">
		<h1 class="page-hero__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $lead ) : ?>
			<p class="page-hero__lead"><?php echo esc_html( $lead ); ?></p>
		<?php endif; ?>
	</div>
</header>
