<?php

$wp_load = dirname( __DIR__, 2 ) . '/wp-load.php';

if ( getenv( 'WP_BOOTSTRAP_SEEDING' ) ) {
	require $wp_load;
	require __DIR__ . '/seed.php';
	return;
}

define( 'WP_INSTALLING', true );
define( 'WP_USE_THEMES', false );
require $wp_load;

if ( is_blog_installed() ) {
	echo "install: already installed\n";
} else {
	$admin_pass = getenv( 'WP_ADMIN_PASS' );
	if ( ! $admin_pass ) {
		fwrite( STDERR, "WP_ADMIN_PASS is required for the first install\n" );
		exit( 1 );
	}

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	wp_install(
		getenv( 'WP_TITLE' ) ?: 'WordPress',
		getenv( 'WP_ADMIN_USER' ) ?: 'admin',
		getenv( 'WP_ADMIN_EMAIL' ) ?: 'admin@example.com',
		true,
		'',
		$admin_pass
	);
	echo "install: done\n";
}

if ( ! file_exists( __DIR__ . '/seed.php' ) ) {
	return;
}

putenv( 'WP_BOOTSTRAP_SEEDING=1' );
passthru( PHP_BINARY . ' ' . escapeshellarg( __FILE__ ), $code );
exit( $code );
