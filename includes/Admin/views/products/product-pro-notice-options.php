<?php
/**
 * Product key options for pro version notice.
 *
 * @since   3.0.0
 * @package WooCommerceSerialNumbers\Admin\views
 * @var $product \WC_Product Product object.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! WCSN()->is_premium_active() ) {
	echo wp_kses_post(
		sprintf(
			'<p class="wc-serial-numbers-upgrade-box">%s <a href="%s" target="_blank" class="button">%s</a></p>',
			__( 'Want to sell keys for variable products?', 'wc-serial-numbers' ),
			'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro',
			__( 'Upgrade to Pro', 'wc-serial-numbers' ),
		)
	);
}
