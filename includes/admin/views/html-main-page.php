<?php

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

$action = isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list'; //phpcs:ignore
if ( in_array( $action, [ 'add', 'edit' ], true ) ) {
	require_once dirname( __DIR__ ) . '/views/html-add-edit-serial-keys.php';
} else {

	require_once dirname( __DIR__ ) . '/tables/class-wcsn-admin-list-table-keys.php';

	$table = new WCSN_Admin_List_Table_Keys();
	$table->prepare_items();
	$do_action = $table->current_action();

	?>

	<div class="wrap">
		<h1 class="wp-heading-inline" rel="ever_modal:open">
			<?php esc_html_e( 'Serial Numbers', 'wc-serial-numbers' ); ?>
		</h1>
		<a id="add-serial-number" rel="ever_modal:open" href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin-ajax.php?action=serial_numbers_get_admin_template&template=edit_serial_key', 'wcsn_get_template' ) ) ); ?>" class="add-serial-title page-title-action">
			<?php esc_html_e( 'Add New', 'wc-serial-numbers' ); ?>
		</a>
		<hr class="wp-header-end">

		<form id="wc-serial-numbers-list" method="get">
			<?php
			$table->search_box( __( 'Search', 'wc-serial-numbers' ), 'search' );
			$table->views();
			$table->display();
			?>
			<input type="hidden" name="page" value="wc-serial-numbers">
		</form>
	</div>

	<?php
}
