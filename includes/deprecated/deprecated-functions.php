<?php
defined( 'ABSPATH' ) || exit();

/**
 * since 1.0.0
 *
 * @param array $args
 * @param bool $count
 *
 * @return array|object|string|null
 * @deprecated 1.2.0 Use wc_serial_numbers_get_serial_numbers()
 */
function wcsn_get_serial_numbers( $args = array(), $count = false ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_get_serial_numbers' );

	return wc_serial_numbers_get_serial_numbers( $args, $count );
}

