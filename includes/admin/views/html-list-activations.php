<?php
/**
 * Admin View: List Activations
 *
 * @since 1.4.0
 * @package WooCommerceSerialNumbers
 */

defined( 'ABSPATH' ) || exit();

if ( ! current_user_can( \WooCommerceSerialNumbers\Admin\Helper::get_manager_role() ) ) {
	return;
}

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Activations', 'wc-serial-numbers' ); ?></h1>
	<hr class="wp-header-end">
</div>
