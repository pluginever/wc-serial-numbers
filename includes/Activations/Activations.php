<?php

namespace WooCommerceSerialNumbers\Activations;

use WooCommerceSerialNumbers\B8\Component;
use WooCommerceSerialNumbers\Models\Activation;
use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Activations.
 *
 * @since 1.5.6
 * @package WooCommerceSerialNumbers\Activations
 */
class Activations extends Component {

	/**
	 * Child components.
	 *
	 * @since 2.4.0
	 * @var array<int|string, class-string>
	 */
	public array $components = array(
		Admin::class,
	);

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'wc_serial_numbers_activation_inserted', array( $this, 'update_activation_count' ) );
		add_action( 'wc_serial_numbers_activation_deleted', array( $this, 'update_activation_count' ) );
	}

	/**
	 * Update activation count.
	 *
	 * @param Activation $activation The activation object.
	 *
	 * @since 1.0.0
	 */
	public function update_activation_count( $activation ) {
		$key = Key::get( $activation->get_serial_id() );
		if ( $key ) {
			$key->recount_remaining_activation();
		}
	}
}
