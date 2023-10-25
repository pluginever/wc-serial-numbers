<?php

namespace WooCommerceSerialNumbers\Admin\ListTables;

use WooCommerceSerialNumbers\Models\Activation;

defined( 'ABSPATH' ) || exit;

/**
 * Class ActivationsTable.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin\ListTables
 */
class ActivationsTable extends ListTable {
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
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $total_count = 0;

	/**
	 * Serial_Keys_List_Table constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Activation', 'wc-serial-numbers' ),
				'plural'   => __( 'Activations', 'wc-serial-numbers' ),
				'ajax'     => false,
			)
		);
	}

	/**
	 * Prepare table data.
	 *
	 * @since 1.4.6
	 */
	public function prepare_items() {
		$per_page              = $this->get_items_per_page( 'wcsn_activations_per_page' );
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$orderby               = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_SPECIAL_CHARS );
		$order                 = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_SPECIAL_CHARS );
		$search                = filter_input( INPUT_GET, 's', FILTER_SANITIZE_SPECIAL_CHARS );
		$product_id            = filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT );
		$order_id              = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		$customer_id           = filter_input( INPUT_GET, 'customer_id', FILTER_SANITIZE_NUMBER_INT );
		$id                    = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		$serial_id             = filter_input( INPUT_GET, 'serial_id', FILTER_SANITIZE_NUMBER_INT );

		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'order_date' !== $orderby ) {
			$args['orderby'] = $orderby;
		}

		$args = array(
			'per_page'    => $per_page,
			'paged'       => $current_page,
			'orderby'     => $orderby,
			'order'       => $order,
			'product_id'  => $product_id,
			'order_id'    => $order_id,
			'customer_id' => $customer_id,
			'include'     => $id,
			'search'      => $search,
			'serial_id'   => $serial_id,
		);

		$this->items       = Activation::query( $args );
		$this->total_count = Activation::count( $args );

		$this->set_pagination_args(
			array(
				'total_items' => $this->total_count,
				'per_page'    => $per_page,
				'total_pages' => $this->total_count > 0 ? ceil( $this->total_count / $per_page ) : 0,
			)
		);
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		esc_html_e( 'No activations found. Once a serial key is activated, it will appear here.', 'wc-serial-numbers' );
	}

	/**
	 * Adds the order and product filters to the licenses list.
	 *
	 * @param string $which Which nav.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			echo '<div class="alignleft actions">';
			$this->order_dropdown();
			$this->product_dropdown();
			submit_button( __( 'Filter', 'wc-serial-numbers' ), '', 'filter-action', false );
			echo '</div>';
		}
	}

	/**
	 * Process bulk action.
	 *
	 * @param string $doaction Action name.
	 *
	 * @since 1.4.6
	 */
	public function process_bulk_actions( $doaction ) {
		if ( $doaction && check_ajax_referer( 'bulk-activations' ) ) {
			if ( isset( $_REQUEST['id'] ) ) {
				$ids      = wp_parse_id_list( wp_unslash( $_REQUEST['id'] ) );
				$doaction = ( - 1 !== $_REQUEST['action'] ) ? $_REQUEST['action'] : $_REQUEST['action2']; // phpcs:ignore
			} elseif ( isset( $_REQUEST['ids'] ) ) {
				$ids = array_map( 'absint', $_REQUEST['ids'] );
			} elseif ( wp_get_referer() ) {
				wp_safe_redirect( wp_get_referer() );
				exit;
			}

			foreach ( $ids as $id ) { // Check the permissions on each.
				$key = Activation::get( $id );
				if ( ! $key ) {
					continue;
				}
				switch ( $doaction ) {
					case 'delete':
						$key->delete();
						break;
				}
			}

			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		parent::process_bulk_actions( $doaction );
	}

	/**
	 * Get bulk actions
	 *
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'wc-serial-numbers' ),
		);
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'instance'        => __( 'Instance', 'wc-serial-numbers' ),
			'product'         => __( 'Product', 'wc-serial-numbers' ),
			'serial_id'       => __( 'Key', 'wc-serial-numbers' ),
			'platform'        => __( 'Platform', 'wc-serial-numbers' ),
			'activation_time' => __( 'Activation Time', 'wc-serial-numbers' ),
		);

		return apply_filters( 'wc_serial_numbers_activations_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'instance'        => array( 'instance', false ),
			'serial_id'       => array( 'serial_id', false ),
			'platform'        => array( 'platform', false ),
			'activation_time' => array( 'activation_time', false ),
		);

		return apply_filters( 'wc_serial_numbers_activations_table_sortable_columns', $sortable_columns );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'instance';
	}

	/**
	 * since 1.0.0
	 *
	 * @param object $item Item.
	 *
	 * @return string|void
	 */
	protected function column_cb( $item ) {
		return "<input type='checkbox' name='ids[]' id='id_{$item->id}' value='{$item->id}' />";
	}

	/**
	 * Display key.
	 *
	 * @param Activation $activation Activation.
	 *
	 * @since 1.4.6
	 */
	protected function column_instance( $activation ) {
		$delete_url        = add_query_arg(
			array(
				'id'     => $activation->id,
				'action' => 'delete',
			),
			admin_url( 'admin.php?page=wc-serial-numbers-activations' )
		);
		$actions['delete'] = sprintf( '<a href="%1$s">%2$s</a>', wp_nonce_url( $delete_url, 'bulk-activations' ), __( 'Delete', 'wc-serial-numbers' ) );

		return sprintf( '<code class="wcsn-activation-instance">%1$s</code> %2$s', esc_html( $activation->get_instance() ), $this->row_actions( $actions ) );
	}

	/**
	 * Display product column.
	 *
	 * @param Activation $activation Activation.
	 *
	 * @since 1.4.6
	 */
	protected function column_product( $activation ) {
		return esc_html( $activation->get_product_title() );
	}

	/**
	 * Display key.
	 *
	 * @param Activation $activation Activation.
	 *
	 * @since 1.4.6
	 */
	protected function column_serial_id( $activation ) {
		$edit_url = admin_url( 'admin.php?page=wc-serial-numbers&id=' . $activation->get_serial_id() );

		return sprintf( '<a href="%1$s">#%2$s</a>', esc_url( $edit_url ), esc_html( $activation->get_serial_id() ) );
	}

	/**
	 * Display platform.
	 *
	 * @param Activation $activation Activation.
	 *
	 * @since 1.4.6
	 */
	protected function column_platform( $activation ) {
		return empty( $activation->get_platform() ) ? '&mdash;' : esc_html( $activation->get_platform() );
	}

	/**
	 * Display activation time.
	 *
	 * @param Activation $activation Activation.
	 *
	 * @since 1.4.6
	 */
	protected function column_activation_time( $activation ) {
		return empty( $activation->get_activation_time() ) ? '&mdash;' : esc_html( $activation->get_activation_time() );
	}
}
