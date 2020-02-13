<?php

/**
 * Validate cart content
 *
 * since 1.0.0
 * @return bool
*/
function wcsn_validate_checkout() {
	$car_products = WC()->cart->get_cart_contents();
	foreach ( $car_products as $id => $cart_product ) {
		/** @var WC_Product $product */
		$product          = $cart_product['data'];
		$product_id       = $product->get_id();
		$quantity         = $cart_product['quantity'];
		$is_enabled       = wcsn_product_support_serial_number( $product_id );
		$allow_validation = apply_filters( 'wcsn_allow_cart_validation', true, $product_id, $car_products );

		if ( $is_enabled && $allow_validation ) {
			$delivery_quantity = get_post_meta( $product_id, '_delivery_quantity', true );
			$needed_quantity   = $quantity * ( empty( $delivery_quantity ) ? 1 : absint( $delivery_quantity ) );

			$total_number = wcsn_get_serial_numbers( array(
				'product_id' => $product_id,
				'status'     => 'available',
				'per_page'   => $needed_quantity
			), true );

			if ( $total_number < $quantity ) {
				$notice = apply_filters( 'wcsn_low_stock_notice_message', sprintf( __( 'Sorry, There is not enough Serial Number available for %s, Please remove this item or lower the quantity, For now we have %d Serial Number for this product. <br>', 'wc-serial-numbers' ), $product->get_title(), $total_number ), $product_id, $cart_product );
				wc_add_notice( $notice, 'error' );

				return false;
			}
		}

		do_action( 'wcsn_product_cart_validation_complete', $product_id, $cart_product );
	}
}

add_action( 'woocommerce_check_cart_items', 'wcsn_validate_checkout' );

function wcsn_order_processed( $order_id ) {
	$order          = wc_get_order( $order_id );
	$items          = $order->get_items();
	$serial_numbers = array();

	foreach ( $items as $item_data ) {
		/** @var WC_Order_Item $item_data */
		/** @var WC_Product $product */
		$product    = $item_data->get_product();
		$product_id = $product->get_id();
		if ( ! wcsn_product_support_serial_number( $product_id ) ) {
			continue;
		}
		$quantity          = $item_data->get_quantity();
		$delivery_quantity = get_post_meta( $product_id, '_delivery_quantity', true );
		$delivery_quantity = empty( $delivery_quantity ) ? 1 : absint( $delivery_quantity );
		$needed_quantity   = $quantity * $delivery_quantity;
		if ( $needed_quantity ) {
			$serial_numbers[ $product_id ] = $needed_quantity;
		}
	}

	update_post_meta( $order_id, 'wc_serial_numbers_products', $serial_numbers );
}

add_action( 'woocommerce_checkout_order_processed', 'wcsn_order_processed' );
add_action( 'woocommerce_update_order', 'wcsn_order_processed' );

