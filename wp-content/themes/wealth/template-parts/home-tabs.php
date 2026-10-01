<?php
$front_id = (int) get_option( 'page_on_front' );
$panels   = array(
	'about-us'                 => array( 'about_heading', 'about_body' ),
	'investment-opportunities' => array( 'opportunities_heading', 'opportunities_body' ),
);
?>
<section class="tabs" aria-labelledby="tabs-title">
	<h2 class="visually-hidden" id="tabs-title">About Wealth</h2>
	<ul class="tabs__list">
		<li><a class="tabs__tab" href="#about-us">About us</a></li>
		<li><a class="tabs__tab" href="#investment-opportunities">Investment opportunities</a></li>
	</ul>
	<?php foreach ( $panels as $panel_id => [ $heading, $body ] ) : ?>
		<div class="tabs__panel" id="<?php echo esc_attr( $panel_id ); ?>" tabindex="-1">
			<div class="tabs__inner container">
				<svg class="tabs__motif motif" aria-hidden="true" focusable="false" viewBox="0 0 120 120"><use href="#mark" /></svg>
				<div class="tabs__copy prose prose--large">
					<h3 class="visually-hidden"><?php echo esc_html( wealth_meta( $heading, $front_id ) ); ?></h3>
					<?php echo wp_kses_post( wealth_paragraphs( (string) wealth_meta( $body, $front_id ) ) ); ?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
	<div class="tabs__next">
		<?php get_template_part( 'template-parts/chevron', null, array( 'href' => '#strategies', 'label' => 'Continue to Investment strategies' ) ); ?>
	</div>
</section>
