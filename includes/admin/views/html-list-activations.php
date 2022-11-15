<?php
/**
 * Admin View: List Activations
 *
 * @since 1.4.0
 * @package WooCommerceSerialNumbers
 */

defined( 'ABSPATH' ) || exit();

if ( ! current_user_can( \WooCommerceSerialNumbers\Admin\Admin::get_manager_role() ) ) {
	return;
}

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Activations', 'wc-serial-numbers' ); ?></h1>
	<hr class="wp-header-end">

	<form id="serial-numbers-keys-table" method="get">
		<?php
		$list_table->prepare_items();
		$list_table->views();
		$list_table->search_box( __( 'Search key', 'wc-serial-numbers' ), 'activation' );
		$list_table->display();
		?>
		<input type="hidden" name="page" value="wc-serial-numbers-activations">
	</form>
</div>
