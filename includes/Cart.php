<?php

namespace PluginEver\SerialNumbers;

use PluginEver\SerialNumbers\B8\Component;
use PluginEver\SerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Cart.
 *
 * @since   1.0.0
 * @package PluginEver\SerialNumbers
 */
class Cart extends Component {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'woocommerce_check_cart_items', array( $this, 'validate_checkout' ) );
	}

	/**
	 * If selling from stock then check if there is enough
	 * serial numbers available otherwise disable checkout
	 *
	 * since 1.2.0
	 *
	 * @return void
	 */
	public function validate_checkout() {
		$cart_products = WC()->cart->get_cart_contents();
		foreach ( $cart_products as $id => $cart_product ) {
			// @var \WC_Product $product Product object.
			$product         = $cart_product['data'];
			$product_id      = $product->get_id();
			$quantity        = $cart_product['quantity'];
			$allow_backorder = apply_filters( 'wc_serial_numbers_allow_backorder', false, $product_id, $cart_product );

			if ( wcsn_is_product_enabled( $product_id ) && ! $allow_backorder ) {
				$per_item_quantity = absint( apply_filters( 'wc_serial_numbers_per_product_delivery_qty', 1, $product_id ) );
				$needed_quantity   = $quantity * ( empty( $per_item_quantity ) ? 1 : absint( $per_item_quantity ) );
				$source            = apply_filters( 'wc_serial_numbers_product_serial_source', 'custom_source', $product_id, $needed_quantity );
				if ( 'custom_source' === $source ) {
					$args        = array(
						'product_id' => $product_id,
						'status'     => 'available',
					);
					$total_found = Key::count( $args );
					if ( $total_found < $needed_quantity ) {
						$stock = floor( $total_found / $per_item_quantity );
						// translators: %1$s: product title, %2$s: stock quantity.
						$message = sprintf( esc_html__( 'Sorry, there aren’t enough Serial Keys for %1$s. Please remove this item or lower the quantity. For now, we have %2$s Serial Keys for this product.', 'wc-serial-numbers' ), '{product_title}', '{stock_quantity}' );
						$notice  = apply_filters( 'wc_serial_numbers_low_stock_message', $message );
						$notice  = str_replace( '{product_title}', $product->get_title(), $notice );
						$notice  = str_replace( '{stock_quantity}', $stock, $notice );

						wc_add_notice( $notice, 'error' );

						return;
					}
				}
			}

			do_action( 'wc_serial_number_product_cart_validation_complete', $product_id, $cart_product );
		}
	}
}
