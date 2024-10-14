<?php
/**
 * View file for editing a generator.
 *
 * @since 1.2.1
 * @package WooCommerceSerialNumbersPro\Admin\Views
 *
 * @var \WooCommerceSerialNumbers\Models\Generator $generator
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>
<div class="wrap pev-wrap">
	<h1 class="wp-heading-inline">
		<?php if ( $generator->exists() ) : ?>
			<?php esc_html_e( 'Edit generator', 'wc-serial-numbers' ); ?>
		<?php else : ?>
			<?php esc_html_e( 'Add New Generator', 'wc-serial-numbers' ); ?>
		<?php endif; ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wc-serial-numbers-generators' ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Back', 'wc-serial-numbers' ); ?>
		</a>
	</h1>
	<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="name">
						<?php esc_html_e( 'Name', 'wc-serial-numbers' ); ?>
						<abbr title="required">*</abbr>
					</label>
				</th>
				<td>
					<input type="text" name="name" id="name" class="regular-text" value="<?php echo esc_attr( $generator->name ); ?>" required="required"/>
					<p class="description">
						<?php esc_html_e( 'Enter a friendly name for the generator.', 'wc-serial-numbers' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pattern">
						<?php esc_html_e( 'Pattern', 'wc-serial-numbers' ); ?>
						<abbr title="required">*</abbr>
					</label>
				</th>
				<td>
					<input type="text" name="pattern" id="pattern" class="regular-text" value="<?php echo esc_attr( $generator->pattern ); ?>" required="required" placeholder="serial-####-####-####-####"/>
					<p class="description">
						<?php esc_html_e( 'Enter a pattern for this generator. Use # for random characters and y, m and d for year, month and date respectively.', 'wc-serial-numbers' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="charset">
						<?php esc_html_e( 'Charset', 'wc-serial-numbers' ); ?>
						<abbr title="required">*</abbr>
					</label>
				</th>
				<td>
					<input type="text" name="charset" id="charset" class="regular-text" value="<?php echo esc_attr( $generator->charset ); ?>" required="required"/>
					<p class="description">
						<?php esc_html_e( 'Enter the charset for the generator. Leave empty for default charset.', 'wc-serial-numbers' ); ?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="valid_for">
						<?php esc_html_e( 'Valid for (days)', 'wc-serial-numbers' ); ?>
					</label>
				</th>
				<td>
					<input name="valid_for" id="valid_for" class="regular-text" type="number" value="<?php echo esc_attr( $generator->valid_for ); ?>">
					<p class="description">
						<?php esc_html_e( 'Number of days the key will be valid from the purchase date. Leave it blank for lifetime validity.', 'wc-serial-numbers' ); ?>
					</p>
				</td>
			</tr>
			<?php if ( wcsn_is_software_support_enabled() ) : ?>
				<tr valign="top">
					<th scope="row">
						<label for="activation_limit">
							<?php esc_html_e( 'Activation limit', 'wc-serial-numbers' ); ?>
							<?php echo wp_kses_post( wc_help_tip( esc_html__( 'Maximum number of times the key can be used to activate the software. If the product is not software, keep it blank.', 'wc-serial-numbers' ), true ) ); ?>
						</label>
					</th>
					<td>
						<input name="activation_limit" id="activation_limit" class="regular-text" type="number" value="<?php echo esc_attr( $generator->activation_limit ); ?>">
						<p class="description">
							<?php esc_html_e( 'Maximum number of times the key can be used to activate the software. If the product is not software, keep it blank.', 'wc-serial-numbers' ); ?>
						</p>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<th scope="row">
					<label for="status">
						<?php esc_html_e( 'Status', 'wc-serial-numbers' ); ?>
					</label>
				</th>
				<td>
					<select name="status" id="status" required="required" class="regular-text">
						<?php
						foreach ( $generator->get_statuses() as $key => $label ) {
							printf(
								'<option value="%s" %s>%s</option>',
								esc_attr( $key ),
								selected( $generator->status, $key, false ),
								esc_html( $label )
							);
						}
						?>
					</select>
					<p class="description">
						<?php esc_html_e( 'Select the status for this generator.', 'wc-serial-numbers' ); ?>
					</p>
				</td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="2">
					<input type="hidden" name="action" value="wcsn_edit_generator">
					<input type="hidden" name="id" value="<?php echo esc_attr( $generator->id ); ?>">
					<?php wp_nonce_field( 'wcsn_edit_generator' ); ?>
					<?php submit_button( $generator->exists() ? esc_html__( 'Save Changes', 'wc-serial-numbers' ) : esc_html__( 'Create', 'wc-serial-numbers' ), 'primary' ); ?>
				</td>
			</tr>
			</tfoot>
		</table>
	</form>
</div>
