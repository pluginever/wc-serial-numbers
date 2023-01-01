<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\Lib\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * Class Notices.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Notices extends Singleton {
	/**
	 * Notices container.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $notices = array();

	/**
	 * Notices constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'admin_init', [ $this, 'add_notices' ] );
		add_action( 'admin_notices', [ $this, 'output_notices' ] );
	}

	/**
	 * Admin notices.
	 *
	 * @since 1.0.0
	 */
	public function add_notices() {
		$notice = array(
			'type'    => 'error',
			'message' => sprintf(
			/* translators: %1$s: link to the plugin page, %2$s: link to the plugin page */
				__( '%s is not functional because you are using outdated version of the plugin, please update to the version 1.1.9 or higher.', 'wc-serial-numbers' ),
				'<a href="' . esc_url( wc_serial_numbers()->get_data( 'premium_url' ) ) . '" target="_blank">WooCommerce Serial Numbers Pro</a>'
			),
		);
	}

	/**
	 * Admin notices.
	 *
	 * @since 1.0.0
	 */
	public function output_notices() {
		foreach ( $this->notices as $notice ) {
			?>
			<div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?>">
				<p><?php echo wp_kses_post( $notice['message'] ); ?></p>
			</div>
			<?php
		}
	}
}
