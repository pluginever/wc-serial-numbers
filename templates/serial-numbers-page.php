<?php if(!isset($_REQUEST['action']) && $_REQUEST['action'] != 'edit'){ ?>
<?php include WPWSN_INCLUDES . '/admin/class-serial-list-table.php' ?>

<?php
$serial_list = new Pluginever\WCSerialNumbers\Admin\Serial_List_Table();
$serial_list->prepare_items();
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e('Serial Numbers', 'wc-serial-numbers') ?></h1>
	<a href="<?php echo admin_url('admin.php?page=add-serial-number') ?>" class="page-title-action"><?php _e('Add new serial number', 'wc-serial-numbers') ?></a>
	<?php
	$serial_list->search_box('Search', 'search_id');
	$serial_list->display();
	?>
</div>
<?php }elseif ($_REQUEST['action'] == 'edit'){
	include WPWSN_TEMPLATES_DIR . '/add-serial-number.php';
}elseif ($_REQUEST['action'] == 'delete'){
	wp_delete_post($_REQUEST['serial_number']);
	wp_redirect( admin_url( 'admin.php?page=serial-numbers' ) );
} ?>
