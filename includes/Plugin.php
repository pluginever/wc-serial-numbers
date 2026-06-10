<?php

namespace PluginEver\SerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @since 1.4.2
 * @package PluginEver\SerialNumbers
 */
class Plugin extends B8\App {

	/**
	 * Components to register.
	 *
	 * @since 2.4.0
	 * @var array<int|string, class-string>
	 */
	protected array $components = array(
		Installer::class,
		Encryption::class,
		Cart::class,
		Shop::class,
		Keys\Keys::class,
		Activations\Activations::class,
		Orders\Orders::class,
		Stocks\Stocks::class,
		Products::class,
		RestAPI\Routes::class,
		Compat::class,
		API::class,
		Admin\Admin::class,
	);

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function bootstrap(): void {
		define( 'WC_SERIAL_NUMBERS_VERSION', $this->version );
		define( 'WC_SERIAL_NUMBERS_FILE', $this->file );

		add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility' ) );
		add_action( 'woocommerce_loaded', array( $this, 'plugins_loaded' ), 0 );
	}

	/**
	 * Declare WooCommerce compatibility.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function declare_compatibility(): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->file, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $this->file, true );
		}
	}

	/**
	 * Initialize the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function plugins_loaded(): void {
		$this->boot( $this->components );

		/**
		 * Fires after the plugin has booted its components.
		 *
		 * @since 1.0.0
		 */
		$this->do_action( 'loaded' );
	}

	/**
	 * Whether the Pro add-on is active.
	 *
	 * @since 2.4.0
	 * @return bool True when the Pro add-on is active.
	 */
	public function is_pro_active(): bool {
		return ! empty( $this->pro_basename ) && $this->plugin_active( $this->pro_basename );
	}

	/**
	 * Gets the plugin version.
	 *
	 * @since 1.0.0
	 * @deprecated 2.4.0
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}
}
