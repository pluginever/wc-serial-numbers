<?php

$row_action = empty($_REQUEST['row_action']) ? '' : $_REQUEST['row_action'];

if ($row_action == 'edit') {
	$serial_number_id = $_REQUEST['serial_number'];
	$serial_number    = get_the_title($serial_number_id);
	$deliver_times    = get_post_meta($serial_number_id, 'deliver_times', true);
	$max_instance     = get_post_meta($serial_number_id, 'max_instance', true);
	$expires_on       = get_post_meta($serial_number_id, 'expires_on', true);
	$validity         = get_post_meta($serial_number_id, 'validity', true);
	$product          = get_post_meta($serial_number_id, 'product', true);
	$image_license    = get_post_meta($serial_number_id, 'image_license', true);
	//$order        = get_post_meta( $serial_number, 'order', true );
	//$purchased_on = get_post_meta( $serial_number, 'purchased_on', true );
	$title                  = __('Edit Serial Number', 'wc-serial-numbers');
	$submit                 = __('Save changes', 'wc-serial-numbers');
	$action_type            = 'wsn_edit_serial_number';
	$input_serial_number_id = '<input type="hidden" name="serial_number_id" value="' . $serial_number_id . '">';
} else {
	$serial_number          = '';
	$deliver_times          = '1';
	$max_instance           = '0';
	$expires_on             = '';
	$validity               = '';
	$product                = '';
	$image_license          = '';
	$title                  = __('Add New Serial Number', 'wc-serial-numbers');
	$submit                 = __('Add Serial Number', 'wc-serial-numbers');
	$action_type            = 'wsn_add_serial_number';
	$input_serial_number_id = '';
}

?>


<div class="wrap wsn-container">

	<div class="ever-form-group">

		<h1 class="wp-heading-inline"><?php echo $title ?></h1>

		<button class="wsn-button add-serial-title page-title-action"><?php _e('Add serial key manually', 'wc-serial-numbers') ?></button>

		<button class="wsn-button page-title-action <?php echo wsn_class_disabled() ?>" <?php echo wsn_disabled() ?>><?php _e('Generate serial key Automatically', 'wc-serial-numbers') ?></button>

		<?php if (!wsn_is_wsnp()) { ?>

			<div class="ever-helper"> ?
				<span class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Atque, aut consectetur, harum modi, mollitia obcaecati omnis optio placeat rerum saepe temporibus veniam! Consequatur dolores excepturi facere repellat, ullam veritatis vitae.</span>
			</div>

		<?php } ?>
	</div>

	<div class="wsn-message">
		<?php include WPWSN_TEMPLATES_DIR . '/messages.php'; ?>
	</div>

	<div class="ever-panel">

		<form action="<?php echo admin_url('admin-post.php') ?>" method="post">

			<?php wp_nonce_field('wsn_add_edit_serial_numbers', 'wsn_add_edit_serial_numbers_nonce') ?>

			<input type="hidden" name="action" value="wsn_add_edit_serial_number">
			<input type="hidden" name="action_type" value="<?php echo $action_type ?>">

			<?php echo $input_serial_number_id ?>

			<table class="form-table">
				<tbody>

				<?php if (!isset($is_serial_number_enabled)) { ?>
					<tr>
						<th scope="row"><label for="product"><?php _e('Choose Product', 'wc-serial-numbers') ?></label>
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

				<tr>
					<th scope="row"><label for="image_license"><?php _e('Image License', 'wc-serial-numbers') ?></label>
					</th>
					<td>
						<button class="ever-upload button <?php echo wsn_class_disabled() ?>" type="button" <?php echo wsn_disabled() ?> id="image_license_upload"><?php _e('Upload', 'wc-serial-numbers'); ?></button>

						<div class="ever-helper"> ?
							<span class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Atque, aut consectetur, harum modi, mollitia obcaecati omnis optio placeat rerum saepe temporibus veniam! Consequatur dolores excepturi facere repellat, ullam veritatis vitae.</span>
						</div>

						<img class="image_license_prev ever-thumbnail" src="<?php echo $image_license ?>">
						<input type="hidden" id="image_license" name="image_license" value="<?php echo $image_license ?>">

						<button type="button" id="image_license_remove" class="button button-link-delete <?php echo $image_license ? '' : 'hidden'; ?>"><?php _e('Remove', 'wsn-serial-numbers') ?></button>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="deliver_times"><?php _e('Deliver Times', 'wc-serial-numbers') ?></label>
					</th>
					<td>
						<input type="number" min="1" value="<?php echo $deliver_times ?>" name="deliver_times" id="deliver_times" class=" ever-field-inline">
						<div class="ever-helper"> ?
							<span class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Atque, aut consectetur, harum modi, mollitia obcaecati omnis optio placeat rerum saepe temporibus veniam! Consequatur dolores excepturi facere repellat, ullam veritatis vitae.</span>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="max_instance"><?php _e('Maximum Instance', 'wc-serial-numbers') ?></label>
					</th>
					<td class="ever-form-group">
						<input type="number" min="0" value="<?php echo $max_instance ?>" name="max_instance" id="max_instance" class="ever-field-inline">
						<div class="ever-helper"> ?
							<span class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Atque, aut consectetur, harum modi, mollitia obcaecati omnis optio placeat rerum saepe temporibus veniam! Consequatur dolores excepturi facere repellat, ullam veritatis vitae.</span>
						</div>
					</td>
				</tr>

<!--Expires On				<tr>-->
<!--					<th scope="row"><label for="expires_on">--><?php //_e('Expires On', 'wc-serial-numbers') ?><!--</label>-->
<!--					</th>-->
<!--					<td>-->
<!--						<input type="text" name="expires_on" id="expires_on" class="ever-date regular-text  ever-field-inline" value="--><?php //echo $expires_on ?><!--">-->
<!--						<div class="ever-helper"> ?-->
<!--							<span class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Atque, aut consectetur, harum modi, mollitia obcaecati omnis optio placeat rerum saepe temporibus veniam! Consequatur dolores excepturi facere repellat, ullam veritatis vitae.</span>-->
<!--						</div>-->
<!--					</td>-->
<!--				</tr>-->

				<tr>
					<th scope="row"><label for="validity"><?php _e('Validity (days)', 'wc-serial-numbers') ?></label>
					</th>
					<td>
						<input type="number" min="0" name="validity" id="validity" class="regular-text  ever-field-inline" value="<?php echo $validity ?>">
						<div class="ever-helper"> ?
							<span class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Atque, aut consectetur, harum modi, mollitia obcaecati omnis optio placeat rerum saepe temporibus veniam! Consequatur dolores excepturi facere repellat, ullam veritatis vitae.</span>
						</div>
					</td>
				</tr>

				</tbody>

			</table>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary add-serial-number-manually" value="<?php echo $submit ?>">
			</p>
		</form>

	</div>
</div>


