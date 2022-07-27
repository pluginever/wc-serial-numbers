<?php

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

require_once dirname( __DIR__ ) . '/tables/class-wcsn-admin-list-table-generators.php';

$table = new WCSN_Admin_List_Table_Generators();
$table->prepare_items();
$do_action = $table->current_action();

?>

	<div class="wrap">
		<h1 class="wp-heading-inline">
			<?php esc_html_e( 'Serial Generators', 'wc-serial-numbers' ); ?>
		</h1>
		<a href="<?php echo admin_url( 'admin.php?page=wc-serial-numbers-generators&action=add' ); ?>" class="add-serial-title page-title-action">
			<?php esc_html_e( 'Add New', 'wc-serial-numbers' ); ?>
		</a>
		<hr class="wp-header-end">

		<form id="wc-serial-numbers-generators-list" method="get">
			<?php
			$table->search_box( __( 'Search', 'wc-serial-numbers' ), 'search' );
			$table->views();
			$table->display();
			?>
			<input type="hidden" name="page" value="wc-serial-numbers-generators">
		</form>
	</div>

<?php
