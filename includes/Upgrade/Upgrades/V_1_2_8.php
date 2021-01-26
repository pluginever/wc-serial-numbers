<?php

namespace WCSerialNumbers\Upgrade\Upgrades;

use WCSerialNumbers\Upgrade\AbstractUpgrader;

class V_1_2_8 extends AbstractUpgrader {

	/**
	 * Add column in serial_numbers table
	 *
	 * @since 1.2.8
	 *
	 * @return void
	 */
	public static function add_serial_numbers_table_column() {
		// add order_item_id column
	}
}
