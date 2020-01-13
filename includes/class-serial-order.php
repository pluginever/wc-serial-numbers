<?php
defined( 'ABSPATH' ) || exit();

/**
 * Class Serial_Numbers_Order
 */
class Serial_Numbers_Order{
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.0.0
	 */
	private static $instance = null;

	/**
	 * Serial_Numbers_Order constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'details_after_order_table') );
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'email_after_order_table') );
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'auto_complete_order') );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'order_complete_handler'), 10, 3  );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'revoke_order'), 10, 3  );
	}

	/**
	 * Print ordered serial numbers on thank you page.
	 *
	 * @since 1.2.0
	 *
	 * @param $order WC_Order
	 */
	public static function details_after_order_table( $order ) {
		$order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();

		$order = wc_get_order( $order_id );
		if ( 'completed' !== $order->get_status( 'edit' ) ) {
			return;
		}

		$serial_numbers = wc_serial_numbers_get_serial_numbers( [
			'order_id' => $order_id,
			'number'   => - 1
		] );

		if ( empty( $serial_numbers ) ) {
			return;
		}

		wc_serial_numbers_get_views( 'order-serial-numbers-table.php', array( 'serial_numbers' => $serial_numbers ) );
	}

	/**
	 * @param $order WC_Order
	 */
	public static function email_after_order_table( $order ){
		$order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();

		$order = wc_get_order( $order_id );
		if ( 'completed' !== $order->get_status( 'edit' ) ) {
			return;
		}

		$serial_numbers = wc_serial_numbers_get_serial_numbers( [
			'order_id' => $order_id,
			'number'   => - 1
		] );

		if ( empty( $serial_numbers ) ) {
			return;
		}

		wc_serial_numbers_get_views( 'order-serial-numbers-table.php', array( 'serial_numbers' => $serial_numbers ) );
	}


	/**
	 * Auto Complete Order
	 *
	 * @param $order
	 *
	 * @since 1.0.0
	 *
	 */
	public static function auto_complete_order( $order_id ) {
		if ( 'on' !== wc_serial_numbers_get_settings( 'autocomplete_order' ) ) {
			return;
		}
		$order          = wc_get_order( $order_id );
		$current_status = $order->get_status();
		// We only want to update the status to 'completed' if it's coming from one of the following statuses:
		//$allowed_current_statuses = array( 'on-hold', 'pending', 'failed' );
		if ( 'processing' == $current_status ) {
			$items = $order->get_items();
			foreach ( $items as $item_data ) {
				/** @var WC_Product $product */
				$product                  = $item_data->get_product();
				$product_id               = $product->get_id();
				$is_serial_number_enabled = get_post_meta( $product_id, '_is_serial_number', true ); //Check if the serial number enabled for this product.
				if ( 'yes' == $is_serial_number_enabled ) {
					$order->update_status( 'completed' );

					return;
				}
			}
		}

	}

	/**
	 * Automatically assign serial number when order is complete
	 *
	 * @param $order_id
	 *
	 * @since 1.0.0
	 */
	public static function order_complete_handler( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		if ( ! wc_serial_numbers_is_order_automatically_assign_serial_numbers() ) {
			return;
		}

		wc_serial_numbers_order_assign_serial_numbers( $order_id );

	}


	/**
	 * Revoke ordered serial numbers
	 *
	 * @param $order_id
	 * @param $status_from
	 * @param $status_to
	 */
	public static function revoke_order( $order_id, $status_from, $status_to ) {
		$serial_numbers = wc_serial_numbers_get_serial_numbers( array(
			'order_id' => $order_id,
			'number'   => - 1,
			'fields'   => 'id'
		) );

		if ( empty( $serial_numbers ) ) {
			return;
		}

		$reuse = wc_serial_numbers_is_reuse_serial_numbers();

		if ( in_array( $status_to, array( 'refunded', 'failed', 'cancelled' ) ) ) {
			foreach ( $serial_numbers as $serial_number_id ) {
				$args = array(
					'id'     => $serial_number_id,
					'status' => $status_to,
				);

				if ( $reuse ) {
					$args = array_merge( $args, array(
						'status'           => 'available',
						'customer_id'      => '',
						'order_id'         => '',
						'activation_email' => '',
						'order_date'       => '',
					) );
				}

				wcsn_insert_serial_number( $args );
			}
		}
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




}

Serial_Numbers_Order::instance();
