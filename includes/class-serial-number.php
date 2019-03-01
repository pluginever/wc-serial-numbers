<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Serial_Numbers_Serial_Number extends WC_Serial_Numbers_Crud {
	/**
	 * The name of the date column.
	 *
	 * @since  1.0.0
	 * @var string
	 */
	public $date_key = 'created';

	/**
	 * Get table name
	 *
	 * since 1.0.0
	 *
	 * @return string
	 */
	public function get_table_name() {
		global $wpdb;

		return $this->table_name = $wpdb->prefix . 'wcsn_serial_numbers';
	}

	/**
	 * get primary key
	 *
	 * since 1.0.0
	 *
	 * @return string
	 */
	public function get_primary_key() {
		return 'id';
	}

	/**
	 * Get columns and formats
	 *
	 * @since   1.0.0
	 */
	public function get_columns() {
		return array(
			'serial_key'       => '%s',
			'serial_image'     => '%s',
			'product_id'       => '%d',
			'activation_limit' => '%d',
			'order_id'         => '%d',
			'activation_email' => '%s',
			'status'           => '%s',
			'validity'         => '%d',
			'expire_date'      => '%s',
			'order_date'       => '%s',
			'created'          => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @since   1.0.0
	 */
	public function get_column_defaults() {
		return array(
			'serial_key'       => '',
			'serial_image'     => '',
			'product_id'       => '',
			'activation_limit' => '1',
			'order_id'         => '',
			'activation_email' => '',
			'status'           => 'new',
			'validity'         => '365',
			'expire_date'      => '0000-00-00 00:00:00',
			'order_date'       => '0000-00-00 00:00:00',
			'created'          => date( 'Y-m-d H:i:s' ),
		);
	}
}
