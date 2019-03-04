<?php

class WC_Serial_Numbers_API {

	/**
	 * @since 1.0.0
	 *
	 * @var object
	 */
	protected $data;

	/**
	 * WC_Serial_Numbers_API constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_api_serial-numbers-api', array( $this, 'handle_api_request' ) );
	}

	/**
	 * handle request
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function handle_api_request() {
		$email         = ! empty( $_REQUEST['email'] ) ? sanitize_email( $_REQUEST['email'] ) : '';
		$serial_key    = ! empty( $_REQUEST['serial_key'] ) ? sanitize_key( $_REQUEST['serial_key'] ) : '';
		$product_id    = ! empty( $_REQUEST['product_id'] ) ? intval( $_REQUEST['product_id'] ) : '';
		$instance      = ! empty( $_REQUEST['instance'] ) ? sanitize_textarea_field( $_REQUEST['instance'] ) : time();
		$platform      = ! empty( $_REQUEST['platform'] ) ? sanitize_textarea_field( $_REQUEST['platform'] ) : null;
		$activation_id = ! empty( $_REQUEST['activation_id'] ) ? sanitize_key( $_REQUEST['activation_id'] ) : null;

		//validation check
		if ( ! is_email( $email ) ) {
			$this->send_result( $this->error( '100', __( 'The email provided is invalid', 'wc-serial-numbers' ) ) );
		}

		if ( empty( $serial_key ) ) {
			$this->send_result( $this->error( '100', __( 'The serial key provided is invalid', 'wc-serial-numbers' ) ) );
		}

		if ( empty( $product_id ) ) {
			$this->send_result( $this->error( '100', __( 'The product id provided is invalid', 'wc-serial-numbers' ) ) );
		}

		$data = wcsn_get_serial_numbers( [ 'serial_key' => $serial_key, 'activation_email' => $email, 'product_id' => $product_id, 'expire_date' => '' ] );

		if ( empty( $data ) ) {
			$this->send_result( $this->error( '101', __( 'No matching serial key exists', 'wc-serial-numbers' ) ) );
		}

		$data = array_pop( $data );

		if ( 'active' !== $data->status ) {
			$this->send_result( $this->error( '106' ) );
		}

		// Validate order if set.
		if ( $data->order_id ) {
			$order = wc_get_order( $data->order_id );
			if ( empty( $order ) ) {
				$this->send_result( $this->error( '102', __( 'The order related to the data is not available.', 'wc-serial-numbers' ) ) );
			}

			if ( ! $order->has_status( 'completed' ) ) {
				$this->send_result( $this->error( '102', __( 'The purchase matching this product is not complete', 'wc-serial-numbers' ) ) );
			}
		}

		$serial = $data;

		$request = empty( $_REQUEST['request'] ) ? '' : sanitize_key( $_REQUEST['request'] );

		if ( empty( $request ) || ! in_array( $request, array( 'check', 'activate', 'deactivate', 'deactivate', 'version_check' ) ) ) {
			$this->send_result( $this->error( '100', __( 'Invalid request type', 'wc-serial-numbers' ) ) );
		}

		switch ( $request ) {
			case 'check':
				$this->check( $serial );
				break;
			case 'activate':
				$this->activate( $serial, $instance, $platform );
				break;
			case 'deactivate':
				$this->deactivation( $serial->id, $instance );
				break;
			case 'version_check':
				$this->version_check( $serial );
				break;
			default:
				$this->send_result( $this->error( '100', __( 'Invalid request type', 'wc-serial-numbers' ) ) );
				break;
		}
	}

	/**
	 * Check if serial number is okay
	 *
	 * since 1.0.0
	 *
	 * @param $serial
	 */
	public function check( $serial ) {
		global $wpdb;
		$activations_rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcsn_activations WHERE serial_id=%d", $serial->id ) );
		$activations      = array();

		foreach ( $activations_rows as $row ) {
			if ( ! $row->active ) {
				continue;
			}

			$activations[] = array(
				'activation_id' => $row->id,
				'instance'      => $row->instance,
				'platform'      => $row->platform,
				'time'          => $row->activation_time,
			);
		}

		$output_data['success']     = true;
		$output_data['time']        = time();
		$output_data['expire_date'] = wcsn_get_serial_expiration_date( $serial );
		$output_data['remaining']   = wcsn_get_remaining_activation( $serial->id );
		$output_data['activations'] = $activations;

		$this->send_result( $output_data );
	}

	/**
	 * activate license
	 *
	 * since 1.0.0
	 *
	 * @param $serial
	 * @param $instance
	 * @param $platform
	 */
	public function activate( $serial, $instance, $platform ) {
		global $wpdb;

//			$activation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wcsn_activations WHERE serial_id=%d AND instance=%s", $serial->id, $instance));

		$activations_remaining = wcsn_get_remaining_activation( $serial->id );

		//Check remaining activations only if this is new activation.
		if ( ! $activations_remaining ) {
			$this->send_result( $this->error( '103', __( 'Remaining activations is equal to zero', 'wc-serial-numbers' ) ) );
		}

		// Activation
		$result = wcsn_activate_serial_key( $serial->id, $instance, $platform );

		$output_data = array();

		$output_data['activated'] = $result;
		$output_data['instance']  = $instance;
		$output_data['message']   = sprintf( __( '%s out of %s activations remaining', 'wc-serial-numbers' ), $activations_remaining, $serial->activation_limit );
		$output_data['time']      = time();

		$this->send_result( $output_data );
	}

	/**
	 * Deactivate license key
	 *
	 * since 1.0.0
	 *
	 * @param        $serial_id
	 * @param string $instance
	 */
	public function deactivation( $serial_id, $instance = '' ) {
		global $wpdb;
		$activation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcsn_activations WHERE serial_id=%d AND instance=%s", $serial_id, $instance ) );

		if ( empty( $activation ) ) {
			$this->send_result( $this->error( '100', __( 'Could not find any active instance of the serial', 'wc-serial-numbers' ) ) );
		}

		$result = wcsn_deactivate_serial_key( $serial_id, $instance );

		$output_data = array();

		$output_data['deactivated'] = $result;
		$output_data['instance']    = $instance;
		$output_data['time']        = time();

		$this->send_result( $output_data );
	}

	/**
	 * Check if serial number is okay
	 *
	 * since 1.0.0
	 *
	 * @param $serial
	 */
	public function version_check( $serial ) {
		$output_data['success'] = true;
		$output_data['time']    = time();
		$output_data['version'] = get_post_meta( $serial->product_id, '_software_version', true );

		$this->send_result( $output_data );
	}


	/**
	 * get error code
	 *
	 * @since 1.0.0
	 *
	 * @param int    $code
	 * @param string $message
	 * @param bool   $status
	 *
	 * @return array
	 */
	public function error( $code = 100, $message = '', $status = false ) {
		switch ( $code ) {
			case '101' :
				$error = array( 'error' => __( 'Invalid Serial Key', 'wc-serial-numbers' ), 'code' => '101' );
				break;
			case '102' :
				$error = array( 'error' => __( 'Serial number has been deactivated', 'wc-serial-numbers' ), 'code' => '102' );
				break;
			case '103' :
				$error = array( 'error' => __( 'Exceeded maximum number of activations', 'wc-serial-numbers' ), 'code' => '103' );
				break;
			case '104' :
				$error = array( 'error' => __( 'Invalid Instance ID', 'wc-serial-numbers' ), 'code' => '104' );
				break;
			case '105' :
				$error = array( 'error' => __( 'Invalid security key', 'wc-serial-numbers' ), 'code' => '105' );
				break;
			case '106' :
				$error = array( 'error' => __( 'Matching serial number is not active yet.', 'wc-serial-numbers' ), 'code' => '106' );
				break;
			case '403' :
				$error = array( 'error' => __( 'Forbidden', 'wc-serial-numbers' ), 'code' => '403' );
				break;
			default :
				$error = array( 'error' => __( 'Invalid Request', 'wc-serial-numbers' ), 'code' => '100' );
				break;
		}

		if ( ! empty( $message ) ) {
			$error['message'] = $message;
		}
		$error['timestamp'] = time();
		$error['status']    = $status;

		return $error;
	}


	/**
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $result
	 */
	public function send_result( $result ) {
		nocache_headers();
		wp_send_json( $result );
	}
}

new WC_Serial_Numbers_API();
