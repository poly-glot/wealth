<header class="site-header">
	<div class="site-header__inner container">
		<?php get_template_part( 'template-parts/logo' ); ?>
		<nav class="site-nav" aria-label="Primary">
			<button class="site-nav__toggle" type="button" aria-expanded="false" aria-controls="site-nav-list" hidden>
				<svg class="icon site-nav__open-icon" aria-hidden="true" focusable="false"><use href="#icon-menu" /></svg>
				<svg class="icon site-nav__close-icon" aria-hidden="true" focusable="false"><use href="#icon-close" /></svg>
				<span class="visually-hidden">Menu</span>
			</button>
			<?php
			wp_nav_menu( array(
				'container'      => false,
				'fallback_cb'    => false,
				'items_wrap'     => '<ul class="%2$s" id="%1$s">%3$s</ul>',
				'menu_class'     => 'site-nav__list',
				'menu_id'        => 'site-nav-list',
				'theme_location' => 'primary',
			) );
			?>
		</nav>
	</div>
</header>
