<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Serial_Numbers_TMP_Serial_Number extends WC_Serial_Numbers_Serial_Number {
    /**
	 * Get table name
	 *
	 * since 1.0.0
	 *
	 * @return string
	 */
	public function get_table_name() {
		global $wpdb;

		return $this->table_name = $wpdb->prefix . 'wcsn_tmp_serial_numbers';
	}
}