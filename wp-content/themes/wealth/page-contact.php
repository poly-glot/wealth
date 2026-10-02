<?php
get_header();

while ( have_posts() ) {
	the_post();

	get_template_part( 'template-parts/hero-page', null, array(
		'lead'    => (string) wealth_meta( 'subtitle' ),
		'variant' => 'slate',
	) );

	$intro      = (string) wealth_meta( 'contact_intro' );
	$steps      = wealth_meta( 'contact_steps' ) ?: array();
	$groups     = wealth_meta( 'contact_groups' ) ?: array();
	$form_intro = (string) wealth_meta( 'form_intro' );
	?>
	<div class="with-aside container">
		<div class="with-aside__main">
			<?php if ( $intro ) : ?>
				<div class="prose">
					<p><?php echo esc_html( $intro ); ?></p>
				</div>
			<?php endif; ?>
			<?php if ( $steps ) : ?>
				<section class="prose" aria-labelledby="next-title">
					<h2 id="next-title">What happens next</h2>
					<ol>
						<?php foreach ( $steps as $step ) : ?>
							<li><strong><?php echo esc_html( $step['title'] ?? '' ); ?></strong> <?php echo esc_html( $step['text'] ?? '' ); ?></li>
						<?php endforeach; ?>
					</ol>
				</section>
			<?php endif; ?>
			<?php if ( $groups ) : ?>
				<section class="prose" aria-labelledby="who-title">
					<h2 id="who-title">Who to contact</h2>
					<?php foreach ( $groups as $group ) : ?>
						<?php
						$links = array();

						if ( ! empty( $group['email'] ) ) {
							$links[] = '<a href="' . esc_url( 'mailto:' . $group['email'] ) . '">' . esc_html( $group['email'] ) . '</a>';
						}

						if ( ! empty( $group['phone'] ) ) {
							$links[] = '<a href="' . esc_url( 'tel:' . ( $group['phone_href'] ?? '' ) ) . '">' . esc_html( wealth_non_breaking( $group['phone'] ) ) . '</a>';
						}
						?>
						<h3><?php echo esc_html( $group['title'] ?? '' ); ?></h3>
						<?php echo wpautop( esc_html( $group['text'] ?? '' ) ); ?>
						<?php if ( $links ) : ?>
							<p><?php echo implode( ' · ', $links ); ?></p>
						<?php endif; ?>
					<?php endforeach; ?>
				</section>
			<?php endif; ?>
			<section aria-labelledby="form-title">
				<?php if ( isset( $_GET['sent'] ) ) : ?>
					<div class="prose">
						<h2 id="form-title">Thank you</h2>
						<?php echo wp_kses_post( wealth_paragraphs( (string) wealth_meta( 'thank_you' ) ) ); ?>
					</div>
				<?php else : ?>
					<div class="prose">
						<h2 id="form-title">Send us a message</h2>
						<?php if ( $form_intro ) : ?>
							<p><?php echo esc_html( $form_intro ); ?></p>
						<?php endif; ?>
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
