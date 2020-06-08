<?php defined( 'ABSPATH' ) || exit(); ?>
<?php
require_once dirname( __DIR__ ) . '/tables/class-wc-serial-numbers-products-table.php';

if( ! empty( $_GET['_wp_http_referer'] ) ) {
	wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
	exit;
}

$list_table = new WC_Serial_Numbers_Products_List_Table();
$list_table->prepare_items();
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e( 'Serial Numbers', 'wc-serial-numbers' ); ?></h1>

	<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'add' ) ) ); ?>" class="page-title-action">
		<?php _e( 'Add New', 'wc-serial-numbers' ); ?>
	</a>

	<form method="get">
		<div class="wcsn-products-table">
			<?php $list_table->search_box( __( 'Search', 'wc-serial-numbers' ), 'serial-number' ); ?>
			<input type="hidden" name="page" value="wc-serial-numbers-products"/>
			<?php $list_table->views() ?>
			<?php $list_table->display() ?>
		</div>
	</form>
</div>

