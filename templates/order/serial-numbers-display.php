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
		<?php echo esc_html( $title ); ?>
	</h2>

	<?php if ( empty( $keys ) ) : ?>
		<span class="woocommerce-order-details__pending_message">
				<?php echo esc_html( $pending_keys_message ); ?>
			</span>
	<?php endif; ?>

	<table class="woocommerce-table woocommerce-table--order-details shop_table">
		<thead>
		<tr>
			<?php foreach ( $columns as $column_key => $column ) : ?>
				<th class="woocommerce-table__product-table product-<?php sanitize_html_class( $column_key ); ?>">
					<?php echo esc_html( $column ); ?>
				</th>
			<?php endforeach; ?>
		</tr>
		</thead>
		<tbody>
		<tbody>
		<?php foreach ( $keys as $key ) : ?>
			<tr>
				<td class="woocommerce-table__product-table product-<?php sanitize_html_class( $column_key ); ?>">
					<a href="<?php echo esc_html( get_the_permalink( $key->product_id ) ); ?>"><?php echo esc_html( $key->get_product_title() ); ?></a>
				</td>
				<td class="woocommerce-table__product-table product-<?php sanitize_html_class( $column_key ); ?>">
					<?php echo Helper::display_key_props( $key ); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

</section>
