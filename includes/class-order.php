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
 * @return Order
 */
class Order {
	/**
	 * Register action and filter hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function init() {
		// add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'validate_checkout' ) );

		// autocomplete.
		// add_action( 'template_redirect', array( __CLASS__, 'maybe_autocomplete_order' ) );

		// add serial numbers.
		// add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'maybe_assign_serial_numbers' ) );
		// add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'maybe_assign_serial_numbers' ) );
		// add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'maybe_assign_serial_numbers' ) );
		// add_action( 'woocommerce_order_status_on-hold', array( __CLASS__, 'maybe_assign_serial_numbers' ) );

		// revoke ordered serial numbers.
		// add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'revoke_serial_numbers' ) );
		// add_action( 'woocommerce_order_status_refunded', array( __CLASS__, 'revoke_serial_numbers' ) );
		// add_action( 'woocommerce_order_status_failed', array( __CLASS__, 'revoke_serial_numbers' ) );
		// add_action( 'woocommerce_order_partially_refunded', array( __CLASS__, 'revoke_serial_numbers' ), 10, 2 );
		//
		// add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'order_print_items' ) );
		// add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'order_print_items' ), 10 );

		// add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'update_order' ) );
		// add_action( 'woocommerce_order_status_completed', array( $this, 'update_order' ) );
		// add_action( 'woocommerce_order_status_changed', array( $this, 'remove_order' ), 10, 3 );
		//
		// add_action( 'woocommerce_order_partially_refunded', array( $this, 'order_partially_refunded' ), 10, 2 );
		// add_action( 'woocommerce_order_fully_refunded', array( $this, 'order_fully_refunded' ), 10, 2 );
		//
		// add_action( 'woocommerce_refund_deleted', array( $this, 'refund_deleted' ), 10, 2 );
		// add_action( 'woocommerce_delete_order_items', array( $this, 'delete_order' ) );
		// add_action( 'woocommerce_delete_order', array( $this, 'delete_order' ) );
		// add_action( 'woocommerce_trash_order', array( $this, 'delete_order' ) );
		//
		// add_action( 'woocommerce_before_delete_order_item', array( $this, 'delete_order_item' ) );
		// add_action( 'wp_trash_post', array( $this, 'trash_post' ) );
		// add_action( 'untrashed_post', array( $this, 'untrashed_post' ) );
		// add_action( 'edit_post', array( $this, 'edit_post' ), 10, 2 );
		// add_action( 'woocommerce_email_before_order_table', array( $this, 'email_license_keys' ), 10, 3 );

		add_filter( 'wc_serial_numbers_get_keys_from_source_stock', [ __CLASS__, 'get_keys_from_stock' ], 10, 3 );
	}

	public static function get_keys_from_stock( $keys, $line_item, $order ) {
		if ( empty( $line_item['product_id'] ) ) {
			return $keys;
		}

		$keys = Serial_Keys::query(
			[
				'per_page'       => $line_item['delivery_qty'],
				'product_id__in' => $line_item['product_id'],
				'status'         => 'available',
			]
		);

		return $keys;
	}

}

Order::init();

