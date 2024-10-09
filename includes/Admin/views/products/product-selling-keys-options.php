<?php
/**
 * Product enable selling keys.
 *
 * @since 3.0.0
 * @package WooCommerceSerialNumbers\Admin\views
 * @var $product \WC_Product Product object.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

echo '<div class="options_group">';
// Enable selling keys.
woocommerce_wp_checkbox(
	array(
		'id'            => '_is_serial_number',
		'label'         => __( 'Sell keys', 'wc-serial-numbers' ),
		'description'   => __( 'Enable this if you are selling keys with this product.', 'wc-serial-numbers' ),
		'value'         => get_post_meta( $product->get_id(), '_is_serial_number', true ),
		'wrapper_class' => '',
		'desc_tip'      => false,
	)
);

/**
* Action hook to add more product key options for selling keys.
*
* @since 3.0.0
*/
do_action( 'wc_serial_numbers_product_enable_selling_keys', $product );

echo '</div>';
