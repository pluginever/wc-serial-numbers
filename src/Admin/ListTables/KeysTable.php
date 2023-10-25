<?php

namespace WooCommerceSerialNumbers\Admin\ListTables;

use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class KeysTable.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin\ListTables
 */
class KeysTable extends ListTable {
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
	 * available number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $available_count = 0;

	/**
	 * On hold number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $pending_count = 0;

	/**
	 * Sold number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $sold_count = 0;

	/**
	 * Expired number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $expired_count = 0;

	/**
	 * Inactive number
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $cancelled_count = 0;

	/**
	 * Serial_Keys_List_Table constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'key', 'wc-serial-numbers' ),
				'plural'   => __( 'keys', 'wc-serial-numbers' ),
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
		$per_page              = $this->get_items_per_page( 'wc_serial_numbers_keys_per_page' );
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$status                = filter_input( INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS );
		$orderby               = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_SPECIAL_CHARS );
		$order                 = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_SPECIAL_CHARS );
		$search                = filter_input( INPUT_GET, 's', FILTER_SANITIZE_SPECIAL_CHARS );
		$product_id            = filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT );
		$order_id              = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		$customer_id           = filter_input( INPUT_GET, 'customer_id', FILTER_SANITIZE_NUMBER_INT );
		$id                    = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( ! empty( $status ) && ! array_key_exists( $status, wcsn_get_key_statuses() ) ) {
			$status = 'available';
		}

		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'order_date' !== $orderby ) {
			$args['orderby'] = $orderby;
		}

		$args = array(
			'per_page'    => $per_page,
			'paged'       => $current_page,
			'orderby'     => $orderby,
			'order'       => $order,
			'status'      => $status,
			'product_id'  => $product_id,
			'order_id'    => $order_id,
			'customer_id' => $customer_id,
			'include'     => $id,
			'search'      => $search,
		);

		$this->items           = Key::query( $args );
		$this->available_count = Key::count( array_merge( $args, array( 'status' => 'available' ) ) );
		$this->pending_count   = Key::count( array_merge( $args, array( 'status' => 'pending' ) ) );
		$this->sold_count      = Key::count( array_merge( $args, array( 'status' => 'sold' ) ) );
		$this->expired_count   = Key::count( array_merge( $args, array( 'status' => 'expired' ) ) );
		$this->cancelled_count = Key::count( array_merge( $args, array( 'status' => 'cancelled' ) ) );
		$this->total_count     = array_sum( array( $this->available_count, $this->sold_count, $this->pending_count, $this->expired_count, $this->cancelled_count ) );

		switch ( $status ) {
			case 'available':
				$total_items = $this->available_count;
				break;
			case 'pending':
				$total_items = $this->pending_count;
				break;
			case 'sold':
				$total_items = $this->sold_count;
				break;
			case 'expired':
				$total_items = $this->expired_count;
				break;
			case 'cancelled':
				$total_items = $this->cancelled_count;
				break;
			case 'any':
			default:
				$total_items = $this->total_count;
				break;
		}

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => get_user_option( 'serials_per_page' ),
				'total_pages' => $total_items > 0 ? ceil( $total_items / $per_page ) : 0,
			)
		);
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		printf( '%s %s', esc_html__( 'No keys found.', 'wc-serial-numbers' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-serial-numbers&add' ) ) . '">' . esc_html__( 'Add new key', 'wc-serial-numbers' ) . '</a>' );
		// Show a documentation about key's statuses.
		?>
		<h4>
			<?php esc_attr_e( 'Keys can have one of the following statuses:', 'wc-serial-numbers' ); ?>
		</h4>
		<ul>
			<li>
				<strong><?php esc_attr_e( 'Available', 'wc-serial-numbers' ); ?></strong>
				&dash;
				<?php esc_attr_e( 'This means the key is available for purchase.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<strong><?php esc_attr_e( 'Pending', 'wc-serial-numbers' ); ?></strong>
				&dash;
				<?php esc_attr_e( 'This means the key has been sold, but the order has not been completed yet.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<strong><?php esc_attr_e( 'Sold', 'wc-serial-numbers' ); ?></strong>
				&dash;
				<?php esc_attr_e( 'This means the key has been sold, and the order has been completed.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<strong><?php esc_attr_e( 'Expired', 'wc-serial-numbers' ); ?></strong>
				&dash;
				<?php esc_attr_e( 'This means the key has expired and is no longer valid.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<strong><?php esc_attr_e( 'Cancelled', 'wc-serial-numbers' ); ?></strong>
				&dash;
				<?php esc_attr_e( 'This means the key has been cancelled and is no longer available for purchase or use.', 'wc-serial-numbers' ); ?>
			</li>
		</ul>
		<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @since 1.0.0
	 * @return array $views All the views sellable
	 */
	public function get_views() {
		$current         = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$available_count = '&nbsp;<span class="count">(' . $this->available_count . ')</span>';
		$pending_count   = '&nbsp;<span class="count">(' . $this->pending_count . ')</span>';
		$sold_count      = '&nbsp;<span class="count">(' . $this->sold_count . ')</span>';
		$expired_count   = '&nbsp;<span class="count">(' . $this->expired_count . ')</span>';
		$cancelled_count = '&nbsp;<span class="count">(' . $this->cancelled_count . ')</span>';
		$total_count     = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$url             = admin_url( 'admin.php?page=wc-serial-numbers' );
		$views           = array(
			'all'       => sprintf(
				'<a href="%s" title="%s" %s>%s</a>',
				remove_query_arg( 'status', $url ),
				__( 'All keys.', 'wc-serial-numbers' ),
				'all' === $current || '' === $current ? ' class="current"' : '',
				__( 'All', 'wc-serial-numbers' ) . $total_count
			),
			'available' => sprintf(
				'<a href="%s" title="%s" %s>%s</a>',
				add_query_arg( 'status', 'available', $url ),
				__( 'Available for sell.', 'wc-serial-numbers' ),
				'available' === $current ? ' class="current"' : '',
				__( 'Available', 'wc-serial-numbers' ) . $available_count
			),
			'pending'   => sprintf(
				'<a href="%s" title="%s" %s>%s</a>',
				add_query_arg( 'status', 'pending', $url ),
				__( 'Pending payment.', 'wc-serial-numbers' ),
				'pending' === $current ? ' class="current"' : '',
				__( 'Pending', 'wc-serial-numbers' ) . $pending_count
			),
			'sold'      => sprintf(
				'<a href="%s" title="%s" %s>%s</a>',
				add_query_arg( 'status', 'sold', $url ),
				__( 'Sold keys.', 'wc-serial-numbers' ),
				'sold' === $current ? ' class="current"' : '',
				__( 'Sold', 'wc-serial-numbers' ) . $sold_count
			),
			'expired'   => sprintf(
				'<a href="%s" title="%s" %s>%s</a>',
				add_query_arg( 'status', 'expired', $url ),
				__( 'Expired keys.', 'wc-serial-numbers' ),
				'expired' === $current ? ' class="current"' : '',
				__( 'Expired', 'wc-serial-numbers' ) . $expired_count
			),
			'cancelled' => sprintf(
				'<a href="%s" title="%s" %s>%s</a>',
				add_query_arg( 'status', 'cancelled', $url ),
				__( 'Cancelled keys.', 'wc-serial-numbers' ),
				'cancelled' === $current ? ' class="current"' : '',
				__( 'Cancelled', 'wc-serial-numbers' ) . $cancelled_count
			),
		);

		return $views;
	}

	/**
	 * Adds the order and product filters to the licenses list.
	 *
	 * @param string $which The location of the extra table nav markup: 'top' or 'bottom'.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
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
	 * @since 1.4.6
	 */
	public function process_bulk_actions( $doaction ) {
		if ( $doaction && check_ajax_referer( 'bulk-' . $this->_args['plural'] ) ) {
			if ( wp_unslash( isset( $_REQUEST['id'] ) ) ) {
				$ids      = wp_parse_id_list( wp_unslash( $_REQUEST['id'] ) );
				$doaction = ( - 1 !== $_REQUEST['action'] ) ? $_REQUEST['action'] : $_REQUEST['action2']; // phpcs:ignore
			} elseif ( isset( $_REQUEST['ids'] ) ) {
				$ids = array_map( 'absint', $_REQUEST['ids'] );
			} elseif ( wp_get_referer() ) {
				wp_safe_redirect( wp_get_referer() );
				exit;
			}

			foreach ( $ids as $id ) { // Check the permissions on each.
				$key = Key::get( $id );
				if ( ! $key ) {
					continue;
				}
				switch ( $doaction ) {
					case 'delete':
						$key->delete();
						break;
					case 'reset_activations':
						$key->reset_activations();
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
			'delete'            => __( 'Delete', 'wc-serial-numbers' ),
			'reset_activations' => __( 'Reset Activations', 'wc-serial-numbers' ),
		);
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'key'       => __( 'Key', 'wc-serial-numbers' ),
			'product'   => __( 'Product', 'wc-serial-numbers' ),
			'order'     => __( 'Order', 'wc-serial-numbers' ),
			'valid_for' => __( 'Validity', 'wc-serial-numbers' ),
		);

		if ( wcsn_is_software_support_enabled() ) {
			$columns['activation'] = __( 'Activation', 'wc-serial-numbers' );
		}

		$columns['order_date'] = __( 'Order Date', 'wc-serial-numbers' );
		$columns['status']     = __( 'Status', 'wc-serial-numbers' );

		return apply_filters( 'wc_serial_numbers_keys_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'key'         => array( 'serial_key', false ),
			'product'     => array( 'product_id', false ),
			'order'       => array( 'order_id', false ),
			'customer'    => array( 'customer', false ),
			'activation'  => array( 'activation_limit', false ),
			'expire_date' => array( 'expire_date', false ),
			'valid_for'   => array( 'valid_for', false ),
			'status'      => array( 'status', false ),
			'order_date'  => array( 'order_date', false ),
		);

		return apply_filters( 'wc_serial_numbers_keys_table_sortable_columns', $sortable_columns );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'key';
	}

	/**
	 * since 1.0.0
	 *
	 * @param \StdClass $item Item.
	 *
	 * @return string|void
	 */
	protected function column_cb( $item ) {
		return "<input type='checkbox' name='ids[]' id='id_{$item->id}' value='{$item->id}' />";
	}

	/**
	 * Display key.
	 *
	 * @param Key $item Item.
	 *
	 * @since 1.4.6
	 */
	protected function column_key( $item ) {
		$is_hidden  = 'yes' === get_option( 'wc_serial_numbers_hide_serial_number', 'yes' );
		$edit_url   = add_query_arg( array( 'edit' => $item->id ), admin_url( 'admin.php?page=wc-serial-numbers' ) );
		$delete_url = add_query_arg(
			array(
				'id'     => $item->id,
				'action' => 'delete',
			),
			admin_url( 'admin.php?page=wc-serial-numbers' )
		);
		// translators: %d: key id.
		$actions['id']     = sprintf( __( 'ID: %d', 'wc-serial-numbers' ), esc_html( $item->id ) );
		$actions['edit']   = sprintf( '<a href="%1$s">%2$s</a>', $edit_url, __( 'Edit', 'wc-serial-numbers' ) );
		$actions['delete'] = sprintf( '<a href="%1$s">%2$s</a>', wp_nonce_url( $delete_url, 'bulk-keys' ), __( 'Delete', 'wc-serial-numbers' ) );

		return sprintf( '%1$s %2$s', $item->print_key( $is_hidden ), $this->row_actions( $actions ) );
	}

	/**
	 * Display column product.
	 *
	 * @param Key $item Item.
	 *
	 * @since 1.4.6
	 */
	protected function column_product( $item ) {
		$product = wc_get_product( $item->product_id );

		return empty( $item->product_id ) || empty( $product ) ? '&mdash;' : sprintf( '<a href="%s" target="_blank">#%d - %s</a>', wcsn_get_edit_product_link( $product->get_id() ), $product->get_id(), $product->get_formatted_name() );
	}

	/**
	 * Display column order.
	 *
	 * @param Key $item Item.
	 *
	 * @since 1.4.6
	 */
	protected function column_order( $item ) {
		$order = $item->get_order();
		if ( empty( $order ) ) {
			return '&mdash;';
		}

		return sprintf( '<a href="%s">#%d - %s</a>', get_edit_post_link( $order->get_id() ), $order->get_id(), $order->get_formatted_billing_full_name() );
	}

	/**
	 * Display column customer.
	 *
	 * @param \StdClass $item Item.
	 *
	 * @since 1.4.6
	 */
	protected function column_customer( $item ) {
		if ( empty( $item->order_id ) ) {
			return '&mdash;';
		}
		$order = wc_get_order( $item->order_id );
		if ( empty( $order ) || empty( $order->get_id() ) ) {
			return '&mdash;';
		}

		return sprintf(
			'<a href="%s">%s (#%d - %s)</a>',
			get_edit_user_link( $order->get_customer_id() ),
			$order->get_formatted_billing_full_name(),
			$order->get_customer_id(),
			$order->get_billing_email()
		);
	}

	/**
	 * Display column activation.
	 *
	 * @param Key $key Key object.
	 *
	 * @since 1.4.6
	 */
	protected function column_activation( $key ) {
		$limit = ! empty( $key->activation_limit ) ? $key->activation_limit : '&infin;';
		$count = (int) $key->activation_count;
		$link  = add_query_arg(
			array(
				'serial_id' => $key->id,
				'page'      => 'wc-serial-numbers-activations',
			),
			admin_url( 'admin.php' )
		);

		$activated = sprintf( '<a href="%s">%s</a>', $link, $count );

		return sprintf( '<b>%s</b> / <b>%s</b>', $activated, $limit );
	}

	/**
	 * Display column valid for.
	 *
	 * @param Key $key Key object.
	 *
	 * @since 1.4.6
	 */
	protected function column_valid_for( $key ) {
		if ( ! empty( $key->get_validity() ) ) {
			return wp_kses_post(
				sprintf(
					// translators: %1$s: validity, %2$s: validity.
					_n(
						'<b>%s</b> Day <br><small>After purchase</small>',
						'<b>%s</b> Days <br><small>After purchase</small>',
						$key->get_validity(),
						'wc-serial-numbers'
					),
					number_format_i18n( $key->get_validity() )
				)
			);
		}
		return __( 'Lifetime', 'wc-serial-numbers' );
	}

	/**
	 * Display column order date.
	 *
	 * @param Key $key Key object.
	 *
	 * @since 1.4.6
	 */
	protected function column_order_date( $key ) {
		if ( ! empty( $key->order_date ) && '0000-00-00 00:00:00' !== $key->order_date ) {
			return wp_date( get_option( 'date_format' ), strtotime( $key->order_date ) );
		}
		return '&mdash;';
	}

	/**
	 * Display column status.
	 *
	 * @param Key $key Key object.
	 *
	 * @since 1.4.6
	 */
	protected function column_status( $key ) {
		return sprintf( "<span class='wcsn-key-status %s'>%s</span>", sanitize_html_class( $key->status ), ucfirst( $key->status ) );
	}
}
