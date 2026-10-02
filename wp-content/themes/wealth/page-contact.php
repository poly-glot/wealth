<?php
get_header();

while ( have_posts() ) {
	the_post();

	get_template_part( 'template-parts/hero-page', null, array(
		'lead'    => (string) wealth_meta( 'subtitle' ),
		'variant' => 'slate',
	) );

	$content = wealth_content_sections( apply_filters( 'the_content', get_the_content() ) );
	?>
	<div class="with-aside container">
		<div class="with-aside__main">
			<div class="prose">
				<?php echo wp_kses_post( $content['intro'] ); ?>
			</div>
			<?php foreach ( $content['sections'] as $section ) : ?>
				<section class="prose" aria-labelledby="<?php echo esc_attr( $section['id'] ); ?>">
					<h2 id="<?php echo esc_attr( $section['id'] ); ?>"><?php echo wp_kses_post( $section['heading'] ); ?></h2>
					<?php echo wp_kses_post( $section['body'] ); ?>
				</section>
			<?php endforeach; ?>
			<section aria-labelledby="form-title">
				<?php if ( isset( $_GET['sent'] ) ) : ?>
					<div class="prose">
						<h2 id="form-title">Thank you</h2>
						<p>We have received your message and a member of the team will reply within two working days. If your enquiry is urgent, call us on <a href="<?php echo esc_url( 'tel:' . get_option( 'phone_href' ) ); ?>"><?php echo esc_html( wealth_non_breaking( (string) get_option( 'phone' ) ) ); ?></a>.</p>
						<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to the home page</a></p>
					</div>
				<?php else : ?>
					<div class="prose">
						<h2 id="form-title">Send us a message</h2>
						<p>Fields marked optional can be left blank. Everything else is needed so that we can reply.</p>
					</div>
					<?php get_template_part( 'template-parts/contact-form' ); ?>
				<?php endif; ?>
			</section>
		</div>
		<div class="with-aside__aside">
			<?php get_template_part( 'template-parts/contact-card' ); ?>
		</div>
	</div>
	<?php
}

get_footer();
