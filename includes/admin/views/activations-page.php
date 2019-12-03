<?php
defined( 'ABSPATH' ) || exit();
?>
	<div class="wrap wcsn-wrapper">
		<?php
		require_once WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/tables/class-activations-list-table.php';
		$list_table = new WC_Serial_Numbers_Activations_List_Table();
		$list_table->prepare_items();
		$base_url = admin_url( 'admin.php?page=wc-serial-numbers-activations' );
		?>

		<h1 class="wp-heading-inline"><?php _e( 'Activations', 'wc-serial-numbers' ); ?></h1>
		<form method="get" action="<?php echo esc_url( $base_url ); ?>">
			<div class="wcsn-list-table">
				<?php $list_table->search_box( __( 'Search', 'wc-serial-numbers' ), 'activations' ); ?>
				<input type="hidden" name="page" value="wc-serial-numbers-activations"/>
				<?php $list_table->views() ?>
				<?php $list_table->display() ?>
			</div>
		</form>
	</div>
<?php
