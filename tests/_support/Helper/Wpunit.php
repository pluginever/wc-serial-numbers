<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use WooCommerceSerialNumbers\Key;

class Wpunit extends \Codeception\Module {

	public function createProduct( $args = array() ) {
		// random string
		$defaults = array(
			'post_title'   => 'Dummy Product ' . mt_rand(),
			'post_content' => 'Test Product Content',
			'post_status'  => 'publish',
			'post_type'    => 'product',
			'meta_input'   => array(
				'_regular_price' => '10.00',
				'_price'         => '10.00',
			),
		);

		$args = wp_parse_args( $args, $defaults );

		$post_id = wp_insert_post( $args );
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			return $post_id;
		}

		return false;
	}

	public function createKey( $args = array() ) {
		$defaults = array(
			'serial_key'       => '1234567890',
			'product_id'       => $this->createProduct(),
			'status'           => 'available',
			'activation_limit' => 1,
			'activation_count' => 10,
			'validity'         => 20,
			'created_date'     => '2020-01-01 00:00:00',
		);

		$args = wp_parse_args( $args, $defaults );

		return Key::insert( $args );
	}
}
