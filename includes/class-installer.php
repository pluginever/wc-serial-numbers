<?php

namespace WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Installer.
 *
 * @since 1.0.0
 * @package WooCommerceSerialNumbers
 */
class Installer extends Controller {

	/**
	 * Update callbacks.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $updates = array(
		'1.0.0' => 'update_100',
		'1.0.1' => 'update_101',
		'1.0.6' => 'update_106',
		'1.0.8' => 'update_108',
		'1.1.2' => 'update_112',
		'1.2.0' => 'update_120',
		'1.2.1' => 'update_121',
	);

	/**
	 * Set up the controller.
	 *
	 * Load files or register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function init() {
		add_action( 'wc_serial_numbers_activated', array( $this, 'install' ) );
		add_action( 'init', array( $this, 'check_version' ), 5 );
		add_filter( 'cron_schedules', array( __CLASS__, 'custom_cron_schedules' ), 20 ); // phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval
	}

	/**
	 * Install the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function install() {
		$db_version = $this->get_plugin()->get_db_version();
		if ( ! is_blog_installed() ) {
			return;
		}

		add_option( $this->get_plugin()->get_db_version_key(), $this->get_plugin()->get_version() );
		add_option( $this->get_plugin()->get_activation_date_key(), current_time( 'mysql' ) );

		// create tables.
		self::create_tables();

		if ( ! $db_version ) {
			/**
			 * Fires after the plugin is installed for the first time.
			 *
			 * @since 1.0.0
			 */
			do_action( $this->get_plugin()->get_id() . '_newly_installed' );
			set_transient( $this->get_plugin()->get_id() . '_activation_redirect', 1, 30 );
		}
	}

	/**
	 * Check plugin version and run the updater if necessary.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function check_version() {
		$db_version      = $this->get_plugin()->get_db_version();
		$current_version = $this->get_plugin()->get_version();
		$requires_update = version_compare( $db_version, $current_version, '<' );

		if ( ! defined( 'IFRAME_REQUEST' ) && $requires_update ) {
			$this->install();

			$update_versions = array_keys( $this->updates );
			usort( $update_versions, 'version_compare' );
			$needs_update = ! is_null( $db_version ) && version_compare( $db_version, end( $update_versions ), '<' );
			if ( $needs_update ) {
				$this->update();
				/**
				 * Fires after the plugin is updated.
				 *
				 * @since 1.0.0
				 */
				do_action( $this->get_plugin()->get_id() . '_updated' );
			} else {
				$this->get_plugin()->update_db_version();
			}
		}
	}

	/**
	 * Add custom cron schedule
	 *
	 * @param array $schedules list of cron schedules.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function custom_cron_schedules( $schedules ) {
		$schedules ['once_a_minute'] = array(
			'interval' => 60,
			'display'  => esc_html__( 'Once a Minute', 'wc-serial-numbers' ),
		);

		return $schedules;
	}

	/**
	 * Update the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update() {
		$db_version      = $this->get_plugin()->get_db_version();
		$current_version = $this->get_plugin()->get_version();
		foreach ( $this->updates as $version => $callbacks ) {
			$callbacks = (array) $callbacks;
			if ( version_compare( $db_version, $version, '<' ) ) {
				foreach ( $callbacks as $callback ) {
					$this->get_plugin()->log( sprintf( 'Updating to %s from %s', $version, $db_version ) );
					// if the callback return false then we need to update the db version.
					$continue = call_user_func( array( $this, $callback ) );
					if ( ! $continue ) {
						$this->get_plugin()->update_db_version( $version );
						$notice = sprintf(
						/* translators: 1: plugin name 2: version number */
							__( '%1$s updated to version %2$s successfully.', 'wc-serial-numbers' ),
							'<strong>' . $this->get_plugin()->get_name() . '</strong>',
							'<strong>' . $version . '</strong>'
						);
						$this->add_notice( $notice );
					}
				}
			}
		}
	}

	/**
	 * Get all tables.
	 *
	 * @return string[]
	 * @since 1.2.0
	 */
	public static function get_tables() {
		global $wpdb;

		$tables = array(
			"{$wpdb->prefix}serial_numbers",
			"{$wpdb->prefix}serial_numbers_activations",
		);

		return $tables;
	}

	/**
	 * Get table schema.
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public static function create_tables() {
		global $wpdb;
		$wpdb->hide_errors();
		$collate = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$tables = "
CREATE TABLE {$wpdb->prefix}serial_numbers (
id bigint(20) NOT NULL AUTO_INCREMENT,
serial_key longtext DEFAULT NULL,
product_id bigint(20) NOT NULL,
activation_limit int(9) NOT NULL DEFAULT 0,
activation_count int(9) NOT NULL  DEFAULT 0,
order_id bigint(20) DEFAULT NULL,
vendor_id bigint(20) DEFAULT NULL,
status varchar(50) DEFAULT 'available',
validity varchar(200) DEFAULT NULL,
expire_date DATETIME NULL DEFAULT NULL,
order_date DATETIME NULL DEFAULT NULL,
source varchar(50) DEFAULT 'custom_source',
created_date DATETIME NULL DEFAULT NULL,
PRIMARY KEY  (id),
key product_id (product_id),
key order_id (order_id),
key vendor_id (vendor_id),
key activation_limit (activation_limit),
key status (status)
) $collate;
CREATE TABLE {$wpdb->prefix}serial_numbers_activations(
id bigint(20) NOT NULL auto_increment,
serial_id bigint(20) NOT NULL,
instance varchar(200) NOT NULL,
active int(1) NOT NULL DEFAULT 1,
platform varchar(200) DEFAULT NULL,
activation_time DATETIME NULL DEFAULT NULL,
PRIMARY KEY  (id),
key serial_id (serial_id),
key active (active)
) $collate;
	";
		dbDelta( $tables );
	}

	/**
	 * Update to version 1.0.0.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function update_100() {
		$this->get_plugin()->log( 'Updating to version 1.0.0' );
	}
}
