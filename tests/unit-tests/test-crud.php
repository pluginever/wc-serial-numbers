<?php

class Crud_Tests extends WP_UnitTestCase {
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var StdClass
	 */
	protected $serial_number;

	/**
	 * @var array
	 */
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
		'status'           => 'available',
		'validity'         => '',
		'expire_date'      => '',
		'order_date'       => '',
	];

	public function setUp() {
		parent::setUp();
	}

	public function test_insert() {
		$id = WCSN_Helper_SerialNumber::create_mock_serial_number();
//		$this->serial_number = WC_Serial_Numbers_Manager::get_serial_number($id);
//		$this->assertNotFalse( $this->id );
		echo print_r($id, true );
	}

	public function test_error_check() {
		//test all props
		$this->assertEquals($this->data['serial_key'], wc_serial_numbers()->decrypt($this->serial_number->serial_key));

//		$serial_number_id = WC_Serial_Numbers_Manager::insert_serial_number( $this->data );
//		echo print_r($serial_number_id, true );
//		$serial_number    =  WC_Serial_Numbers_Manager::get_serial_number( $serial_number_id );
//		$this->assertNotFalse( $serial_number_id );
//		error_log(print_r($serial_number, true ));
//		//check if created
//		$this->assertNotFalse( $serial_number_id );
//
//		//check data match
//		$this->assertEquals( $this->data['serial_key'], $serial_number->serial_key );
//		$this->assertEquals( $this->data['product_id'], $serial_number->product_id );
//		$this->assertEquals( $this->data['activation_limit'], $serial_number->activation_limit );
//
//		$data['product_id'] = null;
//		$this->assertWPError(  wc_serial_numbers()->serial_number->insert( $data ) );
//		echo $serial_number->serial_key;
	}


	public function tearDown() {
		parent::tearDown();
	}
}
