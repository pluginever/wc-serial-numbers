<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;

/**
 * Base test case for Serial Numbers tests.
 */
abstract class TestCase extends \WP_UnitTestCase {

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$this->reset_state();
	}

	/**
	 * Tear down test fixtures.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		$this->reset_state();
		parent::tearDown();
	}

	/**
	 * Empty the keys tables and clear the stock transient.
	 *
	 * Uses DELETE instead of TRUNCATE — TRUNCATE is DDL and would commit the
	 * test transaction, leaking fixtures into the shared test database.
	 *
	 * @return void
	 */
	protected function reset_state(): void {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->prefix}serial_numbers" );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}serial_numbers_activations" );
		delete_transient( 'wcsn_products_stock_count' );
	}

	/**
	 * Create a simple product enabled for serial numbers.
	 *
	 * @param array $args Extra product args.
	 * @return \WC_Product_Simple
	 */
	protected function create_product( array $args = array() ): \WC_Product_Simple {
		$product = new \WC_Product_Simple();
		$product->set_name( $args['name'] ?? 'Test Software' );
		$product->set_regular_price( $args['price'] ?? '10' );
		$product->set_status( 'publish' );
		$product->save();

		update_post_meta( $product->get_id(), '_is_serial_number', 'yes' );
		update_post_meta( $product->get_id(), '_serial_key_source', 'custom_source' );

		return $product;
	}

	/**
	 * Create a saved available key for a product.
	 *
	 * @param array $args Key args (serial_key, product_id, status, ...).
	 * @return Key
	 */
	protected function make_key( array $args = array() ): Key {
		if ( empty( $args['product_id'] ) ) {
			$args['product_id'] = $this->create_product()->get_id();
		}

		$key = wcsn_insert_key(
			wp_parse_args(
				$args,
				array(
					'serial_key' => 'KEY-' . wp_generate_password( 12, false ),
				)
			)
		);
		$this->assertNotWPError( $key );

		return $key;
	}
}
