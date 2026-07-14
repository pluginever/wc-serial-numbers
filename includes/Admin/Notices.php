<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the admin notices.
 *
 * @since   2.4.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Notices extends Component {

	/**
	 * Register hooks.
	 *
	 * @since 2.4.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_notices' ) );
	}

	/**
	 * Register the admin notices.
	 *
	 * @since 2.4.0
	 * @return void
	 */
	public function register_notices(): void {
		$is_outdated_pro = defined( 'WC_SERIAL_NUMBER_PRO_PLUGIN_VERSION' ) && version_compare( WC_SERIAL_NUMBER_PRO_PLUGIN_VERSION, '1.4.0', '<' );
		if ( ! $is_outdated_pro ) {
			$is_outdated_pro = function_exists( 'wc_serial_numbers_pro' ) && is_callable( array( wc_serial_numbers_pro(), 'get_version' ) ) && wc_serial_numbers_pro()->get_version() && version_compare( wc_serial_numbers_pro()->get_version(), '1.4.0', '<' );
		}

		if ( $is_outdated_pro ) {
			$this->app->notices->add(
				array(
					'notice_id'   => 'wc_serial_numbers_outdated_pro',
					'type'        => 'error',
					'dismissible' => false,
					'message'     => sprintf(
					/* translators: %s: link to the plugin page */
						__( '%s is not functional because you are using outdated version of the plugin, please update to the version 1.3.8 or higher.', 'wc-serial-numbers' ),
						'<a href="' . esc_url( $this->app->get( 'upgrade_url' ) ) . '" target="_blank">Serial Numbers Pro</a>'
					),
				)
			);
		}
	}
}
