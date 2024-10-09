<?php
/**
 * Product key source options.
 *
 * @since   3.0.0
 * @package WooCommerceSerialNumbers\Admin\views
 * @var $product \WC_Product Product object.
 */

use WooCommerceSerialNumbers\Models\Generator;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

echo '<div class="options_group">';
$source  = get_post_meta( $product->get_id(), '_serial_key_source', true );
$sources = wcsn_get_key_sources();

// key source select field.
woocommerce_wp_select(
	array(
		'id'            => '_serial_key_source',
		'label'         => __( 'Key Source', 'wc-serial-numbers' ),
		'value'         => $source,
		'options'       => $sources,
		'desc_tip'      => true,
		'description'   => __( 'Automatically generate keys or use preset keys.', 'wc-serial-numbers' ),
		'wrapper_class' => '_serial_key_source',
		'class'         => 'wc-enhanced-select',
		'style'         => 'width: 50%;',
	)
);

// Selling & Generate keys using a generator.
$generators = Generator::results( array( 'status' => 'active' ) );

// Generate options.
$options = array(
	'' => __( 'Default', 'wc-serial-numbers' ),
);
foreach ( $generators as $generator ) {
	$options[ $generator->id ] = $generator->name;
}
woocommerce_wp_select(
	array(
		'id'            => '_generator_id',
		'label'         => __( 'Key Generator', 'wc-serial-numbers' ),
		'description'   => __( 'Select a specific key generator or leave empty to use default settings.', 'wc-serial-numbers' ),
		'options'       => $options,
		'desc_tip'      => true,
		'class'         => 'wc-enhanced-select',
		'wrapper_class' => 'wcsn_show_if_key_source__automatic',
		'style'         => 'width: 50%;',
	)
);

// Generate sequential keys.
woocommerce_wp_checkbox(
	array(
		'id'            => '_wcsn_is_sequential',
		'label'         => __( 'Sequential Keys', 'wc-serial-numbers' ),
		'description'   => __( 'Generate keys in sequential order.', 'wc-serial-numbers' ),
		'value'         => get_post_meta( $product->get_id(), '_wcsn_is_sequential', true ),
		'wrapper_class' => 'wcsn_show_if_key_source__automatic',
	)
);

$stocks = wcsn_get_stocks_count();
$stock  = isset( $stocks[ $product->get_id() ] ) ? $stocks[ $product->get_id() ] : 0;
echo wp_kses_post(
	sprintf(
		'<p class="_wcsn-key-source-stock-field form-field options_group wcsn_show_if_key_source__preset"><label>%s</label><span class="description">%d %s</span></p>',
		__( 'Preset Stock', 'wc-serial-numbers' ),
		$stock,
		_n( 'key available.', 'keys available.', $stock, 'wc-serial-numbers' )
	)
);

/**
 * Action hook to add more product key options for key source.
 *
 * @param WC_Product $product Product object.
 *
 * @since 3.0.0
 */
do_action( 'wc_serial_numbers_product_key_source_options', $product );


echo '</div>';
