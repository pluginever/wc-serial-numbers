<?php

namespace WooCommerceSerialNumbers\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Settings {

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Init settings.
	 *
	 * @since 1.0.0
	 * @return self
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Settings constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'buffer_start' ) );
		add_action( 'admin_init', array( $this, 'save_settings' ), 1 );
	}

	/**
	 * Get settings tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_tabs() {
		$tabs = array(
			'general' => __( 'General', 'wc-serial-numbers' ),
		);

		return apply_filters( 'wc_serial_numbers_settings_tabs', $tabs );
	}

	/**
	 * Get settings.
	 *
	 * @param string $tab Current tab.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings( $tab ) {
		$all_settings = include WCSN()->plugin_path( 'src/Data/settings.php' );
		$settings     = $all_settings[ $tab ] ?? array();

		/**
		 * Filter the settings for the plugin.
		 *
		 * @param array $settings The settings.
		 *
		 * @deprecated 1.4.1
		 */
		$settings = apply_filters( 'wc_serial_numbers_' . $tab . '_settings_fields', $settings );

		return apply_filters( 'wc_serial_numbers_get_settings_' . $tab, $settings );
	}

	/**
	 * Buffer start.
	 *
	 * @since 1.0.0
	 */
	public function buffer_start() {
		ob_start();
	}

	/**
	 * Save settings.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function save_settings() {
		$class_name = get_called_class();
		if ( empty( $_POST ) || ! isset( $_POST[ $class_name ] ) ) {
			return false;
		}
		check_admin_referer( $class_name );
		$current_tab = $this->get_current_tab();
		$settings    = $this->get_settings( $current_tab );
		if ( $this->save_fields( $settings ) ) {
			add_settings_error( $class_name, 'response', __( 'Settings saved.', 'wc-serial-numbers' ), 'updated' );
			return true;
		}

		return false;
	}

	/**
	 * Output settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function output() {
		self::instance()->output_settings();
	}

	/**
	 * Output settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_settings() {
		$tabs        = $this->get_tabs();
		$current_tab = $this->get_current_tab();
		$tab_exists  = isset( $tabs[ $current_tab ] );
		$settings    = $this->get_settings( $current_tab );
		if ( ! empty( $tabs ) && ! $tab_exists && ! headers_sent() ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . $this->get_current_page() ) );
			exit();
		}
		?>
		<div class="wrap b8-wrap woocommerce">
			<nav class="nav-tab-wrapper b8-navbar">
				<?php $this->output_tabs( $tabs ); ?>
			</nav>
			<hr class="wp-header-end">
			<div class="b8-poststuff">
				<div class="column-1">
					<?php $this->output_form( $settings ); ?>
				</div>
				<div class="column-2">
					<?php $this->output_premium_widget(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Output tabs.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_tabs( $tabs ) {
		foreach ( $tabs as $tab_id => $tab_name ) {
			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->get_current_page() . '&tab=' . $tab_id ) ); ?>" class="nav-tab <?php echo esc_attr( $this->get_current_tab() === $tab_id ? 'nav-tab-active' : '' ); ?>">
				<?php echo esc_html( $tab_name ); ?>
			</a>
			<?php
		}
		if ( WCSN()->docs_url ) {
			printf( '<a href="%s" class="nav-tab" target="_blank">%s</a>', esc_url( WCSN()->docs_url ), esc_html__( 'Documentation', 'wc-serial-numbers' ) );
		}
	}

	/**
	 * Output settings form.
	 *
	 * @param array $settings Settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_form( $settings ) {
		if ( ! empty( $settings ) ) {
			$class_name = get_called_class();
			settings_errors( $class_name );
			?>
			<form method="post" id="mainform" action="" enctype="multipart/form-data">
				<?php $this->output_fields( $settings ); ?>
				<?php wp_nonce_field( $class_name ); ?>
				<?php submit_button( null, 'primary', $class_name ); ?>
			</form>
			<?php
		}
	}

	/**
	 * Output admin fields.
	 *
	 * @param array[] $options Options array to output.
	 */
	public function output_fields( $options ) {
		if ( function_exists( 'woocommerce_admin_fields' ) ) {
			woocommerce_admin_fields( $options );
		}
	}

	/**
	 * Save admin fields.
	 *
	 * @param array $options Options array to output.
	 * @param array $data    Optional. Data to use for saving. Defaults to $_POST.
	 * @return bool
	 */
	public function save_fields( $options, $data = null ) {
		if ( class_exists( '\WC_Admin_Settings' ) && ! empty( $options ) && \WC_Admin_Settings::save_fields( $options, $data ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Output premium widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_premium_widget() {
		if ( WCSN()->utils->plugin_active( 'wc-serial-numbers-pro' ) ) {
			return;
		}
		$features = array(
			__( 'Create and assign keys for WooCommerce variable products.', 'wc-serial-numbers' ),
			__( 'Generate bulk keys with your custom key generator rule.', 'wc-serial-numbers' ),
			__( 'Random & sequential key order for the generator rules.', 'wc-serial-numbers' ),
			__( 'Automatic key generator to auto-create & assign keys with orders.', 'wc-serial-numbers' ),
			__( 'License key management option from the order page with required actions.', 'wc-serial-numbers' ),
			__( 'Support for bulk import/export of keys from/to CSV.', 'wc-serial-numbers' ),
			__( 'Send keys via SMS with Twilio.', 'wc-serial-numbers' ),
			__( 'Option to sell keys even if there are no available keys in the stock.', 'wc-serial-numbers' ),
			__( 'Custom deliverable quantity to deliver multiple keys with a single product.', 'wc-serial-numbers' ),
			__( 'Manual delivery option to manually deliver license keys instead of automatic.', 'wc-serial-numbers' ),
			__( 'Email template to easily and quickly customize the order confirmation & low stock alert email.', 'wc-serial-numbers' ),
			__( 'Many more ...', 'wc-serial-numbers' ),
		);
		?>
		<div class="b8-card promo-panel">
			<div class="b8-card__header">
				<h3><?php esc_html_e( 'Want More?', 'wc-serial-numbers' ); ?></h3>
			</div>
			<div class="b8-card__body">
				<p><?php esc_attr_e( 'This plugin offers a premium version which comes with the following features:', 'wc-serial-numbers' ); ?></p>
				<ul>
					<?php foreach ( $features as $feature ) : ?>
						<li>- <?php echo esc_html( $feature ); ?></li>
					<?php endforeach; ?>
				</ul>
				<a href="https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=plugin-settings&utm_medium=banner&utm_campaign=upgrade&utm_id=wc-serial-numbers" class="button" target="_blank"><?php esc_html_e( 'Upgrade to PRO', 'wc-serial-numbers' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Save default settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_defaults() {
		$tabs = $this->get_tabs();
		foreach ( $tabs as $tab => $label ) {
			$options = $this->get_settings( $tab );

			foreach ( $options as $option ) {
				if ( isset( $option['default'] ) && isset( $option['id'] ) ) {
					$autoload = isset( $option['autoload'] ) ? (bool) $option['autoload'] : true;
					add_option( $option['id'], $option['default'], '', $autoload );
				}
			}
		}
	}

	/**
	 * Get current page.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_current_page() {
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS );
		return ! empty( $page ) ? sanitize_text_field( wp_unslash( $page ) ) : '';
	}

	/**
	 * Get the current tab.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_current_tab() {
		$tabs = $this->get_tabs();
		$tab  = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS );
		$tab  = ! empty( $tab ) ? sanitize_text_field( wp_unslash( $tab ) ) : '';

		if ( ! array_key_exists( $tab, $tabs ) ) {
			$tab = key( $tabs );
		}

		return $tab;
	}
}
