<?php

class PluginTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * @var \WpunitTester
	 */
	protected $tester;


	public function test_plugin_activation() {
//		$plugin = wc_serial_numbers();
//		$this->assertTrue( $plugin->is_plugin_active( $plugin->get_basename() ) );
//		$this->assertNotEmpty( get_option( $plugin->get_prefix( '_activated' ) ) );
//		$this->assertEquals( $plugin->get_db_version(), $plugin->get_version() );
	}

	public function test_plugin_data() {
//		$plugin = wc_serial_numbers();
//		$this->assertEquals( 'WooCommerce Serial Numbers', $plugin->get_plugin_data( 'name' ) );
//		$this->assertNotEmpty( $plugin->get_plugin_data( 'version' ) );
//		$this->assertNotEmpty( $plugin->get_plugin_data( 'author' ) );
//		$this->assertNotEmpty( $plugin->get_plugin_data( 'author_uri' ) );
//		$this->assertNotEmpty( $plugin->get_plugin_data( 'plugin_uri' ) );
//		$this->assertNotEmpty( $plugin->get_plugin_data( 'description' ) );
//		$this->assertNotEmpty( $plugin->get_plugin_data( 'text_domain' ) );
//		$this->assertNotEmpty( $plugin->get_plugin_data( 'domain_path' ) );
	}

	public function test_settings() {
//		$plugin   = wc_serial_numbers();
//		$settings = $plugin->get_controller( WooCommerceSerialNumbers\Admin\Settings::class );
//		$this->assertNotEmpty( $settings );
//		do_action( $plugin->get_prefix( '_activated' ) );
//		$tabs = $settings->get_settings_tabs();
//		foreach ( $tabs as $tab => $label ) {
//			$fields = $settings->get_settings_for_tab();
//			foreach ( $fields as $field ) {
//				if ( isset( $field['default'] ) && ! empty( $field['id'] ) ) {
//					codecept_debug( "Checking default value for {$field['id']}" );
//					$this->assertEquals( get_option( $field['id'] ), $field['default'] );
//				}
//			}
//		}
	}
}
