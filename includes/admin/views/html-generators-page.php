<?php

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

include_once __DIR__. '/../class-admin-generators-table.php';

$table = new Admin_Generators_Table();
$table->prepare_items();
$do_action = $table->current_action();

?>

	<div class="wrap">
		<h1 class="wp-heading-inline">
			<?php _e( 'Generators', 'wc-serial-numbers' ); ?>
		</h1>
		<a href="<?php echo admin_url( 'admin.php?page=wc-serial-numbers-generators&action=add' ) ?>"
		   class="add-serial-title page-title-action">
			<?php _e( 'Add New Generators', 'wc-serial-numbers' ) ?>
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
