<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Admin_Screen {
	public static function output() {
		$action = isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
		if ( in_array( $action, [ 'add', 'edit' ] ) ) {
			self::render_add( $action );
		} else {
			self::render_table();
		}

	}

	public static function render_add( $action ) {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		if ( empty( $id ) && 'edit' == $action ) {
			wp_redirect( add_query_arg( [ 'action' => 'add' ], remove_query_arg( array(
				'_wp_http_referer',
				'_wpnonce',
				'id'
			), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
			exit;
		}

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

		if ( ! empty( $id ) && $serial = WC_Serial_Numbers_Query::init()->from( 'serial_numbers' )->find( $id ) ) {
			$update             = true;
			$item               = wp_parse_args( get_object_vars( $serial ), $item );
			$item['serial_key'] = wc_serial_numbers_decrypt_key( $item['serial_key'] );
		}
		?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
				<?php if ( $update ): ?>
					<?php _e( 'Update Serial', 'wc-serial-numbers' ) ?>
				<?php else: ?>
					<?php _e( 'Add Serial', 'wc-serial-numbers' ) ?>
				<?php endif ?>
            </h1>
            <a href="<?php echo esc_url( remove_query_arg( array( 'action', 'id' ) ) ); ?>" class="page-title-action">
				<?php _e( 'Back', 'wc-serial-numbers' ); ?>
            </a>
            <hr class="wp-header-end">

			<?php
			if ( ! wc_serial_numbers()->is_pro_active() ) {
				echo sprintf( '<p class="wc-serial-numbers-upgrade-box" style="background-color: #fff;">%s <a href="%s" target="_blank" class="button">%s</a></p>', __( 'Checkout the full features of WooCommerce Serial Numbers Pro.', 'wc-serial-numbers' ), 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=create_serial_page&utm_medium=button&utm_campaign=wc-serial-numbers&utm_content=View%20Details', __( 'View Details', 'wc-serial-numbers' ) );
			}
			?>

            <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>"
                  style="max-width: 600px">
                <table class="form-table">

                    <tr>
                        <th>
                            <label for="product_id">
								<?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?>
                            </label>
                        </th>

                        <td>
                            <select name="product_id" id="product_id"
                                    class="regular-text wc-serial-numbers-select-product" required="required"
                                    placeholder="<?php _e( 'Select Product', 'wc-serial-numbers' ); ?>">
								<?php echo sprintf( '<option value="%d" selected="selected">%s</option>', $item['product_id'], wc_serial_numbers_get_product_title( $item['product_id'] ) ); ?>
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
                            <textarea name="serial_key" id="serial_key" class="regular-text" required="required"
                                      placeholder="d555b5ae-d9a6-41cb-ae54-361427357382"><?php echo $item['serial_key']; ?></textarea>
                            <p class="description"><?php esc_html_e( 'Your secret number, supports multiline. Will be encrypted before it saving. eg. d555b5ae-d9a6-41cb-ae54-361427357382', 'wc-serial-numbers' ); ?></p>
                        </td>
                    </tr>

					<?php if ( ! wc_serial_numbers_software_support_disabled() ): ?>
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
							<?php echo sprintf( '<input name="expire_date" id="expire_date" class="regular-text wc-serial-numbers-select-date" type="text" autocomplete="off" value="%s">', $item['expire_date'] ); ?>
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
									<?php foreach ( wc_serial_numbers_get_serial_number_statuses() as $key => $option ): ?>
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
                                <input type="hidden" name="action" value="wc_serial_numbers_edit_serial_number">
								<?php wp_nonce_field( 'edit_serial_number' ); ?>
								<?php if ( $update ): ?>
									<?php echo sprintf( '<input type="hidden" name="id" value="%d">', $id ); ?>
									<?php submit_button( __( 'Update Serial Number', 'wc-serial-numbers' ), 'primary', 'submit', false ); ?>
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

	public static function render_table() {
		require_once dirname( __DIR__ ) . '/tables/class-wc-serial-numbers-serial-numbers-list-table.php';
		$table    = new WC_Serial_Numbers_Serial_Numbers_List_Table();
		$doaction = $table->current_action();
		self::handle_bulk_actions( $doaction );
		$table->prepare_items();
		?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
				<?php _e( 'Serial Numbers', 'wc-serial-numbers' ); ?>
            </h1>
            <a href="<?php echo admin_url( 'admin.php?page=wc-serial-numbers&action=add' ) ?>"
               class="add-serial-title page-title-action">
				<?php _e( 'Add New', 'wc-serial-numbers' ) ?>
            </a>
            <hr class="wp-header-end">

            <form id="wc-serial-numbers-list" method="get">
				<?php
				$table->search_box( __( 'Search', 'wc-serial-numbers' ), 'search' );
				$table->views();
				$table->display();
				?>
                <input type="hidden" name="page" value="wc-serial-numbers">
            </form>
        </div>
		<?php
	}

	public static function handle_bulk_actions( $doaction ) {
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
						wc_serial_numbers_delete_serial_number( $id );
						break;
					case 'activate':
						wc_serial_numbers_update_serial_number( [
							'id'         => $id,
							'order_id'   => null,
							'order_date' => null,
							'status'     => 'available',
						] );
						break;
					case 'deactivate':
						wc_serial_numbers_update_serial_number_status( $id, 'inactive' );
						break;
				}
			}

			wp_safe_redirect( wp_get_referer() );
			exit;
		} elseif ( ! empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array(
				'_wp_http_referer',
				'_wpnonce'
			), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			exit;
		}
	}
}
