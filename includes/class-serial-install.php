<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Install {
	/**
	 * Everything need to be done
	 *
	 * @since 1.0.0
	 */
	public static function install() {
		self::create_tables();
		self::create_default_data();
		self::create_cron();
	}

	/**
	 * Delete all data
	 *
	 * @since 1.0.0
	 */
	public static function uninstall() {

	}

	/**
	 * Creat tables
	 * @since 1.0.0
	 */
	public static function create_tables() {
		global $wpdb;
		$wpdb->hide_errors();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$tables = [
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcsn_serial_numbers(
         	id bigint(20) NOT NULL AUTO_INCREMENT,
			serial_key longtext DEFAULT NULL,
			serial_image varchar(200) DEFAULT NULL,
			product_id bigint(20) NOT NULL,
			activation_limit int(9) NULL,
			order_id bigint(20) NOT NULL DEFAULT 0,
			customer_id bigint(20) NOT NULL DEFAULT 0,
			vendor_id bigint(20) NOT NULL DEFAULT 0,
			activation_email varchar(200) DEFAULT NULL,
			status varchar(50) DEFAULT 'available',
			validity varchar(200) DEFAULT NULL,
			expire_date DATETIME NULL DEFAULT NULL,
			order_date DATETIME NULL DEFAULT NULL,
			created DATETIME NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			key product_id (product_id),
			key order_id (order_id),
			key customer_id (customer_id),
			key vendor_id (vendor_id),
			key activation_limit (activation_limit),
			key status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcsn_activations(
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

		foreach ( $tables as $table ) {
			dbDelta( $table );
		}
	}

	/**
	 * @since 1.2.0
	 */
	public static function create_default_data() {
		$key     = sanitize_key( wc_serial_numbers()->plugin_name );
		$version = get_option( $key . '_version', '0' );
		if ( empty( $version ) ) {
			update_option( $key . '_version', wc_serial_numbers()->version );
		}

		$install_date = get_option( $key . '_install_time', '0' );
		if ( empty( $install_date ) ) {
			update_option( $key . '_install_time', current_time( 'timestamp' ) );
		}

		$saved_notification_settings = get_option( 'wc_serial_numbers_settings' );
		if ( !empty( $saved_notification_settings ) ) {
			return ;
		}

		$settings = array(
			'automatic_delivery'           => 'on',
			'reuse_serial_numbers'         => 'off',
			'allow_duplicate'              => 'off',
			'autocomplete_order'           => 'on',
			'disable_software'             => 'off',
			'low_stock_alert'              => 'on',
			'low_stock_notification'       => 'on',
			'low_stock_threshold'          => '10',
			'low_stock_notification_email' => get_option( 'admin_email' ),
		);

		update_option('wc_serial_numbers_settings', $settings);

	}

	/**
	 * create cron event
	 *
	 * @since 1.0.0
	 */
	public static function create_cron() {

		if ( ! wp_next_scheduled( 'wc_serial_numbers_hourly_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'wc_serial_numbers_hourly_event' );
		}

		if ( ! wp_next_scheduled( 'wc_serial_numbers_daily_event' ) ) {
			wp_schedule_event( time(), 'daily', 'wc_serial_numbers_daily_event' );
		}
	}
}
