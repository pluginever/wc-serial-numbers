<?php

namespace WooCommerceSerialNumbers\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Orders.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Orders {

	/**
	 * Orders constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
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

		// Display order keys in order details.
		add_action( 'woocommerce_after_order_itemmeta', array( __CLASS__, 'display_order_item_meta' ), 10, 3 );
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
	 * @param string $column Column name.
	 * @param int    $order_id Order ID.
	 *
	 * @since 1.2.0
	 */
	public static function add_order_serial_column_content( $column, $order_id ) {
		$order_status = wc_get_order( $order_id )->get_status();
		if ( 'order_serials' === $column ) {
			if ( ! wcsn_order_has_products( $order_id ) || ! in_array( $order_status, array( 'completed', 'processing' ), true ) ) {
				echo '&mdash;';
			} else {
				if ( wcsn_order_is_fullfilled( $order_id ) ) {
					$style = 'color:green';
					$title = __( 'Order is fullfilled.', 'wc-serial-numbers' );
				} else {
					$style = 'color:red';
					$title = __( 'Order is not fullfilled.', 'wc-serial-numbers' );
				}
				$url = add_query_arg( array( 'order_id' => $order_id ), admin_url( 'admin.php?page=wc-serial-numbers' ) );
				printf( '<a href="%s" title="%s"><span class="dashicons dashicons-lock" style="%s"></span></a>', esc_url( $url ), esc_html( $title ), esc_attr( $style ) );
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
	 * @param string $action Action name.
	 * @param array  $order_ids Order IDs.
	 *
	 * @since 1.2.0
	 * @return string
	 */
	public function handle_order_bulk_action( $redirect_to, $action, $order_ids ) {
		if ( in_array( $action, array( 'wcsn_add_keys', 'wcsn_remove_keys' ), true ) ) {
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

	/**
	 * Show order item meta.
	 *
	 * @param int            $item_id Item ID.
	 * @param \WC_Order_Item $item Item.
	 * @param \WC_Product    $product Product.
	 *
	 * @since 1.0.0
	 */
	public static function display_order_item_meta( $item_id, $item, $product ) {
		$order_id = wc_get_order_id_by_order_item_id( $item_id );
		if ( ! $order_id || ! $product || ! wcsn_is_product_enabled( $product->get_id() ) ) {
			return;
		}
		$keys = wcsn_get_keys(
			array(
				'order_id'   => $order_id,
				'product_id' => $product->get_id(),
				'limit'      => - 1,
			)
		);

		if ( empty( $keys ) ) {
			return;
		}

		echo '<p style="color: #888;">' . esc_html__( 'Serial keys sold with this product:', 'wc-serial-numbers' ) . '</p>';

		foreach ( $keys as $index => $key ) {
			$data = array(
				'key'              => array(
					'label' => __( 'Key', 'wc-serial-numbers' ),
					'value' => '<code>' . $key->get_key() . '</code>',
				),
				'expire_date'      => array(
					'label' => __( 'Expire date', 'wc-serial-numbers' ),
					'value' => $key->get_expire_date() ? $key->get_expire_date() : __( 'Lifetime', 'wc-serial-numbers' ),
				),
				'activation_limit' => array(
					'label' => __( 'Activation limit', 'wc-serial-numbers' ),
					'value' => $key->get_activation_limit() ? $key->get_activation_limit() : __( 'Unlimited', 'wc-serial-numbers' ),
				),
				'status'           => array(
					'label' => __( 'Status', 'wc-serial-numbers' ),
					'value' => $key->get_status_label(),
				),
			);

			$data = apply_filters( 'wc_serial_numbers_admin_order_item_data', $data, $key, $item, $product, $order_id );
			if ( empty( $data ) ) {
				continue;
			}

			?>
			<table cellspacing="0" class="display_meta wcsn-admin-order-item-meta" style="margin-bottom: 10px;">
				<tbody>
				<tr>
					<th colspan="2">
						<?php // translators: %s is the item number. ?>
						<?php printf( '#%s:', esc_html( $index + 1 ) ); ?>
					</th>
				</tr>
				<?php foreach ( $data as $prop => $field ) : ?>
					<tr class="<?php echo sanitize_html_class( $prop ); ?>">
						<th><?php echo esc_html( $field['label'] ); ?>:</th>
						<td><?php echo wp_kses_post( $field['value'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<td colspan="2">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-serial-numbers&edit=' . $key->get_id() ) ); ?>"><?php esc_html_e( 'View Details', 'wc-serial-numbers' ); ?></a>
					</td>
				</tr>
				</tbody>
			</table>
			<?php
		}
	}
}
