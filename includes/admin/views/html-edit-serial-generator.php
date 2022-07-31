<?php

use PluginEver\WooCommerceSerialNumbers\Helper;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();
?>
	<div class="wrap woocommerce">
		<?php if ( $generator->exists() ) : ?>
			<h2><?php esc_html_e( 'Edit Generator', 'wc-serial-numbers' ); ?></h2>
			<?php else : ?>
			<h2><?php esc_html_e( 'Add Generator', 'wc-serial-numbers' ); ?></h2>
			<?php endif; ?>
		<form method="POST">
			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="name"><?php esc_html_e( 'Name', 'wc-serial-numbers' ); ?></label>
					</th>
					<td>
						<?php echo sprintf( '<input name="name" id="name" class="regular-text" type="text" autocomplete="off" value="%s">', $generator->get_name() ); ?>
					</td>
				</tr>

				<tr valign="top">
					<th>
						<label for="pattern"><?php esc_html_e('Pattern', 'wc-serial-numbers'); ?>
							<?php echo wc_help_tip( esc_html__( 'Your secret number pattern E.g., d555b5ae-d9a6-41cb-ae54-361427357382', 'wc-serial-numbers' ), true ); ?>
						</label>
					</th>

					<td>
						<?php echo sprintf('<textarea name="pattern" id="pattern" class="regular-text" required="required" placeholder="d555b5ae-d9a6-41cb-ae54-361427357382">%s</textarea>', $generator->get_pattern()); ?>
					</td>
				</tr>

				<?php if ( is_plugin_active( 'wc-serial-numbers-pro/wc-serial-numbers-pro.php' ) ): ?>
					<tr valign="top">
						<th>
							<label for="type">
								<?php esc_html_e('Type', 'wc-serial-numbers'); ?>
							</label>
							<?php wc_help_tip( esc_html__('Select how serial numbers will be generated.', 'wc-serial-numbers') ); ?>
						</th>

						<td>
							<select name="type" id="type" class="regular-text">
								<?php
								$types = array(
									'random'     => __('Random', 'wc-serial-numbers'),
									'sequential' => __('Sequential', 'wc-serial-numbers'),
								);
								foreach ($types as $key => $option) {
									echo sprintf( '<option value="%s" %s>%s</option>', $key, selected( $key, $generator->get_is_sequential() ? 'sequential' : 'random' ), $option );
								}
								?>
							</select>
						</td>
					</tr>

					<?php if ( $generator->get_is_sequential() ) : ?>
						<tr>
							<th>
								<label for="sequential_pointer">
									<?php esc_html_e( 'Sequential pointer', 'wc-serial-numbers' ); ?>
								</label>
								<?php echo wc_help_tip( esc_html__( 'Current location of the sequential order pointer, do not change unless you know what you are doing.', 'wc-serial-numbers' ) ); ?>
							</th>
							<td>
								<?php echo sprintf( '<input name="sequential_pointer" id="sequential_pointer" class="regular-text" type="number" value="%s" autocomplete="off">', absint( $generator->get_sequential_pointer() ) ); ?>
							</td>
						</tr>

					<?php endif; ?>
				<?php endif;?>

				<tr valign="top">
					<th scope="row">
						<label for="expire_date"><?php esc_html_e( 'Expires at', 'wc-serial-numbers' ); ?></label>
						<?php echo wc_help_tip( esc_html__( 'After this date, the key will not be assigned with any order. Leave blank for no expiry date.', 'wc-serial-numbers' ), true ); ?>
					</th>
					<td>
						<?php echo sprintf( '<input name="date_expire" id="date_expire" class="regular-text wc-serial-numbers-select-date" type="text" autocomplete="off" value="%s">', $generator->get_date_expire() ); ?>
					</td>
				</tr>

				<?php if ( Helper::is_software_support_enabled() ) : ?>
					<tr valign="top">
						<th scope="row">

							<label for="activation_limit">
								<?php esc_html_e( 'Activation limit', 'wc-serial-numbers' ); ?>
								<?php echo wc_help_tip( esc_html__( 'Maximum number of times the key can be used to activate the software. If the product is not software, keep it blank.', 'wc-serial-numbers' ), true ); ?>
							</label>
						</th>
						<td>
							<?php echo sprintf( '<input name="activation_limit" id="activation_limit" class="regular-text" type="number" value="%d" autocomplete="off">', $generator->get_activation_limit() ); ?>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<label for="validity">
								<?php esc_html_e( 'Validity (days)', 'wc-serial-numbers' ); ?>
								<?php echo wc_help_tip( esc_html__( 'Number of days the key will be valid from the purchase date.', 'wc-serial-numbers' ), true ); ?>
							</label>
						</th>
						<td>
							<?php echo sprintf( '<input name="validity" id="validity" class="regular-text" type="number" value="%d">', $generator->get_validity() ); ?>
						</td>
					</tr>
				<?php endif; ?>

				<tr>
					<td></td>
					<td>
						<p class="submit">
							<input type="hidden" name="action" value="serial_numbers_edit_generator">
							<?php wp_nonce_field( 'serial_numbers_edit_generator' ); ?>
							<?php if ( $generator->exists() ) : ?>
								<?php echo sprintf( '<input type="hidden" name="id" value="%d">', $generator->get_id() ); ?>
								<input type="submit" name="serial_generators_edit" class="button-primary" value="<?php esc_attr_e( 'Update', 'wc-serial-numbers' ); ?>" />
							<?php else : ?>
								<input type="submit" name="serial_generators_edit" class="button-primary" value="<?php esc_attr_e( 'Submit', 'wc-serial-numbers' ); ?>" />
							<?php endif ?>
						</p>
					</td>
				</tr>

				</tbody>
			</table>
		</form>
	</div>
<?php
