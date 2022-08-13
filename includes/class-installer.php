<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Installer class.
 *
 * This class is responsible for installing and uninstalling the plugin.
 * @since 1.0.0
 */
class Installer {
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
	 * Installer Constructor.
	 *
	 * Register hook & actions.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'maybe_install' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_update' ) );
		add_action( 'admin_init', array( __CLASS__, 'activation_redirect' ) );
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
		if ( ! defined( 'IFRAME_REQUEST' ) && ( version_compare( self::get_db_version(), Plugin::instance()->plugin_version(), '!=' ) ) ) {
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
		if ( ! empty( $current_db_version ) && version_compare( $current_db_version, Plugin::instance()->plugin_version(), '<' ) ) {
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
	public static function activation_redirect() {
		if ( Plugin::instance()->plugin_settings_url() && 'yes' === get_transient( 'wc_serial_numbers_activated' ) ) {
			delete_transient( 'wc_serial_numbers_activated' );
			wp_safe_redirect( Plugin::instance()->plugin_settings_url() );
			exit();
		}
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
		set_transient( 'wc_serial_numbers_activated', 'yes', 30 );

		// Use add_option() here to avoid overwriting this value with each
		// plugin version update. We base plugin age off of this value.
		add_option( 'wc_serial_numbers_install_date', current_time( 'timestamp' ) );
		add_option( 'wc_serial_numbers_version', Plugin::get()->plugin_version() );
		self::create_tables();
		self::create_options();
		flush_rewrite_rules();
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
			"CREATE TABLE {$wpdb->prefix}wsn_keys(
         	`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`key` longtext DEFAULT NULL,
			`is_encrypted` TINYINT(9) NOT NULL DEFAULT 0,
			`parent_id` BIGINT UNSIGNED NOT NULL,
			`product_id` BIGINT UNSIGNED NOT NULL,
			`order_id` BIGINT UNSIGNED NOT NULL,
			`order_item_id` BIGINT UNSIGNED NOT NULL,
			`vendor_id` BIGINT UNSIGNED NOT NULL,
			`customer_id` BIGINT UNSIGNED NOT NULL,
			`activation_ids` longtext NOT NULL,
			`activation_limit` INT(9) NOT NULL DEFAULT 0,
			`activation_count` INT(9) NOT NULL  DEFAULT 0,
			`status` VARCHAR(50) DEFAULT 'available',
			`valid_for` BIGINT UNSIGNED NOT NULL,
			`date_expire` DATETIME NULL DEFAULT NULL,
			`date_ordered` DATETIME NULL DEFAULT NULL,
			`date_created` DATETIME NULL DEFAULT NULL,
			PRIMARY KEY  (`id`),
			KEY `product_id` (`product_id`),
			KEY `order_id` (`order_id`),
			KEY `order_item_id` (`order_item_id`),
			KEY `vendor_id` (`vendor_id`),
			KEY `customer_id` (`customer_id`),
			KEY `activation_limit` (`activation_limit`),
			KEY `status` (`status`)
            ) $collate;",

			"CREATE TABLE {$wpdb->prefix}wsn_generators(
			`id` bigint(20) NOT NULL auto_increment,
			`name` VARCHAR(191) NOT NULL,
			`pattern` VARCHAR(32) NOT NULL,
			`activation_limit` INT(9) NOT NULL DEFAULT 0,
			`valid_for` INT(9) DEFAULT NULL,
			`date_expire` VARCHAR(191) NOT NULL,
			`date_created` DATETIME NULL DEFAULT NULL,
			PRIMARY KEY  (`id`),
			KEY `name` (`name`)
			) $collate;",

			"CREATE TABLE {$wpdb->prefix}wsn_activations(
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
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 *
	 * @since 1.0.0
	 */
	private static function create_options() {
		include_once Plugin::instance()->plugin_path() . '/includes/admin/class-settings-page.php';
		$tabs = Admin\Settings_Page::get_tabs();

		foreach ( array_keys( $tabs ) as $tab_id ) {
			$subsections = array_unique( array_merge( array( '' ), array_keys( Admin\Settings_Page::get_sections( $tab_id ) ) ) );
			foreach ( array_keys( $subsections ) as $subsection ) {
				foreach ( Admin\Settings_Page::get_settings_for_tab_section( $tab_id, $subsection ) as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}
	}

	/**
	 * Deactivate the plugin.
	 *
	 *
	 * @since #.#.#
	 */
	public static function deactivate() {
		delete_transient( 'wc_serial_numbers_activated' );
		flush_rewrite_rules();
	}

	/**
	 * Remove plugin related options.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function uninstall() {
		// placeholder function.
		flush_rewrite_rules();
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
		update_option( 'wc_serial_numbers_version', is_null( $version ) ? Plugin::instance()->plugin_version() : $version );
	}
}
