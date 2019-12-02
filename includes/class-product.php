<?php

namespace Pluginever\SerialNumbers;

defined( 'ABSPATH' ) || exit();

class Product {

	/**
	 * since 1.0.0
	 *
	 * @param $product_id
	 *
	 * @return bool
	 */
	public static function enabled( $product_id ) {
		return 'yes' == get_post_meta( $product_id, '_is_serial_number', true );
	}

	/**
	 * since 1.0.0
	 *
	 * @param $args
	 * @param bool $count
	 *
	 * @return array|object|string|null
	 */
	public static function query( $args, $count = false ) {

	}
}
