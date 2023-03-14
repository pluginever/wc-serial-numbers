<?php

namespace WooCommerceSerialNumbers;

use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Orders.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers
 */
class Orders extends \WooCommerceSerialNumbers\Lib\Singleton {

	/**
	 * Orders constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'validate_checkout' ) );
	}

	/**
	 * If selling from stock then check if there is enough
	 * serial numbers available otherwise disable checkout
	 *
	 * since 1.2.0
	 * @return bool
	 */
	public static function validate_checkout() {
		$car_products = WC()->cart->get_cart_contents();
		foreach ( $car_products as $id => $cart_product ) {
			/** @var \WC_Product $product */
			$product         = $cart_product['data'];
			$product_id      = $product->get_id();
			$quantity        = $cart_product['quantity'];
			$allow_backorder = apply_filters( 'wc_serial_numbers_allow_backorder', false, $product_id );

			if ( wc_serial_numbers_product_serial_enabled( $product_id ) && ! $allow_backorder ) {
				$per_item_quantity = absint( apply_filters( 'wc_serial_numbers_per_product_delivery_qty', 1, $product_id ) );
				$needed_quantity   = $quantity * ( empty( $per_item_quantity ) ? 1 : absint( $per_item_quantity ) );
				$source            = apply_filters( 'wc_serial_numbers_product_serial_source', 'custom_source', $product_id, $needed_quantity );
				if ( 'custom_source' == $source ) {
					$args = array(
						'product_id' => $product_id,
						'status'     => 'instock',
						'source'     => $source,
					);
					$total_found = Key::count( $args );
					if ( $total_found < $needed_quantity ) {
						$stock   = floor( $total_found / $per_item_quantity );
						$message = sprintf( __( 'Sorry, there aren’t enough Serial Numbers for %s. Please remove this item or lower the quantity. For now, we have %s Serial Numbers for this product.', 'wc-serial-numbers' ), '{product_title}', '{stock_quantity}' );
						$notice  = apply_filters( 'wc_serial_numbers_low_stock_message', $message );
						$notice  = str_replace( '{product_title}', $product->get_title(), $notice );
						$notice  = str_replace( '{stock_quantity}', $stock, $notice );

						wc_add_notice( $notice, 'error' );

						return false;
					}
				}
			}

			do_action( 'wc_serial_number_product_cart_validation_complete', $product_id, $cart_product );
		}
	}
}
