<?php defined( 'ABSPATH' ) || exit(); ?>
<?php
require_once dirname( __DIR__ ) . '/tables/class-wc-serial-numbers-activations-table.php';
$list_table = new WC_Serial_Numbers_Activations_List_Table();

$doaction = $list_table->current_action();
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
				wc_serial_numbers_delete_activation( $id );
				break;
			case 'activate':
			case 'deactivate':
				global $wpdb;
				$activation = wc_serial_numbers_get_activation( $id );
				$status     = 'activate' === $doaction ? '1' : '0';
				if ( $activation ) {
					$wpdb->update( "{$wpdb->prefix}wc_serial_numbers_activations", array( 'active' => intval($status) ), array( 'id' => $activation->id ) );
					wc_serial_numbers_sync_activation_count( $activation->serial_id );
				}
				break;
		}
	}
	wp_safe_redirect( wp_get_referer() );
	exit;
} elseif ( ! empty( $_GET['_wp_http_referer'] ) ) {
	wp_redirect( remove_query_arg( array(
		'_wp_http_referer',
		'_wpnonce'
	), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
	exit;
}
$list_table->prepare_items();
?>

	<div class="wrap">
		<h1 class="wp-heading-inline"><?php _e( 'Activations', 'wc-serial-numbers' ); ?></h1>
		<form method="get" action="<?php echo admin_url( 'admin.php?page=wc-serial-numbers-activations' ); ?>">
			<div class="wcsn-list-table">
				<?php $list_table->search_box( __( 'Search', 'wc-serial-numbers' ), 'activations' ); ?>
				<input type="hidden" name="page" value="wc-serial-numbers-activations"/>
				<?php $list_table->views() ?>
				<?php $list_table->display() ?>
			</div>
		</form>
	</div>
<?php
