<?php
defined( 'ABSPATH' ) || exit();

/**
 * Order class
 *
 * Handle everything related to order
 *
 * @since 1.5.5
 */
class WC_Serial_Numbers_Order {

	/**
	 * WC_Serial_Numbers_Order constructor.
	 */
	public function __construct() {
		//check if available serial numbers
		add_action( 'woocommerce_check_cart_items', array( $this, 'validate_checkout' ) );

		add_action( 'woocommerce_order_status_completed', array( $this, 'maybe_assign_serial_numbers' ) );
		add_action( 'woocommerce_order_status_processing', array( $this, 'maybe_assign_serial_numbers' ) );
		add_action( 'woocommerce_order_status_on-hold', array( $this, 'maybe_assign_serial_numbers' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'maybe_assign_serial_numbers' ) );

		// revoke ordered serial numbers
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'handle_cancelled_refunded_order' ) );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'handle_cancelled_refunded_order' ) );
		add_action( 'woocommerce_order_status_failed', array( $this, 'handle_cancelled_refunded_order' ) );
		add_action( 'woocommerce_order_partially_refunded', array( $this, 'handle_cancelled_refunded_order' ), 10, 2 );

		add_action( 'template_redirect', array( $this, 'maybe_autocomplete_order' ) );
	}

	/**
	 * If selling from stock then check if there is enough
	 * serial numbers available otherwise disable checkout
	 *
	 * since 1.5.5
	 * @return bool
	 */
	public static function validate_checkout() {
		$car_products = WC()->cart->get_cart_contents();
		foreach ( $car_products as $id => $cart_product ) {
			/** @var WC_Product $product */
			$product         = $cart_product['data'];
			$product_id      = $product->get_id();
			$quantity        = $cart_product['quantity'];
			$is_enabled      = 'yes' == get_post_meta( $product_id, '_is_serial_number', true );
			$sell_from_stock = 'on' == wc_serial_numbers()->get_settings( 'sell_from_stock', 'on', 'wcsn_general_settings' );

			$allow_validation = apply_filters( 'wc_serial_numbers_allow_cart_validation', $sell_from_stock, $product_id, $car_products );

			if ( $is_enabled && $allow_validation ) {
				$delivery_quantity = (int) get_post_meta( $product_id, '_delivery_quantity', true );
				$needed_quantity   = $quantity * ( empty( $delivery_quantity ) ? 1 : absint( $delivery_quantity ) );

				$total_number = wc_serial_numbers_get_items( array(
					'product_id' => $product_id,
					'status'     => 'available',
					'per_page'   => $needed_quantity
				), true );

				$label = wc_serial_numbers()->get_label();

				if ( $total_number < $quantity ) {
					$message = sprintf( __( 'Sorry, There is not enough %s available for %s, Please remove this item or lower the quantity,
												For now we have %s Serial Number for this product. <br>', 'wc-serial-numbers' ), '{serial_number_label}', '{product_title}', '{stock_quantity}' );
					$notice  = apply_filters( 'wc_serial_numbers_low_stock_message', $message );
					$notice  = str_replace( '{serial_number_label}', $label, $notice );
					$notice  = str_replace( '{product_title}', $product->get_title(), $notice );
					$notice  = str_replace( '{stock_quantity}', $total_number, $notice );

					wc_add_notice( $notice, 'error' );

					return false;
				}
			}

			do_action( 'wc_serial_number_product_cart_validation_complete', $product_id, $cart_product );
		}
	}


	/**
	 * Conditionally add serial numbers.
	 *
	 * @param int $order_id
	 *
	 * @version 1.6.7
	 * @since 1.6.0
	 */
	public function maybe_assign_serial_numbers( $order_id ) {
		$automatic_delivery = wc_serial_numbers()->get_settings( 'automatic_delivery' );

		if ( 'on' == $automatic_delivery ) {
			wc_serial_numbers_order_add_items( $order_id );
		}
	}


	/**
	 * Handle an order that is cancelled or refunded by:
	 *
	 * 1) Removing any serial numbers assigned for the order
	 *
	 * 2) If serial number is reusing then return back as available
	 *
	 * @param int $order_id the WC_Order ID
	 *
	 * @since 1.0
	 */
	public function handle_cancelled_refunded_order( $order_id ) {
		$order = wc_get_order( $order_id );

		$order_id = $order->get_id();

		// bail for no order
		if ( ! $order_id ) {
			return;
		}

		$remove_statuses = (array) wc_serial_numbers()->get_settings( 'revoke_statuses', [], 'wcsn_general_settings' );
		if ( array_key_exists( $order->get_status( 'edit' ), $remove_statuses ) ) {
			wc_serial_numbers_order_remove_items( $order_id );
		}
	}


	/**
	 * @return bool|WC_Order|WC_Order_Refund
	 * @since 1.5.5
	 */
	public static function maybe_autocomplete_order() {
		if ( is_checkout() && ! empty( is_wc_endpoint_url( 'order-received' ) ) && ! empty( get_query_var( 'order-received' ) ) ) {
			$order_id = get_query_var( 'order-received' );
			$order    = wc_get_order( $order_id );
			if ( empty( $order ) ) {
				return $order;
			}
			if ( 'complete' === $order->get_status() ) {
				return false;
			}

			$keys = wc_serial_numbers_get_ordered_items_quantity( $order_id );
			if ( empty( $keys ) ) {
				return false;
			}

			$order->set_status( 'complete' );
			$order->add_order_note( __( 'Order marked as complete by WC Serial Numbers', 'wc-serial-numbers' ) );

			return true;
		}

		return false;
	}

}

new WC_Serial_Numbers_Order();
