<?php
global $post;
global $serial_numbers;
$total_assigned = 0;
$total_qty      = 0;
?>
	<table class="widefat striped">
		<thead>
		<tr>
			<th><?php _e( 'Product', 'wc-serial-numbers' ); ?></th>
			<th><?php _e( 'Quantity Ordered', 'wc-serial-numbers' ); ?></th>
			<th><?php _e( 'Assigned Quantity', 'wc-serial-numbers' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php

		foreach ( $serial_numbers as $product_id => $quantity ) {
			$product      = get_post( $product_id );
			$title        = get_the_title( $product );
			$link         = get_edit_post_link( empty( $product->post_parent ) ? $product->ID : $product->post_parent );
			$cols         = sprintf( '<td><a href="%s" target="_blank">%s</a></td><td>%d</td>', $link, $title, $quantity );
			$assigned_qty = WC_Serial_Numbers_Manager::get_serial_numbers( [
				'order_id'   => $post->ID,
				'product_id' => $product_id,
			], true );

			$assigned_link = add_query_arg( array(
				'order_id'   => $post->ID,
				'product_id' => $product_id,
			), admin_url( 'admin.php?page=wc-serial-numbers' ) );

			$cols .= sprintf( '<td><a href="%s" target="_blank">%d</a></td>', $assigned_link, $assigned_qty );
			echo sprintf( '<tr>%s</tr>', $cols );
			$total_assigned += $assigned_qty;
			$total_qty      += $quantity;
		} ?>
		</tbody>
	</table>
<?php
$title = ( $total_assigned == $total_qty ) ? __( 'Reassign Serial Numbers', 'wc-serial-numbers' ) : __( 'Assign Serial Numbers', 'wc-serial-numbers' );
if ( current_user_can( 'manage_woocommerce' ) ) {
	echo sprintf( '<br/><a href="%s" target="_self" class="button button-secondary">%s</a>', add_query_arg( array(
		'action'   => 'wc_serial_numbers_reassign_serial_numbers',
		'order_id' => $post->ID,
		'nonce'    => wp_create_nonce( 'assign_serial_numbers' )
	), admin_url('admin-post.php') ), $title );
}
