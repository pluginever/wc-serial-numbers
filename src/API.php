<?php

namespace WooCommerceSerialNumbers;

use WooCommerceSerialNumbers\Models\Activation;
use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class API.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers
 */
class API {

	/**
	 * API constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// add query vars.
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ), 0 );
		add_action( 'woocommerce_api_serial-numbers-api', array( __CLASS__, 'process_request' ) );
		add_action( 'wc_serial_numbers_api_action_check', array( __CLASS__, 'validate_key' ) );
		add_action( 'wc_serial_numbers_api_action_validate', array( __CLASS__, 'validate_key' ) );
		add_action( 'wc_serial_numbers_api_action_activate', array( __CLASS__, 'activate_key' ) );
		add_action( 'wc_serial_numbers_api_action_deactivate', array( __CLASS__, 'deactivate_key' ) );
		add_action( 'wc_serial_numbers_api_action_version_check', array( __CLASS__, 'check_version' ) );
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars Query vars.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function add_query_vars( $vars ) {
		$vars[] = 'product_id';
		$vars[] = 'serial_key';
		$vars[] = 'request';
		$vars[] = 'email';
		$vars[] = 'instance';
		$vars[] = 'platform';

		return $vars;
	}

	/**
	 * Process request.
	 *
	 * @since 1.0.0
	 */
	public static function process_request() {
		$product_id = self::get_query_var( 'product_id', 'absint' );
		$key        = self::get_query_var( 'serial_key' );
		$action     = self::get_query_var( 'request' );
		$email      = strtolower( self::get_query_var( 'email' ) );

		WCSN()->log(
			'API request',
			'debug',
			array(
				'product_id' => $product_id,
				'key'        => $key,
				'action'     => $action,
				'email'      => $email,
			)
		);

		// Check if action is valid.
		if ( ! in_array( $action, array( 'check', 'validate', 'activate', 'deactivate', 'version_check' ), true ) ) {
			WCSN()->log( sprintf( 'Invalid action: %s', $action ), 'error' );
			wp_send_json_error(
				array(
					'code'    => 'invalid_action',
					'message' => __( 'Invalid action.', 'wc-serial-numbers' ),
				)
			);
		}

		// Check if product ID is valid.
		if ( ! $product_id || ! get_post( $product_id ) ) {
			WCSN()->log( sprintf( 'Invalid product ID: %s', $product_id ), 'error' );
			wp_send_json_error(
				array(
					'code'    => 'invalid_product_id',
					'message' => __( 'Invalid product ID.', 'wc-serial-numbers' ),
				)
			);
		}

		// Check if key is valid.
		if ( empty( $key ) ) {
			wp_send_json_error(
				array(
					'code'    => 'missing_key',
					'message' => __( 'Serial key is required.', 'wc-serial-numbers' ),
				)
			);
		}

		// Check if key exists.
		$serial_key = Key::get(
			array(
				'serial_key' => $key,
				'product_id' => $product_id,
			)
		);
		if ( ! $serial_key ) {
			wp_send_json_error(
				array(
					'code'    => 'invalid_key',
					'message' => __( 'Serial key is invalid.', 'wc-serial-numbers' ),
				)
			);
		}

		// Check if the key has order ID.
		if ( empty( $serial_key->get_order_id() ) || ( $serial_key->get_order_id() && ! get_post( $serial_key->get_order_id() ) ) ) {
			wp_send_json_error(
				array(
					'code'    => 'invalid_key',
					'message' => __( 'Serial key is not authorized to use.', 'wc-serial-numbers' ),
				)
			);
		}

		// Check if order status is completed.
		$order = wc_get_order( $serial_key->get_order_id() );
		if ( ! $order || ! apply_filters( 'wc_serial_numbers_api_validate_order_status', 'completed' === $order->get_status(), $order ) ) {
			wp_send_json_error(
				array(
					'code'    => 'order_invalid',
					'message' => __( 'Please complete your order to activate the serial key.', 'wc-serial-numbers' ),
				)
			);
		}

		// Check if key is valid for the product.
		if ( $serial_key->get_product_id() !== $product_id ) {
			wp_send_json_error(
				array(
					'code'    => 'invalid_product_key',
					'message' => __( 'Serial key is not valid for this product.', 'wc-serial-numbers' ),
				)
			);
		}

		// If email is provided, check if it is valid.
		if ( ( $email || wcsn_is_duplicate_key_allowed() ) && strtolower( $order->get_billing_email() ) !== $email ) {
			wp_send_json_error(
				array(
					'code'    => 'invalid_email',
					'message' => __( 'Invalid email address.', 'wc-serial-numbers' ),
				)
			);
		}

		// based on key status send response.
		if ( 'expired' === $serial_key->get_status() ) {
			wp_send_json_error(
				array(
					'code'    => 'key_expired',
					'message' => __( 'Serial key is expired.', 'wc-serial-numbers' ),
				)
			);
		} elseif ( 'cancelled' === $serial_key->get_status() ) {
			wp_send_json_error(
				array(
					'code'    => 'key_cancelled',
					'message' => __( 'Serial key is cancelled.', 'wc-serial-numbers' ),
				)
			);
		} elseif ( 'sold' !== $serial_key->get_status() ) {
			wp_send_json_error(
				array(
					'code'    => 'invalid_key_status',
					'message' => __( 'Invalid serial key.', 'wc-serial-numbers' ),
				)
			);
		}

		do_action( 'wc_serial_numbers_api_action', $serial_key, $action );
		do_action( 'wc_serial_numbers_api_action_' . $action, $serial_key );
	}

	/**
	 * Validate key.
	 *
	 * @param Key $serial_key Serial key object.
	 *
	 * @since 1.0.0
	 */
	public static function validate_key( $serial_key ) {
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

		wp_send_json_success( apply_filters( 'wc_serial_numbers_api_validate_response', $response, $serial_key ) );
	}

	/**
	 * Activate key.
	 *
	 * @param Key $serial_key Serial key object.
	 *
	 * @since 1.0.0
	 */
	public static function activate_key( $serial_key ) {
		$user_agent = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? md5( sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) . time() ) : md5( time() );
		$instance   = self::get_query_var( 'instance', 'sanitize_text_field', $user_agent );
		$platform   = self::get_query_var( 'platform' );

		// Check if instance key is valid.
		if ( empty( $instance ) ) {
			wp_send_json_error(
				array(
					'code'    => 'invalid_instance',
					'message' => __( 'Instance is  missing, You must provide an instance to deactivate license', 'wc-serial-numbers' ),
				)
			);
		}

		// Check if instance is already activated.
		$activation = Activation::get(
			array(
				'serial_id' => $serial_key->get_id(),
				'instance'  => $instance,
			)
		);
		if ( $activation ) {
			wp_send_json_error(
				array(
					'code'    => 'instance_already_activated',
					'message' => __( 'Instance is already activated.', 'wc-serial-numbers' ),
				)
			);
		}

		// Check remaining activations.
		if ( $serial_key->get_activations_left() <= 0 ) {
			wp_send_json_error(
				array(
					'code'             => 'no_activations_left',
					'message'          => __( 'Activation limit reached', 'wc-serial-numbers' ),
					'activation_limit' => $serial_key->get_activation_limit(),
					'limit'            => $serial_key->get_activation_limit(),
					'count'            => $serial_key->get_activation_count(),
					'remaining'        => $serial_key->get_activations_left(),
					'activations'      => $serial_key->get_activations(
						array(
							'limit'  => - 1,
							'output' => ARRAY_A,
						)
					),
				)
			);
		}

		// Create activation.
		$activation = Activation::insert(
			array(
				'serial_id' => $serial_key->get_id(),
				'instance'  => $instance,
				'platform'  => $platform,
			)
		);
		if ( is_wp_error( $activation ) ) {
			wp_send_json_error(
				array(
					'code'    => 'activation_failed',
					'message' => __( 'Activation failed.', 'wc-serial-numbers' ),
				)
			);
		}

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

		wp_send_json_success( apply_filters( 'wc_serial_numbers_api_activate_response', $response, $serial_key ) );
	}

	/**
	 * Deactivate key.
	 *
	 * @param Key $serial_key Serial key object.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate_key( $serial_key ) {
		$instance = self::get_query_var( 'instance' );

		// Check if instance key is valid.
		if ( empty( $instance ) ) {
			wp_send_json_error(
				array(
					'code'    => 'invalid_instance',
					'message' => __( 'Instance is  missing, You must provide an instance to deactivate license', 'wc-serial-numbers' ),
				)
			);
		}

		// Check if instance is already activated.
		$activation = Activation::get(
			array(
				'serial_id' => $serial_key->get_id(),
				'instance'  => $instance,
			)
		);
		if ( ! $activation ) {
			wp_send_json_error(
				array(
					'code'    => 'instance_not_activated',
					'message' => __( 'Instance is not activated.', 'wc-serial-numbers' ),
				)
			);
		}

		// Deactivate instance.
		if ( is_wp_error( $activation->delete() ) ) {
			wp_send_json_error(
				array(
					'code'    => 'deactivation_failed',
					'message' => __( 'Deactivation failed.', 'wc-serial-numbers' ),
				)
			);
		}

		$serial_key->recount_remaining_activation();

		$response = array(
			'code'             => 'key_deactivated',
			'message'          => __( 'Serial key is deactivated.', 'wc-serial-numbers' ),
			'deactivated'      => true,
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
		);

		wp_send_json_success( apply_filters( 'wc_serial_numbers_api_deactivate_response', $response, $serial_key ) );
	}

	/**
	 * Check version.
	 *
	 * @param Key $serial_key Serial key object.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function check_version( $serial_key ) {
		wp_send_json_success(
			array(
				'code'       => 'version_checked',
				'product_id' => $serial_key->get_product_id(),
				'product'    => $serial_key->get_product_title(),
				'version'    => get_post_meta( $serial_key->get_product_id(), '_software_version', true ),
			)
		);
	}

	/**
	 * Get query var.
	 *
	 * @param string $key Query var key.
	 * @param string $sanitize Sanitize method.
	 * @param mixed  $fallback Fallback value.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public static function get_query_var( $key, $sanitize = 'sanitize_text_field', $fallback = null ) {
		global $wp;

		if ( isset( $wp->query_vars[ $key ] ) ) {
			return call_user_func( $sanitize, wp_unslash( $wp->query_vars[ $key ] ) );
		}

		return $fallback;
	}
}
