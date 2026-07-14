<?php

namespace WooCommerceSerialNumbers;

use WooCommerceSerialNumbers\B8\Component;
use WooCommerceSerialNumbers\Models\Activation;
use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class API.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers
 */
class API extends Component {

	/**
	 * Whether to load.
	 *
	 * @since 2.4.0
	 * @return bool
	 */
	public function autoload(): bool {
		return wcsn_is_software_support_enabled();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		// add query vars.
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
		add_action( 'woocommerce_api_serial-numbers-api', array( $this, 'process_request' ) );
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
	public function add_query_vars( $vars ) {
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
	public function process_request() {
		global $wp;
		$action     = isset( $wp->query_vars['request'] ) ? sanitize_key( $wp->query_vars['request'] ) : '';
		$product_id = isset( $wp->query_vars['product_id'] ) ? absint( $wp->query_vars['product_id'] ) : '';
		$serial_key = isset( $wp->query_vars['serial_key'] ) ? sanitize_text_field( $wp->query_vars['serial_key'] ) : '';
		$email      = isset( $wp->query_vars['email'] ) ? strtolower( sanitize_email( $wp->query_vars['email'] ) ) : '';
		$instance   = isset( $wp->query_vars['instance'] ) ? sanitize_text_field( $wp->query_vars['instance'] ) : '';
		$platform   = isset( $wp->query_vars['platform'] ) ? sanitize_text_field( $wp->query_vars['platform'] ) : '';

		// if key, action or product id is missing, return error.
		if ( empty( $action ) || empty( $product_id ) || empty( $serial_key ) ) {
			wp_send_json_error(
				array(
					'code'    => 'missing_data',
					'message' => __( 'Missing data.', 'wc-serial-numbers' ),
				)
			);
		}

		// end rest api base url.
		$end_point    = rest_url( 'wcsn/' . $action );
		$redirect_url = add_query_arg(
			array(
				'product_id' => $product_id,
				'serial_key' => $serial_key,
				'request'    => $action,
				'email'      => $email,
				'instance'   => $instance,
				'platform'   => $platform,
			),
			$end_point
		);

		wp_safe_redirect( $redirect_url );
		exit();
	}
}
