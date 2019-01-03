<div id="serial_numbers_data" class="panel woocommerce_options_panel hidden wsn-serial-number-tab">

	<div class="options_group plugin-card-bottom">

		<div class="wsn_nottification"></div>

		<h3 style="display: inline;">Enable Serial Number for this Product: </h3>
		<?php $enable_serial_number = get_post_meta( get_the_ID(), 'enable_serial_number', true ) ?>
		<input type="checkbox" name="enable_serial_number" id="enable_serial_number" <?php echo $enable_serial_number ? 'checked' : '' ?>>

		<h3 style="margin-bottom: -30px;">Available license number for this product:</h3>

		<?php require WPWSN_TEMPLATES_DIR . '/serial-numbers-page.php'; ?>

		<?php require WPWSN_TEMPLATES_DIR . '/add-serial-number.php'; ?>

	</div>

</div>

