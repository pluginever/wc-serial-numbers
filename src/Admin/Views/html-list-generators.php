<?php
/**
 * view file for the list of generators.
 *
 * @since 1.2.1
 * @package WooCommerceSerialNumbers\Admin\Views
 */

defined( 'ABSPATH' ) || exit;

$list_table = new \WooCommerceSerialNumbers\Admin\ListTables\GeneratorsTable();
$action     = $list_table->current_action();
$list_table->process_bulk_actions( $action );
?>

<h2 class="wp-heading-inline">
	<?php echo esc_html__( 'Generators', 'wc-serial-numbers-pro' ); ?>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-serial-numbers-tools&tab=generators&add' ) ); ?>" class="page-title-action">
		<?php echo esc_html__( 'Add New', 'wc-serial-numbers-pro' ); ?>
	</a>
</h2>
<form id="wcsn-stock-table" method="get">
	<?php
	$list_table->prepare_items();
	$list_table->display();
	?>
	<input type="hidden" name="tab" value="generators">
	<input type="hidden" name="page" value="wc-serial-numbers-tools">
</form>

