<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;

/**
 * Pins the full public API surface that wc-serial-numbers-pro (1.4.6/1.5.0) consumes
 * from the free plugin, verified against the pro source:
 *
 * - Free-side wcsn_* functions pro calls (Generators, Tools, Orders).
 * - Key class contract: statics, the instance getters pro reads, delete().
 * - The CSV-export query shape Tools.php sends to Key::query() (paged + order_date__between).
 * - wcsn_get_keys() with 'count' => true inside the args array (Generators::generate_serials).
 * - The wc_serial_numbers_pre_order_item_connect_serial_numbers filter pro's generator
 *   hooks to inject auto-generated keys during fulfillment.
 * - The wc_serial_numbers_allow_duplicate_key filter pro uses to permit duplicates.
 */
class ProContractTest extends TestCase {

	/**
	 * Every free-side function the pro plugin calls must exist.
	 */
	public function testFreeFunctionsExist(): void {
		$functions = array(
			'wcsn_get_manager_role',
			'wcsn_insert_key',
			'wcsn_get_keys',
			'wcsn_get_product_title',
			'wcsn_get_key_statuses',
			'wcsn_get_key_sources',
			'wcsn_order_is_fullfilled',
			'wcsn_order_has_products',
			'wcsn_order_get_keys',
			'wcsn_is_software_support_enabled',
			'wcsn_is_duplicate_key_allowed',
			'wcsn_order_update_keys',
		);

		foreach ( $functions as $function ) {
			$this->assertTrue( function_exists( $function ), "Pro calls {$function}() but it does not exist." );
		}
	}

	/**
	 * Key statics pro calls must remain static, and every instance getter pro reads must exist.
	 */
	public function testKeyClassContract(): void {
		foreach ( array( 'query', 'count', 'insert', 'find' ) as $method ) {
			$reflection = new \ReflectionMethod( Key::class, $method );
			$this->assertTrue( $reflection->isStatic(), "Key::{$method}() must be static." );
		}

		$getters = array(
			'get_id',
			'get_product_id',
			'get_serial_key',
			'get_expire_date',
			'get_activation_limit',
			'get_activation_count',
			'get_validity',
			'get_status',
			'get_product_title',
			'get_order_id',
			'get_order_item_id',
			'get_order_date',
			'get_customer_id',
			'get_customer_name',
			'get_customer_email',
			'delete',
		);

		foreach ( $getters as $method ) {
			$this->assertTrue( method_exists( Key::class, $method ), "Pro calls Key::{$method}() but it does not exist." );
		}
	}

	/**
	 * Admin classes pro extends or invokes must exist with the expected members.
	 */
	public function testAdminClassesExist(): void {
		$this->assertTrue(
			class_exists( '\WooCommerceSerialNumbers\Admin\ListTables\ListTable' ),
			'Pro Generators\ListTable extends Admin\ListTables\ListTable.'
		);
		$this->assertTrue(
			class_exists( '\WooCommerceSerialNumbers\Admin\Settings' ),
			'Pro Installer calls Admin\Settings::instance()->save_defaults().'
		);
		$this->assertTrue(
			method_exists( '\WooCommerceSerialNumbers\Admin\Settings', 'save_defaults' ),
			'Admin\Settings must expose save_defaults().'
		);
	}

	/**
	 * Pro's CSV export queries Key::query() with product_id + limit + paged + status
	 * + order_date__between and pages until an empty result.
	 */
	public function testExportQueryShape(): void {
		$product = $this->create_product();
		$order   = $this->create_order( $product, 1, array( 'status' => 'completed' ) );

		foreach ( array( '2026-01-10', '2026-02-10', '2026-03-10' ) as $i => $date ) {
			$key = $this->make_available_key( $product->get_id(), 'EXPORT-' . $i );
			$key->fill(
				array(
					'status'     => 'sold',
					'order_id'   => $order->get_id(),
					'order_date' => $date . ' 12:00:00',
				)
			);
			$this->assertNotWPError( $key->save() );
		}

		$results = Key::query(
			array(
				'product_id'          => $product->get_id(),
				'limit'               => 20,
				'paged'               => 1,
				'status'              => 'sold',
				'order_date__between' => array( '2026-01-01 00:00:00', '2026-02-28 23:59:59' ),
			)
		);

		$this->assertCount( 2, $results );
		foreach ( $results as $result ) {
			$this->assertInstanceOf( Key::class, $result );
			$this->assertStringStartsWith( 'EXPORT-', $result->get_serial_key() );
		}

		$next_page = Key::query(
			array(
				'product_id' => $product->get_id(),
				'limit'      => 20,
				'paged'      => 2,
				'status'     => 'sold',
			)
		);
		$this->assertSame( array(), $next_page, 'An out-of-range page must return empty so the export loop terminates.' );
	}

	/**
	 * Pro's generator counts available stock with wcsn_get_keys() passing
	 * 'count' => true inside the args array, expecting an integer back.
	 */
	public function testGetKeysCountInsideArgs(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'COUNT-1', array( 'source' => 'auto_generated' ) );
		$this->make_available_key( $product->get_id(), 'COUNT-2', array( 'source' => 'auto_generated' ) );
		$this->make_available_key( $product->get_id(), 'COUNT-3', array( 'source' => 'custom_source' ) );

		$count = wcsn_get_keys(
			array(
				'product_id' => $product->get_id(),
				'status'     => 'available',
				'source'     => 'auto_generated',
				'count'      => true,
			)
		);

		$this->assertSame( 2, (int) $count );
	}

	/**
	 * The pre_order_item_connect_serial_numbers filter must keep firing during
	 * fulfillment with (product_id, needed_count, key_source, order_id), and keys a
	 * pro-style callback inserts must be picked up and assigned to the order.
	 */
	public function testGeneratorInjectionDuringFulfillment(): void {
		$product = $this->create_product();
		update_post_meta( $product->get_id(), '_serial_key_source', 'auto_generated' );

		// Pro supplies the source via this filter; free defaults it to custom_source.
		$source_callback = function ( $source, $product_id ) {
			return get_post_meta( $product_id, '_serial_key_source', true ) ?: $source;
		};
		add_filter( 'wc_serial_numbers_product_serial_source', $source_callback, 10, 2 );

		$captured = array();
		$callback = function ( $product_id, $needed_count, $key_source, $order_id ) use ( &$captured ) {
			$captured = compact( 'product_id', 'needed_count', 'key_source', 'order_id' );
			for ( $i = 0; $i < $needed_count; $i++ ) {
				wcsn_insert_key(
					array(
						'product_id' => $product_id,
						'serial_key' => 'GENERATED-' . $i,
						'status'     => 'available',
						'source'     => $key_source,
					)
				);
			}

			return $needed_count;
		};
		add_filter( 'wc_serial_numbers_pre_order_item_connect_serial_numbers', $callback, 10, 4 );

		$order = $this->create_order( $product, 2, array( 'status' => 'completed' ) );
		wcsn_order_update_keys( $order->get_id() );

		remove_filter( 'wc_serial_numbers_pre_order_item_connect_serial_numbers', $callback, 10 );
		remove_filter( 'wc_serial_numbers_product_serial_source', $source_callback, 10 );

		$this->assertSame( $product->get_id(), $captured['product_id'] );
		$this->assertSame( 2, $captured['needed_count'] );
		$this->assertSame( 'auto_generated', $captured['key_source'] );
		$this->assertSame( $order->get_id(), $captured['order_id'] );

		$keys = wcsn_order_get_keys( $order->get_id() );
		$this->assertCount( 2, $keys );
		foreach ( $keys as $key ) {
			$this->assertSame( 'sold', $key->get_status() );
			$this->assertStringStartsWith( 'GENERATED-', $key->get_serial_key() );
		}
	}

	/**
	 * Pro toggles wc_serial_numbers_allow_duplicate_key to permit duplicate serials;
	 * the check compares against the encrypted column, so both branches must work.
	 */
	public function testAllowDuplicateKeyFilter(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'DUPLICATE-ME' );

		$duplicate = Key::insert(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'DUPLICATE-ME',
				'status'     => 'available',
			)
		);
		$this->assertWPError( $duplicate, 'Duplicate serials must be rejected by default.' );

		add_filter( 'wc_serial_numbers_allow_duplicate_key', '__return_true' );
		$allowed = Key::insert(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'DUPLICATE-ME',
				'status'     => 'available',
			)
		);
		remove_filter( 'wc_serial_numbers_allow_duplicate_key', '__return_true' );

		$this->assertNotWPError( $allowed );
		$this->assertSame( 'DUPLICATE-ME', $allowed->get_serial_key() );
	}
}
