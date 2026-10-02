<?php
get_header();

get_template_part( 'template-parts/hero-page', null, array(
	'lead'    => 'The page you were looking for has moved or no longer exists. Check the address, or use one of the links below.',
	'title'   => 'Page not found',
	'variant' => 'slate',
) );
?>
<div class="section">
	<div class="container">
		<div class="button-row">
			<a class="button button--solid" href="<?php echo esc_url( home_url( '/' ) ); ?>">Go to the home page</a>
			<a class="button" href="<?php echo esc_url( wealth_page_url( 'contact' ) ); ?>">Contact us</a>
		</div>
	</div>
</div>
<?php
get_footer();
