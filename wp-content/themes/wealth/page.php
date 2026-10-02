<?php
get_header();

while ( have_posts() ) {
	the_post();

	get_template_part( 'template-parts/hero-page', null, array( 'variant' => 'aqua' ) );

	$content = wealth_content_sections( apply_filters( 'the_content', get_the_content() ) );

	preg_match( '/^((?:\s*<p class="legal__[a-z]+">.*?<\/p>)*)(.*)$/s', $content['intro'], $intro );
	?>
	<div class="with-aside container">
		<div class="with-aside__main legal">
			<?php echo wp_kses_post( $intro[1] ); ?>
			<div class="prose">
				<?php echo wp_kses_post( $intro[2] ); ?>
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
