<?php
defined( 'ABSPATH' ) || exit();

/**
 * Handles installation and updating tasks.
 *
 * @class    WC_Serial_Numbers_Install
 * @version  1.1.6
 */
class WC_Serial_Numbers_Install {
	/**
	 * Store update files
	 *
	 * @var array
	 * @since 1.5.6
	 */
	private static $updates = array(
		'1.0.1' => 'updates/update-1.0.1.php',
		'1.0.6' => 'updates/update-1.0.6.php',
		'1.0.8' => 'updates/update-1.0.8.php',
		'1.1.2' => 'updates/update-1.1.2.php',
	);

	/**
	 * Current plugin version.
	 *
	 * @since 1.5.6
	 * @var string
	 */
	private static $current_version;

	/**
	 * Hookup.
	 *
	 * @since 1.5.6
	 */
	public static function init() {
		//Get plugin version
		self::$current_version = get_option( 'wc_serial_numbers_version', null );

		// Installation and DB updates handling.
		add_action( 'init', array( __CLASS__, 'maybe_install' ) );
		register_activation_hook( WCSN_PLUGIN_FILE, array( __CLASS__, 'install' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_update' ) );

		// Show row meta on the plugin screen.
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );

		//cron actions
		add_filter( 'cron_schedules', array( __CLASS__, 'custom_cron_schedules' ), 20 );
	}

	/**
	 * Check version and run the installer if necessary.
	 *
	 * @since  1.1.6
	 */
	private static function is_installing() {
		return 'yes' === get_transient( 'wc_serial_numbers_installing' );
	}

	/**
	 * Installation possible?
	 *
	 * @return boolean
	 * @since  1.1.6
	 *
	 */
	private static function can_install() {
		return ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! defined( 'IFRAME_REQUEST' ) && ! self::is_installing();
	}

	/**
	 * Installation needed?
	 *
	 * @return boolean
	 * @since  1.1.6
	 *
	 */
	private static function must_install() {
		return version_compare( self::$current_version, wc_serial_numbers()->get_version(), '<' );
	}

	/**
	 * Check version and run the installer if necessary.
	 *
	 * @since  1.1.6
	 */
	public static function maybe_install() {
		if ( self::can_install() && self::must_install() ) {
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

		if ( is_null( self::$current_version ) ) {
			// Add dismissible welcome notice.
			WC_Serial_Numbers_Admin_Notice::welcome_notice();
		}

		//setup transient actions
		if ( false === wp_next_scheduled( 'wc_serial_numbers_hourly_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'wcsn_hourly_event' );
		}

		if ( false === wp_next_scheduled( 'wc_serial_numbers_daily_event' ) ) {
			wp_schedule_event( time(), 'daily', 'wcsn_daily_event' );
		}

		// Update plugin version - once set, 'maybe_install' will not call 'install' again.
		self::update_version();
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
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wc_serial_numbers(
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
			created_date DATETIME NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			key product_id (product_id),
			key order_id (order_id),
			key vendor_id (vendor_id),
			key activation_limit (activation_limit),
			key status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ",
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wc_serial_numbers_activations(
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
	 * Update plugin version to current.
	 */
	private static function update_version() {
		delete_option( 'wc_serial_numbers_version' );
		add_option( 'wc_serial_numbers_version', wc_serial_numbers()->get_version() );
	}


	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $links
	 * @param mixed $file
	 *
	 * @return    array
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
	 * Perform all the necessary upgrade routines.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function maybe_update() {
		$key               = sanitize_key( wc_serial_numbers()->plugin_name ) . '_version';;
		$installed_version = get_option( $key );

		// may be it's the first install
		if ( ! $installed_version ) {
			return false;
		}

		if ( version_compare( $installed_version, wc_serial_numbers()->get_version(), '<' ) ) {
			$path = trailingslashit( dirname( __FILE__ ) . '/updates' );

			foreach ( self::$updates as $version => $file ) {
				if ( version_compare( $installed_version, $version, '<' ) ) {
					include $path . $file;
					update_option( $key, $version );
				}
			}

			delete_option( $key );
			update_option( $key, wc_serial_numbers()->get_version() );
		}

		return true;
	}

	/**
	 * Add custom cron schedule
	 *
	 * @param $schedules
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public static function custom_cron_schedules( $schedules ) {
		$schedules ['once_a_minute'] = array(
			'interval' => 60,
			'display'  => __( 'Once a Minute', 'wc-serial-numbers' )
		);

		return $schedules;
	}


	/**
	 * Get all tables.
	 *
	 * @return string[]
	 * @since 1.1.5
	 */
	public static function get_tables() {
		global $wpdb;

		$tables = array(
			"{$wpdb->prefix}wc_serial_numbers",
			"{$wpdb->prefix}wc_serial_numbers_activations",
		);

		return $tables;
	}


}

WC_Serial_Numbers_Install::init();
