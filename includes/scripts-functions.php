<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wc_serial_numbers_scripts( $hook ) {

	$css_dir = WC_SERIAL_NUMBERS_ASSETS_URL . '/css/';

	//styles
	wp_enqueue_style( 'wc-serial-numbers', $css_dir . "/frontend.css", [], WC_SERIAL_NUMBERS_VERSION );
}

add_action( 'wp_enqueue_scripts', 'wc_serial_numbers_scripts' );

function wc_serial_numbers_load_admin_scripts( $hook ) {

	$hooks = array(
		'toplevel_page_wc-serial-numbers',
		'serial-numbers_page_wcsn-api-doc',
		'serial-numbers_page_wcsn-generator-rules',
		'serial-numbers_page_woocommerce-serial-numbers-pro-license',
		'serial-numbers_page_wc-serial-numbers-settings',
		'post.php'
	);

	if ( ! in_array( $hook, $hooks ) ) {
		return;
	}

	if('post.php' == $hook && 'shop_order' != get_post_type()){
		return;
	}


	$js_dir     = WC_SERIAL_NUMBERS_ASSETS_URL . '/js/';
	$css_dir    = WC_SERIAL_NUMBERS_ASSETS_URL . '/css/';
	$vendor_dir = WC_SERIAL_NUMBERS_ASSETS_URL . '/vendor/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.js' : '.min.js';

	//styles
	wp_enqueue_style( 'jquery-ui-style' );
	wp_enqueue_style( 'wc-serial-numbers', $css_dir . "/admin.css", [ 'woocommerce_admin_styles' ], WC_SERIAL_NUMBERS_VERSION );

	//scripts
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'wc-serial-numbers', $js_dir . "/admin.js", [
		'jquery',
		'wp-util',
		'select2',
	], WC_SERIAL_NUMBERS_VERSION, true );
}

add_action( 'admin_enqueue_scripts', 'wc_serial_numbers_load_admin_scripts', 100 );
