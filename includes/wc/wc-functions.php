<?php
defined( 'ABSPATH' ) || exit();

/**
 * Main function assign serial numbers with order
 * since 1.0.0
 *
 * @param $order_id
 */
function wcsn_order_assign_serial_numbers( $order_id ) {

	$order = new WC_Order( $order_id );

	foreach ( $order->get_items() as $order_item ) {

		$product = $order_item->get_product();
		if ( 'yes' !== get_post_meta( $product->get_id(), '_is_serial_number', true ) ) {
			continue;
		}

		$quantity = $order_item->get_quantity();

		global $wpdb;
		$assigned_serial_count = $wpdb->get_var( $wpdb->prepare( "SELECT (id) from $wpdb->wcsn_serials_numbers WHERE order_id=%d AND product_id=%d AND status !='cancelled'", $order_id, $product->get_id() ) );
		if ( $assigned_serial_count >= $quantity ) {
			continue;
		}

		$needed_serial_count = $quantity - $assigned_serial_count;

		$serial_numbers = wcsn_get_serial_numbers( [
			'product_id' => $product->get_id(),
			'status'     => 'new',
			'per_page'   => $needed_serial_count,
		] );

		if ( empty( $serial_numbers ) ) {
			continue;
		}

		foreach ( $serial_numbers as $serial_number ) {
			wcsn_insert_serial_number( array(
				'id'               => $serial_number->id,
				'order_id'         => $order->get_id(),
				'activation_email' => $order->get_billing_email( 'edit' ),
				'status'           => 'active',
				'order_date'       => current_time( 'mysql' )
			) );
		}

	}

}

/**
 * Unassign serial numbers from order
 *
 * since 1.0.0
 *
 * @param $order_id
 *
 * @return bool|false|int
 */
function wcsn_order_remove_serial_numbers( $order_id ) {
	global $wpdb;
	$reuse = 'on' == wcsn_get_settings( 'reuse_serial_numbers' );
	if ( $reuse ) {
		return $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wcsn_serials_numbers set order_id=null, activation_email=null, status='new', order_date=null WHERE order_id=%d", $order_id ) );
	}

	return $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wcsn_serials_numbers set status='cancelled' WHERE order_id=%d", $order_id ) );
}


/**
 * since 1.0.0
 * @param $order_id
 *
 * @return array|int|object|string|null
 */
function wcsn_get_order_serial_numbers( $order_id ) {
	return wcsn_get_serial_numbers( [
		'order_id' => $order_id,
		'per_page' => '-1'
	] );
}
