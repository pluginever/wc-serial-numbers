<?php

namespace WooCommerceSerialNumbers\Admin\ListTables;

defined( 'ABSPATH' ) || exit;

// Load WP_List_Table if not loaded.
if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class ListTable.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin\ListTables
 */
class ListTable extends \WP_List_Table {
	/**
	 *
	 * Total number of items
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $total_count = 0;

	/**
	 * Get a request var, or return the default if not set.
	 *
	 * @param string $param Request var name.
	 * @param mixed  $fallback Default value.
	 *
	 * @since 1.4.6
	 *
	 * @return mixed Un-sanitized request var
	 */
	protected function get_request_var( $param = '', $fallback = false ) {
		return isset( $_REQUEST[ $param ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $param ] ) ) : $fallback; // phpcs:ignore WordPress.Security.NonceVerification
	}

	/**
	 * Retrieve the search query string.
	 *
	 * @since 1.4.6
	 * @return string Search query.
	 */
	protected function get_search() {
		return $this->get_request_var( 's', '' );
	}

	/**
	 * Retrieve the order query string.
	 *
	 * @since 1.4.6
	 * @return string Order query.
	 */
	protected function get_order() {
		return $this->get_request_var( 'order', 'DESC' );
	}

	/**
	 * Retrieve the orderby query string.
	 *
	 * @since 1.4.6
	 * @return string Orderby query.
	 */
	protected function get_orderby() {
		return $this->get_request_var( 'orderby', 'date' );
	}

	/**
	 * Retrieve the page query string.
	 *
	 * @since 1.4.6
	 * @return string Page query.
	 */
	protected function get_page() {
		return $this->get_request_var( 'page', '' );
	}

	/**
	 * Retrieve the current page URL.
	 *
	 * @since 1.4.6
	 * @return string Current page URL.
	 */
	protected function get_current_page_url() {
		$page = $this->get_page();

		// Build the base URL.
		return add_query_arg( 'page', $page, admin_url( 'admin.php' ) );
	}

	/**
	 * Show the search field
	 *
	 * @param string $text Label for the search box.
	 * @param string $input_id ID of the search box.
	 *
	 * @since 1.4.6
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $this->get_search() ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';
		$orderby  = $this->get_orderby();
		$order    = $this->get_order();

		if ( ! empty( $orderby ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $orderby ) . '" />';
		}
		if ( ! empty( $order ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $order ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Get order dropdown.
	 *
	 * @since 1.4.6
	 * @return void
	 */
	public function order_dropdown() {
		$order_id = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		$order    = wcsn_get_order_object( $order_id );
		?>
		<label for="filter-by-order-id" class="screen-reader-text">
			<?php esc_html_e( 'Filter by order', 'wc-serial-numbers' ); ?>
		</label>
		<select class="wcsn_search_order" name="order_id" id="filter-by-order-id" data-placeholder="<?php esc_attr_e( 'Filter by order', 'wc-serial-numbers' ); ?>">
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
	 * @since 1.4.6
	 * @return void
	 */
	public function product_dropdown() {
		$product_id = filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT );
		$product    = wcsn_get_product_object( $product_id );
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
	 * @since 1.4.6
	 * @return void
	 */
	public function customer_dropdown() {
		$customer_id = filter_input( INPUT_GET, 'customer_id', FILTER_SANITIZE_NUMBER_INT );
		$customer    = new \WC_Customer( $customer_id );
		?>
		<label for="filter-by-customer-id" class="screen-reader-text">
			<?php esc_html_e( 'Filter by customer', 'wc-serial-numbers' ); ?>
		</label>
		<select class="wcsn_search_customer" name="customer_id" id="filter-by-customer-id">
			<?php if ( $customer->get_id() ) : ?>
				<option selected="selected" value="<?php echo esc_attr( $customer->get_id() ); ?>">
					<?php echo esc_html( sprintf( '%s (%s)', $customer->get_first_name() . ' ' . $customer->get_last_name(), $customer->get_email() ) ); ?>
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
	 * @since 1.4.6
	 */
	public function process_bulk_actions( $doaction ) {
		$referer = wp_get_referer();
	}
}
