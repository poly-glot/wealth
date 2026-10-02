<?php $front_id = (int) get_option( 'page_on_front' ); ?>
<section class="home-hero screen screen--below-header" aria-labelledby="hero-title">
	<?php echo wp_get_attachment_image( wealth_hero_image_id( $front_id ), 'hero', false, array( 'class' => 'home-hero__image', 'alt' => '', 'fetchpriority' => 'high', 'sizes' => '100vw' ) ); ?>
	<div class="home-hero__panel">
		<h1 class="home-hero__title" id="hero-title">
			<?php foreach ( array( 'hero_line_1', 'hero_line_2', 'hero_line_3' ) as $line ) : ?>
				<span class="home-hero__line"><?php echo esc_html( wealth_meta( $line, $front_id ) ); ?></span>
			<?php endforeach; ?>
		</h1>
		<p class="home-hero__text"><?php echo esc_html( wealth_meta( 'hero_body', $front_id ) ); ?></p>
	</div>
	<div class="home-hero__next">
		<?php get_template_part( 'template-parts/chevron', null, array( 'href' => '#about-us', 'label' => 'Continue to About us', 'light' => true ) ); ?>
	</div>
</section>
