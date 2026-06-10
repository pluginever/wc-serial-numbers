<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;
use WP_REST_Request;

/**
 * Base test case for Serial Numbers tests.
 *
 * Characterizes the CURRENT (pre-b8-migration) behavior. Assertions target DB
 * rows, REST responses, hook effects, and notices so they survive the framework
 * swap. This plugin uses the WCSN() singleton (no B8 container).
 */
abstract class TestCase extends \WP_UnitTestCase {

	/**
	 * Set up test fixtures.
	 *
	 * Truncates both custom tables and clears the stock-count transient so each
	 * test starts from a known state.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$this->reset_state();
	}

	/**
	 * Tear down test fixtures.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		$this->reset_state();
		parent::tearDown();
	}

	/**
	 * Truncate custom tables and clear the stock transient.
	 *
	 * @return void
	 */
	protected function reset_state(): void {
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}serial_numbers" );
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}serial_numbers_activations" );
		delete_transient( 'wcsn_products_stock_count' );
	}

	/**
	 * Build a REST API request.
	 *
	 * @param string $path   REST API path.
	 * @param array  $params Request parameters.
	 * @param string $method HTTP method.
	 * @return WP_REST_Request
	 */
	protected function get_rest_request( string $path, array $params = array(), string $method = 'GET' ): WP_REST_Request {
		$request = new WP_REST_Request( $method, $path );

		if ( $params && 'GET' === $method ) {
			$request->set_query_params( $params );
		} elseif ( $params && in_array( $method, array( 'POST', 'PUT', 'PATCH', 'DELETE' ), true ) ) {
			$request->set_body_params( $params );
		}

		return $request;
	}

	/**
	 * Set a plugin option.
	 *
	 * @param string $name  Option name.
	 * @param mixed  $value Option value.
	 * @return void
	 */
	protected function set_option( string $name, $value ): void {
		update_option( $name, $value );
	}

	/**
	 * Create an available (unsold) serial key for a product.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $serial     Plaintext serial key.
	 * @param array  $args       Extra key args.
	 * @return Key
	 */
	protected function make_available_key( int $product_id, string $serial, array $args = array() ): Key {
		$key = Key::insert(
			array_merge(
				array(
					'product_id' => $product_id,
					'serial_key' => $serial,
					'status'     => 'available',
				),
				$args
			)
		);

		$this->assertNotWPError( $key, 'make_available_key() failed: ' . ( is_wp_error( $key ) ? $key->get_error_message() : '' ) );

		return $key;
	}

	/**
	 * Create a simple product enabled for serial numbers.
	 *
	 * @param array $args Extra product args.
	 * @return \WC_Product_Simple
	 */
	protected function create_product( array $args = array() ): \WC_Product_Simple {
		$product = new \WC_Product_Simple();
		$product->set_name( $args['name'] ?? 'Test Software' );
		$product->set_regular_price( $args['price'] ?? '10' );
		$product->set_status( 'publish' );
		if ( isset( $args['manage_stock'] ) ) {
			$product->set_manage_stock( $args['manage_stock'] );
		}
		if ( isset( $args['stock_quantity'] ) ) {
			$product->set_stock_quantity( $args['stock_quantity'] );
		}
		$product->save();

		$enabled = $args['serial_enabled'] ?? true;
		if ( $enabled ) {
			update_post_meta( $product->get_id(), '_is_serial_number', 'yes' );
			update_post_meta( $product->get_id(), '_serial_key_source', 'custom_source' );
		}

		return $product;
	}

	/**
	 * Create an order with a single product line item.
	 *
	 * @param \WC_Product $product  Product to add.
	 * @param int         $quantity Quantity.
	 * @param array       $args     Extra args (email, status, customer_id).
	 * @return \WC_Order
	 */
	protected function create_order( \WC_Product $product, int $quantity = 1, array $args = array() ): \WC_Order {
		$order = new \WC_Order();
		$order->add_product( $product, $quantity );
		$order->set_billing_email( $args['email'] ?? 'buyer@example.com' );
		if ( isset( $args['customer_id'] ) ) {
			$order->set_customer_id( $args['customer_id'] );
		}
		$order->calculate_totals();
		$order->save();

		if ( ! empty( $args['status'] ) ) {
			$order->set_status( $args['status'] );
			$order->save();
		}

		return $order;
	}

	/**
	 * Create a customer.
	 *
	 * @param string $email Email.
	 * @return int Customer ID.
	 */
	protected function create_customer( string $email = 'buyer@example.com' ): int {
		return $this->factory()->user->create(
			array(
				'role'       => 'customer',
				'user_email' => $email,
			)
		);
	}

	/**
	 * Read the raw stored serial_key ciphertext directly from the DB.
	 *
	 * @param int $key_id Key ID.
	 * @return string
	 */
	protected function get_raw_serial_key( int $key_id ): string {
		global $wpdb;
		return (string) $wpdb->get_var(
			$wpdb->prepare( "SELECT serial_key FROM {$wpdb->prefix}serial_numbers WHERE id = %d", $key_id )
		);
	}
}