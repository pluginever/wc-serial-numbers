<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @since 1.4.2
 * @package WooCommerceSerialNumbers
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
		Cron::class,
		Cache::class,
		Encryption::class,
		Cart::class,
		Orders::class,
		Stocks::class,
		Actions::class,
		RestAPI::class,
		Frontend\Frontend::class,
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

		add_action( 'woocommerce_loaded', array( $this, 'plugins_loaded' ), 0 );
		add_filter( 'plugin_action_links_' . $this->basename(), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
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
	 * Add plugin action links.
	 *
	 * @since 1.0.0
	 * @param array<string, string> $links Plugin action links.
	 * @return array<string, string>
	 */
	public function plugin_action_links( array $links ): array {
		$settings = sprintf(
			'<a href="%s">%s</a>',
			esc_url( (string) $this->get( 'settings_url' ) ),
			esc_html__( 'Settings', 'wc-serial-numbers' )
		);

		return array_merge( array( 'settings' => $settings ), $links );
	}

	/**
	 * Add the plugin row meta links.
	 *
	 * @since 1.0.0
	 * @param array<int, string> $links Plugin row meta links.
	 * @param string             $file  Plugin file path relative to the plugins directory.
	 * @return array<int, string>
	 */
	public function plugin_row_meta( array $links, string $file ): array {
		if ( $file !== $this->basename() ) {
			return $links;
		}

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( (string) $this->get( 'docs_url' ) ),
			esc_html__( 'Documentation', 'wc-serial-numbers' )
		);

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( (string) $this->get( 'support_url' ) ),
			esc_html__( 'Support', 'wc-serial-numbers' )
		);

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( (string) $this->get( 'review_url' ) ),
			esc_html__( 'Review', 'wc-serial-numbers' )
		);

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( (string) $this->get( 'store_url' ) ),
			esc_html__( 'More Plugins', 'wc-serial-numbers' )
		);

		return $links;
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
