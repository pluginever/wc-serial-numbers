<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\Models\Key;
use WooCommerceSerialNumbers\Lib\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * Class Menus.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Menus extends Singleton {

	/**
	 * List table.
	 *
	 * @since 1.0.0
	 * @var ListTables\ListTable;
	 */
	protected $list_table;

	/**
	 * Menus constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'admin_menu', array( $this, 'main_menu' ) );
		add_action( 'admin_menu', array( $this, 'activations_menu' ), 40 );
		add_action( 'admin_menu', array( $this, 'tools_menu' ), 50 );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 100 );
		add_action( 'admin_menu', array( $this, 'promo_menu' ), PHP_INT_MAX );
		add_action( 'current_screen', array( $this, 'setup_screen' ) );
		add_action( 'check_ajax_referer', array( $this, 'setup_screen' ) );
		add_filter( 'set-screen-option', array( __CLASS__, 'save_screen_options' ), 10, 3 );

		// Add tabs content.
		add_action( 'wc_serial_numbers_tools_tab_status', array( __CLASS__, 'status_tab' ) );
		add_action( 'wc_serial_numbers_tools_tab_api', array( __CLASS__, 'api_tab' ) );
		add_action( 'wc_serial_numbers_tools_tab_import', array( __CLASS__, 'csv_file_import' ) );
		add_action( 'wc_serial_numbers_tools_tab_import', array( __CLASS__, 'text_file_import' ) );
	}

	/**
	 * Add menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function main_menu() {
		$role = wcsn_get_manager_role();
		add_menu_page(
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			$role,
			'wc-serial-numbers',
			null,
			'dashicons-lock',
			'55.9'
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Serial Keys', 'wc-serial-numbers' ),
			__( 'Serial Keys', 'wc-serial-numbers' ),
			$role,
			'wc-serial-numbers',
			array( $this, 'output_main_page' )
		);
	}

	/**
	 * Add activations menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function activations_menu() {
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Activations', 'wc-serial-numbers' ),
			__( 'Activations', 'wc-serial-numbers' ),
			wcsn_get_manager_role(),
			'wc-serial-numbers-activations',
			array( $this, 'output_activations_page' )
		);
	}

	/**
	 * Add tools menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function tools_menu() {
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Tools', 'wc-serial-numbers' ),
			__( 'Tools', 'wc-serial-numbers' ),
			wcsn_get_manager_role(),
			'wc-serial-numbers-tools',
			array( $this, 'output_tools_page' )
		);
	}

	/**
	 * Settings menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function settings_menu() {
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Settings', 'wc-serial-numbers' ),
			__( 'Settings', 'wc-serial-numbers' ),
			wcsn_get_manager_role(),
			'wc-serial-numbers-settings',
			array( Settings::class, 'output' )
		);
	}

	/**
	 * Add promo Menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function promo_menu() {
		$role = wcsn_get_manager_role();
		if ( ! wc_serial_numbers()->is_premium_active() ) {
			add_submenu_page(
				'wc-serial-numbers',
				'',
				'<span style="color:#ff7a03;"><span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . __( 'Go Pro', 'wc-serial-numbers' ) . '</span>',
				$role,
				'go_wcsn_pro',
				array( $this, 'go_pro_redirect' )
			);
		}
	}

	/**
	 * Looks at the current screen and loads the correct list table handler.
	 *
	 * @since 1.4.6
	 */
	public function setup_screen() {
		if ( isset( $_GET['edit'] ) || isset( $_GET['delete'] ) || isset( $_GET['add'] ) || isset( $_GET['generate'] ) ) {
			return;
		}

		$screen_id        = false;
		$plugin_screen_id = sanitize_title( __( 'Serial Numbers', 'wc-serial-numbers' ) );
		if ( function_exists( 'get_current_screen' ) ) {
			$screen    = get_current_screen();
			$screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
		}

		// switch ( $screen_id ) {
		// case $plugin_screen_id . '-page-wc-serial-numbers':
		// $this->list_table = new ListTables\KeysTable();
		// break;
		// }

		// Ensure the table handler is only loaded once. Prevents multiple loads if a plugin calls check_ajax_referer many times.
		remove_action( 'current_screen', array( $this, 'setup_screen' ) );
		remove_action( 'check_ajax_referer', array( $this, 'setup_screen' ) );
	}

	/**
	 * Validate screen options on update.
	 *
	 * @param bool|int $status Screen option value. Default false to skip.
	 * @param string $option The option name.
	 * @param int $value The number of rows to use.
	 */
	public function save_screen_options( $status, $option, $value ) {
		if ( in_array( $option, array( 'wsn_keys_per_page', 'wsn_generators_per_page', 'wsn_activations_per_page' ), true ) ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Output keys page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_main_page() {
		if ( isset( $_GET['add'] ) || isset( $_GET['edit'] ) ) {
			$id  = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
			$key = new Key( $id );
			if ( ! empty( $id ) && ! $key->exists() ) {
				wp_safe_redirect( remove_query_arg( 'edit' ) );
				exit();
			}
			Admin::view( 'html-edit-key.php', array( 'key' => $key ) );
		} else {
			Admin::view( 'html-list-keys.php' );
		}
	}

	/**
	 * Output activations page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_activations_page() {
		Admin::view( 'html-list-activations.php' );
	}

	/**
	 * Output tools page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_tools_page() {
		$tabs = array(
			'import' => __( 'Import', 'wc-serial-numbers' ),
			'export' => __( 'Export', 'wc-serial-numbers' ),
			'api'    => __( 'API', 'wc-serial-numbers' ),
			'status' => __( 'Status', 'wc-serial-numbers' ),
		);

		$tabs        = apply_filters( 'wcsn_tools_tabs', $tabs );
		$tab_ids     = array_keys( $tabs );
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : reset( $tab_ids );
		$page        = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';

		Admin::view(
			'html-tools.php',
			array(
				'tabs'        => $tabs,
				'current_tab' => $current_tab,
				'page'        => $page,
			)
		);
	}

	/**
	 * Redirect to pro page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function go_pro_redirect() {
		if ( isset( $_GET['page'] ) && 'go_wcsn_pro' === $_GET['page'] ) {
			wp_redirect( 'https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=admin-menu&utm_medium=link&utm_campaign=upgrade&utm_id=wc-serial-numbers' );
			die;
		}
	}

	/**
	 * Debug tab content.
	 *
	 * @since 1.4.6
	 * @return void
	 */
	public static function status_tab() {
		$statuses = array(
			'Serial Numbers version' => wc_serial_numbers()->get_version(),
		);
		if ( wc_serial_numbers()->is_premium_active() && function_exists( 'wc_serial_numbers_pro' ) ) {
			$statuses['Serial Numbers Pro version'] = wc_serial_numbers_pro()->get_version();
		}

		// Check if required tables exist.
		$required_tables = array(
			'serial_numbers',
			'serial_numbers_activations',
		);
		foreach ( $required_tables as $table ) {
			$exists = $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare( 'SHOW TABLES LIKE %s', $GLOBALS['wpdb']->prefix . $table ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( $exists ) {
				$statuses[ $table ] = __( 'Table exists', 'wc-serial-numbers' );
			} else {
				$statuses[ $table ] = __( 'Table does not exist', 'wc-serial-numbers' );
			}
		}

		// Cron jobs.
		$cron_jobs = array(
			'wc_serial_numbers_hourly_event' => __( 'Hourly cron', 'wc-serial-numbers' ),
			'wc_serial_numbers_daily_event'  => __( 'Daily cron', 'wc-serial-numbers' ),
		);
		foreach ( $cron_jobs as $cron_job => $cron_job_name ) {
			$next_scheduled = wp_next_scheduled( $cron_job );
			if ( $next_scheduled ) {
				$statuses[ $cron_job_name ] = sprintf( __( 'Next run: %s', 'wc-serial-numbers' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_scheduled ) );
			} else {
				$statuses[ $cron_job_name ] = __( 'Not scheduled', 'wc-serial-numbers' );
			}
		}
		$statuses = apply_filters( 'wc_serial_numbers_plugin_statuses', $statuses );
		?>
		<table class="widefat wcsn-status" cellspacing="0" id="wcsn-status">
			<thead>
			<tr>
				<th colspan="3" data-export-label="Serial Numbers"><h2><?php esc_html_e( 'Serial Numbers', 'wc-serial-numbers' ); ?></h2></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $statuses as $name => $value ) : ?>
				<tr>
					<td data-export-label="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $name ); ?></td>
					<td class="help">&dash;</td>
					<td><?php echo esc_html( $value ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>

		</table>

		<?php
	}

	/**
	 * Debug tab content.
	 *
	 * @since 1.4.6
	 * @return void
	 */
	public static function api_tab() {
		$args        = array_merge(
			wcsn_get_products_query_args(),
			array(
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			)
		);
		$the_query   = new \WP_Query( $args );
		$product_ids = $the_query->get_posts();
		$products    = array();
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				continue;
			}
			$products[ $product->get_id() ] = sprintf( '%s (#%d)', $product->get_name(), $product->get_id() );
		}
		?>
		<div class="wcsn-card card card__header"><h2><?php esc_html_e( 'Validation Tool', 'wc-serial-numbers' ); ?></h2></div>
		<div class="wcsn-card card">
			<p><?php esc_html_e( 'Here is how you can validate serial numbers for a product.', 'wc-serial-numbers' ); ?></p>
			<ol>
				<li><?php esc_html_e( 'Select a product from the dropdown below.', 'wc-serial-numbers' ); ?></li>
				<li><?php esc_html_e( 'Enter the serial numbers you want to validate in the textarea below.', 'wc-serial-numbers' ); ?></li>
				<li><?php esc_html_e( 'Click on the "Validate" button.', 'wc-serial-numbers' ); ?></li>
				<li><?php esc_html_e( 'You will see the results within the API response box below.', 'wc-serial-numbers' ); ?></li>
			</ol>
			<form class="wcsn-api-form" method="post">
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><label for="product_id"><?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?></label></th>
						<td>
							<select name="product_id" id="product_id" class="wc-enhanced-select">
								<?php foreach ( $products as $product_id => $product_name ) : ?>
									<option value="<?php echo esc_attr( $product_id ); ?>"><?php echo esc_html( $product_name ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Select a product to validate serial numbers for.', 'wc-serial-numbers' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="serial_key"><?php esc_html_e( 'Key', 'wc-serial-numbers' ); ?></label></th>
						<td>
							<textarea name="serial_key" id="serial_key" rows="5" cols="50" placeholder="<?php esc_attr_e( 'Please enter serial numbers to validate', 'wc-serial-numbers' ); ?>"></textarea>
							<p class="description">
								<?php esc_html_e( 'Required field. Enter serial numbers to validate.', 'wc-serial-numbers' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="email"><?php esc_html_e( 'Email', 'wc-serial-numbers' ); ?></label></th>
						<td>
							<input type="email" name="email" id="email" placeholder="<?php esc_attr_e( 'Please enter a valid email address', 'wc-serial-numbers' ); ?>">
							<p class="description">
								<?php esc_html_e( 'Optional field. If email is provided, only serial numbers that are assigned to the email will be validated otherwise ignored.', 'wc-serial-numbers' ); ?>
							</p>
						</td>
					</tr>

					<!--Show the JSON API response output nicely in a nice format-->
					<tr>
						<th scope="row"><label><?php esc_html_e( 'API Response', 'wc-serial-numbers' ); ?></label></th>
						<td class="code">
							<pre><span class="wcsn-api-response">&mdash;</span></pre>
						</td>
					</tbody>

					<tfoot>
					<tr>
						<th scope="row"></th>
						<td>
							<input type="hidden" name="request" value="validate">
							<?php submit_button( __( 'Validate', 'wc-serial-numbers' ), 'primary', 'submit', false ); ?>
						</td>
					</tr>
				</table>
			</form>
		</div>
		<div class="wcsn-card card card__header"><h2><?php esc_html_e( 'Activation/Deactivation Tool', 'wc-serial-numbers' ); ?></h2></div>
		<div class="wcsn-card card">
			<p><?php esc_html_e( 'Here is how you can activate/deactivate serial numbers for a product.', 'wc-serial-numbers' ); ?></p>
			<ol>
				<li><?php esc_html_e( 'Select a product from the dropdown below.', 'wc-serial-numbers' ); ?></li>
				<li><?php esc_html_e( 'Enter the serial numbers you want to activate/deactivate in the textarea below.', 'wc-serial-numbers' ); ?></li>
				<li><?php esc_html_e( 'Enter the instance you want to activate/deactivate in the textarea below.', 'wc-serial-numbers' ); ?></li>
				<li><?php esc_html_e( 'Click on the "Activate/Deactivate" button.', 'wc-serial-numbers' ); ?></li>
				<li><?php esc_html_e( 'You will see the results within the API response box below.', 'wc-serial-numbers' ); ?></li>
			</ol>
			<form class="wcsn-api-form" method="post">
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><label for="product_id"><?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?></label></th>
						<td>
							<select name="product_id" id="product_id" class="wc-enhanced-select">
								<?php foreach ( $products as $product_id => $product_name ) : ?>
									<option value="<?php echo esc_attr( $product_id ); ?>"><?php echo esc_html( $product_name ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Select a product to activate/deactivate serial numbers for.', 'wc-serial-numbers' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="serial_key"><?php esc_html_e( 'Key', 'wc-serial-numbers' ); ?></label></th>
						<td>
							<textarea name="serial_key" id="serial_key" rows="5" cols="50" placeholder="<?php esc_attr_e( 'Please enter serial numbers to activate/deactivate', 'wc-serial-numbers' ); ?>" required></textarea>
							<p class="description">
								<?php esc_html_e( 'Required field. Enter serial numbers to activate/deactivate.', 'wc-serial-numbers' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="instance"><?php esc_html_e( 'Instance', 'wc-serial-numbers' ); ?></label></th>
						<td>
							<input type="text" name="instance" id="instance" placeholder="<?php esc_attr_e( 'Please enter a unique instance', 'wc-serial-numbers' ); ?>" value="<?php echo esc_attr( time() ); ?>" required>
							<p class="description">
								<?php esc_html_e( 'Required field. Instance is the unique identifier of the activation record. It is used to identify the activation when activating/deactivating serial numbers.', 'wc-serial-numbers' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="email"><?php esc_html_e( 'Email', 'wc-serial-numbers' ); ?></label></th>
						<td>
							<input type="email" name="email" id="email" placeholder="<?php esc_attr_e( 'Please enter a valid email address', 'wc-serial-numbers' ); ?>">
							<p class="description">
								<?php esc_html_e( 'Optional field. If email is provided, only serial numbers that are assigned to the email will be activated/deactivated otherwise ignored.', 'wc-serial-numbers' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="request"><?php esc_html_e( 'Action', 'wc-serial-numbers' ); ?></label></th>
						<td>
							<select name="request" id="request">
								<option value="activate"><?php esc_html_e( 'Activate', 'wc-serial-numbers' ); ?></option>
								<option value="deactivate"><?php esc_html_e( 'Deactivate', 'wc-serial-numbers' ); ?></option>
							</select>
							<p class="description">
								<?php esc_html_e( 'Select an action to perform on the serial numbers.', 'wc-serial-numbers' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'API Response', 'wc-serial-numbers' ); ?></label></th>
						<td class="code">
							<pre><span class="wcsn-api-response">&mdash;</span></pre>
						</td>
					</tbody>

					<tfoot>
					<tr>
						<th scope="row"></th>
						<td>
							<?php submit_button( __( 'Submit', 'wc-serial-numbers' ), 'primary', 'submit', false ); ?>
						</td>
					</tr>
				</table>
			</form>
		</div>
		<?php
	}

	/**
	 * CSV import.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function csv_file_import() {
		?>
		<div class="wcsn-card">
			<div class="wcsn-card__header"><h3><?php esc_html_e( 'CSV Import', 'wc-serial-numbers' ); ?></h3></div>
			<div class="wcsn-card__body">
				<form>
					<p>
						<?php echo sprintf( __( 'Upload a csv file containing serial number to import the serial numbers. Download %1$s sample file %2$s to learn how to format your csv file', 'wc-serial-numbers-pro' ), '<a target="_blank" href="' . wc_serial_numbers_pro()->plugin_url() . '/data/sample.csv' . '">', '</a>' ); ?>
					</p>
					<h3>List of Fields</h3>
					<ul>
						<li><strong>'sliced_title'</strong> - the title of the quote or invoice (required)  </li>
						<li><strong>'sliced_description'</strong> - the description of the quote or invoice   </li>
						<li><strong>'sliced_author_id'</strong> - the id of the author. Leave blank for current user</li>
						<li><strong>'sliced_number'</strong> - invoice or quote number. Leave blank if auto increment is turned on</li>
						<li><strong>'sliced_created'</strong> - created date. Leave blank for today</li>
						<li><strong>'sliced_due'</strong> - invoice due date</li>
						<li><strong>'sliced_valid'</strong> - quote valid until date</li>
						<li><strong>'sliced_items'</strong> - individual line items</li>
						<li><strong>'sliced_status'</strong> - invoice or quote status. ie sent, unpaid, paid, overdue. Defaults to Draft if left blank</li>
						<li><strong>'sliced_client_email'</strong> - email of the client (required)</li>
						<li><strong>'sliced_client_name'</strong> - name of the client (only use if client does not already exist)</li>
						<li><strong>'sliced_client_business'</strong> - clients business name</li>
						<li><strong>'sliced_client_address'</strong> - clients adress</li>
						<li><strong>'sliced_client_extra'</strong> - clients extra info (phone number, business number etc)</li>
					</ul>
					<table class="form-table">
						<tbody>
						<tr>
							<th scope="row"><label for="csv_file"><?php esc_html_e( 'CSV File', 'wc-serial-numbers' ); ?></label></th>
							<td>
								<input type="file" name="csv_file" id="csv_file" required>
								<p class="description">
									<?php esc_html_e( 'Required field. Select a csv file to import serial numbers.', 'wc-serial-numbers' ); ?>
								</p>
							</td>
						</tr>
						</tbody>
						<tfoot>
						<tr>
							<th scope="row"></th>
							<td>
								<?php submit_button( __( 'Import', 'wc-serial-numbers' ), 'primary', 'submit', false ); ?>
							</td>
						</tr>
						</tfoot>
					</table>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Text file import.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function text_file_import() {
		?>
		<div class="wcsn-card">
			<div class="wcsn-card__header"><h3><?php esc_html_e( 'Text File Export', 'wc-serial-numbers' ); ?></h3></div>
			<div class="wcsn-card__body">
				<form>
					<p>
						<?php esc_html_e( 'Export serial numbers to a text file. You can use this file to import serial numbers to another site.', 'wc-serial-numbers' ); ?>
					</p>
					<table class="form-table">
						<tbody>
						<tr>
							<th scope="row"><label for="product_id"><?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?></label></th>
							<td>
								<select name="product_id" id="product_id">
									<option value=""><?php esc_html_e( 'All Products', 'wc-serial-numbers' ); ?></option>
									<?php
									$products = wc_serial_numbers()->get_products();
									foreach ( $products as $product ) :
										?>
										<option value="<?php echo esc_attr( $product->ID ); ?>"><?php echo esc_html( $product->post_title ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description">
									<?php esc_html_e( 'Select a product to export serial numbers for.', 'wc-serial-numbers' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="status"><?php esc_html_e( 'Status', 'wc-serial-numbers' ); ?></label></th>
							<td>
								<select name="status" id="status">
									<option value=""><?php esc_html_e( 'All Statuses', 'wc-serial-numbers' ); ?></option>
									<?php
									$statuses = wc_serial_numbers()->get_statuses();
									foreach ( $statuses as $status => $label ) :
										?>
										<option value="<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description">
									<?php esc_html_e( 'Select a status to export serial numbers for.', 'wc-serial-numbers' ); ?>
								</p>
							</td>
						</tr>
						</tbody>
						<tfoot>
						<tr>
							<th scope="row"></th>
							<td>
								<?php submit_button( __( 'Export', 'wc-serial-numbers' ), 'primary', 'submit', false ); ?>
							</td>
						</tr>
						</tfoot>
					</table>
				</form>
			</div>
		</div>
		<?php
	}
}
