<?php $cta = wealth_cta_args(); ?>
<section class="cta-band" aria-labelledby="cta-title">
	<div class="cta-band__inner container">
		<h2 class="cta-band__title" id="cta-title"><?php echo esc_html( $cta['heading'] ); ?></h2>
		<?php if ( $cta['body'] ) : ?>
			<p class="cta-band__body"><?php echo esc_html( $cta['body'] ); ?></p>
		<?php endif; ?>
		<a class="button button--light" href="<?php echo esc_url( wealth_page_url( 'contact' ) ); ?>">Contact us</a>
	</div>
</section>
