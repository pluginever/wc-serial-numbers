<?php

namespace PluginEver\WooCommerceSerialNumbers\Admin;

// don't call the file directly.
use PluginEver\WooCommerceSerialNumbers\Helper;
use PluginEver\WooCommerceSerialNumbers\Keys;

defined( 'ABSPATH' ) || exit();


/**
 * Admin product related functionalities.
 *
 * @since 1.3.1
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Orders {

	/**
	 * Orders constructor.
	 *
	 * @since 1.3.1
	 */
	public function __construct() {
//		add_filter( 'manage_edit-shop_order_columns', array( __CLASS__, 'add_order_column' ) );
//		add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'render_order_column' ), 10, 2 );
//		add_filter( 'bulk_actions-edit-shop_order', [ __CLASS__, 'order_bulk_actions' ] );
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_metaboxes' ) );
	}

	/**
	 * Add a column to the WooCommerce Orders admin screen to indicate whether an order contains a serial number Product.
	 *
	 * @param array $columns The current list of columns
	 *
	 * @since 3.0.1
	 *
	 * @return array
	 */
	public static function add_order_column( $columns ) {
		$column_header = '<span class="serial_numbers_product_head tips" data-tip="' . esc_attr__( 'Contains Serial Numbers Product', 'wc-serial-numbers' ) . '">' . esc_attr__( 'Serial Numbers Product', 'wc-serial-numbers' ) . '</span>';
		$key           = array_search( 'shipping_address', array_keys( $columns ), true ) + 1;
		$new_column    = array_slice( $columns, 0, $key, true ) + [ 'serial_numbers_product' => $column_header ] + array_slice( $columns, $key, null, true );

		return array_merge(
			array_slice( $columns, 0, $key ),
			array(
				'serial_numbers' => $column_header,
			),
			array_slice( $columns, $key ) );
	}

	/**
	 * Add a column to the WooCommerce Orders admin screen to indicate whether an order contains an API Product.
	 *
	 * @param string $column The string of the current column
	 * @param int $post_id
	 *
	 * @since 3.0.1
	 *
	 */
	public static function render_order_column( $column, $post_id ) {
		if ( 'serial_numbers' === $column ) {
			if ( Helper::get_order_line_items( $post_id ) ) {
//				$has_activations = WC_AM_API_ACTIVATION_DATA_STORE()->has_activations_for_order_id( $post_id );
//				if ( $has_activations ) {
				echo '<span class="serial_numbers_order_has_keys tips" data-tip="' . esc_attr__( 'Has all keys.', 'wc-serial-numbers' ) . '"></span>';
//				} else {
//					echo '<span class="serial_numbers_order_has_no_keys tips" data-tip="' . esc_attr__( 'Has missing keys.', 'wc-serial-numbers' ) . '"></span>';
//				}
			} else {
				echo '<span class="normal_order">&ndash;</span>';
			}
		}
	}

	/**
	 * Add custom order actions.
	 *
	 * @param array $actions Bulk actions.
	 *
	 * @return array
	 */
	public static function order_bulk_actions( $actions ) {
		$actions['update_ordered_keys'] = __( 'Update ordered keys', 'wc-serial-numbers' );

		return $actions;
	}

	/**
	 * Register order metaboxes.
	 *
	 * @since 1.2.5
	 */
	public static function register_metaboxes() {
		add_meta_box( 'order-serial-numbers', __( 'Serial Numbers', 'wc-serial-numbers' ), array( __CLASS__, 'order_metabox' ), 'shop_order', 'advanced', 'high' ); //phpcs:ignore
	}

	/**
	 * Order metabox.
	 *
	 * @param \WP_Post $post The post object.
	 *
	 * @since 1.2.5
	 */
	public static function order_metabox( $post ) {
		$order = Helper::get_order_object( $post->ID );
		if ( ! $order ) {
			return;
		}
		$line_items = Helper::get_order_line_items( $order->get_id() );
		if ( ! $line_items ) {
			echo '<p>' . esc_html__( 'No keys found.', 'wc-serial-numbers' ) . '</p>';

			return;
		}
		$line_items_ids = array_map( function ( $item ) {
			return $item['product_id'];
		}, $line_items );
//		var_dump($line_items);
//		$keys           = Keys::query( [
//			'order_id__in' => $order->get_id(),
//		] );

		var_dump( $line_items_ids );
		?>
		<table class="wp-list-table widefat fixed wcsn-orders-table">
			<thead>
			<tr>
				<th><?php echo esc_html__( 'Product' ); ?></th>
				<th><?php echo esc_html__( 'Serial Numbers' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $line_items as $item ) : ?>
				<tr>
					<td><a href="<?php echo esc_attr( get_edit_post_link( $item['product_id'] ) ); ?>"><?php echo esc_html( $item['name'] ); ?></a></td>
					<td>
						<?php
						$keys = Keys::query( [
							'order_id__in'   => $order->get_id(),
							'product_id__in' => $item['product_id'],
						] );
						if ( $keys ) {
							echo '<ul class="serial-numbers-keys-list">';
							foreach ( $keys as $key ) {
								echo '<li>';
								echo sprintf( '<code class="serial-numbers-key1">%s</code>', $key->get_decrypted_key() );
								echo '</li>';
							}

							echo '</ul>';
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
}
