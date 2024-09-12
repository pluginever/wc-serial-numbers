<?php
/**
 * List Generators.
 *
 * @since 1.2.1
 * @package WooCommerceSerialNumbersPro\Admin\Views
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$list_table = new \WooCommerceSerialNumbers\Admin\ListTables\GeneratorsTable();
$action     = $list_table->current_action();
$list_table->process_bulk_actions( $action );
?>

<div class="wrap pev-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Generators', 'wc-serial-numbers-pro', 'wc-serial-numbers' ); ?></h1>
	<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wc-serial-numbers-generators&add' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Add New', 'wc-serial-numbers-pro', 'wc-serial-numbers' ); ?>
	</a>

	<form id="wcsn-generators-table" method="get">
		<?php
		wp_verify_nonce( '_nonce' );
		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		$list_table->prepare_items();
		$list_table->views();
		$list_table->display();
		?>
		<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>">
		<input type="hidden" name="page" value="wc-serial-numbers-generators">
	</form>
</div>
