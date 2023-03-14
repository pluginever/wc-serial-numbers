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
require_once __DIR__ . '/Functions/Deprecated.php';

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
		'instock'   => __( 'In Stock', 'wc-serial-numbers' ), // when ready for selling.
		'onhold'    => __( 'On Hold', 'wc-serial-numbers' ), // Assigned to an order but not paid yet.
		'sold'      => __( 'Sold', 'wc-serial-numbers' ), // Assigned to an order and paid.
		'expired'   => __( 'Expired', 'wc-serial-numbers' ), // when expired.
		'cancelled' => __( 'Cancelled', 'wc-serial-numbers' ), // when cancelled.
	);

	return apply_filters( 'wc_serial_numbers_key_statuses', $statuses );
}

/**
 * Insert key.
 *
 * @param array $args Key arguments.
 * @param boolean $wp_error Optional. Whether to return a WP_Error on failure. Default false.
 *
 * @since 1.4.6
 * @return Key|WP_Error object on success, WP_Error object on failure.
 */
function wcsn_insert_key( $args, $wp_error = false ) {
	return Key::insert( $args, $wp_error );
}

/**
 * Query keys.
 *
 * @param array $args Query arguments.
 *
 * @since 1.4.6
 * @return array
 */
function wcsn_get_keys( $args = array() ) {
	return Key::query( $args );
}

/**
 * Get key.
 *
 * @param int $key_id Key ID.
 * @param string $by Optional. Column name to query by. Default 'id'.
 * @param array $args Optional. Query arguments.
 *
 * @since 1.4.6
 * @return Key|false
 */
function wcsn_get_key( $key_id, $by = 'id', $args = array() ) {
	return Key::get( $key_id, $by, $args );
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
 *
 * @since 1.4.6
 * @return array
 */
function wcsn_get_activations( $args = array() ) {
	return Activation::query( $args );
}

/**
 * Get activation.
 *
 * @param int $activation_id Activation ID.
 * @param string $by Optional. Column name to query by. Default 'id'.
 * @param array $args Optional. Query arguments.
 *
 * @since 1.4.6
 * @return Activation|false
 */
function wcsn_get_activation( $activation_id, $by = 'id', $args = array() ) {
	return Activation::get( $activation_id, $by, $args );
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
 * Check if serial number is reusing.
 *
 * @since 1.2.0
 * @return bool
 */
function wcsn_is_reusing_keys() {
	return 'yes' == get_option( 'wc_serial_numbers_reuse_serial_number', 'no' );
}

/**
 * Determine if the order contains product that enabled for selling serial numbers.
 *
 * @param int $order_id Order ID.
 *
 * @since 1.2.0
 * @return bool True if order contains product that enabled for selling serial numbers, false otherwise.
 */
function wcsn_order_has_products( $order_id ) {
	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return false;
	}

	$items = $order->get_items();

	if ( ! $items ) {
		return false;
	}

	foreach ( $items as $item ) {
		$product_id = $item->get_product_id();

		if ( wcsn_is_product_enabled( $product_id ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Add keys to order.
 *
 * @param int $order_id Order ID.
 * @param int $order_item_id Order item ID. If not provided it will assign to all order items.
 *
 * @since 1.0.0
 * @return bool|int Number of keys added to order or false on failure.
 */
function wcsn_order_add_keys( $order_id, $order_item_id = null ) {
	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return false;
	}

	$items = $order->get_items();

	if ( ! $items ) {
		return false;
	}

	$count_added = 0;
	foreach ( $items as $item ) {
		if ( $order_item_id && $order_item_id !== $item->get_id() ) {
			continue;
		}

		$product = $item->get_product();

		if ( ! $product ) {
			continue;
		}

		$product_id = $product->get_variation_id() ? $product->get_variation_id() : $product->get_product_id();
		$quantity   = $item->get_quantity();
		if ( ! wcsn_is_product_enabled( $product_id ) ) {
			continue;
		}
		$per_product_delivery_qty       = absint( apply_filters( 'wc_serial_numbers_per_product_delivery_qty', 1, $product_id ) );
		$per_product_total_delivery_qty = $quantity * $per_product_delivery_qty;
		$delivered_qty                  = Key::count(
			array(
				'product_id' => $product_id,
				'order_id'   => $order_id,
				// todo add order_item_id to query when database supports it.
			)
		);
		if ( $delivered_qty >= $per_product_total_delivery_qty ) {
			continue;
		}
		$total_delivery_qty = $per_product_total_delivery_qty - $delivered_qty;
		$source             = apply_filters( 'wc_serial_numbers_product_serial_source', 'custom_source', $product_id, $total_delivery_qty );
		// Deprecated. use wc_serial_numbers_pre_order_add_keys instead. Will be removed in 1.5.0.
		do_action( 'wc_serial_numbers_pre_order_item_connect_serial_numbers', $product_id, $total_delivery_qty, $source, $order_id );
		do_action( 'wc_serial_numbers_pre_order_add_keys', $product_id, $total_delivery_qty, $source, $order_id );
		$keys = Key::query(
			array(
				'product_id' => $product_id,
				'status'     => 'instock',
				'source'     => $source,
				'number'     => $total_delivery_qty,
			)
		);
		// If the order does not require payment then we need to mark the keys as sold.
		$status     = $order->has_status( 'completed' ) ? 'sold' : 'onhold';
		$order_date = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : current_time( 'mysql' );
		foreach ( $keys as $key ) {
			$key->set_order_id( $order_id );
			// todo $key->set_order_item_id( $item->get_id() );
			$key->set_status( $status );
			$key->set_order_date( $order_date );
			if ( ! is_wp_error( $key->save() ) ) {
				$count_added ++;
			}
		}
	}

	// Now go through all the keys and mark them as sold if the order is completed.
	$keys = Key::query(
		array(
			'order_id' => $order_id,
		)
	);
	foreach ( $keys as $key ) {
		if ( $order->has_status( 'completed' ) && 'onhold' === $key->get_status() ) {
			$key->set_status( 'sold' );
			$key->save();
		}
	}

	// Deprecated. use wc_serial_numbers_order_add_keys instead. Will be removed in 1.5.0.
	do_action( 'wc_serial_numbers_order_connect_serial_numbers', $order_id, $count_added );
	do_action( 'wc_serial_numbers_order_add_keys', $order_id, $count_added );

	return $count_added;
}

/**
 * Remove keys from order.
 *
 * @param int $order_id Order ID.
 * @param int $order_item_id Order item ID. If not provided it will assign to all order items.
 *
 * @since 1.0.0
 */
function wcsn_order_remove_keys( $order_id, $order_item_id = null ) {
	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return false;
	}

	// If the order does not contain any serial numbers then we don't need to do anything.
	if ( ! wcsn_order_has_products( $order_id ) ) {
		return false;
	}

	$items = $order->get_items();

	if ( ! $items ) {
		return false;
	}

	// Deprecated. use wc_serial_numbers_pre_order_remove_keys instead. Will be removed in 1.5.0.
	do_action( 'wc_serial_numbers_pre_order_disconnect_serial_numbers', $order_id );
	do_action( 'wc_serial_numbers_pre_order_remove_keys', $order_id );

	$count_removed = 0;
	$is_reusing    = wcsn_is_reusing_keys();
	$keys          = Key::query(
		array(
			'order_id' => $order_id,
			'number'   => - 1,
		)
	);

	foreach ( $keys as $key ) {
		$key->set_order_id( 0 );
		// todo $key->set_order_item_id( 0 );
		$key->set_status( $is_reusing ? 'instock' : 'cancelled' );
		$key->set_order_date( 0 );
		if ( ! is_wp_error( $key->save() ) ) {
			$count_removed ++;
		}
	}

	// Deprecated. use wc_serial_numbers_order_remove_keys instead. Will be removed in 1.5.0.
	do_action( 'wc_serial_numbers_order_disconnect_serial_numbers', $order_id, $count_removed );
	do_action( 'wc_serial_numbers_order_remove_keys', $order_id, $count_removed );

	return $count_removed;
}

/**
 * Order sync keys.
 *
 * @param int $order_id Order ID.
 *
 * @since 1.0.0
 */
function wcsn_order_sync_keys( $order_id ) {
	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return;
	}

	$items = $order->get_items();

	if ( ! $items ) {
		return;
	}

	// based on the order status we need to add or remove keys.
	if ( wcsn_order_has_products( $order_id ) ) {
		// If processing or completed then we need to add keys.
		if ( $order->has_status( array( 'processing', 'completed' ) ) ) {
			wcsn_order_add_keys( $order_id );
		} else {
			wcsn_order_remove_keys( $order_id );
		}
	}
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
			html_entity_decode( $product->get_formatted_name() )
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

