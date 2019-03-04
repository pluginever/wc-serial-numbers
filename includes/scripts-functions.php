<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wc_serial_numbers_load_admin_scripts( $hook ) {
	$js_dir     = WC_SERIAL_NUMBERS_ASSETS_URL . '/js/';
	$css_dir    = WC_SERIAL_NUMBERS_ASSETS_URL . '/css/';
	$vendor_dir = WC_SERIAL_NUMBERS_ASSETS_URL . '/vendor/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.js' : '.min.js';

	//styles
	wp_enqueue_style( 'wc-serial-numbers', $css_dir . "/admin.css", [ 'woocommerce_admin_styles' ], WC_SERIAL_NUMBERS_VERSION );

	//scripts
	wp_enqueue_style( 'jquery-ui-style' );
	wp_enqueue_script( 'jquery-ui-datepicker' );

	wp_enqueue_script( 'wc-serial-numbers', $js_dir . "/admin{$suffix}", [ 'jquery', 'wp-util', 'select2', ], WC_SERIAL_NUMBERS_VERSION, true );
}

add_action( 'admin_enqueue_scripts', 'wc_serial_numbers_load_admin_scripts', 100 );
