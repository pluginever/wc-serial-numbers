<?php if ( ! defined( 'ABSPATH' ) ) exit;?>

<div class="wsn_nottification"></div>

<h3 class="wsn_enable_serial_number"><?php _e('Enable Serial Number for this Product:', 'wc-serial-numbers');?></h3>

<input type="checkbox" name="enable_serial_number" id="enable_serial_number" <?php echo $is_serial_number_enabled == 'enable' ? 'checked' : '' ?>>
