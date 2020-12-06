<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Installer {
	/**
	 * Updates and callbacks that need to be run per version.
	 *
	 * @since 1.2.0
	 * @var array
	 */
	private static $updates = array(
		'1.0.1' => 'wcsn_update_1_0_1',
		'1.0.6' => 'wcsn_update_1_0_6',
		'1.0.8' => 'wcsn_update_1_0_8',
		'1.1.2' => 'wcsn_update_1_1_2',
		'1.2.0' => 'wcsn_update_1_2_0',
		'1.2.1' => 'wcsn_update_1_2_1',
	);

	/**
	 * Initialize all hooks.
	 *
	 * @since 1.2.0
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_install' ) );
		add_action( 'init', array( __CLASS__, 'maybe_update' ) );
		$action_link = 'plugin_action_links_wc-serial-numbers/wc-serial-numbers.php';
		add_filter( $action_link, array( __CLASS__, 'action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
		//cron actions
		add_filter( 'cron_schedules', array( __CLASS__, 'custom_cron_schedules' ), 20 );
	}

	/**
	 * Check version and run the installer if necessary.
	 *
	 * @since  1.1.6
	 */
	public static function maybe_install() {
		if ( empty( get_option( 'wc_serial_numbers_version' ) ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			self::install();
		}
	}


	/**
	 * Install Plugin.
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}

		// Running for the first time? Set a transient now. Used in 'can_install' to prevent race conditions.
		set_transient( 'wc_serial_numbers_installing', 'yes', 10 );

		// Create tables.
		self::create_tables();
		$settings = array(
			'wc_serial_numbers_autocomplete_order'        => 'yes',
			'wc_serial_numbers_reuse_serial_number'       => 'no',
			'wc_serial_numbers_disable_software_support'  => 'no',
			'wc_serial_numbers_enable_stock_notification' => 'yes',
			'wc_serial_numbers_hide_serial_number'        => 'yes',
			'wc_serial_numbers_revoke_status_failed'      => 'yes',
			'wc_serial_numbers_revoke_status_refunded'    => 'yes',
			'wc_serial_numbers_revoke_status_cancelled'   => 'yes',
			'wc_serial_numbers_stock_threshold'           => '5',
			'wc_serial_numbers_notification_recipient'    => get_option( 'admin_email' ),
		);

		foreach ( $settings as $key => $value ) {
			if ( empty( get_option( $key ) ) ) {
				update_option( $key, $value );
			}
		}

		//setup transient actions
		if ( false === wp_next_scheduled( 'wc_serial_numbers_hourly_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'wc_serial_numbers_hourly_event' );
		}

		if ( false === wp_next_scheduled( 'wc_serial_numbers_daily_event' ) ) {
			wp_schedule_event( time(), 'daily', 'wc_serial_numbers_daily_event' );
		}
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *     wcsn_serial_numbers
	 *     wcsn_activations
	 */
	private static function create_tables() {
		global $wpdb;
		$wpdb->hide_errors();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$tables = self::get_schema();

		foreach ( $tables as $table ) {
			dbDelta( $table );
		}
	}

	/**
	 * Get table schema.
	 *
	 * @return array
	 */
	private static function get_schema() {
		global $wpdb;
		$schema = [
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}serial_numbers(
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ",
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}serial_numbers_activations(
			  id bigint(20) NOT NULL auto_increment,
			  serial_id bigint(20) NOT NULL,
			  instance varchar(200) NOT NULL,
			  active int(1) NOT NULL DEFAULT 1,
			  platform varchar(200) DEFAULT NULL,
			  activation_time DATETIME NULL DEFAULT NULL,
			  PRIMARY KEY  (id),
			  key serial_id (serial_id),
			  key active (active)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		];

		return $schema;
	}


	/**
	 * Check Serial Numbers version and run the updater is required.
	 * This check is done on all requests and runs if the versions do not match.
	 *
	 * @since 1.0.2
	 *
	 * @return void
	 */
	public static function maybe_update() {
		$current_version   = wc_serial_numbers()->get_version();
		$installed_version = get_option( 'wc_serial_numbers_version', null );
		$updates           = self::$updates;
		$update_versions   = array_keys( $updates );
		usort( $update_versions, 'version_compare' );

		$need_update = ! is_null( $installed_version ) && version_compare( $installed_version, end( $update_versions ), '<' );
		if ( $need_update ) {
			self::update();
		} else if ( $current_version != $installed_version ) {
			update_option( 'wc_serial_numbers_version', $current_version );
		}
	}

	/**
	 * Push all needed updates to the queue for processing.
	 *
	 * @since 1.0.2
	 * @return void
	 */
	private static function update() {
		$current_version = get_option( 'wc_serial_numbers_version' );
		foreach ( self::$updates as $version => $update_callbacks ) {

			if ( version_compare( $current_version, $version, '<' ) ) {
				if ( is_array( $update_callbacks ) ) {
					array_map( array( __CLASS__, 'run_update_callback' ), $update_callbacks );
				} else {
					self::run_update_callback( $update_callbacks );
				}
				update_option( 'wc_serial_numbers_version', $version );
			}
		}
	}

	/**
	 * Run an update callback.
	 *
	 * @since 1.0.2
	 *
	 * @param string $callback Callback name.
	 */
	public static function run_update_callback( $callback ) {
		include_once WC_SERIAL_NUMBER_PLUGIN_DIR . '/includes/wc-serial-numbers-update-functions.php';
		if ( is_callable( $callback ) ) {
			call_user_func( $callback );
		}
	}


	/**
	 * Plugin action links
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public static function action_links( $links ) {
		$links['settings'] = sprintf( '<a href="%s">', admin_url( 'admin.php?page=wc-serial-numbers-settings' ) ) . __( 'Settings', 'wc-serial-numbers' ) . '</a>';

		return $links;
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $links Plugin Row Meta.
	 * @param mixed $file  Plugin Base file.
	 *
	 * @return array
	 */
	public static function plugin_row_meta( $links, $file ) {
		if ( $file == wc_serial_numbers()->plugin_basename() ) {
			$upgrade_link = 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro';
			$row_meta     = array(
				'docs'    => '<a href="https://www.pluginever.com/docs/woocommerce-serial-numbers/">' . __( 'Documentation', 'wc-serial-numbers' ) . '</a>',
				'upgrade' => '<a href="' . esc_url( $upgrade_link ) . '" style="color: red;font-weight: bold;" target="_blank">' . __( 'Upgrade to PRO', 'wc-serial-numbers' ) . '</a>',
			);

			if ( wc_serial_numbers()->is_pro_active() ) {
				unset( $row_meta['upgrade'] );
			}

			return array_merge( $links, $row_meta );
		}

		return $links;
	}

	/**
	 * Add custom cron schedule
	 *
	 * @since 1.0.0
	 *
	 * @param $schedules
	 *
	 * @return array
	 */
	public static function custom_cron_schedules( $schedules ) {
		$schedules ['once_a_minute'] = array(
			'interval' => 60,
			'display'  => __( 'Once a Minute', 'wc-serial-numbers' )
		);

		return $schedules;
	}

}

WC_Serial_Numbers_Installer::init();
