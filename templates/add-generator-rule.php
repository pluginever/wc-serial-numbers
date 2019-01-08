<?php

$row_action = empty($_REQUEST['row_action']) ? '' : $_REQUEST['row_action'];


if ($row_action == 'edit') {
	$serial_number_id       = $_REQUEST['serial_number'];
	$serial_number          = get_the_title($serial_number_id);
	$deliver_times          = get_post_meta($serial_number_id, 'deliver_times', true);
	$max_instance           = get_post_meta($serial_number_id, 'max_instance', true);
	$expires_on             = get_post_meta($serial_number_id, 'expires_on', true);
	$validity               = get_post_meta($serial_number_id, 'validity', true);
	$product                = get_post_meta($serial_number_id, 'product', true);
	$image_license          = get_post_meta($serial_number_id, 'image_license', true);
	$title                  = __('Edit Generator Rule', 'wc-serial-numbers');
	$submit                 = __('Save changes', 'wc-serial-numbers');
	$action_type            = 'wsn_edit_generator_rule';
	$input_serial_number_id = '<input type="hidden" name="serial_number_id" value="' . $serial_number_id . '">';
} else {
	$serial_number          = '';
	$deliver_times          = '1';
	$max_instance           = '0';
	$expires_on             = '';
	$validity               = '';
	$product                = '';
	$image_license          = '';
	$title                  = __('Add New Generator Rule', 'wc-serial-numbers');
	$submit                 = __('Add Rule', 'wc-serial-numbers');
	$action_type            = 'wsn_add_generator_rule';
	$input_serial_number_id = '';
}


?>


<div class="wrap wsn-container">

	<div class="ever-form-group">

		<h1 class="wp-heading-inline"><?php echo $title ?></h1>

		<a href="<?php echo add_query_arg('type', 'manual', WPWSN_ADD_SERIAL_PAGE); ?>" class="wsn-button add-serial-title page-title-action"><?php _e('Add serial key manually', 'wc-serial-numbers') ?></a>

		<a href="<?php echo add_query_arg('type', 'automate', WPWSN_ADD_SERIAL_PAGE); ?>" class="wsn-button page-title-action <?php echo wsn_class_disabled() ?>" <?php echo wsn_disabled() ?>><?php _e('Generate serial key Automatically', 'wc-serial-numbers') ?></a>

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

			<?php wp_nonce_field('wsn_add_edit_generator_rule', 'wsn_add_edit_generator_rule_nonce') ?>

			<input type="hidden" name="action" value="wsn_add_edit_generator_rule">
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
							<div class="ever-spinner-product hidden"></div>
						</td>
					</tr>
				<?php } ?>

				<tr>
					<th scope="row">
						<label for="variation"><?php _e('Product Variation', 'wc-serial-numbers') ?></label>
					</th>
					<td>
						<select name="variation" id="variation" class="ever-select  ever-field-inline">
							<option value=""><?php _e('Main Product', 'wc-serial-numbers') ?></option>
						</select>
					</td>
				</tr>

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
	</div>
</div>
