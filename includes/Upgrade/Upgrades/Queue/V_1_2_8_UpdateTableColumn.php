<?php

namespace WCSerialNumbers\Upgrade\Upgrades\Queue;

use WC_Order;
use WC_Serial_Numbers_Query;
use WCSerialNumbers\Interfaces\QueueHandler;

class V_1_2_8_UpdateTableColumn implements QueueHandler {

	/**
	 * The executable function that runs on queue
	 *
	 * @since 1.2.8
	 *
	 * @param array $args
	 *
	 * @return array|bool
	 */
	public static function run( $args ) {
		$limit = 2;
		$serial_numbers = WC_Serial_Numbers_Query::init()->table( 'serial_numbers' )->page( $args['page'],  $limit)->get();

		if ( empty( $serial_numbers ) ) {
			return false;
		}

		foreach ( $serial_numbers as $serial_number ) {
			if ( $serial_number->order_item_id ) {
				continue;
			}

			$order_item_id = null;

			$order = wc_get_order( $serial_number->order_id );

			if ( ! $order instanceof WC_Order ) {
				continue;
			}

			$line_items = $order->get_items( 'line_item' );

			foreach ( $line_items as $line_item ) {
				if ( $line_item->get_product_id() === absint( $serial_number->product_id ) ) {
					$order_item_id = $line_item->get_id();
					break;
				}
			}

			if ( $order_item_id ) {
				WC_Serial_Numbers_Query::init()->table( 'serial_numbers' )
					->where( 'id', $serial_number->id )
					->update( [ 'order_item_id' => $order_item_id ] );
			}
		}

		++$args['page'];

		return $args;
	}
}
