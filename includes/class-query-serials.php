<?php

namespace pluginever\SerialNumbers;
defined( 'ABSPATH' ) || exit();

/**
 * @since 1.2.0
 * Class Query_Serials
 * @package pluginever\SerialNumbers
 */
class Query_Serials extends Query {
	/**
	 * @var string
	 */
	const TABLE = 'wc_serial_numbers';

	/**
	 * Static constructor.
	 *
	 *
	 * @param string $id
	 *
	 * @return Query
	 * @since 1.0.0
	 */
	public static function init( $id = 'serials' ) {
		$builder     = new self();
		$builder->id = ! empty( $id ) ? $id : uniqid();
		global $wpdb;
		$builder->from = $wpdb->prefix . self::TABLE;

		return $builder;
	}
}
