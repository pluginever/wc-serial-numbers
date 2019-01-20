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

	protected $is_single = false;

	/** Class constructor */
	public function __construct( $post_id = '' ) {

		$GLOBALS['hook_suffix'] = null;

		parent::__construct( [
			'singular' => __( 'Serial Number', 'wc-serial-numbers' ),
			'plural'   => __( 'Serial Numbers', 'wc-serial-numbers' ),
			'ajax'     => false,
		] );

		$this->is_single = $post_id;

	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return void
	 */

	public function prepare_items() {

		$per_page = wsn_get_settings( 'wsn_rows_per_page', 15, 'wsn_general_settings' );

		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$data     = $this->table_data();
		usort( $data, array( &$this, 'sort_data' ) );
		$perPage     = $per_page;
		$currentPage = $this->get_pagenum();
		$totalItems  = count( $data );

		$hidden = $this->get_hidden_columns();

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

		$shortable = [
			'serial_numbers' => array( 'serial_numbers', false ),
			'purchaser'      => array( 'purchaser', false ),
			'order'          => array( 'order', false ),
			'purchased_on'   => array( 'purchased_on', false ),
		];

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

		$serialnumber = ! empty( $_REQUEST['serialnumber'] ) ? esc_attr( $_REQUEST['serialnumber'] ) : '';

		if ( ! empty( $_REQUEST['s'] ) || ! empty( $serialnumber ) ) {
			$search_query = ! empty( $_REQUEST['s'] ) ? esc_attr( $_REQUEST['s'] ) : $serialnumber;
		}

		$product = ! empty( $_REQUEST['product'] ) ? esc_attr( $_REQUEST['product'] ) : '';

		$data = array();

		$product = ! empty( $product ) ? [
			'key'     => 'product',
			'value'   => $product,
			'compare' => '=',
		] : '';

		$query = ! $this->is_single
			? [
				's'          => $search_query,
				'meta_query' => array( $product ),

			]
			: [
				'meta_key'   => 'product',
				'meta_value' => $this->is_single
			];

		$posts = wsn_get_serial_numbers( $query );

		foreach ( $posts as $post ) {

			setup_postdata( $post );

			$product            = get_post_meta( $post->ID, 'product', true );
			$variation          = get_post_meta( $post->ID, 'variation', true );
			$deliver_times      = get_post_meta( $post->ID, 'deliver_times', true );
			$used_deliver_times = get_post_meta( $post->ID, 'used', true );
			$max_instance       = get_post_meta( $post->ID, 'max_instance', true );
			$validity           = get_post_meta( $post->ID, 'validity', true );
			$order              = get_post_meta( $post->ID, 'order', true );


			if ( $this->is_single && ( $used_deliver_times >= $deliver_times ) ) {
				continue;
			}

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

			$data[] = [
				'ID'             => $post->ID,
				'serial_numbers' => get_the_title( $post->ID ),
				'product'        => '<a href="' . get_edit_post_link( $product ) . '">' . get_the_title( $product ) . '</a>',
				'variation'      => empty( $variation ) ? __( 'Main Product', 'wc-serial-numbers' ) : get_the_title( $variation ),
				'deliver_times'  => empty( $deliver_times ) ? '∞' : $used_deliver_times . '/' . $deliver_times,
				'max_instance'   => empty( $max_instance ) ? '∞' : $max_instance,
				'purchaser'      => empty( $purchaser ) ? '-' : $purchaser,
				'order'          => empty( $order ) ? '-' : '<a href="' . get_edit_post_link( $order ) . '">#' . $order . '</a>',
				'purchased_on'   => empty( $purchased_on ) ? '-' : date( 'm-d-Y H:i a', strtotime( $purchased_on ) ),
				'validity'       => empty( $validity ) ? '∞' : $validity,
				'product_id'     => empty( $product ) ? '' : $product,
			];

		}

		return $data;
	}

	function get_pagenum() {

		if ( $this->is_single ) {
			return get_query_var( 'wsn_product_edit_paged' ) ? get_query_var( 'wsn_product_edit_paged' ) : 1;
		}

		$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

		if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] ) {
			$pagenum = $this->_pagination_args['total_pages'];
		}

		return max( 1, $pagenum );

	}

	public function get_hidden_columns() {

		$hidden = [];

		if ( $this->is_single ) {

			$hidden = [ 'purchaser', 'order', 'purchased_on', ];

		}

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

		$actions = [];

		if ( ! $this->is_single ) {
			$actions ['bulk-delete'] = 'Delete';
		}

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
			'<input type="checkbox" name="bulk-delete[]" value="%d" />
					<input type="hidden" name="product[%d]" value="%d" />',
			$item['ID'], $item['ID'], $item['product_id']
		);
	}

	function column_serial_numbers( $item ) {

		$actions = array(
			'edit' => '<a href="' . add_query_arg( [
					'type'          => 'manual',
					'row_action'    => 'edit',
					'serial_number' => $item['ID'],
					'product'       => $item['product_id'],
				], WPWSN_ADD_SERIAL_PAGE ) . '">' . __( 'Edit', 'wc-serial-numbers' ) . '</a>',

			'delete' => '<a href="' . add_query_arg( [
					'type'          => 'manual',
					'row_action'    => 'delete',
					'serial_number' => $item['ID'],
					'product'       => $item['product_id'],
				], WPWSN_SERIAL_INDEX_PAGE ) . '">Delete</a>',
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
				<?php

				$this->pagination( $which );
			}

			if ( ! $this->is_single ) {
				$this->extra_tablenav( $which );
			}

			?>

			<br class="clear"/>
		</div>
		<?php
	}

	/**
	 * Filter the table
	 *
	 * @param string $which
	 */

	function extra_tablenav( $which ) {

		echo apply_filters( 'wsn_extra_table_nav', '', 'serial-numbers' );
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @since 1.0.0
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return mixed
	 */
	private function sort_data( $a, $b ) {
		// Set defaults
		$orderby = 'serial_numbers';
		$order   = 'desc';

		// If orderby is set, use this as the sort column
		if ( ! empty( $_GET['orderby'] ) ) {
			$orderby = esc_attr( $_GET['orderby'] );
		}
		// If order is set use this as the order
		if ( ! empty( $_GET['order'] ) ) {
			$order = esc_attr( $_GET['order'] );
		}

		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );
		if ( $order === 'asc' ) {
			return $result;
		}

		return - $result;
	}


}
