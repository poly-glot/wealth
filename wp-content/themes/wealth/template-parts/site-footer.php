<footer class="site-footer">
	<div class="site-footer__inner container container--narrow">
		<?php get_template_part( 'template-parts/logo' ); ?>
		<div class="site-footer__text">
			<p><?php echo esc_html( (string) get_option( 'footer_notice' ) ); ?></p>
			<nav aria-label="Legal">
				<?php
				wp_nav_menu( array(
					'container'      => false,
					'fallback_cb'    => false,
					'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
					'menu_class'     => 'site-footer__links',
					'theme_location' => 'legal',
				) );
				?>
			</nav>
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( (string) get_option( 'legal_name' ) ); ?>. Registered in England and Wales, number <?php echo esc_html( (string) get_option( 'company_number' ) ); ?>. Registered office: <?php echo esc_html( (string) get_option( 'registered_office' ) ); ?>.</p>
		</div>
	</div>
</footer>
