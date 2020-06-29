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
		add_filter( 'manage_edit-shop_order_columns', array( __CLASS__, 'add_order_serial_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'add_order_serial_column_content' ), 20, 2 );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public static function includes() {
		require_once dirname( __FILE__ ) . '/class-wc-serial-numbers-admin-metaboxes.php';
		require_once dirname( __FILE__ ) . '/class-wc-serial-numbers-admin-settings.php';
		require_once dirname( __FILE__ ) . '/class-wc-serial-numbers-admin-menus.php';
		require_once dirname( __FILE__ ) . '/class-wc-serial-numbers-admin-notice.php';
		require_once dirname( __FILE__ ) . '/class-wc-serial-numbers-admin-actions.php';
		require_once dirname( __FILE__ ) . '/screen/class-wc-serial-numbers-activations-screen.php';
		require_once dirname( __FILE__ ) . '/screen/class-wc-serial-numbers-serial-numbers-screen.php';
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

		$css_url = wc_serial_numbers()->plugin_url() . '/assets/css';
		$js_url  = wc_serial_numbers()->plugin_url() . '/assets/js';
		$version = wc_serial_numbers()->get_version();


		wp_enqueue_style( 'wc-serial-numbers-admin', $css_url . '/wc-serial-numbers-admin.css', array( 'woocommerce_admin_styles', 'jquery-ui-style' ), $version );
		wp_enqueue_style( 'jquery-ui-style' );
		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'wc-serial-numbers-admin', $js_url . '/wc-serial-numbers-admin.js', [ 'jquery', 'wp-util', 'select2', ], $version, true );

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
	 * @param $columns
	 *
	 * @return array|string[]
	 * @since 1.2.0
	 */
	public static function add_order_serial_column( $columns ) {
		$postition = 3;
		$new       = array_slice( $columns, 0, $postition, true ) + array( 'order_serials' => '<span class="dashicons dashicons-lock"></span>' ) + array_slice( $columns, $postition, count( $columns ) - $postition, true );;

		return $new;
	}

	/**
	 * @param $column
	 * @param $order_id
	 *
	 * @since 1.2.0
	 */
	public static function add_order_serial_column_content( $column, $order_id ) {
		if ( $column == 'order_serials' ) {
			$total_ordered = wc_serial_numbers_order_has_serial_numbers( $order_id );
			if ( empty( $total_ordered ) ) {
				echo '&mdash;';
			} else {
				$total_connected = WC_Serial_Numbers_Query::init()->from( 'serial_numbers' )->where( 'order_id', intval( $order_id ) )->count();
				if ( $total_ordered == $total_connected ) {
					$style = "color:green";
					$title = __( 'Order assigned all serial numbers.', 'wc-serial-numbers' );
				} else if ( ! empty( $total_connected ) && $total_ordered !== $total_connected ) {
					$style = "color:#f39c12";
					$title = sprintf( __( 'Order partially missing serial numbers(%d)', 'wc-serial-numbers' ), $total_ordered );
				} else {
					$style = "color:red";
					$title = sprintf( __( 'Order missing serial numbers(%d)', 'wc-serial-numbers' ), $total_ordered );
				}
				$url = add_query_arg( [ 'order_id' => $order_id ], admin_url( 'admin.php?page=wc-serial-numbers' ) );
				echo sprintf( '<a href="%s" title="%s"><span class="dashicons dashicons-lock" style="%s"></span></a>', $url, $title, $style );

			}
		}
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

			._serial_key_source_field label {
				margin: 0 !important;
				width: 100% !important;
			}

			.wc-serial-numbers-upgrade-box {
				background: #f1f1f1;
				padding: 10px;
				border-left: 2px solid #007cba;
			}

			.wc-serial-numbers-variation-settings .wc-serial-numbers-settings-title {
				border-bottom: 1px solid #eee;
				padding-left: 0 !important;
				font-weight: 600;
				font-size: 1em;
				padding-bottom: 5px;
			}

			.wc-serial-numbers-variation-settings label, .wc-serial-numbers-variation-settings legend {
				margin-bottom: 5px !important;
				display: inline-block;
			}

			.wc-serial-numbers-variation-settings .wc-radios li {
				padding-bottom: 0 !important;

			}

			.wc-serial-numbers-variation-settings .woocommerce-help-tip {
				margin-top: -5px;
			}

			.wc-serial-numbers-variation-settings .short {
				min-width: 200px;
			}
		</style>
		<?php
		$style = ob_get_contents();
		ob_get_clean();
		echo $style;
	}
}

new WC_Serial_Numbers_Admin();
