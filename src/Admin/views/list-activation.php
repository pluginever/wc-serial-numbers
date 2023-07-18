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

	<div class="pev-admin-page__header">
		<h2 class="wp-heading-inline">
			<?php esc_html_e( 'Activations', 'wc-serial-numbers' ); ?>
		</h2>
	</div>
	<form id="wcsn-activations-table" method="get">
		<?php
		$list_table->prepare_items();
		$list_table->views();
		$list_table->search_box( __( 'Search key', 'wc-serial-numbers' ), 'activation' );
		$list_table->display();
		?>
		<input type="hidden" name="page" value="wc-serial-numbers-activations">
	</form>
<?php
