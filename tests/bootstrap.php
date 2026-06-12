<?php
//phpcs:ignoreFile

use WooCommerceSerialNumbers\Installer;

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Detect where to load the WordPress test environment from.
if ( false !== getenv( 'WP_TESTS_DIR' ) ) {
	$_tests_dir = getenv( 'WP_TESTS_DIR' );
} elseif ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$_tests_dir = getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit';
} elseif ( false !== getenv( 'WP_PHPUNIT__DIR' ) ) {
	$_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
} else {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php\n";
	exit( 1 );
}

require_once "{$_tests_dir}/includes/functions.php";

/**
 * Load WooCommerce and the plugin under test.
 *
 * WooCommerce must load first — Serial Numbers self-registers on `woocommerce_loaded`.
 *
 * @return void
 */
function _manually_load_plugins() {
	require_once WP_CONTENT_DIR . '/plugins/woocommerce/woocommerce.php';
	require_once dirname( __DIR__ ) . '/wc-serial-numbers.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugins' );

require_once "{$_tests_dir}/includes/bootstrap.php";

// Create the WooCommerce + plugin tables now that WordPress has loaded.
WC_Install::install();

// Serial Numbers reads orders via get_post(), so keep CPT order storage (HPOS off).
update_option( 'woocommerce_custom_orders_table_enabled', 'no' );

Installer::install();