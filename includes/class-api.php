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
		add_action( 'wc_serial_numbers_api_action_activate', array( $this, 'activate_license' ) );
		add_action( 'wc_serial_numbers_api_action_deactivate', array( $this, 'deactivate_license' ) );
		add_action( 'wc_serial_numbers_api_action_version_check', array( $this, 'version_check' ) );
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
		$order_id    = ! empty( $_REQUEST['order_id'] ) ? absint( $_REQUEST['order_id'] ) : '';
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
			$query_where .= $wpdb->prepare( " AND order_id=%s", $order_id );
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

		if ( $serial_number->status !== 'active' ) {
			$this->send_error( [
				'error' => sprintf( __( '%s is not active', 'wc-serial-numbers' ), wc_serial_numbers_labels( 'serial_numbers' ) ),
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

		$activations = wc_serial_numbers_get_activations( [
			'serial_id' => $serial_number->id,
			'status' => 'active'
		] );
		$activation_limit = empty($serial_number->activation_limit)? 99999 : intval($serial_number->activation_limit);
		$remaining   = $activation_limit - intval( wc_serial_numbers_get_activations_count( $serial_number->id ) );
		$this->send_success( apply_filters( 'wc_serial_numbers_check_license_response', [
			'expire_date' => $serial_number->expire_date,
			'remaining'   => $remaining,
			'status'      => $serial_number->status,
			'activations' => $this->get_activations_response( $activations ),
			'product_id' => $serial_number->product_id,
			'product'    => get_the_title( $serial_number->product_id ),
		], $serial_number ) );

	}

	/**
	 * since 1.0.0
	 *
	 * @param $serial_number
	 */
	public function activate_license( $serial_number ) {
		$user_agent  = empty( $_SERVER['HTTP_USER_AGENT'] ) ? md5( time() ) : md5( $_SERVER['HTTP_USER_AGENT'] . time() );
		$instance    = ! empty( $_REQUEST['instance'] ) ? sanitize_textarea_field( $_REQUEST['instance'] ) : $user_agent;
		$platform    = ! empty( $_REQUEST['platform'] ) ? sanitize_textarea_field( $_REQUEST['platform'] ) : self::get_os();
		$activations = wc_serial_numbers_get_activations( [
			'serial_id' => $serial_number->id,
			'status'    => 'active',
			'instance'  => $instance,
			'platform'  => $platform,
		] );

		$remaining = intval( $serial_number->activation_limit ) - intval( wc_serial_numbers_get_activations_count( $serial_number->id ) );

		if ( empty( $activations ) && $remaining < 1 ) {
			$activations = wc_serial_numbers_get_activations( [
				'serial_id' => $serial_number->id,
				'status'    => 'active',
			] );

			$this->send_error( [
				'error'            => __( 'Activation limit reached', 'wc-serial-numbers' ),
				'activations'      => $this->get_activations_response( $activations ),
				'activation_limit' => intval( $serial_number->activation_limit ),
				'remaining'        => $remaining,
				'code'             => 403
			] );
		}

		$activation_id = wc_serial_numbers_activate_serial_number( $serial_number->id, $instance, $platform );

		if ( ! $activation_id ) {
			$this->send_error( [
				'error'       => __( 'Activation was failed', 'wc-serial-numbers' ),
				'activations' => $activations,
				'code'        => 403
			] );
		}

		$remaining = intval( $serial_number->activation_limit ) - intval( wc_serial_numbers_get_activations_count( $serial_number->id ) );

		$new_activations = wc_serial_numbers_get_activations( [
			'serial_id' => $serial_number->id,
			'status'    => 'active'
		] );

		$new_activation = wc_serial_numbers_get_activation( $activation_id );

		$new_activations_response = $this->get_activations_response( $new_activations );

		$response = apply_filters( 'wc_serial_numbers_activate_license_response', array(
			'activated'        => true,
			'activations'      => $new_activations_response,
			'remaining'        => $remaining,
			'activation_limit' => intval( $serial_number->activation_limit ),
			'instance'         => $new_activation->instance,
			'product_id' => $serial_number->product_id,
			'product'    => get_the_title( $serial_number->product_id ),
			'message'          => sprintf( __( '%s out of %s activations remaining', 'wc-serial-numbers' ), $remaining, $serial_number->activation_limit ),
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

		$activations = wc_serial_numbers_get_activations( [
			'serial_id' => $serial_number->id,
			'instance'  => $instance,
			'status'    => 'active',
		] );

		if ( empty( $activations ) ) {
			$this->send_error( [
				'error' => __( 'Could not find any related instance to deactivate ', 'wc-serial-numbers' ),
				'code'  => 403
			] );
		}

		foreach ( $activations as $activation ) {
			$deactivated = wc_serial_numbers_deactivate_activation( $activation->id );
		}

		$remaining = intval( $serial_number->activation_limit ) - intval( wc_serial_numbers_get_activations_count( $serial_number->id ) );

		$response = apply_filters( 'wc_serial_numbers_deactivate_license_response', array(
			'deactivated'      => true,
			'remaining'        => $remaining,
			'activation_limit' => intval( $serial_number->activation_limit ),
			'message'          => sprintf( __( '%s out of %s activations remaining', 'wc-serial-numbers' ), $remaining, $serial_number->activation_limit ),
		) );

		$this->send_success( $response );
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

WC_Serial_Numbers_API::instance();
