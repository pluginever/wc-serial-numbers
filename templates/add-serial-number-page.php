<?php

$row_action = empty($_REQUEST['row_action']) ? '' : $_REQUEST['row_action'];
$type       = empty($_REQUEST['type']) ? '' : $_REQUEST['type'];

if ($type == 'automate') {
	$title = __('Add New Serial Number', 'wc-serial-numbers');
}else{

	if ($row_action == 'edit') {
		$serial_number_id = $_REQUEST['serial_number'];

		$serial_number = get_the_title($serial_number_id);
		$product       = get_post_meta($serial_number_id, 'product', true);
		$variation     = get_post_meta($serial_number_id, 'variation', true);
		$deliver_times = get_post_meta($serial_number_id, 'deliver_times', true);
		$max_instance  = get_post_meta($serial_number_id, 'max_instance', true);
		$validity_type = get_post_meta($serial_number_id, 'validity_type', true);
		$validity      = get_post_meta($serial_number_id, 'validity', true);
		$image_license = get_post_meta($serial_number_id, 'image_license', true);
		$title                  = __('Edit Serial Number', 'wc-serial-numbers');
		$submit                 = __('Save changes', 'wc-serial-numbers');
		$action_type            = 'wsn_edit_serial_number';
		$input_serial_number_id = '<input type="hidden" name="serial_number_id" value="' . $serial_number_id . '">';
	} else {
		$serial_number          = '';
		$product                = '';
		$variation              = '';
		$deliver_times          = '1';
		$max_instance           = '0';
		$validity_type          = 'days';
		$validity               = '';
		$image_license          = '';
		$title                  = __('Add New Serial Number', 'wc-serial-numbers');
		$submit                 = __('Add Serial Number', 'wc-serial-numbers');
		$action_type            = 'wsn_add_serial_number';
		$input_serial_number_id = '';
	}

}

?>


<div class="wrap wsn-container">

	<div class="ever-form-group">

		<h1 class="wp-heading-inline"><?php echo $title ?></h1>

		<a href="<?php echo add_query_arg('type', 'manual', WPWSN_ADD_SERIAL_PAGE); ?>" class="wsn-button-primary add-serial-title page-title-action"><?php _e('Add serial key manually', 'wc-serial-numbers') ?></a>

		<a href="<?php echo add_query_arg('type', 'automate', WPWSN_ADD_SERIAL_PAGE); ?>" class="wsn-button page-title-action <?php echo wsn_is_wsnp()? '': 'button-disabled'; ?>" <?php echo wsn_disabled() ?>><?php _e('Generate serial key Automatically', 'wc-serial-numbers') ?></a>

		<?php if (!wsn_is_wsnp()) { ?>

			<div class="ever-helper"> ?
				<span class="text"><strong><?php _e('Please Upgrade to PRO, for generating serial numbers Automatically.','wc-serial-numbers'); ?></strong></span>
			</div>

		<?php } ?>
	</div>

	<div class="wsn-message">
		<?php include WPWSN_TEMPLATES_DIR . '/messages.php'; ?>
	</div>

	<div class="ever-panel">
		<?php
		if ($type == 'automate') {

			ob_start();
			include WPWSN_TEMPLATES_DIR . '/generate-serial-number.php';
			$html = ob_get_clean();
			echo $html;

		}else{

			ob_start();
			include WPWSN_TEMPLATES_DIR . '/add-serial-number.php';
			$html = ob_get_clean();
			echo $html;

		}?>
	</div>
</div>


