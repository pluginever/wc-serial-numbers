<?php

namespace WooCommerceSerialNumbers\Admin\ListTables;

use WooCommerceSerialNumbersPro\Models\Generator;

defined( 'ABSPATH' ) || exit;

/**
 * Class GeneratorsTable.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin\ListTables
 */
class GeneratorsTable  extends ListTable {
	/**
	 * StockTable constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'generator', 'wc-serial-numbers-pro' ),
				'plural'   => __( 'generators', 'wc-serial-numbers-pro' ),
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
		$per_page              = $this->get_items_per_page( 'wcsn_stocks_per_page' );
		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$orderby               = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'order_date';
		$order                 = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$search                = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$product_id            = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : '';

		$args = array(
			'per_page'   => $per_page,
			'paged'      => $current_page,
			'orderby'    => $orderby,
			'order'      => $order,
			'product_id' => $product_id,
			'search'     => $search,
		);

		$query             = new \WP_Query( $args );
		$this->items       = wcsn_get_generators( $args );
		$this->total_count = wcsn_get_generators( array_merge( $args, array( 'count' => true ) ) );

		$this->set_pagination_args(
			array(
				'total_items' => $query->found_posts,
				'per_page'    => $per_page,
				'total_pages' => $query->found_posts > 0 ? ceil( $query->found_posts / $per_page ) : 0,
			)
		);
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		esc_html_e( 'No generators found.', 'wc-serial-numbers-pro' );
	}

	/**
	 * Process bulk action.
	 *
	 * @param string $doaction Action name.
	 *
	 * @since 1.4.6
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
				$key = Generator::get( $id );
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
			'delete' => __( 'Delete', 'wc-serial-numbers-pro' ),
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
			'pattern'          => __( 'Pattern', 'wc-serial-numbers-pro' ),
			'product'          => __( 'Product', 'wc-serial-numbers-pro' ),
			'type'             => __( 'Type', 'wc-serial-numbers-pro' ),
			'activation_limit' => __( 'Activation Limit', 'wc-serial-numbers-pro' ),
			'validity'         => __( 'Validity', 'wc-serial-numbers-pro' ),
			'generate'         => __( 'Generate', 'wc-serial-numbers-pro' ),
		);

		return apply_filters( 'wc_serial_numbers_stock_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		return array(
			'product' => array( 'product_id', false ),
		);
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'column_pattern';
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param Generator $item The item being acted upon.
	 *
	 * @return string
	 */
	protected function column_cb( $item ) {
		return "<input type='checkbox' name='ids[]' id='id_{$item->get_id()}' value='{$item->get_id()}' />";
	}

	/**
	 * column pattern
	 *
	 * @param $item
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function column_pattern( $item ) {
		$row_actions           = array();
		$base_url              = admin_url( 'admin.php?page=wc-serial-numbers-tools&tab=generators' );
		$edit_url              = add_query_arg( [ 'edit' => $item->get_id() ], $base_url );
		$delete_url            = add_query_arg(
			[
				'action' => 'delete',
				'id'     => $item->get_id(),
			],
			$base_url
		);
		$row_actions['edit']   = sprintf( '<a href="%1$s">%2$s</a>', $edit_url, __( 'Edit', 'wc-serial-numbers-pro' ) );
		$row_actions['delete'] = sprintf( '<a href="%1$s">%2$s</a>', $delete_url, __( 'Delete', 'wc-serial-numbers-pro' ) );
		$pattern               = get_post_meta( $item->ID, 'pattern', true );

		return sprintf( '<code>%1$s</code> %2$s', $pattern, $this->row_actions( $row_actions ) );
	}

	/**
	 * column generate
	 *
	 * @param $item
	 *
	 * @since 1.0.0
	 */
	public function column_generate( $item ) {
		?>
		<input type="number" class="serial_count" maxlength="2" min="1" max="10000" style="width: 90px;"/>
		<?php
		submit_button(
			__( 'Generate', 'wc-serial-numbers-pro' ),
			'secondary',
			'',
			false,
			array(
				'class'      => 'generate-serials',
				'data-id'    => $item->get_id(),
				'data-nonce' => wp_create_nonce( 'generate_serials' ),
			)
		);
		?>

		<div class="spinner" style="margin-top: -5px;float:none;"></div>
		<?php
	}

	/**
	 * since 1.0.0
	 *
	 * @param Generator $item Item.
	 * @param string    $column_name
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'product':
				$product_id = get_post_meta( $item->ID, 'product_id', true );
				$line       = ! empty( $product_id ) ? get_the_title( $product_id ) : '&#45;';
				echo ! empty( $product_id ) ? '<a href="' . get_edit_post_link( $product_id ) . '">' . $line . '</a>' : $line;
				break;
			case 'type':
				$type = get_post_meta( $item->ID, 'type', true );
				echo empty( $item->get_ ) ? '&#45;' : ucfirst( $type );
				break;
			case 'activation_limit':
				$activation_limit = get_post_meta( $item->ID, 'activation_limit', true );
				echo empty( $activation_limit ) ? __( 'Unlimited', 'wc-serial-numbers-pro' ) : $activation_limit;
				break;
			case 'validity':
				$validity = get_post_meta( $item->ID, 'validity', true );
				echo empty( $validity ) ? __( 'Lifetime', 'wc-serial-numbers-pro' ) : $validity;
				break;
			default:
				return '';
		}

		return '';
	}
}
