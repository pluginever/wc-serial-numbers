<?php
/**
 * Product software options.
 *
 * @since   3.0.0
 * @package WooCommerceSerialNumbers\Admin\views
 * @var $product \WC_Product Product object.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

echo '<div class="options_group">';

woocommerce_wp_text_input(
	array(
		'id'          => '_software_version',
		'label'       => __( 'Software version', 'wc-serial-numbers' ),
		'description' => __( 'Version number for the software. Ignore if it\'s not a software.', 'wc-serial-numbers' ),
		'placeholder' => __( 'e.g. 1.0', 'wc-serial-numbers' ),
		'desc_tip'    => true,
	)
);

/**
 * Action hook to add more software options.
 *
 * @param WC_Product $product Product object.
 *
 * @since 3.0.0
 */
do_action( 'wc_serial_numbers_product_software_options', $product );

echo '</div>';
