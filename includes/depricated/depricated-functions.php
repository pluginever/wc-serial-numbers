<?php
defined( 'ABSPATH' ) || exit();

/**
 * Get serial number user role.
 *
 * @since 1.2.0
 * @return mixed|void
 * @deprecated 1.4.0
 */
function wc_serial_numbers_get_user_role() {
	return apply_filters( 'wc_serial_numbers_user_role', 'manage_woocommerce' );
}

/**
 * Get serial number's statuses.
 *
 * @return array
 * @deprecated 1.4.0
 * since 1.2.0
 */
function wc_serial_numbers_get_serial_number_statuses() {
	$statuses = array(
		'available' => __( 'Available', 'wc-serial-numbers' ),
		'sold'      => __( 'Sold', 'wc-serial-numbers' ),
		'refunded'  => __( 'Refunded', 'wc-serial-numbers' ),
		'cancelled' => __( 'Cancelled', 'wc-serial-numbers' ),
		'expired'   => __( 'Expired', 'wc-serial-numbers' ),
		'failed'    => __( 'Failed', 'wc-serial-numbers' ),
		'inactive'  => __( 'Inactive', 'wc-serial-numbers' ),
	);

	return apply_filters( 'wc_serial_numbers_serial_number_statuses', $statuses );
}

/**
 * Decrypt number.
 *
 * @param string $key Key.
 *
 * @since 1.2.0
 * @return false|string
 * @deprecated 1.4.0
 */
function wc_serial_numbers_decrypt_key( $key ) {
	return WC_Serial_Numbers_Encryption::maybeDecrypt( $key );
}
