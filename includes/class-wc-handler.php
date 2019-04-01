<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCSN_WC_Handler {

	/**
	 * WCSN_WC_Handler constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_check_cart_items', array( $this, 'validate_cart_content' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'order_process' ) );
		add_action( 'wcsn_process_serial_number', array( $this, 'process_serial_number' ), 10, 4 );
		add_action( 'woocommerce_order_status_completed', array( $this, 'mark_serial_numbers_as_used' ) );
		add_action( 'woocommerce_order_status_changed', array( $this, 'revoke_serial_numbers' ), 10, 4 );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_keys' ) );
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
			$product        = $cart_product['data'];
			$product_id     = $product->get_id();
			$quantity       = $cart_product['quantity'];
			$is_enabled     = get_post_meta( $product_id, '_is_serial_number', true ); //Check if the serial number enabled for this product.
			$assign_type    = get_post_meta( $product_id, '_serial_key_source', true );
			$allow_checkout = wcsn_get_settings( 'wsn_allow_checkout', 'no', 'wsn_general_settings' );
			if ( ( $is_enabled == 'yes' ) ) {

				if ( ( 'auto_generated' == $assign_type ) || ( 'on' == $allow_checkout ) ) {
					return true;
				}

				$total_number = wcsn_get_serial_numbers( array( 'product_id' => $product_id, 'status' => 'new' ), true );

				if ( $total_number < $quantity ) {

					wc_add_notice( sprintf( __( 'Sorry, There is not enough Serial Number available for %s, Please remove this item or lower the quantity,
												For now we have %d Serial Number for this product. <br>', 'wc-serial-numbers' ), $product->get_title(), $total_number ), 'error' );

					return false;
				}

			}
		}
	}

	/**
	 * Reserve or generate a serial number for the product during place order process.
	 *
	 * @param $order
	 * @param $data
	 */

	function order_process( $order_id ) {

		$order = wc_get_order( $order_id );
		$items = $order->get_items();

		foreach ( $items as $item_id => $item_data ) {

			$product    = $item_data->get_product();
			$product_id = $product->get_id();
			$quantity   = $item_data->get_quantity();

			$is_serial_number_enabled = get_post_meta( $product_id, '_is_serial_number', true ); //Check if the serial number enabled for this product.

			if ( 'yes' !== $is_serial_number_enabled ) {
				continue;
			}

			$serial_key_source = get_post_meta( $product_id, '_serial_key_source', true );
			$serial_key_source = empty( $serial_key_source ) ? 'custom_source' : $serial_key_source;
			do_action( 'wcsn_process_serial_number', $order, $product_id, $quantity, $serial_key_source );
		}

		do_action( 'wcsn_after_process_serial_number', $order, $product_id );

	}

	/**
	 * Assign a serial number with order but keep the status pending
	 *
	 * since 1.0.0
	 *
	 * @param $order
	 * @param $product_id
	 * @param $variation_id
	 * @param $quantity
	 * @param $serial_key_source
	 *
	 * @return bool
	 */
	public function process_serial_number( $order, $product_id, $quantity, $serial_key_source ) {
		if ( 'custom_source' != $serial_key_source ) {
			return false;
		}

		$serial_numbers = wcsn_get_serial_numbers( array( 'product_id' => $product_id, 'number' => $quantity, 'status' => 'new' ) );

		foreach ( $serial_numbers as $serial_number_item ) {
			wc_serial_numbers()->serial_number->update( $serial_number_item->id, array(
				'order_id'         => $order->get_id(),
				'activation_email' => $order->get_billing_email( 'edit' ),
				'status'           => 'pending',
				'order_date'       => current_time( 'mysql' )
			) );
		}

		if ( count( $serial_numbers ) < $quantity ) {
			$pending_serial_keys                = get_post_meta( $order->get_id(), 'wcsn_pending_keys_products', true );
			$pending_serial_keys                = empty( $pending_serial_keys ) ? [] : $pending_serial_keys;
			$pending_serial_keys[ $product_id ] = intval( $quantity - count( $serial_numbers ) );
		}
	}

	/**
	 * order_complete function.
	 *
	 * Order is complete - give out any license codes!
	 */
	public function mark_serial_numbers_as_used( $order_id ) {
		$order = new WC_Order( $order_id );

		if ( sizeof( $order->get_items() ) == 0 ) {
			return;
		}

		if ( class_exists( 'WC_Subscriptions' ) ) {
			return;
		}

		$serial_numbers = wcsn_get_serial_numbers( array( 'order_id' => $order_id ) );

		if ( empty( $serial_numbers ) ) {
			return;
		}

		foreach ( $serial_numbers as $product_id => $serial_keys ) {
			foreach ( $serial_keys as $serial_serial_id ) {
				wc_serial_numbers()->serial_number->update( $serial_serial_id, [ 'status' => 'active', 'order_date' => current_time( 'mysql' ) ] );
			}
		}
	}

	/**
	 *
	 * since 1.0.0
	 *
	 * @param $order_id
	 * @param $from_status
	 * @param $to_status
	 * @param $instance
	 */
	public function revoke_serial_numbers( $order_id, $from_status, $to_status, $instance ) {
		$serial_numbers = wcsn_get_serial_numbers( array( 'order_id' => $order_id ) );

		if ( empty( $serial_numbers ) ) {
			return;
		}

		$delivery_settings = wcsn_get_settings( 'wsn_revoke_serial_number', [], 'wsn_delivery_settings' );
		$delivery_settings = array_keys( $delivery_settings );
		if ( in_array( $from_status, array( 'completed', 'processing', 'on-hold' ) ) && in_array( $to_status, $delivery_settings ) ) {
			$reuse_serial_number = wcsn_get_settings( 'wsn_re_use_serial', 'no', 'wsn_delivery_settings' );
			foreach ( $serial_numbers as $serial_number ) {
				$data = [];
				if ( 'yes' == $reuse_serial_number ) {
					$data['order_date']       = '';
					$data['order_id']         = '';
					$data['order_date']       = '';
					$data['activation_email'] = '';
					$data['status']           = 'new';
				} else {
					$data['status'] = 'rejected';
				}
				wc_serial_numbers()->serial_number->update( $serial_number->id, $data );
			}
		}
	}

	/**
	 * email_keys function.
	 *
	 * @access public
	 * @return void
	 */
	public function email_keys( $order ) {
		global $wpdb;

		$order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();
		$order    = wc_get_order( $order_id );
		if ( 'completed' !== $order->get_status( 'edit' ) ) {
			return;
		}

		$serial_numbers = wcsn_get_serial_numbers( [ 'order_id' => $order_id ] );
		if ( empty( $serial_numbers ) ) {
			return;
		}
		wc_get_template( 'email-serial-numbers.php', array( 'serial_numbers' => $serial_numbers ), '', WC_SERIAL_NUMBERS_INCLUDES . '/admin/emails/' );
	}
}

new WCSN_WC_Handler();

