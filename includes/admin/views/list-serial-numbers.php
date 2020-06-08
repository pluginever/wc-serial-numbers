<?php defined( 'ABSPATH' ) || exit(); ?>
<?php
require_once dirname( __DIR__ ) . '/tables/class-wc-serial-numbers-serial-numbers-table.php';
$list_table = new WC_Serial_Numbers_List_Table();
$doaction   = $list_table->current_action();
$page       = admin_url( 'admin.php?page=wc-serial-numbers' );
if ( $doaction ) {
	if ( isset( $_REQUEST['id'] ) ) {
		$ids      = [ intval( $_REQUEST['id'] ) ];
		$doaction = ( - 1 != $_REQUEST['action'] ) ? $_REQUEST['action'] : $_REQUEST['action2'];
	} elseif ( isset( $_REQUEST['ids'] ) ) {
		$ids = array_map( 'absint', $_REQUEST['ids'] );
	} elseif ( wp_get_referer() ) {
		wp_safe_redirect( wp_get_referer() );
		exit;
	}
	foreach ( $ids as $id ) { // Check the permissions on each.
		switch ( $doaction ) {
			case 'delete':
				wc_serial_numbers_delete_item( $id );
				break;
			case 'activate':
				wc_serial_numbers_insert_item( array(
					'id'         => $id,
					'order_id'   => null,
					'order_date' => null,
				) );
				break;
			case 'deactivate':
				WC_Serial_Numbers_Helper::update_serial_number_status( $id, 'inactive' );
				break;
			case 'export':
				WC_Serial_Numbers_IO::export_csv();
				break;
		}
	}

	wp_redirect( $page );
	exit;

} elseif ( ! empty( $_GET['_wp_http_referer'] ) ) {
	wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
	exit;
}

$list_table->prepare_items();
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e( 'Serial Numbers', 'wc-serial-numbers' ); ?></h1>

	<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'add' ) ) ); ?>" class="page-title-action">
		<?php _e( 'Add New', 'wc-serial-numbers' ); ?>
	</a>

	<form method="get">
		<div class="wcsn-serials-table">
			<?php $list_table->search_box( __( 'Search', 'wc-serial-numbers' ), 'serial-number' ); ?>
			<input type="hidden" name="page" value="wc-serial-numbers"/>
			<?php $list_table->views() ?>
			<?php $list_table->display() ?>
		</div>
	</form>


</div>
