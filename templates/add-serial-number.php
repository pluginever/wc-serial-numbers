<?php
if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ) {
	$serial_number_id = $_REQUEST['serial_number'];
	$serial_number    = get_the_title( $serial_number_id );
	$usage_limit      = get_post_meta( $serial_number_id, 'usage_limit', true );
	$expires_on       = get_post_meta( $serial_number_id, 'expires_on', true );
	$product          = get_post_meta( $serial_number_id, 'product', true );
	//$order        = get_post_meta( $serial_number, 'order', true );
	//$purchased_on = get_post_meta( $serial_number, 'purchased_on', true );
	$title                  = 'Edit';
	$submit                 = 'Save changes';
	$action                 = 'wsn_edit_serial_number';
	$input_serial_number_id = '<input type="hidden" name="serial_number_id" value="' . $serial_number_id . '">';
} else {
	$serial_number          = '';
	$usage_limit            = '';
	$expires_on             = '';
	$product                = '';
	$title                  = 'Add New';
	$submit                 = 'Add Serial Number';
	$action                 = 'wsn_add_serial_number';
	$input_serial_number_id = '';
}
?>
<div class="wrap wsn-container">
	<h1 class="wp-heading-inline"><?php _e( $title . ' Serial Number', 'wc-serial-numbers' ) ?></h1>

	<div class="wsn-body">
		<?php include WPWSN_TEMPLATES_DIR . '/messages.php'; ?>
		<form action="<?php echo admin_url( 'admin-post.php' ) ?>" method="post">
			<?php wp_nonce_field( 'wsn_generate_serial_numbers', 'wsn_generate_serial_numbers_nonce' ) ?>
			<input type="hidden" name="action" value="<?php echo $action ?>">
			<?php echo $input_serial_number_id ?>
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row"><label
							for="serial_number"><?php _e( 'Serial Number', 'wc-serial-numbers' ) ?></label>
					</th>
					<td>
						<input name="serial_number" type="text" id="serial_number" value="<?php echo $serial_number ?>"
							placeholder="51C8-P9NZ-UM37-YKZH" class="regular-text">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="product"><?php _e( 'Product', 'wc-serial-numbers' ) ?></label></th>
					<td>
						<select name="product" id="product">
							<option value=""><?php _e( 'Choose a product', 'wc-serial-numbers' ) ?></option>
							<?php
							$posts = get_posts( [ 'post_type' => 'product', 'posts_per_page' => - 1 ] );
							foreach ( $posts as $post ) {
								setup_postdata( $post );
								$selected = $post->ID == $product ? 'selected' : '';
								echo '<option value="' . $post->ID . '" ' . $selected . '>' . $post->ID . ' - ' . get_the_title( $post->ID ) . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="usage_limit"><?php _e( 'Usage Limit', 'wc-serial-numbers' ) ?></label>
					</th>
					<td>
						<input type="number" min="1" value="<?php echo $usage_limit ?>" name="usage_limit">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="expires_on"><?php _e( 'Expires On', 'wc-serial-numbers' ) ?></label>
					</th>
					<td>
						<input type="date" name="expires_on" id="expires_on" class="regular-text"
							value="<?php echo $expires_on ?>">
					</td>
				</tr>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary"
					value="<?php _e( $submit, 'wc-serial-numbers' ) ?>">
			</p>
		</form>
	</div>
</div>
