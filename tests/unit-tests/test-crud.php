<?php

class Crud_Tests extends WP_UnitTestCase {
	protected $data = [
		'id'               => 0,
		'serial_key'       => 'ES-004522-20-X009',
		'serial_image'     => '',
		'product_id'       => 10,
		'activation_limit' => '1',
		'order_id'         => '',
		'customer_id'      => '',
		'vendor_id'        => '',
		'activation_email' => '',
		'status'           => '',
		'validity'         => '',
		'expire_date'      => '',
		'order_date'       => '',
	];

	public function setUp() {
		parent::setUp();
	}

	public function test_insert() {
		$this->assertNotFalse( WCSN_Helper_SerialNumber::create_mock_serial_number() );
	}

	public function test_error_check() {
		$serial_number_id = WCSN_Serial_Number::insert( $this->data );
		$serial_number    = WCSN_Serial_Number::get( $serial_number_id );

		//check if created
		$this->assertNotFalse( $serial_number_id );

		//check data match
		$this->assertEquals( $this->data['serial_key'], $serial_number->serial_key );
		$this->assertEquals( $this->data['product_id'], $serial_number->product_id );
		$this->assertEquals( $this->data['activation_limit'], $serial_number->activation_limit );

		$data['product_id'] = null;
		$this->assertWPError( WCSN_Serial_Number::insert( $data ) );
		echo $serial_number->serial_key;
	}


	public function tearDown() {
		parent::tearDown();
	}
}
