<?php

use PluginEver\WooCommerceSerialNumbers\Generators;

defined( 'ABSPATH' ) || exit();

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Admin_Generators_Table extends \WP_List_Table {
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
	 * active number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $active_count;

	/**
	 * Inactive number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $inactive_count;

	/**
	 * Activations_Table constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Activation', 'wc-serial-numbers' ),
			'plural'   => __( 'Activations', 'wc-serial-numbers' ),
			'ajax'     => false,
		) );
	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'striped', $this->_args['plural'] );
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 1.0.0
	 * @throws Exception
	 * @return void
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
	 * @since 1.0.0
	 *
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
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @since 1.0.0
	 * @return array $views All the views available
	 */
	public function get_views() {}

	/**
	 * Get bulk actions
	 *
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'delete'     => __( 'Delete', 'wc-serial-numbers' ),
		);
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'               => '<input type="checkbox" />',
			'pattern'          => __( 'Pattern', 'wc-serial-numbers' ),
			'type'             => __( 'Type', 'wc-serial-numbers' ),
			'activation_limit' => __( 'Activation Limit', 'wc-serial-numbers' ),
			'action'           => __( 'Action', 'wc-serial-numbers' ),
		);

		return apply_filters( 'wc_serial_numbers_generators_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'pattern'          => array( 'Pattern', false ),
			'type'             => array( 'Type', false ),
			'activation_limit' => array( 'Activation Limit', false ),
		);

		return apply_filters( 'wc_serial_numbers_generators_table_sortable_columns', $sortable_columns );
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
		return 'pattern';
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
			case 'pattern':
				return empty( $item->pattern ) ? '&mdash;' : $item->pattern;
				break;
			case 'type':
				return $item->is_sequential ? 'sequential' : 'random';
				break;
			case 'activation_limit':
				return empty( $item->activation_limit ) ? '&mdash;' : $item->activation_limit;
				break;

			default:
				$column = isset( $item->$column_name ) ? $item->$column_name : '&mdash;';

				return apply_filters( 'wc_serial_numbers_serial_generators_table_column_content', $column, $item, $column_name );
		}
	}

	/**
	 * Retrieve all the data for all the discount codes
	 *
	 * @since 1.0.0
	 * @throws Exception
	 * @return Object $get_results Array of all the data for the discount codes
	 */
	public function get_results() {
		$per_page = $this->per_page;

		$orderby    = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'activation_time';
		$page       = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
		$order      = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$status     = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : '';
		$order_id   = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : '';
		$serial_id  = isset( $_GET['serial_id'] ) ? absint( $_GET['serial_id'] ) : '';

		$args = array(
			'per_page'   => $per_page,
			'page'       => $page,
			'orderby'    => $orderby,
			'order'      => $order,
			'status' => $status,
			'product_id' => $product_id,
			'key_id'     => $serial_id,
			'search'     => $search
		);

		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'order_date' !== $orderby ) {
			$args['orderby'] = $orderby;
		}

		$this->total_count    = Generators::query( $args, true );
//		$this->active_count   = Generators::query( array_merge( $args, [ 'is_active' => '1' ] ), true );
//		$this->inactive_count = Generators::query( array_merge( $args, [ 'is_active' => '0' ] ), true );

		return Generators::query( $args );
	}
}
