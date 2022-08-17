<?php

namespace PluginEver\WooCommerceSerialNumbers\Admin\List_Tables;

// don't call the file directly.
use PluginEver\WooCommerceSerialNumbers\Activations;

defined( 'ABSPATH' ) || exit();

/**
 * Activations admin list table.
 *
 * @since #.#.#
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Activations_List_Table extends List_Table {
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
	 * @since #.#.#
	 */
	public function prepare_items() {
		$per_page              = $this->get_items_per_page( 'wcsn_activations_per_page' );
		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$orderby               = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'order_date';
		$order                 = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$search                = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$product_id            = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : '';
		$order_id              = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : '';
		$customer_id           = isset( $_GET['customer_id'] ) ? absint( $_GET['customer_id'] ) : '';
		$id                    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : '';


		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'order_date' !== $orderby ) {
			$args['orderby'] = $orderby;
		}

		$args = array(
			'per_page'        => $per_page,
			'paged'           => $current_page,
			'orderby'         => $orderby,
			'order'           => $order,
			'product_id__in'  => $product_id,
			'order_id__in'    => $order_id,
			'customer_id__in' => $customer_id,
			'include'         => $id,
			'search'          => $search,
		);

		$this->items           = Activations::query( $args );
		$this->total_count     = Activations::query( $args, true );

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
		esc_html_e( 'No activations found.', 'wc-serial-numbers' );
	}


	/**
	 * Show the search field
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @since 1.0.0
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
			<label class="screen-reader-text" for="<?php echo $input_id; ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id; ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @since 1.0.0
	 * @return array $views All the views sellable
	 */
	public function get_views() {
	}

	/**
	 * Adds the order and product filters to the licenses list.
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( $which === 'top' ) {
			echo '<div class="alignleft actions">';
			$this->order_dropdown();
			$this->product_dropdown();
			$this->customer_dropdown();
			submit_button( __( 'Filter', 'wc-serial-numbers' ), '', 'filter-action', false );
			echo '</div>';
		}
	}

	/**
	 * Process bulk action.
	 *
	 * @param string $doaction Action name.
	 *
	 * @since #.#.#
	 */
	public function process_bulk_actions( $doaction ) {
		if ( $doaction ) {
			if ( isset( $_REQUEST['id'] ) ) {
				$ids      = wp_parse_id_list( $_REQUEST['id'] );
				$doaction = ( - 1 !== $_REQUEST['action'] ) ? $_REQUEST['action'] : $_REQUEST['action2']; // phpcs:ignore
			} elseif ( isset( $_REQUEST['ids'] ) ) {
				$ids = array_map( 'absint', $_REQUEST['ids'] );
			} elseif ( wp_get_referer() ) {
				wp_safe_redirect( wp_get_referer() );
				exit;
			}

			foreach ( $ids as $id ) { // Check the permissions on each.
				$key = Activations::get( $id );
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
			'cb'               => '<input type="checkbox" />',
			'pattern'          => __( 'Pattern', 'wc-serial-numbers' ),
			'type'             => __( 'Type', 'wc-serial-numbers' ),
			'activation_limit' => __( 'Activation Limit', 'wc-serial-numbers' ),
			'action'           => __( 'Action', 'wc-serial-numbers' ),
		);

		return apply_filters( 'wc_serial_numbers_activations_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'pattern'          => array( 'Pattern', false ),
			'type'             => array( 'Type', false ),
			'activation_limit' => array( 'Activation Limit', false ),
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
	 * Display key.
	 *
	 * @param $item
	 *
	 * @since #.#.#
	 */
	protected function column_pattern( $item ) {
		$edit_url            = add_query_arg( [ 'edit' => $item->id ], admin_url( 'admin.php?page=wc-serial-numbers-activations' ) );
		$delete_url          = add_query_arg( [ 'id' => $item->id, 'action' => 'delete' ], admin_url( 'admin.php?page=wc-serial-numbers-activations' ) );
		$generate_url        = add_query_arg( [ 'generate' => $item->id ], admin_url( 'admin.php?page=wc-serial-numbers-activations' ) );
		$actions['edit']     = sprintf( '<a href="%1$s">%2$s</a>', $edit_url, __( 'Edit', 'wc-serial-numbers' ) );
		$actions['generate'] = sprintf( '<a href="%1$s">%2$s</a>', $generate_url, __( 'Generate', 'wc-serial-numbers' ) );
		$actions['delete']   = sprintf( '<a href="%1$s">%2$s</a>', $delete_url, __( 'Delete', 'wc-serial-numbers' ) );

		return sprintf( '<code class="wsn-generator">%1$s</code> %2$s', $item->key, $this->row_actions( $actions ) );
	}

}
