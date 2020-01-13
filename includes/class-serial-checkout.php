<?php
defined( 'ABSPATH' ) || exit();

/**
 * Class Serial_Numbers_Checkout
 */
class Serial_Numbers_Checkout {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.0.0
	 */
	private static $instance = null;

	/**
	 * Serial_Numbers_Checkout constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'validate_checkout' ) );
		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'process_order' ) );
		add_action( 'woocommerce_update_order', array( __CLASS__, 'process_order' ) );
	}

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
	 * Validate cart content
	 *
	 * since 1.0.0
	 * @return bool
	 */
	public static function validate_checkout() {
		$car_products = WC()->cart->get_cart_contents();
		foreach ( $car_products as $id => $cart_product ) {
			/** @var WC_Product $product */
			$product          = $cart_product['data'];
			$product_id       = $product->get_id();
			$quantity         = $cart_product['quantity'];
			$is_enabled       = wc_serial_numbers_product_support_serial_number( $product_id );
			$allow_validation = apply_filters( 'wc_serial_numbers_allow_cart_validation', true, $product_id, $car_products );

			if ( $is_enabled && $allow_validation ) {
				$delivery_quantity = get_post_meta( $product_id, '_delivery_quantity', true );
				$needed_quantity   = $quantity * ( empty( $delivery_quantity ) ? 1 : absint( $delivery_quantity ) );

				$total_number = wc_serial_numbers_get_serial_numbers( array(
					'product_id' => $product_id,
					'status'     => 'available',
					'per_page'   => $needed_quantity
				), true );

				if ( $total_number < $quantity ) {
					$notice = apply_filters( 'wc_serial_numbers_low_stock_notice_message', sprintf( __( 'Sorry, There is not enough Serial Number available for %s, Please remove this item or lower the quantity,
												For now we have %d Serial Number for this product. <br>', 'wc-serial-numbers' ), $product->get_title(), $total_number ), $product_id, $cart_product );
					wc_add_notice( $notice, 'error' );

					return false;
				}
			}

			do_action( 'wc_serial_number_product_cart_validation_complete', $product_id, $cart_product );
		}
	}


	/**
	 * @param $order_id
	 */
	function process_order( $order_id ) {
		$order          = wc_get_order( $order_id );
		/** @var $order WC_Order $order */
		$items          = $order->get_items();
		$serial_numbers = array();

		foreach ( $items as $item_data ) {
			/** @var WC_Order_Item $item_data */
			/** @var WC_Product $product */
			$product    = $item_data->get_product();
			$product_id = $product->get_id();
			if ( ! wc_serial_numbers_product_support_serial_number( $product_id ) ) {
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


}

Serial_Numbers_Checkout::instance();
