<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Serial_Numbers_Install {
	/**
	 * WC_Serial_Numbers_Install constructor.
	 */
	public function __construct() {
		register_activation_hook( WC_SERIAL_NUMBERS_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( WC_SERIAL_NUMBERS_FILE, array( $this, 'deactivate' ) );
	}

	public static function activate() {
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

		self::create_tables();
		self::create_cron();
	}

	/**
	 * Create tables
	 *
	 * @since 1.0.0
	 */
	public static function create_tables() {
		global $wpdb;
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

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
			expire_date TIMESTAMP DEFAULT '0000-00-00 00:00:00' NOT NULL,
			order_date TIMESTAMP DEFAULT '0000-00-00 00:00:00' NOT NULL,
			created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
			) $collate;",
			"CREATE TABLE {$wpdb->prefix}wcsn_activations (
			  id bigint(20) NOT NULL auto_increment,
			  serial_id bigint(20) NOT NULL,
			  instance varchar(200) NOT NULL,
			  active int(1) NOT NULL DEFAULT 1,
			  platform varchar(200) NULL,
			  activation_time TIMESTAMP DEFAULT '0000-00-00 00:00:00' NOT NULL,
			  PRIMARY KEY  (id)
			) $collate;"
		];

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		foreach ( $tables as $table ) {
			dbDelta( $table );
		}
	}

	/**
	 * create cron event
	 *
	 * @since 1.0.0
	 */
	public static function create_cron() {
		if ( ! wp_next_scheduled( 'wcsn_hourly_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'wcsn_hourly_event' );
		}

		if ( ! wp_next_scheduled( 'wcsn_daily_event' ) ) {
			wp_schedule_event( time(), 'daily', 'wcsn_daily_event' );
		}
	}

	/**
	 * Disable plugin specific data
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'wcsn_hourly_event' );
		wp_clear_scheduled_hook( 'wcsn_daily_event' );
	}

}

new WC_Serial_Numbers_Install();
