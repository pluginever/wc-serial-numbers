<?php

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

include_once __DIR__. '/../class-admin-serial-keys-table.php';

$table = new Admin_Serial_Keys_Table();
$table->prepare_items();
$do_action = $table->current_action();

?>

	<div class="wrap">
		<h1 class="wp-heading-inline">
			<?php _e( 'Serial Numbers', 'wc-serial-numbers' ); ?>
		</h1>
		<a href="<?php echo admin_url( 'admin.php?page=wc-serial-numbers&action=add' ) ?>"
		   class="add-serial-title page-title-action">
			<?php _e( 'Add New', 'wc-serial-numbers' ) ?>
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
