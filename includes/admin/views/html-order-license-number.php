<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $wpdb;

$serial_numbers = wcsn_get_serial_numbers( [ 'order_id' => $post->ID ] );

if ( sizeof( $serial_numbers ) > 0 ) { ?>
	<div class="wcsn-metabox-table-wrapper">
		<table class="wcsn-metabox-table wp-list-table widefat fixed striped" cellspacing="0">
			<thead>
			<tr>
				<th><?php _e( 'Product', 'wc-serial-numbers' ) ?></th>
				<th><?php _e( 'Serial Key', 'wc-serial-numbers' ) ?></th>
				<th><?php _e( 'Activation Limit', 'wc-serial-numbers' ) ?></th>
				<th><?php _e( 'Expire Date', 'wc-serial-numbers' ) ?></th>
				<th><?php _e( 'Status', 'wc-serial-numbers' ) ?></th>
				<th><?php _e( 'Actions', 'wc-serial-numbers' ) ?></th>
			</tr>
			</thead>
			<tbody>
			<?php $i = 1;
			foreach ( $serial_numbers as $serial_number ): $i ++ ?>
				<tr class="<?php echo ( $i % 2 == 1 ) ? 'alternate' : ''; ?>">
					<td>
						<a href="<?php echo get_edit_post_link( $serial_number->product_id ); ?>"><?php echo get_the_title( $serial_number->product_id ); ?></a>
					</td>
					<td><span style="max-width: 150px;"><?php echo $serial_number->serial_key; ?></span></td>
					<td><?php echo ( $serial_number->activation_limit ) ? $serial_number->activation_limit : __( 'N/A', 'wc-serial-numbers' ); ?></td>
					<td><?php echo wcsn_get_serial_expiration_date($serial_number); ?></td>
					<td><?php echo ! empty( $serial_number->status ) ? "<span class='wcsn-status-{$serial_number->status}'>" . wcsn_get_serial_statuses()[ $serial_number->status ] . '</span>' : '&#45;'; ?></td>
					<td>
						<a href="<?php echo add_query_arg( array(
							'page'        => 'wc-serial-numbers',
							'action_type' => 'add_serial_number',
							'row_action'  => 'edit',
							'serial_id'   => $serial_number->id,
						), admin_url( 'admin.php' ) ); ?>" class="wcsn-action"  title="<?php _e( 'Edit', 'wc-serial-numbers' ); ?>">
							<span class="dashicons-edit dashicons-before wp-menu-image wcsn-action-edit" ></span>
						</a>

						<a href="<?php echo add_query_arg( array(
							'_wp_http_referer' => urlencode( $_SERVER['REQUEST_URI'] ),
							'nonce'            => wp_create_nonce( 'unlink_serial_number' ),
							'action'           => 'unlink_serial_number',
							'serial_id'        => $serial_number->id,
						), admin_url( 'admin-post.php' ) ); ?>" class="wcsn-action" title="<?php _e( 'Unlink', 'wc-serial-numbers' ); ?>"><span class="dashicons-editor-unlink dashicons-before wp-menu-image wcsn-action-unlink"></span></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php } else {
	?>
	<p style="padding:0 12px 12px;"><?php _e( 'No Serial enabled products', 'wc-serial-numbers' ) ?></p>
	<?php
}
?>
	<style>
		.wcsn-metabox-table th {
			text-align: left;
			padding: 1em;
			font-weight: 600;
			color: #333;
			background: #efefef;
			-webkit-touch-callout: none;
			-webkit-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
		}
	</style>
<?php
