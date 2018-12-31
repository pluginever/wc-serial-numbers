<div class="wrap wsn-container">
	<h1 class="wp-heading-inline"><?php _e( 'Generate New Serial Number', 'wc-serial-numbers' ) ?></h1>
	<?php include WPWSN_TEMPLATES_DIR . '/messages.php'; ?>
	<form action="<?php echo admin_url( 'admin-post.php' ) ?>" method="post">
		<?php wp_nonce_field( 'wsn_generate_serial_numbers', 'wsn_generate_serial_numbers_nonce' ) ?>
		<input type="hidden" name="action" value="wsn_generate_serial_numbers">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="serial_number"><?php _e( 'Serial Number', 'wc-serial-numbers' ) ?></label>
				</th>
				<td>
					<input name="serial_number" type="text" id="serial_number" value=""
					       placeholder="51C8-P9NZ-UM37-YKZH" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="product"><?php _e( 'Product', 'wc-serial-numbers' ) ?></label></th>
				<td>
					<select name="product" id="product">
						<option value=""><?php _e( 'Choose a product', 'wc-serial-numbers' ) ?></option>
						<?php
						$posts = get_posts( [ 'post_type' => 'product' ] );
						foreach ( $posts as $post ) {
							setup_postdata( $post );
							echo "<option value='" . $post->ID . "'>" . get_the_title( $post->ID ) . "</option>";
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="usage_limit"><?php _e( 'Usage Limit', 'wc-serial-numbers' ) ?></label></th>
				<td>
					<input type="number" min="0" value="0" name="usage_limit">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="expired_date"><?php _e( 'Expired Date', 'wc-serial-numbers' ) ?></label>
				</th>
				<td>
					<input type="date" name="expired_date" id="expired_date" class="regular-text">
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
		                         value="<?php _e( 'Generate', 'wc-serial-numbers' ) ?>"></p>
	</form>
</div>
