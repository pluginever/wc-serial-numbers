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
class Orders extends Lib\Singleton {

	/**
	 * Orders constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'validate_checkout' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array( __CLASS__, 'maybe_autocomplete_order' ), 10, 3 );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'handle_order_status_changed' ), 10, 3 );
		add_action( 'wc_serial_numbers_recover_keys', array( __CLASS__, 'recover_keys' ) );

		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'order_print_items' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'order_print_items' ), 10 );
	}

	/**
	 * If selling from stock then check if there is enough
	 * serial numbers available otherwise disable checkout
	 *
	 * since 1.2.0
	 * @return void
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
					$args        = array(
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

						return;
					}
				}
			}

			do_action( 'wc_serial_number_product_cart_validation_complete', $product_id, $cart_product );
		}
	}

	/**
	 * Automatically set the order's status to complete.
	 *
	 * @param string $new_order_status
	 * @param int $order_id
	 * @param \WC_Order $order
	 *
	 * @since 1.4.6
	 * @return string $new_order_status
	 */
	public static function maybe_autocomplete_order( $new_order_status, $order_id, $order = null ) {
		// Exit early if the order has no ID, or if the new order status is not 'processing'.
		if ( 'yes' !== get_option( 'wc_serial_numbers_autocomplete_order' ) || 0 === $order_id || 'processing' !== $new_order_status ) {
			return $new_order_status;
		}
		if ( null === $order ) {
			remove_filter( 'woocommerce_payment_complete_order_status', __METHOD__, 10 );
			$order = wc_get_order( $order_id );
			add_filter( 'woocommerce_payment_complete_order_status', __METHOD__, 10, 3 );
		}

		if ( wcsn_order_has_products( $order ) && $order->has_status( 'processing' ) ) {
			$new_order_status = 'completed';
			// Add a note to the order mentioning that the order has been automatically completed by the plugin.
			$order->add_order_note(
				apply_filters(
					'wc_serial_numbers_autocomplete_order_note',
					__( 'Order automatically completed by the Serial Numbers for WooCommerce.', 'wc-serial-numbers' ),
					$order
				)
			);
		}

		return $new_order_status;
	}

	/**
	 * Handle order status changed.
	 *
	 * @param int|\WC_Order $order_id The order ID or WC_Order object.
	 * @param string $old_status The old order status.
	 * @param string $new_status The new order status.
	 *
	 * @since 1.4.6
	 */
	public static function handle_order_status_changed( $order_id, $old_status, $new_status ) {
		if ( 'completed' === $new_status && apply_filters( 'wc_serial_numbers_maybe_manual_delivery', false, $order_id ) ) {
			return;
		}

		wcsn_order_update_keys( $order_id );

		if ( 'completed' !== $new_status && function_exists( 'as_schedule_single_action' ) ) {
//			// schedule a woocommerce scheduled action to recover the keys after 30 minutes.
//			$timestamp = time() + 30 * MINUTE_IN_SECONDS;
//			$args      = array( $order_id );
//			$hook      = 'wc_serial_numbers_recover_keys';
//			as_schedule_single_action( $timestamp, $hook, $args );
		}
	}

	/**
	 * Recover keys for an order.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @since 1.4.6
	 */
	public static function recover_keys( $order_id ) {
		// If the order is still not paid then recover the keys.
//		$order = wc_get_order( $order_id );
//		if ( $order && ! $order->is_paid() && wcsn_order_has_products( $order ) ) {
//			// set order status to cancel.
//			$order->update_status( 'cancelled' );
//		}
	}

	/**
	 * Print ordered serials
	 *
	 * @param $order
	 *
	 * @throws \Exception
	 * @since 1.2.0
	 */
	public static function order_print_items( $order ) {
		if ( wcsn_order_has_products( $order ) ) {
			wc_serial_numbers_get_order_table( $order );
		}
	}
}
