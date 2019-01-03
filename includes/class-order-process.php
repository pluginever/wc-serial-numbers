<?php

namespace Pluginever\WCSerialNumbers;


class Order_Process {

	function __construct() {
		//add_action( 'woocommerce_check_cart_items', [ $this, 'wsn_validate_cart_content' ] );
		//add_action( 'woocommerce_checkout_create_order', [ $this, 'wsn_order_process' ] );
		add_action( 'woocommerce_new_order', [ $this, 'wsn_order_process' ] );
		add_action( 'woocommerce_order_details_after_order_table', [ $this, 'wsn_order_serial_number_details' ] );
	}

	function wsn_order_process( $order_id ) {

		$order = new \WC_Order( $order_id );
		$items = $order->get_items();

		error_log( print_r( $order, true ) );

		$serial_numbers_ids = [];

		foreach ( $items as $item_id => $item_data ) {

			$product              = $item_data->get_product();
			$product_id           = $product->get_id();
			$quantity             = $item_data->get_quantity();
			$enable_serial_number = get_post_meta( $product_id, 'enable_serial_number', true );

			if ( $enable_serial_number ) {

				$serial_numbers = wsn_get_serial_numbers( [
					'meta_key'   => 'product',
					'meta_value' => $product_id,
				] );

				$serial_number = $serial_numbers[ array_rand( $serial_numbers ) ]; //serial_number_to_be_used

				$remain_deliver_times = get_post_meta( $serial_number->ID, 'remain_deliver_times', true );

				update_post_meta( $serial_number->ID, 'order', $order->get_id() );
				update_post_meta( $serial_number->ID, 'remain_deliver_times', ( $remain_deliver_times - $quantity ) );
				update_post_meta( $serial_number->ID, 'purchased_on', $order->get_date_created() );

				$serial_numbers_ids[ $product_id ] = $serial_number->ID;

			}

		}

		//Update Order meta data
		$order->update_meta_data( 'serial_numbers', $serial_numbers_ids );
	}

	/**
	 * Show th Serial number details in the order details page
	 *
	 * @param $order
	 */

	function wsn_order_serial_number_details( $order ) {

		if ( $order->get_meta( 'serial_numbers' ) ) {
			include WPWSN_TEMPLATES_DIR . '/order-details-serial-number.php';
		}

	}
}
