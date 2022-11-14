<?php
/**
 * Admin View: List Serial Numbers
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
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Serial Numbers', 'wc-serial-numbers' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-serial-numbers&action=add' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'wc-serial-numbers' ); ?></a>
	<hr class="wp-header-end">
</div>
