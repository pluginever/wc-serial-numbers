<?php

$row_action = empty($_REQUEST['row_action']) ? '' : $_REQUEST['row_action'];

$is_product_tab = !empty(get_query_var('is_product_tab')) ? get_query_var('is_product_tab') : '';

if (!$row_action) {

	include WPWSN_INCLUDES . '/admin/class-serial-list-table.php';

	$serial_list = new Pluginever\WCSerialNumbers\Admin\Serial_List_Table($is_product_tab);

	$serial_list->prepare_items();

	?>

	<div class="wrap wsn-container">
		<?php if (!$is_product_tab) { ?>

			<h1 class="wp-heading-inline"><?php _e('Serial Numbers', 'wc-serial-numbers') ?></h1>

			<a href="<?php echo WPWSN_ADD_SERIAL_PAGE ?>" class="page-title-action"><?php _e('Add new serial number', 'wc-serial-numbers') ?></a>

			<a href="<?php echo WPWSN_SETTINGS_PAGE ?>" class="page-title-action"><?php _e('Settings', 'wc-serial-numbers') ?></a>

		<?php } ?>

		<div class="wsn-body">
			<?php
			if (!$is_product_tab) {
				echo '<form action="" method="GET">';
				echo $serial_list->search_box(__('Search', 'wc-serial-numbers'), 'wsn_serial_page');
				echo '<input type="hidden" name="page" value="' . esc_attr($_REQUEST['page']) . '"/></form>'; // form end
			}

			echo '<form id="wsn-serial-numbers-table" action="' . admin_url('admin-post.php') . '" method="post">
			  	 <input type="hidden" name="wsn-serial-numbers-table-action">'
				. wp_nonce_field('wsn-serial-numbers-table', 'wsn-serial-numbers-table-nonce');

			$serial_list->display();

			echo '</form>';

			?>
		</div>

	</div>

<?php } elseif ($row_action == 'edit') {

	include WPWSN_TEMPLATES_DIR . '/add-serial-number-page.php';

} elseif ($row_action == 'delete') {

	if(current_user_can('manage_options')) {
		wp_delete_post($_REQUEST['serial_number']);
	}

	wp_redirect(admin_url('admin.php?page=serial-numbers'));

} ?>
