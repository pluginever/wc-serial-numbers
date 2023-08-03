<?php

class PluginTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * @var \WpunitTester
	 */
	protected $tester;


	public function test_plugin_activation() {
		$plugin = WCSN();
		$this->assertTrue( $plugin->is_plugin_active( $plugin->get_basename() ) );
		$this->assertEquals( $plugin->get_version(), get_option( 'wc_serial_numbers_version' ) );
	}

	public function test_plugin_data() {
		$plugin = WCSN();
		$this->assertNotEmpty( $plugin->get_name() );
		$this->assertNotEmpty( $plugin->get_version() );
		$this->assertNotEmpty( $plugin->get_text_domain() );
		$this->assertNotEmpty( $plugin->get_domain_path() );
		$this->assertNotEmpty( $plugin->get_lang_path() );
		$this->assertNotEmpty( $plugin->get_slug() );
		$this->assertNotEmpty( $plugin->get_prefix() );
		$this->assertNotEmpty( $plugin->get_basename() );
		$this->assertNotEmpty( $plugin->get_path() );
		$this->assertNotEmpty( $plugin->get_url() );
		$this->assertNotEmpty( $plugin->get_assets_url() );
		$this->assertNotEmpty( $plugin->get_assets_path() );
		$this->assertNotEmpty( $plugin->get_template_path() );
		$this->assertNotEmpty( $plugin->get_api_url() );
		$this->assertNotEmpty( $plugin->get_store_url() );

		$plugin_data = get_plugin_data( $plugin->get_file(), false, false );
		$this->assertEquals( $plugin->get_name(), $plugin_data['Name'] );
		$this->assertEquals( $plugin->get_version(), $plugin_data['Version'] );
		$this->assertEquals( $plugin->get_text_domain(), $plugin_data['TextDomain'] );
		$this->assertEquals( $plugin->get_domain_path(), $plugin_data['DomainPath'] );
		$this->assertEquals( $plugin->get_data( 'author' ), $plugin_data['Author'] );
		$this->assertEquals( $plugin->get_data( 'authoruri' ), $plugin_data['AuthorURI'] );
		$this->assertEquals( $plugin->get_data( 'network' ), $plugin_data['Network'] );
	}

	public function test_settings() {
		$settings = \WooCommerceSerialNumbers\Admin\Settings::get_instance();
		$tabs     = $settings->get_tabs();
		foreach ( array_keys( $tabs ) as $tab ) {
			$options = $settings->get_settings( $tab );
			foreach ( $options as $option ) {
				if ( isset( $option['default'] ) && isset( $option['id'] ) ) {
					$this->assertEquals( $option['default'], get_option( $option['id'] ) );
				}
			}
		}
	}

	public function test_cron() {
		$this->assertNotEmpty( wp_get_schedules() );
		$this->assertNotEmpty( wp_next_scheduled( 'wc_serial_numbers_hourly_event' ) );
		$this->assertNotEmpty( wp_next_scheduled( 'wc_serial_numbers_daily_event' ) );
	}

	public function test_tables(){
		// Verify that the tables are created and have the correct columns.
		global $wpdb;
		$tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}serial_numbers%'" );
		$this->assertNotEmpty( $tables );

		$tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}serial_numbers_activations%'" );
		$this->assertNotEmpty( $tables );
	}
}
