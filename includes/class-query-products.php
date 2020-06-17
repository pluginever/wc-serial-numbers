<?php

namespace PluginEver\SerialNumbers;
defined( 'ABSPATH' ) || exit();

class Query_Products extends Query {
	/**
	 * @var string
	 */
	const TABLE = 'posts';

	/**
	 * Static constructor.
	 *
	 *
	 * @param string $id
	 *
	 * @return Query
	 * @since 1.0.0
	 */
	public static function init( $id = 'products' ) {
		$builder     = new self();
		$builder->id = ! empty( $id ) ? $id : uniqid();
		global $wpdb;
		$builder->from = $wpdb->prefix . self::TABLE;
		$builder->where( 'post_status', 'publish' );
		$types = apply_filters( 'wc_serial_numbers_product_types', array( 'product'));
		$builder->whereRaw( 'post_type IN ("' . implode( '","', $types ) . '")' );
		$builder->whereRaw( "ID NOT IN  (SELECT DISTINCT post_parent FROM {$wpdb->posts} WHERE post_type='product_variation') ");

		return $builder;
	}
}
