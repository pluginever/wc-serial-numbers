<?php
defined( 'ABSPATH' ) || exit();

/**
 * since 1.0.0
 *
 * @param $order_id
 *
 * @return bool
 */
function wc_serial_numbers_order_assign_serial_numbers( $order_id ) {
	$serial_numbers = get_post_meta( $order_id, 'wc_serial_numbers_products', true );
	if ( false == $serial_numbers || empty( $serial_numbers ) ) {
		return false;
	}
	$total_quantity = 0;
	foreach ( $serial_numbers as $product_id => $quantity ) {
		do_action( 'wc_serial_numbers_order_product_assign_serial_numbers', $product_id, $quantity, $order_id );
		$total_quantity += $quantity;
	}

	$assigned_serial_numbers_count = wc_serial_numbers_get_serial_numbers( array(
		'order_id' => $order_id,
	), true );

	update_post_meta($order_id, 'wc_serial_numbers_assigned_serial_numbers', $assigned_serial_numbers_count);

	if ( $assigned_serial_numbers_count == $total_quantity ) {
		do_action( 'wc_serial_number_order_assigned_serial_numbers', $order_id, $assigned_serial_numbers_count, $total_quantity );
		return true;
	}

	return false;
}

/**
 * since 1.0.0
 *
 * @param $product_id
 * @param $quantity
 * @param $order_id
 */
function wc_serial_numbers_order_product_assign_serial_numbers_handler( $product_id, $quantity, $order_id ) {
	$serial_numbers = wc_serial_numbers_get_serial_numbers( array(
		'fields'     => 'id',
		'product_id' => $product_id,
		'per_page'   => $quantity,
	) );
	$order          = new WC_Order( $order_id );

	foreach ( $serial_numbers as $serial_number_id ) {

		$error = wc_serial_numbers_insert_serial_number( array(
			'id'               => $serial_number_id,
			'order_id'         => $order_id,
			'activation_email' => $order->get_billing_email( 'edit' ),
			'order_date'       => current_time( 'mysql' ),
			'status'           => 'sold'
		) );
	}
}

add_action( 'wc_serial_numbers_order_product_assign_serial_numbers', 'wc_serial_numbers_order_product_assign_serial_numbers_handler', 10, 3 );
