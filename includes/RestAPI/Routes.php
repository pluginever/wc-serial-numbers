<?php

namespace WooCommerceSerialNumbers\RestAPI;

use WooCommerceSerialNumbers\B8\Component;
use WooCommerceSerialNumbers\RestAPI\Controllers\Activations;
use WooCommerceSerialNumbers\RestAPI\Controllers\Keys;
use WooCommerceSerialNumbers\RestAPI\Controllers\Software;

defined( 'ABSPATH' ) || exit;

/**
 * REST API routes registration.
 *
 * @since 2.4.0
 * @package WooCommerceSerialNumbers\RestAPI
 */
class Routes extends Component {

	/**
	 * Register hooks.
	 *
	 * @since 2.4.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register all plugin REST API routes.
	 *
	 * @since 2.4.0
	 *
	 * @return void
	 */
	public function register_routes(): void {
		$router = $this->app->router;

		// Resources.
		$router->resource( 'keys', Keys::class );
		$router->resource( 'activations', Activations::class );

		// Software licensing endpoints, registered under the legacy unversioned namespace.
		$this->app->make( Software::class )->register_routes();
	}
}
