<?php
/**
 * Deprecated functions.
 *
 * @since 1.4.6
 * @package WooCommerceSerialNumbers/Functions
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get manager role.
 *
 * @since 1.4.2
 * @return string
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_manager_role() {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_get_manager_role' );

	return wcsn_get_manager_role();
}

/**
 * Get serial number's statuses.
 *
 * since 1.2.0
 * @return array
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_serial_number_statuses() {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_get_key_statuses' );

	return wcsn_get_key_statuses();
}

/**
 * Get product title.
 *
 * @param $product
 *
 * @since 1.2.0
 * @return string
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_product_title( $product ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_get_product_title' );

	return wcsn_get_product_title( $product );
}

/**
 * Check if product enabled for selling serial numbers.
 *
 * @param $product_id
 *
 * @since 1.2.0
 * @return bool
 * @deprecated 1.4.6
 */
function wc_serial_numbers_product_serial_enabled( $product_id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_product_serial_enabled' );

	return wcsn_is_product_enabled( $product_id );
}

/**
 * Connect serial numbers with order.
 *
 * @param $order_id
 *
 * @since 1.2.0
 * @return bool|int
 * @deprecated 1.4.6
 */
function wc_serial_numbers_order_connect_serial_numbers( $order_id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_order_connect_serial_numbers' );

	return wcsn_order_add_keys( $order_id );
}

/**
 * Check if serial number is reusing.
 *
 * @since 1.2.0
 * @return bool
 * @deprecated 1.4.6
 */
function wc_serial_numbers_reuse_serial_numbers() {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_reuse_keys' );

	return wcsn_is_reusing_keys();
}

/**
 * Disconnect serial numbers from order.
 *
 * @param $order_id
 *
 * @since 1.2.0
 * @return bool
 * @deprecated 1.4.6
 */
function wc_serial_numbers_order_disconnect_serial_numbers( $order_id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_order_disconnect_serial_numbers' );

	return wcsn_order_remove_keys( $order_id );
}

/**
 * Insert serial number.
 *
 * @param $args
 *
 * @since 1.2.0
 * @return int|WP_Error
 * @deprecated 1.4.6
 */
function wc_serial_numbers_insert_serial_number( $args ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_insert_key' );

	return wcsn_insert_key( $args );
}

/**
 * @param $args
 *
 * @since 1.2.0
 * @return int|WP_Error
 * @deprecated 1.4.6
 */
function wc_serial_numbers_update_serial_number( $args ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_insert_key' );

	return wcsn_insert_key( $args );
}

/**
 * Update status.
 *
 * @param $id
 * @param $status
 *
 * @since 1.2.0
 * @return int|WP_Error
 * @deprecated 1.4.6
 */
function wc_serial_numbers_update_serial_number_status( $id, $status ) {
	$key = wcsn_get_key( $id );
	if ( ! $key ) {
		return new WP_Error( 'invalid_data', __( 'Serial number not found.', 'wc-serial-numbers' ) );
	}
	$key->set_status( $status );

	return $key->save();
}

/**
 * Delete serial number.
 *
 * @param $id
 *
 * @since 1.2.0
 * @return bool
 * @deprecated 1.4.6
 */
function wc_serial_numbers_delete_serial_number( $id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_delete_key' );

	return wcsn_delete_key( $id );
}

/**
 * @param $id
 *
 * @since 1.2.0
 * @return mixed
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_serial_number( $id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_get_key' );

	return wcsn_get_key( $id );
}

/**
 * Get activation
 *
 * @param $args
 *
 * @since 1.2.0
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_activation( $activation_id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_get_activation' );

	return wcsn_get_activation( $activation_id );
}

/**
 * @param $args
 *
 * @since 1.2.0
 * @return int|WP_Error
 * @deprecated 1.4.6
 */
function wc_serial_numbers_insert_activation( $args ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_insert_activation' );

	return wcsn_insert_activation( $args );
}

/**
 * @param $args
 *
 * @since 1.2.0
 * @return int|WP_Error
 * @deprecated 1.4.6
 */
function wc_serial_numbers_update_activation( $args ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_insert_activation' );

	return wcsn_insert_activation( $args );
}

/**
 * @param $id
 *
 * @since 1.2.0
 * @return bool
 * @deprecated 1.4.6
 */
function wc_serial_numbers_delete_activation( $id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_delete_activation' );

	return wcsn_delete_activation( $id );
}

/**
 * @param $id
 * @param int $status
 *
 * @since 1.2.0
 * @return int|WP_Error
 * @deprecated 1.4.6
 */
function wc_serial_numbers_update_activation_status( $id, $status = 1 ) {
	// Do nothing.
}

/**
 * Encrypt serial number.
 *
 * @param string $key Serial number.
 *
 * @since 1.2.0
 * @return false|string
 * @deprecated 1.4.6
 */
function wc_serial_numbers_encrypt_key( $key ) {
	return wcsn_encrypt_key( $key );
}

/**
 * Decrypt number.
 *
 * @param string $key Serial number.
 *
 * @since 1.2.0
 * @return false|string
 * @deprecated 1.4.6
 */
function wc_serial_numbers_decrypt_key( $key ) {
	return wcsn_decrypt_key( $key );
}

