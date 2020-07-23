<?php

/**
 * Class WC_Helper_Customer.
 *
 * This helper class should ONLY be used for unit tests!.
 */
class WCSN_Helper_SerialNumber {

	/**
	 * Create a mock serial number for testing purposes.
	 *
	 * @return int
	 */
	public static function create_mock_serial_number() {

		$product = WC_Helper_Product::create_simple_product( true );
		$data    = array(
			'id'               => 0,
			'serial_key'       => 'ES-004522-20-X009',
			'serial_image'     => '',
			'product_id'       => $product->get_id(),
			'activation_limit' => '1',
			'order_id'         => '',
			'customer_id'      => '',
			'vendor_id'        => '',
			'activation_email' => '',
			'status'           => 'available',
			'validity'         => '',
			'expire_date'      => '',
			'order_date'       => '',
		);

		return WC_Serial_Numbers_Manager::insert_serial_number( $data );
	}
}
