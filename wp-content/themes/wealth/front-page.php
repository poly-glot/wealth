<?php
get_header();

get_template_part( 'template-parts/hero-home' );
get_template_part( 'template-parts/home-tabs' );

$strategies = get_posts( array(
	'order'          => 'ASC',
	'orderby'        => 'menu_order',
	'post_type'      => 'strategy',
	'posts_per_page' => -1,
) );

$people = get_posts( array(
	'order'          => 'ASC',
	'orderby'        => 'menu_order',
	'post_type'      => 'team_member',
	'posts_per_page' => -1,
) );

$contact = get_page_by_path( 'contact' );
$map_id  = $contact ? get_post_thumbnail_id( $contact ) : 0;
?>
<section class="section section--grey screen" id="strategies" aria-labelledby="strategies-title">
	<div class="container container--narrow screen__body">
		<h2 class="display section__title" id="strategies-title">Investment strategies</h2>
		<?php get_template_part( 'template-parts/loop-strategies', null, array( 'posts' => $strategies ) ); ?>
		<div class="screen__next">
			<?php get_template_part( 'template-parts/chevron', null, array( 'href' => '#people', 'label' => 'Continue to People' ) ); ?>
		</div>
	</div>
	<svg class="strategy-grid__motif motif motif--light" aria-hidden="true" focusable="false" viewBox="0 0 120 120"><use href="#mark" /></svg>
</section>

<section class="section screen" id="people" aria-labelledby="people-title">
	<div class="container container--narrow screen__body">
		<h2 class="display section__title" id="people-title">People</h2>
		<?php get_template_part( 'template-parts/loop-people', null, array( 'posts' => $people ) ); ?>
		<div class="screen__next">
			<?php get_template_part( 'template-parts/chevron', null, array( 'href' => '#contact', 'label' => 'Continue to Contact' ) ); ?>
		</div>
	</div>
</section>

<section class="contact-band screen" id="contact" aria-labelledby="contact-title">
	<div class="contact-band__header">
		<h2 class="display display--light" id="contact-title">Contact</h2>
	</div>
	<div class="contact-band__map screen__body">
		<?php if ( $map_id ) : ?>
			<?php echo wp_get_attachment_image( $map_id, 'full', false, array( 'class' => 'contact-band__image', 'alt' => '', 'loading' => 'lazy' ) ); ?>
		<?php endif; ?>
		<?php get_template_part( 'template-parts/contact-card' ); ?>
	</div>
</section>
<?php
get_footer();
