<?php

use WooCommerceSerialNumbers\Key;

class KeyCrudTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * @var \WpunitTester
	 */
	protected $tester;

	public function setUp(): void {
		parent::setUp();
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function test_filters() {
		add_filter( 'wc_serial_numbers_key_statuses', function ( $statuses ) {
			$statuses['manual'] = __( 'Manual', 'wc-serial-numbers' );

			return $statuses;
		} );
		$statuses = Key::get_statuses();
		$this->assertArrayHasKey( 'manual', $statuses );
	}

	public function test_create() {
		$product_id = $this->tester->createProduct();
		$this->assertIsInt( $product_id );

		$key = new Key();
		$key->set_props( [
			'serial_key'       => '1234567890',
			'product_id'       => $product_id,
			'activation_limit' => 1,
			'activation_count' => 10,
			'validity'         => 20,
			'created_date'     => '2020-01-01 00:00:00',
		] );

		$this->assertNotWPError( $key->save(), 'Key save failed' );
		$this->assertIsInt( $key->get_id(), 'Key ID is not an integer' );
		$this->assertEquals( '1234567890', $key->get_serial_key(), 'Serial key does not match' );
		$this->assertEquals( $product_id, $key->get_product_id(), 'Product ID does not match' );
		$this->assertEquals( 1, $key->get_activation_limit(), 'Activation limit does not match' );
		$this->assertEquals( 10, $key->get_activation_count(), 'Activation count does not match' );
		$this->assertEquals( 20, $key->get_validity(), 'Validity does not match' );
		$this->assertEquals( '2020-01-01 00:00:00', $key->get_created_date(), 'Created date does not match' );
	}

	public function test_read() {
		$product_id = $this->tester->createProduct();
		$this->assertIsInt( $product_id );

		$key = new Key();
		$key->set_props( [
			'serial_key'       => '1234567890',
			'product_id'       => $product_id,
			'activation_limit' => 1,
			'activation_count' => 10,
			'validity'         => 20,
			'created_date'     => '2020-01-01 00:00:00',
		] );

		$this->assertNotWPError( $key->save(), 'Key save failed' );
		$this->assertIsInt( $key->get_id(), 'Key ID is not an integer' );

		$key = new Key( $key->get_id() );
		$this->assertEquals( '1234567890', $key->get_serial_key(), 'Serial key does not match' );
		$this->assertEquals( $product_id, $key->get_product_id(), 'Product ID does not match' );
		$this->assertEquals( 1, $key->get_activation_limit(), 'Activation limit does not match' );
		$this->assertEquals( 10, $key->get_activation_count(), 'Activation count does not match' );
		$this->assertEquals( 20, $key->get_validity(), 'Validity does not match' );
		$this->assertEquals( '2020-01-01 00:00:00', $key->get_created_date(), 'Created date does not match' );
	}

	public function test_update() {
		$product_id = $this->tester->createProduct();
		$this->assertIsInt( $product_id );

		$key = new Key();
		$key->set_props( [
			'serial_key'       => '1234567890',
			'product_id'       => $product_id,
			'activation_limit' => 1,
			'activation_count' => 10,
			'validity'         => 20,
			'created_date'     => '2020-01-01 00:00:00',
		] );

		$this->assertNotWPError( $key->save(), 'Key save failed' );
		$this->assertIsInt( $key->get_id(), 'Key ID is not an integer' );

		$key = new Key( $key->get_id() );
		$key->set_serial_key( '0987654321' );
		$key->set_product_id( $product_id );
		$key->set_activation_limit( 2 );
		$key->set_activation_count( 20 );
		$key->set_validity( 30 );
		$key->set_created_date( '2020-01-02 00:00:00' );
		$this->assertNotWPError( $key->save(), 'Key save failed' );

		$key = new Key( $key->get_id() );
		$this->assertEquals( '0987654321', $key->get_serial_key(), 'Serial key does not match' );
		$this->assertEquals( $product_id, $key->get_product_id(), 'Product ID does not match' );
		$this->assertEquals( 2, $key->get_activation_limit(), 'Activation limit does not match' );
		$this->assertEquals( 20, $key->get_activation_count(), 'Activation count does not match' );
		$this->assertEquals( 30, $key->get_validity(), 'Validity does not match' );
		$this->assertEquals( '2020-01-02 00:00:00', $key->get_created_date(), 'Created date does not match' );
	}

	public function test_delete() {
		$product_id = $this->tester->createProduct();
		$this->assertIsInt( $product_id );

		$key = new Key();
		$key->set_props( [
			'serial_key'       => '1234567890',
			'product_id'       => $product_id,
			'activation_limit' => 1,
			'activation_count' => 10,
			'validity'         => 20,
			'created_date'     => '2020-01-01 00:00:00',
		] );

		$this->assertNotWPError( $key->save(), 'Key save failed' );
		$this->assertIsInt( $key->get_id(), 'Key ID is not an integer' );

		$key = new Key( $key->get_id() );
		$this->assertNotFalse( $key->delete(), 'Key delete failed' );

		$key = new Key( $key->get_id() );
		$this->assertFalse( $key->delete(), 'Key delete failed' );
	}


	public function test_required_fields() {
		$key = new Key();
		$this->assertWPError( $key->save(), 'Key save failed' );

		$key->set_serial_key( '1234567890' );
		$this->assertWPError( $key->save(), 'Key save failed' );

		$key->set_product_id( 1 );
		$this->assertNotWPError( $key->save(), 'Key save failed' );

		$key->delete();
		$key = new Key();
		$key->set_props( [
			'serial_key'       => '1234567890',
			'product_id'       => 1,
			'status'           => 'invalid',
			'activation_limit' => 1,
			'activation_count' => 10,
			'validity'         => 20,
			'created_date'     => '2020-01-01 00:00:00',
		] );

		$key->set_status( 'sold' );
		$this->assertWPError( $key->save(), 'Status sold saved without order ID' );
	}

	public function test_query() {
		$this->tester->createSerialNumber( [
			'serial_key' => '1234567890',
			'product_id' => 1,
			'status'     => 'active',
		] );
		$key = Key::get( '1234567890', 'serial_key' );
		$this->assertInstanceOf( Key::class, $key, 'Key not found' );
	}
}
