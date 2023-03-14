<?php
/**
 * List keys.
 *
 * @since 1.0.0
 * @package WooCommerceSerialNumbers\Admin\Views
 */

defined( 'ABSPATH' ) || exit;

$list_table = new WooCommerceSerialNumbers\Admin\ListTables\KeysTable();
$doaction   = $list_table->current_action();
$list_table->process_bulk_actions( $doaction );
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Serial Keys', 'wc-serial-numbers' ); ?>
	</h1>
	<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wc-serial-numbers&create' ) ); ?>" class="add-serial-title page-title-action">
		<?php esc_html_e( 'Add New', 'wc-serial-numbers' ); ?>
	</a>

	<hr class="wp-header-end">

	<form id="wcsn-keys-table" method="get">
		<?php
		$list_table->prepare_items();
		$list_table->views();
		$list_table->search_box( __( 'Search key', 'woocommerce' ), 'key' );
		$list_table->display();
		?>
		<input type="hidden" name="page" value="wc-serial-numbers">
	</form>
</div>

