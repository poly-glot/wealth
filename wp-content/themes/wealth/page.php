<?php
get_header();

while ( have_posts() ) {
	the_post();

	get_template_part( 'template-parts/hero-page', null, array( 'variant' => 'aqua' ) );

	$content       = wealth_content_sections( apply_filters( 'the_content', get_the_content() ) );
	$notice        = preg_split( '/(?<=\.)\s+/', trim( (string) wealth_meta( 'notice' ) ), 2 );
	$last_updated  = (string) wealth_meta( 'last_updated' );
	$updated_label = WEALTH_UPDATED_LABELS[ (string) wealth_meta( 'updated_label' ) ] ?? WEALTH_UPDATED_LABELS['updated'];
	?>
	<div class="with-aside container">
		<div class="with-aside__main legal">
			<?php if ( '' !== $notice[0] ) : ?>
				<p class="legal__notice"><strong><?php echo esc_html( $notice[0] ); ?></strong> <?php echo esc_html( $notice[1] ?? '' ); ?></p>
			<?php endif; ?>
			<?php if ( $last_updated ) : ?>
				<p class="legal__updated"><?php echo esc_html( $updated_label . ' ' . wp_date( 'j F Y', strtotime( $last_updated ) ) ); ?></p>
			<?php endif; ?>
			<div class="prose">
				<?php echo wp_kses_post( $content['intro'] ); ?>
				<?php foreach ( $content['sections'] as $section ) : ?>
					<h2 id="<?php echo esc_attr( $section['id'] ); ?>"><?php echo wp_kses_post( $section['heading'] ); ?></h2>
					<?php echo wp_kses_post( $section['body'] ); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		if ( $content['sections'] ) {
			get_template_part( 'template-parts/legal-toc', null, array( 'sections' => $content['sections'] ) );
		}
		?>
	</div>
	<?php
}

get_footer();
