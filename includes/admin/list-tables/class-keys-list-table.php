<?php

namespace WooCommerceSerialNumbers\Admin\List_Tables;

use WooCommerceSerialNumbers\Helper;
use WooCommerceSerialNumbers\Key;

defined( 'ABSPATH' ) || exit;

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
	public $total_count;

	/**
	 * Sold number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $sold_count;

	/**
	 * Refunded number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $refunded_count;

	/**
	 * Expired number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $expired_count;

	/**
	 * Expired number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $failed_count;

	/**
	 * Cancelled number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $cancelled_count;

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
	public $inactive_count;

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
	 * Retrieve all the data for all the discount codes
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {
		$per_page              = $this->get_items_per_page( 'wcsn_keys_per_page' );
		$columns               = $this->get_columns();
		$hidden                = get_hidden_columns( $this->screen );
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$page                  = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$orderby               = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'order_date'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order                 = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status                = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search                = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$product_id            = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_id              = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id                    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$args = array(
			'per_page'   => $per_page,
			'page'       => $page,
			'orderby'    => $orderby,
			'order'      => $order,
			'status'     => $status,
			'product_id' => $product_id,
			'order_id'   => $order_id,
			'include'    => $id,
			'search'     => $search,
		);

		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'order_date' !== $orderby ) {
			$args['orderby'] = $orderby;
		}
		$this->items = Key::query( $args );
		$count_query = array_merge( $args, [ 'count' => true ] );

		$this->available_count = Key::query( array_merge( $count_query, [ 'status' => 'available' ] ) );
		$this->sold_count      = Key::query( array_merge( $count_query, [ 'status' => 'sold' ] ) );
		$this->refunded_count  = Key::query( array_merge( $count_query, [ 'status' => 'refunded' ] ) );
		$this->cancelled_count = Key::query( array_merge( $count_query, [ 'status' => 'cancelled' ] ) );
		$this->failed_count    = Key::query( array_merge( $count_query, [ 'status' => 'failed' ] ) );
		$this->expired_count   = Key::query( array_merge( $count_query, [ 'status' => 'expired' ] ) );
		$this->inactive_count  = Key::query( array_merge( $count_query, [ 'status' => 'inactive' ] ) );
		$this->total_count     = array_sum(
			[
				$this->available_count,
				$this->sold_count,
				$this->refunded_count,
				$this->cancelled_count,
				$this->failed_count,
				$this->expired_count,
				$this->inactive_count,
			]
		);

		switch ( $status ) {
			case 'available':
				$this->total_count = $this->available_count;
				break;
			case 'sold':
				$this->total_count = $this->sold_count;
				break;
			case 'refunded':
				$this->total_count = $this->refunded_count;
				break;
			case 'cancelled':
				$this->total_count = $this->cancelled_count;
				break;
			case 'failed':
				$this->total_count = $this->failed_count;
				break;
			case 'expired':
				$this->total_count = $this->expired_count;
				break;
			case 'inactive':
				$this->total_count = $this->inactive_count;
				break;
			case 'any':
			default:
				$total_items = $this->total_count;
				break;
		}

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
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
	 * Retrieve the view types
	 *
	 * @since 1.0.0
	 * @return array $views All the views sellable
	 */
	public function get_views() {
		$current         = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$available_count = '&nbsp;<span class="count">(' . $this->available_count . ')</span>';
		$total_count     = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$sold_count      = '&nbsp;<span class="count">(' . $this->sold_count . ')</span>';
		$refunded_count  = '&nbsp;<span class="count">(' . $this->refunded_count . ')</span>';
		$cancelled_count = '&nbsp;<span class="count">(' . $this->cancelled_count . ')</span>';
		$failed_count    = '&nbsp;<span class="count">(' . $this->failed_count . ')</span>';
		$expired_count   = '&nbsp;<span class="count">(' . $this->expired_count . ')</span>';
		$inactive_count  = '&nbsp;<span class="count">(' . $this->inactive_count . ')</span>';
		$url             = admin_url( 'admin.php?page=wc-serial-numbers' );
		$views           = array(
			'all'       => sprintf( '<a href="%s" title="%s" %s>%s</a>', remove_query_arg( 'status', $url ), __( 'All serial numbers', 'wc-serial-numbers' ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'wc-serial-numbers' ) . $total_count ),
			'available' => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'available', $url ), __( 'Available for sell', 'wc-serial-numbers' ), $current === 'available' ? ' class="current"' : '', __( 'Available', 'wc-serial-numbers' ) . $available_count ),
			'sold'      => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'sold', $url ), __( 'Sold & active serial numbers', 'wc-serial-numbers' ), $current === 'sold' ? ' class="current"' : '', __( 'Sold', 'wc-serial-numbers' ) . $sold_count ),
			'refunded'  => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'refunded', $url ), __( 'Refunded serial numbers', 'wc-serial-numbers' ), $current === 'refunded' ? ' class="current"' : '', __( 'Refunded', 'wc-serial-numbers' ) . $refunded_count ),
			'cancelled' => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'cancelled', $url ), __( 'Cancelled serial numbers', 'wc-serial-numbers' ), $current === 'cancelled' ? ' class="current"' : '', __( 'Cancelled', 'wc-serial-numbers' ) . $cancelled_count ),
			'expired'   => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'expired', $url ), __( 'Expired serial numbers', 'wc-serial-numbers' ), $current === 'expired' ? ' class="current"' : '', __( 'Expired', 'wc-serial-numbers' ) . $expired_count ),
			'failed'    => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'failed', $url ), __( 'Expired serial numbers', 'wc-serial-numbers' ), $current === 'failed' ? ' class="current"' : '', __( 'Failed', 'wc-serial-numbers' ) . $failed_count ),
			'inactive'  => sprintf( '<a href="%s" title="%s" %s>%s</a>', add_query_arg( 'status', 'inactive', $url ), __( 'Inactive serial numbers', 'wc-serial-numbers' ), $current === 'inactive' ? ' class="current"' : '', __( 'Inactive', 'wc-serial-numbers' ) . $inactive_count ),
		);

		return $views;
	}

	/**
	 * Adds the order and product filters to the licenses list.
	 *
	 * @param string $which The location of the filters.
	 */
	protected function extra_tablenav( $which ) {
		if ( $which === 'top' ) {
			$current        = filter_input( INPUT_GET, 'status', FILTER_SANITIZE_STRING );
			$remove_all_url = wp_nonce_url( add_query_arg( [ 'action' => 'empty_revoked' ] ), 'bulk-serialkeys' );
			echo '<div class="alignleft actions">';
			$this->order_dropdown();
			$this->product_dropdown();
			$this->customer_dropdown();
			submit_button( __( 'Filter', 'wc-serial-numbers' ), '', 'filter-action', false );
			if ( 'revoked' === $current ) {
				echo '&nbsp;<a class="button" href="' . esc_url( $remove_all_url ) . '" onclick="return confirm(' . esc_js( __( 'Are you sure you want to empty the revoked keys?', 'wc-serial-numbers' ) ) . ')">' . __( 'Empty Revoked', 'wc-serial-numbers' ) . '</a>';
			}
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
			'cb'       => '<input type="checkbox" />',
			'key'      => __( 'Key', 'wc-serial-numbers' ),
			'product'  => __( 'Product', 'wc-serial-numbers' ),
			'order'    => __( 'Order', 'wc-serial-numbers' ),
			'customer' => __( 'Customer', 'wc-serial-numbers' ),
		);

		if ( ! Helper::is_software_support_enabled() ) {
			$columns['activation'] = __( 'Activation', 'wc-serial-numbers' );
			$columns['validity']   = __( 'Validity', 'wc-serial-numbers' );
		}

		$columns['expire_date'] = __( 'Expire Date', 'wc-serial-numbers' );
		$columns['order_date']  = __( 'Order Date', 'wc-serial-numbers' );
		$columns['status']      = __( 'Status', 'wc-serial-numbers' );

		return apply_filters( 'wc_serial_numbers_serials_table_columns', $columns );
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

		return apply_filters( 'wc_serial_numbers_serials_table_sortable_columns', $sortable_columns );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
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
	 * @param Key $item Item.
	 *
	 * @since #.#.#
	 */
	protected function column_key( $item ) {
		$edit_url = add_query_arg(
			[
				'action' => 'edit',
				'edit'   => $item->id,
			],
			admin_url( 'admin.php?page=wc-serial-numbers' )
		);
		$del_url  = wp_nonce_url(
			add_query_arg(
				[
					'action' => 'delete',
					'id'     => $item->id,
				],
				admin_url( 'admin.php?page=wc-serial-numbers' )
			),
			'bulk-serialkeys'
		);
		/* translators: %s: serial key id */
		$actions['id']     = sprintf( __( 'ID: %d', 'wc-serial-numbers' ), $item->get_id() );
		$actions['edit']   = sprintf( '<a href="%1$s">%2$s</a>', $edit_url, __( 'Edit', 'wc-serial-numbers' ) );
		$actions['delete'] = sprintf( '<a href="%1$s">%2$s</a>', $del_url, __( 'Delete', 'wc-serial-numbers' ) );

		return sprintf( '<code class="wcsn-serial-key wcsn_copy_text">%1$s</code> %2$s', $item->get_serial_key(), $this->row_actions( $actions ) );
	}

	/**
	 * Display product.
	 *
	 * @param Key $item Key object.
	 *
	 * @since #.#.#
	 */
	protected function column_product( $item ) {
		$product = $item->get_product();
		return $product === null ? '&mdash;' : sprintf( '<a href="%s" target="_blank">#%d - %s</a>', get_edit_post_link( $item->get_product_id() ), $product->get_id(), $product->get_title() );
	}

	/**
	 * Display order.
	 *
	 * @param Key $item Key object.
	 *
	 * @since #.#.#
	 */
	protected function column_order( $item ) {
		$order = $item->get_order();
		return $order !== null ? sprintf( '<a href="%s">#%s</a>', get_edit_post_link( $order->get_id() ), $order->get_id() ) : '&mdash;';
	}

	/**
	 * Display customer.
	 *
	 * @param Key $item Key object.
	 *
	 * @since #.#.#
	 */
	protected function column_customer( $item ) {
		$order    = $item->get_order();
		$customer = $item->get_customer();
		if ( empty( $order ) || empty( $customer ) ) {
			return '&mdash;';
		}

		$actions          = [];
		$actions['email'] = esc_html( $order->get_billing_email() );

		return sprintf(
			'<a href="%1$s">%2$s</a> %3$s',
			esc_attr( get_edit_user_link( $order->get_customer_id() ) ),
			esc_html( $order->get_formatted_billing_full_name() ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Display activation limit.
	 *
	 * @param Key $item Key object.
	 *
	 * @since #.#.#
	 */
	protected function column_activation( $item ) {
		$limit = ! empty( $item->get_activation_limit() ) ? $item->get_activation_limit() : '&infin;';
		$count = (int) $item->get_activation_count();
		$link  = add_query_arg(
			[
				'key_id' => $item->id,
				'page'   => 'wc-serial-numbers-activations',
			],
			admin_url( 'admin.php' )
		);

		$activated = sprintf( '<a href="%s">%s</a>', $link, $count );

		return sprintf( '<b>%s</b> / <b>%s</b>', $activated, $limit );
	}

	/**
	 * Display expire date.
	 *
	 * @param Key $item Key object.
	 *
	 * @since #.#.#
	 */
	protected function column_validity( $item ) {
		return $item->get_validity( 'view' );
	}

	/**
	 * Display order date.
	 *
	 * @param Key $item Key object.
	 *
	 * @since #.#.#
	 */
	protected function column_order_date( $item ) {
		return ! empty( $item->get_order_date() ) ? date( get_option( 'date_format' ), strtotime( $item->get_order_date() ) ) : '&mdash;';
	}

	/**
	 * Display expire date.
	 *
	 * @param Key $item Key object.
	 *
	 * @since #.#.#
	 */
	protected function column_expire_date( $item ) {
		return empty( $item->get_date_expire() ) ? '&mdash;' : $item->get_date_expire( 'view' );
	}

	/**
	 * Render the status column.
	 *
	 * @param Key $item Key object.
	 *
	 * @return string
	 */
	protected function column_status( $item ) {
		return sprintf( "<span class='wcsn-serial-key-status %s'>%s</span>", sanitize_html_class( $item->get_status() ), $item->get_status_label() );
	}

	/**
	 * since 1.0.0
	 *
	 * @param object $item The current item.
	 * @param string $column_name The current column name.
	 *
	 * @return string|void
	 */
	public function column_default( $item, $column_name ) {
		return '&dash;';
	}
}
