<?php
/**
 * Essential functions for the plugin.
 *
 * @since 1.0.0
 * @package WooCommerceSerialNumbers
 */

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
}
