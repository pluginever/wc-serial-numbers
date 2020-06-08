<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_API {

	/**
	 * WC_Serial_Numbers_API constructor.
	 */
	public static function init() {
		add_action( 'woocommerce_api_serial-numbers-api', array( __CLASS__, 'handle_api_request' ) );
		add_action( 'wcsn_api_action_check', array( __CLASS__, 'check_license' ) );
		add_action( 'wcsn_api_action_activate', array( __CLASS__, 'activate_license' ) );
		add_action( 'wcsn_api_action_deactivate', array( __CLASS__, 'deactivate_license' ) );
		add_action( 'wcsn_api_action_version_check', array( __CLASS__, 'version_check' ) );
	}

	/**
	 * handle request
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function handle_api_request() {
		$email         = ! empty( $_REQUEST['email'] ) ? sanitize_email( $_REQUEST['email'] ) : '';
		$serial_key    = ! empty( $_REQUEST['serial_key'] ) ? esc_attr( $_REQUEST['serial_key'] ) : '';
		$product_id    = ! empty( $_REQUEST['product_id'] ) ? absint( $_REQUEST['product_id'] ) : '';
		$order_id      = ! empty( $_REQUEST['order_id'] ) ? absint( $_REQUEST['order_id'] ) : '';
		$activation_id = ! empty( $_REQUEST['activation_id'] ) ? sanitize_key( $_REQUEST['activation_id'] ) : null;

		$allow_duplicate = 'on' === wc_serial_numbers()->get_settings( 'allow_duplicate', 'off' );
		if ( $allow_duplicate && ! is_email( $email ) ) {
			self::send_error( [
				'error' => __( 'Email is required', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		if ( empty( $serial_key ) ) {
			self::send_error( [
				'error' => sprintf( __( '%s is required', 'wc-serial-numbers' ), wc_serial_numbers()->get_label() ),
				'code'  => 403
			] );
		}

		if ( empty( $product_id ) ) {
			self::send_error( [
				'error' => __( 'Product id is required', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		$post_type = get_post_type( $product_id );
		if ( ! in_array( $post_type, array( 'product', 'product_variation' ) ) ) {
			self::send_error( [
				'error' => __( 'Invalid product id', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		global $wpdb;

		$query_where = 'WHERE 1=1';
		if ( $allow_duplicate ) {
			$query_where .= $wpdb->prepare( " AND activation_email=%s", $email );
			$query_where .= $wpdb->prepare( " AND order_id=%s", $order_id );
		}

		$query_where .= $wpdb->prepare( " AND serial_key=%s", wc_serial_numbers_decrypt_key( $serial_key ) );
		$query_where .= $wpdb->prepare( " AND product_id=%d", $product_id );

		$serial_number = $wpdb->get_row( "SELECT * FROM $wpdb->wcsn_serials_numbers $query_where" );

		if ( ! $serial_number ) {
			self::send_error( [
				'error' => sprintf( __( 'Invalid %s', 'wc-serial-numbers' ), wc_serial_numbers()->get_label() ),
				'code'  => 403
			] );
		}

		if ( $allow_duplicate && $serial_number->activation_email !== $email ) {
			self::send_error( [
				'error' => __( 'This email address is not associated with any serial key', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		if ( $serial_number->status !== 'active' ) {
			self::send_error( [
				'error' => sprintf( __( '%s is not active', 'wc-serial-numbers' ), wc_serial_numbers()->get_label() ),
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
			self::send_error( [
				'error' => __( 'Invalid request type', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		do_action( 'wcsn_api_action', $serial_number );
		do_action( "wcsn_api_action_{$request}", $serial_number );
	}

	/**
	 * since 1.0.0
	 *
	 * @param $serial_number
	 */
	public static function check_license( $serial_number ) {

		$activations      = WC_Serial_Numbers_Activation::get_activations( [
			'serial_id' => $serial_number->id,
			'status'    => 'active'
		] );
		$activation_limit = empty( $serial_number->activation_limit ) ? 99999 : intval( $serial_number->activation_limit );
		$remaining        = $activation_limit - intval( WC_Serial_Numbers_Activation::get_activation_count( $serial_number->id ) );
		self::send_success( apply_filters( 'wc_serial_numbers_check_license_response', [
			'expire_date' => $serial_number->expire_date,
			'remaining'   => $remaining,
			'status'      => $serial_number->status,
			'activations' => self::get_activations_response( $activations ),
			'product_id'  => $serial_number->product_id,
			'product'     => get_the_title( $serial_number->product_id ),
		], $serial_number ) );

	}

	/**
	 * since 1.0.0
	 *
	 * @param $serial_number
	 */
	public static function activate_license( $serial_number ) {
		$user_agent  = empty( $_SERVER['HTTP_USER_AGENT'] ) ? md5( time() ) : md5( $_SERVER['HTTP_USER_AGENT'] . time() );
		$instance    = ! empty( $_REQUEST['instance'] ) ? sanitize_textarea_field( $_REQUEST['instance'] ) : $user_agent;
		$platform    = ! empty( $_REQUEST['platform'] ) ? sanitize_textarea_field( $_REQUEST['platform'] ) : self::get_os();
		$activations = WC_Serial_Numbers_Activation::get_activations( [
			'serial_id' => $serial_number->id,
			'status'    => 'active',
			'instance'  => $instance,
			'platform'  => $platform,
		] );

		$remaining = intval( $serial_number->activation_limit ) - intval( WC_Serial_Numbers_Activation::get_activation_count( $serial_number->id ) );

		if ( empty( $activations ) && $remaining < 1 ) {
			$activations = WC_Serial_Numbers_Activation::get_activations( [
				'serial_id' => $serial_number->id,
				'status'    => 'active',
			] );

			self::send_error( [
				'error'            => __( 'Activation limit reached', 'wc-serial-numbers' ),
				'activations'      => self::get_activations_response( $activations ),
				'activation_limit' => intval( $serial_number->activation_limit ),
				'remaining'        => $remaining,
				'code'             => 403
			] );
		}

		$activation_id = WC_Serial_Numbers_Activation::activate( $serial_number->id, $instance, $platform );

		if ( ! $activation_id ) {
			self::send_error( [
				'error'       => __( 'Activation was failed', 'wc-serial-numbers' ),
				'activations' => $activations,
				'code'        => 403
			] );
		}

		$remaining = intval( $serial_number->activation_limit ) - intval( WC_Serial_Numbers_Activation::get_activation_count( $serial_number->id ) );

		$new_activations = WC_Serial_Numbers_Activation::get_activations( [
			'serial_id' => $serial_number->id,
			'status'    => 'active'
		] );

		$new_activation = WC_Serial_Numbers_Activation::get_activation( $activation_id );

		$new_activations_response = self::get_activations_response( $new_activations );

		$response = apply_filters( 'wc_serial_numbers_activate_license_response', array(
			'activated'        => true,
			'activations'      => $new_activations_response,
			'remaining'        => $remaining,
			'activation_limit' => intval( $serial_number->activation_limit ),
			'instance'         => $new_activation->instance,
			'product_id'       => $serial_number->product_id,
			'product'          => get_the_title( $serial_number->product_id ),
			'message'          => sprintf( __( '%s out of %s activations remaining', 'wc-serial-numbers' ), $remaining, $serial_number->activation_limit ),
		) );

		self::send_success( $response );

	}

	public static function deactivate_license( $serial_number ) {
		$instance = ! empty( $_REQUEST['instance'] ) ? sanitize_textarea_field( $_REQUEST['instance'] ) : '';

		if ( empty( $instance ) ) {
			self::send_error( [
				'error' => __( 'Instance is  missing, You must provide an instance to deactivate license', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		$activations = WC_Serial_Numbers_Activation::get_activations( [
			'serial_id' => $serial_number->id,
			'instance'  => $instance,
			'status'    => 'active',
		] );

		if ( empty( $activations ) ) {
			self::send_error( [
				'error' => __( 'Could not find any related instance to deactivate ', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		foreach ( $activations as $activation ) {
			$deactivated = WC_Serial_Numbers_Activation::get_activation( $activation->id );
		}

		$remaining = intval( $serial_number->activation_limit ) - intval( WC_Serial_Numbers_Activation::get_activation_count( $serial_number->id ) );

		$response = apply_filters( 'wcsn_deactivate_license_response', array(
			'deactivated'      => true,
			'remaining'        => $remaining,
			'activation_limit' => intval( $serial_number->activation_limit ),
			'message'          => sprintf( __( '%s out of %s activations remaining', 'wc-serial-numbers' ), $remaining, $serial_number->activation_limit ),
		) );

		self::send_success( $response );
	}

	/**
	 * @param $serial
	 *
	 * @since 1.0.0
	 */
	public static function version_check( $serial ) {
		self::send_success( array(
			'product_id' => $serial->product_id,
			'product'    => get_the_title( $serial->product_id ),
			'version'    => get_post_meta( $serial->product_id, '_software_version', true ),
		) );
	}

	/**
	 * since 1.0.0
	 *
	 * @param $activations
	 *
	 * @return array
	 */
	public static function get_activations_response( $activations ) {
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
	 * since 1.0.0
	 *
	 * @param $result
	 */
	public static function send_error( $result ) {
		nocache_headers();
		$result['timestamp'] = time();
		wp_send_json_error( $result );
	}

	/**
	 * since 1.0.0
	 *
	 * @param $result
	 */
	public static function send_success( $result ) {
		nocache_headers();
		$result['timestamp'] = time();
		wp_send_json_success( $result );
	}
}

WC_Serial_Numbers_API::init();
