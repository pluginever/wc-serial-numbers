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
		add_filter( 'manage_edit-shop_order_columns', array( __CLASS__, 'add_order_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'render_order_column' ), 10, 2 );
		add_filter( 'bulk_actions-edit-shop_order', [ __CLASS__, 'order_bulk_actions' ] );
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_metaboxes' ) );
	}


	/**
	 * Show booking data if a line item is linked to a booking ID.
	 */
	public function serial_numbers_display( $item_id, $item, $order ) {
//		$booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );

//		wc_get_template(
//			'order/booking-display.php',
//			array(
//				'booking_ids'       => $booking_ids,
//				'endpoint'          => $this->get_endpoint(),
//				'hide_item_details' => true,
//			),
//			'woocommerce-bookings',
//			WC_BOOKINGS_TEMPLATE_PATH
//		);
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
		$column_header = '<span class="wcsn-items-column-icon tips" data-tip="' . esc_attr__( 'Contains Serial Numbers Products', 'wc-serial-numbers' ) . '">' . esc_attr__( 'Serial Numbers Products', 'wc-serial-numbers' ) . '</span>';
		$key           = array_search( 'shipping_address', array_keys( $columns ), true ) + 1;

		return array_merge(
			array_slice( $columns, 0, $key ),
			array(
				'wcsn_items' => $column_header,
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
		if ( 'wcsn_items' === $column ) {
			if ( Helper::get_order_line_items( $post_id ) ) {
				echo '<span class="wcsn-items-icon tips" data-tip="' . esc_attr__( 'Has serial numbers items.', 'wc-serial-numbers' ) . '"></span>';
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
		$actions['update_serial_numbers'] = __( 'Update serial numbers', 'wc-serial-numbers' );

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
			echo '<p>' . esc_html__( 'Order does not contains any serial numbers items.', 'wc-serial-numbers' ) . '</p>';

			return;
		}

		$keys = Helper::get_ordered_keys( $order->get_id() );
		if ( empty( $keys ) ) {
			echo '<p>' . esc_html__( 'No serial numbers found.', 'wc-serial-numbers' ) . '</p>';

			return;
		}
		$table_columns = [
			'product_name'   => esc_html__( 'Product', 'wc-serial-numbers' ),
			'serial_numbers' => esc_html__( 'Serial Numbers', 'wc-serial-numbers' ),
		];
		$columns       = apply_filters( 'wc_serial_numbers_order_table_columns', $table_columns, $post->ID, $keys );
		?>
		<table class="wp-list-table widefat fixed striped wcsn-orders-table">
			<thead>
			<tr>
				<?php foreach ( $columns as $column_key => $column ) : ?>
					<th class="product-<?php sanitize_html_class( $column_key ); ?>">
						<?php echo esc_html( $column ); ?>
					</th>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $keys as $key ) : ?>
				<tr>
					<td class="product-name">
						<a href="<?php echo esc_html( get_the_permalink( $key->product_id ) ); ?>"><?php echo esc_html( $key->get_product_title() ); ?></a>
					</td>
					<td class="product-serial-numbers">
						<?php echo Helper::display_key_props( $key ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
}
