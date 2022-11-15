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
		parent::__construct( array(
			'singular' => __( 'Serial', 'wc-serial-numbers' ),
			'plural'   => __( 'Serials', 'wc-serial-numbers' ),
			'ajax'     => false,
		) );
	}



	/**
	 * Retrieve all the data for all the discount codes
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {
		$per_page   = $this->get_items_per_page( 'serials_per_page', $this->per_page );
		$page       = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$orderby    = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'order_date';
		$order      = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$status     = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : '';
		$order_id   = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : '';
		$id         = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : '';

		$args = array(
			'per_page'   => $per_page,
			'page'       => $page,
			'orderby'    => $orderby,
			'order'      => $order,
			'status'     => $status,
			'product_id' => $product_id,
			'order_id'   => $order_id,
			'include'    => $id,
			'search'     => $search
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
		$this->total_count     = array_sum( [
			$this->available_count,
			$this->sold_count,
			$this->refunded_count,
			$this->cancelled_count,
			$this->failed_count,
			$this->expired_count,
			$this->inactive_count,
		] );

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

		if ( ! Helper::is_software_support_enabled() ) {
			$columns['activation'] = __( 'Activation', 'wc-serial-numbers' );
			$columns['validity']   = __( 'Validity', 'wc-serial-numbers' );
		}

		return apply_filters( 'wc_serial_numbers_serials_table_columns', $columns );
	}

	/**
	 * since 1.0.0
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
	 * since 1.0.0
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string|void
	 */
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'key':
				$actions           = array();
				$actions['id']     = sprintf( __( 'ID: %d', 'wc-serial-numbers' ), $item->id );
				$actions['show']   = sprintf( '<a data-serial-id="%d" data-nonce="%s" class="wc-serial-numbers-decrypt-key"   href="#">%s</a>', $item->id, wp_create_nonce( 'wc_serial_numbers_decrypt_key' ), __( 'Show', 'wc-serial-numbers' ) );
				$actions['edit']   = sprintf( '<a href="%1$s">%2$s</a>', add_query_arg( [
					'action' => 'edit',
					'id'     => $item->id
				], admin_url( 'admin.php?page=wc-serial-numbers' ) ), __( 'Edit', 'wc-serial-numbers' ) );
				$actions['delete'] = sprintf( '<a href="%1$s">%2$s</a>', add_query_arg( [
					'action' => 'delete',
					'id'     => $item->id
				], admin_url( 'admin.php?page=wc-serial-numbers' ) ), __( 'Delete', 'wc-serial-numbers' ) );
				$spinner           = sprintf( '<img class="serial-spinner" style="display: none;" src="%s"/>', admin_url( 'images/loading.gif' ) );
				$class             = 'encrypted';
				$serial_key        = '';

				if ( ! wc_serial_numbers_validate_boolean( get_option( 'wc_serial_numbers_hide_serial_number' ) ) ) {
					$class      = 'decrypted';
					$serial_key = apply_filters( 'wc_serial_numbers_maybe_decrypt', $item->serial_key );
					unset( $actions['show'] );
				}

				return sprintf( '<code class="serial-key %1$s">%2$s</code> %3$s%4$s', $class, $serial_key, $spinner, $this->row_actions( $actions ) );

				break;

			case 'product':
				$product     = wc_get_product( $item->product_id );
				$post_parent = wp_get_post_parent_id( $item->product_id );
				$post_id     = $post_parent ? $post_parent : $item->product_id;

				return empty( $item->product_id ) || empty( $product ) ? '&mdash;' : sprintf( '<a href="%s" target="_blank">#%d - %s</a>', get_edit_post_link( $post_id ), $product->get_id(), $product->get_formatted_name() );
				break;

			case 'order':
				return ! empty( $item->order_id ) ? sprintf( '<a href="%s">#%s</a>', get_edit_post_link( $item->order_id ), $item->order_id ) : '&mdash;';
				break;

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

				break;

			case 'activation':
				$limit = ! empty( $item->activation_limit ) ? $item->activation_limit : __( 'Unlimited', 'wc-serial-numbers' );
				$count = intval( $item->activation_count );
				$link  = add_query_arg( [
					'key_id' => $item->id,
					'page'   => 'serial-numbers-activations'
				], admin_url( 'admin.php' ) );

				$activated = sprintf( '<a href="%s">%s</a>', $link, $count );

				return sprintf( '<b>%s</b> / <b>%s</b>', $activated, $limit );
				break;

			case 'validity':

				return ! empty( $item->validity ) ? sprintf( _n( '<b>%s</b> Day <br><small>After purchase</small>', '<b>%s</b> Days <br><small>After purchase</small>', $item->validity, 'wc-serial-numbers' ), number_format_i18n( $item->validity ) ) : __( 'Lifetime', 'wc-serial-numbers' );

				break;

			case 'status':
				return sprintf( "<span class='serial-key-status %s'>%s</span>", sanitize_html_class( $item->status ), ucfirst( $item->status ) );

				break;

			case 'expire_date':
				return ! empty( $item->expire_date ) && '0000-00-00 00:00:00' != $item->expire_date ? date( get_option( 'date_format' ), strtotime( $item->expire_date ) ) : '&mdash;';

				break;

			case 'order_date':
				return ! empty( $item->order_date ) && '0000-00-00 00:00:00' != $item->order_date ? date( get_option( 'date_format' ), strtotime( $item->order_date ) ) : '&mdash;';

				break;

			default:
				$column = isset( $item->$column_name ) ? $item->$column_name : '&mdash;';

				return apply_filters( 'wc_serial_numbers_serials_table_column_content', $column, $item, $column_name );
		}

	}
}
