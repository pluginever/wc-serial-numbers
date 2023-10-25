<?php
/**
 * Low stock email notification template.
 *
 * @since 1.2.0
 * @package WooCommerceSerialNumbers
 * @var array $low_stock_products List of low stock products.
 */

?>

<?php defined( 'ABSPATH' ) || exit; ?>

<p><?php printf( esc_html__( 'Hi There,', 'wc-serial-numbers' ) ); ?></p>
<p><?php printf( esc_html__( 'There are few  products stock running low, please add serial numbers for these products', 'wc-serial-numbers' ) ); ?></p>
<ul>
	<?php
	foreach ( $low_stock_products as $product_id => $stock ) {
		$product_id = absint( $product_id );
		if ( ! $product_id ) {
			continue;
		}
		$product = wc_get_product( $product_id );

		printf( "<li><a href='%s' target='_blank'>%s</a> - Stock %s</li>", esc_url( get_edit_post_link( $product->get_id() ) ), esc_html( $product->get_formatted_name() ), esc_html( $stock ) );
	}
	?>
</ul>

<br>
<br>
<p>
	<?php
	echo wp_kses_post(
		sprintf(
			// translators: %s: plugin url.
			__( 'The email is sent by <a href="%s" target="_blank">Serial Numbers for WooCommerce</a>', 'wc-serial-numbers' ),
			'https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=serialnumberemail&utm_medium=email&utm_campaign=lowstocknotification'
		)
	);
	?>
</p>
