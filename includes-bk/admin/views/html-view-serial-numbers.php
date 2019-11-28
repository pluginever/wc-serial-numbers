<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
	wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'nonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
	exit;

}
require_once WC_SERIAL_NUMBERS_INCLUDES . '/admin/tables/class-serial-numbers-list-table.php';
$serial_number_id = ! empty( $_REQUEST['serial_id'] ) ? intval( $_REQUEST['serial_id'] ) : null;
$wp_list_table    = new WCSN_Serial_Numbers_List_Table();
$wp_list_table->prepare_items();
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php _e( 'Serial Numbers', 'wc-serial-numbers' ); ?>
	</h1>

	<a href="<?php echo admin_url( 'admin.php?page=wc-serial-numbers&action_type=add_serial_number' ) ?>" class="add-serial-title page-title-action"><?php _e( 'Add serial number', 'wc-serial-numbers' ) ?></a>
	<?php do_action( 'wcsn_serial_numbers_list_table_after_title' ); ?>
	<hr class="wp-header-end">
	<form id="wc-serial-numbers-list" method="get">
		<input type="hidden" name="page" value="wc-serial-numbers">
		<?php wp_nonce_field( 'wc_serial_numbers_list', 'nonce' ); ?>
		<?php
		$wp_list_table->search_box( __( 'Search', 'wc-serial-numbers' ), 's' );
		$wp_list_table->display();
		?>
	</form>
</div>
