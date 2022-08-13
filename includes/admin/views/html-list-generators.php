<?php

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

global $wp_list_table;
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Generators', 'wc-serial-numbers' ); ?>
	</h1>
	<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wc-serial-numbers-generators&create' ) ); ?>" class="add-serial-title page-title-action">
		<?php esc_html_e( 'Add New', 'wc-serial-numbers' ); ?>
	</a>
	<hr class="wp-header-end">

	<form id="wsn-generators-table" method="get">
		<?php
		$wp_list_table->prepare_items();
		$wp_list_table->views();
		$wp_list_table->search_box( __( 'Search', 'woocommerce' ), 'search' );
		$wp_list_table->display();
		?>
		<input type="hidden" name="page" value="wsn-generators">
	</form>
</div>
