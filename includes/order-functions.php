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

	update_post_meta( $order_id, 'wc_serial_numbers_assigned_serial_numbers', $assigned_serial_numbers_count );

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
		'status'     => 'available',
		'order'      => 'asc',
	) );
	$order          = new WC_Order( $order_id );

	if ( $serial_numbers < $quantity ) {
		do_action( 'wc_serial_numbers_order_product_assign_serial_numbers_failed', $product_id, $order_id, $serial_numbers, $quantity );

		return;
	}

	foreach ( $serial_numbers as $serial_number_id ) {

		wcsn_insert_serial_number( array(
			'id'               => $serial_number_id,
			'order_id'         => $order_id,
			'customer_id'      => get_post_meta( $order_id, '_customer_user', true ),
			'activation_email' => $order->get_billing_email( 'edit' ),
			'order_date'       => current_time( 'mysql' ),
			'status'           => 'active'
		) );
	}
}

add_action( 'wc_serial_numbers_order_product_assign_serial_numbers', 'wc_serial_numbers_order_product_assign_serial_numbers_handler', 10, 3 );


function wc_serial_numbers_set_stock_for_serial_number( $value, $product ) {
//	if ( $product->managing_stock() && wc_st( $product->get_id() ) && ! wc_serial_numbers_is_key_source_automatic( $product->get_id() ) ) {
//		$total_serials = wc_serial_numbers_get_serial_numbers( array(
//			'product_id' => $product->get_id(),
//			'number'     => - 1,
//			'status'     => 'new',
//		), true );
//
//		$total_serials = intval( $total_serials );
//
//		return $total_serials;
//	}
//
//	return $value;
}

add_filter( 'woocommerce_product_get_stock_quantity', 'wc_serial_numbers_set_stock_for_serial_number', 10, 2 );

/**
 * since 1.0.0
 *
 * @param $order
 */
function wc_serial_numbers_customer_send_serial_numbers( $order ) {

	$order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();

	$order = wc_get_order( $order_id );
	if ( 'completed' !== $order->get_status( 'edit' ) ) {
		return;
	}

	$serial_numbers = wc_serial_numbers_get_serial_numbers( [
		'order_id' => $order_id,
		'number'   => - 1
	] );

	if ( empty( $serial_numbers ) ) {
		return;
	}

	wc_serial_numbers_get_views( 'order-serial-numbers-table.php', array( 'serial_numbers' => $serial_numbers ) );
}

add_action( 'woocommerce_email_after_order_table', 'wc_serial_numbers_customer_send_serial_numbers' );

