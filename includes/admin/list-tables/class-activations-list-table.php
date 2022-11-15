<?php

namespace WooCommerceSerialNumbers\Admin\List_Tables;

use WooCommerceSerialNumbers\Activation;

defined( 'ABSPATH' ) || exit;

class Activations_List_Table extends List_Table {
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
	 * Serial_Keys_List_Table constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Activation', 'wc-serial-numbers' ),
				'plural'   => __( 'Activations', 'wc-serial-numbers' ),
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
		$per_page              = $this->get_items_per_page( 'wcsn_activations_per_page' );
		$columns               = $this->get_columns();
		$hidden                = get_hidden_columns( $this->screen );
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$orderby               = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'order_date'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order                 = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search                = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$product_id            = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_id              = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$customer_id           = isset( $_GET['customer_id'] ) ? absint( $_GET['customer_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id                    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'order_date' !== $orderby ) {
			$args['orderby'] = $orderby;
		}

		$args = array(
			'per_page'        => $per_page,
			'paged'           => $current_page,
			'orderby'         => $orderby,
			'order'           => $order,
			'product_id__in'  => $product_id,
			'order_id__in'    => $order_id,
			'customer_id__in' => $customer_id,
			'include'         => $id,
			'search'          => $search,
		);

		$this->items       = Activation::query( $args );
		$this->total_count = Activation::query( $args, true );

		$this->set_pagination_args(
			array(
				'total_items' => $this->total_count,
				'per_page'    => $per_page,
				'total_pages' => $this->total_count > 0 ? ceil( $this->total_count / $per_page ) : 0,
			)
		);

	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		esc_html_e( 'No activations found.', 'wc-serial-numbers' );
	}

	/**
	 * Retrieve the view types
	 *
	 * @since 1.0.0
	 * @return array $views All the views sellable
	 */
	public function get_views() {
		return array();
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
			'delete' => __( 'Delete', 'wc-serial-numbers' ),
		);
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'instance'        => __( 'Instance', 'wc-serial-numbers' ),
			'key_id'          => __( 'Key ID', 'wc-serial-numbers' ),
			'platform'        => __( 'Platform', 'wc-serial-numbers' ),
			'date_activation' => __( 'Activation Time', 'wc-serial-numbers' ),
			'status'          => __( 'Status', 'wc-serial-numbers' ),
		);

		return apply_filters( 'wc_serial_numbers_activations_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'instance'        => array( 'instance', false ),
			'key_id'          => array( 'key_id', false ),
			'platform'        => array( 'platform', false ),
			'date_activation' => array( 'date_activation', false ),
			'status'          => array( 'status', false ),
		);

		return apply_filters( 'wc_serial_numbers_activations_table_sortable_columns', $sortable_columns );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'instance';
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
	 * Display Instance.
	 *
	 * @param Activation $item The item being displayed.
	 *
	 * @since #.#.#
	 */
	protected function column_instance( $item ) {
		$deactivate_url        = add_query_arg( [ 'deactivate' => $item->id ], admin_url( 'admin.php?page=wc-serial-numbers-activations' ) );
		$delete_url            = add_query_arg(
			[
				'id'     => $item->id,
				'action' => 'delete',
			],
			admin_url( 'admin.php?page=wc-serial-numbers-activations' )
		);
		$actions['deactivate'] = sprintf( '<a href="%1$s">%2$s</a>', $deactivate_url, __( 'Deactivate', 'wc-serial-numbers' ) );
		$actions['delete']     = sprintf( '<a href="%1$s">%2$s</a>', $delete_url, __( 'Delete', 'wc-serial-numbers' ) );

		return sprintf( '%1$s %2$s', $item->get_instance( 'view' ), $this->row_actions( $actions ) );
	}

	/**
	 * Display Key ID.
	 *
	 * @param Activation $item The item being displayed.
	 *
	 * @since #.#.#
	 * @return string
	 */
	protected function column_key_id( $item ) {
		$url = add_query_arg( [ 'id' => $item->key_id ], admin_url( 'admin.php?page=wc-serial-numbers' ) );
		return sprintf('<a href="%1$s">#%2$s</a>', esc_url( $url ), $item->get_key_id( 'view' ) );
	}

	/**
	 * Display Platform.
	 *
	 * @param Activation $item The item being displayed.
	 *
	 * @since #.#.#
	 * @return mixed
	 */
	protected function column_platform( $item ) {
		return $item->get_platform( 'view' );
	}

	/**
	 * Display Activation Time.
	 *
	 * @param Activation $item The item being displayed.
	 *
	 * @since #.#.#
	 * @return mixed
	 */
	protected function column_date_activation( $item ) {
		return $item->get_activation_time( 'view' );
	}

	/**
	 * Display Status.
	 *
	 * @param Activation $item The item being displayed.
	 *
	 * @since #.#.#
	 * @return mixed
	 */
	protected function column_status( $item ) {
		return $item->get_status( 'view' );
	}

	/**
	 * since 1.0.0
	 *
	 * @param object $item Item.
	 * @param string $column_name Column name.
	 *
	 * @return string|void
	 */
	function column_default( $item, $column_name ) {
		return '&dash;';
	}
}
