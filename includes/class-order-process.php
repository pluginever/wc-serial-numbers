<?php

namespace Pluginever\WCSerialNumbers\Admin;


class WSN_Process_Order {

	function __construct() {
		add_action( 'woocommerce_checkout_create_order', [ $this, 'wsn_order_process' ] );
		add_action( 'woocommerce_order_details_after_order_table', [ $this, 'wsn_order_serial_number_details' ] );
	}

	function wsn_order_process( $order ) {
		$items              = $order->get_items();
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
				$usage_limit   = get_post_meta( $serial_number->ID, 'usage_limit', true );
				$remain_usage  = wsn_remain_usage( $serial_number->ID );
				$expires_on    = get_post_meta( $serial_number->ID, 'expires_on', true );

				update_post_meta( $serial_number->ID, 'order', $order->get_id() );
				update_post_meta( $serial_number->ID, 'remain_usage', ( $remain_usage + $quantity ) );
				update_post_meta( $serial_number->ID, 'purchased_on', $order->get_date_created() );

				$serial_numbers_ids[] = $serial_number->ID;

			}
		}
		//Update Order meta data
		$order->update_meta_data( 'serial_numbers', $serial_numbers_ids );
	}


	function wsn_order_serial_number_details( $order ) {

	}
}
