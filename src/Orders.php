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
class Orders {

	/**
	 * Orders constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'validate_checkout' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array( __CLASS__, 'maybe_autocomplete_order' ), 10, 3 );
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'handle_order_status_changed' ) );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'handle_order_status_changed' ) );
		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'handle_order_status_changed' ) );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'handle_order_status_changed' ) );
		// TODO: handle order status change and order remove scenario.
		// TODO: handle order again feature.

		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'order_email_keys' ), PHP_INT_MAX );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'order_display_keys' ), 9 );
	}

	/**
	 * If selling from stock then check if there is enough
	 * serial numbers available otherwise disable checkout
	 *
	 * since 1.2.0
	 *
	 * @return void
	 */
	public static function validate_checkout() {
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

	/**
	 * Automatically set the order's status to complete.
	 *
	 * @param string    $new_order_status The new order status.
	 * @param int       $order_id      The order ID.
	 * @param \WC_Order $order        The order object.
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

		if ( wcsn_order_has_products( $order ) ) {
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
	 *
	 * @since 1.4.6
	 */
	public static function handle_order_status_changed( $order_id ) {
		if ( apply_filters( 'wc_serial_numbers_maybe_manual_delivery', false, $order_id ) ) {
			return;
		}
		wcsn_order_update_keys( $order_id );
	}

	/**
	 * Print ordered serials
	 *
	 * @param \WC_Order $order The order object.
	 *
	 * @since 1.2.0
	 */
	public static function order_display_keys( $order ) {
		/**
		 * Filter to allow or disallow displaying keys in order details.
		 *
		 * @param bool $allow Whether to allow or disallow displaying serial numbers in order details.
		 * @param \WC_Order $order The order object.
		 */
		$allow = apply_filters( 'wc_serial_numbers_allow_order_display_keys', $order->has_status( 'completed' ), $order );

		if ( ! $allow || ! wcsn_order_has_products( $order ) ) {
			return;
		}

		wcsn_display_order_keys( $order );
	}

	/**
	 * Order email keys.
	 *
	 * @param \WC_Order $order The order object.
	 *
	 * @since 1.2.0
	 */
	public static function order_email_keys( $order ) {
		/**
		 * Filter to allow or disallow sending serial numbers in order emails.
		 *
		 * @param bool $allow Whether to allow or disallow sending serial numbers in order emails.
		 * @param \WC_Order $order The order object.
		 */
		$allow = apply_filters( 'wc_serial_numbers_allow_order_email_keys', $order->has_status( 'completed' ), $order );

		if ( ! $allow || ! wcsn_order_has_products( $order ) ) {
			return;
		}

		wcsn_display_order_keys( $order );
	}
}
