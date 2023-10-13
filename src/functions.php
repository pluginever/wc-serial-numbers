<?php
/**
 * Essential functions for the plugin.
 *
 * @since 1.0.0
 * @package WooCommerceSerialNumbers
 */

use WooCommerceSerialNumbers\Encryption;
use WooCommerceSerialNumbers\Models\Activation;
use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/Functions/Template.php';

/**
 * Get manager role.
 *
 * @since 1.4.2
 * @return string
 */
function wcsn_get_manager_role() {
	return apply_filters( 'wc_serial_numbers_manager_role', 'manage_woocommerce' );
}

/**
 * Check if software support is enabled or not.
 *
 * @since 1.4.6
 * @return bool
 */
function wcsn_is_software_support_enabled() {
	return 'yes' !== get_option( 'wc_serial_numbers_disable_software_support', 'no' );
}

/**
 * Get serial number's statuses.
 *
 * since 1.2.0
 *
 * @return array
 */
function wcsn_get_key_statuses() {
	$statuses = array(
		'available' => __( 'Available', 'wc-serial-numbers' ), // when ready for selling.
		'pending'   => __( 'Pending', 'wc-serial-numbers' ), // Assigned to an order but not paid yet.
		'sold'      => __( 'Sold', 'wc-serial-numbers' ), // Assigned to an order and paid.
		'expired'   => __( 'Expired', 'wc-serial-numbers' ), // when expired.
		'cancelled' => __( 'Cancelled', 'wc-serial-numbers' ), // when cancelled.
	);

	return apply_filters( 'wc_serial_numbers_key_statuses', $statuses );
}

/**
 * Check if serial number is reusing.
 *
 * @since 1.2.0
 * @return bool
 */
function wcsn_is_reusing_keys() {
	return 'yes' == get_option( 'wc_serial_numbers_reuse_serial_number', 'no' );
}

/**
 * Get order revoke statuses.
 *
 * @since 1.4.6
 * @return array
 */
function wcsn_get_revoke_statuses() {
	$statues = [
		'cancelled',
		'refunded',
		'failed',
		'pending',
	];

	return apply_filters( 'wc_serial_numbers_revoke_statuses', $statues );
}

/**
 * Get key sources.
 *
 * @since 1.2.0
 * @return mixed|void
 */
function wcsn_get_key_sources() {
	$sources = array(
		'custom_source' => __( 'Manually added', 'wc-serial-numbers' ),
	);

	return apply_filters( 'wc_serial_numbers_key_sources', $sources );
}

/**
 * Return true if pre argument version is older than the current version.
 *
 * @param string $version WC version.
 *
 * @since 1.4.6
 *
 * @return bool
 */
function wcsn_is_woocommerce_pre( $version ) {
	return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, $version, '<' );
}

/**
 * Return the product object.
 *
 * @param int|mixed $product WC_Product or order ID.
 *
 * @since 1.4.6
 *
 * @return null|\WC_Product object or null if not found.
 */
function wcsn_get_product_object( $product ) {
	return is_object( $product ) ? $product : wc_get_product( $product );
}

/**
 * Return the customer/user ID.
 *
 * @param int|mixed $order WC_Order or order ID.
 *
 * @since 1.4.6
 *
 * @return bool|int|mixed false if not found.
 */
function wcsn_get_customer_id( $order ) {
	$order = wcsn_get_order_object( $order );

	if ( $order && ! ( $order instanceof \WC_Order_Refund ) ) {
		return wcsn_is_woocommerce_pre( '3.0' ) ? $order->get_user_id() : $order->get_customer_id();
	}

	return false;
}

/**
 * Return the order object.
 *
 * @param int|mixed $order WC_Order or order ID.
 *
 * @since 1.4.6
 *
 * @return bool|\WC_Order
 */
function wcsn_get_order_object( $order ) {
	return is_object( $order ) ? $order : wc_get_order( $order );
}

/**
 * Insert key.
 *
 * @param array   $args Key arguments.
 * @param boolean $wp_error Optional. Whether to return a WP_Error on failure. Default false.
 *
 * @since 1.4.6
 * @return Key|WP_Error object on success, WP_Error object on failure.
 */
function wcsn_insert_key( $args, $wp_error = true ) {
	return Key::insert( $args, $wp_error );
}

/**
 * Query keys.
 *
 * @param array $args Query arguments.
 * @param bool  $count Optional. Whether to return only the total found count. Default false.
 *
 * @since 1.4.6
 * @return Key[]|array|int Keys array or count of keys.
 */
function wcsn_get_keys( $args = array(), $count = false ) {
	$defaults = array(
		'limit'   => 20,
		'offset'  => 0,
		'orderby' => 'id',
		'order'   => 'DESC',
		'fields'  => 'all',
	);
	$args     = wp_parse_args( $args, $defaults );
	if ( $count ) {
		return Key::count( $args );
	}

	return Key::query( $args );
}

/**
 * Get key.
 *
 * @param mixed $key Key ID.
 *
 * @since 1.4.6
 * @return Key|false
 */
function wcsn_get_key( $key ) {
	return Key::get( $key );
}

/**
 * Delete key.
 *
 * @param int $key_id Key ID.
 *
 * @since 1.4.6
 * @return bool True on success, false on failure.
 */
function wcsn_delete_key( $key_id ) {
	$key = wcsn_get_key( $key_id );
	if ( ! $key ) {
		return false;
	}

	return $key->delete();
}

/**
 * Insert activation.
 *
 * @param array $args Activation arguments.
 *
 * @since 1.4.6
 * @return Activation|WP_Error object on success, WP_Error object on failure.
 */
function wcsn_insert_activation( $args ) {
	return Activation::insert( $args );
}

/**
 * Query activations.
 *
 * @param array $args Query arguments.
 * @param bool  $count Optional. Whether to return only the total found count. Default false.
 *
 * @since 1.4.6
 * @return Activation[]|array|int Activations array or count of activations.
 */
function wcsn_get_activations( $args = array(), $count = false ) {
	$defaults = array(
		'limit'   => 20,
		'offset'  => 0,
		'orderby' => 'id',
		'order'   => 'DESC',
		'fields'  => 'all',
	);
	$args     = wp_parse_args( $args, $defaults );
	if ( $count ) {
		return Activation::count( $args );
	}

	return Activation::query( $args );
}

/**
 * Get activation.
 *
 * @param mixed $activation Activation ID.
 *
 * @since 1.4.6
 * @return Activation|false
 */
function wcsn_get_activation( $activation ) {
	return Activation::get( $activation );
}

/**
 * Delete activation.
 *
 * @param int $activation_id Activation ID.
 *
 * @since 1.4.6
 * @return bool True on success, false on failure.
 */
function wcsn_delete_activation( $activation_id ) {
	$activation = wcsn_get_activation( $activation_id );
	if ( ! $activation ) {
		return false;
	}

	return $activation->delete();
}

/**
 * Check if product enabled for selling serial numbers.
 *
 * @param int $product_id Product ID.
 *
 * @since 1.2.0
 * @return bool True if enabled, false otherwise.
 */
function wcsn_is_product_enabled( $product_id ) {
	return 'yes' == get_post_meta( $product_id, '_is_serial_number', true );
}

/**
 * Get order items.
 *
 * @param int $order_id Order ID.
 * @param int $order_item_id Order item ID. If not provided it will return all order items.
 *
 * @since 1.2.0
 * @return array
 */
function wcsn_get_order_line_items_data( $order_id, $order_item_id = null ) {
	// Cache the line items.
	$line_items = array();
	$order      = wcsn_get_order_object( $order_id );
	$items      = $order->get_items();
	if ( is_object( $order ) && count( $items ) > 0 ) {
		foreach ( $items as $item_id => $item ) {
			$product = $item->get_product();
			if ( ! $product ) {
				continue;
			}
			$product_id = $product->get_id();
			if ( ! wcsn_is_product_enabled( $product_id ) ) {
				continue;
			}
			if ( $order_item_id && absint( $order_item_id ) !== absint( $item_id ) ) {
				continue;
			}
			$quantity   = ! empty( $item->get_quantity() ) ? $item->get_quantity() : 0;
			$refund_qty = $order->get_qty_refunded_for_item( $item_id );
			// Deprecated filter.
			// todo: remove this filter in the future.
			$sources  = apply_filters( 'wc_serial_numbers_product_serial_source', 'custom_source', $product_id );
			$quantity = $quantity * apply_filters( 'wc_serial_numbers_per_product_delivery_qty', 1, $product_id, $order_id );

			$data = array(
				'product_id'    => $product_id,
				'order_item_id' => ! empty( $item_id ) ? (int) $item_id : 0,
				'refunded_qty'  => $refund_qty,
				'quantity'      => apply_filters( 'wc_serial_numbers_order_item_quantity', $quantity, $product_id, $order_id ),
				'key_source'    => $sources,
			);

			$line_items[] = $data;
		}
	}

	return apply_filters( 'wc_serial_numbers_order_line_items_data', $line_items, $order_id );
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
	return ! empty( wcsn_get_order_line_items_data( $order_id ) );
}

/**
 * Get order keys.
 *
 * @param int $order_id Order ID.
 *
 * @since 1.0.0
 * @return Key[]
 */
function wcsn_order_get_keys( $order_id ) {
	if ( ! wcsn_order_has_products( $order_id ) ) {
		return array();
	}

	return Key::query(
		array(
			'order_id' => $order_id,
			'limit'    => - 1,
		)
	);
}

/**
 * Determine if the order is fullfilled.
 *
 * @param int $order_id Order ID.
 *
 * @since 1.2.0
 * @return bool True if order is fullfilled, false otherwise.
 */
function wcsn_order_is_fullfilled( $order_id ) {
	if ( ! wcsn_order_has_products( $order_id ) ) {
		return true;
	}

	$keys       = wcsn_order_get_keys( $order_id );
	$line_items = wcsn_get_order_line_items_data( $order_id );
	$total_qty  = 0;
	foreach ( $line_items as $line_item ) {
		$total_qty += $line_item['quantity'];
	}

	return count( $keys ) >= $total_qty;
}

/**
 * Order get unfulfilled items.
 *
 * @param int $order_id Order ID.
 *
 * @since 1.2.0
 * @return array
 */
function wcsn_order_get_unfulfilled_items( $order_id ) {
	if ( ! wcsn_order_has_products( $order_id ) ) {
		return array();
	}

	$line_items = wcsn_get_order_line_items_data( $order_id );
	$keys       = wcsn_order_get_keys( $order_id );
	$items      = array();

	foreach ( $line_items as $line_item ) {
		$qty = $line_item['quantity'];
		foreach ( $keys as $key ) {
			if ( $key->get_product_id() !== $line_item['product_id'] ) {
				continue;
			}
//          todo uncomment this when we will support for order item id.
//			if ( $key->get_variation_id() !== $line_item['variation_id'] ) {
//				continue;
//			}
//			if ( $key->get_order_item_id() !== $line_item['order_item_id'] ) {
//				continue;
//			}
			$qty --;
		}
		if ( $qty > 0 ) {
			$items[] = array_merge( $line_item, array( 'quantity' => $qty ) );
		}
	}

	return $items;
}

/**
 * Update order keys.
 *
 * @param int $order_id Order ID.
 *
 * @since 1.4.6
 * @return void
 */
function wcsn_order_update_keys( $order_id ) {
	$order          = wcsn_get_order_object( $order_id );
	$customer_id    = $order->get_customer_id();
	$line_items     = wcsn_get_order_line_items_data( $order );
	$revoke_statues = wcsn_get_revoke_statuses();
	$order_status   = $order->get_status( 'edit' );
	if ( empty( $line_items ) ) {
		return;
	}

	if ( ! apply_filters( 'wc_serial_numbers_update_order_keys', true, $order_id, $line_items, $order_status ) ) {
		return;
	}

	/**
	 * Action hook to pre update order keys.
	 *
	 * @param int   $order_id Order ID.
	 * @param array $line_items Order line items.
	 *
	 * @since 1.4.6
	 */
	do_action( 'wc_serial_numbers_pre_update_order_keys', $order_id, $line_items );

	$do_add = apply_filters( 'wc_serial_numbers_add_order_keys', true, $order_id, $line_items, $order_status );


	if ( in_array( $order_status, [ 'processing', 'completed' ], true ) && ! wcsn_order_is_fullfilled( $order_id ) && $do_add ) {

		/**
		 * Action hook to pre add order keys.
		 *
		 * @param int    $order_id Order ID.
		 * @param array  $line_items Order line items.
		 * @param string $order_status Order status.
		 *
		 * @since 1.4.6
		 */
		do_action( 'wc_serial_numbers_pre_add_order_keys', $order_id, $line_items, $order_status );
		$added = 0;
		foreach ( $line_items as $k => $item ) {
			if ( ! apply_filters( 'wc_serial_numbers_add_order_item_keys', true, $item, $order_id ) ) {
				continue;
			}

			$delivered_qty = Key::count( array(
				'order_id'       => $order_id,
				'product_id'     => $item['product_id'],
				'status__not_in' => [ 'cancelled' ],
			) );
			if ( $item['refunded_qty'] >= $item['quantity'] ) {
				continue;
			}
			$needed_count = $item['quantity'] - $delivered_qty - $item['refunded_qty'];
			if ( $delivered_qty >= $needed_count || empty( $needed_count ) ) {
				continue;
			}

			/**
			 * Action hook to pre add keys to order item.
			 *
			 * @param array $line_item Order line item.
			 * @param int   $order_id Order ID.
			 * @param int   $needed_count Needed count.
			 */
			do_action( 'wc_serial_numbers_pre_add_order_item_eys', $item, $order_id, $needed_count );

			// Deprecated. use wc_serial_numbers_pre_order_add_keys instead. Will be removed in 1.5.0.
			apply_filters( 'wc_serial_numbers_pre_order_item_connect_serial_numbers', $item['product_id'], $needed_count, $item['key_source'], $order_id );

			// Get new keys.
			$keys = Key::query(
				array(
					'product_id' => $item['product_id'],
					'status'     => 'available',
					'limit'      => $needed_count,
					'orderby'    => 'id',
					'order'      => 'ASC'
				)
			);
			if ( count( $keys ) < $needed_count ) {
				$order->add_order_note(
					sprintf(
					/* translators: 1: product title 2: source and 3: Quantity */
						esc_html__( 'There is not enough serial numbers for the product %1$s from selected source %2$s, needed total %3$d.', 'wc-serial-numbers' ),
						wcsn_get_product_title( $item['product_id'] ),
						$item['key_source'],
						$needed_count
					),
					false
				);

				continue;
			}

			// Assign keys to order.
			foreach ( $keys as $key ) {
				$key->set_data( array(
					'order_id'      => $order_id,
					'order_item_id' => $item['order_item_id'],
					'order_date'    => $order->get_date_created() ? $order->get_date_created()->format( 'Y-m-d H:i:s' ) : current_time( 'mysql' ),
					'customer_id'   => $customer_id,
					'status'        => 'sold',
				) );
				if ( ! is_wp_error( $key->save() ) ) {
					$added ++;
				}
			}

			/**
			 * Action hook to post add keys to order item.
			 *
			 * @param array $line_item Order line item.
			 * @param int   $order_id Order ID.
			 * @param int   $needed_count Needed count.
			 */
			do_action( 'wc_serial_numbers_added_order_item_keys', $item, $order_id, $needed_count );

			// Deprecated. use wc_serial_numbers_pre_order_add_keys instead. Will be removed in 1.5.0.
			do_action( 'wc_serial_numbers_order_connect_serial_numbers', $order_id, count( $keys ) );
		}

		if ( $added > 0 ) {
			/**
			 * Action hook to post add order keys.
			 *
			 * @param int    $order_id Order ID.
			 * @param array  $line_items Order line items.
			 * @param string $order_status Order status.
			 *
			 * @since 1.4.6
			 */
			do_action( 'wc_serial_numbers_add_order_keys', $order_id, $line_items, $order_status );
		}
	}

	// Revoke keys.
	$keys = Key::query(
		array(
			'order_id' => $order_id,
			'limit'    => - 1,
		)
	);

	foreach ( $keys as $key ) {
		$is_expired = $key->is_expired();
		if ( $is_expired && 'expired' !== $key->get_status() ) {
			$key->set_status( 'expired' );
			$key->save();
		} elseif ( ! $is_expired && in_array( $order_status, [
				'processing',
				'complete'
			], true ) && 'sold' !== $key->get_status() ) {
			$key->set_status( 'sold' );
			$key->save();
		} elseif ( 'on-hold' === $order_status && ! $is_expired && 'pending' !== $key->get_status() ) {
			$key->set_status( 'pending' );
			$key->save();
		} elseif ( in_array( $order_status, $revoke_statues, true ) && ! $is_expired && apply_filters( 'wc_serial_numbers_revoke_order_item_keys', true, $line_items, $order_id ) ) {
			wcsn_order_remove_keys( $order_id );
		}
	}

	/**
	 * Action hook to post add keys to order item.
	 *
	 * @param int   $order_id Order ID.
	 * @param array $line_items Order line items.
	 *
	 * @since 1.4.6
	 */
	do_action( 'wc_serial_numbers_order_update_keys', $order_id, $line_items );
}

/**
 * Order remove keys.
 *
 * @param int   $order_id Order ID.
 * @param array $line_items Order line items.
 *
 * @since 1.4.6
 *
 * @return array|false Array of keys or false if no keys found.
 */
function wcsn_order_remove_keys( $order_id, $product_id = null ) {
	$is_reusing = wcsn_is_reusing_keys();
	$args       = array(
		'order_id' => $order_id,
		'limit'    => - 1,
	);

	if ( ! empty( $product_id ) ) {
		$args['product_id'] = $product_id;
	}

	$keys = Key::query( $args );

	if ( ! $keys ) {
		return false;
	}

	/**
	 * Action hook to pre revoke keys from order item.
	 *
	 * @param int   $order_id Order ID.
	 * @param int   $product_id Product ID.
	 * @param array $keys Order keys.
	 *
	 * @since 1.4.6
	 */
	do_action( 'wc_serial_numbers_pre_revoke_order_keys', $order_id, $product_id, $keys );

	foreach ( $keys as $key ) {
		$props = array(
			'status' => $is_reusing ? 'available' : 'cancelled'
		);
		if ( ! $is_reusing ) {
			$props['order_id']      = 0;
			$props['order_item_id'] = 0;
			$props['order_date']    = null;
		}
		$key->set_data( $props );
		$key->save();
	}

	/**
	 * Action hook to post revoke keys from order item.
	 *
	 * @param int   $order_id Order ID.
	 * @param int   $product_id Product ID.
	 * @param array $keys Order keys.
	 *
	 * @since 1.4.6
	 */
	do_action( 'wc_serial_numbers_revoke_order_keys', $order_id, $product_id, $keys );

	return $keys;
}

/**
 * Replace keys.
 *
 * @param int $order_id Order ID.
 * @param int $product_id Product ID.
 * @param int $key_id Key ID.
 *
 * @since 1.4.7
 *
 * @return bool
 */
function wcsn_order_replace_key( $order_id, $product_id = null, $key_id = null ) {
	$is_reusing = wcsn_is_reusing_keys();
	$args       = array(
		'order_id' => $order_id,
		'limit'    => - 1,
		'no_count' => true,
	);

	if ( ! empty( $product_id ) ) {
		$args['product_id'] = $product_id;
	}

	if ( ! empty( $key_id ) ) {
		$args['include'] = $key_id;
	}

	$keys = Key::query( $args );

	if ( ! $keys ) {
		return false;
	}

	/**
	 * Action hook to pre replace keys from order item.
	 *
	 * @param int   $order_id Order ID.
	 * @param int   $product_id Product ID.
	 * @param array $keys Order keys.
	 *
	 * @since 1.4.7
	 */
	do_action( 'wc_serial_numbers_pre_replace_order_keys', $order_id, $product_id, $keys );

	$replaced = 0;

	foreach ( $keys as $key ) {
		$props = array(
			'status' => $is_reusing ? 'available' : 'cancelled'
		);
		if ( ! $is_reusing ) {
			$props['order_id']      = 0;
			$props['order_item_id'] = 0;
			$props['order_date']    = null;
		}
		$key->set_data( $props );
		if ( $key->save() ) {
			$replaced ++;
		}
	}

	if ( $replaced > 0 ) {
		wcsn_order_update_keys( $order_id );

		/**
		 * Action hook to post replace keys from order item.
		 *
		 * @param int   $order_id Order ID.
		 * @param int   $product_id Product ID.
		 * @param array $keys Order keys.
		 *
		 * @since 1.4.7
		 */
		do_action( 'wc_serial_numbers_replace_order_keys', $order_id, $product_id, $keys );

		return true;
	}

	return false;
}

/**
 * Get product title.
 *
 * @param \WC_Product| int $product Product title.
 *
 * @since 1.2.0
 *
 * @return string
 */
function wcsn_get_product_title( $product ) {
	$product = wcsn_get_product_object( $product );
	if ( $product && ! empty( $product->get_id() ) ) {
		return sprintf(
			'(#%1$s) %2$s',
			$product->get_id(),
			wp_strip_all_tags( $product->get_formatted_name() )
		);
	}

	return '';
}

/**
 * Get enabled products query args.
 *
 * @since 1.4.6
 *
 * @return array
 */
function wcsn_get_products_query_args() {
	$args = array(
		'post_type' => [ 'product' ],
		'tax_query' => array( // @codingStandardsIgnoreLine
			'relation' => 'OR',
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => [ 'simple' ],
				'operator' => 'IN',
			),
		),
	);

	return apply_filters( 'wc_serial_numbers_products_query_args', $args );
}

/**
 * Get enabled products.
 *
 * @since 1.4.6
 * @return array|int List of products or number of products.
 */
function wcsn_get_products( $args = array() ) {
	$args = wp_parse_args( $args, wcsn_get_products_query_args() );
	if ( empty( $args['meta_query'] ) ) {
		$args['meta_query'] = [];
	}
	$args['meta_query'][] = array(
		'key'     => '_is_serial_number',
		'value'   => 'yes',
		'compare' => '=',
	);

	$is_count = isset( $args['count'] ) && $args['count'];
	unset( $args['count'] );
	$query = new \WP_Query( $args );
	if ( $is_count ) {
		return $query->found_posts;
	}

	return $query->posts;
}

/**
 * Encrypt serial number.
 *
 * @param string $key Key.
 *
 * @since 1.2.0
 * @return false|string
 */
function wcsn_encrypt_key( $key ) {
	return Encryption::maybeEncrypt( $key );
}

/**
 * Decrypt number.
 *
 * @param string $key Key.
 *
 * @since 1.2.0
 * @return false|string
 */
function wcsn_decrypt_key( $key ) {
	return Encryption::maybeDecrypt( $key );
}

/**
 * Check if encryption is disabled (i.e, for real physical product serial numbers)
 *
 * @param $product_id
 *
 * @return bool
 * @since 1.x.x
 */
function wc_serial_numbers_encryption_disabled() {
	return 'yes' == get_option( 'wc_serial_numbers_disable_encryption' );
}

/**
 * Get product stocks
 *
 * @param int  $stock_limit Stock limit.
 * @param bool $force Force.
 *
 * @since 1.4.6
 *
 * @return array
 */
function wcsn_get_stocks_count( $stock_limit = null, $force = true ) {
	$transient_key = 'wcsn_products_stock_count';
	$counts        = get_transient( $transient_key );

	if ( $force || false === $counts ) {
		$counts   = array();
		$post_ids = wcsn_get_products( array(
			'posts_per_page' => - 1,
			'fields'         => 'ids',
			'meta_query'     => array( // @codingStandardsIgnoreLine
				'relation' => 'AND',
				array(
					'key'     => '_serial_key_source',
					'value'   => 'custom_source',
					'compare' => '=',
				)
			),
		) );

		foreach ( $post_ids as $post_id ) {
			$counts[ $post_id ] = wcsn_get_keys( [
				'product_id' => $post_id,
				'status'     => 'available',
				'count'      => true,
			] );
		}

		set_transient( $transient_key, $counts, 60 * 60 );
	}
	if ( $stock_limit > 0 ) {
		// get the results where value is equal or less than max.
		$counts = array_filter( $counts, function ( $value ) use ( $stock_limit ) {
			return $value <= $stock_limit;
		} );
	}

	return $counts;
}

/**
 * Get stock of product.
 *
 * @param int $product_id Product ID.
 *
 * @since 1.4.6
 * @retun int
 */
function wcsn_get_product_stock( $product_id ) {
	$counts = wcsn_get_stocks_count();
	if ( isset( $counts[ $product_id ] ) ) {
		return $counts[ $product_id ];
	}

	return 0;
}

/**
 * Get product edit link.
 *
 * @param int $product_id Product ID.
 *
 * @since 1.4.8
 * @retun string
 */
function wcsn_get_edit_product_link( $product_id ) {
	// If the product is a variation, get the parent product.

	$product = wc_get_product( $product_id );
	if ( $product && $product->is_type( 'variation' ) ) {
		$product_id = $product->get_parent_id();
	}

	return get_edit_post_link( $product_id );
}

/**
 * Is duplicate serial key allowed.
 *
 * @since 1.4.8
 * @return bool
 */
function wcsn_is_duplicate_key_allowed() {
	return apply_filters( 'wc_serial_numbers_allow_duplicate_key', false );
}

/**
 * Get product link.
 *
 * @param int $product_id Product ID.
 *
 * @since 1.4.8
 * @retun string
 */
function wcsn_get_product_link( $product_id ) {
	// If the product is a variation, get the parent product.
	$product = wc_get_product( $product_id );
	if ( $product && $product->is_type( 'variation' ) ) {
		$product_id = $product->get_parent_id();
	}

	return get_permalink( $product_id );
}

/**
 * Get product display properties.
 *
 * @param Key    $key Key object.
 * @param string $context Context.
 *
 * @since 1.5.6
 * @return array
 */
function wcsn_get_key_display_properties( $key, $context = 'order_details' ) {
	if ( empty( $key ) || ! $key instanceof Key ) {
		return array();
	}
	$properties = array(
		'key'              => array(
			'label'    => __( 'Key', 'wc-serial-numbers' ),
			'value'    => '<code>' . $key->get_key() . '</code>',
			'priority' => 10,
		),
		'activation_email' => array(
			'label'    => __( 'Activation Email', 'wc-serial-numbers' ),
			'value'    => $key->get_customer_email(),
			'priority' => 20,
		),
		'activation_limit' => array(
			'label'    => __( 'Activation Limit', 'wc-serial-numbers' ),
			'value'    => ! empty( $key->get_activation_limit() ) ? number_format_i18n( $key->get_activation_limit() ) : __( 'None', 'wc-serial-numbers' ),
			'priority' => 30,
		),
		'activation_count' => array(
			'label'    => __( 'Activation Count', 'wc-serial-numbers' ),
			'value'    => ! empty( $key->get_activation_count() ) ? number_format_i18n( $key->get_activation_count() ) : __( 'None', 'wc-serial-numbers' ),
			'priority' => 40,
		),
		'expire_date'      => array(
			'label'    => __( 'Expire Date', 'wc-serial-numbers' ),
			'value'    => ! empty( $key->get_expire_date() ) ? $key->get_expire_date() : __( 'Lifetime', 'wc-serial-numbers' ),
			'priority' => 50,
		),
		'status'           => array(
			'label'    => __( 'Status', 'wc-serial-numbers' ),
			'value'    => $key->get_status(),
			'priority' => 100,
		),
	);

	/**
	 * Filter key properties.
	 *
	 * @param array $props Key properties.
	 * @param Key $key Key object.
	 * @param string $context Context.
	 *
	 * @since 1.4.9
	 */
	$properties = apply_filters( 'wc_serial_numbers_display_key_props', $properties, $key, $context );

	usort(
		$properties,
		function( $a, $b ) {
			$a_priority = isset( $a['priority'] ) ? $a['priority'] : 10;
			$b_priority = isset( $b['priority'] ) ? $b['priority'] : 10;
			return $a_priority - $b_priority;
		}
	);

	return $properties;
}
