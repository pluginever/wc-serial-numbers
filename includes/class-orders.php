<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Order.
 *
 * Handles order related actions.
 *
 * @since  1.0.0
 * @return Orders
 */
class Orders {

	/**
	 * Orders constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'validate_checkout' ) );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'maybe_update_order' ) );
	}

	/**
	 * If selling from stock then check if there is enough
	 * serial numbers available otherwise disable checkout
	 *
	 * since 1.2.0
	 * @return bool
	 */
	public static function validate_checkout() {
		$cart_items = WC()->cart->get_cart_contents();
		foreach ( $cart_items as $id => $cart_item ) {
			/** @var \WC_Product $wc_product */
			$wc_product = $cart_item['data'];
			$product_id = $wc_product->get_id();
			$product    = Product::get( $product_id );
			if ( ! $product->is_selling_serial_numbers() ) {
				continue;
			}

			if ( 'pre_generated' === $product->get_key_source() ) {
				$stock        = (int) $product->get_key_stock_count();
				$delivery_qty = $product->get_delivery_quantity( $cart_item['quantity'] );
				if ( ! $product->is_on_backorder() && $stock < $delivery_qty ) {
					$message = sprintf( __( 'Sorry, There is not enough serial numbers available for %s, Please remove this item or lower the quantity, For now we have %s Serial Number for this product.', 'wc-serial-numbers' ), '{product_title}', '{stock_quantity}' );
					$notice  = apply_filters( 'wc_serial_numbers_low_stock_notice', $message );
					$notice  = str_replace( array( '{product_title}', '{stock_quantity}' ), array( $product->get_title(), $stock ), $notice );
					wc_add_notice( $notice, 'error' );

					return false;
				}
			}
		}
	}

	/**
	 * Based on status change update the keys in the database.
	 *
	 * When the order status is on hold then set the key status as sold.
	 * When the order status is processing then set the key status as sold.
	 * When the order status is completed then set the key status as delivered.
	 * When the order status is anything other than on hold, pressing or completed then set the key status as available.
	 *
	 * Case "Reuse" and source is pre-generated.
	 * Completed to any status set the key status as cancelled.
	 * Sold to any status set the key status as available.
	 *
	 *
	 * @param int $order_id The order id.
	 *
	 * @since 1.2.0
	 */
	public static function maybe_update_order( $order_id ) {
		$order           = Helper::get_order_object( $order_id );
		$customer_id     = Helper::get_customer_id( $order );
		$line_items      = Helper::get_order_line_items( $order );
		$revoke_statuses = Helper::get_revoke_statues();
		$is_reuse        = 'yes' === get_option( 'wc_serial_numbers_reuse', 'yes' );
		$order_status    = $order->get_status( 'edit' );
		if ( empty( $line_items ) ) {
			self::remove_order_items( $order_id );

			return false;
		}

		// Connect any missing keys.
		foreach ( $line_items as $k => $item ) {
			if ( in_array( $order_status, [ 'processing', 'completed', 'on-hold' ], true ) ) {
				$product       = Product::get( $item['product_id'] );
				$order_item_id = $item->get_id();
				$key_source    = ! array_key_exists( $product->get_key_source(), Helper::get_key_sources() ) ? __( 'Unknown', 'wc-serial-numbers' ) : $product->get_key_source();
				$item_qty      = ! empty( $item->get_quantity() ) ? $item->get_quantity() : 0;
				$refunded      = $order->get_qty_refunded_for_item( $item->get_id() );
				if ( $refunded >= $item_qty ) {
					continue;
				}

				$delivered_qty = Keys::query( [
					'product_id__in'    => absint( $product->get_id() ),
					'order_id__in'      => $order->get_id(),
					'order_item_id__in' => absint( $order_item_id ),
					'per_page'          => - 1,
				], true );

				$pending_qty = $item_qty - $delivered_qty;
				if ( $delivered_qty >= $item_qty || empty( $pending_qty ) ) {
					continue;
				}

				$new_keys = apply_filters(
					'wc_serial_numbers_order_item_keys',
					array(),
					$item,
					$key_source,
					$pending_qty,
					$order_id
				);


				if ( count( $new_keys ) < $pending_qty ) {
					$order->add_order_note(
						sprintf(
						/* translators: 1: product title 2: source and 3: Quantity */
							esc_html__( 'There is not enough serial numbers for the product %1$s from selected source %2$s, needed total %3$d.', 'wc-serial-numbers' ),
							Helper::get_product_title( $item['product_id'] ),
							$key_source,
							$pending_qty
						),
						false
					);

					continue;
				}

				foreach ( $new_keys as $key ) {
					$key->set_props(
						[
							'order_id'      => $order->get_id(),
							'order_item_id' => $order_item_id,
							'status'        => 'completed' === $order_status ? 'delivered' : 'sold',
							'customer_id'   => $customer_id,
							'date_ordered'  => current_time( 'mysql' ),
						]
					);

					$key->save();
				}

				return true;
			}
		}


		// update status.
		$delivered_keys = Keys::query(
			[
				'order_id__in' => $order->get_id(),
				'per_page'     => - 1,
			],
		);

		foreach ( $delivered_keys as $delivered_key ) {
			$validity_expire_date = strtotime( "+{$delivered_key->valid_for}} day", strtotime( $delivered_key->date_order ) );
			$is_expired           = ! empty( $delivered_key->valid_for ) && strtotime( $validity_expire_date ) > strtotime( $delivered_key->date_order );
			$key_source           = get_post_meta( $delivered_key->product_id, '_serial_numbers_key_source', true );

			if ( $is_expired ) {
				$delivered_key->set_status( 'expired' );
				$delivered_key->save();
			} elseif ( 'completed' === $order_status ) {
				$delivered_key->set_status( 'delivered' );
				$delivered_key->save();
			} elseif ( in_array( $order_status, [ 'processing', 'on-hold' ] ) ) {
				$delivered_key->set_status( 'sold' );
				$delivered_key->save();
			}else {
//				if( $is_reuse )

//				$is_reuse && 'pre_generated' === $key_source
//				if ( 'completed' === $delivered_key->status ) {
//					$delivered_key->set_status( 'cancelled' );
//					$delivered_key->save();
//				} else {
//					$delivered_key->set_status_available();
//				}
//				$delivered_key->delete();
			}
		}

		return false;
	}

	/**
	 * @param $order_id
	 */
	public static function remove_order_items( $order_id ) {
		$keys = Keys::query( [
			'order_id__in' => $order_id,
			'per_page'     => - 1,
		] );

		foreach ( $keys as $key ) {
			$key->delete();
		}
	}
}
