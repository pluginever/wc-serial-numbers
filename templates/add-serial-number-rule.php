<form action="<?php echo admin_url('admin-post.php') ?>" method="post">

	<?php wp_nonce_field('wsn_add_edit_serial_numbers', 'wsn_add_edit_serial_numbers_nonce') ?>

	<input type="hidden" name="action" value="wsn_add_edit_serial_number">
	<input type="hidden" name="action_type" value="<?php echo $action_type ?>">

	<?php echo $input_serial_number_id ?>

	<table class="form-table">
		<tbody>

		<?php if (!isset($is_serial_number_enabled)) { ?>
			<tr>
				<th scope="row">
					<label for="product"><?php _e('Choose Product', 'wc-serial-numbers') ?></label>
				</th>
				<td>
					<select name="product" id="product" class="ever-select  ever-field-inline">
						<option value=""><?php _e('Choose a product', 'wc-serial-numbers') ?></option>
						<?php
						$posts = get_posts(['post_type' => 'product', 'posts_per_page' => -1]);
						foreach ($posts as $post) {
							setup_postdata($post);
							$selected = $post->ID == $product ? 'selected' : '';
							echo '<option value="' . $post->ID . '" ' . $selected . '>' . $post->ID . ' - ' . get_the_title($post->ID) . '</option>';
						}
						?>
					</select>
				</td>
			</tr>
		<?php } ?>

		<tr>
			<th scope="row">
				<label for="serial_number"><?php _e('Serial Number', 'wc-serial-numbers') ?></label></th>
			<td class="ever-form-group">
				<textarea name="serial_number" type="text" id="serial_number" class="regular-text ever-field-inline"><?php echo $serial_number ?></textarea>
				<div class="ever-helper"> ?
					<span class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Atque, aut consectetur, harum modi, mollitia obcaecati omnis optio placeat rerum saepe temporibus veniam! Consequatur dolores excepturi facere repellat, ullam veritatis vitae.</span>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
</form>
