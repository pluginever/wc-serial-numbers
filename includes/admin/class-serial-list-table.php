<?php

namespace Pluginever\WCSerialNumbers\Admin;


// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Serial_List_Table extends \WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Serial Number', 'wc-serial-number' ), //singular name of the listed records
			'plural'   => __( 'Serial Numbers', 'wc-serial-number' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?

		] );

	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */

	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$data     = $this->table_data();
		usort( $data, array( &$this, 'sort_data' ) );
		$perPage     = 15;
		$currentPage = $this->get_pagenum();
		$totalItems  = count( $data );
		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page'    => $perPage
		) );
		$data                  = array_slice( $data, ( ( $currentPage - 1 ) * $perPage ), $perPage );
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return Array
	 */
	public function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'serial_numbers' => __( 'Serial Numbers', 'wc-serial-numbers' ),
			'usage_limit'    => __( 'Usage/ Limit', 'wc-serial-numbers' ),
			'expires_on'     => __( 'Expires On', 'wc-serial-numbers' ),
			'product'        => __( 'Product', 'wc-serial-numbers' ),
			'order'          => __( 'Order', 'wc-serial-numbers' ),
			'purchased_on'   => __( 'Purchased On', 'wc-serial-numbers' ),
		);

		return $columns;
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return Array
	 */
	public function get_hidden_columns() {
		return array(

		);
	}

	/**
	 * Define the sortable columns
	 *
	 * @return Array
	 */
	public function get_sortable_columns() {
		return array( 'serial_numbers' => array( 'serial_numbers', false ) );
	}

	/**
	 * Get the table data
	 *
	 * @return Array
	 */
	private function table_data() {
		$data = array();

		$posts = get_posts( [ 'post_type' => 'serial_number', 'posts_per_page' => -1 ] );

		foreach ( $posts as $post ) {
			setup_postdata( $post );
			$usage_limit  = get_post_meta( $post->ID, 'usage_limit', true );
			$expires_on   = get_post_meta( $post->ID, 'expires_on', true );
			$product      = get_post_meta( $post->ID, 'product', true );
			$order        = get_post_meta( $post->ID, 'order', true );
			$purchased_on = get_post_meta( $post->ID, 'purchased_on', true );
			$data[]       = [
				'ID'             => $post->ID,
				'serial_numbers' => get_the_title( $post->ID ),
				'usage_limit'    => empty( $usage_limit ) ? '∞' : $usage_limit,
				'expires_on'     => empty( $expires_on ) ? '∞' : $expires_on,
				'product'        => '<a href="' . get_the_permalink( $product ) . '">' . get_the_title( $product ) . '</a>',
				'order'          => empty( $order ) ? '-' : $order,
				'purchased_on'   => empty( $purchased_on ) ? '-' : $order,
			];
		}

		return $data;
	}


	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  Array $item Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'ID':
			case 'serial_numbers':
			case 'usage_limit':
			case 'expires_on':
			case 'product':
			case 'order':
			case 'purchased_on':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['serial_numbers']
		);
	}

	function column_serial_numbers( $item ) {
		$actions = array(
			'edit'   => sprintf( '<a href="?page=%s&action=%s&serial_number=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['ID'] ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&serial_number=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['ID'] ),
		);

		return sprintf( '%1$s %2$s', $item['serial_numbers'], $this->row_actions( $actions ) );
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @return Mixed
	 */
	private function sort_data( $a, $b ) {
		// Set defaults
		$orderby = 'serial_numbers';
		$order   = 'asc';

		// If orderby is set, use this as the sort column
		if ( ! empty( $_GET['orderby'] ) ) {
			$orderby = $_GET['orderby'];
		}
		// If order is set use this as the order
		if ( ! empty( $_GET['order'] ) ) {
			$order = $_GET['order'];
		}

		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );
		if ( $order === 'asc' ) {
			return $result;
		}

		return - $result;
	}

}
