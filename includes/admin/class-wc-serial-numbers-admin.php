<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Admin {

	/**
	 * WC_Serial_Numbers_Admin constructor.
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'includes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_head', array( __CLASS__, 'print_style' ) );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public static function includes() {
		require_once dirname( __FILE__ ) . '/class-wc-serial-numbers-admin-metaboxes.php';
		require_once dirname( __FILE__ ) . '/class-wc-serial-numbers-admin-settings.php';
		require_once dirname( __FILE__ ) . '/class-wc-serial-numbers-admin-menus.php';
	}

	/**
	 * Enqueue admin related assets
	 *
	 * @param $hook
	 *
	 * @since 1.2.0
	 */
	public function admin_scripts( $hook ) {
		if ( ! wc_serial_numbers()->is_wc_active() ) {
			return;
		}

		$css_url = wc_serial_numbers()->plugin_url() . '/assets/admin/css';
		$js_url  = wc_serial_numbers()->plugin_url() . '/assets/admin/js';
		$version = wc_serial_numbers()->get_version();

//		wp_register_style( 'serial-list-tables', $css_url . '/list-tables.css', array(), $version );
		wp_enqueue_style( 'wc-serial-numbers-admin', $css_url . '/wc-serial-numbers-admin.css', array( 'woocommerce_admin_styles', 'jquery-ui-style' ), $version );
		wp_enqueue_style( 'jquery-ui-style' );
		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'wc-serial-numbers-admin', $js_url . '/wc-serial-numbers.js', [ 'jquery', 'wp-util', 'select2', ], $version, true );

		wp_localize_script( 'wc-serial-numbers-admin', 'wc_serial_numbers_admin_i10n', array(
			'i18n'    => array(
				'search_product' => __( 'Search product by name', 'wc-serial-numbers' ),
				'search_order'   => __( 'Search order', 'wc-serial-numbers' ),
				'show'           => __( 'Show', 'wc-serial-numbers' ),
				'hide'           => __( 'Hide', 'wc-serial-numbers' ),
			),
			'nonce'   => wp_create_nonce( 'wc_serial_numbers_admin_js_nonce' ),
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		) );
	}

	/**
	 * Print style
	 *
	 * @since 1.0.0
	 */
	public static function print_style() {
		ob_start();
		?>
		<style>
			#woocommerce-product-data ul.wc-tabs li.wc_serial_numbers_options a:before {
				font-family: 'dashicons';
				content: "\f112";
			}

			._serial_key_source_field {

			label {
				margin: 0 !important;
				width: 100% !important;
			}

			}
		</style>
		<?php
		$style = ob_get_contents();
		ob_get_clean();
		echo $style;
	}
}

new WC_Serial_Numbers_Admin();
