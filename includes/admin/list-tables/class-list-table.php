<?php

namespace PluginEver\WooCommerceSerialNumbers\Admin\List_Tables;

use PluginEver\WooCommerceSerialNumbers\Helper;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( '\WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Common functionalities of list tables.
 *
 * @since #.#.#
 * @package PluginEver\WooCommerceSerialNumbers
 */
abstract class List_Table extends \WP_List_Table {

	/**
	 * Get order dropdown.
	 *
	 * @since #.#.#
	 * @return void
	 */
	public function order_dropdown() {
		$order_id = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		$order    = Helper::get_order_object( $order_id );
		?>
		<label for="filter-by-order-id" class="screen-reader-text">
			<?php esc_html_e( 'Filter by order', 'wc-serial-numbers' ); ?>
		</label>
		<select class="wcsn_search_order" name="order_id" id="filter-by-order-id">
			<?php if ( ! empty( $order ) ) : ?>
				<option selected="selected" value="<?php echo esc_attr( $order->get_id() ); ?>">
					<?php echo esc_html( $order->get_formatted_billing_full_name() ); ?>
				</option>
			<?php endif; ?>
		</select>
		<?php
	}

	/**
	 * Get product dropdown.
	 *
	 * @since #.#.#
	 * @return void
	 */
	public function product_dropdown() {
		$product_id = filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT );
		$product    = Helper::get_product_object( $product_id );
		?>
		<label for="filter-by-product-id" class="screen-reader-text">
			<?php esc_html_e( 'Filter by product', 'wc-serial-numbers' ); ?>
		</label>
		<select class="wcsn_search_product" name="product_id" id="filter-by-product-id">
			<?php if ( ! empty( $product ) ) : ?>
				<option selected="selected" value="<?php echo esc_attr( $product->get_id() ); ?>">
					<?php echo esc_html( $product->get_name() ); ?>
				</option>
			<?php endif; ?>
		</select>
		<?php
	}

	/**
	 * Get customer dropdown.
	 *
	 * @since #.#.#
	 * @return void
	 */
	public function customer_dropdown() {
		$customer_id = filter_input( INPUT_GET, 'customer_id', FILTER_SANITIZE_NUMBER_INT );
		$customer    = get_user_by( 'ID', $customer_id );
		?>
		<label for="filter-by-customer-id" class="screen-reader-text">
			<?php esc_html_e( 'Filter by customer', 'wc-serial-numbers' ); ?>
		</label>
		<select class="wcsn_search_customer" name="customer_id" id="filter-by-customer-id">
			<?php if ( ! empty( $customer ) ) : ?>
				<option selected="selected" value="<?php echo esc_attr( $customer->get_id() ); ?>">
					<?php echo esc_html( $customer->get_name() ); ?>
				</option>
			<?php endif; ?>
		</select>
		<?php
	}

	/**
	 * Process bulk action.
	 *
	 * @param string $doaction Action name.
	 *
	 * @since #.#.#
	 */
	public function process_bulk_actions( $doaction ){
		if ( ! empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array(
				'_wp_http_referer',
				'_wpnonce'
			), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			exit;
		}
	}
}
