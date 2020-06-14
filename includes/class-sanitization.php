<?php

namespace pluginever\SerialNumbers;
defined( 'ABSPATH' ) || exit();

class Sanitization {

	/**
	 * @param $key
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public static function sanitize_key( $key ) {
		return sanitize_textarea_field( $key );
	}

	/**
	 * Sanitize date input
	 *
	 * @param $date
	 *
	 * @return false|string
	 * @since 1.2.0
	 */
	public static function sanitize_date( $date ) {
		if (  empty( $date ) || '0000-00-00 00:00:00' == $date ) {
			return '';
		}

		return date( 'Y-m-d', strtotime( $date ) );
	}

	/**
	 * Sanitize serial status.
	 *
	 * @param $status
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public static function sanitize_status( $status ) {
		$statuses = wc_serial_numbers_get_serial_statuses();
		if ( array_key_exists( $status, $statuses ) ) {
			return $status;
		}

		return '';
	}

	/**
	 * Sanitize serial number fields before inserting
	 *
	 * @param array $args
	 *
	 * @return array|\WP_Error
	 * @since 1.2.0
	 */
	public static function sanitize_serial_args( $args ) {
		$order = false;

		if ( empty( $args['product_id'] ) ) {
			return new \WP_Error( 'empty_content', __( 'You must select a product to add serial number.', 'wc-serial-numbers' ) );
		}

		if ( empty( $args['serial_key'] ) ) {
			return new \WP_Error( 'empty_content', __( 'The Serial Key is empty. Please enter a serial key and try again', 'wc-serial-numbers' ) );
		}

		//updating ordered item
		if ( ! empty( $args['order_id'] ) ) {
			$order = wc_get_order( $args['order_id'] );
			if ( empty( $order ) ) {
				return new \WP_Error( 'invalid_order_id', __( 'Associated order is not valid.', 'wp-serial-numbers' ) );
			}
		}

		//set status if not exist
		if ( empty( $args['status'] ) ) {
			$args['status'] = 'available';
		}

		if ( $args['status'] == 'available' && ! empty( $order ) ) {
			return new \WP_Error( 'invalid_status', __( 'Item with available status could not be assigned with order.', 'wp-serial-numbers' ) );
		}

		if ( $args['status'] == 'sold' && empty( $order ) ) {
			return new \WP_Error( 'invalid_status', __( 'Sold item must have a associated valid order.', 'wp-serial-numbers' ) );
		}

		if ( $order && $args['status'] == 'sold' ) {
			$items         = $order->get_items();
			$valid_product = false;
			foreach ( $items as $item_id => $item ) {
				$product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
				if ( $product_id === intval( $args['product_id'] ) ) {
					$valid_product = true;
					break;
				}
			}

			if ( ! $valid_product ) {
				return new \WP_Error( 'invalid_status', __( 'Order does not contains the product.', 'wp-serial-numbers' ) );
			}
		}

		if ( $order && ( empty( $args['order_date'] ) || $args['order_date'] == '0000-00-00 00:00:00' ) && $order->get_date_completed() ) {
			$args['order_date'] = $order->get_date_completed()->format( 'Y-m-d H:i:s' );
		}

		if ( $order && ( empty( $args['order_date'] ) || $args['order_date'] == '0000-00-00 00:00:00' ) && ! $order->get_date_completed() ) {
			$args['order_date'] = current_time( 'mysql' );
		}

		if ( empty( $order ) ) {
			$args['order_id']   = null;
			$args['order_date'] = '0000-00-00 00:00:00';
		}

		$args['activation_limit'] = intval( $args['activation_limit'] );
		$args['validity']         = intval( $args['validity'] );
		$args['product_id']       = intval( $args['product_id'] );
		$args['order_id']         = intval( $args['order_id'] );


		return $args;
	}
}
