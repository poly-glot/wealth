<?php
get_header();

while ( have_posts() ) {
	the_post();

	$author_id = (int) wealth_meta( 'author_profile' );
	$author    = $author_id ? get_post( $author_id ) : null;
	?>
	<article class="article" aria-labelledby="article-title">
		<header class="article__header container">
			<?php
			get_template_part( 'template-parts/insight-meta', null, array(
				'author'      => $author,
				'class'       => 'article__meta',
				'link_author' => true,
				'post'        => get_post(),
			) );
			?>
			<h1 class="article__title" id="article-title"><?php echo esc_html( get_the_title() ); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="article__standfirst"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</header>
		<?php the_post_thumbnail( 'feature', array( 'class' => 'feature-image' ) ); ?>
		<div class="article__body container container--measure">
			<div class="prose">
				<?php the_content(); ?>
			</div>
		</div>
		<?php if ( $author ) : ?>
			<aside class="article__author" aria-label="About the author">
				<?php wealth_portrait( $author->ID, 'thumb', array( 'alt' => '', 'class' => 'article__author-image', 'loading' => 'lazy' ) ); ?>
				<p class="article__author-name"><a href="<?php echo esc_url( get_permalink( $author ) ); ?>"><?php echo esc_html( get_the_title( $author ) ); ?></a></p>
				<p class="article__author-role"><?php echo esc_html( wealth_meta( 'role', $author->ID ) ); ?></p>
			</aside>
		<?php endif; ?>
	</article>
	<div class="section">
		<div class="container container--measure">
			<a class="button" href="<?php echo esc_url( wealth_news_url() ); ?>">
				<svg class="icon button__icon" aria-hidden="true" focusable="false"><use href="#icon-arrow-left" /></svg>
				All insights
			</a>
		</div>
	</div>
	<?php
	get_template_part( 'template-parts/cta-band' );
}

get_footer();
