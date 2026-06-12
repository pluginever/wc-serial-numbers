<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

/**
 * Tests for installation: tables, options and cron.
 */
class InstallerTest extends TestCase {

	/**
	 * Both custom tables exist with their key columns.
	 */
	public function testTablesExist(): void {
		global $wpdb;

		$this->assertSame( "{$wpdb->prefix}serial_numbers", $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}serial_numbers'" ) );
		$this->assertSame( "{$wpdb->prefix}serial_numbers_activations", $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}serial_numbers_activations'" ) );

		$this->assertNotEmpty( $wpdb->get_var( "SHOW COLUMNS FROM {$wpdb->prefix}serial_numbers LIKE 'uuid'" ) );
		$this->assertNotEmpty( $wpdb->get_var( "SHOW COLUMNS FROM {$wpdb->prefix}serial_numbers LIKE 'order_item_id'" ) );
	}

	/**
	 * The current plugin version is stored as the db version.
	 */
	public function testDbVersionStored(): void {
		$this->assertSame( WCSN()->get_version(), WCSN()->get_db_version() );
	}

	/**
	 * Installation records the install date and schedules the hourly cron.
	 */
	public function testInstallDateAndCronScheduled(): void {
		$this->assertNotEmpty( get_option( 'wc_serial_numbers_install_date' ) );
		$this->assertNotFalse( wp_next_scheduled( 'wc_serial_numbers_hourly_event' ) );
	}
}
