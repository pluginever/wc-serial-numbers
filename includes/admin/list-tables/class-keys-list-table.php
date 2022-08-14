<?php

namespace PluginEver\WooCommerceSerialNumbers\Admin\List_Tables;

// don't call the file directly.
use PluginEver\WooCommerceSerialNumbers\Helper;
use PluginEver\WooCommerceSerialNumbers\Keys;

defined( 'ABSPATH' ) || exit();

/**
 * Serial keys admin list table.
 *
 * @since #.#.#
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Keys_List_Table extends List_Table {
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
	public $total_count = 0;

	/**
	 * Sold number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $sold_count = 0;

	/**
	 * Delivered number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $delivered_count = 0;

	/**
	 * Expired number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $expired_count = 0;

	/**
	 * available number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $available_count = 0;

	/**
	 * Inactive number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $active_count = 0;

	/**
	 * Serial_Keys_List_Table constructor.
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
	 * Prepare table data.
	 *
	 * @since #.#.#
	 */
	public function prepare_items() {
		$per_page              = $this->get_items_per_page( 'wc_serial_numbers_keys_per_page' );
		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$status                = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$orderby               = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'order_date';
		$order                 = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$search                = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$product_id            = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : '';
		$order_id              = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : '';
		$customer_id           = isset( $_GET['customer_id'] ) ? absint( $_GET['customer_id'] ) : '';
		$id                    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : '';
		if ( ! empty( $status ) && ! array_key_exists( $status, Keys::get_statuses() ) ) {
			$status = 'available';
		}

		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'order_date' !== $orderby ) {
			$args['orderby'] = $orderby;
		}

		$args = array(
			'per_page'        => $per_page,
			'paged'           => $current_page,
			'orderby'         => $orderby,
			'order'           => $order,
			'status__in'      => $status,
			'product_id__in'  => $product_id,
			'order_id__in'    => $order_id,
			'customer_id__in' => $customer_id,
			'include'         => $id,
			'search'          => $search,
		);

		$this->items           = Keys::query( $args );
		$this->available_count = Keys::query( array_merge( $args, [ 'status__in' => 'available' ] ), true );
		$this->sold_count      = Keys::query( array_merge( $args, [ 'status__in' => 'sold' ] ), true );
		$this->delivered_count = Keys::query( array_merge( $args, [ 'status__in' => 'delivered' ] ), true );
		$this->expired_count   = Keys::query( array_merge( $args, [ 'status__in' => 'expired' ] ), true );
		$this->active_count    = Keys::query( array_merge( $args, [ 'status__in' => 'active' ] ), true );
		$this->total_count     = array_sum( [ $this->available_count, $this->sold_count, $this->delivered_count, $this->expired_count, $this->active_count ] );
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
			case 'delivered':
				$total_items = $this->delivered_count;
				break;
			case 'any':
			default:
				$total_items = $this->total_count;
				break;
		}

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => get_user_option( 'serials_per_page' ),
				'total_pages' => $total_items > 0 ? ceil( $total_items / $per_page ) : 0,
			)
		);

	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		esc_html_e( 'No keys found.', 'wc-serial-numbers' );
	}


	/**
	 * Show the search field
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @since 1.0.0
	 * @return void
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
	 * @since 1.0.0
	 * @return array $views All the views sellable
	 */
	public function get_views() {
		$current         = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$available_count = '&nbsp;<span class="count">(' . $this->available_count . ')</span>';
		$total_count     = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$sold_count      = '&nbsp;<span class="count">(' . $this->sold_count . ')</span>';
		$delivered_count = '&nbsp;<span class="count">(' . $this->delivered_count . ')</span>';
		$url             = admin_url( 'admin.php?page=wc-serial-numbers' );
		$views           = array(
			'all'       => sprintf( '<a href="%s" title="%s" %s>%s</a>', remove_query_arg( 'status', $url ), __( 'All keys.', 'wc-serial-numbers' ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'wc-serial-numbers' ) . $total_count ),
			'available' => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'available', $url ), __( 'Available for sell.', 'wc-serial-numbers' ), $current === 'available' ? ' class="current"' : '', __( 'Available', 'wc-serial-numbers' ) . $available_count ),
			'sold'      => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'sold', $url ), __( 'Sold but not sent to customers yet.', 'wc-serial-numbers' ), $current === 'sold' ? ' class="current"' : '', __( 'Sold', 'wc-serial-numbers' ) . $sold_count ),
			'delivered' => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'delivered', $url ), __( 'Delivered to customers.', 'wc-serial-numbers' ), $current === 'delivered' ? ' class="current"' : '', __( 'Delivered', 'wc-serial-numbers' ) . $delivered_count ),
		);

		if ( Helper::is_software_support_enabled() ) {
			$expired_count    = '&nbsp;<span class="count">(' . $this->expired_count . ')</span>';
			$active_count     = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
			$views['active']  = sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'active', $url ), __( 'Active keys.', 'wc-serial-numbers' ), $current === 'active' ? ' class="current"' : '', __( 'Active', 'wc-serial-numbers' ) . $active_count );
			$views['expired'] = sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'expired', $url ), __( 'Expired keys.', 'wc-serial-numbers' ), $current === 'expired' ? ' class="current"' : '', __( 'Expired', 'wc-serial-numbers' ) . $expired_count );
		}

		return $views;
	}

	/**
	 * Adds the order and product filters to the licenses list.
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( $which === 'top' ) {
			echo '<div class="alignleft actions">';
			$this->order_dropdown();
			$this->product_dropdown();
			$this->customer_dropdown();
			submit_button( __( 'Filter', 'wc-serial-numbers' ), '', 'filter-action', false );
			echo '</div>';
		}
	}

	/**
	 * Process bulk action.
	 *
	 * @param string $doaction Action name.
	 *
	 * @since #.#.#
	 */
	public function process_bulk_actions( $doaction ) {
		if ( $doaction ) {
			if ( isset( $_REQUEST['id'] ) ) {
				$ids      = wp_parse_id_list( $_REQUEST['id'] );
				$doaction = ( - 1 !== $_REQUEST['action'] ) ? $_REQUEST['action'] : $_REQUEST['action2']; // phpcs:ignore
			} elseif ( isset( $_REQUEST['ids'] ) ) {
				$ids = array_map( 'absint', $_REQUEST['ids'] );
			} elseif ( wp_get_referer() ) {
				wp_safe_redirect( wp_get_referer() );
				exit;
			}

			foreach ( $ids as $id ) { // Check the permissions on each.
				$key = Keys::get( $id );
				if ( ! $key ) {
					continue;
				}
				switch ( $doaction ) {
					case 'delete':
						$key->delete();
						break;
					case 'set_available':
						$key->set_status('available');
						$key->save();
						break;
				}
			}

			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		parent::process_bulk_actions( $doaction );
	}

	/**
	 * Get bulk actions
	 *
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'set_available' => __( 'Set to "Available"', 'wc-serial-numbers' ),
			'delete'        => __( 'Delete', 'wc-serial-numbers' ),
		);
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'key'      => __( 'Key', 'wc-serial-numbers' ),
			'product'  => __( 'Product', 'wc-serial-numbers' ),
			'order'    => __( 'Order', 'wc-serial-numbers' ),
			'customer' => __( 'Customer', 'wc-serial-numbers' ),
		);

		if ( Helper::is_software_support_enabled() ) {
			$columns['activation']  = __( 'Activation', 'wc-serial-numbers' );
			$columns['valid_for']   = __( 'Validity', 'wc-serial-numbers' );
			$columns['expire_date'] = __( 'Expire Date', 'wc-serial-numbers' );
		}

		$columns['order_date'] = __( 'Order Date', 'wc-serial-numbers' );
		$columns['status']     = __( 'Status', 'wc-serial-numbers' );

		return apply_filters( 'wc_serial_numbers_keys_table_columns', $columns );
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
			'customer'    => array( 'customer', false ),
			'activation'  => array( 'activation_limit', false ),
			'expire_date' => array( 'expire_date', false ),
			'valid_for'   => array( 'valid_for', false ),
			'status'      => array( 'status', false ),
			'order_date'  => array( 'order_date', false ),
		);

		return apply_filters( 'wc_serial_numbers_keys_table_sortable_columns', $sortable_columns );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return string Name of the primary column.
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
	 * Display key.
	 *
	 * @param $item
	 *
	 * @since #.#.#
	 */
	protected function column_key( $item ) {
		$edit_url          = add_query_arg( [ 'edit' => $item->id ], admin_url( 'admin.php?page=wc-serial-numbers' ) );
		$delete_url        = add_query_arg( [ 'id' => $item->id, 'action' => 'delete' ], admin_url( 'admin.php?page=wc-serial-numbers' ) );
		$actions['id']     = sprintf( __( 'ID: %d', 'wc-serial-numbers' ), $item->id );
		$actions['show']   = sprintf( '<a class="wsn_decrypt_key" href="#" data-key="%s">%s</a>', $item->key, __( 'Show', 'wc-serial-numbers' ) );
		$actions['edit']   = sprintf( '<a href="%1$s">%2$s</a>', $edit_url, __( 'Edit', 'wc-serial-numbers' ) );
		$actions['delete'] = sprintf( '<a href="%1$s">%2$s</a>', $delete_url, __( 'Delete', 'wc-serial-numbers' ) );

		return sprintf( '<code class="wsn-key %1$s">%2$s</code> %3$s', 'encrypted', $item->key, $this->row_actions( $actions ) );
	}


	protected function column_product( $item ) {
		$product     = wc_get_product( $item->product_id );
		$post_parent = wp_get_post_parent_id( $item->product_id );
		$post_id     = $post_parent ? $post_parent : $item->product_id;

		return empty( $item->product_id ) || empty( $product ) ? '&mdash;' : sprintf( '<a href="%s" target="_blank">#%d - %s</a>', get_edit_post_link( $post_id ), $product->get_id(), $product->get_formatted_name() );
	}

	protected function column_order( $item ) {
		return ! empty( $item->order_id ) ? sprintf( '<a href="%s">#%s</a>', get_edit_post_link( $item->order_id ), $item->order_id ) : '&mdash;';
	}

	protected function column_customer( $item ) {
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
	}

	protected function column_activation( $item ) {
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
	}

	protected function column_valid_for( $item ) {
		return ! empty( $item->valid_for ) ? sprintf( _n( '<b>%s</b> Day <br><small>After purchase</small>', '<b>%s</b> Days <br><small>After purchase</small>', $item->valid_for, 'wc-serial-numbers' ), number_format_i18n( $item->valid_for ) ) : __( 'Lifetime', 'wc-serial-numbers' );
	}

	protected function column_order_date( $item ) {
		return ! empty( $item->order_date ) && '0000-00-00 00:00:00' !== $item->order_date ? date( get_option( 'date_format' ), strtotime( $item->order_date ) ) : '&mdash;';

	}

	protected function column_status( $item ) {
		return sprintf( "<span class='serial-key-status %s'>%s</span>", sanitize_html_class( $item->status ), ucfirst( $item->status ) );
	}
}
