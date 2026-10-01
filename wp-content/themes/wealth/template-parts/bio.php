<?php
$name       = get_the_title();
$linkedin   = (string) wealth_meta( 'linkedin' );
$x_url      = (string) wealth_meta( 'x' );
$vcard      = 'assets/vcards/' . get_post_field( 'post_name' ) . '.vcf';
$strategies = wealth_posts_by_id( (array) wealth_meta( 'strategies' ), 'strategy' );
$insight    = wealth_latest_insight_for( get_the_ID() );
$category   = $insight ? ( get_the_category( $insight->ID )[0] ?? null ) : null;
?>
<article class="bio container container--narrow" aria-labelledby="bio-name">
	<div class="bio__panel">
		<div class="bio__body">
			<h1 class="bio__name" id="bio-name"><?php echo esc_html( $name ); ?></h1>
			<p class="bio__role"><?php echo esc_html( wealth_meta( 'role' ) ); ?></p>
			<p class="bio__role"><?php echo esc_html( wealth_meta( 'group' ) ); ?></p>
			<div class="bio__text">
				<p><?php echo esc_html( wealth_meta( 'lead' ) ); ?></p>
				<?php the_content(); ?>
			</div>
			<div class="bio__actions">
				<ul class="bio__connect">
					<li class="bio__connect-label">Connect</li>
					<?php if ( $linkedin ) : ?>
						<li><a class="icon-link icon-link--round" href="<?php echo esc_url( $linkedin ); ?>" rel="noopener"><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-linkedin" /></svg><span class="visually-hidden"><?php echo esc_html( $name ); ?> on LinkedIn</span></a></li>
					<?php endif; ?>
					<?php if ( $x_url ) : ?>
						<li><a class="icon-link icon-link--round" href="<?php echo esc_url( $x_url ); ?>" rel="noopener"><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-x" /></svg><span class="visually-hidden"><?php echo esc_html( $name ); ?> on X</span></a></li>
					<?php endif; ?>
				</ul>
				<?php if ( file_exists( get_theme_file_path( $vcard ) ) ) : ?>
					<a class="bio__vcard" href="<?php echo esc_url( get_theme_file_uri( $vcard ) ); ?>" download>Download vCard<span class="visually-hidden"> for <?php echo esc_html( $name ); ?></span><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-vcard" /></svg></a>
				<?php endif; ?>
			</div>
			<?php if ( $strategies ) : ?>
				<div>
					<h2 class="fact-list__title">Manages</h2>
					<ul class="doc-list">
						<?php foreach ( $strategies as $strategy ) : ?>
							<li><a class="doc-list__link" href="<?php echo esc_url( get_permalink( $strategy ) ); ?>"><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-arrow-right" /></svg><?php echo esc_html( get_the_title( $strategy ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<?php if ( $insight ) : ?>
				<div class="bio__insight">
					<?php echo get_the_post_thumbnail( $insight, 'thumb', array( 'class' => 'bio__insight-image', 'alt' => '', 'loading' => 'lazy' ) ); ?>
					<h2 class="bio__insight-label">Latest insight</h2>
					<p class="bio__insight-meta">
						<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $insight ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y', $insight ) ); ?></time>
						<?php if ( $category ) : ?>
							in <?php echo esc_html( $category->name ); ?>
						<?php endif; ?>
					</p>
					<h3 class="bio__insight-title"><a href="<?php echo esc_url( get_permalink( $insight ) ); ?>"><?php echo esc_html( get_the_title( $insight ) ); ?></a></h3>
				</div>
			<?php endif; ?>
		</div>
		<?php
		wealth_portrait( get_the_ID(), 'portrait', array(
			'alt'   => 'Portrait of ' . $name,
			'class' => 'bio__portrait',
		) );
		?>
	</div>
	<div class="bio__back">
		<a class="button" href="<?php echo esc_url( get_post_type_archive_link( 'team_member' ) ); ?>">
			<svg class="icon button__icon" aria-hidden="true" focusable="false"><use href="#icon-arrow-left" /></svg>
			Back to people
		</a>
	</div>
</article>
