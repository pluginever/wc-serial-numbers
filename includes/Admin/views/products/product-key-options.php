<?php
/**
 * Product key options.
 *
 * @since   3.0.0
 * @package WooCommerceSerialNumbers\Admin\views
 * @var $product \WC_Product Product object.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

echo '<div class="options_group">';
// Delivery quantity and software version fields.
$delivery_quantity = (int) get_post_meta( $product->get_id(), '_delivery_quantity', true );
woocommerce_wp_text_input(
	apply_filters(
		'wc_serial_numbers_delivery_quantity_field_args',
		array(
			'id'                => '_delivery_quantity',
			'label'             => __( 'Delivery quantity', 'wc-serial-numbers' ),
			'description'       => __( 'Number of key(s) will be delivered per item. Available in PRO.', 'wc-serial-numbers' ),
			'value'             => empty( $delivery_quantity ) ? 1 : $delivery_quantity,
			'type'              => 'number',
			'desc_tip'          => true,
			'custom_attributes' => array(
				'disabled' => 'disabled',
			),
		)
	)
);

/**
 * Action hook to add more product key options.
 *
 * @since 3.0.0
 */
do_action( 'wc_serial_numbers_product_key_options', $product );

echo '</div>';
