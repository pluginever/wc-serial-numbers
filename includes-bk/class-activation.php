<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Serial_Numbers_Activation extends WC_Serial_Numbers_Crud {
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

		return $this->table_name = $wpdb->prefix . 'wcsn_activations';
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
			'id'                  => '%d',
			'serial_id'              => '%d',
			'instance'            => '%s',
			'time'     => '%s',
			'active'   => '%d',
			'platform' => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @since   1.0.0
	 */
	public function get_column_defaults() {
		return array(
			'serial_id'              => '',
			'instance'            => '',
			'time'     => date( 'Y-m-d H:i:s' ),
			'active'   => '',
			'platform' => '',
		);
	}
}
