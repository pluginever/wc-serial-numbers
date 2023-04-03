<?php
/**
 * List all stocks
 *
 * @package WooCommerceSerialNumbers/Admin/Views
 *
 */

defined( 'ABSPATH' ) || exit;
$list_table = new WooCommerceSerialNumbers\Admin\ListTables\StockTable();
?>

<form id="wcsn-stock-table" method="get">
	<?php
	$list_table->prepare_items();
	//$list_table->views();
	$list_table->display();
	?>
	<input type="hidden" name="tab" value="stock">
	<input type="hidden" name="page" value="wc-serial-numbers-reports">
</form>
<p class="description">
	<strong><?php esc_html_e( 'Note:', 'wc-serial-numbers' ); ?></strong>
	<?php esc_html_e( 'The following report displays a comprehensive list of products whose source for serial keys has been set to "Manually Added.".', 'wc-serial-numbers' ); ?>
</p>
