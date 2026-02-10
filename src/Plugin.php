<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin.
 *
 * @since   1.4.2
 * @package WooCommerceSerialNumbers
 */
final class Plugin extends B8\Plugin\App {

	/**
	 * Bootstraps the plugin.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	protected function bootstrap(): void {
		define( 'WCSN_VERSION', $this->version );
		define( 'WCSN_FILE', $this->file );
		define( 'WCSN_PATH', $this->plugin_path() );
		define( 'WCSN_URL', $this->plugin_url() );
		define( 'WCSN_ASSETS_URL', $this->assets_url() );
		define( 'WCSN_ASSETS_PATH', $this->assets_path() );

		register_activation_hook( $this->file, array( Installer::class, 'install' ) );
		add_action( 'admin_notices', array( $this, 'dependencies_notices' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility' ) );
		add_action( 'woocommerce_loaded', array( $this, 'register_services' ), 0 );
		add_filter( 'plugin_action_links_' . $this->basename(), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
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
	 * Missing dependencies notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function dependencies_notices(): void {
		if ( $this->utils->plugin_active( 'woocommerce' ) ) {
			return;
		}
		$notice = sprintf(
		/* translators: 1: plugin name 2: WooCommerce */
			__( '%1$s requires %2$s to be installed and active.', 'wc-serial-numbers' ),
			'<strong>' . esc_html( $this->plugin_name ) . '</strong>',
			'<strong>' . esc_html__( 'WooCommerce', 'wc-serial-numbers' ) . '</strong>'
		);

		echo '<div class="notice notice-error"><p>' . wp_kses_post( $notice ) . '</p></div>';
	}

	/**
	 * Register plugin services.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_services(): void {
		$this->make( Installer::class );
		$this->make( Cron::class );
		$this->make( Cache::class );
		$this->make( Encryption::class );
		$this->make( Orders::class );
		$this->make( Stocks::class );
		$this->make( Actions::class );
		$this->make( RestAPI::class );
		$this->make( Utilities\Utilities::class );
		$this->make( Frontend\Frontend::class );

		if ( 'yes' === get_option( 'wcsn_enable_pdf_invoices', 'no' ) ) {
			$this->make( Compat::class );
		}

		if ( wcsn_is_software_support_enabled() ) {
			$this->make( API::class );
		}

		if ( is_admin() ) {
			$this->make( Admin\Admin::class );
		}

		do_action( 'wc_serial_numbers_loaded' );
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links Plugin action links.
	 *
	 * @since 2.3.2
	 * @return array
	 */
	public function plugin_action_links( array $links ): array {
		$plugin_links = array(
			'settings' => sprintf(
				'<a href="%s">%s</a>',
				esc_url( $this->settings_url ),
				esc_html__( 'Settings', 'wc-serial-numbers' )
			),
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Add plugin row meta links.
	 *
	 * @param array  $links Plugin row meta links.
	 * @param string $file  Plugin file.
	 *
	 * @since 2.3.2
	 * @return array
	 */
	public function plugin_row_meta( array $links, string $file ): array {
		if ( $this->basename() !== $file ) {
			return $links;
		}

		$row_meta = array(
			'docs'    => sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( $this->docs_url ),
				esc_html__( 'Documentation', 'wc-serial-numbers' )
			),
			'support' => sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( $this->support_url ),
				esc_html__( 'Support', 'wc-serial-numbers' )
			),
		);

		return array_merge( $links, $row_meta );
	}

	/**
	 * Determines if the pro version active.
	 *
	 * @since 1.0.0
	 * @return bool
	 * @deprecated 1.4.0
	 */
	public static function is_pro_active() {
		_deprecated_function( __METHOD__, '1.4.0' );

		return WCSN()->utils->plugin_active( 'wc-serial-numbers-pro' );
	}
}
