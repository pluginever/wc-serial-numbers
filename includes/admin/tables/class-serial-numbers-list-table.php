<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WC_Serial_Numbers_List_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $per_page = 20;

	/**
	 *
	 * Total number
	 * @var string
	 * @since 1.0.0
	 */
	public $total_count;

	/**
	 * Sold number
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $sold_count;

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
	 * WC_Serial_Numbers_List_Table constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => wc_serial_numbers()->get_serial_number_label(),
			'plural'   => wc_serial_numbers()->get_serial_number_label( true ),
			'ajax'     => false,
		) );
		$this->process_bulk_action();
	}

	/**
	 * since 1.0.0
	 */
	function no_items() {
		echo sprintf( __( 'No %s found.', 'wc-serial-numbers' ), wc_serial_numbers()->get_serial_number_label() );
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'               => '<input type="checkbox" />',
			'serial_key'       => wc_serial_numbers()->get_serial_number_label(),
			'product'          => __( 'Product', 'wc-serial-numbers' ),
			'order'            => __( 'Order', 'wc-serial-numbers' ),
			'activation_email' => __( 'Customer Email', 'wc-serial-numbers' ),
			'activation_limit' => __( 'Activation Limit', 'wc-serial-numbers' ),
			'validity'         => __( 'Validity', 'wc-serial-numbers' ),
			'date'             => __( 'Order Date', 'wc-serial-numbers' ),
			'status'           => __( 'Status', 'wc-serial-numbers' ),
		);

		return apply_filters( 'wcsn_serial_numbers_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'serial_key'       => array( 'serial_key', false ),
			'product'          => array( 'product_id', false ),
			'activation_email' => array( 'activation_email', false ),
			'activation_limit' => array( 'activation_limit', false ),
			'order'            => array( 'order_id', false ),
			'status'           => array( 'status', false ),
			'date'             => array( 'order_date', false ),
		);

		return apply_filters( 'wcsn_serial_numbers_table_sortable_columns', $sortable_columns );
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
		$edit_url            = wp_nonce_url( add_query_arg( [ 'wcsn-action' => 'edit_serial_number' ], $base_url ), 'serial_number_nonce' );
		$activate_url        = wp_nonce_url( add_query_arg( [ 'wcsn-action' => 'activate_serial_number' ], $base_url ), 'serial_number_nonce' );
		$deactivate_url      = wp_nonce_url( add_query_arg( [ 'wcsn-action' => 'deactivate_serial_number' ], $base_url ), 'serial_number_nonce' );
		$delete_url          = wp_nonce_url( add_query_arg( [ 'wcsn-action' => 'delete_serial_number' ], $base_url ), 'serial_number_nonce' );
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

		$nonce   = wp_create_nonce( 'serial_number_toggle_visibility' );
		$row_actions = apply_filters( 'eaccounting_accounts_row_actions', $row_actions, $item );

		$spinner = sprintf( '<img class="wcsn-spinner" style="display: none;" src="%s"/>', admin_url( 'images/loading.gif' ) );

		return sprintf( '<code class="serial-number-key encrypted" id="serial-number-key-%1$d" data-serail_id="%1$s" data-nonce="%2$s"></code> %3$s%4$s', $item->id, $nonce, $spinner, $this->row_actions($row_actions) );
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
				$post       = get_post( $item->product_id );
				$product_id = $post->post_parent ? $post->post_parent : $item->product_id;
				$column     = empty( $item->product_id ) ? '&mdash;' : sprintf( '<a href="%s" target="_blank">#%d - %s</a>', get_edit_post_link( $product_id ), $product_id, get_the_title( $item->product_id ) );
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
			case 'validity':
				$column = ! empty( $item->validity ) ? sprintf( _n( '%s Day', '%s Days', $item->validity, 'wc-serial-numbers' ), number_format_i18n( $item->validity ) ) : __( 'Never expire', 'wc-serial-numbers' );
				break;
			case 'status':
				$statues = wcsn_get_serial_statuses();
				$column  = ! empty( $item->status ) && array_key_exists( $item->status, $statues ) ? "<span class='wcsn-key-status {$item->status}'>{$statues[$item->status]}</span>" : '&mdash;';
				break;
			case 'date':
				$column = ! empty( $item->order_date ) && ( '0000-00-00 00:00:00' != $item->order_date ) ? date( get_option( 'date_format' ), strtotime( $item->order_date ) ) : '&mdash;';
				break;
		}

		return apply_filters( 'wcsn_serial_number_table_column_content', $column, $item, $column_name );
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
	 *
	 * @param string $which
	 */
	function extra_tablenav( $which ) {
		if ( $which == "top" ) {
			$statuses = wcsn_get_serial_statuses();
			$s_status = empty( $_REQUEST['status'] ) ? '' : sanitize_text_field( $_REQUEST['status'] );
			?>
			<div class="alignleft actions bulkactions">
				<select name="status" id="status">
					<?php echo sprintf( '<option value="%s" %s>%s</option>', '', selected( '', $s_status, false ), __( 'Status', 'wc-serial-numbers' ) ); ?>
					<?php foreach ( $statuses as $key => $val ) {
						echo sprintf( '<option value="%s" %s>%s</option>', $key, selected( $key, $s_status, false ), $val );
					} ?>
				</select>
				<button type="submit" class="button-secondary"><?php _e( 'Submit', 'wc-serial-numbers' ); ?></button>
			</div>
			<?php
		}
		if ( $which == "bottom" ) {
			//The code that goes after the table is there

		}
	}

	/**
	 * Retrieve the view types
	 *
	 * @return array $views All the views available
	 * @since 1.0.0
	 */
	public function get_views() {
		$base_url        = admin_url( 'admin.php?page=wc-serial-numbers' );
		$current         = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$available_count = '&nbsp;<span class="count">(' . $this->available_count . ')</span>';
		$total_count     = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$sold_count      = '&nbsp;<span class="count">(' . $this->sold_count . ')</span>';
		$refunded_count  = '&nbsp;<span class="count">(' . $this->refunded_count . ')</span>';
		$cancelled_count = '&nbsp;<span class="count">(' . $this->cancelled_count . ')</span>';
		$expired_count   = '&nbsp;<span class="count">(' . $this->expired_count . ')</span>';
		$inactive_count  = '&nbsp;<span class="count">(' . $this->inactive_count . ')</span>';

		$views = array(
			'all'       => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base_url ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'wc-serial-numbers' ) . $total_count ),
			'available' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'available', $base_url ), $current === 'available' ? ' class="current"' : '', __( 'Available', 'wc-serial-numbers' ) . $available_count ),
			'sold'      => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'sold', $base_url ), $current === 'sold' ? ' class="current"' : '', __( 'Sold', 'wc-serial-numbers' ) . $sold_count ),
			'refunded'  => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'refunded', $base_url ), $current === 'refunded' ? ' class="current"' : '', __( 'Refunded', 'wc-serial-numbers' ) . $refunded_count ),
			'cancelled' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'cancelled', $base_url ), $current === 'cancelled' ? ' class="current"' : '', __( 'Cancelled', 'wc-serial-numbers' ) . $cancelled_count ),
			'expired'   => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'expired', $base_url ), $current === 'expired' ? ' class="current"' : '', __( 'Expired', 'wc-serial-numbers' ) . $expired_count ),
			'inactive'  => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'inactive', $base_url ), $current === 'inactive' ? ' class="current"' : '', __( 'Inactive', 'wc-serial-numbers' ) . $inactive_count ),
		);

		return $views;
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

		if ( 'delete' === $this->current_action() ) {

			if ( $items ) {
				foreach ( $items as $id ) {
					if ( ! $id ) {
						continue;
					}

					$id = (int) $id;
					wcsn_delete_serial_number( $id );
				}
			}
		} else if ( 'mark_available' === $this->current_action() ) {

			if ( $items ) {
				foreach ( $items as $id ) {
					if ( ! $id ) {
						continue;
					}

					$id = (int) $id;
					wcsn_insert_serial_number( [
						'id'     => $id,
						'status' => 'available'
					] );
				}
			}
		} else if ( 'mark_inactivate' === $this->current_action() ) {

			if ( $items ) {
				foreach ( $items as $id ) {
					if ( ! $id ) {
						continue;
					}

					$id = (int) $id;
					wcsn_insert_serial_number( [
						'id'     => $id,
						'status' => 'inactive'
					] );
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


		$args = array(
			'per_page'   => $per_page,
			'page'       => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			'orderby'    => $orderby,
			'order'      => $order,
			'status'     => $status,
			'product_id' => $product_id,
			'order_id'   => $order_id,
			'search'     => $search
		);

		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'order_date' != $orderby ) {
			$args['orderby'] = $orderby;
		}

		$this->total_count     = wcsn_get_serial_numbers( array_merge( $args, array( 'status' => '' ) ), true );
		$this->available_count = wcsn_get_serial_numbers( array_merge( $args, array( 'status' => 'available' ) ), true );
		$this->sold_count      = wcsn_get_serial_numbers( array_merge( $args, array( 'status' => 'sold' ) ), true );
		$this->refunded_count  = wcsn_get_serial_numbers( array_merge( $args, array( 'status' => 'refunded' ) ), true );
		$this->cancelled_count = wcsn_get_serial_numbers( array_merge( $args, array( 'status' => 'cancelled' ) ), true );
		$this->expired_count   = wcsn_get_serial_numbers( array_merge( $args, array( 'status' => 'expired' ) ), true );
		$this->inactive_count  = wcsn_get_serial_numbers( array_merge( $args, array( 'status' => 'inactive' ) ), true );

		$results = wcsn_get_serial_numbers( $args );

		return $results;
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
			case 'sold':
				$total_items = $this->sold_count;
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

}
