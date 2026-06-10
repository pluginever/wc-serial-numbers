<?php
/**
 * List all stocks
 *
 * @package PluginEver\SerialNumbers/Admin/Views
 */

defined( 'ABSPATH' ) || exit;
$list_table = new PluginEver\SerialNumbers\Stocks\ListTable();
?>

<form id="wcsn-stock-table" method="get">
	<?php
	$list_table->prepare_items();
	$list_table->views();
	$list_table->search_box( __( 'Search', 'wc-serial-numbers' ), 'key' );
	$list_table->display();
	?>
	<input type="hidden" name="tab" value="stock">
	<input type="hidden" name="page" value="wc-serial-numbers-reports">
</form>
