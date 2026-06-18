<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Activation;
use WooCommerceSerialNumbers\Models\Key;

/**
 * Tests for the /wcsn/validate REST endpoint and its permission gate.
 */
class RestApiValidateTest extends TestCase {

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
	 * Create a key bound to a completed order in a given status.
	 *
	 * @param string $serial Serial key.
	 * @param string $status Key status.
	 * @return array{0:\WC_Product,1:\WC_Order,2:Key}
	 */
	protected function make_bound_key( string $serial, string $status = 'sold' ): array {
		$product = $this->create_product();
		$order   = $this->create_order( $product, 1, array( 'status' => 'completed' ) );
		$key     = Key::insert(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => $serial,
				'status'     => $status,
				'order_id'   => $order->get_id(),
			)
		);
		$this->assertNotWPError( $key );

		return array( $product, $order, $key );
	}

	/**
	 * An unknown product id is rejected.
	 */
	public function testRejectsInvalidProduct(): void {
		$request  = $this->get_rest_request( '/wcsn/validate', array( 'product_id' => 99999999, 'serial_key' => 'X' ) );
		$response = rest_do_request( $request );

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'invalid_product_id', $response->get_data()['code'] );
	}

	/**
	 * A serial key that does not exist is rejected.
	 */
	public function testRejectsUnknownKey(): void {
		$product  = $this->create_product();
		$request  = $this->get_rest_request( '/wcsn/validate', array( 'product_id' => $product->get_id(), 'serial_key' => 'DOES-NOT-EXIST' ) );
		$response = rest_do_request( $request );

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'invalid_key', $response->get_data()['code'] );
	}

	/**
	 * A key whose order is not completed is rejected.
	 */
	public function testRejectsKeyWithoutCompletedOrder(): void {
		$product = $this->create_product();
		$order   = $this->create_order( $product, 1, array( 'status' => 'processing' ) );
		$key     = Key::insert(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'PROCESSING-1',
				'status'     => 'sold',
				'order_id'   => $order->get_id(),
			)
		);
		$this->assertNotWPError( $key );

		$request  = $this->get_rest_request( '/wcsn/validate', array( 'product_id' => $product->get_id(), 'serial_key' => 'PROCESSING-1' ) );
		$response = rest_do_request( $request );

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'invalid_order', $response->get_data()['code'] );
	}

	/**
	 * A mismatched email is rejected.
	 */
	public function testEmailMismatchRejected(): void {
		// The routes declare an invalid 'email' schema type; pin that known notice.
		$this->setExpectedIncorrectUsage( 'rest_validate_value_from_schema' );
		$this->setExpectedIncorrectUsage( 'rest_sanitize_value_from_schema' );

		list( $product, , ) = $this->make_bound_key( 'EMAIL-1' );

		$request  = $this->get_rest_request(
			'/wcsn/validate',
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'EMAIL-1',
				'email'      => 'wrong@example.com',
			)
		);
		$response = rest_do_request( $request );

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'invalid_email', $response->get_data()['code'] );
	}

	/**
	 * Expired and cancelled keys are rejected with their own codes.
	 */
	public function testExpiredAndCancelledStatusRejected(): void {
		list( $expired_product, , ) = $this->make_bound_key( 'EXP-1', 'expired' );
		$expired                    = rest_do_request( $this->get_rest_request( '/wcsn/validate', array( 'product_id' => $expired_product->get_id(), 'serial_key' => 'EXP-1' ) ) );
		$this->assertSame( 'expired_key', $expired->get_data()['code'] );

		list( $cancelled_product, , ) = $this->make_bound_key( 'CAN-1', 'cancelled' );
		$cancelled                    = rest_do_request( $this->get_rest_request( '/wcsn/validate', array( 'product_id' => $cancelled_product->get_id(), 'serial_key' => 'CAN-1' ) ) );
		$this->assertSame( 'key_cancelled', $cancelled->get_data()['code'] );
	}

	/**
	 * A valid sold key returns the documented response contract.
	 */
	public function testValidSoldKeyReturnsContract(): void {
		list( $product, , ) = $this->make_bound_key( 'VALID-1' );

		$response = rest_do_request( $this->get_rest_request( '/wcsn/validate', array( 'product_id' => $product->get_id(), 'serial_key' => 'VALID-1' ) ) );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 'key_valid', $data['code'] );
		$this->assertSame( 'active', $data['status'] );
		$this->assertArrayHasKey( 'activation_limit', $data );
		$this->assertArrayHasKey( 'activation_count', $data );
		$this->assertArrayHasKey( 'activations_left', $data );
		$this->assertArrayHasKey( 'expire_date', $data );
	}

	/**
	 * Regression #531: the `activations` field must serialize as a list of
	 * associative arrays (the documented contract the Legacy API add-on parses),
	 * not model objects that JSON-encode to empty `{}` and fatal the client.
	 */
	public function testActivationsFieldSerializesAsArrays(): void {
		list( $product, , $key ) = $this->make_bound_key( 'ACT-CONTRACT-1' );

		$activation = Activation::insert(
			array(
				'serial_id' => $key->get_id(),
				'instance'  => 'instance-1',
				'platform'  => 'linux',
			)
		);
		$this->assertNotWPError( $activation );

		$response = rest_do_request( $this->get_rest_request( '/wcsn/validate', array( 'product_id' => $product->get_id(), 'serial_key' => 'ACT-CONTRACT-1' ) ) );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertIsArray( $data['activations'] );
		$this->assertCount( 1, $data['activations'] );
		$this->assertIsArray( $data['activations'][0], 'Each activation must be an array, not a model object.' );
		$this->assertSame( 'instance-1', $data['activations'][0]['instance'] );
		$this->assertSame( 'linux', $data['activations'][0]['platform'] );

		// The whole payload must round-trip through JSON without emptying the activations.
		$decoded = json_decode( wp_json_encode( $data ), true );
		$this->assertSame( 'instance-1', $decoded['activations'][0]['instance'] );
	}
}
