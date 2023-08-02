<?php

namespace WooCommerceSerialNumbers\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Metaboxes.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Metaboxes extends \WooCommerceSerialNumbers\Lib\Singleton {

	/**
	 * Metaboxes constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_metaboxes' ) );
		// add_action( 'woocommerce_after_order_itemmeta', array( $this, 'order_itemmeta' ), 10, 3 );
	}


	/**
	 * Register metaboxes.
	 *
	 * @since 1.2.5
	 */
	public static function register_metaboxes() {
		add_meta_box( 'order-serial-numbers', __( 'Serial Numbers', 'wc-serial-numbers' ), array( __CLASS__, 'order_metabox' ), 'shop_order', 'advanced', 'high' );
	}



	/**
	 *
	 * @param $o_item
	 * @param $product
	 *
	 * @param $o_item_id
	 *
	 * @since 1.1.6
	 *
	 * @return bool|string
	 */
	public function order_itemmeta( $o_item_id, $o_item, $product ) {
		global $post;
		if ( ! is_object( $post ) || ! isset( $post->ID ) ) {
			return false;
		}

		$order = wc_get_order( $post->ID );

		// bail for no order
		if ( ! $order ) {
			return false;
		}

		if ( 'completed' !== $order->get_status( 'edit' ) ) {
			return '';
		}

		// if this is not product then no need to process
		if ( empty( $product ) ) {
			return false;
		}

		$is_serial_product = 'yes' == get_post_meta( $product->get_id(), '_is_serial_number', true );

		if ( ! $is_serial_product ) {
			return false;
		}

		$items = wcsn_get_keys(
			array(
				'order_id'   => $post->ID,
				'product_id' => $product->get_id(),
			)
		);

		if ( empty( $items ) && $order ) {
			echo sprintf( '<div class="wcsn-missing-serial-number">%s</div>', __( 'Order missing serial numbers for this item.', 'wc-serial-numbers' ) );

			return true;
		}

		$url = admin_url( 'admin.php?page=wc-serial-numbers' );
		echo sprintf(
			'<br/><a href="%s">%s&rarr;</a>',
			add_query_arg(
				[
					'order_id'   => $post->ID,
					'product_id' => $product->get_id(),
				],
				$url
			),
			__( 'Serial Numbers', 'wc-serial-numbers' )
		);

		$url = admin_url( 'admin.php?page=wc-serial-numbers' );

		$li = '';

		foreach ( $items as $item ) {
			$li .= sprintf(
				'<li><a href="%s">&rarr;</a>&nbsp;%s</li>',
				add_query_arg(
					[
						'edit' => $item->id,
					],
					$url
				),
				wc_serial_numbers_decrypt_key( $item->serial_key )
			);
		}

		echo sprintf( '<ul>%s</ul>', $li );
	}

	/**
	 * Render order metabox.
	 *
	 * The metabox shows all ordered serial numbers.
	 *
	 * @param $post
	 *
	 * @since 1.2.6
	 *
	 * @return bool
	 */
	public static function order_metabox( $post ) {
		if ( ! is_object( $post ) || ! isset( $post->ID ) ) {
			return false;
		}
		$order = wc_get_order( $post->ID );

		// bail for no order
		if ( ! $order ) {
			return false;
		}

		$order_keys = wcsn_order_has_products( $order );
		if ( empty( $order_keys ) ) {
			echo sprintf( '<p>%s</p>', __( 'No serial keys associated with the order.', 'wc-serial-numbers' ) );

			return false;
		}

		$serial_numbers = wcsn_get_keys(
			array(
				'order_id' => $order->get_id(),
				'limit'    => - 1,
			)
		);

		if ( empty( $serial_numbers ) ) {
			echo sprintf( '<p>%s</p>', apply_filters( 'wc_serial_numbers_pending_notice', __( 'Order waiting for assigning serial keys.', 'wc-serial-numbers' ) ) );

			return false;
		}

		do_action( 'wc_serial_numbers_order_table_top', $order, $serial_numbers );
		$columns = wc_serial_numbers_get_order_table_columns();

		?>
		<table class="widefat striped">
			<tbody>
			<tr>
				<?php
				foreach ( $columns as $key => $label ) {
					echo sprintf( '<th class="td %s" scope="col" style="text-align:left;">%s</th>', sanitize_html_class( $key ), $label );
				}
				?>

				<th>
					<?php _e( 'Actions', 'wc-serial-numbers' ); ?>
				</th>
			</tr>

			<?php foreach ( $serial_numbers as $serial_number ) : ?>
				<tr>
					<?php foreach ( $columns as $key => $column ) : ?>
						<td class="td" style="text-align:left;">
							<?php
							switch ( $key ) {
								case 'product':
									echo sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $serial_number->product_id ) ), get_the_title( $serial_number->product_id ) );
									break;
								case 'serial_key':
									echo esc_html( wp_unslash( $serial_number->get_key() ) );
									break;
								case 'activation_email':
									echo $order->get_billing_email();
									break;
								case 'activation_limit':
									if ( empty( $serial_number->activation_limit ) ) {
										echo __( 'Unlimited', 'wc-serial-numbers' );
									} else {
										echo $serial_number->activation_limit;
									}
									break;
								case 'expire_date':
									if ( empty( $serial_number->validity ) ) {
										echo __( 'Lifetime', 'wc-serial-numbers' );
									} else {
										echo date( 'Y-m-d', strtotime( $serial_number->order_date . ' + ' . $serial_number->validity . ' Day ' ) );
									}
									break;
								default:
									do_action( 'wc_serial_numbers_order_table_cell_content', $key, $serial_number, $order->get_id() );
							}
							?>

						</td>
					<?php endforeach; ?>
					<td>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-serial-numbers&edit=' . $serial_number->id ) ); ?>"><?php _e( 'Edit', 'wc-serial-numbers' ); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php

		do_action( 'wc_serial_numbers_order_table_bottom', $order, $serial_numbers );

		return true;
	}
}
