<?php
namespace WooCommerceSerialNumbers\Admin\List_Tables;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Common functionalities of list tables.
 *
 * @since #.#.#
 * @package WooCommerceSerialNumbers
 */
abstract class List_Table extends \WP_List_Table {

	/**
	 * Gets a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 3.1.0
	 *
	 * @return string[] Array of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		$mode = get_user_setting( 'posts_list_mode', 'list' );

		$mode_class = esc_attr( 'table-view-' . $mode );

		return array( 'widefat', 'striped', $mode_class, $this->_args['plural'] );
	}


	/**
	 * Show the search field
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'order_date';
		$order   = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$input_id .= '-search-input';

		if ( ! empty( $orderby ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $orderby ) . '" />';
		}
		if ( ! empty( $order ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $order ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ) ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ) ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Get order dropdown.
	 *
	 * @since #.#.#
	 * @return void
	 */
	public function order_dropdown() {
		$order_id = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		$order    = empty( $order_id ) ? false : wc_get_order( $order_id );
		?>
		<label for="filter-by-order-id" class="screen-reader-text">
			<?php esc_html_e( 'Filter by order', 'wc-serial-numbers' ); ?>
		</label>
		<select class="wcsn_search_order" name="order_id" id="filter-by-order-id" placeholder="<?php echo esc_attr__( 'Filter by order', 'wc-serial-numbers' ); ?>">
			<?php if ( $order ) : ?>
				<option selected="selected" value="<?php echo esc_attr( $order->get_id() ); ?>">
					<?php
					echo esc_html(
						sprintf(
						/* translators: $1: order id, $2: customer name, $3: customer email */
							'#%1$s %2$s <%3$s>',
							$order->get_order_number(),
							$order->get_formatted_billing_full_name(),
							$order->get_billing_email()
						)
					);
					?>
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
		$product    = empty( $product_id ) ? '' : wc_get_product( $product_id );
		?>
		<label for="filter-by-product-id" class="screen-reader-text">
			<?php esc_html_e( 'Filter by product', 'wc-serial-numbers' ); ?>
		</label>
		<select class="wcsn_search_product" name="product_id" id="filter-by-product-id" placeholder="<?php echo esc_attr__( 'Filter by product', 'wc-serial-numbers' ); ?>">
			<?php if ( ! empty( $product ) ) : ?>
				<option selected="selected" value="<?php echo esc_attr( $product_id ); ?>">
					<?php
					echo esc_html(
						sprintf(
							'(#%1$s) %2$s',
							$product->get_id(),
							wp_strip_all_tags( $product->get_formatted_name() )
						)
					);
					?>
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
		$customer    = empty( $customer_id ) ? '' : get_user_by( 'ID', $customer_id );
		?>
		<label for="filter-by-customer-id" class="screen-reader-text">
			<?php esc_html_e( 'Filter by customer', 'wc-serial-numbers' ); ?>
		</label>
		<select class="wcsn_search_customer" name="customer_id" id="filter-by-customer-id" placeholder="<?php echo esc_attr__( 'Filter by customer', 'wc-serial-numbers' ); ?>">
			<?php if ( ! empty( $customer ) ) : ?>
				<option selected="selected" value="<?php echo esc_attr( $customer->get_id() ); ?>">
					<?php
					echo esc_html(
						sprintf(
						/* translators: $1: user nicename, $2: user id, $3: user email */
							'%1$s (#%2$d - %3$s)',
							$customer->user_nicename,
							$customer->ID,
							$customer->user_email
						)
					);
					?>
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
	public function process_bulk_actions( $doaction ) {
		if ( ! empty( $_GET['_wp_http_referer'] ) ) {
			wp_safe_redirect(
				remove_query_arg(
					array(
						'_wp_http_referer',
						'_wpnonce',
						'action2',
						'action',
						'paged',
					),
					wp_unslash( $_SERVER['REQUEST_URI'] )
				)
			);

			exit;
		}
	}
}
