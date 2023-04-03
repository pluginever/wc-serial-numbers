<?php
/**
 * Template functions.
 *
 * @since 1.4.6
 * @package WooCommerceSerialNumbers/Functions
 */

defined( 'ABSPATH' ) || exit;

/**
 * A wrapper function for wc_get_template.
 *
 * @param string $template_name Template name.
 * @param array $args Arguments. (default: array).
 *
 * @since 1.4.6
 * @return void
 */
function wcsn_get_template( $template_name, $args = array() ) {
	$template_name = apply_filters( 'wcsn_get_template', $template_name, $args );
	wc_get_template( $template_name, $args, 'wc-serial-numbers/', wc_serial_numbers()->get_template_path() );
}
