<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Activation;
use WooCommerceSerialNumbers\Models\Key;

/**
 * Tests for the model lifecycle hook contract after the b8 migration.
 *
 * The plugin wires Stocks, Cache and Actions to the wc_serial_numbers_key_* /
 * wc_serial_numbers_activation_* hooks, so these pin the hook names, the
 * argument shapes, and the functional effects the listeners produce.
 */
class ModelHooksTest extends TestCase {

	/**
	 * key_inserted fires once with the Key model and the inserted column data,
	 * and listeners receive the plaintext serial (encryption stays inside the
	 * persistence boundary).
	 */
	public function testInsertedHookFiresWithModelAndPlaintextData(): void {
		$captured = array();
		add_action(
			'wc_serial_numbers_key_inserted',
			function ( $model, $data ) use ( &$captured ) {
				$captured[] = array( $model, $data );
			},
			10,
			2
		);

		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'HOOK-INS-1' );

		$this->assertCount( 1, $captured );
		$this->assertInstanceOf( Key::class, $captured[0][0] );
		$this->assertSame( $key->get_id(), $captured[0][0]->get_id() );
		$this->assertIsArray( $captured[0][1] );
		$this->assertSame( 'HOOK-INS-1', $captured[0][1]['serial_key'] );
		$this->assertSame( $key->get_id(), $captured[0][1]['id'] );
	}

	/**
	 * Inserting a key auto-enables serial numbers on its product.
	 *
	 * Regression: the b8 base fires `..._key_inserted` (passing the model), not
	 * the legacy `..._key_insert` (passing an id). Actions::enable_product must
	 * stay wired to the live hook so programmatic inserts (e.g. Pro CSV import)
	 * still mark the product as serial-enabled.
	 */
	public function testKeyInsertEnablesProductViaHook(): void {
		$product = $this->create_product( array( 'serial_enabled' => false ) );
		$this->assertSame( '', get_post_meta( $product->get_id(), '_is_serial_number', true ) );

		$this->make_available_key( $product->get_id(), 'HOOK-ENABLE-1' );

		$this->assertSame( 'yes', get_post_meta( $product->get_id(), '_is_serial_number', true ) );
	}

	/**
	 * key_updated fires with the model and only the changed columns.
	 */
	public function testUpdatedHookFiresWithChangedColumnsOnly(): void {
		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'HOOK-UPD-1' );

		$captured = array();
		add_action(
			'wc_serial_numbers_key_updated',
			function ( $model, $updates ) use ( &$captured ) {
				$captured[] = array( $model, $updates );
			},
			10,
			2
		);

		$reloaded = Key::find( $key->get_id() );
		$reloaded->set_activation_limit( 9 );
		$this->assertNotWPError( $reloaded->save() );

		$this->assertCount( 1, $captured );
		$this->assertInstanceOf( Key::class, $captured[0][0] );
		$this->assertSame( $key->get_id(), $captured[0][0]->get_id() );
		$this->assertArrayHasKey( 'activation_limit', $captured[0][1] );
		$this->assertSame( 9, (int) $captured[0][1]['activation_limit'] );
		$this->assertArrayNotHasKey( 'serial_key', $captured[0][1] );
		$this->assertArrayNotHasKey( 'status', $captured[0][1] );
	}

	/**
	 * key_saved fires on both insert and update with the model first.
	 */
	public function testSavedHookFiresOnInsertAndUpdate(): void {
		$fired = 0;
		add_action(
			'wc_serial_numbers_key_saved',
			function ( $model ) use ( &$fired ) {
				$this->assertInstanceOf( Key::class, $model );
				++$fired;
			}
		);

		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'HOOK-SAV-1' );
		$this->assertSame( 1, $fired );

		$reloaded = Key::find( $key->get_id() );
		$reloaded->set_validity( 15 );
		$this->assertNotWPError( $reloaded->save() );
		$this->assertSame( 2, $fired );
	}

	/**
	 * key_deleted fires with a still-usable model and the row data.
	 */
	public function testDeletedHookReceivesUsableModel(): void {
		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'HOOK-DEL-1' );
		$key_id  = $key->get_id();

		$captured = array();
		add_action(
			'wc_serial_numbers_key_deleted',
			function ( $model, $data ) use ( &$captured ) {
				$captured[] = array( $model, $data );
			},
			10,
			2
		);

		$this->assertTrue( $key->delete() );

		$this->assertCount( 1, $captured );
		$this->assertInstanceOf( Key::class, $captured[0][0] );
		$this->assertSame( $key_id, $captured[0][0]->get_id() );
		$this->assertSame( $product->get_id(), $captured[0][0]->get_product_id() );
		$this->assertIsArray( $captured[0][1] );
		$this->assertSame( $key_id, (int) $captured[0][1]['id'] );
	}

	/**
	 * Deleting a key cascades to its activations (Actions::delete_activations
	 * listens on wc_serial_numbers_key_deleted).
	 */
	public function testKeyDeleteCascadesActivations(): void {
		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'HOOK-CASC-1' );

		foreach ( array( 'inst-a', 'inst-b' ) as $instance ) {
			$activation = Activation::insert(
				array(
					'serial_id' => $key->get_id(),
					'instance'  => $instance,
				)
			);
			$this->assertNotWPError( $activation );
		}
		$this->assertSame( 2, Activation::count( array( 'serial_id' => $key->get_id() ) ) );

		$this->assertTrue( $key->delete() );

		$this->assertNull( Key::find( $key->get_id() ) );
		$this->assertSame( 0, Activation::count( array( 'serial_id' => $key->get_id() ) ) );
	}

	/**
	 * Activation insert/delete recounts the key activation_count
	 * (Actions::update_activation_count on activation_inserted/_deleted).
	 *
	 * The key must be sold: Key::save() resets activation_count to zero for
	 * available keys, so recounts only persist on sold keys.
	 */
	public function testActivationLifecycleRecountsKeyCount(): void {
		$product = $this->create_product();
		$order   = $this->create_order( $product, 1, array( 'status' => 'completed' ) );
		$key     = Key::insert(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'HOOK-CNT-1',
				'status'     => 'sold',
				'order_id'   => $order->get_id(),
			)
		);
		$this->assertNotWPError( $key );

		$activation = Activation::insert(
			array(
				'serial_id' => $key->get_id(),
				'instance'  => 'inst-count',
			)
		);
		$this->assertNotWPError( $activation );
		$this->assertSame( 1, Key::find( $key->get_id() )->get_activation_count() );

		$this->assertTrue( wcsn_delete_activation( $activation->get_id() ) );
		$this->assertSame( 0, Key::find( $key->get_id() )->get_activation_count() );
	}

	/**
	 * With stock management on, key insert and delete sync the WC product
	 * stock (Stocks::update_stocks on key_inserted/_deleted).
	 */
	public function testStockSyncOnKeyInsertAndDelete(): void {
		update_option( 'wcsn_manage_stocks', 'yes' );

		$product = $this->create_product( array( 'manage_stock' => true, 'stock_quantity' => 0 ) );

		$first = $this->make_available_key( $product->get_id(), 'HOOK-STK-1' );
		$this->assertSame( 1, (int) get_post_meta( $product->get_id(), '_stock', true ) );

		$this->make_available_key( $product->get_id(), 'HOOK-STK-2' );
		$this->assertSame( 2, (int) get_post_meta( $product->get_id(), '_stock', true ) );

		$this->assertTrue( wcsn_delete_key( $first->get_id() ) );
		$this->assertSame( 1, (int) get_post_meta( $product->get_id(), '_stock', true ) );

		delete_option( 'wcsn_manage_stocks' );
	}
}
