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
	) );
	$order          = new WC_Order( $order_id );

	if ( $serial_numbers < $quantity ) {
		do_action( 'wc_serial_numbers_order_product_assign_serial_numbers_failed', $product_id, $order_id, $serial_numbers, $quantity );

		return;
	}

	foreach ( $serial_numbers as $serial_number_id ) {

		wc_serial_numbers_insert_serial_number( array(
			'id'               => $serial_number_id,
			'order_id'         => $order_id,
			'activation_email' => $order->get_billing_email( 'edit' ),
			'order_date'       => current_time( 'mysql' ),
			'status'           => 'active'
		) );
	}
}

add_action( 'wc_serial_numbers_order_product_assign_serial_numbers', 'wc_serial_numbers_order_product_assign_serial_numbers_handler', 10, 3 );

/**
 * Automatically assign serial number when order is complete
 *
 * @param $order_id
 *
 * @since 1.0.0
 */
function wc_serial_numbers_order_complete_handler( $order_id ) {
	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return;
	}

	if ( ! wc_serial_numbers_is_order_automatically_assign_serial_numbers() ) {
		return;
	}

	wc_serial_numbers_order_assign_serial_numbers( $order_id );

}

add_action( 'woocommerce_order_status_completed', 'wc_serial_numbers_order_complete_handler', 10 );

function wc_serial_numbers_revoke_order_serial_numbers( $order_id, $status_from, $status_to ) {
	$serial_numbers = wc_serial_numbers_get_serial_numbers( array(
		'order_id' => $order_id,
		'number'   => - 1,
		'fields'   => 'id'
	) );

	if ( empty( $serial_numbers ) ) {
		return;
	}

	$reuse = wc_serial_numbers_is_reuse_serial_numbers();

	if ( in_array( $status_to, array( 'refunded', 'failed', 'cancelled' ) ) ) {
		foreach ( $serial_numbers as $serial_number_id ) {
			$args = array(
				'id'     => $serial_number_id,
				'status' => $status_to,
			);

			if ( $reuse ) {
				$args = array_merge( $args, array(
					'status'           => 'available',
					'order_id'         => '',
					'activation_email' => '',
					'order_date'       => '',
				) );
			}

			wc_serial_numbers_insert_serial_number( $args );
		}
	}
}

add_action( 'woocommerce_order_status_changed', 'wc_serial_numbers_revoke_order_serial_numbers', 10, 3 );


/**
 * Auto Complete Order
 *
 * @param $order
 *
 * @since 1.0.0
 *
 */
function wc_serial_numbers_auto_complete_order_handler( $order_id ) {
	if ( 'on' !== wc_serial_numbers_get_settings( 'autocomplete_order' ) ) {
		return;
	}
	$order          = wc_get_order( $order_id );
	$current_status = $order->get_status();
	// We only want to update the status to 'completed' if it's coming from one of the following statuses:
	//$allowed_current_statuses = array( 'on-hold', 'pending', 'failed' );
	if ( 'processing' == $current_status ) {
		$items = $order->get_items();
		foreach ( $items as $item_data ) {
			/** @var WC_Product $product */
			$product                  = $item_data->get_product();
			$product_id               = $product->get_id();
			$is_serial_number_enabled = get_post_meta( $product_id, '_is_serial_number', true ); //Check if the serial number enabled for this product.
			if ( 'yes' == $is_serial_number_enabled ) {
				$order->update_status( 'completed' );
				return;
			}
		}
	}

}

add_action( 'woocommerce_thankyou', 'wc_serial_numbers_auto_complete_order_handler', 99, 1 );


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
 * @param $order
 */
function wc_serial_numbers_customer_send_serial_numbers($order){

	$order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();

	$order    = wc_get_order( $order_id );
	if ( 'completed' !== $order->get_status( 'edit' ) ) {
		return;
	}

	$serial_numbers = wc_serial_numbers_get_serial_numbers([
		'order_id' => $order_id,
		'number' => - 1
	]);

	if ( empty( $serial_numbers ) ) {
		return;
	}

	wc_serial_numbers_get_views( 'order-serial-numbers-table.php', array( 'serial_numbers' => $serial_numbers ));
}

add_action( 'woocommerce_email_after_order_table', 'wc_serial_numbers_customer_send_serial_numbers');


function wc_serial_numbers_order_table_serial_number_details($order){
	$order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();

	$order    = wc_get_order( $order_id );
	if ( 'completed' !== $order->get_status( 'edit' ) ) {
		return;
	}

	$serial_numbers = wc_serial_numbers_get_serial_numbers([
		'order_id' => $order_id,
		'number' => - 1
	]);

	if ( empty( $serial_numbers ) ) {
		return;
	}

	wc_serial_numbers_get_views( 'order-serial-numbers-table.php', array( 'serial_numbers' => $serial_numbers ));
}
add_action( 'woocommerce_order_details_after_order_table', 'wc_serial_numbers_order_table_serial_number_details' );
