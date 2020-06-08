<?php
defined( 'ABSPATH' ) || exit();

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WC_Serial_Numbers_Products_List_Table extends \WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $per_page = 20;

	/**
	 *
	 * Total number of items
	 * @var string
	 * @since 1.0.0
	 */
	public $total_count;

	/**
	 * Base URL
	 * @var string
	 */
	public $base_url;


	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'product', 'wc-serial-number' ),
			'plural'   => __( 'products', 'wc-serial-number' ),
			'ajax'     => false,
		) );
	}

	/**
	 * Setup the final data for the table
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function prepare_items() {
		$per_page              = $this->per_page;
		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$data = $this->get_results();

		$total_items = $this->total_count;

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
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
	 *
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
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>"/>
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
			'activate'   => __( 'Activate', 'wc-serial-numbers' ),
			'deactivate' => __( 'Deactivate', 'wc-serial-numbers' ),
			'delete'     => __( 'Delete', 'wc-serial-numbers' ),
			'export'     => __( 'Export', 'wc-serial-numbers' ),
		);

		return $actions;
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'thumb'         => '',
			'product'       => __( 'Product', 'wc-serial-numbers' ),
			'product_price' => __( 'Price', 'wc-serial-numbers' ),
			'stock'         => __( 'Stock', 'wc-serial-numbers' ),
			'sold'          => __( 'Sold', 'wc-serial-numbers' ),
			'source'        => __( 'Source', 'wc-serial-numbers' ),
		);

		return apply_filters( 'wc_serial_numbers_products_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array();

		return apply_filters( 'wc_serial_numbers_products_table_sortable_columns', $sortable_columns );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @return string Name of the primary column.
	 * @since 1.0.0
	 * @access protected
	 *
	 */
	protected function get_primary_column_name() {
		return 'product';
	}

	/**
	 * since 1.0.0
	 *
	 * @param object $item
	 *
	 * @return string|void
	 */
	protected function column_cb( $item ) {
		return "<input type='checkbox' name='ids[]' id='id_{$item->ID}' value='{$item->ID}' />";
	}

	/**
	 * Get serial Key
	 *
	 * since 1.0.0
	 *
	 * @param $item
	 *
	 * @return string
	 */
	function column_product( $item ) {

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
		$product     = wc_get_product( $item->ID );
		$post_parent = wp_get_post_parent_id( $item->ID );
		$post_id     = $post_parent ? $post_parent : $item->ID;

		switch ( $column_name ) {
			case 'product':
				$column = empty( $item->ID ) || empty( $product ) ? '&mdash;' : sprintf( '<a href="%s">#%d - %s</a>', get_edit_post_link( $post_id ), $product->get_id(), $product->get_formatted_name() );
				break;
			case 'product_price':
				$column = empty( $item->ID ) || empty( $product ) ? '&mdash;' : wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) );
				break;
			case 'stock':
				$stock  = wc_serial_numbers_get_items( [
					'status'     => 'available',
					'product_id' => $product->get_id()
				], true );
				$column = empty( $item->ID ) || empty( $stock ) ? '&mdash;' : $stock;
				break;
			case 'sold':
				$stock  = wc_serial_numbers_get_items( [
					'status'     => 'sold',
					'product_id' => $product->get_id()
				], true );
				$column = empty( $item->ID ) || empty( $stock ) ? '&mdash;' : $stock;
				break;
			default:
				$column = isset( $item->$column_name ) ? $item->$column_name : '&mdash;';
				break;
		}


		return apply_filters( 'wcsn_serials_table_column_content', $column, $item, $column_name );
	}


	/**
	 * Retrieve all the data for all the discount codes
	 *
	 * @return array $get_results Array of all the data for the discount codes
	 * @since 1.0.0
	 */
	public function get_results() {
		$per_page   = $this->get_items_per_page( 'serials_per_page', $this->per_page );
		$orderby    = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'product_id';
		$order      = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : '';


		$args = array(
			'per_page'      => $per_page,
			'page'          => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			'orderby'       => $orderby,
			'order'         => $order,
			'product_id'    => $product_id,
			'search'        => $search,
			'serial_number' => true
		);

		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'order_date' != $orderby ) {
			$args['orderby'] = $orderby;
		}

		$this->total_count = wc_serial_numbers_get_products( $args, true );

		$results = wc_serial_numbers_get_products( $args );

		return $results;
	}

}
