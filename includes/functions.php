<?php
/**
 * Essential functions for the plugin.
 *
 * @since 1.0.0
 * @package WooCommerceSerialNumbers
 */

use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Order add keys.
 *
 * @param int $order_id The order ID.
 * @param int $order_item_id The order item ID.
 *
 * @since 1.0.0
 * @return int The number of keys added.
 */
function wcsn_add_order_keys( $order_id, $order_item_id = null ) {
	$order = wc_get_order( $order_id );

	// Bail if order is not found.
	if ( ! $order ) {
		return false;
	}

	/**
	 * Filter to allow processing the order.
	 *
	 * @param bool      $allow Should the order be processed.
	 * @param \WC_Order $order Order object.
	 */
	$allow = apply_filters( 'wc_serial_numbers_add_order_keys', true, $order );

	/**
	 * Filter to allow processing the order.
	 *
	 * @param bool      $allow Should the order be processed.
	 * @param \WC_Order $order Order object.
	 * @depecated 2.0.2
	 */
	$allow = apply_filters( 'wc_serial_numbers_update_order_keys', $allow, $order->get_id(), $order->get_items(), $order->get_status() );

	if ( ! $allow ) {
		// Create a log.
		WCSN()->log(
			sprintf(
				/* translators: %s: Order ID */
				esc_html__( 'Processing of order #%s is not allowed.', 'wc-serial-numbers' ),
				$order_id
			)
		);

		return false;
	}

	// Check if the order has keyed products.
	if ( ! wcsn_order_has_products( $order_id ) ) {
		WCSN()->log(
			sprintf(
				/* translators: %s: Order ID */
				esc_html__( 'Order #%s does not have any keyed products.', 'wc-serial-numbers' ),
				$order_id
			)
		);

		if ( 'yes' === $order->get_meta( '_wcsn_order', true ) ) {
			$order->delete_meta_data( '_wcsn_order' );
			$order->save();
		}
	}

	/**
	 * Action hook to pre add order keys.
	 *
	 * @param int    $order_id Order ID.
	 * @param array  $line_items Order line items.
	 * @param string $order_status Order status.
	 *
	 * @since 1.4.6
	 */
	do_action( 'wc_serial_numbers_pre_add_order_keys', $order_id, $order->get_items(), $order->get_status() );

	$keys_added = 0;
	foreach ( $order->get_items() as $item ) {
		// If the item is not a product, then skip.
		if ( 'line_item' !== $item['type'] || ! $item instanceof \WC_Order_Item_Product ) {
			continue;
		}

		if ( ! apply_filters( 'wc_serial_numbers_add_order_item_keys', true, $item, $order_id ) ) {
			continue;
		}

		// If order item ID is set, and it does not match, then skip.
		if ( ! empty( $order_item_id ) && $order_item_id !== $item->get_id() ) {
			continue;
		}

		WCKM()->log(
			sprintf(
				/* translators: %s: Order ID */
				esc_html__( 'Processing order item #%s.', 'wc-serial-numbers' ),
				$item->get_id()
			)
		);

		$product = $item->get_product();

		// If the product is not enabled for selling serial numbers, then skip.
		if ( ! wcsn_is_keyed_product( $product ) ) {
			WCSN()->log(
				sprintf(
					/* translators: %s: Product ID */
					esc_html__( 'Product #%s is not enabled for selling serial numbers.', 'wc-serial-numbers' ),
					$product->get_id()
				)
			);
			continue;
		}

		// check if the item have delivery quantity set otherwise calculate it.
		if ( $item->get_meta( '_wcsn_delivery_quantity', true ) ) {
			$delivery_qty = max( 1, (int) $item->get_meta( '_wckm_delivery_qty', true ) );
			WCSN()->log(
				sprintf(
					/* translators: %s: Order Item ID */
					esc_html__( 'Delivery quantity for order item #%1$s is set to %2$s.', 'wc-serial-numbers' ),
					$item->get_id(),
					$delivery_qty
				)
			);
		} else {
			$delivery_qty = max( 1, (int) wcsn_get_delivery_quantity( $product, 1 ) );
			$item->add_meta_data( '_wcsn_order_item', 'yes', true );
			$item->add_meta_data( '_wcsn_delivery_quantity', $delivery_qty, true );
			$item->save();
			WCSN()->log(
				sprintf(
					/* translators: %s: Order Item ID */
					esc_html__( 'Delivery quantity for order item #%1$s is set to %2$s.', 'wc-serial-numbers' ),
					$item->get_id(),
					$delivery_qty
				)
			);
		}

		$total_qty = $delivery_qty * $item->get_quantity();

		WCSN()->log(
			sprintf(
				/* translators: %s: Order Item ID */
				esc_html__( 'The item should have %1$s keys in total.', 'wc-serial-numbers' ),
				$total_qty
			)
		);

		$added_count = Key::count(
			array(
				'order_id'       => $order_id,
				'product_id'     => $item['product_id'],
				'status__not_in' => array( 'cancelled' ),
			)
		);

		if ( $added_count >= $total_qty ) {
			WCSN()->log(
				sprintf(
				// translators: %d: total quantity, %d: added count.
					esc_html__( 'The item already has %2$d keys. Total keys needed %1$d. Skipping processing.', 'wc-serial-numbers' ),
					$total_qty,
					$added_count
				)
			);
			continue;
		}

		$pending_count = $total_qty - $added_count;

		WCSN()->log(
			sprintf(
			// translators: %d: total quantity, %d: added count.
				esc_html__( 'Previously added %1$d keys for the item. Need to add %2$d more keys.', 'wc-serial-numbers' ),
				$added_count,
				$pending_count
			)
		);

		/**
		 * Action hook before adding order item keys.
		 *
		 * @param int            $quantity The quantity of keys to be added.
		 * @param \WC_Order_Item $item Order item.
		 * @param \WC_Order      $order Order object.
		 */
		do_action( 'wc_serial_numbers_pre_add_order_item_keys', $pending_count, $item, $order );

		// Get new serial keys.
		$keys = Key::query(
			array(
				'product_id' => $item['product_id'],
				'status'     => 'available',
				'limit'      => $pending_count,
				'orderby'    => 'id',
				'order'      => 'ASC',
			)
		);

		WCSN()->log(
			sprintf(
				/* translators: %s: Order Item ID */
				esc_html__( 'Found %1$s available keys for order item.', 'wc-serial-numbers' ),
				count( $keys )
			)
		);

		/**
		 * Filter hook to alter the keys before assigning them to the order item.
		 *
		 * @param Key[]          $keys The keys.
		 * @param \WC_Order_Item $item Order item.
		 * @param \WC_Order      $order Order object.
		 */
		$keys = apply_filters( 'wc_serial_numbers_order_item_keys', $keys, $item, $order );

		// If keys are found, assign them to the order item.
		if ( ! empty( $keys ) ) {
			foreach ( $keys as $key ) {
				$key->set_data(
					array(
						'order_id'      => $order_id,
						'order_item_id' => $item['order_item_id'],
						'order_date'    => $order->get_date_created() ? $order->get_date_created()->format( 'Y-m-d H:i:s' ) : current_time( 'mysql' ),
						'customer_id'   => $order->get_customer_id(),
						'status'        => 'sold',
					)
				);

				if ( ! is_wp_error( $key->save() ) ) {
					WCSN()->log(
						sprintf(
						// translators: %d: key ID.
							esc_html__( 'Key #%1$d added to the order item %2$d.', 'wc-serial-numbers' ),
							$key->get_id(),
							$item->get_id(),
						)
					);

					--$pending_count;
					++$keys_added;
				}
			}
		}

		// If we still need more keys, add order notes about the shortage of keys and send admin notification.
		if ( $pending_count > 0 ) {
			// translators: %1$s: product name, %2$d: total quantity, %3$d: needed count.
			$note = sprintf( esc_html__( 'Not enough keys available for product %1$s. Needed %2$d, but only %3$d available.', 'wc-serial-numbers' ), $product->get_name(), $pending_count, count( $keys ) );
			$order->add_order_note( $note );

			WCSN()->log(
				sprintf(
				// translators: %1$s: product name, %2$d: total quantity, %3$d: needed count.
					esc_html__( 'Not enough keys available for product %1$s. Needed %2$d, but only %3$d available.', 'wc-serial-numbers' ),
					$product->get_name(),
					$pending_count,
					count( $keys )
				)
			);

			/**
			 * Action hook when there is a shortage of keys for an order item.
			 *
			 * @param int            $pending_count The number of keys needed.
			 * @param \WC_Order_Item $item Order item.
			 * @param \WC_Order      $order Order object.
			 */
			do_action( 'wc_serial_numbers_order_item_keys_shortage', $pending_count, $item, $order );
		} else {
			WCSN()->log(
				sprintf(
				// translators: %d: order item ID.
					esc_html__( 'All keys added for order item #%1$d.', 'wc-key-manager' ),
					$item->get_id()
				)
			);
		}
	}

	// Set a flag to indicate that we have processed the order.
	if ( 'yes' !== $order->get_meta( '_wcsn_order', true ) ) {
		$order->update_meta_data( '_wcsn_order', 'yes' );
		$order->save();
	}

	return $keys_added;
}

/**
 * Determine if the order contains product that enabled for selling serial numbers.
 *
 * @param int|WC_Order $order_id Order ID.
 *
 * @since 1.2.0
 * @return bool True if order contains product that enabled for selling serial numbers, false otherwise.
 */
function wcsn_order_has_products( $order_id ) {
	$order = wc_get_order( $order_id );

	// Bail if order is not found.
	if ( ! $order ) {
		return false;
	}

	$items = $order->get_items();

	if ( empty( $items ) ) {
		return false;
	}

	$keyed = false;
	foreach ( $items as $item ) {
		// If the item is not a product, then skip.
		if ( 'line_item' !== $item['type'] || ! $item instanceof \WC_Order_Item_Product ) {
			continue;
		}

		$product = $item->get_product();

		if ( wcsn_is_keyed_product( $product ) ) {
			$keyed = true;
			break;
		}
	}

	return apply_filters( 'wcsn_order_has_keyed_products', $keyed, $order );
}

/**
 * Determine if the product is enabled for selling serial numbers.
 *
 * @param int|WC_Product $product Product ID or product object.
 *
 * @since 2.0.2
 * @return bool True if product is enabled for selling serial numbers, false otherwise.
 */
function wcsn_is_keyed_product( $product ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}

	if ( ! $product ) {
		return false;
	}

	// if variable product, then get the parent product.
	if ( $product->is_type( 'variation' ) ) {
		$product = wc_get_product( $product->get_parent_id() );
	}

	$enabled = 'yes' === get_post_meta( $product->get_id(), '_wcsn_enable_key', true );

	return apply_filters( 'wcsn_is_keyed_product', $enabled, $product );
}

/**
 * Get the delivery quantity for the product.
 *
 * @param int|WC_Product $product Product ID or product object.
 * @param int            $quantity Default quantity.
 *
 * @since 1.0.0
 * @return int The delivery quantity.
 */
function wcsn_get_delivery_quantity( $product, $quantity = 1 ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}

	// Bail if product is not found.
	if ( ! $product ) {
		return 0;
	}

	$delivery_qty = (int) $product->get_meta( '_wcsn_delivery_quantity', true );
	if ( empty( $delivery_qty ) && $product->is_type( 'variation' ) ) {
		$delivery_qty = wcsn_get_delivery_quantity( $product->get_parent_id(), $quantity );
	}

	if ( empty( $delivery_qty ) ) {
		$delivery_qty = 1;
	}

	/**
	 * Filter to allow altering the delivery quantity.
	 *
	 * @param int            $delivery_qty The delivery quantity.
	 * @param WC_Product     $product The product.
	 * @param int            $quantity The quantity.
	 */
	$delivery_quantity = apply_filters( 'wc_serial_numbers_delivery_quantity', $delivery_qty, $product, $quantity );

	return $delivery_quantity * $quantity;
}
