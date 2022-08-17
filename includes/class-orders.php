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
		add_action( 'woocommerce_order_status_processing', array( $this, 'maybe_autocomplete_order' ) );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'maybe_update_order' ) );

//		add_action( 'woocommerce_delete_order_items', array( $this, 'delete_order' ) );
//		add_action( 'woocommerce_delete_order', array( $this, 'delete_order' ) );
//		add_action( 'woocommerce_trash_order', array( $this, 'delete_order' ) );
//		add_action( 'woocommerce_before_delete_order_item', array( $this, 'delete_order_item' ) );

//		add_action( 'wp_trash_post', array( $this, 'trash_post' ) );
//		add_action( 'untrashed_post', array( $this, 'untrashed_post' ) );
//		add_action( 'edit_post', array( $this, 'edit_post' ), 10, 2 );

		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'order_print_items' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'order_print_items' ), - 1 );
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
	 * If order contains serial numbers autocomplete order.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public static function maybe_autocomplete_order() {
		if ( 'yes' !== get_option( 'wc_serial_numbers_autocomplete_order', 'no' ) ) {
			return;
		}
	}

	/**
	 * Based on status change update the keys in the database.
	 *
	 * When the order status is on hold then set the key status as sold.
	 * When the order status is processing then set the key status as sold.
	 * When the order status is completed then set the key status as delivered.
	 * When the order status is anything other than on hold, pressing or completed then remove the key.
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
			self::remove_ordered_keys( $order_id );

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
			} else if ( 'pre_generated' === $key_source ) {
				if ( $delivered_key->status !== 'delivered' && $is_reuse ) {
					$delivered_key->set_props( [
						'order_id'         => null,
						'customer_id'      => null,
						'order_item_id'    => null,
						'activation_count' => 0,
						'status'           => 'available',
						'date_ordered'     => '',
					] );
				} else {
					$delivered_key->set_props( [
						'status' => 'revoked',
					] );
				}
				$delivered_key->save();
			} else {
				$delivered_key->delete();
			}
		}

		return false;
	}

	/**
	 * Remove keys from order.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public static function remove_ordered_keys( $order_id ) {
		$keys = Keys::query( [
			'order_id__in' => $order_id,
			'per_page'     => - 1,
		] );

		foreach ( $keys as $key ) {
			$key->delete();
		}
	}


	/**
	 * Remove keys for specific product.
	 *
	 * @param int $product_id Product ID.
	 * @param int $order_id Order ID.
	 *
	 * @since 3.0.1
	 *
	 */
	public static function remove_ordered_product_keys( $product_id, $order_id ) {
		$keys = Keys::query( [
			'product_id__in' => $product_id,
			'order_id__in'   => $order_id,
			'per_page'       => - 1,
		] );
		foreach ( $keys as $key ) {
			$key->delete();
		}
	}

	/**
	 * @param int $order_id Order Id.
	 */
	public static function order_print_items( $order_id ) {
		$order = Helper::get_order_object( $order_id );
		if ( 'completed' !== $order->get_status( 'edit' ) ) {
			return;
		}
		$line_items = Helper::get_ordered_keys( $order->get_id() );
		if ( empty( $line_items ) ) {
			return;
		}
		$table_columns        = [
			'product_name'     => esc_html__( 'Product', 'wc-serial-numbers' ),
			'serial_numbers'   => esc_html__( 'Serial Numbers', 'wc-serial-numbers' ),
		];
		$keys                 = Helper::get_ordered_keys( $order->get_id() );
		$columns              = apply_filters( 'wc_serial_numbers_order_table_columns', $table_columns, $order_id, $keys );
		$title                = apply_filters( 'wc_serial_numbers_order_table_title', esc_html__( 'Your Serial Number(s)', 'wc-serial-numbers' ) );
		$pending_keys_message = apply_filters( 'wc_serial_numbers_order_table_pending_keys_message', esc_html__( 'Pending', 'wc-serial-numbers' ) );

		$template_data = array(
			'title'                => $title,
			'columns'              => $columns,
			'keys'                 => $keys,
			'pending_keys_message' => $pending_keys_message,
		);
		wc_get_template(
			'/order/serial-numbers-display.php',
			$template_data,
			'wc-serial-numbers',
			Plugin::get()->templates_path()
		);
	}
}
