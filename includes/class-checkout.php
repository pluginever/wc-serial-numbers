<?php
namespace Pluginever\SerialNumbers;

defined( 'ABSPATH' ) || exit();

class Checkout {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.0.0
	 */
	private static $instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @return self Main instance.
	 * @since  1.0.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * WC_Serial_Numbers_Checkout constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_check_cart_items', array( $this, 'validate_cart_content' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'process_order' ) );
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'remove_serial_numbers' ) );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'remove_serial_numbers' ) );
		add_action( 'woocommerce_order_status_failed', array( $this, 'remove_serial_numbers' ) );
	}

	/**
	 * Validate cart check if there are enough serial numbers
	 *
	 * since 1.0.0
	 *
	 * @return bool
	 */
	function validate_cart_content() {
		$car_products = WC()->cart->get_cart_contents();
		foreach ( $car_products as $id => $cart_product ) {
			$product    = $cart_product['data'];
			$product_id = $product->get_id();
			$quantity   = $cart_product['quantity'];
			$is_enabled = get_post_meta( $product_id, '_is_serial_number', true );
			if ( ( $is_enabled == 'yes' ) ) {
				$allow_checkout = apply_filters( 'wc_serial_numbers_allow_checkout', false, $product_id, $product );
				if ( $allow_checkout ) {
					return true;
				}

				$total_number = wcsn_get_serial_numbers( array(
					'product_id' => $product_id,
					'status'     => 'new'
				), true );

				if ( $total_number < $quantity ) {

					wc_add_notice( sprintf( __( 'Sorry, There is not enough Serial Number available for %s, Please remove this item or lower the quantity,
												For now we have %d Serial Number for this product. <br>', 'wc-serial-numbers' ), $product->get_title(), $total_number ), 'error' );

					return false;
				}

			}
		}
	}

	/**
	 * since 1.0.0
	 *
	 * @param $order_id
	 */
	public function process_order( $order_id ) {

		if ( 'on' == wcsn_get_settings( 'disable_automatic_delivery' ) ) {
			return;
		}

		wcsn_order_assign_serial_numbers( $order_id );

		do_action( 'wcsn_order_assigned_serial_numbers', $order_id );
	}

	public function remove_serial_numbers( $order_id ) {
		wcsn_order_remove_serial_numbers( $order_id );
	}
}
