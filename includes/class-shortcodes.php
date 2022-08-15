<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

class Shortcodes {

	/**
	 * Shortcodes constructor.
	 */
	public function __construct() {
		add_shortcode( 'wc_serial_numbers_validate_key', array( __CLASS__, 'validate_key' ) );
	}


	/**
	 * Validate key.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public static function validate_key( $atts ) {
//		$atts = shortcode_atts( array(
//			'key'   => '',
//			'email' => '',
//		), $atts );
//
//		$key     = ;
//		$product = Product::get_by_key( $key );
//		if ( $product ) {
//			return '<p>' . __( 'Valid key', 'wc-serial-numbers' ) . '</p>';
//		}
//
//		return '<p>' . __( 'Invalid key', 'wc-serial-numbers' ) . '</p>';
	}
}
