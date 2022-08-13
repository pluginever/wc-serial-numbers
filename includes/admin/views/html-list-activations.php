<?php

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

global $wp_list_table;
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Activations', 'wc-serial-numbers' ); ?>
	</h1>
	<hr class="wp-header-end">

	<form id="wsn-activations-table" method="get">
		<?php
		$wp_list_table->prepare_items();
		$wp_list_table->views();
		$wp_list_table->search_box( __( 'Search', 'wc-serial-numbers' ), 'search' );
		$wp_list_table->display();
		?>
		<input type="hidden" name="page" value="wsn-activations">
	</form>
</div>
