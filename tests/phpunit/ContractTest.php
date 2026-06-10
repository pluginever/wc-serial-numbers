<?php
//phpcs:ignoreFile

namespace PluginEver\SerialNumbers\Tests;

/**
 * Tests for the public surface the migration must preserve.
 */
class ContractTest extends TestCase {

	/**
	 * The public functions exist.
	 */
	public function testPublicFunctionsExist(): void {
		$functions = array(
			'WCSN',
			'wc_serial_numbers',
			'wcsn_insert_key',
			'wcsn_get_keys',
			'wcsn_get_key_statuses',
			'wcsn_get_key_sources',
			'wcsn_get_product_title',
			'wcsn_order_has_products',
			'wcsn_order_is_fullfilled',
			'wcsn_order_get_keys',
			'wcsn_get_manager_role',
			'wcsn_is_software_support_enabled',
		);

		foreach ( $functions as $function ) {
			$this->assertTrue( function_exists( $function ), "Function {$function} should exist." );
		}
	}

	/**
	 * The public classes exist.
	 */
	public function testPublicClassesExist(): void {
		$this->assertTrue( class_exists( \PluginEver\SerialNumbers\Models\Key::class ) );

		// The list table is admin-only and extends WP_List_Table.
		if ( ! class_exists( '\WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}
		$this->assertTrue( class_exists( \PluginEver\SerialNumbers\Admin\ListTables\ListTable::class ) );
	}

	/**
	 * The plugin boots its services on woocommerce_loaded and fired its loaded action.
	 */
	public function testBootHooks(): void {
		$this->assertNotFalse( has_action( 'woocommerce_loaded', array( WCSN(), 'plugins_loaded' ) ) );
		$this->assertGreaterThanOrEqual( 1, did_action( 'wc_serial_numbers_loaded' ) );
	}

	/**
	 * Key statuses and sources expose the expected defaults.
	 */
	public function testStatusesAndSourcesDefaults(): void {
		$statuses = wcsn_get_key_statuses();
		foreach ( array( 'available', 'pending', 'sold', 'expired', 'cancelled' ) as $status ) {
			$this->assertArrayHasKey( $status, $statuses );
		}

		$this->assertArrayHasKey( 'custom_source', wcsn_get_key_sources() );
	}

	/**
	 * Default option-driven helpers report the expected values.
	 */
	public function testHelperDefaults(): void {
		$this->assertSame( 'manage_woocommerce', wcsn_get_manager_role() );
		$this->assertTrue( wcsn_is_software_support_enabled() );
	}

	/**
	 * WCSN() returns the same plugin instance every call.
	 */
	public function testSingletonInstance(): void {
		$this->assertSame( WCSN(), WCSN() );
	}

	/**
	 * The pre-rename class names resolve through the alias loader.
	 */
	public function testLegacyClassAliases(): void {
		if ( ! class_exists( '\WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}

		$aliases = array(
			'WooCommerceSerialNumbers\\Plugin'                            => \PluginEver\SerialNumbers\Plugin::class,
			'WooCommerceSerialNumbers\\Models\\Key'                       => \PluginEver\SerialNumbers\Models\Key::class,
			'WooCommerceSerialNumbers\\Models\\Activation'                => \PluginEver\SerialNumbers\Models\Activation::class,
			'WooCommerceSerialNumbers\\Admin\\Menus'                      => \PluginEver\SerialNumbers\Admin\Menus::class,
			'WooCommerceSerialNumbers\\Admin\\Settings'                   => \PluginEver\SerialNumbers\Admin\Settings::class,
			'WooCommerceSerialNumbers\\Admin\\ListTables\\ListTable'      => \PluginEver\SerialNumbers\Admin\ListTables\ListTable::class,
			'WooCommerceSerialNumbers\\Admin\\ListTables\\KeysTable'      => \PluginEver\SerialNumbers\Keys\ListTable::class,
			'WooCommerceSerialNumbers\\Admin\\ListTables\\ActivationsTable' => \PluginEver\SerialNumbers\Activations\ListTable::class,
			'WooCommerceSerialNumbers\\Admin\\ListTables\\StockTable'     => \PluginEver\SerialNumbers\Stocks\ListTable::class,
		);

		foreach ( $aliases as $legacy => $current ) {
			$this->assertTrue( class_exists( $legacy ), "Legacy class {$legacy} should resolve." );
			$this->assertInstanceOf( $current, ( new \ReflectionClass( $legacy ) )->newInstanceWithoutConstructor() );
		}
	}

	/**
	 * The tools tabs are registered under the pre-rename callable identity the pro removes by.
	 */
	public function testLegacyToolsTabCallbacks(): void {
		// The admin components don't boot in the test context, so register the menus directly.
		WCSN()->make( \PluginEver\SerialNumbers\Admin\Menus::class )->register();

		foreach ( array( 'import', 'export', 'generators' ) as $tab ) {
			$this->assertNotFalse(
				has_action( "wc_serial_numbers_tools_tab_{$tab}", "WooCommerceSerialNumbers\\Admin\\Menus::{$tab}_tab" ),
				"Tools tab {$tab} should keep its legacy callback identity."
			);
		}
	}

	/**
	 * The REST API exposes the legacy software routes and the new resource routes.
	 */
	public function testRestRoutes(): void {
		do_action( 'rest_api_init' );
		$routes = rest_get_server()->get_routes();

		foreach ( array( '/wcsn/validate', '/wcsn/activate', '/wcsn/deactivate' ) as $route ) {
			$this->assertArrayHasKey( $route, $routes, "Legacy route {$route} should exist." );
		}

		$this->assertArrayHasKey( '/wcsn/v1/keys', $routes );
		$this->assertArrayHasKey( '/wcsn/v1/activations', $routes );
	}
}
