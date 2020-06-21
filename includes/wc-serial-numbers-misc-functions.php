<?php
defined( 'ABSPATH' ) || exit();
/**
 * Get product title.
 *
 * @param $product
 *
 * @return string
 * @since 1.2.0
 */
function wc_serial_numbers_get_product_title( $product ) {
	if ( ! empty( $product ) ) {
		$product = wc_get_product( $product );
	}
	if ( $product && ! empty( $product->get_id() ) ) {
		return sprintf(
			'(#%1$s) %2$s',
			$product->get_id(),
			html_entity_decode( $product->get_formatted_name() )
		);
	}

	return '';
}
