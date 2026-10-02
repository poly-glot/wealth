<?php
get_header();

while ( have_posts() ) {
	the_post();

	get_template_part( 'template-parts/hero-page', null, array(
		'lead'    => (string) wealth_meta( 'tagline' ),
		'variant' => (string) wealth_meta( 'tint' ) ?: 'tan',
	) );

	$feature_id = (int) wealth_meta( 'feature_image' );

	if ( $feature_id ) {
		echo wp_get_attachment_image( $feature_id, 'feature', false, array( 'class' => 'feature-image', 'alt' => '' ) );
	}
	?>
	<div class="with-aside container">
		<div class="with-aside__main">
			<?php
			get_template_part( 'template-parts/strategy-overview' );
			get_template_part( 'template-parts/strategy-approach' );
			get_template_part( 'template-parts/strategy-risks' );
			get_template_part( 'template-parts/strategy-faqs' );
			?>
		</div>
		<aside class="with-aside__aside" aria-label="Strategy details">
			<?php
			get_template_part( 'template-parts/strategy-facts' );
			get_template_part( 'template-parts/strategy-documents' );
			?>
			<a class="button" href="<?php echo esc_url( get_post_type_archive_link( 'strategy' ) ); ?>">
				<svg class="icon button__icon" aria-hidden="true" focusable="false"><use href="#icon-arrow-left" /></svg>
				All strategies
			</a>
		</aside>
	</div>
	<?php
	get_template_part( 'template-parts/cta-band' );
}

get_footer();
