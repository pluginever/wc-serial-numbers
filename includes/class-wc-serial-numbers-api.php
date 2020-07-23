<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_API {

	/**
	 * WC_Serial_Numbers_API constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_api_serial-numbers-api', array( $this, 'validate_api_request' ) );
		add_action( 'wc_serial_numbers_api_action_check', array( $this, 'check_license' ) );
		add_action( 'wc_serial_numbers_api_action_activate', array( $this, 'activate_license' ) );
		add_action( 'wc_serial_numbers_api_action_deactivate', array( $this, 'deactivate_license' ) );
		add_action( 'wc_serial_numbers_api_action_version_check', array( $this, 'version_check' ) );
	}

	public function validate_api_request() {
		$email           = ! empty( $_REQUEST['email'] ) ? sanitize_email( $_REQUEST['email'] ) : '';
		$serial_key      = ! empty( $_REQUEST['serial_key'] ) ? sanitize_text_field( $_REQUEST['serial_key'] ) : '';
		$product_id      = ! empty( $_REQUEST['product_id'] ) ? absint( $_REQUEST['product_id'] ) : '';
		$allow_duplicate = apply_filters( 'wc_serial_numbers_allow_duplicate_serial_number', false );
		if ( $allow_duplicate && ! is_email( $email ) ) {
			$this->send_error( [
				'error' => __( 'Email is required', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}
		if ( empty( $serial_key ) ) {
			$this->send_error( [
				'error' => __( 'Serial Number is required', 'wc-serial-numbers' ),
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
//		if ( $allow_duplicate ) {
//			$query_where .= $wpdb->prepare( " AND order_id=%s", $order_id );
//		}
		$query_where   .= $wpdb->prepare( " AND serial_key=%s", wc_serial_numbers_encrypt_key( $serial_key ) );
		$query_where   .= $wpdb->prepare( " AND product_id=%d", $product_id );
		$serial_number = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}serial_numbers $query_where" );
		if ( ! $serial_number ) {
			$this->send_error( [
				'error' => __( 'Serial Number is not associated with provided product id', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		$order = wc_get_order( $serial_number->order_id );

		if ( $allow_duplicate && $order->get_billing_email( 'edit' ) !== $email ) {
			$this->send_error( [
				'error' => __( 'This email address is not associated with any serial key', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		if ( $serial_number->status !== 'sold' ) {
			$this->send_error( [
				'error' => __( 'Serial Number is not active', 'wc-serial-numbers' ),
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


	/**
	 * since 1.0.0
	 *
	 * @param $serial_number
	 */
	public function check_license( $serial_number ) {
		$activations = WC_Serial_Numbers_Query::init()->from( 'serial_numbers_activations' )->where( [
			'serial_id' => $serial_number->id,
			'active'    => '1'
		] )->get();

		$remaining = $this->sanitize_activation_limit( $serial_number->activation_limit ) - $serial_number->activation_count;
		$this->send_success( apply_filters( 'wc_serial_numbers_check_license_response', [
			'expire_date' => $this->calculate_expire_date( $serial_number ),
			'remaining'   => $remaining,
			'status'      => $serial_number->status === 'sold' ? 'active' : $serial_number->status,
			'product_id'  => $serial_number->product_id,
			'product'     => get_the_title( $serial_number->product_id ),
			'activations' => $this->get_activations_response( $activations ),
		], $serial_number ) );
	}

	/**
	 * since 1.0.0
	 *
	 * @param $serial_number
	 */
	public function activate_license( $serial_number ) {
		$user_agent = empty( $_SERVER['HTTP_USER_AGENT'] ) ? md5( time() ) : md5( $_SERVER['HTTP_USER_AGENT'] . time() );
		$instance   = ! empty( $_REQUEST['instance'] ) ? sanitize_textarea_field( $_REQUEST['instance'] ) : $user_agent;
		$platform   = ! empty( $_REQUEST['platform'] ) ? sanitize_textarea_field( $_REQUEST['platform'] ) : self::get_os();

		$activation = WC_Serial_Numbers_Query::init()->from( 'serial_numbers_activations' )->where( [
			'serial_id' => $serial_number->id,
			'active'    => '1',
			'instance'  => $instance,
			'platform'  => $platform,
		] )->first();


		$remaining        = $this->sanitize_activation_limit($serial_number->activation_limit ) - intval( $serial_number->activation_count );

		//not active and no remaining
		if ( empty( $activation ) && $remaining < 1 ) {

			$this->send_error( [
				'error'            => __( 'Activation limit reached', 'wc-serial-numbers' ),
				'activation_limit' => intval( $this->sanitize_activation_limit($serial_number->activation_limit ) ),
				'remaining'        => $remaining,
				'activations'      => $this->get_activations_response( $this->get_active_activations( $serial_number->id ) ),
				'code'             => 403
			] );
		}

		//if not created yet
		if ( empty( $activation ) ) {
			$activation_id = wc_serial_numbers_insert_activation( [
				'serial_id' => $serial_number->id,
				'instance'  => $instance,
				'platform'  => $platform,
				'active'    => '1',
			] );

			if ( is_wp_error( $activation_id ) ) {
				$this->send_error( [
					'error'       => __( 'Activation was failed', 'wc-serial-numbers' ),
					'activations' => [],
					'code'        => 403
				] );
			}
			$activation = wc_serial_numbers_get_activation( $activation_id );
		}

		//since activation count updated so get again
		$serial_number = wc_serial_numbers_get_serial_number( $serial_number->id );
		$remaining     = intval( $this->sanitize_activation_limit($serial_number->activation_limit ) ) - intval( $serial_number->activation_count );
		$response      = apply_filters( 'wc_serial_numbers_activate_license_response', array(
			'activated'        => true,
			'remaining'        => $remaining,
			'activation_limit' => intval( $this->sanitize_activation_limit($serial_number->activation_limit ) ),
			'instance'         => $activation->instance,
			'product_id'       => $serial_number->product_id,
			'product'          => get_the_title( $serial_number->product_id ),
			'message'          => sprintf( __( 'Successfully activated. %s out of %s activations remaining', 'wc-serial-numbers' ), $remaining, $this->sanitize_activation_limit($serial_number->activation_limit ) ),
			'activations'      => $this->get_activations_response( $this->get_active_activations( $serial_number->id ) ),
		) );

		$this->send_success( $response );

	}

	public function deactivate_license( $serial_number ) {
		$instance = ! empty( $_REQUEST['instance'] ) ? sanitize_textarea_field( $_REQUEST['instance'] ) : '';

		if ( empty( $instance ) ) {
			$this->send_error( [
				'error' => __( 'Instance is  missing, You must provide an instance to deactivate license', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		$activation = WC_Serial_Numbers_Query::init()->from( 'serial_numbers_activations' )->where( [
			'serial_id' => $serial_number->id,
			'instance'  => $instance,
			'active'    => '1',
		] )->first();

		if ( empty( $activation ) ) {
			$this->send_error( [
				'error' => __( 'Could not find any related instance to deactivate ', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		wc_serial_numbers_update_activation( [ 'id' => $activation->id, 'active' => '0' ] );

		$serial_number = wc_serial_numbers_get_serial_number( $serial_number->id );
		$remaining     = intval( $this->sanitize_activation_limit($serial_number->activation_limit ) ) - intval( $serial_number->activation_count );

		$response = apply_filters( 'wc_serial_numbers_deactivate_license_response', array(
			'deactivated'      => true,
			'remaining'        => $remaining,
			'activation_limit' => intval( $this->sanitize_activation_limit($serial_number->activation_limit ) ),
			'message'          => sprintf( __( 'Deactivation completed. %s out of %s activations remaining', 'wc-serial-numbers' ), $remaining, $this->sanitize_activation_limit($serial_number->activation_limit ) ),
		) );

		self::send_success( $response );
	}

	/**
	 * @param $serial
	 *
	 * @since 1.0.0
	 */
	public function version_check( $serial ) {
		$this->send_success( array(
			'product_id' => $serial->product_id,
			'product'    => get_the_title( $serial->product_id ),
			'version'    => get_post_meta( $serial->product_id, '_software_version', true ),
		) );
	}

	/**
	 * @param $serial_id
	 *
	 * @return Object
	 * @since 1.2.0
	 */
	public function get_active_activations( $serial_id ) {
		return WC_Serial_Numbers_Query::init()->from( 'serial_numbers_activations' )->where( [
			'serial_id' => $serial_id,
			'active'    => '1'
		] )->get();
	}

	/**
	 * @return mixed|string
	 * @since 1.0.0
	 */
	public static function get_os() {
		$user_agent = @$_SERVER['HTTP_USER_AGENT'];

		$os_platform = "Unknown OS Platform";

		$os_array = array(
			'/windows nt 10/i'      => 'Windows 10',
			'/windows nt 6.3/i'     => 'Windows 8.1',
			'/windows nt 6.2/i'     => 'Windows 8',
			'/windows nt 6.1/i'     => 'Windows 7',
			'/windows nt 6.0/i'     => 'Windows Vista',
			'/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
			'/windows nt 5.1/i'     => 'Windows XP',
			'/windows xp/i'         => 'Windows XP',
			'/windows nt 5.0/i'     => 'Windows 2000',
			'/windows me/i'         => 'Windows ME',
			'/win98/i'              => 'Windows 98',
			'/win95/i'              => 'Windows 95',
			'/win16/i'              => 'Windows 3.11',
			'/macintosh|mac os x/i' => 'Mac OS X',
			'/mac_powerpc/i'        => 'Mac OS 9',
			'/linux/i'              => 'Linux',
			'/ubuntu/i'             => 'Ubuntu',
			'/iphone/i'             => 'iPhone',
			'/ipod/i'               => 'iPod',
			'/ipad/i'               => 'iPad',
			'/android/i'            => 'Android',
			'/blackberry/i'         => 'BlackBerry',
			'/webos/i'              => 'Mobile'
		);

		foreach ( $os_array as $regex => $value ) {

			if ( preg_match( $regex, $user_agent ) ) {
				$os_platform = $value;
			}
		}

		return $os_platform;
	}

	/**
	 * @param $limit
	 *
	 * @return int
	 */
	public function sanitize_activation_limit( $limit ) {
		return empty( $limit ) ? 99999 : intval( $limit );
	}

	/**
	 * since 1.0.0
	 *
	 * @param $activations
	 *
	 * @return array
	 */
	public function get_activations_response( $activations ) {
		$activations_response = [];
		foreach ( $activations as $activation ) {
			$activations_response[] = array(
				'instance'        => $activation->instance,
				'status'          => $activation->active ? 'active' : 'inactive',
				'platform'        => $activation->platform,
				'activation_time' => $activation->activation_time,
			);
		}

		return $activations_response;
	}

	/**
	 * Calculate expire date
	 *
	 * @param $serial
	 *
	 * @return false|string
	 * @since 1.1.6
	 */
	public function calculate_expire_date( $serial ) {
		if ( empty( $serial->validity ) ) {
			return '';
		}
		if ( empty( $serial->order_date ) || $serial->order_date == '0000-00-00 00:00:00' ) {
			return '';
		}

		return date( 'Y-m-d H:i:s', strtotime( "+$serial->validity day", strtotime( $serial->order_date ) ) );
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
		wp_send_json_success( $result );
	}
}

new WC_Serial_Numbers_API();
