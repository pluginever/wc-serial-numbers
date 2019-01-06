<?php

namespace Pluginever\WCSerialNumbers;


class Order_Process {

	function __construct() {
		add_action( 'woocommerce_check_cart_items', [ $this, 'wsn_validate_cart_content' ] );
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

	function wsn_validate_cart_content() {
		$car_products = WC()->cart->get_cart_contents();

		foreach ( $car_products as $id => $cart_product ) {
			$product    = $cart_product['data'];
			$product_id = $cart_product['product_id'];
			$quantity   = $cart_product['quantity'];

			$is_enabled = get_post_meta( $product_id, 'enable_serial_number', true ); //Check if the serial number enabled for this product.

			if ( $is_enabled == 'enable' ) {
				$serial_numbers = wsn_get_serial_numbers( [ 'meta_key' => 'product', 'meta_value' => $product_id ] );
				$count_numbers  = count( $serial_numbers );


				if ( $count_numbers < $quantity ) {
					wc_add_notice( __( 'Sorry, There is not enough <strong>Serial Number</strong> available for', 'wc-serial-numbers' ) . ' <strong>' . $product->get_title() . '</strong>, <br>' . __( 'Please remove this item or lower the quantity, For now we have', 'wc-serial-numbers' ) . ' ' . $count_numbers . ' ' . __( 'Serial Number(s)', 'wc-serial-numbers' ) . ' ' . __( 'for this product.', 'wc-serial-numbers' ) . '' . '<br>', 'error' );
				}
			}
		}
	}
}
