<?php

use PluginEver\WooCommerceSerialNumbers\Entity\Serial_Key;
use PluginEver\WooCommerceSerialNumbers\Serial_Keys;

defined( 'ABSPATH' ) || exit();

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Admin_Serial_Keys_Table extends \WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $per_page = 20;

	/**
	 *
	 * Total number of items
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $total_count;

	/**
	 * Sold number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $sold_count;

	/**
	 * Expired number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $expired_count;

	/**
	 * available number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $available_count;

	/**
	 * Inactive number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $active_count;

	/**
	 * Serial_Numbers_Table constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Serial key', 'wc-serial-numbers' ),
				'plural'   => __( 'Serial keys', 'wc-serial-numbers' ),
				'ajax'     => false,
			)
		);
	}

	/**
	 * Setup the final data for the table
	 *
	 * @return void
	 * @throws Exception
	 * @since 1.0.0
	 */
	function prepare_items() {
		$per_page              = $this->per_page;
		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$data = $this->get_results();

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch ( $status ) {
			case 'available':
				$total_items = $this->available_count;
				break;
			case 'sold':
				$total_items = $this->sold_count;
				break;
			case 'expired':
				$total_items = $this->expired_count;
				break;
			case 'active':
				$total_items = $this->active_count;
				break;
			case 'any':
			default:
				$total_items = $this->total_count;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => get_user_option( 'serials_per_page' ),
				'total_pages' => $total_items > 0 ? ceil( $total_items / (int) get_user_option( 'serials_per_page' ) ) : 0,
			)
		);
	}

	/**
	 * Show the search field
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id; ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id; ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @return array $views All the views sellable
	 * @since 1.0.0
	 */
	public function get_views() {
		$current         = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$available_count = '&nbsp;<span class="count">(' . $this->available_count . ')</span>';
		$total_count     = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$sold_count      = '&nbsp;<span class="count">(' . $this->sold_count . ')</span>';
		$expired_count   = '&nbsp;<span class="count">(' . $this->expired_count . ')</span>';
		$active_count    = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
		$url             = admin_url( 'admin.php?page=wc-serial-numbers' );
		$views           = array(
			'all'       => sprintf( '<a href="%s" title="%s" %s>%s</a>', remove_query_arg( 'status', $url ), __( 'All serial numbers', 'wc-serial-numbers' ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'wc-serial-numbers' ) . $total_count ),
			'available' => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'available', $url ), __( 'Available for sell', 'wc-serial-numbers' ), $current === 'available' ? ' class="current"' : '', __( 'Available', 'wc-serial-numbers' ) . $available_count ),
			'sold'      => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'sold', $url ), __( 'Sold & active serial numbers', 'wc-serial-numbers' ), $current === 'sold' ? ' class="current"' : '', __( 'Sold', 'wc-serial-numbers' ) . $sold_count ),
			'expired'   => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'expired', $url ), __( 'Expired serial numbers', 'wc-serial-numbers' ), $current === 'expired' ? ' class="current"' : '', __( 'Expired', 'wc-serial-numbers' ) . $expired_count ),
			'active'    => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'active', $url ), __( 'Active serial numbers', 'wc-serial-numbers' ), $current === 'active' ? ' class="current"' : '', __( 'Active', 'wc-serial-numbers' ) . $active_count ),
		);

		return $views;
	}

	/**
	 * Get bulk actions
	 *
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'activate'   => __( 'Set to "Available"', 'wc-serial-numbers' ),
			'deactivate' => __( 'Set to "Inactive"', 'wc-serial-numbers' ),
			'delete'     => __( 'Delete', 'wc-serial-numbers' ),
		);

		return $actions;
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'key'         => __( 'Key', 'wc-serial-numbers' ),
			'product'     => __( 'Product', 'wc-serial-numbers' ),
			'order'       => __( 'Order', 'wc-serial-numbers' ),
			'customer'    => __( 'Customer', 'wc-serial-numbers' ),
			'expire_date' => __( 'Expire Date', 'wc-serial-numbers' ),
			'order_date'  => __( 'Order Date', 'wc-serial-numbers' ),
			'status'      => __( 'Status', 'wc-serial-numbers' ),
		);

		// if ( ! wc_serial_numbers_software_support_disabled() ) {
		// $columns['activation'] = __( 'Activation', 'wc-serial-numbers' );
		// $columns['validity']   = __( 'Validity', 'wc-serial-numbers' );
		// }

		return apply_filters( 'wc_serial_numbers_serial_keys_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'key'         => array( 'serial_key', false ),
			'product'     => array( 'product_id', false ),
			'order'       => array( 'order_id', false ),
			'activation'  => array( 'activation_limit', false ),
			'expire_date' => array( 'expire_date', false ),
			'validity'    => array( 'validity', false ),
			'status'      => array( 'status', false ),
			'order_date'  => array( 'order_date', false ),
		);

		return apply_filters( 'wc_serial_numbers_serial_keys_table_sortable_columns', $sortable_columns );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @return string Name of the primary column.
	 * @since 1.0.0
	 * @access protected
	 */
	protected function get_primary_column_name() {
		return 'key';
	}

	/**
	 * since 1.0.0
	 *
	 * @param object $item
	 *
	 * @return string|void
	 */
	protected function column_cb( $item ) {
		return "<input type='checkbox' name='ids[]' id='id_{$item->id}' value='{$item->id}' />";
	}

	/**
	 * since 1.0.0
	 *
	 * @param Serial_key $item
	 * @param string     $column_name
	 *
	 * @return string|void
	 */
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'key':
				$actions           = array();
				$actions['id']     = sprintf( __( 'ID: %d', 'wc-serial-numbers' ), $item->id );
				$actions['show']   = sprintf( '<a class="wcsn-decrypt-key" href="#" data-key="%s">%s</a>', $item->key, __( 'Show', 'wc-serial-numbers' ) );
				$actions['edit']   = sprintf(
					'<a href="%1$s">%2$s</a>',
					add_query_arg(
						[
							'action' => 'edit',
							'id'     => $item->id,
						],
						admin_url( 'admin.php?page=wc-serial-numbers' )
					),
					__( 'Edit', 'wc-serial-numbers' )
				);
				$actions['delete'] = sprintf(
					'<a href="%1$s">%2$s</a>',
					add_query_arg(
						[
							'action' => 'delete',
							'id'     => $item->id,
						],
						admin_url( 'admin.php?page=wc-serial-numbers' )
					),
					__( 'Delete', 'wc-serial-numbers' )
				);
				$class             = 'encrypted';
				$serial_key        = $item->key;

				return sprintf( '<code class="serial-key %1$s">%2$s</code> %3$s', $class, $serial_key, $this->row_actions( $actions ) );

			case 'product':
				$product     = wc_get_product( $item->product_id );
				$post_parent = wp_get_post_parent_id( $item->product_id );
				$post_id     = $post_parent ? $post_parent : $item->product_id;

				return empty( $item->product_id ) || empty( $product ) ? '&mdash;' : sprintf( '<a href="%s" target="_blank">#%d - %s</a>', get_edit_post_link( $post_id ), $product->get_id(), $product->get_formatted_name() );

			case 'order':
				return ! empty( $item->order_id ) ? sprintf( '<a href="%s">#%s</a>', get_edit_post_link( $item->order_id ), $item->order_id ) : '&mdash;';

			case 'customer':
				if ( empty( $item->order_id ) ) {
					return '&mdash;';
				}
				$order = wc_get_order( $item->order_id );
				if ( empty( $order ) || empty( $order->get_id() ) ) {
					return '&mdash;';
				}

				return sprintf(
					'<a href="%s">%s (#%d - %s)</a>',
					get_edit_user_link( $order->get_customer_id() ),
					$order->get_formatted_billing_full_name(),
					$order->get_customer_id(),
					$order->get_billing_email()
				);

			case 'activation':
				$limit = ! empty( $item->activation_limit ) ? $item->activation_limit : __( 'Unlimited', 'wc-serial-numbers' );
				$count = (int) $item->activation_count;
				$link  = add_query_arg(
					[
						'key_id' => $item->id,
						'page'   => 'serial-numbers-activations',
					],
					admin_url( 'admin.php' )
				);

				$activated = sprintf( '<a href="%s">%s</a>', $link, $count );

				return sprintf( '<b>%s</b> / <b>%s</b>', $activated, $limit );

			case 'validity':
				return ! empty( $item->validity ) ? sprintf( _n( '<b>%s</b> Day <br><small>After purchase</small>', '<b>%s</b> Days <br><small>After purchase</small>', $item->validity, 'wc-serial-numbers' ), number_format_i18n( $item->validity ) ) : __( 'Lifetime', 'wc-serial-numbers' );

			case 'status':
				return sprintf( "<span class='serial-key-status %s'>%s</span>", sanitize_html_class( $item->status ), ucfirst( $item->status ) );

			case 'expire_date':
				return ! empty( $item->expire_date ) && '0000-00-00 00:00:00' !== $item->expire_date ? date( get_option( 'date_format' ), strtotime( $item->expire_date ) ) : '&mdash;';

			case 'order_date':
				return ! empty( $item->order_date ) && '0000-00-00 00:00:00' !== $item->order_date ? date( get_option( 'date_format' ), strtotime( $item->order_date ) ) : '&mdash;';

			default:
				$column = isset( $item->$column_name ) ? $item->$column_name : '&mdash;';

				return apply_filters( 'wc_serial_numbers_serial_keys_table_column_content', $column, $item, $column_name );
		}

	}


	/**
	 * Retrieve all the data for all the discount codes
	 *
	 * @return object $get_results Array of all the data for the discount codes
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function get_results() {
		$per_page   = $this->get_items_per_page( 'serials_per_page', $this->per_page );
		$page       = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
		$orderby    = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'order_date';
		$order      = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$status     = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : '';
		$order_id   = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : '';
		$id         = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : '';

		$args = array(
			'per_page'   => $per_page,
			'paged'      => $page,
			'orderby'    => $orderby,
			'order'      => $order,
			'status__in' => $status,
			'product_id' => $product_id,
			'order_id'   => $order_id,
			'include'    => $id,
			'search'     => $search,
		);

		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'order_date' !== $orderby ) {
			$args['orderby'] = $orderby;
		}

		$results               = Serial_Keys::query( $args );
		$this->available_count = Serial_Keys::query( array_merge( $args, [ 'status__in' => 'available' ] ), true );
		$this->sold_count      = Serial_Keys::query( array_merge( $args, [ 'status__in' => 'sold' ] ), true );
		$this->expired_count   = Serial_Keys::query( array_merge( $args, [ 'status__in' => 'expired' ] ), true );
		$this->active_count    = Serial_Keys::query( array_merge( $args, [ 'status__in' => 'active' ] ), true );
		$this->total_count     = array_sum( [ $this->available_count, $this->sold_count, $this->expired_count, $this->active_count ] );

		return $results;
	}
}
