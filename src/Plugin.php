<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin.
 *
 * @since 1.4.2
 * @package WooCommerceSerialNumbers
 */
class Plugin extends Lib\Plugin {

	/**
	 * Plugin constructor.
	 *
	 * @param array $data The plugin data.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $data ) {
		parent::__construct( $data );
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function includes() {
		require_once __DIR__ . '/functions.php';
		require_once __DIR__ . '/Deprecated/Functions.php';
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		register_activation_hook( $this->get_file(), array( Installer::class, 'install' ) );
		add_action( 'admin_notices', array( $this, 'dependencies_notices' ) );
		add_action( 'before_woocommerce_init', array( $this, 'on_before_woocommerce_init' ) );
		add_action( 'woocommerce_loaded', array( $this, 'init' ), 0 );
	}

	/**
	 * Run on before WooCommerce init.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function on_before_woocommerce_init() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->get_file(), true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $this->get_file(), true );
		}
	}

	/**
	 * Missing dependencies notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function dependencies_notices() {
		if ( $this->is_plugin_active( 'woocommerce' ) ) {
			return;
		}
		$notice = sprintf(
		/* translators: 1: plugin name 2: WooCommerce */
			__( '%1$s requires %2$s to be installed and active.', 'wc-serial-numbers' ),
			'<strong>' . esc_html( $this->get_name() ) . '</strong>',
			'<strong>' . esc_html__( 'WooCommerce', 'wc-serial-numbers' ) . '</strong>'
		);

		echo '<div class="notice notice-error"><p>' . wp_kses_post( $notice ) . '</p></div>';
	}

	/**
	 * Init the plugin after plugins_loaded so environment variables are set.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		$this->services['installer']  = new Installer();
		$this->services['cron']       = new Cron();
		$this->services['cache']      = new Cache();
		$this->services['encryption'] = new Encryption();
		$this->services['orders']     = new Orders();
		$this->services['stocks']     = new Stocks();
		$this->services['actions']    = new Actions();
		$this->services['restapi']    = new RestAPI();
		$this->services['utilities']  = new Utilities\Utilities();
		$this->services['frontend']   = new Frontend\Frontend();

		// Compatibility.
		if ( 'yes' === get_option( 'wcsn_enable_pdf_invoices', 'no' ) ) {
			$this->services['compat'] = new Compat();
		}

		if ( wcsn_is_software_support_enabled() ) {
			$this->services['api'] = new API();
		}

		if ( self::is_request( 'admin' ) ) {
			$this->services['admin'] = new Admin\Admin();
		}

		// Init action.
		do_action( 'wc_serial_numbers_loaded' );
	}

	/**
	 * Determines if the pro version active.
	 *
	 * @since 1.0.0
	 * @return bool
	 * @deprecated 1.4.0
	 */
	public static function is_pro_active() {
		_deprecated_function( __METHOD__, '1.4.0', 'Plugin::is_premium_active()' );

		return self::$instance->is_premium_active();
	}

	/**
	 * Determines if the wc is active.
	 *
	 * @since 1.0.0
	 * @return bool
	 * @deprecated 1.4.0
	 */
	public function is_wc_active() {
		return $this->is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Plugin URL getter.
	 *
	 * @since 1.2.0
	 * @return string
	 * @deprecated 1.4.0
	 */
	public function plugin_url() {
		_deprecated_function( __METHOD__, '1.4.0', 'Plugin::get_url()' );

		return $this->get_url();
	}

	/**
	 * Plugin path getter.
	 *
	 * @since 1.2.0
	 * @return string
	 * @deprecated 1.4.0
	 */
	public function plugin_path() {
		_deprecated_function( __METHOD__, '1.4.0', 'Plugin::get_path()' );

		return $this->get_path();
	}

	/**
	 * Plugin base path name getter.
	 *
	 * @since 1.2.0
	 * @return string
	 * @deprecated 1.4.2
	 */
	public function plugin_basename() {
		_deprecated_function( __METHOD__, '1.4.2', 'Plugin::get_basename()' );

		return $this->get_basename();
	}
}
