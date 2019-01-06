<?php

$row_action = empty( $_REQUEST['row_action'] ) ? '' : $_REQUEST['action'];

$is_product_tab = !empty(get_query_var( 'is_product_tab' )) ? get_query_var( 'is_product_tab' ) : '';

if ( ! $row_action ) {

	include WPWSN_INCLUDES . '/admin/class-serial-list-table.php';

	$serial_list = new Pluginever\WCSerialNumbers\Admin\Serial_List_Table($is_product_tab);

	$serial_list->prepare_items();

	?>

	<div class="wrap wsn-container">
		<?php if ( ! $is_product_tab ) { ?>
			<h1 class="wp-heading-inline"><?php _e( 'Serial Numbers', 'wc-serial-numbers' ) ?></h1>
			<a href="<?php echo admin_url( 'admin.php?page=add-serial-number' ) ?>" class="page-title-action"><?php _e( 'Add new serial number', 'wc-serial-numbers' ) ?></a>
			<a href="<?php echo admin_url( 'admin.php?page=wc_serial_numbers-settings' ) ?>" class="page-title-action"><?php _e( 'Settings', 'wc-serial-numbers' ) ?></a>
		<?php } ?>
		<div class="wsn-body">
			<?php
			if ( ! $is_product_tab ) {
				$serial_list->search_box( 'Search', 'search_id' );
			}
			echo '<form id="wsn-serial-numbers-table" action="' . admin_url( 'admin-post.php' ) . '" method="post">
			  <input type="hidden" name="wsn-serial-numbers-table-action">'
			     . wp_nonce_field( 'wsn-serial-numbers-table', 'wsn-serial-numbers-table-nonce' );

			$serial_list->display();

			echo '</form>';

			?>
		</div>
	</div>

<?php } elseif ( $row_action == 'edit' ) {

	include WPWSN_TEMPLATES_DIR . '/add-serial-number.php';

} elseif ( $row_action == 'delete' ) {

	wp_delete_post( $_REQUEST['serial_number'] );

	wp_redirect( admin_url( 'admin.php?page=serial-numbers' ) );

} ?>
