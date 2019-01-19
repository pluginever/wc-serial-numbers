<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$row_action = '';

if ( ! empty( $_REQUEST['row_action'] ) && in_array( $_REQUEST['row_action'], array( 'edit', 'delete' ) ) ) {
	$row_action = sanitize_key( $_REQUEST['row_action'] );
}

$is_product_tab   = ! empty( get_query_var( 'is_product_tab' ) ) ? get_query_var( 'is_product_tab' ) : '';
$serial_number_id = ! empty( $_REQUEST['serial_number'] ) ? intval( $_REQUEST['serial_number'] ) : '';

if ( 'edit' === $row_action && ! empty( $serial_number_id ) ) {
	include WPWSN_TEMPLATES_DIR . '/add-serial-number-page.php';

} elseif ( 'delete' === $row_action && ! empty( $serial_number_id ) ) {

	$post = get_post( $serial_number_id );

	if ( current_user_can( 'manage_options' ) && $post && $post->post_type === 'wsn_serial_number' ) {

		wp_delete_post( $serial_number_id, true );
		do_action( 'wsn_update_notification_on_order_delete', $serial_number_id );
	}

	wp_redirect( WPWSN_SERIAL_INDEX_PAGE );

} else {

	include WPWSN_INCLUDES . '/admin/class-serial-list-table.php';

	$serial_list = new Pluginever\WCSerialNumbers\Admin\Serial_List_Table( $is_product_tab );

	$serial_list->prepare_items();

	?>

	<div class="wrap wsn-container">

		<?php if ( ! $is_product_tab ) { ?>

			<h1 class="wp-heading-inline"><?php _e( 'Serial Numbers', 'wc-serial-numbers' ) ?></h1>

			<a href="<?php echo WPWSN_ADD_SERIAL_PAGE ?>"
			   class="page-title-action"><?php _e( 'Add new serial number', 'wc-serial-numbers' ) ?></a>

			<a href="<?php echo WPWSN_SETTINGS_PAGE ?>"
			   class="page-title-action"><?php _e( 'Settings', 'wc-serial-numbers' ) ?></a>

		<?php } ?>

		<div class="wsn-body">
			<?php

			if ( ! $is_product_tab ) {

				echo '<form action="" method="GET">';
				$serial_list->search_box( __( 'Search', 'wc-serial-numbers' ), 'wsn_serial_page' );
				echo '<input type="hidden" name="page" value="' . sanitize_key( $_REQUEST['page'] ) . '"/></form>'; // form end

				echo '<form id="wsn-serial-numbers-table" action="' . admin_url( 'admin-post.php' ) . '" method="post">
			  	 <input type="hidden" name="wsn-serial-numbers-table-action">'
				     . wp_nonce_field( 'wsn-serial-numbers-table', 'wsn-serial-numbers-table-nonce' );
			}

			$serial_list->display();

			if ( ! $is_product_tab ) {
				echo '</form>';
			}

			?>
		</div>

	</div>
	<?php
}

