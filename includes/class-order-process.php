<?php

namespace Pluginever\WCSerialNumbers\Admin;


class WSN_Process_Order {

	function __construct() {
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'wsn_order_process' ] );
		//add_action('woocommerce_order_details_after_order_table', [$this, 'wsn_order_serial_number_details']);
	}

	function wsn_order_process( $order_id ) {
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		foreach ( $items as $item_id => $item_data ) {
			$product              = $item_data->get_product();
			$product_id           = $product->get_id();
			$enable_serial_number = get_post_meta( $product_id, 'enable_serial_number', true );
			if ( $enable_serial_number ) {
				$serial_numbers = wsn_get_serial_numbers( [
					'meta_key'   => 'product',
					'meta_value' => $product_id,
				] );

				$serial_number = $serial_numbers[array_rand($serial_numbers)]; //serial_number_to_be_used
				$usage_limit = get_post_meta( $serial_number->ID, 'usage_limit', true );
				$expires_on  = get_post_meta( $serial_number->ID, 'expires_on', true );
			}
		}
	}
}
