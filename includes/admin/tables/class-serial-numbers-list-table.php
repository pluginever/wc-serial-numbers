<?php
defined( 'ABSPATH' ) || exit();

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WC_Serial_Numbers_Serial_Numbers_List_Table extends \WP_List_Table {
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
	 * active number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $active_count;

	/**
	 * Refunded number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $refunded_count;

	/**
	 * Expired number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $expired_count;

	/**
	 * Cancelled number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $cancelled_count;

	/**
	 * Available number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $available_count;

	/**
	 * Inactive number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $inactive_count;


	/**
	 * Base URL
	 * @var string
	 */
	public $base_url;


	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Serial Number', 'wc-serial-number' ),
			'plural'   => __( 'Serial Numbers', 'wc-serial-number' ),
			'ajax'     => false,
		) );
		$this->base_url = admin_url( 'admin.php?page=wc-serial-numbers' );
		$this->process_bulk_action();
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

		$this->process_bulk_action();

		$data = $this->get_results();

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch ( $status ) {
			case 'available':
				$total_items = $this->available_count;
				break;
			case 'active':
				$total_items = $this->active_count;
				break;
			case 'refunded':
				$total_items = $this->refunded_count;
				break;
			case 'cancelled':
				$total_items = $this->cancelled_count;
				break;
			case 'expired':
				$total_items = $this->expired_count;
				break;
			case 'inactive':
				$total_items = $this->inactive_count;
				break;
			case 'any':
			default:
				$total_items = $this->total_count;
				break;
		}

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
	 * @return array $views All the views available
	 * @since 1.0.0
	 */
	public function get_views() {
		$current         = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$available_count = '&nbsp;<span class="count">(' . $this->available_count . ')</span>';
		$total_count     = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$active_count    = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
		$refunded_count  = '&nbsp;<span class="count">(' . $this->refunded_count . ')</span>';
		$cancelled_count = '&nbsp;<span class="count">(' . $this->cancelled_count . ')</span>';
		$expired_count   = '&nbsp;<span class="count">(' . $this->expired_count . ')</span>';
		$inactive_count  = '&nbsp;<span class="count">(' . $this->inactive_count . ')</span>';

		$views = array(
			'all'       => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $this->base_url ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'wc-serial-numbers' ) . $total_count ),
			'available' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'available', $this->base_url ), $current === 'available' ? ' class="current"' : '', __( 'Available', 'wc-serial-numbers' ) . $available_count ),
			'active'    => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'active', $this->base_url ), $current === 'active' ? ' class="current"' : '', __( 'Active', 'wc-serial-numbers' ) . $active_count ),
			'refunded'  => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'refunded', $this->base_url ), $current === 'refunded' ? ' class="current"' : '', __( 'Refunded', 'wc-serial-numbers' ) . $refunded_count ),
			'cancelled' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'cancelled', $this->base_url ), $current === 'cancelled' ? ' class="current"' : '', __( 'Cancelled', 'wc-serial-numbers' ) . $cancelled_count ),
			'expired'   => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'expired', $this->base_url ), $current === 'expired' ? ' class="current"' : '', __( 'Expired', 'wc-serial-numbers' ) . $expired_count ),
			'inactive'  => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'inactive', $this->base_url ), $current === 'inactive' ? ' class="current"' : '', __( 'Inactive', 'wc-serial-numbers' ) . $inactive_count ),
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
			'delete'          => __( 'Delete', 'wc-serial-numbers' ),
			'mark_available'  => __( 'Mark available', 'wc-serial-numbers' ),
			'mark_inactivate' => __( 'Mark inactive', 'wc-serial-numbers' ),
		);

		return $actions;
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'               => '<input type="checkbox" />',
			'serial_key'       => wc_serial_numbers_labels( 'serial_numbers' ),
			'product'          => __( 'Product', 'wc-serial-numbers' ),
			'order'            => __( 'Order', 'wc-serial-numbers' ),
			'activation_limit' => __( 'Activation Limit', 'wc-serial-numbers' ),
			'activation_count' => __( 'Activation Count', 'wc-serial-numbers' ),
			'expire_date'      => __( 'Expire Date', 'wc-serial-numbers' ),
			'validity'         => __( 'Validity', 'wc-serial-numbers' ),
			'date'             => __( 'Order Date', 'wc-serial-numbers' ),
			'status'           => __( 'Status', 'wc-serial-numbers' ),
		);

		return apply_filters( 'serial_numbers_serials_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'serial_key'       => array( 'serial_key', false ),
			'product'          => array( 'product_id', false ),
			'activation_limit' => array( 'activation_limit', false ),
			'expire_date'      => array( 'activation_limit', false ),
			'order'            => array( 'order_id', false ),
			'validity'         => array( 'order_id', false ),
			'status'           => array( 'status', false ),
			'date'             => array( 'order_date', false ),
		);

		return apply_filters( 'serial_numbers_serials_table_sortable_columns', $sortable_columns );
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
		return 'serial_key';
	}

	/**
	 * since 1.0.0
	 *
	 * @param object $item
	 *
	 * @return string|void
	 */
	protected function column_cb( $item ) {
		return "<input type='checkbox' name='id[]' id='id_{$item->id}' value='{$item->id}' />";
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
	function column_serial_key( $item ) {
		$actions             = array();
		$base_url            = add_query_arg( array( 'serial' => $item->id ), admin_url( 'admin.php?page=wc-serial-numbers' ) );
		$edit_url            = wp_nonce_url( add_query_arg( [ 'serial_numbers_action' => 'edit_serial_number' ], $base_url ), 'serial_number_nonce' );
		$activate_url        = wp_nonce_url( add_query_arg( [ 'serial_numbers_action' => 'activate_serial_number' ], $base_url ), 'serial_number_nonce' );
		$deactivate_url      = wp_nonce_url( add_query_arg( [ 'serial_numbers_action' => 'inactive_serial_number' ], $base_url ), 'serial_number_nonce' );
		$delete_url          = wp_nonce_url( add_query_arg( [ 'serial_numbers_action' => 'delete_serial_number' ], $base_url ), 'serial_number_nonce' );
		$row_actions['id']   = sprintf( __( 'ID: %d', 'wp-serial-numbers' ), $item->id );
		$row_actions['show'] = sprintf( '<a data-serial-id="%d" data-nonce="%s" class="wsn-show-serial-key"   href="#"> %s</a>', $item->id, wp_create_nonce( 'wcsn_show_serial_key' ), __( 'Show', 'wc-serial-numbers' ) );
		$row_actions['edit'] = sprintf( '<a href="%1$s">%2$s</a>', $edit_url, __( 'Edit', 'wp-serial-numbers' ) );

		if ( $item->status == 'inactive' ) {
			$row_actions['activate'] = sprintf( '<a href="%1$s">%2$s</a>', $activate_url, __( 'Activate', 'wp-serial-numbers' ) );
		}
		if ( $item->status !== 'inactive' ) {
			$row_actions['inactivate'] = sprintf( '<a href="%1$s">%2$s</a>', $deactivate_url, __( 'Inactivate', 'wp-serial-numbers' ) );
		}

		$row_actions['delete'] = sprintf( '<a href="%1$s">%2$s</a>', $delete_url, __( 'Delete', 'wp-serial-numbers' ) );


		$row_actions = apply_filters( 'serial_numbers_serial_number_table_row_actions', $row_actions, $item );

		$spinner = sprintf( '<img class="wcsn-spinner" style="display: none;" src="%s"/>', admin_url( 'images/loading.gif' ) );

		return sprintf( '<code class="serial-number-key encrypted"></code> %1$s%2$s', $spinner, $this->row_actions( $row_actions ) );
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
			case 'product':
				$product = wc_get_product( $item->product_id );
				$post_parent = wp_get_post_parent_id($item->product_id);
				$post_id = $post_parent? $post_parent : $item->product_id;
				$column     = empty( $item->product_id ) ? '&mdash;' : sprintf( '<a href="%s" target="_blank">#%d - %s</a>', get_edit_post_link( $post_id ), $product->get_id(), $product->get_formatted_name() );
				break;
			case 'order':
				$line   = ! empty( $item->order_id ) ? '#' . $item->order_id : '&mdash;';
				$column = ! empty( $item->product_id ) ? '<strong><a href="' . get_edit_post_link( $item->order_id ) . '" target="_blank">' . $line . '</a></strong>' : $line;
				break;
			case 'activation_email':
				$column = ! empty( $item->activation_email ) ? $item->activation_email : '&mdash;';
				break;
			case 'activation_limit':
				$column = ! empty( $item->activation_limit ) ? $item->activation_limit : __( 'Unlimited', 'wc-serial-numbers' );
				break;
			case 'activation_count':
				$link   = add_query_arg( [
					'serial_id' => $item->id,
					'page'      => 'wc-serial-numbers-activations',
				], admin_url( 'admin.php' ) );
				$activation_count =  wc_serial_numbers_get_activations_count( $item->id );
				$column = sprintf('<a href="%s" target="_blank">%s</a>', $link, $activation_count);
				break;
			case 'validity':
				$column = ! empty( $item->validity ) ? sprintf( _n( '%s Day', '%s Days', $item->validity, 'wc-serial-numbers' ), number_format_i18n( $item->validity ) ) : __( 'Never expire', 'wc-serial-numbers' );
				break;
			case 'status':
				$status = wc_serial_numbers_get_serial_number_status( $item, 'view' );
				$column = "<span class='wcsn-key-status {$item->status}'>{$status}</span>";
				break;
			case 'expire_date':
				$column = ! empty( $item->expire_date ) && ( '0000-00-00 00:00:00' != $item->expire_date ) ? date( get_option( 'date_format' ), strtotime( $item->expire_date ) ) : '&mdash;';
				break;
			case 'date':
				$column = ! empty( $item->order_date ) && ( '0000-00-00 00:00:00' != $item->order_date ) ? date( get_option( 'date_format' ), strtotime( $item->order_date ) ) : '&mdash;';
				break;
		}

		return apply_filters( 'serial_numbers_serials_table_column_content', $column, $item, $column_name );
	}


	/**
	 * since 1.0.0
	 */
	function process_bulk_action() {
		global $wpdb;
		if ( ! isset( $_REQUEST['id'] ) ) {
			return;
		}

		$items = array_map( 'intval', $_REQUEST['id'] );

		if ( $items ) {
			foreach ( $items as $id ) {
				if ( ! $id ) {
					continue;
				}

				$id = (int) $id;
				if ( 'delete' === $this->current_action() ) {
					wc_serial_numbers_delete_serial_number( $id );
				} else if ( 'mark_available' === $this->current_action() ) {
					wc_serial_numbers_change_serial_number_status( $id, 'available' );
				} else if ( 'mark_inactivate' === $this->current_action() ) {
					wc_serial_numbers_change_serial_number_status( $id, 'inactive' );
				}

			}
		}
	}

	/**
	 * Retrieve all the data for all the discount codes
	 *
	 * @return array $get_results Array of all the data for the discount codes
	 * @since 1.0.0
	 */
	public function get_results() {
		$per_page = $this->per_page;

		$orderby    = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'order_date';
		$order      = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$status     = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : '';
		$order_id   = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : '';
		$serial_id  = isset( $_GET['serial_id'] ) ? absint( $_GET['serial_id'] ) : '';


		$args = array(
			'per_page'   => $per_page,
			'page'       => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			'orderby'    => $orderby,
			'order'      => $order,
			'status'     => $status,
			'product_id' => $product_id,
			'order_id'   => $order_id,
			'include'    => $serial_id,
			'search'     => $search
		);

		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'order_date' != $orderby ) {
			$args['orderby'] = $orderby;
		}

		$this->total_count     = wc_serial_numbers_get_serial_numbers( array_merge( $args, array( 'status' => '' ) ), true );
		$this->available_count = wc_serial_numbers_get_serial_numbers( array_merge( $args, array( 'status' => 'available' ) ), true );
		$this->active_count    = wc_serial_numbers_get_serial_numbers( array_merge( $args, array( 'status' => 'active' ) ), true );
		$this->refunded_count  = wc_serial_numbers_get_serial_numbers( array_merge( $args, array( 'status' => 'refunded' ) ), true );
		$this->cancelled_count = wc_serial_numbers_get_serial_numbers( array_merge( $args, array( 'status' => 'cancelled' ) ), true );
		$this->expired_count   = wc_serial_numbers_get_serial_numbers( array_merge( $args, array( 'status' => 'expired' ) ), true );
		$this->inactive_count  = wc_serial_numbers_get_serial_numbers( array_merge( $args, array( 'status' => 'inactive' ) ), true );

		$results = wc_serial_numbers_get_serial_numbers( $args );

		return $results;
	}

}
