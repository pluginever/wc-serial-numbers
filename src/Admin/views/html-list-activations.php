<?php
/**
 * List Activations.
 *
 * @since 1.0.0
 * @package WooCommerceSerialNumbers\Admin\Views
 */

defined( 'ABSPATH' ) || exit;

$list_table = new WooCommerceSerialNumbers\Admin\ListTables\ActivationsTable();
$doaction   = $list_table->current_action();
$list_table->process_bulk_actions( $doaction );
?>

<div class="wrap pev-wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Activations', 'wc-serial-numbers' ); ?>
	</h1>

	<hr class="wp-header-end">
	<form id="wcsn-activations-table" method="get">
		<?php
		$list_table->prepare_items();
		$list_table->views();
		$list_table->search_box( __( 'Search activation', 'wc-serial-numbers' ), 'activation' );
		$list_table->display();
		?>
		<input type="hidden" name="page" value="wc-serial-numbers-activations">
	</form>
</div>
<?php
