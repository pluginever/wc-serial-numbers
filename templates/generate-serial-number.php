<?php

$row_action = empty($_REQUEST['row_action']) ? '' : $_REQUEST['row_action'];

$is_product_tab = !empty(get_query_var('is_product_tab')) ? get_query_var('is_product_tab') : '';

if (!$row_action) {

	include WPWSNP_INCLUDES . '/admin/generate-serial-table.php';

	$rule_list = new Pluginever\WCSerialNumberPro\Admin\Generate_Serial_Table($is_product_tab);

	$rule_list->prepare_items();

	?>

	<div class="wrap wsn-container">
		<?php if (!$is_product_tab) { ?>

			<h1 class="wp-heading-inline"><?php _e('Generator Rules', 'wc-serial-numbers') ?></h1>

			<a href="<?php echo WPWSN_ADD_GENERATE_RULE ?>" class="page-title-action"><?php _e('Add new generator rule', 'wc-serial-numbers') ?></a>


		<?php } ?>

		<div class="wsn-body">
			<?php
			if (!$is_product_tab) {
				echo '<form action="" method="GET">';
				echo $rule_list->search_box(__('Search'), 'wsn_generate_serial');
				echo '<input type="hidden" name="page" value="' . esc_attr($_REQUEST['page']) . '"/></form>'; // form end
			}

			echo '<form id="wsn-serial-numbers-table" action="' . admin_url('admin-post.php') . '" method="post">
			  	 <input type="hidden" name="wsn-serial-numbers-table-action">'
				. wp_nonce_field('wsn-serial-numbers-table', 'wsn-serial-numbers-table-nonce');

			$rule_list->display();

			echo '</form>';

			?>
		</div>

	</div>

<?php } elseif ($row_action == 'edit') {

	include WPWSNP_TEMPLATES_DIR . '/add-generator-rule.php';

} elseif ($row_action == 'delete') {

	wp_delete_post($_REQUEST['generator_rule']);

	wp_redirect(WPWSN_GENERATE_SERIAL_PAGE);

} ?>

