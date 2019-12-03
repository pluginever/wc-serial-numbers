<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_API {
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
	 * WC_Serial_Numbers_API constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_api_serial-numbers-api', array( $this, 'handle_api_request' ) );
		add_action( 'wc_serial_numbers_api_action_check', array( $this, 'check_license' ) );
	}

	/**
	 * handle request
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function handle_api_request() {
		$email         = ! empty( $_REQUEST['email'] ) ? sanitize_email( $_REQUEST['email'] ) : '';
		$serial_key    = ! empty( $_REQUEST['serial_key'] ) ? esc_attr( $_REQUEST['serial_key'] ) : '';
		$product_id    = ! empty( $_REQUEST['product_id'] ) ? absint( $_REQUEST['product_id'] ) : '';
		$instance      = ! empty( $_REQUEST['instance'] ) ? sanitize_textarea_field( $_REQUEST['instance'] ) : time();
		$platform      = ! empty( $_REQUEST['platform'] ) ? sanitize_textarea_field( $_REQUEST['platform'] ) : null;
		$activation_id = ! empty( $_REQUEST['activation_id'] ) ? sanitize_key( $_REQUEST['activation_id'] ) : null;

		$allow_duplicate = wc_serial_numbers_is_allowed_duplicate_serial_numbers();
		if ( $allow_duplicate && ! is_email( $email ) ) {
			$this->send_error( [
				'error' => __( 'Email is required', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		if ( empty( $serial_key ) ) {
			$this->send_error( [
				'error' => sprintf( __( '%s is required', 'wc-serial-numbers' ), wc_serial_numbers_labels( 'serial_numbers' ) ),
				'code'  => 403
			] );
		}

		if ( empty( $product_id ) ) {
			$this->send_error( [
				'error' => __( 'Product id is required', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		$post_type = get_post_type( $product_id );
		if ( ! in_array( $post_type, array( 'product', 'product_variation' ) ) ) {
			$this->send_error( [
				'error' => __( 'Invalid product id', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		global $wpdb;

		$query_where = 'WHERE 1=1';
		if ( $allow_duplicate ) {
			$query_where .= $wpdb->prepare( " AND activation_email=%s", $email );
		}

		$query_where .= $wpdb->prepare( " AND serial_key=%s", wc_serial_numbers_encrypt_serial_number( $serial_key ) );
		$query_where .= $wpdb->prepare( " AND product_id=%d", $product_id );

		$serial_number = $wpdb->get_row( "SELECT * FROM $wpdb->wcsn_serials_numbers $query_where" );

		if ( ! $serial_number ) {
			$this->send_error( [
				'error' => sprintf( __( 'Invalid %s', 'wc-serial-numbers' ), wc_serial_numbers_labels( 'serial_numbers' ) ),
				'code'  => 403
			] );
		}

		if ( $allow_duplicate && $serial_number->activation_email !== $email ) {
			$this->send_error( [
				'error' => __( 'This email address is not associated with any serial key', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}


		$request = empty( $_REQUEST['request'] ) ? '' : sanitize_key( $_REQUEST['request'] );
		if ( empty( $request ) || ! in_array( $request, array(
				'check',
				'activate',
				'deactivate',
				'deactivate',
				'version_check'
			) ) ) {
			$this->send_error( [
				'error' => __( 'Invalid request type', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		do_action( 'wc_serial_numbers_api_action', $serial_number );
		do_action( "wc_serial_numbers_api_action_{$request}", $serial_number );
	}


	public function check_license( $serial_number ) {

	}


	/**
	 * since 1.0.0
	 *
	 * @param $result
	 */
	public function send_error( $result ) {
		nocache_headers();
		$result['timestamp'] = time();
		wp_send_json_error( $result );
	}

	/**
	 * since 1.0.0
	 *
	 * @param $result
	 */
	public function send_success( $result ) {
		nocache_headers();
		$result['timestamp'] = time();
		wp_send_json_error( $result );
	}

}

WC_Serial_Numbers_API::instance();
