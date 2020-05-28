<?php defined('ABSPATH') || exit; ?>
<h1 class="wp-heading-inline"><?php esc_html_e('Add Serial Number', 'wc-serial-number'); ?></h1>
<hr class="wp-header-end">


<form method="post" action="<?php echo esc_html(admin_url('admin-post.php'));?>" class="wc-serial-number-form">
	<table class="form-table">
		<tbody>
		<!-- product -->
		<tr scope="row">
			<th scope="row"><label for="product_id"><?php esc_html_e('Product', 'wc-serial-numbers');?></label></th>
			<td>
				<select name="product_id" id="product_id" class="regular-text"></select>
			</td>
		</tr>

		<!-- Serial Number -->
		<tr scope="row">
			<th scope="row"><label for="serial_key"><?php esc_html_e('Serial Number', 'wc-serial-numbers');?></label></th>
			<td>
				<textarea name="serial_key" id="serial_key" class="regular-text" placeholder="d555b5ae-d9a6-41cb-ae54-361427357382"></textarea>
				<p class="description"><?php esc_html_e('Your secret number, supports multiline.', 'wc-serial-numbers' );?></p>
			</td>
		</tr>

		<!-- Activation Limit -->
		<tr scope="row">
			<th scope="row"><label for="activation_limit"><?php esc_html_e('Activation Limit', 'wc-serial-numbers');?></label></th>
			<td>
				<input name="activation_limit" id="activation_limit" class="regular-text" type="number" min="min">
				<p class="description"><?php esc_html_e('Maximum number of times the key can be used to activate specially software. If the product is not software keep blank.', 'wc-serial-numbers');?></p>
			</td>
		</tr>


		<!-- Validity -->
		<tr scope="row">
			<th scope="row"><label for="validity"><?php esc_html_e('Validity (days)', 'wc-serial-numbers');?></label></th>
			<td>
				<input name="validity" id="validity" class="regular-text" type="number">
				<p class="description"><?php esc_html_e('The number of days after purchase the key will be valid.', 'wc-serial-numbers');?></p>
			</td>
		</tr>

		<!-- EXPIRES AT -->
		<tr scope="row">
			<th scope="row"><label for="expire_date"><?php esc_html_e('Expire Date', 'wc-serial-numbers');?></label></th>
			<td>
				<input name="expire_date" id="expire_date" class="regular-text" type="text">
				<p class="description"><?php esc_html_e('After this date the key will not be assigned with any order. Leave blank for no expire date.', 'wc-serial-numbers');?></p>
			</td>
		</tr>


		<!-- STATUS -->
		<tr scope="row">
			<th scope="row"><label for="status"><?php esc_html_e('Status', 'wc-serial-numbers');?></label></th>
			<td>
				<select id="status" name="status" class="regular-text">
					<?php foreach(WCSN_Serial_Number::statuses() as $key => $value): ?>
						<option value="<?php echo esc_html($key); ?>"><?php echo esc_html($value); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>

		<!-- Order -->
		<tr scope="row">
			<th scope="row"><label for="order_id"><?php esc_html_e('Order', 'wc-serial-numbers');?></label></th>
			<td>
				<select name="order_id" id="order_id" class="regular-text"></select>
				<p class="description"><?php esc_html_e('The order to which the license keys will be assigned.', 'wc-serial-numbers');?></p>
			</td>
		</tr>


		<!-- Customer -->
		<tr scope="row">
			<th scope="row"><label for="customer_id"><?php esc_html_e('Customer', 'wc-serial-numbers');?></label></th>
			<td>
				<select name="customer_id" id="customer_id" class="regular-text"></select>
				<p class="description"><?php esc_html_e('The customer to which the license keys will be assigned.', 'wc-serial-numbers');?></p>
			</td>
		</tr>

		<tr>
			<td></td>
			<td>
				<p class="submit">
					<input type="hidden" name="action" value="wcsn_add_serial_number">
					<?php wp_nonce_field('add_serial_number'); ?>
					<input name="submit" id="submit" class="button button-primary" value="<?php esc_html_e('Sumit' ,'wc-serial-numbers');?>" type="submit">
				</p>
			</td>
		</tr>

		</tbody>
	</table>

</form>
