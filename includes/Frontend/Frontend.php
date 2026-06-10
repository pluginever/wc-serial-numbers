<?php
namespace WooCommerceSerialNumbers\Frontend;

use WooCommerceSerialNumbers\B8\Component;
defined( 'ABSPATH' ) || exit;

/**
 * Class Frontend.
 *
 * This class is responsible for all frontend functionality.
 *
 * @since   1.5.6
 * @package WooCommerceSerialNumbers\Frontend
 */
class Frontend extends Component {

	/**
	 * Child components.
	 *
	 * @since 1.5.6
	 * @var array<int|string, class-string>
	 */
	public array $components = array(
		Shortcodes::class,
	);

	/**
	 * Register hooks.
	 *
	 * @since 1.5.6
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wc_serial_numbers_before_display_order_keys', 'wcsn_display_order_keys_title', 10, 2 );
		add_action( 'wc_serial_numbers_display_order_keys', 'wcsn_display_order_keys_table', 10, 2 );
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * @since 1.5.6
	 * @return void
	 */
	public function enqueue_scripts() {
		$this->app->scripts->enqueue_style( 'wc-serial-numbers-frontend', 'css/frontend-style.css' );
		$this->app->scripts->enqueue_script( 'wc-serial-numbers-frontend', 'js/frontend-script.js', array( 'jquery' ) );
		wp_localize_script(
			'wc-serial-numbers-frontend',
			'wc_serial_numbers_frontend_vars',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'i18n'     => array(
					'copied'  => __( 'Copied', 'wc-serial-numbers' ),
					'loading' => __( 'Loading', 'wc-serial-numbers' ),
				),
			)
		);
	}
}
