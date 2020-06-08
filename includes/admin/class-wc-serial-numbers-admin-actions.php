<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Actions {
	public static function init() {

		//woocommerce
		add_filter( 'woocommerce_order_actions', array( __CLASS__, 'include_custom_order_actions' ) );
		add_action( 'woocommerce_order_action_order_add_serial_numbers', array(
			__CLASS__,
			'order_add_serial_numbers'
		) );

		add_action( 'woocommerce_order_action_order_remove_serial_numbers', array(
			__CLASS__,
			'order_revoke_serial_numbers'
		) );


		add_action( 'admin_post_wc_serial_numbers_add_serial_number', array( __CLASS__, 'add_serial_number' ) );
		add_action( 'admin_post_wc_serial_numbers_edit_serial_number', array( __CLASS__, 'add_serial_number' ) );
		add_action( 'wp_ajax_wc_serial_numbers_search_products', array( __CLASS__, 'search_products' ) );
		add_action( 'wp_ajax_wc_serial_numbers_decrypt_key', array( __CLASS__, 'decrypt_key' ) );
	}

	/**
	 * WooCommerce order add custom actions.
	 *
	 * @param $actions
	 *
	 * @return array
	 * @since 1.1.6
	 */
	public static function include_custom_order_actions( $actions ) {
		return array_merge( $actions, array(
			'order_add_serial_numbers'    => __( 'Assign Serial Numbers', 'wc-serial-numbers' ),
			'order_remove_serial_numbers' => __( 'Revoke Serial Numbers', 'wc-serial-numbers' ),
		) );
	}

	/**
	 * Manually add serial number with WC action.
	 *
	 * @since 1.1.6
	 * @param $order \WC_Order
	 */
	public static function order_add_serial_numbers( $order ) {
		$added = wc_serial_numbers_order_add_items( $order->get_id() );
		error_log($added);
	}


	/**
	 * Manually add serial number with WC action.
	 *
	 * @since 1.1.6
	 * @param $order \WC_Order
	 */
	public static function order_revoke_serial_numbers( $order ) {
		error_log('order_revoke_serial_numbers');
		$removed = wc_serial_numbers_order_remove_items( $order->get_id() );
		error_log($removed);
	}


	/**
	 * Add serial number.
	 *
	 * @since 1.1.5
	 */
	public static function add_serial_number() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wc_serial_numbers_edit_item' ) ) {
			wp_die( 'No, Cheating!' );
		}

		$posted = array(
			'id'               => ! empty( $_POST['id'] ) ? intval( $_POST['id'] ) : '',
			'serial_key'       => ! empty( $_POST['serial_key'] ) ? sanitize_textarea_field( $_POST['serial_key'] ) : '',
			'product_id'       => ! empty( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : '',
			'order_id'         => ! empty( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : '',
			'activation_limit' => ! empty( $_POST['activation_limit'] ) ? intval( $_POST['activation_limit'] ) : '',
			'validity'         => ! empty( $_POST['validity'] ) ? intval( $_POST['validity'] ) : '',
			'expire_date'      => ! empty( $_POST['expire_date'] ) ? sanitize_text_field( $_POST['expire_date'] ) : '',
			'status'           => ! empty( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'available',
		);

		$redirect_args = array(
			'page'   => 'wc-serial-numbers',
			'action' => empty( $posted['id'] ) ? 'add' : 'edit',
		);
		if ( ! empty( $posted['id'] ) ) {
			$redirect_args['id'] = $posted['id'];
		}

		if ( empty( $posted['product_id'] ) ) {
			WC_Serial_Numbers_Admin_Notice::add_notice( __( 'You must select a product to add serial number.', 'wc-serial-numbers' ), [ 'type' => 'error' ] );
			wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
			exit();
		}

		if ( empty( $posted['serial_key'] ) && empty( $posted['license_image'] ) ) {
			WC_Serial_Numbers_Admin_Notice::add_notice( __( 'The Serial Number is empty. Please enter a serial number and try again', 'wc-serial-numbers' ), [ 'type' => 'error' ] );
			wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
			exit();
		}

		$inserted = wc_serial_numbers_insert_item( $posted );
		if ( is_wp_error( $inserted ) ) {
			WC_Serial_Numbers_Admin_Notice::add_notice( $inserted->get_error_message(), [ 'type' => 'error' ] );
			wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
			exit();
		}
		WC_Serial_Numbers_Admin_Notice::add_notice( __( 'Serial Number saved successfully', 'wc-serial-numbers' ), [ 'type' => 'success' ] );

		wp_safe_redirect( add_query_arg( array( 'page' => $redirect_args['page'] ), admin_url( 'admin.php' ) ) );

		exit();
	}


	/**
	 * Search products.
	 *
	 * @since 1.1.6
	 */
	public static function search_products() {
		self::verify_nonce( 'wcsn_search_nonce', 'nonce' );
		self::check_permission();
		$search   = isset( $_REQUEST['search'] ) ? sanitize_text_field( $_REQUEST['search'] ) : '';
		$page     = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$products = wc_serial_numbers_get_products( [
			'page'   => $page,
			'search' => $search,
			'fields' => 'id',
		] );
		$total    = wc_serial_numbers_get_products( [
			'page'   => $page,
			'search' => $search,
			'fields' => 'id',
		], true );

		$more = false;
		if ( $total > ( 20 * $page ) ) {
			$more = true;
		}


		$results = array();
		foreach ( $products as $product_id ) {
			/** @var \WC_Product $product */
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				continue;
			}

			$text = sprintf(
				'(#%1$s) %2$s',
				$product->get_id(),
				html_entity_decode( $product->get_formatted_name() )
			);

			$results[] = array(
				'id'   => $product->get_id(),
				'text' => $text
			);

		}

		wp_send_json(
			array(
				'page'       => $page,
				'results'    => $results,
				'pagination' => array(
					'more' => $more
				)
			)
		);

	}

	/**
	 * Decrypt key
	 * @since 1.1.6
	 */
	public static function decrypt_key() {
		self::verify_nonce( 'wc_serial_numbers_decrypt_key', 'nonce' );
		self::check_permission();
		$serial_id = isset( $_REQUEST['serial_id'] ) ? sanitize_text_field( $_REQUEST['serial_id'] ) : '';
		if ( empty( $serial_id ) ) {
			wp_send_json_error( [] );
		}

		$serial_number = wc_serial_numbers_get_item( $serial_id );
		if ( empty( $serial_number ) ) {
			wp_send_json_error( [] );
		}

		try {
			$key = wc_serial_numbers_decrypt_key( $serial_number->serial_key );
		} catch ( Exception $exception ) {
			wp_send_json_error( [ $exception ] );
		}

		wp_send_json_success( [
			'key' => $key
		] );

	}


	/**
	 * Check permission
	 *
	 * since 1.0.0
	 */
	public static function check_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			self::send_error( __( 'Error: You are not allowed to do this.', 'wc-serial-numbers' ) );
		}
	}

	/**
	 * Verify nonce request
	 * since 1.0.0
	 *
	 * @param $action
	 */
	public static function verify_nonce( $action, $field = '_wpnonce' ) {
		if ( ! isset( $_REQUEST[ $field ] ) || ! wp_verify_nonce( $_REQUEST[ $field ], $action ) ) {
			self::send_error( __( 'Error: Nonce verification failed', 'wc-serial-numbers' ) );
		}
	}

	/**
	 * Wrapper function for sending success response
	 * since 1.0.0
	 *
	 * @param null $data
	 */
	public static function send_success( $data = null ) {
		wp_send_json_success( $data );
	}

	/**
	 * Wrapper function for sending error
	 * since 1.0.0
	 *
	 * @param null $data
	 */
	public static function send_error( $data = null ) {
		wp_send_json_error( $data );
	}
}

WC_Serial_Numbers_Actions::init();
