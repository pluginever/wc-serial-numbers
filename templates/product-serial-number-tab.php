<div id="serial_numbers_data" class="panel woocommerce_options_panel hidden">
	<div class="options_group plugin-card-bottom">
		<h4>Available license number for this product:</h4>
		<table class="fixed wp-list-table widefat striped" id="tab-table-serial-numbers">
			<thead>
			<tr>
				<td>Serial Numbers</td>
				<td>Usage/ Limit</td>
				<td>Expires On</td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>vbnm</td>
				<td>05</td>
				<td>dfdghj</td>
			</tr>
			</tbody>
		</table>
		<h4>Add new serial number for this product</h4>
		<table class="form-table">
			<input type="hidden" name="product" id="product" value="<?php echo get_the_ID() ?>">
			<tbody>
			<tr>
				<th scope="row">
					<?php _e( 'Serial Number', 'wc-serial-numbers' ) ?>
				</th>
				<td>
					<input name="serial_number" type="text" id="serial_number" value=""
					       placeholder="51C8-P9NZ-UM37-YKZH" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Usage Limit', 'wc-serial-numbers' ) ?></th>
				<td>
					<input type="number" min="0" value="0" name="usage_limit" id="usage_limit">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Expires On', 'wc-serial-numbers' ) ?></th>
				<td>
					<input type="date" name="expires_on" id="expires_on" class="regular-text">
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="add-serial-number" id="add-serial-number" class="button button-primary"
			       value="<?php _e( 'Generate', 'wc-serial-numbers' ) ?>">
		</p>
	</div>
</div>

