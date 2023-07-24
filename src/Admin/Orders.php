<?php

namespace WooCommerceSerialNumbers\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Orders.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Orders extends \WooCommerceSerialNumbers\Lib\Singleton {

	/**
	 * Orders constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		// add custom order action.
		add_filter( 'woocommerce_order_actions', array( $this, 'add_order_action' ) );
		// handle custom order action.
		add_action( 'woocommerce_order_action_wcsn_add_keys', array( $this, 'handle_order_action' ) );
		add_action( 'woocommerce_order_action_wcsn_remove_keys', array( $this, 'handle_order_action' ) );
		add_filter( 'manage_edit-shop_order_columns', array( __CLASS__, 'add_order_serial_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'add_order_serial_column_content' ), 20, 2 );

		// Add order bulk action.
		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_order_bulk_action' ) );
		add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'handle_order_bulk_action' ), 10, 3 );
	}

	/**
	 * Add custom order action.
	 *
	 * @param array $actions Order actions.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function add_order_action( $actions ) {
		$actions['wcsn_add_keys']    = __( 'Add serial keys', 'wc-serial-numbers' );
		$actions['wcsn_remove_keys'] = __( 'Remove serial keys', 'wc-serial-numbers' );

		return $actions;
	}

	/**
	 * Handle custom order action.
	 *
	 * @param \WC_Order $order Order object.
	 *
	 * @since 1.0.0
	 */
	public function handle_order_action( $order ) {
		$order_id = $order->get_id();
		$action   = current_action();
		$action   = str_replace( 'woocommerce_order_action_', '', $action );
		if ( 'wcsn_add_keys' === $action ) {
			wcsn_order_update_keys( $order_id );
			// add a notice.
			WCSN()->add_notice( __( 'Serial keys added successfully to the order.', 'wc-serial-numbers' ) );
		} elseif ( 'wcsn_remove_keys' === $action ) {
			wcsn_order_remove_keys( $order_id );
			// add a notice.
			WCSN()->add_notice( __( 'Serial keys removed successfully from the order.', 'wc-serial-numbers' ) );
		}
	}

	/**
	 * Add order serial column.
	 *
	 * @param array $columns Order columns.
	 *
	 * @since 1.2.0
	 * @return array|string[]
	 */
	public static function add_order_serial_column( $columns ) {
		$postition = 3;
		$new       = array_slice( $columns, 0, $postition, true ) + array( 'order_serials' => '<span class="dashicons dashicons-lock"></span>' ) + array_slice( $columns, $postition, count( $columns ) - $postition, true );

		return $new;
	}

	/**
	 * Add order serial column content.
	 *
	 * @param string $column  Column name.
	 * @param int    $order_id Order ID.
	 *
	 * @since 1.2.0
	 */
	public static function add_order_serial_column_content( $column, $order_id ) {
		$order_status = wc_get_order( $order_id )->get_status();
		if ( 'order_serials' === $column ) {
			if ( ! wcsn_order_has_products( $order_id ) || ! in_array( $order_status, [ 'completed', 'processing' ], true ) ) {
				echo '&mdash;';
			} else {
				if ( wcsn_order_is_fullfilled( $order_id ) ) {
					$style = 'color:green';
					$title = __( 'Order is fullfilled.', 'wc-serial-numbers' );
				} else {
					$style = 'color:red';
					$title = __( 'Order is not fullfilled.', 'wc-serial-numbers' );
				}
				$url = add_query_arg( [ 'order_id' => $order_id ], admin_url( 'admin.php?page=wc-serial-numbers' ) );
				echo sprintf( '<a href="%s" title="%s"><span class="dashicons dashicons-lock" style="%s"></span></a>', esc_url( $url ), esc_html( $title ), esc_attr( $style ) );
			}
		}
	}

	/**
	 * Add order bulk action.
	 *
	 * @param array $actions Order actions.
	 *
	 * @since 1.2.0
	 * @return array
	 */
	public function add_order_bulk_action( $actions ) {
		$actions['wcsn_add_keys']    = __( 'Add serial keys', 'wc-serial-numbers' );
		$actions['wcsn_remove_keys'] = __( 'Remove serial keys', 'wc-serial-numbers' );

		return $actions;
	}

	/**
	 * Handle order bulk action.
	 *
	 * @param string $redirect_to Redirect URL.
	 * @param string $action      Action name.
	 * @param array  $order_ids   Order IDs.
	 *
	 * @since 1.2.0
	 * @return string
	 */
	public function handle_order_bulk_action( $redirect_to, $action, $order_ids ) {
		if ( in_array( $action, [ 'wcsn_add_keys', 'wcsn_remove_keys' ], true ) ) {
			foreach ( $order_ids as $order_id ) {
				switch ( $action ) {
					case 'wcsn_add_keys':
						wcsn_order_update_keys( $order_id );
						break;
					case 'wcsn_remove_keys':
						wcsn_order_remove_keys( $order_id );
						break;
				}
			}
			// Translators: %d: number of orders.
			WCSN()->add_notice( sprintf( __( '%d orders updated successfully.', 'wc-serial-numbers' ), count( $order_ids ) ) );
			$redirect_to = add_query_arg( 'bulk_action', $action, $redirect_to );
		}

		return $redirect_to;
	}

}
