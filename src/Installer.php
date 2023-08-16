<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Installer.
 *
 * @since   1.4.2
 * @package WooCommerceSerialNumbers
 */
class Installer {

	/**
	 * Update callbacks.
	 *
	 * @since 1.4.2
	 * @var array
	 */
	protected $updates = array(
		'1.1.2' => 'update_112',
		'1.2.0' => 'update_120',
		'1.2.1' => 'update_121',
		'1.4.6' => 'update_146',
		'1.5.6' => 'update_156',
	);

	/**
	 * Installer constructor.
	 *
	 * @since 1.4.2
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( __CLASS__, 'custom_cron_schedules' ), 20 ); // phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval
		add_action( 'init', array( $this, 'check_update' ), 0 );
	}

	/**
	 * Add custom cron schedule
	 *
	 * @param array $schedules list of cron schedules.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function custom_cron_schedules( $schedules ) {
		$schedules ['once_a_minute'] = array(
			'interval' => 60,
			'display'  => esc_html__( 'Once a Minute', 'wc-serial-numbers' ),
		);

		return $schedules;
	}

	/**
	 * Check the plugin version and run the updater if necessary.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 *
	 * @since 1.4.2
	 * @return void
	 */
	public function check_update() {
		$db_version      = WCSN()->get_db_version();
		$current_version = WCSN()->get_version();
		$requires_update = version_compare( $db_version, $current_version, '<' );
		$can_install     = ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! defined( 'IFRAME_REQUEST' );
		if ( $can_install && $requires_update ) {
			static::install();

			$update_versions = array_keys( $this->updates );
			usort( $update_versions, 'version_compare' );
			if ( ! is_null( $db_version ) && version_compare( $db_version, end( $update_versions ), '<' ) ) {
				$this->update();
			} else {
				WCSN()->update_db_version( $current_version );
			}
		}
	}

	/**
	 * Update the plugin.
	 *
	 * @since 1.4.2
	 * @return void
	 */
	public function update() {
		$db_version = WCSN()->get_db_version();
		foreach ( $this->updates as $version => $callbacks ) {
			$callbacks = (array) $callbacks;
			if ( version_compare( $db_version, $version, '<' ) ) {
				foreach ( $callbacks as $callback ) {
					WCSN()->log( sprintf( 'Updating to %s from %s', $version, $db_version ) );
					// if the callback return false then we need to update the db version.
					$continue = call_user_func( array( $this, $callback ) );
					if ( ! $continue ) {
						WCSN()->update_db_version( $version );
						$notice = sprintf(
						/* translators: 1: plugin name 2: version number */
							__( '%1$s updated to version %2$s successfully.', 'wc-serial-numbers' ),
							'<strong>Serial Numbers for WooCommerce</strong>',
							'<strong>' . $version . '</strong>'
						);
						WCSN()->add_notice( $notice, 'success' );
					}
				}
			}
		}
	}

	/**
	 * Install the plugin.
	 *
	 * @since 1.4.2
	 * @return void
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}

		self::create_tables();
		self::create_cron_jobs();
		Admin\Settings::get_instance()->save_defaults();
		WCSN()->update_db_version( WCSN()->get_version(), false );
		add_option( 'wc_serial_numbers_install_date', current_time( 'mysql' ) );
		set_transient( 'wc_serial_numbers_activated', true, 30 );
		set_transient( 'wc_serial_numbers_activation_redirect', true, 30 );
	}

	/**
	 * Create tables.
	 *
	 * @return void
	 */
	public static function create_tables() {
		global $wpdb;
		$wpdb->hide_errors();
		// todo rename table names to wcsn_keys and wcsn_activations.
		$tables = [
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}serial_numbers(
         	id bigint(20) NOT NULL AUTO_INCREMENT,
			serial_key longtext DEFAULT NULL,
			product_id bigint(20) NOT NULL,
			activation_limit int(9) NOT NULL DEFAULT 0,
			activation_count int(9) NOT NULL  DEFAULT 0,
			order_id bigint(20) DEFAULT NULL,
    		order_item_id bigint(20) DEFAULT NULL,
			vendor_id bigint(20) DEFAULT NULL,
			status varchar(50) DEFAULT 'available',
			validity varchar(200) DEFAULT NULL,
			expire_date DATETIME NULL DEFAULT NULL,
			order_date DATETIME NULL DEFAULT NULL,
    		uuid varchar(50) DEFAULT NULL,
			source varchar(50) DEFAULT 'custom_source',
			created_date DATETIME NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			key product_id (product_id),
			key order_id (order_id),
			key vendor_id (vendor_id),
			key activation_limit (activation_limit),
    		KEY order_item_id (order_item_id),
    		UNIQUE KEY uuid (uuid),
			key status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ",
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}serial_numbers_activations(
			  id bigint(20) NOT NULL auto_increment,
			  serial_id bigint(20) NOT NULL,
			  instance varchar(200) NOT NULL,
			  platform varchar(200) DEFAULT NULL,
			  activation_time DATETIME NULL DEFAULT NULL,
			  PRIMARY KEY  (id),
			  key serial_id (serial_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		];

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $tables as $table ) {
			dbDelta( $table );
		}
	}

	/**
	 * Create cron jobs (clear them first).
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function create_cron_jobs() {
		// setup transient actions.
		if ( false === wp_next_scheduled( 'wc_serial_numbers_hourly_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'wc_serial_numbers_hourly_event' );
		}

		if ( false === wp_next_scheduled( 'wc_serial_numbers_daily_event' ) ) {
			wp_schedule_event( time(), 'daily', 'wc_serial_numbers_daily_event' );
		}
	}

	/**
	 * Update to version 1.1.2
	 *
	 * @since 1.1.2
	 * @return void
	 */
	protected function update_112() {
		global $wpdb;
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers ADD KEY product_id(`product_id`)" );
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers ADD KEY order_id (`order_id`)" );
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers ADD KEY status (`status`)" );

		$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers CHANGE expire_date expire_date DATETIME DEFAULT NULL" );
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers CHANGE order_date order_date DATETIME DEFAULT NULL" );
		$wpdb->query( "UPDATE {$wpdb->prefix}wcsn_serial_numbers  set expire_date=NULL WHERE expire_date='0000-00-00 00:00:00'" );
		$wpdb->query( "UPDATE {$wpdb->prefix}wcsn_serial_numbers  set order_date=NULL WHERE order_date='0000-00-00 00:00:00'" );

		$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_activations ADD KEY serial_id (`serial_id`)" );
	}

	/**
	 * Update to version 1.2.0
	 *
	 * @since 1.2.0
	 * @return void
	 */
	protected function update_120() {
		wp_clear_scheduled_hook( 'wcsn_per_minute_event' );
		wp_clear_scheduled_hook( 'wcsn_daily_event' );
		wp_clear_scheduled_hook( 'wcsn_hourly_event' );

		if ( ! wp_next_scheduled( 'wc_serial_numbers_hourly_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'wc_serial_numbers_hourly_event' );
		}

		if ( ! wp_next_scheduled( 'wc_serial_numbers_daily_event' ) ) {
			wp_schedule_event( time(), 'daily', 'wc_serial_numbers_daily_event' );
		}

		global $wpdb;
		$prefix = $wpdb->prefix;
		$wpdb->query( "RENAME TABLE `{$prefix}wcsn_serial_numbers` TO `{$prefix}serial_numbers`" );
		$wpdb->query( "RENAME TABLE `{$prefix}wcsn_activations` TO `{$prefix}serial_numbers_activations`" );

		$wpdb->query( "ALTER TABLE {$prefix}serial_numbers DROP COLUMN `serial_image`;" );
		$wpdb->query( "ALTER TABLE {$prefix}serial_numbers DROP COLUMN `activation_email`;" );
		$wpdb->query( "ALTER TABLE {$prefix}serial_numbers CHANGE `created` `created_date` DATETIME NULL DEFAULT NULL;" );
		$wpdb->query( "ALTER TABLE {$prefix}serial_numbers ADD vendor_id bigint(20) NOT NULL DEFAULT 0" );
		$wpdb->query( "ALTER TABLE {$prefix}serial_numbers ADD activation_count int(9) NOT NULL  DEFAULT 0" );
		$wpdb->query( "ALTER TABLE {$prefix}serial_numbers ADD KEY vendor_id(`vendor_id`)" );
		$wpdb->query( "ALTER TABLE {$prefix}serial_numbers ADD source varchar(200) NOT NULL default 'custom_source'" );
		$wpdb->query( "ALTER TABLE {$prefix}serial_numbers_activations CHANGE platform platform varchar(200) DEFAULT NULL" );
		// status update
		$wpdb->query( $wpdb->prepare( "UPDATE {$prefix}serial_numbers set status=%s WHERE status=%s AND order_id=0", 'available', 'new' ) );
		$wpdb->query( $wpdb->prepare( "UPDATE {$prefix}serial_numbers set status=%s WHERE status=%s AND order_id != 0", 'sold', 'active' ) );
		$wpdb->query( $wpdb->prepare( "UPDATE {$prefix}serial_numbers set status=%s WHERE status=%s", 'cancelled', 'pending' ) );
		$wpdb->query( $wpdb->prepare( "UPDATE {$prefix}serial_numbers set status=%s WHERE status=%s", 'cancelled', 'rejected' ) );
		global $current_user;
		if ( ! empty( $current_user->ID ) && current_user_can( 'manage_options' ) ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}serial_numbers set vendor_id=%d", $current_user->ID ) );
		}

		$activations = $wpdb->get_results( "select serial_id, count(id) as active_count from  {$wpdb->prefix}serial_numbers_activations where active='1' GROUP BY serial_id" );
		foreach ( $activations as $activation ) {
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}serial_numbers SET activation_count = %d WHERE id=%d", intval( $activation->active_count ), intval( $activation->serial_id ) ) );
		}

		$wpdb->query( "UPDATE {$wpdb->prefix}serial_numbers set status='available', order_date='0000-00-00 00:00:00', order_id='0' WHERE status !='available' AND order_id='0' AND expire_date='0000-00-00 00:00:00'" );

		// settings update
		$heading_text          = $this->update_1_2_0_get_option( 'heading_text', 'Serial Numbers', 'wsn_delivery_settings' );
		$serial_col_heading    = $this->update_1_2_0_get_option( 'table_column_heading', 'Serial Number', 'wsn_delivery_settings' );
		$serial_key_label      = $this->update_1_2_0_get_option( 'serial_key_label', 'Serial Number', 'wsn_delivery_settings' );
		$serial_email_label    = $this->update_1_2_0_get_option( 'serial_email_label', 'Activation Email', 'wsn_delivery_settings' );
		$show_validity         = 'yes' === $this->update_1_2_0_get_option( 'show_validity', 'yes', 'wsn_delivery_settings' );
		$show_activation_limit = 'yes' === $this->update_1_2_0_get_option( 'show_activation_limit', 'yes', 'wsn_delivery_settings' );
		$license               = get_option( 'woocommerce_serial_numbers_pro_pluginever_license' );
		$options               = [
			'wc_serial_numbers_autocomplete_order'            => $this->update_1_2_0_get_option( 'wsn_auto_complete_order', 'yes', 'wsn_delivery_settings' ),
			'wc_serial_numbers_reuse_serial_number'           => $this->update_1_2_0_get_option( 'wsn_re_use_serial', 'no', 'wsn_delivery_settings' ),
			'wc_serial_numbers_disable_encryption'            => 'no',
			'wc_serial_numbers_disable_software_support'      => 'no',
			'wc_serial_numbers_manual_delivery'               => 'no',
			'wc_serial_numbers_hide_serial_number'            => 'yes',
			'wc_serial_numbers_revoke_status_cancelled'       => in_array( 'cancelled', $this->update_1_2_0_get_option( 'wsn_revoke_serial_number', [], 'wsn_delivery_settings' ), true ) ? 'yes' : 'no',
			'wc_serial_numbers_revoke_status_refunded'        => in_array( 'refunded', $this->update_1_2_0_get_option( 'wsn_revoke_serial_number', [], 'wsn_delivery_settings' ), true ) ? 'yes' : 'no',
			'wc_serial_numbers_revoke_status_failed'          => in_array( 'failed', $this->update_1_2_0_get_option( 'wsn_revoke_serial_number', [], 'wsn_delivery_settings' ), true ) ? 'yes' : 'no',
			'wc_serial_numbers_enable_stock_notification'     => $this->update_1_2_0_get_option( 'wsn_admin_bar_notification_send_email', 'yes', 'wsn_notification_settings' ),
			'wc_serial_numbers_stock_threshold'               => $this->update_1_2_0_get_option( 'wsn_admin_bar_notification_number', '5', 'wsn_notification_settings' ),
			'wc_serial_numbers_notification_recipient'        => $this->update_1_2_0_get_option( 'wsn_admin_bar_notification_email', get_option( 'admin_email' ), 'wsn_notification_settings' ),
			'wc_serial_numbers_order_table_heading'           => $heading_text,
			'wc_serial_numbers_order_table_col_product_label' => 'Product',
			'wc_serial_numbers_order_table_col_key_label'     => $serial_key_label,
			'wc_serial_numbers_order_table_col_email_label'   => $serial_email_label,
			'wc_serial_numbers_order_table_col_limit_label'   => 'Activation Limit',
			'wc_serial_numbers_order_table_col_expires_label' => 'Expire Date',
			'wc_serial_numbers_order_table_col_product'       => 'yes',
			'wc_serial_numbers_order_table_col_key'           => 'yes',
			'wc_serial_numbers_order_table_col_email'         => 'no',
			'wc_serial_numbers_order_table_col_limit'         => $show_activation_limit ? 'yes' : 'no',
			'wc_serial_numbers_order_table_col_expires'       => $show_validity ? 'yes' : 'no',
			'wc_serial_numbers_install_time'                  => get_option( 'woocommerceserialnumbers_install_time' ),
			'woocommerce-serial-numbers-pro_license_key'      => array_key_exists( 'key', $license ) ? $license['key'] : '',
			'woocommerce-serial-numbers-pro_license_status'   => array_key_exists( 'license', $license ) ? $license['license'] : '',
		];
		foreach ( $options as $key => $option ) {
			add_option( $key, $option );
		}
	}

	/**
	 * Get option from old settings.
	 *
	 * @param string $option_name Option name.
	 * @param string $default Default value.
	 * @param string $section Section name.
	 *
	 * @return string
	 */
	protected function update_1_2_0_get_option( $option_name, $default, $section = 'serial_numbers_settings' ) {
		$settings = get_option( $section, [] );

		return ! empty( $settings[ $option_name ] ) ? $settings[ $option_name ] : $default;
	}

	/**
	 * Update to version 1.2.1
	 *
	 * @since 1.2.1
	 * @return void
	 */
	protected function update_121() {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$wpdb->query( "ALTER TABLE {$prefix}serial_numbers CHANGE order_id order_id bigint(20) DEFAULT NULL" );
		$wpdb->query( "ALTER TABLE {$prefix}serial_numbers CHANGE vendor_id vendor_id bigint(20) DEFAULT NULL" );
	}

	/**
	 * Update to version 1.4.6
	 *
	 * @since 1.4.6
	 * @return void
	 */
	protected function update_146() {
		global $wpdb;
		// Update key status default value to 'available'.
		// Change key status.
		// Drop expired column.
		$statuses_map = [
			'refunded' => 'cancelled',
			'expired'  => 'expired',
			'failed'   => 'cancelled',
			'inactive' => 'sold',
		];
		$prefix       = $wpdb->prefix;
		foreach ( $statuses_map as $old_status => $new_status ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$prefix}serial_numbers SET status = %s WHERE status = %s", $new_status, $old_status ) );
		}
		$wpdb->query( "ALTER TABLE {$prefix}serial_numbers DROP expire_date" );

		// Remove all inactive activations.
		$wpdb->query( "DELETE FROM {$prefix}serial_numbers_activations WHERE active = 0" );
		// Drop active column.
		$wpdb->query( "ALTER TABLE {$prefix}serial_numbers_activations DROP active" );
	}

	/**
	 * Update to version 1.5.6
	 *
	 * @since 1.5.6
	 * @return bool
	 */
	protected function update_156() {
		global $wpdb;
		$prefix = $wpdb->prefix;
		// if order_item_id column not exist then add it.
		if ( ! $wpdb->get_var( "SHOW COLUMNS FROM {$prefix}serial_numbers LIKE 'order_item_id'" ) ) {
			$wpdb->query( "ALTER TABLE {$prefix}serial_numbers ADD order_item_id bigint(20) DEFAULT NULL AFTER order_id" );
			$wpdb->query( "ALTER TABLE {$prefix}serial_numbers ADD KEY order_item_id (order_item_id)" );
		}

		// if uuid column not exist then add it.
		if ( ! $wpdb->get_var( "SHOW COLUMNS FROM {$prefix}serial_numbers LIKE 'uuid'" ) ) {
			$wpdb->query( "ALTER TABLE {$prefix}serial_numbers ADD uuid varchar(50) DEFAULT NULL AFTER order_item_id" );
			$wpdb->query( "ALTER TABLE {$prefix}serial_numbers ADD UNIQUE KEY  uuid (uuid)" );
			$wpdb->query( "UPDATE {$prefix}serial_numbers SET uuid = UUID()" );
		}

	}
}
