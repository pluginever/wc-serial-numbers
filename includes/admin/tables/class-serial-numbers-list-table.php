<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WCSN_Serial_Numbers_List_Table extends \WP_List_Table {
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Serial Number', 'wc-serial-numbers' ),
			'plural'   => __( 'Serial Numbers', 'wc-serial-numbers' ),
			'ajax'     => false,
		) );
	}

	function no_items() {
		_e( 'No serial number found.', 'wc-serial-numbers' );
	}

	function get_columns() {
		$columns = array(
			'cb'               => '<input type="checkbox" />',
			'serial_key'       => __( 'Serial Key', 'wc-serial-numbers' ),
			'product'          => __( 'Product', 'wc-serial-numbers' ),
			'order'            => __( 'Order', 'wc-serial-numbers' ),
			'activation_email' => __( 'Customer Email', 'wc-serial-numbers' ),
			'activation_limit' => __( 'Activation Limit', 'wc-serial-numbers' ),
			'validity'         => __( 'Validity', 'wc-serial-numbers' ),
			'status'           => __( 'Status', 'wc-serial-numbers' ),
			'date'             => __( 'Order Date', 'wc-serial-numbers' ),
		);

		return $columns;
	}

	function get_sortable_columns() {
		return $sortable_columns = array(
			'serial_key'       => array( 'serial_key', false ),
			'product'          => array( 'product_id', false ),
			'activation_email' => array( 'activation_email', false ),
			'activation_limit' => array( 'activation_limit', false ),
			'order'            => array( 'order_id', false ),
			'status'           => array( 'status', false ),
			'date'             => array( 'order_date', false ),
		);
	}

	function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'product':
				$line = ! empty( $item->product_id ) ? get_the_title( $item->product_id ) : '&#45;';
				echo ! empty( $item->product_id ) ? '<a href="' . get_edit_post_link( $item->product_id ) . '">' . $line . '</a>' : $line;
				break;
			case 'order':
				$line = ! empty( $item->order_id ) ? '#' . $item->order_id : '&#45;';
				echo ! empty( $item->product_id ) ? '<strong><a href="' . get_edit_post_link( $item->order_id ) . '">' . $line . '</a></strong>' : $line;
				break;
			case 'activation_email':
				echo ! empty( $item->activation_email ) ? $item->activation_email : '&#45;';
				break;
			case 'activation_limit':
				echo ! empty( $item->activation_limit ) ? $item->activation_limit : __( 'Unlimited', 'wc-serial-numbers' );
				echo '<br>';
				echo '<span class="wcsn-remaining-activation" style="color: #999;font-size: 11px;">' . __( 'Remaining', 'wc-serial-numbers' ) . ': ' . wcsn_get_remaining_activation( $item->id, 'view' ) . '</span>';
				break;
			case 'validity':
				echo ! empty( $item->validity ) ? sprintf( _n( '%s Day', '%s Days', $item->validity, 'wc-serial-numbers' ), number_format_i18n( $item->validity ) ) : __( 'Never expire', 'wc-serial-numbers' );
				break;
			case 'status':
				$statues = wcsn_get_serial_statuses();
				echo ! empty( $item->status ) && array_key_exists( $item->status, $statues ) ? "<span class='wcsn-status-{$item->status}'>{$statues[$item->status]}</span>" : '&#45;';
				break;
			case 'date':
				echo ! empty( $item->order_date ) && '0000-00-00 00:00:00' != $item->order_date ? date( get_option( 'date_format' ), strtotime( $item->order_date ) ) : '&#45;';
				break;
		}
	}

	function column_serial_key( $item ) {

		$actions = array(

			'edit' => '<a href="' . add_query_arg( array(
					'page'        => 'wc-serial-numbers',
					'action_type' => 'add_serial_number',
					'row_action'  => 'edit',
					'serial_id'   => $item->id,
				), admin_url( 'admin.php' ) ) . '">' . __( 'Edit', 'wc-serial-numbers' ) . '</a>',

			'delete' => '<a href="' . add_query_arg( array(
					'action'    => 'delete_wc_serial_number',
					'nonce'     => wp_create_nonce( 'delete_wc_serial_number' ),
					'serial_id' => $item->id,
				), admin_url( 'admin-post.php' ) ) . '">' . __( 'Delete', 'wc-serial-numbers' ) . '</a>',

		);

		return sprintf( '%1$s %2$s', $item->serial_key, $this->row_actions( $actions ) );
	}


	protected function column_cb( $item ) {
		return "<input type='checkbox' name='id[]' id='id_{$item->id}' value='{$item->id}' />";
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
			'delete' => __( 'Delete', 'wc-serial-numbers' )
		);

		return $actions;
	}

	/**
	 * Process bulk actions
	 */
	function process_bulk_action() {
		global $wpdb;
		if ( ! isset( $_REQUEST['id'] ) ) {
			return;
		}

		$items = array_map( 'intval', $_REQUEST['id'] );

		//Detect when a bulk action is being triggered…
		if ( 'delete' === $this->current_action() ) {

			if ( $items ) {
				foreach ( $items as $id ) {
					if ( ! $id ) {
						continue;
					}

					$id = (int) $id;

					$wpdb->query( "DELETE FROM {$wpdb->prefix}wcsn_serial_numbers WHERE id = {$id}" );
					$wpdb->query( "DELETE FROM {$wpdb->prefix}wcsn_activations WHERE serial_id = {$id}" );
				}
			}

			echo '<div class="updated"><p>' . __( 'Serial Number Deleted', 'wc-serial-numbers' ) . '</p></div>';

		}

	}

	function extra_tablenav( $which ) {
		if ( $which == "top" ) {
			$products   = wcsn_get_product_list();
			$statuses   = wcsn_get_serial_statuses();
			$s_product_id = empty( $_REQUEST['product_id'] ) ? '' : intval( $_REQUEST['product_id'] );
			$s_status     = empty( $_REQUEST['status'] ) ? '' : sanitize_text_field( $_REQUEST['status'] );
			?>
			<div class="alignleft actions bulkactions">
				<select name="product_id" id="product_id" class="product_id">
					<option value=""><?php _e( 'Filter by Product', 'wc-serial-numbers' ); ?></option>
					<?php foreach ( $products as $product_id => $product_title ) {
						echo '<option value="' . $product_id . '" '.selected($product_id, $s_product_id).'>' . esc_html( $product_title ) . '</option>';
					} ?>
				</select>

				<select name="status" id="status">
					<option value=""><?php _e( 'Filter by status', 'wc-serial-numbers' ); ?></option>
					<?php foreach ( $statuses as $status_key => $status_label ) {
						echo '<option value="' . $status_key . '" '.selected($status_key, $s_status).'>' . esc_html( $status_label ) . '</option>';
					} ?>

				</select>
				<button type="submit" class="button-secondary">Submit</button>
			</div>
			<?php
		}
		if ( $which == "bottom" ) {
			//The code that goes after the table is there

		}
	}

	function prepare_items() {
		global $wpdb;
		$per_page              = wcsn_get_settings( 'wsn_rows_per_page', 20, 'wsn_general_settings' );
		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();

		$this->set_pagination_args( [
			'number'     => $per_page,
			'offset'     => ( $current_page - 1 ) * $per_page,
			'orderby'    => ! empty( $_REQUEST['orderby'] ) && '' != $_REQUEST['orderby'] ? $_REQUEST['orderby'] : 'id',
			'order'      => ! empty( $_REQUEST['order'] ) && '' != $_REQUEST['order'] ? $_REQUEST['order'] : 'desc',
			'search'     => ! empty( $_REQUEST['s'] ) && '' != $_REQUEST['s'] ? $_REQUEST['s'] : '',
			'status'     => ! empty( $_REQUEST['status'] ) && '' != $_REQUEST['status'] ? sanitize_text_field( $_REQUEST['status'] ) : '',
			'product_id' => ! empty( $_REQUEST['product_id'] ) && '' != $_REQUEST['product_id'] ? intval( $_REQUEST['product_id'] ) : '',
		] );

		$this->process_bulk_action();

		$total_items = wcsn_get_serial_numbers( $this->_pagination_args, true );

		$this->items = wcsn_get_serial_numbers( $this->_pagination_args );

		$this->set_pagination_args(
			array(

				'total_items' => $total_items,
				'per_page'    => $per_page,
				'orderby'     => ! empty( $_REQUEST['orderby'] ) && '' != $_REQUEST['orderby'] ? $_REQUEST['orderby'] : 'id',
				'order'       => ! empty( $_REQUEST['order'] ) && '' != $_REQUEST['order'] ? $_REQUEST['order'] : 'asc'
			)
		);
	}

}
