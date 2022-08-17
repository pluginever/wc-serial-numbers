<?php

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

global $wp_list_table;
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Serial Keys', 'wc-serial-numbers' ); ?>
	</h1>
	<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wc-serial-numbers&create' ) ); ?>" class="add-serial-title page-title-action">
		<?php esc_html_e( 'Add New', 'wc-serial-numbers' ); ?>
	</a>

	<hr class="wp-header-end">

	<form id="serial-numbers-keys-table" method="get">
		<?php
		$wp_list_table->prepare_items();
		$wp_list_table->views();
		$wp_list_table->search_box( __( 'Search key', 'woocommerce' ), 'key' );
		$wp_list_table->display();
		?>
		<input type="hidden" name="page" value="wcsn-keys">
	</form>
</div>
