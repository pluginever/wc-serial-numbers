<?php

namespace pluginever\SerialNumbers;
defined( 'ABSPATH' ) || exit();

class Helper {
	public static function add_notice( $message, $type = 'success', $save = true ) {

	}

	public static function encrypt( $key ) {
		return Encryption::encrypt( $key );
	}

	public static function decrypt( $key ) {
		return Encryption::decrypt( $key );
	}

	/**
	 * Get serial number status.
	 *
	 * @param $item
	 * @param string $context
	 *
	 * @return mixed|null
	 * @since 1.2.0
	 */
	public static function get_serial_status( $item, $context = 'edit' ) {
		if ( empty( $item ) ) {
			return null;
		}

		if ( is_numeric( $item ) ) {
			$item = Query_Serials::init()->find( $item );
		}

		$status  = null;
		$statues = wc_serial_numbers_get_serial_statuses();

		if ( ! empty( $item ) && array_key_exists( $item->status, $statues ) ) {
			$status = 'edit' === $context ? $item->status : $statues[ $item->status ];
		}

		return $status;
	}

	/**
	 * Check if product support serial.
	 *
	 * @param $product_id
	 *
	 * @return bool
	 * @since 1.2.0
	 */
	public static function product_is_selling_serial( $product_id ) {
		return 'yes' == get_post_meta( $product_id, '_is_serial_number', true );
	}

	/**
	 * Get product title.
	 *
	 * @param $product
	 *
	 * @return int
	 * @since 1.2.0
	 */
	public static function get_product_id( $product ) {
		if ( empty( $product ) ) {
			return 0;
		}

		return empty( $product->get_variation_id() ) ? $product->get_product_id() : $product->get_variation_id();
	}

	/**
	 * Get formatted product title.
	 *
	 * @param $product
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public static function get_product_title( $product ) {
		if ( ! empty( $product ) ) {
			$product = wc_get_product( $product );
		}
		if ( $product && ! empty( $product->get_id() ) ) {
			return sprintf(
				'(#%1$s) %2$s',
				$product->get_id(),
				html_entity_decode( $product->get_formatted_name() )
			);
		}

		return '';
	}

	/**
	 * @since 1.2.0
	 * @param $serial
	 *
	 * @return false|string|void
	 */
	public static function get_expiration_date( $serial ) {
		if ( empty( $serial->validity ) ) {
			return __( 'Never Expire', 'wc-serial-numbers' );
		}

		return date( 'Y-m-d', strtotime( $serial->order_date . ' + ' . $serial->validity . ' Day ' ) );
	}

	/**
	 * Get activation limit.
	 *
	 * @since 1.1.6
	 * @param $serial
	 *
	 * @return string|void
	 */
	public static function  get_activation_limit($serial){
		if ( empty( $serial->activation_limit ) ) {
			return __( 'Unlimited', 'wc-serial-numbers' );
		}

		return $serial->activation_limit;
	}

}
