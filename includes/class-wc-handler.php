<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCSN_WC_Handler {

	/**
	 * WCSN_WC_Handler constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_check_cart_items', array( $this, 'validate_cart_content' ) );
	}

	/**
	 * Validate cart check if there are enough serial numbers
	 *
	 * since 1.0.0
	 *
	 * @return bool
	 */
	function validate_cart_content() {
		$car_products = WC()->cart->get_cart_contents();
		foreach ( $car_products as $id => $cart_product ) {
			$product    = $cart_product['data'];
			$product_id = $cart_product['product_id'];
			$quantity   = $cart_product['quantity'];

			$is_enabled     = get_post_meta( $product_id, '_is_serial_number', true ); //Check if the serial number enabled for this product.
			$assign_type    = get_post_meta( $product_id, '_serial_key_source', true );
			$allow_checkout = wcsn_get_settings( 'wsn_allow_checkout', 'no', 'wsn_general_settings' );

			if ( ( $is_enabled == 'yes' ) ) {

				if ( ( 'auto_generated' == $assign_type ) || ( 'on' == $allow_checkout ) ) {
					return true;
				}

				$total_number = wcsn_get_serial_numbers( array( 'product_id' => $product_id, 'status' => 'new' ), true );

				if ( $total_number < $quantity ) {

					wc_add_notice( sprintf( __( 'Sorry, There is not enough Serial Number available for %s, Please remove this item or lower the quantity,
												For now we have %d Serial Number for this product. <br>', 'wc-serial-numbers' ), $product->get_title(), $total_number ), 'error' );

					return false;
				}

			}
		}
	}
}

new WCSN_WC_Handler();

