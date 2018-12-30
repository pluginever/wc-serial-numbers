<div class="wrap wsn-container">
	<h1 class="wp-heading-inline"><?php _e( 'Generate New Serial Number', 'wc-serial-numbers' ) ?></h1>
	<form action="<?php echo admin_url( 'admin-post.php' ) ?>" method="post">
		<?php wp_nonce_field('wsn_generate_serial_numbers', 'wsn_generate_serial_numbers_nonce') ?>
		<input type="hidden" name="action" value="wsn_generate_serial_numbers">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="product"><?php _e( 'Product', 'wc-serial-numbers' ) ?></label></th>
				<td>
					<!--				<input name="product" type="text" id="product" value="" class="regular-text">-->
					<select name="product" id="product">
						<option value=""><?php _e( 'Choose a product', 'wc-serial-numbers' ) ?></option>
						<option value="">Sample Product</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="usage_limit"><?php _e( 'Usage Limit', 'wc-serial-numbers' ) ?></label></th>
				<td>
					<select name="usage_limit" id="usage_limit">
						<option value=""><?php _e( 'Choose limit', 'wc-serial-numbers' ) ?></option>
						<option value="1">1</option>
						<option value="5">5</option>
						<option value="10">10</option>
						<option value="15">15</option>
						<option value="unlimited">Unlimited</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="expired_date"><?php _e( 'Expired Date', 'wc-serial-numbers' ) ?></label>
				</th>
				<td>
					<select name="expired_date" id="expired_date">
						<option value=""><?php _e( 'Choose Expired Date', 'wc-serial-numbers' ) ?></option>
						<option value="1">1 days</option>
						<option value="5">5 days</option>
						<option value="10">10 days</option>
						<option value="15">15 days</option>
						<option value="life">life</option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Generate', 'wc-serial-numbers' ) ?>"></p>
	</form>
</div>
