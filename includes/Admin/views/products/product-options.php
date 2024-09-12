<?php
/**
 * Product key options.
 *
 * @since   3.0.0
 * @package WooCommerceSerialNumbers\Admin\views
 * @var $product \WC_Product Product object.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>
<div id="wc_serial_numbers_data" class="panel woocommerce_options_panel show_if_simple" style="display: none;">
	<?php
	/**
	 * Action after serial key settings.
	 *
	 * @param WC_Product $product Product object.
	 *
	 * @since 3.0.0
	 */
	do_action( "wc_serial_numbers_{$product->get_type()}_product_options", $product );

	/**
	 * Action after key settings.
	 *
	 * @param WC_Product $product Product object.
	 *
	 * @since 3.0.0
	 */
	do_action( 'wc_serial_numbers_product_options', $product );
	?>
</div>
<?php
