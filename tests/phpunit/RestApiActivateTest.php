<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;

/**
 * Tests for the /wcsn/activate and /wcsn/deactivate REST endpoints.
 */
class RestApiActivateTest extends TestCase {

	/**
	 * Spin up a fresh REST server so the wcsn routes are registered.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		global $wp_rest_server;
		$wp_rest_server = new \WP_REST_Server();
		do_action( 'rest_api_init' );
	}

	/**
	 * Create an activatable sold key on a completed order.
	 *
	 * @param string $serial Serial key.
	 * @param int    $limit  Activation limit.
	 * @return \WC_Product
	 */
	protected function make_activatable( string $serial, int $limit ): \WC_Product {
		$product = $this->create_product();
		$order   = $this->create_order( $product, 1, array( 'status' => 'completed' ) );
		$key     = Key::insert(
			array(
				'product_id'       => $product->get_id(),
				'serial_key'       => $serial,
				'status'           => 'sold',
				'order_id'         => $order->get_id(),
				'activation_limit' => $limit,
			)
		);
		$this->assertNotWPError( $key );

		return $product;
	}

	/**
	 * Activating creates an activation and decrements the remaining count.
	 */
	public function testActivateCreatesActivationAndDecrements(): void {
		$product  = $this->make_activatable( 'ACT-1', 2 );
		$response = rest_do_request(
			$this->get_rest_request( '/wcsn/activate', array( 'product_id' => (string) $product->get_id(), 'serial_key' => 'ACT-1', 'instance' => 'inst-1' ) )
		);
		$data = $response->get_data();

		$this->assertSame( 'key_activated', $data['code'] );
		$this->assertSame( 1, $data['activation_count'] );
		$this->assertSame( 1, $data['activations_left'] );
	}

	/**
	 * An activation with no instance gets a server-generated one.
	 */
	public function testActivateGeneratesInstanceWhenMissing(): void {
		$product  = $this->make_activatable( 'ACT-2', 1 );
		$response = rest_do_request(
			$this->get_rest_request( '/wcsn/activate', array( 'product_id' => (string) $product->get_id(), 'serial_key' => 'ACT-2' ) )
		);

		$this->assertNotEmpty( $response->get_data()['instance'] );
	}

	/**
	 * Activating the same instance twice is rejected.
	 */
	public function testDuplicateInstanceRejected(): void {
		$product = $this->make_activatable( 'ACT-3', 5 );
		rest_do_request( $this->get_rest_request( '/wcsn/activate', array( 'product_id' => (string) $product->get_id(), 'serial_key' => 'ACT-3', 'instance' => 'dup' ) ) );

		$response = rest_do_request(
			$this->get_rest_request( '/wcsn/activate', array( 'product_id' => (string) $product->get_id(), 'serial_key' => 'ACT-3', 'instance' => 'dup' ) )
		);

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'instance_already_activated', $response->get_data()['code'] );
	}

	/**
	 * Activating past the limit is rejected.
	 */
	public function testActivationLimitReached(): void {
		$product = $this->make_activatable( 'ACT-4', 1 );
		rest_do_request( $this->get_rest_request( '/wcsn/activate', array( 'product_id' => (string) $product->get_id(), 'serial_key' => 'ACT-4', 'instance' => 'one' ) ) );

		$response = rest_do_request(
			$this->get_rest_request( '/wcsn/activate', array( 'product_id' => (string) $product->get_id(), 'serial_key' => 'ACT-4', 'instance' => 'two' ) )
		);

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'no_activations_left', $response->get_data()['code'] );
	}

	/**
	 * Deactivating removes the activation and restores the remaining count.
	 */
	public function testDeactivateRemovesActivation(): void {
		$product = $this->make_activatable( 'ACT-5', 2 );
		rest_do_request( $this->get_rest_request( '/wcsn/activate', array( 'product_id' => (string) $product->get_id(), 'serial_key' => 'ACT-5', 'instance' => 'gone' ) ) );

		$response = rest_do_request(
			$this->get_rest_request( '/wcsn/deactivate', array( 'product_id' => (string) $product->get_id(), 'serial_key' => 'ACT-5', 'instance' => 'gone' ) )
		);
		$data = $response->get_data();

		$this->assertSame( 'key_deactivated', $data['code'] );
		$this->assertSame( 2, $data['activations_left'] );
	}

	/**
	 * Deactivating without an instance is rejected.
	 */
	public function testDeactivateRequiresInstance(): void {
		$product  = $this->make_activatable( 'ACT-6', 2 );
		$response = rest_do_request(
			$this->get_rest_request( '/wcsn/deactivate', array( 'product_id' => (string) $product->get_id(), 'serial_key' => 'ACT-6' ) )
		);

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'missing_instance', $response->get_data()['code'] );
	}
}
