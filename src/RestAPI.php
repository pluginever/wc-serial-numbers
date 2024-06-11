<?php

namespace WooCommerceSerialNumbers;

use WooCommerceSerialNumbers\Models\Activation;
use WooCommerceSerialNumbers\Models\Key;

/**
 * Handles rest requests.
 *
 * @since 1.7.3
 * @package WooCommerceSerialNumbers
 */
class RestAPI {

	/**
	 * RestAPI Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	}

	/**
	 * Add endpoints.
	 *
	 * @since 1.7.3
	 * @return void
	 */
	public function register_endpoints() {
		register_rest_route(
			'wcsn',
			'/validate',
			array(
				'methods'             => 'GET',
				'permission_callback' => array( $this, 'validate_request' ),
				'callback'            => array( $this, 'validate_key' ),
				'args'                => array(
					'product_id' => array(
						'required' => true,
						'type'     => array( 'string', 'integer' ),
					),
					'serial_key' => array(
						'required' => true,
						'type'     => 'string',
					),
					'email'      => array(
						'required' => false,
						'type'     => 'email',
					),
				),
			)
		);

		// activate key.
		register_rest_route(
			'wcsn',
			'/activate',
			array(
				'methods'             => 'GET',
				'permission_callback' => array( $this, 'validate_request' ),
				'callback'            => array( $this, 'activate_key' ),
				'args'                => array(
					'product_id' => array(
						'required' => true,
						'type'     => 'string',
					),
					'serial_key' => array(
						'required' => true,
						'type'     => 'string',
					),
					'email'      => array(
						'required' => false,
						'type'     => 'email',
					),
				),
			)
		);

		// deactivate key.
		register_rest_route(
			'wcsn',
			'/deactivate',
			array(
				'methods'             => 'GET',
				'permission_callback' => array( $this, 'validate_request' ),
				'callback'            => array( $this, 'deactivate_key' ),
				'args'                => array(
					'product_id' => array(
						'required' => true,
						'type'     => 'string',
					),
					'serial_key' => array(
						'required' => true,
						'type'     => 'string',
					),
					'email'      => array(
						'required' => false,
						'type'     => 'email',
					),
				),
			)
		);

		// Check version.
		register_rest_route(
			'wcsn',
			'/version_check',
			array(
				'methods'             => 'GET',
				'permission_callback' => array( $this, 'validate_request' ),
				'callback'            => array( $this, 'version_check' ),
				array(
					'product_id' => array(
						'required' => true,
						'type'     => 'string',
					),
					'serial_key' => array(
						'required' => true,
						'type'     => 'string',
					),
					'email'      => array(
						'required' => false,
						'type'     => 'email',
					),
				),
			)
		);
	}

	/**
	 * Check if a given request has access to create an item.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_Error|boolean
	 */
	public function validate_request( $request ) {
		$product_id = absint( $request->get_param( 'product_id' ) );
		$key        = sanitize_text_field( $request->get_param( 'serial_key' ) );
		$email      = sanitize_email( $request->get_param( 'email' ) );

		// Check if product ID is valid.
		if ( ! $product_id || ! get_post( $product_id ) ) {
			return new \WP_Error( 'invalid_product_id', __( 'Invalid product ID.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}
		// Check if key is valid.
		if ( empty( $key ) ) {
			return new \WP_Error( 'missing_key', __( 'Serial key is required.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}

		// Check if key exists.
		$serial_key = Key::get(
			array(
				'serial_key' => $key,
				'product_id' => $product_id,
			)
		);
		if ( ! $serial_key ) {
			return new \WP_Error( 'invalid_key', __( 'Serial key is invalid.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}

		// Check if the key has order ID.
		if ( empty( $serial_key->get_order_id() ) || ( $serial_key->get_order_id() && ! get_post( $serial_key->get_order_id() ) ) ) {
			return new \WP_Error( 'invalid_key', __( 'Serial key is not authorized to use.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}

		// Check if order status is completed.
		$order = wc_get_order( $serial_key->get_order_id() );
		if ( ! $order || ! apply_filters( 'wc_serial_numbers_api_validate_order_status', 'completed' === $order->get_status(), $order ) ) {
			return new \WP_Error( 'invalid_order', __( 'Please complete your order to activate the serial key.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}

		// Check if key is valid for the product.
		if ( $serial_key->get_product_id() !== $product_id ) {
			return new \WP_Error( 'invalid_product_key', __( 'Serial key is not valid for this product.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}

		// If email is provided, check if it is valid.
		if ( ( $email || wcsn_is_duplicate_key_allowed() ) && strtolower( $order->get_billing_email() ) !== $email ) {
			return new \WP_Error( 'invalid_email', __( 'Invalid email address.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}

		// based on key status send response.
		if ( 'expired' === $serial_key->get_status() ) {
			return new \WP_Error( 'expired_key', __( 'Serial key is expired.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		} elseif ( 'cancelled' === $serial_key->get_status() ) {
			return new \WP_Error( 'key_cancelled', __( 'Serial key is cancelled.', 'wc-serial-numbers' ), array( 'status' => 400 ) );

		} elseif ( 'sold' !== $serial_key->get_status() ) {
			return new \WP_Error( 'invalid_key_status', __( 'Invalid serial key.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}

		return true;
	}

	/**
	 * Validate key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response
	 */
	public function validate_key( $request ) {
		$product_id = absint( $request->get_param( 'product_id' ) );
		$key        = sanitize_text_field( $request->get_param( 'serial_key' ) );

		$serial_key = Key::get(
			array(
				'serial_key' => $key,
				'product_id' => $product_id,
			)
		);

		$response = array(
			'code'             => 'key_valid',
			'message'          => __( 'Serial key is valid.', 'wc-serial-numbers' ),
			'activation_limit' => $serial_key->get_activation_limit(),
			'activation_count' => $serial_key->get_activation_count(),
			'activations_left' => $serial_key->get_activations_left(),
			'expire_date'      => $serial_key->get_expire_date(),
			'status'           => 'sold' === $serial_key->get_status() ? 'active' : $serial_key->get_status(),
			'product_id'       => $serial_key->get_product_id(),
			'product'          => $serial_key->get_product_title(),
			'activations'      => $serial_key->get_activations(
				array(
					'limit'  => - 1,
					'output' => ARRAY_A,
				)
			),

			// Deprecated.
			'remaining'        => $serial_key->get_activations_left(),
		);

		// send response.
		return rest_ensure_response( apply_filters( 'wc_serial_numbers_api_validate_response', $response, $serial_key ) );
	}

	/**
	 * Activate key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Activation response.
	 */
	public function activate_key( $request ) {
		$product_id = absint( $request->get_param( 'product_id' ) );
		$key        = sanitize_text_field( $request->get_param( 'serial_key' ) );
		$email      = sanitize_email( $request->get_param( 'email' ) );
		$instance   = sanitize_text_field( $request->get_param( 'instance' ) );
		$platform   = sanitize_text_field( $request->get_param( 'platform' ) );

		// if instance is not provided, create a new instance based on the request.
		if ( empty( $instance ) ) {
			$instance = md5( $email . $platform . time() );
		}

		$serial_key = Key::get(
			array(
				'serial_key' => $key,
				'product_id' => $product_id,
			)
		);

		// Check if instance is already activated.
		$activation = Activation::get(
			array(
				'serial_id' => $serial_key->get_id(),
				'instance'  => $instance,
			)
		);

		if ( $activation ) {
			return new \WP_Error( 'instance_already_activated', __( 'Instance is already activated.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}

		// Check if key is already activated.
		if ( $serial_key->get_activations_left() <= 0 ) {
			return new \WP_Error( 'no_activations_left', __( 'Activation limit reached.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}

		// Create activation.
		$activation = Activation::insert(
			array(
				'serial_id' => $serial_key->get_id(),
				'instance'  => $instance,
				'platform'  => $platform,
			)
		);

		$serial_key->recount_remaining_activation();

		$response = array(
			'code'             => 'key_activated',
			'message'          => __( 'Serial key is activated.', 'wc-serial-numbers' ),
			'activated'        => true,
			'instance'         => $activation->get_instance(),
			'platform'         => $activation->get_platform(),
			'activation_limit' => $serial_key->get_activation_limit(),
			'activation_count' => $serial_key->get_activation_count(),
			'activations_left' => $serial_key->get_activations_left(),
			'expires_at'       => $serial_key->get_expire_date(),
			'product_id'       => $serial_key->get_product_id(),
			'product'          => $serial_key->get_product_title(),
			'activations'      => $serial_key->get_activations(
				array(
					'limit'  => - 1,
					'output' => ARRAY_A,
				)
			),

			// Deprecated.
			'remaining'        => $serial_key->get_activations_left(),
		);

		// send response.
		return rest_ensure_response( apply_filters( 'wc_serial_numbers_api_activate_response', $response, $activation, $serial_key ) );
	}

	/**
	 * Deactivate key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Deactivation response.
	 */
	public function deactivate_key( $request ) {
		$product_id = absint( $request->get_param( 'product_id' ) );
		$key        = sanitize_text_field( $request->get_param( 'serial_key' ) );
		$instance   = sanitize_text_field( $request->get_param( 'instance' ) );

		if ( empty( $instance ) ) {
			return new \WP_Error( 'missing_instance', __( 'Instance is  missing, You must provide an instance to deactivate license.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}

		$serial_key = Key::get(
			array(
				'serial_key' => $key,
				'product_id' => $product_id,
			)
		);

		$activation = Activation::get(
			array(
				'serial_id' => $serial_key->get_id(),
				'instance'  => $instance,
			)
		);

		if ( ! $activation ) {
			return new \WP_Error( 'instance_not_found', __( 'Instance not found.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}

		$activation->delete();
		$serial_key->recount_remaining_activation();

		$response = array(
			'code'             => 'key_deactivated',
			'message'          => __( 'Serial key is deactivated.', 'wc-serial-numbers' ),
			'deactivated'      => true,
			'instance'         => $activation->get_instance(),
			'activation_limit' => $serial_key->get_activation_limit(),
			'activation_count' => $serial_key->get_activation_count(),
			'activations_left' => $serial_key->get_activations_left(),
			'expires_at'       => $serial_key->get_expire_date(),
			'product_id'       => $serial_key->get_product_id(),
			'product'          => $serial_key->get_product_title(),
			'activations'      => $serial_key->get_activations(
				array(
					'limit'  => - 1,
					'output' => ARRAY_A,
				)
			),

			// Deprecated.
			'remaining'        => $serial_key->get_activations_left(),
		);

		// send response.
		return rest_ensure_response( apply_filters( 'wc_serial_numbers_api_deactivate_response', $response, $activation, $serial_key ) );
	}

	/**
	 * Check version.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Version check response.
	 */
	public function version_check( $request ) {
		$product_id = absint( $request->get_param( 'product_id' ) );
		$key        = sanitize_text_field( $request->get_param( 'serial_key' ) );

		$serial_key = Key::get(
			array(
				'serial_key' => $key,
				'product_id' => $product_id,
			)
		);

		$response = array(
			'code'       => 'version_checked',
			'product_id' => $serial_key->get_product_id(),
			'product'    => $serial_key->get_product_title(),
			'version'    => get_post_meta( $serial_key->get_product_id(), '_software_version', true ),
		);

		// send response.
		return rest_ensure_response( $response );
	}
}
