<?php include WPWSN_INCLUDES . '/admin/class-serial-list-table.php' ?>

<?php
$serial_list = new Pluginever\WCSerialNumbers\Admin\Serial_List_Table();
$serial_list->prepare_items();
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e('Serial Numbers', 'wc-serial-numbers') ?></h1>
	<a href="<?php echo admin_url('admin.php?page=generate-serial-numbers') ?>" class="page-title-action"><?php _e('Generate new serial number', 'wc-serial-numbers') ?></a>
	<?php
	$serial_list->search_box('Search', 'search_id');
	$serial_list->display();
	?>
</div>

