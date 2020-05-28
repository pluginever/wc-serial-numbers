<?php

class Installation_Tests extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_options() {
		$key     = sanitize_key( wc_serial_numbers()->plugin_name );
		$version = get_option( $key . '_version', '0' );
		$this->assertEquals( $version, wc_serial_numbers()->version );
	}

	public function test_tables() {
		global $wpdb;
		$wp_tables = $wpdb->get_col( "SHOW TABLES LIKE '%wcsn%'" );
		$tables    = [
			$wpdb->prefix . 'wcsn_activations',
			$wpdb->prefix . 'wcsn_serial_numbers',
		];
		foreach ( $tables as $table ) {
			$this->assertNotFalse( in_array( $table, $wp_tables ) );
		}
	}

	public function test_crons() {
		$this->assertNotFalse( wp_next_scheduled( 'wcsn_hourly_event' ) );
		$this->assertNotFalse( wp_next_scheduled( 'wcsn_daily_event' ) );
	}


	public function tearDown() {
		parent::tearDown();
	}
}
