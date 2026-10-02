<?php

const WEALTH_INVESTOR_TYPES = array(
	'Institutional investor',
	'Wealth manager or adviser',
	'Consultant',
	'Family office',
	'Press',
	'Other',
);

const WEALTH_LEGAL_PAGES = array( 'legal', 'privacy', 'cookies', 'accessibility' );

const WEALTH_GROUP_LABELS = array(
	'executive'  => 'Executive Management',
	'investment' => 'Investment Team',
);

const WEALTH_UPDATED_LABELS = array(
	'reviewed' => 'Last reviewed',
	'updated'  => 'Last updated',
);

function wealth_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

	register_nav_menus( array(
		'primary' => 'Primary',
		'legal'   => 'Legal',
	) );

	add_image_size( 'hero', 1920, 920, true );
	add_image_size( 'hero-medium', 1440, 690, true );
	add_image_size( 'hero-small', 960, 460, true );
	add_image_size( 'feature', 1600, 900, true );
	add_image_size( 'portrait', 900, 1200, true );
	add_image_size( 'card', 800, 450, true );
	add_image_size( 'thumb', 240, 240, true );
}

add_action( 'after_setup_theme', 'wealth_setup' );

function wealth_stylesheets(): array {
	$css   = get_theme_file_path( 'assets/css/' );
	$parts = array_merge( glob( $css . 'atoms/*.css' ), glob( $css . 'components/*.css' ) );

	return array_merge(
		array( 'base/tokens.css', 'base/base.css', 'base/layout.css' ),
		array_map( fn ( $path ) => substr( $path, strlen( $css ) ), $parts )
	);
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'wealth-fonts', 'https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500&family=Sanchez:ital@0;1&display=swap', array(), null );

	$version = wp_get_theme()->get( 'Version' );
	$bundle  = get_theme_file_path( 'assets/css/main.css' );

	if ( file_exists( $bundle ) ) {
		wp_enqueue_style( 'wealth-main', get_theme_file_uri( 'assets/css/main.css' ), array( 'wealth-fonts' ), filemtime( $bundle ) );
	} else {
		$previous = array( 'wealth-fonts' );

		foreach ( wealth_stylesheets() as $sheet ) {
			$handle = 'wealth-' . str_replace( array( '/', '.css' ), array( '-', '' ), $sheet );
			wp_enqueue_style( $handle, get_theme_file_uri( 'assets/css/' . $sheet ), $previous, $version );
			$previous = array( $handle );
		}
	}

	wp_enqueue_script( 'wealth-navigation', get_theme_file_uri( 'assets/js/navigation.js' ), array(), $version, array( 'strategy' => 'defer' ) );
} );

add_filter( 'document_title_separator', fn () => '|' );

add_filter( 'image_editor_output_format', fn ( array $formats ) => $formats + array( 'image/jpeg' => 'image/webp' ) );

function wealth_hero_image_id( int $front_id ): int {
	$ids = array_filter( array_map( 'intval', (array) wealth_meta( 'hero_images', $front_id ) ) );

	return $ids ? $ids[ array_rand( $ids ) ] : (int) get_post_thumbnail_id( $front_id );
}

function wealth_section_urls(): array {
	if ( is_front_page() ) {
		return array( home_url( '/#about-us' ) );
	}

	if ( is_singular( 'strategy' ) || is_post_type_archive( 'strategy' ) ) {
		return array( get_post_type_archive_link( 'strategy' ), home_url( '/#strategies' ) );
	}

	if ( is_singular( 'team_member' ) || is_post_type_archive( 'team_member' ) ) {
		return array( get_post_type_archive_link( 'team_member' ), home_url( '/#people' ) );
	}

	if ( is_home() || is_singular( 'post' ) || is_category() ) {
		return array( wealth_news_url() );
	}

	if ( is_page( 'contact' ) ) {
		return array( wealth_page_url( 'contact' ) );
	}

	if ( is_page( WEALTH_LEGAL_PAGES ) ) {
		return array( wealth_page_url( 'legal' ) );
	}

	return array();
}

add_filter( 'nav_menu_css_class', function ( $classes, $item, $args ) {
	return 'primary' === $args->theme_location ? array( 'site-nav__item' ) : array();
}, 10, 3 );

add_filter( 'nav_menu_item_id', fn () => '' );

add_filter( 'nav_menu_link_attributes', function ( $atts, $item, $args ) {
	static $section_urls = null;

	if ( 'primary' !== $args->theme_location ) {
		return $atts;
	}

	$section_urls ??= array_map( 'untrailingslashit', wealth_section_urls() );

	$atts['class']        = 'site-nav__link';
	$atts['aria-current'] = in_array( untrailingslashit( $item->url ), $section_urls, true ) ? 'page' : '';

	return $atts;
}, 10, 3 );

function wealth_meta( string $name, ?int $post_id = null ) {
	return get_post_meta( $post_id ?? get_the_ID(), $name, true );
}

function wealth_page_url( string $slug ): string {
	$page = get_page_by_path( $slug );

	return $page ? get_permalink( $page ) : home_url( '/' );
}

function wealth_news_url(): string {
	$page_id = (int) get_option( 'page_for_posts' );

	return $page_id ? get_permalink( $page_id ) : home_url( '/' );
}

function wealth_reading_time( WP_Post $post ): string {
	$words = str_word_count( wp_strip_all_tags( $post->post_content ) );

	return max( 1, (int) ceil( $words / 230 ) ) . ' min read';
}

function wealth_initials( string $name ): string {
	$words = preg_split( '/\s+/', trim( $name ) );
	$first = mb_substr( $words[0] ?? '', 0, 1 );
	$last  = count( $words ) > 1 ? mb_substr( end( $words ), 0, 1 ) : '';

	return mb_strtoupper( $first . $last );
}

function wealth_paragraphs( string $html ): string {
	return str_contains( $html, '<p' ) ? $html : wpautop( $html );
}

function wealth_non_breaking( string $text ): string {
	return str_replace( ' ', "\u{00A0}", $text );
}

function wealth_posts_by_id( array $ids, string $post_type ): array {
	$ids = array_filter( array_map( 'intval', $ids ) );

	if ( ! $ids ) {
		return array();
	}

	$posts = get_posts( array(
		'orderby'        => 'post__in',
		'post__in'       => $ids,
		'post_type'      => $post_type,
		'posts_per_page' => -1,
	) );

	return array_combine( wp_list_pluck( $posts, 'ID' ), $posts );
}

function wealth_group_label( string $key ): string {
	return WEALTH_GROUP_LABELS[ $key ] ?? '';
}

function wealth_strategies_managed_by( int $person_id ): array {
	$strategies = get_posts( array(
		'order'          => 'ASC',
		'orderby'        => 'menu_order',
		'post_type'      => 'strategy',
		'posts_per_page' => -1,
	) );

	$by_role = array();

	foreach ( $strategies as $strategy ) {
		$managers = array_map( 'intval', (array) wealth_meta( 'managers', $strategy->ID ) );
		$position = array_search( $person_id, $managers, true );

		if ( false !== $position ) {
			$by_role[ $position ][] = $strategy;
		}
	}

	ksort( $by_role );

	return array_merge( ...$by_role );
}

function wealth_names_sentence( array $people ): string {
	$links = array_map(
		fn ( WP_Post $person ) => '<a href="' . esc_url( get_permalink( $person ) ) . '">' . esc_html( get_the_title( $person ) ) . '</a>',
		array_values( $people )
	);
	$last  = array_pop( $links );

	return $links ? implode( ', ', $links ) . ' and ' . $last : (string) $last;
}

function wealth_latest_insight_for( int $person_id ): ?WP_Post {
	$posts = get_posts( array(
		'meta_key'       => 'author_profile',
		'meta_value'     => $person_id,
		'posts_per_page' => 1,
	) );

	return $posts[0] ?? null;
}

function wealth_content_sections( string $html ): array {
	$chunks   = preg_split( '/<h2(?:\s+id="([^"]*)")?[^>]*>(.*?)<\/h2>/s', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	$intro    = array_shift( $chunks );
	$sections = array();

	foreach ( array_chunk( $chunks, 3 ) as [ $id, $heading, $body ] ) {
		$sections[] = array(
			'body'    => $body,
			'heading' => $heading,
			'id'      => $id ?: sanitize_title( wp_strip_all_tags( $heading ) ),
		);
	}

	return array(
		'intro'    => $intro,
		'sections' => $sections,
	);
}

function wealth_cta_args(): array {
	$cta = array(
		'body'    => (string) get_option( 'cta_default_body' ),
		'heading' => get_option( 'cta_default_heading' ) ?: 'Start a conversation',
	);

	if ( is_singular() ) {
		foreach ( array( 'heading' => 'cta_heading', 'body' => 'cta_body' ) as $arg => $meta ) {
			$value = (string) wealth_meta( $meta );

			if ( '' !== $value ) {
				$cta[ $arg ] = $value;
			}
		}
	}

	return $cta;
}

function wealth_portrait( int $post_id, string $size, array $attributes ): void {
	if ( has_post_thumbnail( $post_id ) ) {
		echo get_the_post_thumbnail( $post_id, $size, $attributes );

		return;
	}

	?>
	<div class="<?php echo esc_attr( $attributes['class'] ?? '' ); ?>" aria-hidden="true"><?php echo esc_html( wealth_initials( get_the_title( $post_id ) ) ); ?></div>
	<?php
}

add_action( 'admin_post_nopriv_wealth_enquiry', 'wealth_handle_enquiry' );
add_action( 'admin_post_wealth_enquiry', 'wealth_handle_enquiry' );

function wealth_handle_enquiry(): void {
	$back = wp_get_referer() ?: wealth_page_url( 'contact' );

	if ( ! isset( $_POST['wealth_enquiry_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wealth_enquiry_nonce'] ) ), 'wealth_enquiry' ) ) {
		wp_safe_redirect( $back );
		exit;
	}

	if ( ! empty( $_POST['website'] ) ) {
		wp_safe_redirect( add_query_arg( 'sent', '1', $back ) );
		exit;
	}

	$investor_type = sanitize_text_field( wp_unslash( $_POST['investor_type'] ?? '' ) );

	$fields = array(
		'name'          => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
		'organisation'  => sanitize_text_field( wp_unslash( $_POST['organisation'] ?? '' ) ),
		'email'         => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
		'phone'         => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
		'investor_type' => in_array( $investor_type, WEALTH_INVESTOR_TYPES, true ) ? $investor_type : '',
		'message'       => sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) ),
		'consent'       => empty( $_POST['consent'] ) ? '' : 'yes',
	);

	if ( '' === $fields['name'] || '' === $fields['email'] || '' === $fields['message'] || '' === $fields['consent'] ) {
		wp_safe_redirect( $back );
		exit;
	}

	wp_insert_post( array(
		'meta_input'  => $fields,
		'post_status' => 'private',
		'post_title'  => $fields['name'] . ' — ' . wp_date( 'j F Y H:i' ),
		'post_type'   => 'enquiry',
	) );

	wp_safe_redirect( add_query_arg( 'sent', '1', $back ) );
	exit;
}
