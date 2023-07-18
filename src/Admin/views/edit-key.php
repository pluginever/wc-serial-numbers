<?php
/**
 * The template for editing a key.
 *
 * @package WooCommerceSerialNumbers/Admin/Views
 * @version 1.4.6
 * @var \WooCommerceSerialNumbers\Models\Key $key
 */

defined( 'ABSPATH' ) || exit;

$title = $key->exists() ? __( 'Update Serial Key', 'wc-serial-numbers' ) : __( 'Add Serial Key', 'wc-serial-numbers' );

?>

<div class="pev-admin-page__header">
	<div>
		<h2><?php echo esc_html( $title ); ?></h2>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-serial-numbers' ) ); ?>">
			<span class="dashicons dashicons-undo"></span>
		</a>
	</div>
	<div>
		<button type="submit" class="button button-primary" form="wcsn-edt-key">
			<?php esc_html_e( 'Save Serial Key', 'wc-serial-numbers' ); ?>
		</button>
		<div class="pev-dropdown">
			<!-- aria-expanded needs managed with Javascript -->
			<button type="button" class="button pev-dropdown__button" aria-expanded="false" aria-controls="pev-dropdown">
				<span class="dashicons dashicons-ellipsis"></span>
			</button>
			<ul class="pev-dropdown__menu" id="pev-dropdown">
				<li><a href="#">Donuts Donuts Donuts</a></li>
				<li><a href="#">Cupcakes</a></li>
				<li><a href="#">Chocolate</a></li>
				<li><a href="#">Bonbons</a></li>
			</ul>
		</div>
	</div>
</div>

<div class="pev-admin-page__wrapper">
	<div class="pev-admin-page__body">
		<div class="pev-card">
			<div class="pev-card__header">
				<h3 class="pev-card__title">
					<?php esc_html_e( 'Serial Key Details', 'wc-serial-numbers' ); ?>
				</h3>
			</div>
			<div class="pev-card__body">
				<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" id="wcsn-edt-key">
					<div class="pev-row">
						<div class="pev-form-field pev-col-12">
							<label for="product_id">
								<?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?>
								<abbr title="required">*</abbr>
							</label>
							<select name="product_id" id="product_id" class="wcsn_search_product" required="required" placeholder="<?php esc_html_e( 'Select Product', 'wc-serial-numbers' ); ?>">
								<option value=""><?php esc_html_e( 'Select Product', 'wc-serial-numbers' ); ?></option>
								<?php if ( ! empty( $key->get_product_id() ) ) : ?>
									<option value="<?php echo esc_attr( $key->get_product_id() ); ?>" selected="selected"><?php echo esc_html( $key->get_product_title() ); ?></option>
								<?php endif; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Select the product for which this key is applicable.', 'wc-serial-numbers' ); ?>
								<?php if ( ! empty( $key->get_parent_product_id() ) ) : ?>
									<small><a href="<?php echo esc_url( get_edit_post_link( $key->get_parent_product_id() ) ); ?>" target="_blank"><?php esc_html_e( 'Edit Product', 'wc-serial-numbers' ); ?></a></small>
								<?php endif; ?>
							</p>
						</div>

						<div class="pev-form-field pev-col-12">
							<label for="serial_key">
								<?php esc_html_e( 'Serial Key', 'wc-serial-numbers' ); ?>
								<abbr title="required">*</abbr>
							</label>
							<textarea name="serial_key" id="serial_key" rows="2" required="required" placeholder="<?php esc_html_e( 'Enter serial key', 'wc-serial-numbers' ); ?>"><?php echo esc_textarea( $key->get_serial_key() ); ?></textarea>
							<p class="description">
								<?php esc_html_e( 'Enter serial key for the product. e.g. 4CE0460D0G-4CE0460D1G-4CE0460D2G', 'wc-serial-numbers' ); ?>
							</p>
						</div>

						<!--activation limit-->
						<div class="pev-form-field pev-col-6">
							<label for="activation_limit"><?php esc_html_e( 'Activation Limit', 'wc-serial-numbers' ); ?></label>
							<input type="number" name="activation_limit" id="activation_limit" required="required" placeholder="<?php esc_html_e( 'Enter activation limit', 'wc-serial-numbers' ); ?>" value="<?php echo absint( $key->get_activation_limit() ); ?>">
							<p class="description">
								<?php esc_html_e( 'Maximum number of times the key can be used to activate the software. If its not a software leave it blank.', 'wc-serial-numbers' ); ?>
							</p>
						</div>

						<!--expire date-->
						<div class="pev-form-field pev-col-6">
							<label for="expire_date"><?php esc_html_e( 'Valid for (days)', 'wc-serial-numbers' ); ?></label>
							<input type="number" name="validity" id="validity" required="required" placeholder="<?php esc_html_e( 'Enter valid for', 'wc-serial-numbers' ); ?>" value="<?php echo absint( $key->get_expire_date() ); ?>">
							<p class="description">
								<?php esc_html_e( 'Number of days the key will be valid from the purchase date. Leave it blank for lifetime validity.', 'wc-serial-numbers' ); ?>
							</p>
						</div>

						<?php if ( $key->exists() ) : ?>
							<div class="pev-form-field pev-col-6">
								<label for="status"><?php esc_html_e( 'Key Status', 'wc-serial-numbers' ); ?></label>
								<select name="status" id="status" required="required" placeholder="<?php esc_html_e( 'Select Serial Key Status', 'wc-serial-numbers' ); ?>">
									<?php foreach ( wcsn_get_key_statuses() as $status => $value ) : ?>
										<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $status, $key->get_status() ); ?>><?php echo esc_html( $value ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description">
									<?php esc_html_e( 'Not recommended to change the status of the key. Status will be automatically updated based on the order status.', 'wc-serial-numbers' ); ?>
								</p>
							</div>

							<!--order-->
							<div class="pev-form-field pev-col-6">
								<label for="order_id"><?php esc_html_e( 'Order', 'wc-serial-numbers' ); ?></label>
								<select name="order_id" id="order_id" class="wcsn_search_order" placeholder="<?php esc_html_e( 'Select Order', 'wc-serial-numbers' ); ?>">
									<option value=""><?php esc_html_e( 'Select Order', 'wc-serial-numbers' ); ?></option>
									<?php if ( ! empty( $key->get_order_id() ) ) : ?>
										<option value="<?php echo esc_attr( $key->get_order_id() ); ?>" selected="selected"><?php echo esc_html( $key->get_order_id() ); ?></option>
									<?php endif; ?>
								</select>
								<p class="description">
									<?php esc_html_e( 'Select the order for which this key is generated.', 'wc-serial-numbers' ); ?>
								</p>
							</div>
						<?php endif; ?>
					</div>

					<input type="hidden" name="action" value="wc_serial_numbers_edit_key">
					<input type="hidden" name="id" value="<?php echo esc_attr( $key->get_id() ); ?>">
					<?php wp_nonce_field( 'wc_serial_numbers_edit_key' ); ?>
				</form>
			</div>
		</div>
		<?php if ( $key->exists() ) : ?>
			<div class="pev-card">
				<div class="pev-card__header">
					<h3 class="pev-card__title"><?php esc_html_e( 'Associated Order', 'wc-serial-numbers' ); ?></h3>
				</div>
				<div class="pev-card__body">
					<table class="widefat striped fixed">
						<thead>
						<tr>
							<th><?php esc_html_e( 'Order ID', 'wc-serial-numbers' ); ?></th>
							<th><?php esc_html_e( 'Order Date', 'wc-serial-numbers' ); ?></th>
							<th><?php esc_html_e( 'Order Status', 'wc-serial-numbers' ); ?></th>
							<th><?php esc_html_e( 'Order Total', 'wc-serial-numbers' ); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php if ( $key->get_order() ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( admin_url( 'post.php?post=' . $key->get_order_id() . '&action=edit' ) ); ?>" target="_blank">#<?php echo esc_html( $key->get_order_id() ); ?></a></td>
								<td><?php echo esc_html( $key->get_order()->get_date_created()->date( 'Y-m-d' ) ); ?></td>
								<td><?php echo esc_html( wc_get_order_status_name( $key->get_order()->get_status() ) ); ?></td>
								<td><?php echo wp_kses_post( $key->get_order()->get_formatted_order_total() ); ?></td>
							</tr>
						<?php else : ?>
							<tr>
								<td colspan="4"><?php esc_html_e( 'The key is not associated with any order.', 'wc-serial-numbers' ); ?></td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $key->exists() ) : ?>
		<div class="pev-card">
			<div class="pev-card__header">
				<h3 class="pev-card__title"><?php esc_html_e( 'Recent Activations', 'wc-serial-numbers' ); ?></h3>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-serial-numbers-activations&key_id=' . $key->get_id() ) ); ?>" class="pev-card__action"><?php esc_html_e( 'View All', 'wc-serial-numbers' ); ?></a>
			</div>
			<div class="pev-card__body">
				<table class="widefat striped fixed">
					<thead>
					<tr>
						<th><?php esc_html_e( 'Instance', 'wc-serial-numbers' ); ?></th>
						<th><?php esc_html_e( 'Platform', 'wc-serial-numbers' ); ?></th>
						<th><?php esc_html_e( 'Activation date', 'wc-serial-numbers' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php if ( $key->get_activations() ) : ?>
						<?php foreach ( $key->get_activations( [ 'limit' => 5 ] ) as $activation ) : ?>
							<tr>
								<td><?php echo esc_html( $activation->get_instance() ); ?></td>
								<td><?php echo esc_html( $activation->get_platform() ); ?></td>
								<td><?php echo esc_html( $activation->get_activation_time() ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="3"><?php esc_html_e( 'No activations found.', 'wc-serial-numbers' ); ?></td>
						</tr>
					</tbody>
					<?php endif; ?>
				</table>
				</div>
		</div>
		<?php endif; ?>
	</div>

	<?php if ( $key->exists() ) : ?>
		<div class="pev-admin-page__sidebar">
			<div class="pev-card">
				<div class="pev-card__header">
					<h3 class="pev-card__title"><?php esc_html_e( 'Notes', 'wc-serial-numbers' ); ?></h3>
				</div>
				<div class="pev-card__body">
					<form action="">
						<div class="pev-form-field">
							<label for="note"><?php esc_html_e( 'Add Note', 'wc-serial-numbers' ); ?></label>
							<textarea name="note" id="note" cols="30" rows="2" required="required" placeholder="Enter Notes"></textarea>
						</div>
						<input type="hidden" name="key_id" value="<?php echo esc_attr( $key->get_id() ); ?>">
						<?php wp_nonce_field( 'wcsn_add_note' ); ?>
						<button class="button"><?php esc_html_e( 'Add Note', 'wc-serial-numbers' ); ?></button>
					</form>
				</div>

				<div class="pev-card__body">
					<ul id="wcsn-notes">
						<li class="note">
							<div class="note__header">
								<div class="note__author">
									<?php echo get_avatar( get_current_user_id(), 32 ); ?>
									<span
										class="note__author-name"><?php echo get_the_author_meta( 'display_name', get_current_user_id() ); ?></span>
								</div>
								<div class="note__date">
									<?php echo date_i18n( 'M d, Y', strtotime( current_time( 'mysql' ) ) ); ?>
								</div>
							</div>
							<div class="note__content">
								<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium, quibusdam.</p>
							</div>
							<div class="note__actions">
								<a href="#"
								   class="note__action"><?php esc_html_e( 'Delete', 'wc-serial-numbers' ); ?></a>
							</div>
						</li>
						<li class="note">
							<div class="note__header">
								<div class="note__author">
									<?php echo get_avatar( get_current_user_id(), 32 ); ?>
									<span
										class="note__author-name"><?php echo get_the_author_meta( 'display_name', get_current_user_id() ); ?></span>
								</div>
								<div class="note__date">
									<?php echo date_i18n( 'M d, Y', strtotime( current_time( 'mysql' ) ) ); ?>
								</div>
							</div>
							<div class="note__content">
								<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium, quibusdam.</p>
							</div>
							<div class="note__actions">
								<a href="#"
								   class="note__action"><?php esc_html_e( 'Delete', 'wc-serial-numbers' ); ?></a>
							</div>
						</li>
						<li class="note">
							<div class="note__header">
								<div class="note__author">
									<?php echo get_avatar( get_current_user_id(), 32 ); ?>
									<span
										class="note__author-name"><?php echo get_the_author_meta( 'display_name', get_current_user_id() ); ?></span>
								</div>
								<div class="note__date">
									<?php echo date_i18n( 'M d, Y', strtotime( current_time( 'mysql' ) ) ); ?>
								</div>
							</div>
							<div class="note__content">
								<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium, quibusdam.</p>
							</div>
							<div class="note__actions">
								<a href="#"
								   class="note__action"><?php esc_html_e( 'Delete', 'wc-serial-numbers' ); ?></a>
							</div>
						</li>
						<li class="note">
							<div class="note__header">
								<div class="note__author">
									<?php echo get_avatar( get_current_user_id(), 32 ); ?>
									<span
										class="note__author-name"><?php echo get_the_author_meta( 'display_name', get_current_user_id() ); ?></span>
								</div>
								<div class="note__date">
									<?php echo date_i18n( 'M d, Y', strtotime( current_time( 'mysql' ) ) ); ?>
								</div>
							</div>
							<div class="note__content">
								<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium, quibusdam.</p>
							</div>
							<div class="note__actions">
								<a href="#"
								   class="note__action"><?php esc_html_e( 'Delete', 'wc-serial-numbers' ); ?></a>
							</div>
						</li>
						<li class="note">
							<div class="note__header">
								<div class="note__author">
									<?php echo get_avatar( get_current_user_id(), 32 ); ?>
									<span
										class="note__author-name"><?php echo get_the_author_meta( 'display_name', get_current_user_id() ); ?></span>
								</div>
								<div class="note__date">
									<?php echo date_i18n( 'M d, Y', strtotime( current_time( 'mysql' ) ) ); ?>
								</div>
							</div>
							<div class="note__content">
								<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium, quibusdam.</p>
							</div>
							<div class="note__actions">
								<a href="#"
								   class="note__action"><?php esc_html_e( 'Delete', 'wc-serial-numbers' ); ?></a>
							</div>
						</li>
					</ul>
				</div>
			</div>

		</div>
	<?php endif; ?>
</div>
