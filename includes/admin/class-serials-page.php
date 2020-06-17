<?php

namespace PluginEver\SerialNumbers\Admin;

use PluginEver\SerialNumbers\Admin\Table\Serial_Numbers_Table;
use PluginEver\SerialNumbers\Helper;
use PluginEver\SerialNumbers\Query_Serials;

defined( 'ABSPATH' ) || exit();

class Serials_Page {
	/**
	 * @var string;
	 */
	const SLUG = 'serial-numbers';

	/**
	 * Render serial number page.
	 *
	 * @since 1.2.0
	 */
	public static function output() {
		$action = isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
		if ( 'add' == $action ) {
			self::render_edit( null );
		} elseif ( 'edit' == $action && ! empty( $_GET['id'] ) ) {
			self::render_edit( intval( $_GET['id'] ) );
		} else {
			self::render_table();
		}
	}

	/**
	 * Render table
	 * @since 1.2.0
	 */
	protected static function render_table() {
		require_once dirname( __FILE__ ) . '/tables/class-table-serial-numbers.php';
		wp_enqueue_style( 'serial-list-tables' );
		$list_table = new Serial_Numbers_Table();
		$action     = $list_table->current_action();
		self::handle_actions( $action );
		$list_table->prepare_items();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php _e( 'Serial Numbers', 'wc-serial-numbers' ); ?></h1>
			<?php echo sprintf( '<a class="page-title-action" href="%s">%s</a>', add_query_arg( array( 'action' => 'add' ), admin_url( 'admin.php?page=serial-numbers' ) ), __( 'Add New', 'wc-serial-numbers' ) ); ?>
			<hr class="wp-header-end">
			<form method="get">
				<div class="serials-table">
					<?php $list_table->search_box( __( 'Search', 'wc-serial-numbers' ), 'serial-number' ); ?>
					<input type="hidden" name="page" value="serial-numbers"/>
					<?php $list_table->views() ?>
					<?php $list_table->display() ?>
				</div>
			</form>
		</div>
		<?php
	}

	protected static function render_edit( $id ) {
		$update = false;
		$item   = array(
			'id'               => '',
			'serial_key'       => '',
			'product_id'       => '',
			'activation_limit' => '',
			'order_id'         => '',
			'status'           => 'available',
			'validity'         => '',
			'expire_date'      => '',
		);

		if ( ! empty( $id ) && $serial = Query_Serials::init()->find( $id ) ) {
			$item               = wp_parse_args( get_object_vars( $serial ), $item );
			$item['serial_key'] = Helper::decrypt( $item['serial_key'] );
			$update             = true;
		}
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<?php if ( ! empty( $id ) ): ?>
					<?php _e( 'Update Serial', 'wc-serial-numbers' ) ?>
				<?php else: ?>
					<?php _e( 'Add Serial', 'wc-serial-numbers' ) ?>
				<?php endif ?>
			</h1>
			<a href="<?php echo esc_url( remove_query_arg( array( 'action', 'id' ) ) ); ?>" class="page-title-action">
				<?php _e( 'All Serials', 'wc-serial-numbers' ); ?>
			</a>
			<hr class="wp-header-end">

			<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" style="max-width: 600px">
				<table class="form-table">

					<tr>
						<th>
							<label for="product_id">
								<?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?>
							</label>
						</th>

						<td>
							<select name="product_id" id="product_id" class="regular-text serial-select-product" required="required" placeholder="<?php _e( 'Select Product', 'wc-serial-numbers' ); ?>">
								<?php echo sprintf( '<option value="%d" selected="selected">%s</option>', $item['product_id'], Helper::get_product_title( $item['product_id'] ) ); ?>
							</select>
							<p class="description"><?php esc_html_e( 'Select product to add serial number. NOTE: Free version does not support variation & subscription product.', 'wc-serial-numbers' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="serial_key">
								<?php esc_html_e( 'Serial Number', 'wc-serial-numbers' ); ?>
							</label>
						</th>

						<td>
							<textarea name="serial_key" id="serial_key" class="regular-text" required="required" placeholder="d555b5ae-d9a6-41cb-ae54-361427357382"><?php echo $item['serial_key']; ?></textarea>
							<p class="description"><?php esc_html_e( 'Your secret number, supports multiline. Will be encrypted before it saving. eg. d555b5ae-d9a6-41cb-ae54-361427357382', 'wc-serial-numbers' ); ?></p>
						</td>
					</tr>

					<?php if ( wc_serial_numbers()->get_settings('disable_software_support', false, true ) ): ?>
						<tr>
							<th scope="row">
								<label for="activation_limit">
									<?php esc_html_e( 'Activation Limit', 'wc-serial-numbers' ); ?>
								</label>
							</th>
							<td>
								<?php echo sprintf( '<input name="activation_limit" id="activation_limit" class="regular-text" type="number" value="%d" autocomplete="off">', $item['activation_limit'] ); ?>
								<p class="description"><?php esc_html_e( 'Maximum number of times the key can be used to activate the software. If the product is not software keep blank.', 'wc-serial-numbers' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="validity">
									<?php esc_html_e( 'Validity (days)', 'wc-serial-numbers' ); ?>
								</label>
							</th>
							<td>
								<?php echo sprintf( '<input name="validity" id="validity" class="regular-text" type="number" value="%d">', $item['validity'] ); ?>
								<p class="description"><?php esc_html_e( 'The number of days the key will be valid from the purchase date.', 'wc-serial-numbers' ); ?></p>
							</td>
						</tr>

					<?php endif; ?>

					<tr>
						<th scope="row">
							<label for="expire_date"><?php esc_html_e( 'Expires at', 'wc-serial-numbers' ); ?></label>
						</th>
						<td>
							<?php echo sprintf( '	<input name="expire_date" id="expire_date" class="regular-text serial-date-picker" type="text" autocomplete="off" value="%s">', $item['expire_date'] ); ?>
							<p class="description"><?php esc_html_e( 'After this date the key will not be assigned with any order. Leave blank for no expire date.', 'wc-serial-numbers' ); ?></p>
						</td>
					</tr>

					<?php if ( $update ): ?>
						<!-- status -->
						<tr>
							<th scope="row">
								<label for="status">
									<?php esc_html_e( 'Status', 'wc-serial-numbers' ); ?>
								</label>
							</th>
							<td>
								<select id="status" name="status" class="regular-text">
									<?php foreach ( wc_serial_numbers_get_serial_statuses() as $key => $option ): ?>
										<?php echo sprintf( '<option value="%s" %s>%s</option>', $key, selected( $item['status'], $key, false ), $option ); ?>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'The status of the serial number.', 'wc-serial-numbers' ); ?></p>
							</td>
						</tr>
						<!-- order -->
						<tr>
							<th scope="row">
								<label for="order_id">
									<?php esc_html_e( 'Order ID', 'wc-serial-numbers' ); ?>
								</label>
							</th>
							<td>
								<?php echo sprintf( '<input name="order_id" id="order_id" class="regular-text" type="number" value="%d" autocomplete="off">', $item['order_id'] ); ?>
								<p class="description"><?php esc_html_e( 'The order to which the serial number will be assigned.', 'wc-serial-numbers' ); ?></p>
							</td>
						</tr>
					<?php endif; ?>

					<tr>
						<td></td>
						<td>
							<p class="submit">
								<input type="hidden" name="action" value="add_serial_number">
								<?php echo sprintf( '<input type="hidden" name="id" value="%d">', $id ); ?>
								<?php wp_nonce_field( 'add_serial_number' ); ?>
								<?php if ( $update ): ?>
									<?php submit_button( __( 'Update Serial Number', 'wc-serial-numbers' ) ); ?>
								<?php else: ?>
									<?php submit_button( __( 'Add Serial Number', 'wc-serial-numbers' ) ); ?>
								<?php endif ?>
							</p>
						</td>
					</tr>

				</table>
			</form>

		</div>

		<?php
	}


	/**
	 * Handle table actions.
	 *
	 * @since 1.2.0
	 */
	protected static function handle_actions( $doaction ) {
		if ( $doaction ) {
			if ( isset( $_REQUEST['id'] ) ) {
				$ids      = [ intval( $_REQUEST['id'] ) ];
				$doaction = ( - 1 != $_REQUEST['action'] ) ? $_REQUEST['action'] : $_REQUEST['action2'];
			} elseif ( isset( $_REQUEST['ids'] ) ) {
				$ids = array_map( 'absint', $_REQUEST['ids'] );
			} elseif ( wp_get_referer() ) {
				wp_safe_redirect( wp_get_referer() );
				exit;
			}
			foreach ( $ids as $id ) { // Check the permissions on each.
				switch ( $doaction ) {
					case 'delete':
						wc_serial_numbers_delete_serial( $id );
						break;
					case 'activate':
						wc_serial_numbers_insert_serial( array(
							'id'         => $id,
							'order_id'   => null,
							'order_date' => null,
							'status'     => 'available',
						) );
						break;
					case 'deactivate':
						wc_serial_numbers_insert_serial( array(
							'id'     => $id,
							'status' => 'inactive',
						) );
						break;
				}
			}

			wp_safe_redirect( wp_get_referer() );
			exit;
		} elseif ( ! empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			exit;
		}
	}
}
