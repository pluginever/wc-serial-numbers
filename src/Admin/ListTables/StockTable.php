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
		$per_page              = 20;
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$orderby               = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_SPECIAL_CHARS );
		$order                 = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_SPECIAL_CHARS );
		$search                = filter_input( INPUT_GET, 's', FILTER_SANITIZE_SPECIAL_CHARS );
		$product_id            = filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT );

		$query_args = array(
			'posts_per_page' => $per_page,
			'fields'         => 'ids',
			's'              => $search,
			'paged'          => $current_page,
			'orderby'        => $orderby,
			'order'          => $order,
			'post__in'       => $product_id ? wp_parse_id_list( $product_id ) : array(),
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
	 * Adds the order and product filters to the licenses list.
	 *
	 * @param string $which The location of the extra table nav markup: 'top' or 'bottom'.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			echo '<div class="alignleft actions">';
			$this->product_dropdown();
			submit_button( __( 'Filter', 'wc-serial-numbers' ), '', 'filter-action', false );

			echo '</div>';
		}
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'product' => __( 'Product', 'wc-serial-numbers' ),
			'source'  => __( 'Source', 'wc-serial-numbers' ),
			'sold'    => __( 'Sold', 'wc-serial-numbers' ),
			'stock'   => __( 'Stock', 'wc-serial-numbers' ),
		);

		return apply_filters( 'wc_serial_numbers_stock_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$columns = array(
			'product' => array( 'product_id', false ),
		);

		return apply_filters( 'wc_serial_numbers_stock_table_sortable_columns', $columns );
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
	 * @param \WC_Product $item The current item.
	 *
	 * @return string
	 */
	public function column_product( $item ) {
		$product_id = $item->get_id();
		$product    = wc_get_product( $product_id );
		$title      = $product->get_formatted_name();
		$edit_link  = wcsn_get_edit_product_link( $product_id );

		$actions = array(
			'id'   => sprintf( '<span>ID: %d</span>', esc_attr( $item->get_id() ) ),
			'edit' => sprintf( '<a href="%s">%s</a>', esc_url( $edit_link ), esc_html__( 'Edit', 'wc-serial-numbers' ) ),
		);

		return sprintf( '<a href="%s">%s</a> %s', esc_url( $edit_link ), wp_kses_post( $title ), $this->row_actions( $actions ) );
	}

	/**
	 * since 1.0.0
	 *
	 * @param \WC_Product $item The current item.
	 * @param string      $column_name The current column name.
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'sold':
				$sold_count = wcsn_get_keys(
					array(
						'status__in' => array( 'sold', 'expired' ),
						'product_id' => $item->get_id(),
						'count'      => true,
					)
				);

				return number_format_i18n( $sold_count );
			case 'source':
				$source = get_post_meta( $item->get_id(), '_serial_key_source', true );
				if ( 'custom_source' === $source ) {
					$label = esc_html__( 'Manual', 'wc-serial-numbers' );
				} elseif ( 'generator_rule' === $source ) {
					$label = esc_html__( 'Generator Rule', 'wc-serial-numbers' );
				} elseif ( 'auto_generated' === $source ) {
					$label = esc_html__( 'Auto Generated', 'wc-serial-numbers' );
				} else {
					$label = esc_html__( 'Unknown', 'wc-serial-numbers' );
				}

				return $label;

			case 'stock':
				$stocks = wcsn_get_stocks_count();
				if ( array_key_exists( $item->get_id(), $stocks ) ) {
					$stock = number_format_i18n( $stocks[ $item->get_id() ] );
					$link  = admin_url( 'admin.php?page=wc-serial-numbers&status=available&product_id=' . $item->get_id() );

					return sprintf( '<a href="%s">%s</a>', esc_url( $link ), $stock );
				} else {
					return '&mdash;';
				}
			default:
				return apply_filters( 'wc_serial_numbers_stock_table_column_content', '', $item, $column_name );
		}
	}
}
