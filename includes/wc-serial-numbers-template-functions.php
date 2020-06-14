<?php
defined( 'ABSPATH' ) || exit();

/**
 * Settings template.
 *
 * @since 1.1.6
 */
function wc_serial_number_view_template_settings() {
	include apply_filters('wc_serial_number_view_template_settings', dirname( __FILE__ ) .'/admin/views/view-template-settings.php');
}

/**
 * Print ordered serial numbers.
 *
 * @since 1.1.6
 * @param $order
 */
function wc_serial_numbers_order_print_items( $order ){
	$order_id = $order->get_id();

	$order = wc_get_order( $order_id );

	if ( 'completed' !== $order->get_status( 'edit' ) ) {
		return;
	}
	global $serial_numbers;
	$serial_numbers = wc_serial_numbers_get_items( [
		'order_id' => $order_id,
		'number'   => - 1
	] );

	if ( empty( $serial_numbers ) ) {
		return;
	}

	$heading                = apply_filters( 'wc_serial_numbers_headline', __( 'Serial Numbers', 'wc-serial-numbers' ) );
	$product_column         = apply_filters( 'wc_serial_numbers_product_col_heading', __( 'Product', 'wc-serial-numbers' ) );
	$content_column         = apply_filters( 'wc_serial_numbers_serial_col_heading', __( 'Serial Number', 'wc-serial-numbers' ) );
	$product_column_content = apply_filters( 'wc_serial_numbers_product_col_content', '{product_title}' );
	$serial_column_content  = apply_filters( 'wc_serial_numbers_serial_col_content', '<strong>Serial Number:</strong> {serial_number}<br/><strong>Activation Email:</strong> {activation_email}<br/><strong>Expire At:</strong> {expired_at}<br/><strong>Activation Limit:</strong> {activation_limit}' );

	include dirname( __FILE__ ) . '/admin/views/order-table.php';
}


/**
 * Get expiration date.
 *
 * @since 1.1.6
 * @param $serial
 *
 * @return false|string|void
 */
function wc_serial_numbers_get_expiration_date( $serial ) {
	if ( empty( $serial->validity ) ) {
		return __( 'Never Expire', 'wc-serial-numbers' );
	}

	return date( 'Y-m-d', strtotime( $serial->order_date . ' + ' . $serial->validity . ' Day ' ) );
}


/**
 * Get activation limit.
 *
 * @since 1.1.6
 * @param $serial
 *
 * @return string|void
 */
function wc_serial_numbers_get_activation_limit($serial){
	if ( empty( $serial->activation_limit ) ) {
		return __( 'Unlimited', 'wc-serial-numbers' );
	}

	return $serial->activation_limit;
}
