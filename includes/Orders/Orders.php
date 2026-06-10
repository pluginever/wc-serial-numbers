<?php

namespace PluginEver\SerialNumbers\Orders;

use PluginEver\SerialNumbers\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Class Orders.
 *
 * @since   1.0.0
 * @package PluginEver\SerialNumbers\Orders
 */
class Orders extends Component {

	/**
	 * Child components.
	 *
	 * @since 2.4.0
	 * @var array<int|string, class-string>
	 */
	public array $components = array(
		Admin::class,
	);

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'maybe_autocomplete_order' ), 10, 3 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'handle_order_status_changed' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'handle_order_status_changed' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'handle_order_status_changed' ) );
		add_action( 'woocommerce_order_status_changed', array( $this, 'handle_order_status_changed' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'order_email_keys' ), PHP_INT_MAX );
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'order_display_keys' ), 9 );
		add_filter( 'wc_serial_numbers_order_table_columns', array( $this, 'control_order_table_columns' ), 99 );
	}

	/**
	 * Control software related columns
	 *
	 * @param array $columns Order table columns.
	 *
	 * @since 1.2.0
	 * @return mixed
	 */
	public function control_order_table_columns( $columns ) {
		if ( ! wcsn_is_software_support_enabled() ) {
			$software_columns = array( 'activation_email', 'activation_limit', 'expire_date' );
			foreach ( $columns as $key => $label ) {
				if ( in_array( $key, $software_columns, true ) ) {
					unset( $columns[ $key ] );
				}
			}
		}

		return $columns;
	}

	/**
	 * Automatically set the order's status to complete.
	 *
	 * @param string    $new_order_status The new order status.
	 * @param int       $order_id      The order ID.
	 * @param \WC_Order $order        The order object.
	 *
	 * @since 1.4.6
	 * @return string $new_order_status
	 */
	public function maybe_autocomplete_order( $new_order_status, $order_id, $order = null ) {
		// Exit early if the order has no ID, or if the new order status is not 'processing'.
		if ( 'yes' !== get_option( 'wc_serial_numbers_autocomplete_order' ) || 0 === $order_id || 'processing' !== $new_order_status ) {
			return $new_order_status;
		}
		if ( null === $order ) {
			remove_filter( 'woocommerce_payment_complete_order_status', __METHOD__, 10 );
			$order = wc_get_order( $order_id );
			add_filter( 'woocommerce_payment_complete_order_status', __METHOD__, 10, 3 );
		}

		if ( wcsn_order_has_products( $order ) ) {
			$new_order_status = 'completed';
			// Add a note to the order mentioning that the order has been automatically completed by the plugin.
			$order->add_order_note(
				apply_filters(
					'wc_serial_numbers_autocomplete_order_note',
					__( 'Order automatically completed by the Serial Numbers for WooCommerce.', 'wc-serial-numbers' ),
					$order
				)
			);
		}

		return $new_order_status;
	}

	/**
	 * Handle order status changed.
	 *
	 * @param int|\WC_Order $order_id The order ID or WC_Order object.
	 *
	 * @since 1.4.6
	 */
	public function handle_order_status_changed( $order_id ) {
		if ( apply_filters( 'wc_serial_numbers_maybe_manual_delivery', false, $order_id ) ) {
			return;
		}
		wcsn_order_update_keys( $order_id );
	}

	/**
	 * Print ordered serials
	 *
	 * @param \WC_Order $order The order object.
	 *
	 * @since 1.2.0
	 */
	public function order_display_keys( $order ) {
		/**
		 * Filter to allow or disallow displaying keys in order details.
		 *
		 * @param bool $allow Whether to allow or disallow displaying serial numbers in order details.
		 * @param \WC_Order $order The order object.
		 */
		$allow = apply_filters( 'wc_serial_numbers_allow_order_display_keys', $order->has_status( 'completed' ), $order );

		if ( ! $allow || ! wcsn_order_has_products( $order ) ) {
			return;
		}

		wcsn_display_order_keys( $order );
	}

	/**
	 * Order email keys.
	 *
	 * @param \WC_Order $order The order object.
	 *
	 * @since 1.2.0
	 */
	public function order_email_keys( $order ) {
		/**
		 * Filter to allow or disallow sending serial numbers in order emails.
		 *
		 * @param bool $allow Whether to allow or disallow sending serial numbers in order emails.
		 * @param \WC_Order $order The order object.
		 */
		$allow = apply_filters( 'wc_serial_numbers_allow_order_email_keys', $order->has_status( 'completed' ), $order );

		if ( ! $allow || ! wcsn_order_has_products( $order ) ) {
			return;
		}

		wcsn_display_order_keys( $order );
	}
}
