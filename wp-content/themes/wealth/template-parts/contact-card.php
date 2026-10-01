<?php
$address_lines = preg_split( '/\R/', trim( (string) get_option( 'address' ) ) );
$address_name  = array_shift( $address_lines );
$email         = (string) get_option( 'email' );
?>
<address class="contact-card">
	<div class="contact-card__row contact-card__address">
		<svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-pin" /></svg>
		<p>
			<span class="contact-card__name"><?php echo esc_html( $address_name ); ?></span>
			<?php echo implode( '<br>', array_map( 'esc_html', $address_lines ) ); ?>
		</p>
	</div>
	<ul class="contact-card__list">
		<li class="contact-card__row">
			<svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-phone" /></svg>
			<a href="<?php echo esc_url( 'tel:' . get_option( 'phone_href' ) ); ?>"><span class="visually-hidden">Telephone </span><?php echo esc_html( wealth_non_breaking( (string) get_option( 'phone' ) ) ); ?></a>
		</li>
		<li class="contact-card__row">
			<svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-at" /></svg>
			<a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><span class="visually-hidden">Email </span><?php echo esc_html( $email ); ?></a>
		</li>
		<li class="contact-card__social">
			<span class="contact-card__social-label">Follow us</span>
			<?php foreach ( get_option( 'social', array() ) as $social ) : ?>
				<a class="icon-link" href="<?php echo esc_url( $social['url'] ?? '' ); ?>" rel="noopener">
					<svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-<?php echo esc_attr( $social['icon'] ?? '' ); ?>" /></svg>
					<span class="visually-hidden"><?php echo esc_html( get_bloginfo( 'name' ) . ' on ' . ( $social['label'] ?? '' ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</li>
	</ul>
</address>
