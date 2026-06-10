<?php

namespace WooCommerceSerialNumbers\Activations;

use WooCommerceSerialNumbers\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Activations
 */
class Admin extends Component {

	/**
	 * Whether to load.
	 *
	 * @since 2.4.0
	 * @return bool
	 */
	public function autoload(): bool {
		return is_admin();
	}

	/**
	 * Output activations page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_page() {
		$this->app->template->view( 'admin.list-activations' );
	}
}
