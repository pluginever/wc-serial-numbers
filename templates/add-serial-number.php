<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly ?>

<form action="<?php echo admin_url( 'admin-post.php' ) ?>" method="post">

	<?php wp_nonce_field( 'wsn_add_edit_serial_numbers', 'wsn_add_edit_serial_numbers_nonce' ) ?>

	<input type="hidden" name="action" value="wsn_add_edit_serial_number">
	<input type="hidden" name="action_type" value="<?php echo $action_type ?>">

	<?php echo $input_serial_number_id ?>

	<table class="form-table">
		<tbody>

		<?php if ( ! isset( $is_serial_number_enabled ) ) { ?>
			<tr>
				<th scope="row">
					<label for="product"><?php _e( 'Choose Product', 'wc-serial-numbers' ) ?></label>
				</th>
				<td>
					<select name="product" id="product" class="ever-select  ever-field-inline" required>
						<option value=""><?php _e( 'Choose a product', 'wc-serial-numbers' ) ?></option>
						<?php

						$posts = wsn_get_products();

						foreach ( $posts as $post ) {

							setup_postdata( $post );

							if ( ! wsn_is_wsnp() && $post->get_type() != 'simple' ) {
								$disabled = 'disabled="disabled"';
								$upgrade  = __( '( Upgrade to PRO )', 'wc-serial-numbers' );
							} else {
								$upgrade  = '';
								$disabled = '';
							}

							$selected = $post->get_id() == $product ? 'selected' : '';

							echo sprintf( '<option value="%d" %s %s>%1$d - %s %s</option>', $post->get_id(), $selected, $disabled, get_the_title( $post->get_id() ), $upgrade );
						}

						?>
					</select>

					<?php if ( empty( wsn_disabled() ) ) { ?>
						<div class="ever-spinner-product hidden"></div>
					<?php } else { ?>

						<div class="ever-helper"> ?
							<span class="text"><?php _e( 'Upgrade to PRO for adding serial numbers for variable products.', 'wc-serial-numbers' ); ?></span>
						</div>

					<?php } ?>

				</td>
			</tr>
		<?php } ?>

		<tr>
			<th scope="row">
				<label for="variation"><?php _e( 'Product Variation', 'wc-serial-numbers' ) ?></label>
			</th>
			<td>

				<select name="variation" id="variation" class="ever-select  ever-field-inline" <?php echo wsn_disabled() ?>>
					<option value=""><?php _e( 'Main Product', 'wc-serial-numbers' ) ?></option>

					<?php

					if ( wsn_is_wsnp() && ! empty( $variation ) ) {

						$product_obj = wc_get_product( $product );

						$variations = $product_obj->get_children();

						if ( ! empty( $variations ) ) {

							foreach ( $variations as $all_variation ) {

								$variation_selected = ( $all_variation == $variation ) ? 'selected' : 'selected';

								echo '<option value="' . $all_variation . '" ' . $variation_selected . '>' . get_the_title( $variation ) . '</option>';
							}
						}
					}

					?>

				</select>

			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="serial_number"><?php _e( 'Serial Number', 'wc-serial-numbers' ) ?></label></th>
			<td class="ever-form-group">
				<textarea name="serial_number" type="text" id="serial_number" class="regular-text ever-field-inline" required><?php echo $serial_number ?></textarea>
				<div class="ever-helper"> ? <span class="text">
						<?php _e( 'You can enter multiline text.', 'wc-serial-numbers' ) ?>
						<br>
						<strong><?php _e( 'Example:', 'wc-serial-numbers' ); ?></strong>
						<br>
						username: johnsmith
						<br>
						password: johnsmith123
					</span>
				</div>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="image_license"><?php _e( 'Image License', 'wc-serial-numbers' ) ?></label>
			</th>
			<td>
				<button class="ever-upload button <?php echo wsn_class_disabled() ?>" type="button" <?php echo wsn_disabled() ?> id="image_license_upload"><?php _e( 'Upload', 'wc-serial-numbers' ); ?></button>

				<?php if ( ! empty( wsn_disabled() ) ) { ?>

					<div class="ever-helper"> ?
						<span class="text"><strong><?php _e( 'Upgrade to PRO for, using image as License', 'wc-serial-numbers' ); ?></strong></span>
					</div>

				<?php } else { ?>

					<div class="ever-helper"> ?
						<span class="text"><strong><?php _e( 'Upload a image for using image as License', 'wc-serial-numbers' ); ?></strong></span>
					</div>

				<?php } ?>

				<img class="image_license_prev ever-thumbnail" src="<?php echo $image_license ?>">
				<input type="hidden" id="image_license" name="image_license" value="<?php echo $image_license ?>">

				<button type="button" id="image_license_remove" class="button button-link-delete <?php echo $image_license ? '' : 'hidden'; ?>"><?php _e( 'Remove', 'wc-serial-numbers' ) ?></button>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="deliver_times"><?php _e( 'Max. Deliver Times', 'wc-serial-numbers' ) ?></label>
			</th>
			<td>
				<input type="number" min="1" value="<?php echo $deliver_times ?>" name="deliver_times" id="deliver_times" class=" ever-field-inline">
				<div class="ever-helper"> ?
					<span class="text"><?php _e( 'The maximum number, the serial number can be delivered.', 'wc-serial-numbers' ); ?></span>
				</div>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="max_instance"><?php _e( 'Maximum Instance', 'wc-serial-numbers' ) ?></label>
			</th>
			<td class="ever-form-group">
				<input type="number" min="0" value="<?php echo $max_instance ?>" name="max_instance" id="max_instance" class="ever-field-inline">
				<div class="ever-helper"> ?
					<span class="text"><?php _e( 'The maximum number of the implementation for the verification of the tracking api. Ignore, If your product doesn\'t need tracking', 'wc-serial-numbers' ); ?></span>
				</div>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="validity"><?php _e( 'Validity', 'wc-serial-numbers' ) ?></label>
			</th>
			<td>

				<input type="radio" class="validity_type" name="validity_type" value="days" <?php echo $validity_type == 'days' ? 'checked' : '' ?>> <?php _e( 'Days', 'wc-serial-numbers' ) ?>
				&ensp;
				<input type="radio" class="validity_type" name="validity_type" value="date" <?php echo $validity_type == 'date' ? 'checked' : '' ?>> <?php _e( 'Date', 'wc-serial-numbers' ) ?>

				<br> <br>

				<input type="<?php echo $validity_type == 'days' ? 'number' : 'text' ?>" min="0" name="validity" id="validity" class="regular-text  ever-field-inline" value="<?php echo $validity ?>">
				<div class="ever-helper"> ? <span class="text">
						<?php _e( 'Check Days for validity type of Days numbers', 'wc-serial-numbers' ); ?>
						<hr>
						<?php _e( 'Check Date for validity type of Date', 'wc-serial-numbers' ); ?>
					</span>
				</div>
			</td>
		</tr>

		</tbody>

	</table>
	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary add-serial-number-manually" value="<?php echo $submit ?>">
	</p>
</form>
