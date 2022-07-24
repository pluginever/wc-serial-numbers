<?php

namespace PluginEver\WooCommerceSerialNumbers;

use PluginEver\WooCommerceSerialNumbers\Admin\Admin_Settings;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Installation.
 *
 * @since 1.0.0
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Install {

	/**
	 * DB updates and callbacks that need to be run per version.
	 *
	 * @var array
	 */
	private static $updates = array(
//		'1.0.1' => 'update-1.0.1.php',
//		'1.0.6' => 'update-1.0.6.php',
//		'1.0.8' => 'update-1.0.8.php',
//		'1.1.2' => 'update-1.1.2.php',
//		'1.2.0' => 'update-1.2.0.php',
//		'1.2.1' => 'update-1.2.1.php',
	);

	/**
	 * Register hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_install' ), 50 );
		add_action( 'admin_init', array( __CLASS__, 'maybe_update' ) );
		add_action( 'admin_init', [ __CLASS__, 'admin_init' ] );
		add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
	}

	/**
	 * Check plugin version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function maybe_install() {
		if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( self::get_db_version(), Plugin::instance()->get_plugin_version(), '!=' ) ) {
			self::install();
		}
	}

	/**
	 * Perform all the necessary upgrade routines.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function maybe_update() {
		$current_db_version = self::get_db_version();
		if ( ! empty( $current_db_version ) && version_compare( $current_db_version, Plugin::instance()->get_plugin_version(), '<' ) ) {
			self::install();
			$logger = wc_get_logger();
			foreach ( self::$updates as $version => $update_callbacks ) {
				if ( version_compare( $current_db_version, $version, '<' ) ) {
					if ( is_callable( $update_callbacks ) ) {
						$update_callbacks = [ $update_callbacks ];
					}
					foreach ( $update_callbacks as $update_callback ) {
						$logger->info(
							sprintf( 'Queuing %s - %s', $version, $update_callback ),
							array( 'source' => 'wc_serial_numbers' )
						);
						call_user_func( $update_callback );
					}
				}
			}
			self::update_db_version();
		}
	}

	/**
	 * Redirect to settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function admin_init() {
		if ( Plugin::instance()->get_plugin_settings_url() && 'yes' === get_transient( 'wc_serial_numbers_activated' ) ) {
			delete_transient( 'wc_serial_numbers_activated' );
			wp_safe_redirect( Plugin::instance()->get_plugin_settings_url() );
			exit();
		}
	}

	/**
	 * Add custom cron schedule
	 *
	 * @param array $schedules Cron schedules.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function cron_schedules( $schedules ) {
		$schedules ['once_a_minute'] = array(
			'interval' => 60,
			'display'  => __( 'Once a Minute', 'wc-serial-numbers' ),
		);

		return $schedules;
	}

	/**
	 * Install Plugin.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}

		// Set transient.
		set_transient( 'wc_serial_numbers_activated', 'yes', MINUTE_IN_SECONDS * 10 );

		// Use add_option() here to avoid overwriting this value with each
		// plugin version update. We base plugin age off of this value.
		add_option( 'wc_serial_numbers_install_timestamp', time() );
		add_option( 'wc_serial_numbers_version', Plugin::instance()->get_plugin_version() );
		self::create_tables();
		self::create_cron_jobs();
		self::create_options();
	}

	/**
	 * Create tables.
	 *
	 * When adding or removing a table, make sure to update the list of tables in get_tables().
	 *
	 * @return void
	 */
	public static function create_tables() {
		global $wpdb;
		$wpdb->hide_errors();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$max_index_length = 191;
		$collate          = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$tables = [
			"CREATE TABLE {$wpdb->prefix}wcsn_keys(
         	`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`key` longtext DEFAULT NULL,
			`product_id` BIGINT UNSIGNED NOT NULL,
			`order_id` BIGINT UNSIGNED NOT NULL,
			`order_item_id` BIGINT UNSIGNED NOT NULL,
			`vendor_id` BIGINT UNSIGNED NOT NULL,
			`activation_limit` INT(9) NOT NULL DEFAULT 0,
			`activation_count` INT(9) NOT NULL  DEFAULT 0,
			`status` VARCHAR(50) DEFAULT 'available',
			`validity` VARCHAR(200) DEFAULT NULL,
			`hash` longtext DEFAULT NULL,
			`date_expire` DATETIME NULL DEFAULT NULL,
			`date_ordered` DATETIME NULL DEFAULT NULL,
			`date_created` DATETIME NULL DEFAULT NULL,
			PRIMARY KEY  (`id`),
			KEY `product_id` (`product_id`),
			KEY `order_id` (`order_id`),
			KEY `order_item_id` (`order_item_id`),
			KEY `vendor_id` (`vendor_id`),
			KEY `activation_limit` (`activation_limit`),
			KEY `status` (`status`)
            ) $collate;",

			"CREATE TABLE {$wpdb->prefix}wcsn_generators(
			`id` bigint(20) NOT NULL auto_increment,
			`name` VARCHAR(191) NOT NULL,
			`pattern` VARCHAR(32) NOT NULL,
			`is_sequential` INT(1) NOT NULL DEFAULT 1,
			`activation_limit` INT(9) NOT NULL DEFAULT 0,
			`validity` INT(9) DEFAULT NULL,
			`date_expire` VARCHAR(191) NOT NULL,
			`date_created` DATETIME NULL DEFAULT NULL,
			PRIMARY KEY  (`id`),
			KEY `name` (`name`),
			KEY `is_sequential` (`is_sequential`)
			) $collate;",

			"CREATE TABLE {$wpdb->prefix}wcsn_activations(
			`id` bigint(20) NOT NULL auto_increment,
			`key_id` BIGINT UNSIGNED NOT NULL,
			`instance` VARCHAR(200) NOT NULL,
			`is_active` INT(1) NOT NULL DEFAULT 1,
			`platform` VARCHAR(200) DEFAULT NULL,
			`date_activation` DATETIME NULL DEFAULT NULL,
			PRIMARY KEY  (`id`),
			KEY `key_id` (`key_id`),
			KEY `is_active` (`is_active`)
			) $collate; ",
		];

		foreach ( $tables as $table ) {
			dbDelta( $table );
		}
	}

	/**
	 * Create cron jobs.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public static function create_cron_jobs() {
		// setup transient actions
		if ( false === wp_next_scheduled( 'wc_serial_numbers_hourly_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'wc_serial_numbers_hourly_event' );
		}

		if ( false === wp_next_scheduled( 'wc_serial_numbers_daily_event' ) ) {
			wp_schedule_event( time(), 'daily', 'wc_serial_numbers_daily_event' );
		}
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 *
	 * @since 1.0.0
	 */
	private static function create_options() {
		include_once Plugin::instance()->get_plugin_path() . '/includes/admin/class-admin-settings.php';
		$tabs = Admin_Settings::get_tabs();

		foreach ( array_keys( $tabs ) as $tab_id ) {
			$subsections = array_unique( array_merge( array( '' ), array_keys( Admin_Settings::get_sections( $tab_id ) ) ) );
			foreach ( array_keys( $subsections ) as $subsection ) {
				foreach ( Admin_Settings::get_settings_for_tab_section( $tab_id, $subsection ) as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}
	}

	/**
	 * Remove plugin related options.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function uninstall() {
		// placeholder function.
	}

	/**
	 * Gets the currently installed plugin database version.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	protected static function get_db_version() {
		return get_option( 'wc_serial_numbers_version', null );
	}

	/**
	 * Update the installed plugin database version.
	 *
	 * @param string $version version to set.
	 *
	 * @since 1.0.0
	 */
	protected static function update_db_version( $version = null ) {
		update_option( 'wc_serial_numbers_version', is_null( $version ) ? Plugin::instance()->get_plugin_version() : $version );
	}
}

Install::init();
