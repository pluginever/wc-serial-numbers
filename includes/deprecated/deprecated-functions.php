<?php
defined( 'ABSPATH' ) || exit();

/**
 * since 1.2.0
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

/**
 * since 1.2.0
 *
 * @param $args
 *
 * @return int|WP_Error|null
 * @deprecated 1.2.0 Use wcsn_insert_serial_number()
 */
function wcsn_insert_serial_number($args) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wcsn_insert_serial_number' );

	return wcsn_insert_serial_number($args);
}

/**
 * since 1.2.0
 *
 * @param $id
 *
 * @return bool
 * @deprecated 1.2.0 Use wc_serial_numbers_delete_serial_number()
 */
function wcsn_delete_serial_number($id) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_delete_serial_number' );

	return wc_serial_numbers_delete_serial_number($id);
}

/**
 * Change status
 *
 * since 1.2.0
 *
 * @param $id
 * @param $status
 *
 * @return bool|int|WP_Error|null
 * @deprecated 1.2.0 Use wc_serial_numbers_change_serial_number_status()
 */
function wcsn_change_serial_number_status( $id, $status ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_change_serial_number_status' );

	return wc_serial_numbers_change_serial_number_status( $id, $status );
}

/**
 *
 * since 1.2.0
 *
 * @param $key
 *
 * @return string|WP_Error
 * @deprecated 1.2.0 Use wc_serial_numbers_encrypt_serial_number()
 */
function wcsn_encrypt_serial_number( $key ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_encrypt_serial_number' );

	return wc_serial_numbers_encrypt_serial_number( $key );
}

/**
 *
 * since 1.2.0
 *
 * @param $key
 *
 * @return string|WP_Error
 * @deprecated 1.2.0 Use wc_serial_numbers_decrypt_serial_number()
 */
function wcsn_decrypt_serial_number( $key ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_decrypt_serial_number' );

	return wc_serial_numbers_decrypt_serial_number( $key );
}

/**
 *
 * since 1.2.0
 *
 * @param array $args
 * @param bool $count
 *
 * @return array|object|string|null
 * @deprecated 1.2.0 Use wc_serial_numbers_get_products()
 */
function wcsn_get_products( $args = array(), $count = false ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_get_products' );

	return wc_serial_numbers_get_products( $args, $count );
}

/**
 *
 * since 1.2.0
 *
 * @param $product_id
 *
 * @deprecated 1.2.0 Use wc_serial_numbers_product_enable_serial_number()
 */
function wcsn_product_enable_serial_number( $product_id ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_product_enable_serial_number' );

	wc_serial_numbers_product_enable_serial_number( $product_id );
}

/**
 *
 * since 1.2.0
 *
 * @param $product_id
 *
 * @return bool
 * @deprecated 1.2.0 Use wc_serial_numbers_product_support_serial_number()
 */
function wcsn_product_support_serial_number( $product_id ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_product_support_serial_number' );

	return wc_serial_numbers_product_support_serial_number( $product_id );
}

/**
 *
 * since 1.2.0
 *
 * @param $order_id
 *
 * @return bool
 * @deprecated 1.2.0 Use wc_serial_numbers_order_assign_serial_numbers()
 */
function wcsn_order_assign_serial_numbers( $order_id ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_order_assign_serial_numbers' );

	return wc_serial_numbers_order_assign_serial_numbers( $order_id );
}

/**
 *
 * since 1.2.0
 *
 * @param $product_id
 * @param $quantity
 * @param $order_id
 *
 * @deprecated 1.2.0 Use wc_serial_numbers_order_product_assign_serial_numbers_handler()
 */
function wcsn_order_product_assign_serial_numbers_handler( $product_id, $quantity, $order_id ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_order_product_assign_serial_numbers_handler' );

	return wc_serial_numbers_order_product_assign_serial_numbers_handler( $product_id, $quantity, $order_id );
}

/**
 *
 * since 1.2.0
 *
 * @param $key
 * @param string $default
 *
 * @deprecated 1.2.0 Use wc_serial_numbers_get_settings()
 */
function wcsn_get_settings( $key, $default = '' ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_get_settings' );

	return wc_serial_numbers_get_settings( $key, $default );
}

/**
 *
 * since 1.2.0
 *
 * @param string $key
 * @param bool $plural
 *
 * @return string
 * @deprecated 1.2.0 Use wc_serial_numbers_labels()
 */
function wcsn_labels( $key = 'serial_number', $plural = false ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_labels' );

	return wc_serial_numbers_labels( $key, $default );
}

/**
 * Get admin view
 * since 1.2.0
 *
 * @param $template_name
 * @param array $args
 *
 * @return string
 * @deprecated 1.2.0 Use wc_serial_numbers_get_views()
 */
function wcsn_get_views( $template_name, $args = [] ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_get_views' );

	return wc_serial_numbers_get_views( $template_name, $args );
}

/**
 * since 1.2.0
 *
 * @param $notice
 * @param string $type
 * @param bool $dismissible
 *
 * @deprecated 1.2.0 Use wc_serial_numbers_add_admin_notice()
 */
function wcsn_add_admin_notice( $notice, $type = 'success', $dismissible = true ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_add_admin_notice' );

	return wc_serial_numbers_add_admin_notice( $notice, $type, $dismissible );
}

/**
 * Generate Random String
 * since 1.2.0
 *
 * @param integer $length
 *
 * @return string
 * @deprecated 1.2.0 Use wc_serial_numbers_generate_random_string()
 */
function wcsn_generate_random_string( $length = 10 ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_generate_random_string' );

	return wc_serial_numbers_generate_random_string( $length );
}

/**
 * Generate Random String
 * since 1.2.0
 *
 * @return string
 * @deprecated 1.2.0 Use wc_serial_numbers_get_encrypt_key()
 */
function wcsn_get_encrypt_key() {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_get_encrypt_key' );

	return wc_serial_numbers_get_encrypt_key();
}

/**
 * Is encrypted
 * since 1.2.0
 *
 * @param string $string
 *
 * @return bool
 * @deprecated 1.2.0 Use wc_serial_numbers_is_encrypted_string()
 */
function wcsn_is_encrypted_string( $string ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_is_encrypted_string' );

	return wc_serial_numbers_is_encrypted_string( $string );
}

/**
 * since 1.2.0
 *
 * @param string $string
 *
 * @return mixed|void
 * @deprecated 1.2.0 Use wc_serial_numbers_is_allowed_duplicate_serial_numbers()
 */
function wcsn_is_allowed_duplicate_serial_numbers() {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_is_allowed_duplicate_serial_numbers' );

	return wc_serial_numbers_is_allowed_duplicate_serial_numbers();
}

/**
 * since 1.2.0
 *
 * @param $serial_number
 * @param string $context
 *
 * @return string
 * @deprecated 1.2.0 Use wc_serial_numbers_get_serial_number_status()
 */
function wcsn_get_serial_number_status( $serial_number, $context = 'edit' ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_get_serial_number_status' );

	return wc_serial_numbers_get_serial_number_status( $serial_number, $context );
}

/**
 * Validate cart content
 * since 1.2.0
 *
 * @return bool
 * @deprecated 1.2.0 Use wc_serial_numbers_validate_checkout()
 */
function wcsn_validate_checkout() {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_validate_checkout' );

	return wc_serial_numbers_validate_checkout();
}

/**
 * since 1.2.0
 *
 * @param $order_id
 *
 * @deprecated 1.2.0 Use wc_serial_numbers_order_processed()
 */
function wcsn_order_processed( $order_id ) {
	_deprecated_function( __FUNCTION__, '1.2.0', 'wc_serial_numbers_order_processed' );

	return wc_serial_numbers_order_processed( $order_id );
}

