<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

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
		$this->assertTrue( class_exists( \WooCommerceSerialNumbers\Models\Key::class ) );

		// The list table is admin-only and extends WP_List_Table.
		if ( ! class_exists( '\WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}
		$this->assertTrue( class_exists( \WooCommerceSerialNumbers\Admin\ListTables\ListTable::class ) );
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
}
