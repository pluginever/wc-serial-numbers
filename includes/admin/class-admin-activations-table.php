<?php

use PluginEver\WooCommerceSerialNumbers\Activations;

defined( 'ABSPATH' ) || exit();

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Admin_Activations_Table extends \WP_List_Table {
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
	public function get_views() {
		$current        = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$active_count   = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
		$inactive_count = '&nbsp;<span class="count">(' . $this->inactive_count . ')</span>';
		$url            = admin_url( 'admin.php?page=wc-serial-numbers-activations' );
		$views          = array(
			'all'      => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $url ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'wc-serial-numbers' ) . $total_count ),
			'active'   => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'active', $url ), $current === 'active' ? ' class="current"' : '', __( 'Active', 'wc-serial-numbers' ) . $active_count ),
			'inactive' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'inactive', $url ), $current === 'inactive' ? ' class="current"' : '', __( 'Inactive', 'wc-serial-numbers' ) . $inactive_count ),
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
			'delete'     => __( 'Delete', 'wc-serial-numbers' ),
			'activate'   => __( 'Activate', 'wc-serial-numbers' ),
			'deactivate' => __( 'Deactivate', 'wc-serial-numbers' ),
		);

		return $actions;
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'instance'        => __( 'Instance', 'wc-serial-numbers' ),
			'key_id'          => __( 'Serial Key', 'wc-serial-numbers' ),
//			'serial_numbers'  => __( 'Serial Numbers', 'wc-serial-numbers' ),
			'platform'        => __( 'Platform', 'wc-serial-numbers' ),
			//'product'         => __( 'Product', 'wc-serial-numbers' ),
			//'order'           => __( 'Order', 'wc-serial-numbers' ),
			//'expire_date'     => __( 'Expire Date', 'wc-serial-numbers' ),
			'activation_time' => __( 'Activation time', 'wc-serial-numbers' ),
			'status'          => __( 'Status', 'wc-serial-numbers' ),
		);

		return apply_filters( 'serial_numbers_activation_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'key_id'          => array( 'key_id', false ),
			'instance'        => array( 'instance', false ),
			'platform'        => array( 'platform', false ),
			'activation_time' => array( 'activation_time', false ),
			'status'          => array( 'status', false ),
		);

		return apply_filters( 'serial_numbers_activation_table_sortable_columns', $sortable_columns );
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
	 * Get serial Key
	 *
	 * since 1.0.0
	 *
	 * @param $item
	 *
	 * @return string
	 */
	protected function column_instance( $item ) {
		$actions           = array();
		$base_url          = add_query_arg( array( 'id' => $item->id ), admin_url( 'admin.php?page=wc-serial-numbers-activations' ) );
		$activate_url      = wp_nonce_url( add_query_arg( [ 'action' => 'activate' ], $base_url ), 'serial_number_nonce' );
		$deactivate_url    = wp_nonce_url( add_query_arg( [ 'action' => 'deactivate' ], $base_url ), 'serial_number_nonce' );
		$delete_url        = wp_nonce_url( add_query_arg( [ 'action' => 'delete' ], $base_url ), 'serial_number_nonce' );
		$row_actions['id'] = sprintf( __( 'ID: %d', 'wc-serial-numbers' ), $item->id );

		if ( $item->active == '0' ) {
			$row_actions['activate'] = sprintf( '<a href="%1$s">%2$s</a>', $activate_url, __( 'Activate', 'wc-serial-numbers' ) );
		}
		if ( $item->active != '0' ) {
			$row_actions['inactivate'] = sprintf( '<a href="%1$s">%2$s</a>', $deactivate_url, __( 'Inactivate', 'wc-serial-numbers' ) );
		}

		$row_actions['delete'] = sprintf( '<a href="%1$s">%2$s</a>', $delete_url, __( 'Delete', 'wc-serial-numbers' ) );

		return sprintf( '<strong>%1$s</strong>%2$s', $item->instance, $this->row_actions( $row_actions ) );
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
		$column = '';
		switch ( $column_name ) {
			case 'key_id':
				$serial_number_url = add_query_arg( [
					'id'   => $item->serial_id,
					'page' => 'wc-serial-numbers',
				], admin_url( 'admin.php' ) );
				$column            = empty( $item->instance ) ? '&mdash;' : sprintf( '<strong><a href="%1$s" target="_blank">#%2$s</a></strong>', $serial_number_url, $item->serial_id );
				break;
			case 'platform':
				$column = empty( $item->platform ) ? '&mdash;' : $item->platform;
				break;
			case 'activation_time':
				$column = ! empty( $item->activation_time ) && ( '0000-00-00 00:00:00' != $item->activation_time ) ? $item->activation_time : '&mdash;';
				break;
			case 'status':
				$status = $item->active == '1' ? 'active' : 'inactive';
				$column = "<span class='wcsn-key-status {$status}'>" . ucfirst( $status ) . "</span>";
				break;
		}

		return apply_filters( 'serial_numbers_activations_table_column_content', $column, $item, $column_name );
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
		$serial_id  = isset( $_GET['serial_id'] ) ? absint( $_GET['serial_id'] ) : '';

		$args = array(
			'per_page'   => $per_page,
			'page'       => $page,
			'orderby'    => $orderby,
			'order'      => $order,
			'status__in' => $status,
			'product_id' => $product_id,
			'key_id'     => $serial_id,
			'search'     => $search
		);

		$this->active_count   = Activations::query( array_merge( $args, [ 'is_active' => '1' ] ), true );
		$this->inactive_count = Activations::query( array_merge( $args, [ 'is_active' => '0' ] ), true );
		$this->total_count    = array_sum( [ $this->active_count, $this->inactive_count ] );

		return Activations::query( $args );
	}
}
