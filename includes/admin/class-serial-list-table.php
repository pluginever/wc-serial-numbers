<?php

namespace Pluginever\WCSerialNumbers\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class Serial_List_Table extends \WP_List_Table {

	/** Class constructor */
	public function __construct() {

		$GLOBALS['hook_suffix'] = null;

		parent::__construct( [
			'singular' => __( 'Serial Number', 'wc-serial-numbers' ),
			'plural'   => __( 'Serial Numbers', 'wc-serial-numbers' ),
			'ajax'     => false,
		] );

	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return void
	 */

	public function prepare_items() {

		$this->process_bulk_action();
		$per_page = wsn_get_settings( 'wsn_rows_per_page', 15, 'wsn_general_settings' );

		$columns     = $this->get_columns();
		$sortable    = $this->get_sortable_columns();
		$data        = $this->table_data();
		$perPage     = $per_page;
		$currentPage = $this->get_pagenum();
		$totalItems  = count( $data );

		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page'    => $perPage
		) );

		$data                  = array_slice( $data, ( ( $currentPage - 1 ) * $perPage ), $perPage );
		$this->_column_headers = array( $columns, array(), $sortable );
		$this->items           = $data;
	}

	//Process Bulk action
	public function process_bulk_action() {

		// security check!
		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

			$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
			$action = 'bulk-' . $this->_args['plural'];

			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( 'No, Cheating!' );
			}

			$bulk_deletes = ! empty( $_REQUEST['bulk-delete'] ) && is_array( $_REQUEST['bulk-delete'] ) ? array_map( 'intval', $_REQUEST['bulk-delete'] ) : '';

			if ( ! empty( $bulk_deletes ) ) {

				foreach ( $bulk_deletes as $bulk_delete ) {

					$bulk_delete = intval( $bulk_delete );
					$product     = get_post_meta( $bulk_delete, 'product', true );

					if ( current_user_can( 'delete_posts' ) && get_post_status( $bulk_delete ) ) {

						wp_delete_post( $bulk_delete, true );

					}

					do_action( 'wsn_update_notification_on_order_delete', $product );
				}

			}

		}

	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'serial_numbers' => __( 'Serial Numbers', 'wc-serial-numbers' ),
			'product'        => __( 'Product', 'wc-serial-numbers' ),
			'variation'      => __( 'Variation', 'wc-serial-numbers' ),
			'deliver_times'  => __( 'Used/ Deliver Times', 'wc-serial-numbers' ),
			'max_instance'   => __( 'Max. Instance', 'wc-serial-numbers' ),
			'purchaser'      => __( 'Purchaser', 'wc-serial-numbers' ),
			'order'          => __( 'Order', 'wc-serial-numbers' ),
			'purchased_on'   => __( 'Purchased On', 'wc-serial-numbers' ),
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

		$search_query = false;

		$query = array();

		if ( ! empty( $_REQUEST['wsn-filter-table-serial-numbers'] ) && $_REQUEST['wsn-filter-table-serial-numbers'] == 'Filter' ) {

			if ( ! empty( $_REQUEST['filter-product'] ) ) {
				$query['meta_key']   = 'product';
				$query['meta_value'] = intval( $_REQUEST['filter-product'] );
			}

			if ( ! empty( $_REQUEST['filter-serialnumber'] ) ) {
				$query['s'] = sanitize_key( $_REQUEST['filter-serialnumber'] );
			}

		}

		if ( ! empty( $_REQUEST['s'] ) ) {
			$query['s'] = sanitize_key( $_REQUEST['s'] );
		}


		$data = array();

		$posts = wsn_get_serial_numbers( $query );

		foreach ( $posts as $post ) {

			setup_postdata( $post );

			$product            = get_post_meta( $post->ID, 'product', true );
			$variation          = get_post_meta( $post->ID, 'variation', true );
			$image_license      = get_post_meta( $post->ID, 'image_license', true );
			$deliver_times      = get_post_meta( $post->ID, 'deliver_times', true );
			$used_deliver_times = get_post_meta( $post->ID, 'used', true );
			$max_instance       = get_post_meta( $post->ID, 'max_instance', true );
			$validity_type      = get_post_meta( $post->ID, 'validity_type', true );
			$validity           = get_post_meta( $post->ID, 'validity', true );
			$order              = get_post_meta( $post->ID, 'order', true );
			$purchased_on       = '';


			//Order Details
			if ( ! empty( $order ) ) {

				$order_obj = wc_get_order( $order );

				$customer_name  = wsn_get_customer_detail( 'first_name', $order_obj ) . ' ' . wsn_get_customer_detail( 'last_name', $order_obj );
				$customer_email = wsn_get_customer_detail( 'email', $order_obj );
				$purchaser      = $customer_name . '<br>' . $customer_email;

				if ( is_object( $order_obj ) ) {
					$purchased_on = $order_obj->get_data()['date_created'];
				}

			}

			$is_valid = wsn_is_serial_valid( $validity, $validity_type, $purchased_on );

			$valid_style = ! empty( $is_valid ) ? 'style="color:red"' : '';

			$data[] = [
				'ID'             => $post->ID,
				'serial_numbers' => sprintf( '%s <br> <img src="%s" class="ever-thumbnail-small">', get_the_title( $post->ID ), $image_license ),
				'product'        => '<a href="' . get_edit_post_link( $product ) . '">' . get_the_title( $product ) . '</a>',
				'variation'      => empty( $variation ) ? __( 'Main Product', 'wc-serial-numbers' ) : get_the_title( $variation ),
				'deliver_times'  => empty( $deliver_times ) ? '∞' : $used_deliver_times . '/' . $deliver_times,
				'max_instance'   => empty( $max_instance ) ? '∞' : $max_instance,
				'purchaser'      => empty( $purchaser ) ? '-' : $purchaser,
				'order'          => empty( $order ) ? '-' : '<a href="' . get_edit_post_link( $order ) . '">#' . $order . '</a>',
				'purchased_on'   => empty( $purchased_on ) ? '-' : date( 'm-d-Y H:i a', strtotime( $purchased_on ) ),
				'validity'       => empty( $validity ) ? '∞' : sprintf( '<span %s>%s <br> %s </span>', $valid_style, $validity, $is_valid ),
				'product_id'     => empty( $product ) ? '' : $product,
			];

		}

		return $data;
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
			case 'product':
			case 'variation':
			case 'deliver_times':
			case 'max_instance':
			case 'purchaser':
			case 'order':
			case 'purchased_on':
			case 'product_id':
			case 'validity':
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

		$actions ['bulk-delete'] = __( 'Delete', 'wc-serial-numbers' );

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
		return sprintf( '<input type="checkbox" name="bulk-delete[]" value="%d" />', $item['ID'] );
	}

	function column_serial_numbers( $item ) {

		$actions = array(

			'edit' => '<a href="' . add_query_arg( array(
					'type'          => 'manual',
					'row_action'    => 'edit',
					'serial_number' => $item['ID'],
					'product'       => $item['product_id'],
				), WPWSN_ADD_SERIAL_PAGE ) . '">' . __( 'Edit', 'wc-serial-numbers' ) . '</a>',

			'delete' => '<a href="' . add_query_arg( array(
					'type'          => 'manual',
					'row_action'    => 'delete',
					'serial_number' => $item['ID'],
					'product'       => $item['product_id'],
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
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}

		?>

		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if ( $this->has_items() ) { ?>

				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>

				<?php $this->pagination( $which );

				$this->extra_tablenav( $which );
			}

			?>

			<br class="clear"/>

		</div>

		<?php
	}

	/**
	 * Table Filter html
	 *
	 * @since 1.0.0
	 *
	 * @param string $which
	 */

	function extra_tablenav( $which ) {
		echo apply_filters( 'wsn_extra_table_nav', '', 'serial-numbers' );
	}


}
