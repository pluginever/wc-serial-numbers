<?php

namespace Pluginever\WCSerialNumbers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class Single_List_Table extends \WP_List_Table {

	/** Class constructor */
	public function __construct() {

		$GLOBALS['hook_suffix'] = null;

		parent::__construct( array(
			'singular' => __( 'Serial Number', 'wc-serial-numbers' ),
			'plural'   => __( 'Serial Numbers', 'wc-serial-numbers' ),
			'ajax'     => false,
		) );

	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return void
	 */

	public function prepare_items() {

		$per_page = wsn_get_settings( 'wsn_rows_per_page', 15, 'wsn_general_settings' );

		$columns     = $this->get_columns();
		$sortable    = $this->get_sortable_columns();
		$data        = $this->table_data();
		$perPage     = $per_page;
		$currentPage = $this->get_pagenum();
		$totalItems  = count( $data );
		$hidden      = $this->get_hidden_columns();

		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page'    => $perPage
		) );

		$data                  = array_slice( $data, ( ( $currentPage - 1 ) * $perPage ), $perPage );
		$this->items           = $data;
		$this->_column_headers = array( $columns, $hidden, $sortable );
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'serial_numbers' => __( 'Serial Numbers', 'wc-serial-numbers' ),
			'variation'      => __( 'Variation', 'wc-serial-numbers' ),
			'deliver_times'  => __( 'Used/ Deliver Times', 'wc-serial-numbers' ),
			'max_instance'   => __( 'Max. Instance', 'wc-serial-numbers' ),
			'validity'       => __( 'Validity', 'wc-serial-numbers' ),
		);

		return $columns;
	}


	/**
	 * Define the sortable columns
	 *

	 */
	public function get_sortable_columns() {

		$shortable = array();

		return $shortable;
	}

	/**
	 * Get the table data
	 *
	 * @return array
	 *
	 */
	private function table_data() {

		$product = ! empty( get_query_var( 'single_list_post_id' ) ) ? get_query_var( 'single_list_post_id' ) : '';


		$data = array();

		if ( ! empty( $product ) ) {

			$serial_numbers = wsn_get_available_numbers( $product );

			foreach ( $serial_numbers as $serial_number_id ) {

				$variation          = get_post_meta( $serial_number_id, 'variation', true );
				$image_license      = get_post_meta( $serial_number_id, 'image_license', true );
				$deliver_times      = get_post_meta( $serial_number_id, 'deliver_times', true );
				$used_deliver_times = get_post_meta( $serial_number_id, 'used', true );
				$max_instance       = get_post_meta( $serial_number_id, 'max_instance', true );
				$validity           = get_post_meta( $serial_number_id, 'validity', true );


				$data[] = array(
					'ID'             => $serial_number_id,
					'serial_numbers' => sprintf( '%s <br> <img src="%s" class="ever-thumbnail-small">', get_the_title( $serial_number_id ), $image_license ),
					'variation'      => empty( $variation ) ? __( 'Main Product', 'wc-serial-numbers' ) : get_the_title( $variation ),
					'deliver_times'  => empty( $deliver_times ) ? '∞' : $used_deliver_times . '/' . $deliver_times,
					'max_instance'   => empty( $max_instance ) ? '∞' : $max_instance,
					'validity'       => empty( $validity ) ? '∞' : $validity,
				);

			}
		}

		return $data;
	}

	function get_pagenum() {

		$paged =  !empty(get_query_var( 'wsn_product_edit_paged' )) ? get_query_var( 'wsn_product_edit_paged' ) : 1;

		return $paged;

	}

	public function get_hidden_columns() {

		$hidden = array( 'ID', 'product_id' );

		return $hidden;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  array $item Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'ID':
			case 'serial_numbers':
			case 'variation':
			case 'deliver_times':
			case 'max_instance':
			case 'validity':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}


	function column_serial_numbers( $item ) {

		$actions = array(

			'edit' => '<a href="' . add_query_arg( array(
					'type'          => 'manual',
					'row_action'    => 'edit',
					'serial_number' => $item['ID'],
				), WPWSN_ADD_SERIAL_PAGE ) . '">' . __( 'Edit', 'wc-serial-numbers' ) . '</a>',

			'delete' => '<a href="' . add_query_arg( array(
					'type'          => 'manual',
					'row_action'    => 'delete',
					'serial_number' => $item['ID'],
				), WPWSN_SERIAL_INDEX_PAGE ) . '">' . __( 'Delete', 'wc-serial-numbers' ) . '</a>',

		);

		return sprintf( '%1$s %2$s', $item['serial_numbers'], $this->row_actions( $actions ) );
	}

	/**
	 * Display only the top table nav
	 *
	 * @param string $which
	 *
	 */

	function display_tablenav( $which ) {

		if ( 'bottom' === $which ) {
			return;
		}

		if ( 'top' === $which ) {
			return;
		}

	}


}
