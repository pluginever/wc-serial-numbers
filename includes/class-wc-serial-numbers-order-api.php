<?php
/**
 * Serial numbers order api
 *
 * @since 1.2.9
 * @package WCSerialNumbers
 */

defined( 'ABSPATH' ) || exit();

/**
 * Class WC_Serial_Numbers_Order_API
 */
class WC_Serial_Numbers_Order_API {
	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_api_serial-numbers-order-api', array( $this, 'validate_api_request' ) );
		add_action( 'wc_serial_numbers_order_api_action', array( $this, 'check_orders' ) );
	}

	/**
	 * Validate api request
	 *
	 * @since 1.2.9
	 */
	public function validate_api_request() {
		$order_id        = ! empty( $_REQUEST['order_id'] ) ? $_REQUEST['order_id'] : array(); //phpcs:ignore
		$email           = ! empty( $_REQUEST['email'] ) ? sanitize_email( $_REQUEST['email'] ) : ''; //phpcs:ignore
		$page            = ! empty( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : 1; //phpcs:ignore
		$per_page        = ! empty( $_REQUEST['per_page'] ) ? sanitize_text_field( $_REQUEST['per_page'] ) : 10; //phpcs:ignore
		$exclude         = ! empty( $_REQUEST['exclude'] ) ? sanitize_text_field( $_REQUEST['exclude'] ) : ''; //phpcs:ignore
		$include         = ! empty( $_REQUEST['include'] ) ? sanitize_text_field( $_REQUEST['include'] ) : ''; //phpcs:ignore
		$order           = ! empty( $_REQUEST['order'] ) ? sanitize_text_field( $_REQUEST['order'] ) : 'asc'; //phpcs:ignore
		$orderby         = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'post_date'; //phpcs:ignore
		$parent          = ! empty( $_REQUEST['parent'] ) ? sanitize_text_field( $_REQUEST['parent'] ) : array(); //phpcs:ignore
		$status          = ! empty( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : ''; //phpcs:ignore
		$product         = ! empty( $_REQUEST['product'] ) ? sanitize_text_field( $_REQUEST['product'] ) : array(); //phpcs:ignore
		$allow_duplicate = apply_filters( 'wc_serial_numbers_allow_duplicate_serial_number', false );
		if ( $allow_duplicate && ! is_email( $email ) ) {
			$this->send_error(
				[
					'error' => __( 'Email is required', 'wc-serial-numbers' ),
					'code'  => 403,
				]
			);
		}

		if ( ! empty( $order_id ) ) {
			$wc_order = wc_get_order( $order_id );
			if ( empty( $wc_order ) ) {
				$this->send_error(
					[
						'error' => __( 'No order is found for this provided id', 'wc-serial-number' ),
						'code'  => 403,
					]
				);
			}
		}

		$request = empty( $_REQUEST['request'] ) ? '' : sanitize_key( $_REQUEST['request'] ); //phpcs:ignore
		if ( empty( $request ) || ! in_array( $request, array( 'check_order' ), true ) ) { //phpcs:ignore
			$this->send_error(
				[
					'error' => __( 'Invalid request type', 'wc-serial-numbers' ),
					'code'  => 403,
				]
			);
		}

		$request_data = array(
			'order_id' => $order_id,
			'email'    => $email,
			'page'     => $page,
			'per_page' => $per_page,
			'order'    => $order,
			'orderby'  => $orderby,
			'status'   => $status,
		);
		do_action( 'wc_serial_numbers_order_api_action', $request_data );

	}

	/**
	 * Check order details
	 *
	 * @param array $request_data Request data
	 *
	 * @since 1.2.9
	 */
	public function check_orders( $request_data ) {
		$orders = WC_Serial_Numbers_Query::init()->from( 'posts' )->where(
			array(
				'post_type'   => 'shop_order',
				'post_status' => 'wc-' . $request_data['status'],
			)
		)->order_by( $request_data['orderby'], $request_data['order'] )->page( $request_data['page'], $request_data['per_page'] )->get();

		$order_data = array();
		if ( is_array( $orders ) && ! empty( $orders ) ) {
			foreach ( $orders as $order ) {
				$wc_orders = wc_get_order( $order->ID );
				if ( ! empty( $wc_orders ) ) {
					$items          = $wc_orders->get_items();
					$item_data      = array();
					$serial_numbers = array();
					foreach ( $items as $item ) {
						$item_data[] = $item->get_data();

					}
					$serial_numbers = WC_Serial_Numbers_Query::init()->from( 'serial_numbers' )->where( array( 'order_id' => $order->ID ) )->get();
					foreach ( $serial_numbers as $key => $serial_number ) {
						$serial_numbers[ $key ]->{'serial_key'} = wc_serial_numbers_decrypt_key( $serial_number->serial_key );
					}
					$order_data[] = array(
						'order_id'             => $wc_orders->get_id(),
						'status'               => $wc_orders->get_status(),
						'currency'             => $wc_orders->get_currency(),
						'total'                => $wc_orders->get_total(),
						'total_shipping'       => $wc_orders->get_shipping_total(),
						'total_discount'       => $wc_orders->get_discount_total(),
						'billing'              => array(
							'first_name' => $wc_orders->get_billing_first_name(),
							'last_name'  => $wc_orders->get_billing_last_name(),
							'company'    => $wc_orders->get_billing_company(),
							'address_1'  => $wc_orders->get_billing_address_1(),
							'address_2'  => $wc_orders->get_billing_address_2(),
							'city'       => $wc_orders->get_billing_city(),
							'state'      => $wc_orders->get_billing_state(),
							'postcode'   => $wc_orders->get_billing_postcode(),
							'country'    => $wc_orders->get_billing_country(),
							'email'      => $wc_orders->get_billing_email(),
							'phone'      => $wc_orders->get_billing_phone(),
						),
						'shipping'             => array(
							'first_name' => $wc_orders->get_shipping_first_name(),
							'last_name'  => $wc_orders->get_shipping_last_name(),
							'company'    => $wc_orders->get_shipping_company(),
							'address_1'  => $wc_orders->get_shipping_address_1(),
							'address_2'  => $wc_orders->get_shipping_address_2(),
							'city'       => $wc_orders->get_shipping_city(),
							'state'      => $wc_orders->get_shipping_state(),
							'postcode'   => $wc_orders->get_shipping_postcode(),
							'country'    => $wc_orders->get_shipping_country(),
						),
						'payment_method'       => $wc_orders->get_payment_method(),
						'payment_method_title' => $wc_orders->get_payment_method_title(),
						'transaction_id'       => $wc_orders->get_transaction_id(),
						'date_paid'            => $wc_orders->get_date_paid()->format( 'y-m-d' ),
						'date_completed'       => $wc_orders->get_date_completed()->format( 'y-m-d' ),
						'items'                => $item_data,
						'serial_numbers'       => $serial_numbers,
					);
				}
			}
		}
		// error_log( print_r( wp_json_encode( $order_data ), true ) );
		$this->send_success(  $order_data  );
	}


	/**
	 * since 1.0.0
	 *
	 * @param array $result Result
	 */
	public function send_error( $result ) {
		nocache_headers();
		$result['timestamp'] = time();
		wp_send_json_error( $result );
	}

	/**
	 * since 1.0.0
	 *
	 * @param array $result Result
	 */
	public function send_success( $result ) {
		nocache_headers();
		$result['timestamp'] = time();
		wp_send_json_success( $result );
	}
}

new WC_Serial_Numbers_Order_API();
