<?php

namespace WooCommerceSerialNumbers\Admin\ListTables;

defined( 'ABSPATH' ) || exit;

/**
 * Class StockTable.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin\ListTables
 */
class StockTable extends ListTable {
	/**
	 * StockTable constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'stock', 'wc-serial-numbers' ),
				'plural'   => __( 'stocks', 'wc-serial-numbers' ),
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
		$order_id              = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : '';
		$customer_id           = isset( $_GET['customer_id'] ) ? absint( $_GET['customer_id'] ) : '';
		$id                    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : '';

		$query_args = array(
			'posts_per_page' => $per_page,
			'fields'         => 'ids',
			'search'         => $search,
			'paged'          => $current_page,
			'post__in'       => $id ? wp_parse_id_list( $id ) : array(),
			'meta_query'     => array( // @codingStandardsIgnoreLine
				'relation' => 'AND',
				array(
					'key'     => '_serial_key_source',
					'value'   => 'custom_source',
					'compare' => '=',
				)
			),
		);
		$post_ids   = wcsn_get_products( $query_args );

		$this->items       = array_map( 'wc_get_product', $post_ids );
		$this->total_count = wcsn_get_products( array_merge( $query_args, array( 'count' => true ) ) );

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
		esc_html_e( 'No products selling serial keys from "stock" found.', 'wc-serial-numbers' );
	}

	/**
	 * Retrieve the view types
	 *
	 * @since 1.0.0
	 * @return array $views All the views sellable
	 */
	public function get_views() {
		return parent::get_views();
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'product' => __( 'Product', 'wc-serial-numbers' ),
			'sku'     => __( 'SKU', 'wc-serial-numbers' ),
			'stock'   => __( 'Stock', 'wc-serial-numbers' ),
			'action'  => __( 'Action', 'wc-serial-numbers' ),
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
		return 'product';
	}

	/**
	 * since 1.0.0
	 *
	 * @param \WC_Product $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'product':
				$product_id = $item->get_id();
				$product    = wc_get_product( $product_id );
				$title      = $product->get_title();
				$edit_link  = get_edit_post_link( $product_id );

				return sprintf( '<a href="%s">%s</a>', $edit_link, $title);
			case 'sku':
				return $item->get_sku();
			case 'stock':
				$stock = number_format_i18n( wcsn_get_product_stock( $item->get_id() ) );
				return sprintf('<a href="%s">%s</a>', admin_url( 'admin.php?page=wc-serial-numbers&product_id=' . $item->get_id() ), $stock);
			case 'action':
				$product_id = $item->get_id();
				$edit_link  = get_edit_post_link( $product_id );
				return sprintf( '<a href="%s">%s</a>', $edit_link, __( 'Edit', 'wc-serial-numbers' ) );
			default:
				return '';
		}
	}
}
