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

//	wp_localize_script( 'wc-serial-numbers', 'wpwsn', [
//		'ajaxurl' => admin_url( 'admin-ajax.php' ),
//		'nonce'   => wp_create_nonce( 'wc-serial-numbers' ),
//		'i18n'    => array(
//			'serial_number_activated'    => __( 'Serial Number Activated.', 'wc-serial-numbers' ),
//			'serial_number_deactivated'  => __( 'Serial Number Deactivated.', 'wc-serial-numbers' ),
//			'empty_serial_number_notice' => __( 'The Serial Number is empty. Please enter a serial number and try again.', 'wc-serial-numbers' ),
//			'generate_confirm'           => __( 'Are you sure to generate ', 'wc-serial-numbers' ),
//			'generate_success'           => __( ' Keys generated successfully.', 'wc-serial-numbers' ),
//			'generate_number_validate'   => __( 'Please, enter a valid number.', 'wc-serial-numbers' )
//		)
//	] );
}

add_action( 'admin_enqueue_scripts', 'wc_serial_numbers_load_admin_scripts', 100 );
