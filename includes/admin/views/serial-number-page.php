<?php
defined( 'ABSPATH' ) || exit();
?>
<div class="wrap wcsn-wrapper">
	<?php 
		if ( isset( $_GET['serial_numbers_action'] ) && $_GET['serial_numbers_action'] == 'add_serial_number' ) {
			wcsn_get_views( 'edit-serial-number.php' );
		} elseif ( isset( $_GET['serial_numbers_action'] ) && $_GET['serial_numbers_action'] == 'edit_serial_number' ) {
			wcsn_get_views( 'edit-serial-number.php' );
		} else {
			require_once WCSN_ADMIN_ABSPATH . '/tables/class-serial-numbers-list-table.php';
			$list_table = new WCSN_Serial_Numbers_List_Table();
			$list_table->prepare_items();
			$base_url = admin_url( 'admin.php?page=wc-serial-numbers' );
			?>

			<h1 class="wp-heading-inline"><?php _e( 'Serial Numbers', 'wc-serial-numbers' ); ?></h1>
			<a href="<?php echo esc_url( add_query_arg( array( 'serial_numbers_action' => 'add_serial_number' ), $base_url ) ); ?>" class="page-title-action">
				<?php _e( 'Add New', 'wc-serial-numbers' ); ?>
			</a>
			<?php do_action( 'edit_serial_number_page_top' ); ?>
			<form method="get" action="<?php echo esc_url( $base_url ); ?>">
				<div class="wcsn-list-table">
					<?php $list_table->search_box( __( 'Search', 'wc-serial-numbers' ), 'serial-number' ); ?>
					<input type="hidden" name="page" value="wc-serial-numbers"/>
					<?php $list_table->views() ?>
					<?php $list_table->display() ?>
				</div>
			</form>
			<?php
			do_action( 'edit_serial_number_page_bottom' );
		}
	?>
</div>

<?php 