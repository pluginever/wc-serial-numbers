<?php
/**
 * The template for displaying a serial numbers summary to customers.
 * It will display in three places:
 * - After checkout,
 * - In the order confirmation email, and
 * - When customer reviews order in My Account > Orders.
 *
 * This template can be overridden by copying it to yourtheme/wc-serial-numbers/order/serial-numbers-display.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @since   #.#.#
 */

use PluginEver\WooCommerceSerialNumbers\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<section class="woocommerce-order-details serial-numbers">
	<h2 class="woocommerce-order-details__title">
		<?php echo esc_html( apply_filters( 'wc_serial_numbers_order_table_heading', esc_html__( 'Serial Numbers', 'wc-serial-numbers' ) ) ); ?>
	</h2>
	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
		<thead>
		<tr>
			<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
			<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Serial Number', 'woocommerce' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $keys as $key ) : ?>
			<tr>
				<td class="woocommerce-table__product-name product-name">
					<a href="<?php echo esc_attr( get_permalink( $key->product_id ) ); ?>"><?php echo esc_html( get_the_title( $key->product_id ) ); ?></a>
				</td>
				<td>
					<?php
					$props = array(
						array(
							'display_index' => 'key',
							'display_key'   => esc_html__( 'Key', 'wc-serial-numbers' ),
							'display_value' => esc_html( $key->get_key() ),
						),
						array(
							'display_index' => 'date_expire',
							'display_key'   => esc_html__( 'Expire Date', 'wc-serial-numbers' ),
							'display_value' => empty( $key->get_validity() ) ? esc_html__( 'Lifetime', 'wc-serial-numbers' ) : esc_html( $key->get_validity() ),
						),
					);
					if ( Helper::is_software_support_enabled() ) {
						$props[] = array(
							'display_index' => 'activation_limit',
							'display_key'   => esc_html__( 'Activation Limit', 'wc-serial-numbers' ),
							'display_value' => empty( $key->get_activation_limit() ) ? esc_html__( 'Unlimited', 'wc-serial-numbers' ) : esc_html( $key->get_activation_limit() ),
						);
					}
					echo wp_kses_post( Helper::display_key_props( apply_filters( 'wc_serial_numbers_keys_props', $props, $key, $order_id ) ) );
					?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</section>
