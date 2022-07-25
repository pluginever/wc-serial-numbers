<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Order.
 *
 * Handles order related actions.
 *
 * @since  1.0.0
 * @return Order
 */
class Order {
	/**
	 * Register action and filter hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function init() {
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'validate_checkout' ) );

		// autocomplete.
		add_action( 'template_redirect', array( __CLASS__, 'maybe_autocomplete_order' ) );

		// add serial numbers.
		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'maybe_assign_serial_numbers' ) );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'maybe_assign_serial_numbers' ) );
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'maybe_assign_serial_numbers' ) );
		add_action( 'woocommerce_order_status_on-hold', array( __CLASS__, 'maybe_assign_serial_numbers' ) );

		// revoke ordered serial numbers.
		add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'revoke_serial_numbers' ) );
		add_action( 'woocommerce_order_status_refunded', array( __CLASS__, 'revoke_serial_numbers' ) );
		add_action( 'woocommerce_order_status_failed', array( __CLASS__, 'revoke_serial_numbers' ) );
		add_action( 'woocommerce_order_partially_refunded', array( __CLASS__, 'revoke_serial_numbers' ), 10, 2 );

		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'order_print_items' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'order_print_items' ), 10 );
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
					$total_number = WC_Serial_Numbers_Query::init()
					                                       ->from( 'serial_numbers' )
					                                       ->where( 'product_id', $product_id )
					                                       ->where( 'status', 'available' )
					                                       ->where( 'source', $source )
					                                       ->limit( $needed_quantity )
					                                       ->count();

					if ( $total_number < $needed_quantity ) {
						$stock   = floor( $total_number / $per_item_quantity );
						$message = sprintf( __( 'Sorry, There is not enough serial numbers available for %s, Please remove this item or lower the quantity, For now we have %s Serial Number for this product.', 'wc-serial-numbers' ), '{product_title}', '{stock_quantity}' );
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

Order::init();

