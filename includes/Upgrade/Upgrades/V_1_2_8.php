<?php

namespace WCSerialNumbers\Upgrade\Upgrades;

use WCSerialNumbers\Upgrade\AbstractUpgrader;
use WCSerialNumbers\Upgrade\Upgrades\Queue\V_1_2_8_UpdateTableColumn;

class V_1_2_8 extends AbstractUpgrader {

	/**
	 * Add order_item_id column in serial_numbers table
	 *
	 * @since 1.2.8
	 *
	 * @return void
	 */
	public static function add_column() {
		global $wpdb;

		$map_table = $wpdb->prefix . 'serial_numbers';

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES like %s', $map_table ) ) !== $map_table ) {
			return;
		}

		$columns = $wpdb->get_results( "DESCRIBE {$map_table}" );

		$columns = array_filter( $columns, function ( $column ) {
			return 'order_item_id' === $column->Field;
		} );

		if ( empty( $columns ) ) {
			$wpdb->query(
			    "ALTER TABLE {$map_table} ADD COLUMN order_item_id bigint(20) DEFAULT NULL"
			);
		}
	}

	public static function update_order_item_id_column() {
		$args = [
			'page' => 1,
		];

		wc_serial_numbers()->upgrades->add_to_queue( V_1_2_8_UpdateTableColumn::class, $args );
	}
}
