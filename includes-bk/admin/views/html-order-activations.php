<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
global $wpdb;

$activations = $wpdb->get_results( "
			SELECT * FROM {$wpdb->prefix}wcsn_activations as activations
			LEFT JOIN {$wpdb->prefix}wcsn_serial_numbers as licenses ON activations.serial_id = licenses.id
			WHERE order_id = {$post->ID}
		" );

if ( sizeof( $activations ) > 0 ) { ?>
	<div class="wcsn-metabox-table-wrapper">
		<table class="wcsn-metabox-table wp-list-table widefat fixed striped" cellspacing="0">
			<thead>
			<tr>
				<th><?php _e( 'Product', 'wc-serial-numbers' ) ?></th>
				<th><?php _e( 'Instance', 'wc-serial-numbers' ) ?></th>
				<th><?php _e( 'Platform/OS', 'wc-serial-numbers' ) ?></th>
				<th><?php _e( 'Status', 'wc-serial-numbers' ) ?></th>
				<th><?php _e( 'Date &amp; Time', 'wc-serial-numbers' ) ?></th>
			</tr>
			</thead>
			<tbody>
			<?php $i = 1;
			foreach ( $activations as $activation ) : $i ++ ?>
				<tr class="<?php echo ( $i % 2 == 1 ) ? 'alternate' : ''; ?>">
					<td><a href="<?php echo get_edit_post_link( $activation->product_id ); ?>"><?php echo get_the_title( $activation->product_id ); ?></a></td>
					<td><?php echo ( $activation->instance ) ? $activation->instance : __( 'N/A', 'wc-serial-numbers' ); ?></td>
					<td><?php echo ( $activation->platform ) ? $activation->platform : __( 'N/A', 'wc-serial-numbers' ); ?></td>
					<td><?php echo ( $activation->active ) ? __( 'Activated', 'wc-serial-numbers' ) : __( 'Deactivated', 'wc-serial-numbers' ) ?></td>
					<td><?php echo date( __( 'D j M Y \a\t h:ia', 'wc-serial-numbers' ), strtotime( $activation->activation_time ) ) ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

<?php } else { ?>
	<p style="padding:0 12px 12px;"><?php _e( 'No activations yet', 'wc-serial-numbers' ) ?></p>
	<?php
}
