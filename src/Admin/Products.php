<?php

namespace WooCommerceSerialNumbers\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Products.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Products extends \WooCommerceSerialNumbers\Lib\Singleton {

	/**
	 * Products constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_write_panel' ) );
		add_filter( 'woocommerce_process_product_meta', array( __CLASS__, 'product_save_data' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'variable_product_content' ), 10, 3 );
		add_action( 'admin_head', array( __CLASS__, 'print_style' ) );
	}

	/**
	 * Add product data tab.
	 *
	 * @param array $tabs product tabs.
	 *
	 * since 1.0.0
	 */
	public static function product_data_tab( $tabs ) {
		$tabs['wc_serial_numbers'] = array(
			'label'    => __( 'Serial Numbers', 'wc-serial-numbers' ),
			'target'   => 'wc_serial_numbers_data',
			'class'    => array( 'show_if_simple' ),
			'priority' => 11,
		);

		return $tabs;
	}

	/**
	 * since 1.0.0
	 */
	public static function product_write_panel() {
		global $post, $woocommerce;
		include 'Views/html-product-options.php';
	}

	/**
	 * since 1.0.0
	 */
	public static function product_write_panel_bk() {
		global $post, $woocommerce;
		?>
		<div id="wc_serial_numbers_data" class="panel woocommerce_options_panel show_if_simple" style="padding-bottom: 50px;display: none;">
			<?php
			woocommerce_wp_checkbox(
				array(
					'id'            => '_is_serial_number',
					'label'         => __( 'Sell serial keys', 'wc-serial-numbers' ),
					'description'   => __( 'Enable this if you are selling serial keys for this product.', 'wc-serial-numbers' ),
					'value'         => get_post_meta( $post->ID, '_is_serial_number', true ),
					'wrapper_class' => 'options_group',
					'desc_tip'      => true,
				)
			);

			$delivery_quantity = (int) get_post_meta( $post->ID, '_delivery_quantity', true );
			woocommerce_wp_text_input(
				apply_filters(
					'wc_serial_numbers_delivery_quantity_field_args',
					array(
						'id'                => '_delivery_quantity',
						'label'             => __( 'Delivery quantity', 'wc-serial-numbers' ),
						'description'       => __( 'Number of serial key(s) will be delivered per item. Available in PRO.', 'wc-serial-numbers' ),
						'value'             => empty( $delivery_quantity ) ? 1 : $delivery_quantity,
						'type'              => 'number',
						'wrapper_class'     => 'options_group',
						'desc_tip'          => true,
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
					)
				)
			);

			$source  = get_post_meta( $post->ID, '_serial_key_source', true );
			$sources = wc_serial_numbers_get_key_sources();
			if ( count( $sources ) > 1 ) {
				woocommerce_wp_radio(
					array(
						'id'            => '_serial_key_source',
						'name'          => '_serial_key_source',
						'class'         => 'serial_key_source',
						'label'         => __( 'Serial key source', 'wc-serial-numbers' ),
						'value'         => empty( $source ) ? 'custom_source' : $source,
						'wrapper_class' => 'options_group',
						'options'       => $sources,
					)
				);
				foreach ( array_keys( $sources ) as $key_source ) {
					do_action( 'wc_serial_numbers_source_settings_' . $key_source, $post->ID );
					do_action( 'wc_serial_numbers_source_settings', $key_source, $post->ID );
				}
			}

			do_action( 'wc_serial_numbers_simple_product_metabox', $post );

			if ( ! wc_serial_numbers_software_support_disabled() ) {
				woocommerce_wp_text_input(
					array(
						'id'            => '_software_version',
						'label'         => __( 'Software version', 'wc-serial-numbers' ),
						'description'   => __( 'Version number for the software. Ignore if it\'s not a software product.', 'wc-serial-numbers' ),
						'placeholder'   => __( 'e.g. 1.0', 'wc-serial-numbers' ),
						'wrapper_class' => 'options_group',
						'desc_tip'      => true,
					)
				);
			}
			$stocks = wcsn_get_stocks_count();
			$stock  = isset( $stocks[ $post->ID ] ) ? $stocks[ $post->ID ] : 0;

			echo sprintf(
				'<p class="wcsn-key-source-based-field form-field options_group" data-source="custom_source"><label>%s</label><span class="description">%d %s</span></p>',
				__( 'Stock status', 'wc-serial-numbers' ),
				$stock,
				_n( 'serial key', 'serial keys', $stock, 'wc-serial-numbers' )
			);
			if ( ! wc_serial_numbers()->is_premium_active() ) {
				echo sprintf( '<p class="wc-serial-numbers-upgrade-box">%s <a href="%s" target="_blank" class="button">%s</a></p>', __( 'Want serial keys to be generated automatically and auto assign with order and many more?', 'wc-serial-numbers' ), 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro', __( 'Upgrade to Pro', 'wc-serial-numbers' ) );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Show promo box.
	 *
	 * @param $variation_data
	 * @param $variation
	 *
	 * @param $loop
	 *
	 * @since 1.2.0
	 */
	public static function variable_product_content( $loop, $variation_data, $variation ) {
		if ( ! wc_serial_numbers()->is_premium_active() ) {
			echo sprintf( '<p class="wc-serial-numbers-upgrade-box">%s <a href="%s" target="_blank" class="button">%s</a></p>', __( 'The free version of Serial Numbers for WooCommerce does not support product variation.', 'wc-serial-numbers' ), 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro', __( 'Upgrade to Pro', 'wc-serial-numbers' ) );
		}

	}

	/**
	 * since 1.0.0
	 */
	public static function product_save_data() {
		global $post;
		$status = isset( $_POST['_is_serial_number'] ) ? 'yes' : 'no';
		$source = isset( $_POST['_serial_key_source'] ) ? sanitize_text_field( $_POST['_serial_key_source'] ) : 'custom_source';
		update_post_meta( $post->ID, '_is_serial_number', $status );
		update_post_meta( $post->ID, '_serial_key_source', $source );
		// save only if software licensing enabled
		if ( ! wc_serial_numbers_software_support_disabled() ) {
			update_post_meta( $post->ID, '_software_version', ! empty( $_POST['_software_version'] ) ? sanitize_text_field( $_POST['_software_version'] ) : '' );
		}

		do_action( 'wcsn_save_simple_product_meta', $post );
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
