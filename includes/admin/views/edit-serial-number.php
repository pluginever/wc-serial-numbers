<?php defined( 'ABSPATH' ) || exit(); ?>
<?php
$id   = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : '';
$item = wc_serial_numbers_get_item( $id );
if ( empty( $item->id ) ) {
	wp_redirect( admin_url( 'admin.php?page=wc-serial-numbers' ) );
	exit();
}
$product       = wc_get_product( $item->product_id );
$product_title = sprintf(
	'(#%1$s) %2$s',
	$product->get_id(),
	html_entity_decode( $product->get_formatted_name() )
);

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo sprintf( __( 'Edit %s', 'wc-serial-numbers' ), wc_serial_numbers()->get_label() ) ?></h1>
	<a href="<?php echo esc_url( remove_query_arg( array( 'action', 'id' ) ) ); ?>" class="page-title-action">
		<?php echo sprintf( __( 'All %s', 'wc-serial-numbers' ), wc_serial_numbers()->get_label() ); ?>
	</a>

	<hr class="wp-header-end">

	<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">

		<table class="form-table">
			<tbody>

			<!-- Product -->
			<tr scope="row">
				<th scope="row">
					<label for="product_id">
						<?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?>
					</label>
				</th>
				<td>
					<select name="product_id" id="product_id" class="regular-text wcsn-select-product"
							required="required">
						<?php echo sprintf( '<option value="%d" %s>%s</option>', $item->product_id, selected( 'true', 'true', false ), $product_title ); ?>
					</select>
					<p class="description"><?php esc_html_e( 'The product to which the serial number will be assigned.', 'wc-serial-numbers' ); ?></p>
				</td>
			</tr>

			<!-- Serial Number -->
			<tr scope="row">
				<th scope="row">
					<label for="serial_key">
						<?php esc_html_e( 'Serial Number', 'wc-serial-numbers' ); ?>
					</label>
				</th>

				<td>
					<textarea
						name="serial_key"
						id="serial_key"
						class="regular-text"
						required="required"
						placeholder="d555b5ae-d9a6-41cb-ae54-361427357382"><?php echo wc_serial_numbers_decrypt_key($item->serial_key); ?></textarea>

					<p class="description"><?php esc_html_e( 'Your secret number, supports multiline. Will be encrypted before it is stored inside the database.', 'wc-serial-numbers' ); ?></p>
				</td>
			</tr>

			<?php if ( wc_serial_numbers()->is_software_support_enabled() ): ?>
				<!-- Activation Limit -->
				<tr scope="row">
					<th scope="row">
						<label for="activation_limit">
							<?php esc_html_e( 'Activation Limit', 'wc-serial-numbers' ); ?>
						</label>
					</th>
					<td>
						<?php echo sprintf( '<input name="activation_limit" id="activation_limit" class="regular-text" type="number" value="%d" autocomplete="off">', $item->activation_limit ); ?>
						<p class="description"><?php esc_html_e( 'Maximum number of times the key can be used to activate the software. If the product is not software keep blank.', 'wc-serial-numbers' ); ?></p>
					</td>
				</tr>

				<!-- Valid for -->
				<tr scope="row">
					<th scope="row">
						<label for="validity">
							<?php esc_html_e( 'Validity (days)', 'wc-serial-numbers' ); ?>
						</label>
					</th>
					<td>
						<?php echo sprintf( '<input name="validity" id="validity" class="regular-text" type="number" value="%d">', $item->validity ); ?>
						<p class="description"><?php esc_html_e( 'The number of days the key will be valid for after the purchase date.', 'wc-serial-numbers' ); ?></p>
					</td>
				</tr>

			<?php endif; ?>


			<!-- Expire Date -->
			<tr scope="row">
				<th scope="row">
					<label for="expire_date"><?php esc_html_e( 'Expires at', 'wc-serial-numbers' ); ?></label>
				</th>
				<td>
					<?php echo sprintf( '	<input name="expire_date" id="expire_date" class="regular-text wcsn-date-picker" type="text" autocomplete="off" value="%s">', $item->expire_date ); ?>
					<p class="description"><?php esc_html_e( 'After this date the key will not be assigned with any order. Leave blank for no expire date.', 'wc-serial-numbers' ); ?></p>
				</td>
			</tr>

			<!-- status -->
			<tr scope="row">
				<th scope="row">
					<label for="status">
						<?php esc_html_e( 'Status', 'wc-serial-numbers' ); ?>
					</label>
				</th>
				<td>
					<select id="status" name="status" class="regular-text">
						<?php foreach ( wc_serial_numbers_get_item_statuses() as $key => $option ): ?>
							<?php echo sprintf( '<option value="%s" %s>%s</option>', $key, selected( $item->status, $key, false ), $option ); ?>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'The status of the serial number.', 'wc-serial-numbers' ); ?></p>
				</td>
			</tr>

			<!-- order -->
			<tr scope="row">
				<th scope="row">
					<label for="order_id">
						<?php esc_html_e( 'Order ID', 'wc-serial-numbers' ); ?>
					</label>
				</th>
				<td>
					<?php echo sprintf( '<input name="order_id" id="order_id" class="regular-text" type="number" value="%d" autocomplete="off">', $item->order_id ); ?>
					<p class="description"><?php esc_html_e( 'The order to which the serial number will be assigned.', 'wc-serial-numbers' ); ?></p>
				</td>
			</tr>

			<tr>
				<td></td>
				<td>
					<p class="submit">
						<input type="hidden" name="action" value="wc_serial_numbers_add_serial_number">
						<?php wp_nonce_field( 'wc_serial_numbers_edit_item' ); ?>
						<?php echo sprintf( '<input type="hidden" name="id" value="%d">', $item->id ); ?>
						<?php echo sprintf( '<input name="submit" id="submit" class="button button-primary" value="%s"  type="submit">', __( 'Update', 'wc-serial-numbers' ) ); ?>
					</p>
				</td>
			</tr>


			</tbody>
		</table>


	</form>

</div>
