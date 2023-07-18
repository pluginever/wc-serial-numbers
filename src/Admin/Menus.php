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
	 * Menus constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		// add_action( 'current_screen', array( $this, 'setup_screen' ) );
		// add_action( 'check_ajax_referer', array( $this, 'setup_screen' ) );
		// add_filter( 'set-screen-option', array( __CLASS__, 'save_screen_options' ), 10, 3 );

		// Register the menus.
		add_action( 'admin_menu', array( $this, 'main_menu' ) );
		add_action( 'admin_menu', array( $this, 'activations_menu' ), 40 );
		add_action( 'admin_menu', array( $this, 'tools_menu' ), 50 );
		add_action( 'admin_menu', array( $this, 'reports_menu' ), 60 );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 100 );
		add_action( 'admin_menu', array( $this, 'promo_menu' ), PHP_INT_MAX );

		// Keys page.
		add_action( 'wc_serial_numbers_keys_content', array( __CLASS__, 'output_keys_content' ) );
		add_action( 'wc_serial_numbers_activations_content', array( __CLASS__, 'output_activations_content' ) );

		// Add tabs content.
		add_action( 'wc_serial_numbers_tools_import_content', array( __CLASS__, 'import_tab' ) );
		add_action( 'wc_serial_numbers_tools_export_content', array( __CLASS__, 'export_tab' ) );
		add_action( 'wc_serial_numbers_tools_generators_content', array( __CLASS__, 'generators_tab' ) );
		add_action( 'wc_serial_numbers_tools_status_content', array( __CLASS__, 'status_tab' ) );
		add_action( 'wc_serial_numbers_tools_api_content', array( __CLASS__, 'api_validation_section' ) );
		add_action( 'wc_serial_numbers_tools_api_content', array( __CLASS__, 'api_activation_deactivation_section' ) );
		add_action( 'wc_serial_numbers_reports_stock_content', array( __CLASS__, 'stock_report_content' ) );
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
		if ( in_array( $option, array(
			'wsn_keys_per_page',
			'wsn_generators_per_page',
			'wsn_activations_per_page'
		), true ) ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Add menu.
	 *
	 * @return void
	 * @since 1.0.0
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
	 * @return void
	 * @since 1.0.0
	 */
	public function activations_menu() {
		if ( ! wcsn_is_software_support_enabled() ) {
			return;
		}
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
	 * @return void
	 * @since 1.0.0
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
	 * Add reports menu.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function reports_menu() {
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Reports', 'wc-serial-numbers' ),
			__( 'Reports', 'wc-serial-numbers' ),
			wcsn_get_manager_role(),
			'wc-serial-numbers-reports',
			array( $this, 'output_reports_page' )
		);
	}

	/**
	 * Settings menu.
	 *
	 * @return void
	 * @since 1.0.0
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
	 * @return void
	 * @since 1.0.0
	 */
	public function promo_menu() {
		$role = wcsn_get_manager_role();
		if ( ! wc_serial_numbers()->is_premium_active() ) {
			add_submenu_page(
				'wc-serial-numbers',
				'',
				'<span style="color:#05ef82;"><span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . __( 'Upgrade to Pro', 'wc-serial-numbers' ) . '</span>',
				$role,
				'go_wcsn_pro',
				array( $this, 'go_pro_redirect' )
			);
		}
	}

	/**
	 * Output keys page.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function output_main_page() {
		$page_hook = 'keys';
		include dirname( __FILE__ ) . '/views/admin-page.php';
	}

	/**
	 * Output activations page.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function output_activations_page() {
		$page_hook = 'activations';
		include dirname( __FILE__ ) . '/views/admin-page.php';
	}


	/**
	 * Output tools page.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function output_tools_page() {
		$tabs = array(
			'generators' => __( 'Generators', 'wc-serial-numbers' ),
			'api'        => __( 'API', 'wc-serial-numbers' ),
			'import'     => __( 'Import', 'wc-serial-numbers' ),
			'export'     => __( 'Export', 'wc-serial-numbers' ),
			'status'     => __( 'Status', 'wc-serial-numbers' ),
		);

		// If software support is disabled, remove the activations tab.
		if ( ! wcsn_is_software_support_enabled() ) {
			unset( $tabs['api'] );
		}

		$page_hook = 'tools';
		include dirname( __FILE__ ) . '/views/admin-page.php';
	}

	/**
	 * Output reports page.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function output_reports_page() {
		$tabs = array(
			'stock' => __( 'Stock', 'wc-serial-numbers' ),
		);

		$page_hook = 'reports';
		include dirname( __FILE__ ) . '/views/admin-page.php';
	}

	/**
	 * Redirect to pro page.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function go_pro_redirect() {
		if ( isset( $_GET['page'] ) && 'go_wcsn_pro' === $_GET['page'] ) {
			wp_redirect( 'https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=admin-menu&utm_medium=link&utm_campaign=upgrade&utm_id=wc-serial-numbers' );
			die;
		}
	}

	/**
	 * Output keys content.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function output_keys_content() {
		if ( isset( $_GET['add'] ) || isset( $_GET['edit'] ) ) {
			$id  = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
			$key = new Key( $id );
			if ( ! empty( $id ) && ! $key->exists() ) {
				wp_safe_redirect( remove_query_arg( 'edit' ) );
				exit();
			}
			include dirname( __FILE__ ) . '/views/edit-key.php';
		} else {
			include dirname( __FILE__ ) . '/views/list-key.php';
		}
	}

	/**
	 * Output activations content.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function output_activations_content() {
		include dirname( __FILE__ ) . '/views/list-activation.php';
	}

	/**
	 * Add status tab.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @return array
	 */
	public static function add_tools_status_tab( $tabs ) {
		$tabs['status'] = __( 'Status', 'wc-serial-numbers' );

		return $tabs;
	}

	/**
	 * Import tab content.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function import_tab() {
		?>
		<div class="wcsn-feature-promo-banner">
			<div class="wcsn-feature-promo-banner__content">
				<h3><?php esc_html_e( 'Available in Pro Version', 'wc-serial-numbers' ); ?></h3>
				<a href="https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=import-tab&utm_medium=link&utm_campaign=upgrade&utm_id=wc-serial-numbers"
				   target="_blank"
				   class="button-primary"><?php esc_html_e( 'Upgrade to Pro Now', 'wc-serial-numbers' ); ?></a>
			</div>
			<img src="<?php echo esc_url( wc_serial_numbers()->get_url() . 'assets/images/csv-import.png' ); ?>"
				 alt="<?php esc_attr_e( 'Import Serial Numbers', 'wc-serial-numbers' ); ?>"/>
		</div>
		<div class="wcsn-feature-promo-banner">
			<div class="wcsn-feature-promo-banner__content">
				<h3><?php esc_html_e( 'Available in Pro Version', 'wc-serial-numbers' ); ?></h3>
				<a href="https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=import-tab&utm_medium=link&utm_campaign=upgrade&utm_id=wc-serial-numbers"
				   target="_blank"
				   class="button-primary"><?php esc_html_e( 'Upgrade to Pro Now', 'wc-serial-numbers' ); ?></a>
			</div>
			<img src="<?php echo esc_url( wc_serial_numbers()->get_assets_url() . 'images/txt-import.png' ); ?>"
				 alt="<?php esc_attr_e( 'Import Serial Numbers', 'wc-serial-numbers' ); ?>"/>
		</div>
		<?php
	}

	/**
	 * Export tab content.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function export_tab() {
		?>
		<div class="wcsn-feature-promo-banner">
			<div class="wcsn-feature-promo-banner__content">
				<h3><?php esc_html_e( 'Available in Pro Version', 'wc-serial-numbers' ); ?></h3>
				<a href="https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=export-tab&utm_medium=link&utm_campaign=upgrade&utm_id=wc-serial-numbers"
				   target="_blank"
				   class="button-primary"><?php esc_html_e( 'Upgrade to Pro Now', 'wc-serial-numbers' ); ?></a>
			</div>
			<img src="<?php echo esc_url( wc_serial_numbers()->get_assets_url() . 'images/csv-export.png' ); ?>"
				 alt="<?php esc_attr_e( 'Export Serial Numbers', 'wc-serial-numbers' ); ?>"/>
		</div>
		<?php
	}

	/**
	 * Getnerators tab content.
	 *
	 * @return void
	 * @since 1.4.6
	 */
	public static function generators_tab() {
		?>
		<div class="wcsn-feature-promo-banner">
			<div class="wcsn-feature-promo-banner__content">
				<h3><?php esc_html_e( 'Available in Pro Version', 'wc-serial-numbers' ); ?></h3>
				<a href="https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=generators-tab&utm_medium=link&utm_campaign=upgrade&utm_id=wc-serial-numbers"
				   target="_blank"
				   class="button-primary"><?php esc_html_e( 'Upgrade to Pro Now', 'wc-serial-numbers' ); ?></a>
			</div>
			<img src="<?php echo esc_url( wc_serial_numbers()->get_assets_url() . 'images/add-generator.png' ); ?>"
				 alt="<?php esc_attr_e( 'Generators', 'wc-serial-numbers' ); ?>"/>
		</div>
		<?php
	}

	/**
	 * Debug tab content.
	 *
	 * @return void
	 * @since 1.4.6
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
		<div class="pev-card">
			<div class="pev-card__header">
				<h2><?php esc_html_e( 'Plugin Status', 'wc-serial-numbers' ); ?></h2>
			</div>
			<div class="pev-card__body">
				<table class="widefat striped fixed" cellspacing="0">
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
			</div>
		</div>
		<?php
	}

	/**
	 * Validation section.
	 *
	 * @return void
	 * @since 1.4.6
	 */
	public static function api_validation_section() {
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

		Admin::view( 'html-api-validation', array( 'products' => $products ) );
	}

	/**
	 * Activation deactivation section.
	 *
	 * @return void
	 * @since 1.4.6
	 */
	public static function api_activation_deactivation_section() {
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

		Admin::view( 'html-api-actions', array( 'products' => $products ) );
	}

	/**
	 * Stock section.
	 *
	 * @return void
	 * @since 1.4.6
	 */
	public static function stock_report_content() {
		include dirname( __FILE__ ) . '/views/stock-report.php';
	}
}
