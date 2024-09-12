<?php

namespace WooCommerceSerialNumbers\Admin\ListTables;

use WooCommerceSerialNumbers\Models\Generator;

defined( 'ABSPATH' ) || exit;

/**
 * Class GeneratorsTable.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbersPro\Admin\ListTables
 */
class GeneratorsTable extends \WooCommerceSerialNumbers\Admin\ListTables\ListTable {
	/**
	 * Current page URL.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $base_url;

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

		$this->base_url = admin_url( 'admin.php?page=wc-serial-numbers-generators' );
	}

	/**
	 * Prepare table data.
	 *
	 * @since 1.4.6
	 */
	public function prepare_items() {
		wp_verify_nonce( '_wpnonce' );
		$per_page              = $this->get_items_per_page( 'wcsn_stocks_per_page' );
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$orderby               = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'order_date';
		$order                 = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$status                = ( ! empty( $_GET['status'] ) ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		$args = array(
			'limit'   => $per_page,
			'page'    => $current_page,
			'orderby' => $orderby,
			'order'   => $order,
			'status'  => $status,
		);

		/**
		 * Filter the query arguments for the list table.
		 *
		 * @param array $args An associative array of arguments.
		 *
		 * @since 1.0.0
		 */
		$args = apply_filters( 'wc_serial_numbers_pro_generators_table_query_args', $args );

		$args['no_found_rows'] = false;
		$this->items           = Generator::results( $args );
		$this->total_count     = Generator::count( $args );

		$this->set_pagination_args(
			array(
				'total_items' => $this->total_count,
				'per_page'    => $per_page,
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
	 * Returns an associative array listing all the views that can be used with this table.
	 *
	 * @since 1.0.0
	 * @return string[] Array of views.
	 */
	public function get_views() {
		wp_verify_nonce( '_wpnonce' );
		$current      = ( ! empty( $_GET['status'] ) ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		$status_links = array();
		$statuses     = array_merge(
			array(
				'all' => __( 'All', 'wc-serial-numbers-pro' ),
			),
			Generator::get_statuses()
		);

		foreach ( $statuses as $status => $label ) {
			$link  = 'all' === $status ? $this->base_url : add_query_arg( 'status', $status, $this->base_url );
			$args  = 'all' === $status ? array() : array( 'status' => $status );
			$count = Generator::count( $args );
			$label = sprintf( '%s <span class="count">(%s)</span>', esc_html( $label ), number_format_i18n( $count ) );

			$status_links[ $status ] = array(
				'url'     => $link,
				'label'   => $label,
				'current' => $current === $status,
			);
		}

		return $this->get_views_links( $status_links );
	}

	/**
	 * Process bulk action.
	 *
	 * @param string $doaction Action name.
	 *
	 * @since 1.4.6
	 */
	public function process_bulk_actions( $doaction ) {
		wp_verify_nonce( '_wpnonce' );
		if ( $doaction ) {
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
				$generator = Generator::find( $id );
				if ( ! $generator ) {
					continue;
				}
				switch ( $doaction ) {
					case 'delete':
						$generator->delete();
						break;

					case 'activate':
						$generator->status = 'active';
						$generator->save();
						break;

					case 'deactivate':
						$generator->status = 'inactive';
						$generator->save();
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
			'delete'     => __( 'Delete', 'wc-serial-numbers-pro' ),
			'activate'   => __( 'Activate', 'wc-serial-numbers-pro' ),
			'deactivate' => __( 'Deactivate', 'wc-serial-numbers-pro' ),
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
			'name'             => __( 'Name', 'wc-serial-numbers-pro' ),
			'pattern'          => __( 'Pattern', 'wc-serial-numbers-pro' ),
			'validity_for'     => __( 'Validity For', 'wc-serial-numbers-pro' ),
			'activation_limit' => __( 'Activation Limit', 'wc-serial-numbers-pro' ),
			'status'           => __( 'Status', 'wc-serial-numbers-pro' ),
		);

		return apply_filters( 'wc_serial_numbers_pro_generators_table_columns', $columns );
	}

	/**
	 * Get sortable columns.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_sortable_columns() {
		return apply_filters(
			'wc_serial_numbers_pro_generators_table_sortable_columns',
			array(
				'name'             => array( 'name', false ),
				'pattern'          => array( 'pattern', false ),
				'valid_for'        => array( 'valid_for', false ),
				'activation_limit' => array( 'activation_limit', false ),
				'status'           => array( 'status', false ),
			)
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
		return 'name';
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param Generator $item Item being rendered.
	 *
	 * @return string
	 */
	protected function column_cb( $item ) {
		return "<input type='checkbox' name='ids[]' id='id_{$item->id}' value='{$item->id}' />";
	}

	/**
	 * Display name column.
	 *
	 * @param Generator $item Item being rendered.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function column_name( $item ) {
		return sprintf(
			'<a class="row-title" href="%s"><strong>%s</strong></a>',
			esc_url( add_query_arg( 'edit', $item->id, $this->base_url ) ),
			esc_html( $item->name )
		);
	}

	/**
	 * Column pattern.
	 *
	 * @param Generator $item Item being rendered.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function column_pattern( $item ) {
		return wp_kses_post( '<code>' . esc_html( $item->pattern ) . '</code>' );
	}

	/**
	 * Column validity_for.
	 *
	 * @param Generator $item Item being rendered.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function column_validity_for( $item ) {
		if ( empty( $item->validity_for ) ) {
			return esc_html__( 'Lifetime', 'wc-serial-numbers-pro' );
		}
		if ( ! empty( $item->validity_for ) ) {
			// translators: %d: number of days.
			return sprintf( _nx( '%d day <small>After Purchase</small>', '%d days <small>After Purchase</small>', $item->validity_for, 'valid for days', 'wc-serial-numbers-pro' ), $item->validity_for );
		}

		return '&mdash;';
	}

	/**
	 * Column activation_limit.
	 *
	 * @param Generator $item Item being rendered.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function column_activation_limit( $item ) {
		if ( empty( $item->activation_limit ) ) {
			return esc_html__( 'Unlimited', 'wc-serial-numbers-pro' );
		}

		if ( ! empty( $item->activation_limit ) ) {
			return number_format( $item->activation_limit );
		}

		return esc_html( $item->activation_limit );
	}

	/**
	 * Column status.
	 *
	 * @param Generator $item Item being rendered.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function column_status( $item ) {
		return $item->get_status_html();
	}

	/**
	 * Generates and displays row actions links.
	 *
	 * @param Generator $item The object.
	 * @param string    $column_name Current column name.
	 * @param string    $primary Primary column name.
	 *
	 * @since 1.0.0
	 * @return string Row actions output.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return null;
		}

		$actions = array(
			'id'     => sprintf( '#%d', esc_attr( $item->id ) ),
			'edit'   => sprintf(
				'<a href="%s">%s</a>',
				esc_url( add_query_arg( 'edit', $item->id, $this->base_url ) ),
				__( 'Edit', 'wc-serial-numbers-pro' )
			),
			'delete' => sprintf(
				'<a href="%s" class="del">%s</a>',
				esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'delete',
								'id'     => $item->id,
							),
							$this->base_url
						),
						'bulk-' . $this->_args['plural']
					)
				),
				__( 'Delete', 'wc-serial-numbers-pro' )
			),
		);
		// based on the status, add activate or deactivate action.
		if ( 'active' === $item->status ) {
			$actions['deactivate'] = sprintf(
				'<a href="%s" class="deactivate">%s</a>',
				esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'deactivate',
								'id'     => $item->id,
							),
							$this->base_url
						),
						'bulk-' . $this->_args['plural']
					)
				),
				__( 'Deactivate', 'wc-serial-numbers-pro' )
			);
		} else {
			$actions['activate'] = sprintf(
				'<a href="%s" class="activate">%s</a>',
				esc_url(
					wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'activate',
								'id'     => $item->id,
							),
							$this->base_url
						),
						'bulk-' . $this->_args['plural']
					)
				),
				__( 'Activate', 'wc-serial-numbers-pro' )
			);
		}

		return $this->row_actions( $actions );
	}
}
