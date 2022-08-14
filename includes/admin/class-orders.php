<?php

namespace PluginEver\WooCommerceSerialNumbers\Admin;

// don't call the file directly.
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
		add_filter( 'bulk_actions-edit-shop_order', [ __CLASS__, 'order_bulk_actions'] );
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_metaboxes' ) );
	}


	/**
	 * Add custom order actions.
	 *
	 * @param array $actions Bulk actions.
	 *
	 * @return array
	 */
	public static function order_bulk_actions( $actions ){
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
		$order          = wc_get_order( $post->ID );
		$items          = $order->get_items();
		$serial_numbers = [];
		foreach ( $items as $item ) {
			$product = $item->get_product();
			if ( $product->is_type( 'variation' ) ) {
				$product = $product->get_parent_id();
			}
			$serial_numbers[ $product ] = get_post_meta( $item->get_id(), '_wc_serial_numbers_serial_numbers', true );
		}
		?>
		<table class="widefat">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?></th>
				<th><?php esc_html_e( 'Serial Numbers', 'wc-serial-numbers' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $serial_numbers as $product_id => $serial_number ) : ?>
				<tr>
					<td><?php echo esc_html( get_the_title( $product_id ) ); ?></td>
					<td><?php echo esc_html( $serial_number ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
}
