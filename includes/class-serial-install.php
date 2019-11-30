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
			activation_email varchar(200) DEFAULT NULL,
			status varchar(50) DEFAULT 'available',
			validity varchar(200) DEFAULT NULL,
			expire_date DATETIME NULL DEFAULT NULL,
			order_date DATETIME NULL DEFAULT NULL,
			created DATETIME NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			key product_id (product_id),
			key order_id (order_id),
			key status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcsn_activations(
			  id bigint(20) NOT NULL auto_increment,
			  serial_id bigint(20) NOT NULL,
			  instance varchar(200) NOT NULL,
			  active int(1) NOT NULL DEFAULT 1,
			  platform varchar(200) NULL,
			  activation_time DATETIME NULL DEFAULT NULL,
			  PRIMARY KEY  (id),
			  key serial_id (serial_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		];

		foreach ( $tables as $table ) {
			dbDelta( $table );
		}
	}

	/**
	 * @since 1.2.0
	 */
	public static function create_default_data(){
		$key     = sanitize_key( wc_serial_numbers()->plugin_name );
		$version = get_option( $key . '_version', '0' );
		if ( empty( $version ) ) {
			update_option( $key . '_version', wc_serial_numbers()->version );
		}

		$install_date = get_option( $key . '_install_time', '0' );
		if ( empty( $install_date ) ) {
			update_option( $key . '_install_time', current_time( 'timestamp' ) );
		}

		$general_settings = array(
			'wsn_rows_per_page'  => '20',
			'wsn_allow_checkout' => 'no',
		);

		$saved_general_settings = get_option( 'wsn_general_settings' );
		if ( empty( $saved_general_settings ) ) {
			update_option( 'wsn_general_settings', $general_settings );
		}

		$delivery_settings = array(
			'wsn_auto_complete_order'  => 'no',
			'wsn_re_use_serial'        => 'no',
			'wsn_send_serial_number'   => 'completed',
			'wsn_revoke_serial_number' => array(
				'cancelled' => 'cancelled',
				'refunded'  => 'refunded',
				'failed'    => 'failed',
			),
		);

		$saved_delivery_settings = get_option( 'wsn_delivery_settings' );
		if ( empty( $saved_delivery_settings ) ) {
			update_option( 'wsn_delivery_settings', $delivery_settings );
		}

		$notification_settings = array(
			'wsn_admin_bar_notification'            => 'on',
			'wsn_admin_bar_notification_number'     => '5',
			'wsn_admin_bar_notification_send_email' => 'on',
			'wsn_admin_bar_notification_email'      => get_option( 'admin_email' ),
		);

		$saved_notification_settings = get_option( 'wsn_notification_settings' );
		if ( empty( $saved_notification_settings ) ) {
			update_option( 'wsn_notification_settings', $notification_settings );
		}
	}

	/**
	 * create cron event
	 *
	 * @since 1.0.0
	 */
	public static function create_cron() {
		if ( ! wp_next_scheduled( 'wcsn_per_minute_event' ) ) {
			wp_schedule_event( time(), 'once_a_minute', 'wcsn_per_minute_event' );
		}

		if ( ! wp_next_scheduled( 'wcsn_hourly_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'wcsn_hourly_event' );
		}

		if ( ! wp_next_scheduled( 'wcsn_daily_event' ) ) {
			wp_schedule_event( time(), 'daily', 'wcsn_daily_event' );
		}
	}
}
