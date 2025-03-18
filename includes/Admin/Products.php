<?php

namespace WooCommerceSerialNumbers\Admin;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Class Products.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Products {

	/**
	 * Products constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_head', array( __CLASS__, 'print_style' ) );
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_write_panel' ) );
		add_filter( 'woocommerce_process_product_meta', array( __CLASS__, 'product_save_data' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'variable_product_content' ), 10, 3 );
	}

	/**
	 * Print style
	 *
	 * @since 1.0.0
	 */
	public static function print_style() {
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
	}

	/**
	 * Add product data tab.
	 *
	 * @param array $tabs product data tabs.
	 *
	 * @return mixed
	 */
	public static function product_data_tab( $tabs ) {
		$tabs['wc_serial_numbers'] = apply_filters(
			'wc_serial_numbers_product_data_tab',
			array(
				'label'    => __( 'Serial Numbers', 'wc-serial-numbers' ),
				'target'   => 'wc_serial_numbers_data',
				'class'    => array( 'show_if_simple', 'hide_if_subscription', 'hide_if_variable-subscription' ),
				'priority' => 11,
			)
		);

		return $tabs;
	}

	/**
	 * since 1.0.0
	 */
	public static function product_write_panel() {
		global $post, $woocommerce;
		?>
		<div id="wc_serial_numbers_data" class="panel woocommerce_options_panel show_if_simple"
			style="padding-bottom: 50px;display: none;">
			<?php
			woocommerce_wp_checkbox(
				array(
					'id'            => '_is_serial_number',
					'label'         => __( 'Sell keys', 'wc-serial-numbers' ),
					'description'   => __( 'Enable this if you are selling keys with this product.', 'wc-serial-numbers' ),
					'value'         => get_post_meta( $post->ID, '_is_serial_number', true ),
					'wrapper_class' => 'options_group',
					'desc_tip'      => false,
				)
			);

			$delivery_quantity = (int) get_post_meta( $post->ID, '_delivery_quantity', true );
			woocommerce_wp_text_input(
				apply_filters(
					'wc_serial_numbers_delivery_quantity_field_args',
					array(
						'id'                => '_delivery_quantity',
						'label'             => __( 'Delivery quantity', 'wc-serial-numbers' ),
						'description'       => __( 'Number of key(s) will be delivered per item. Available in PRO.', 'wc-serial-numbers' ),
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
			$sources = wcsn_get_key_sources();
			if ( count( $sources ) >= 1 ) {
				woocommerce_wp_radio(
					array(
						'id'            => '_serial_key_source',
						'name'          => '_serial_key_source',
						'class'         => 'serial_key_source',
						'label'         => __( 'Key source', 'wc-serial-numbers' ),
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

			if ( wcsn_is_software_support_enabled() ) {
				woocommerce_wp_text_input(
					array(
						'id'            => '_software_version',
						'label'         => __( 'Software version', 'wc-serial-numbers' ),
						'description'   => __( 'Version number for the software. Ignore if it\'s not a software.', 'wc-serial-numbers' ),
						'placeholder'   => __( 'e.g. 1.0', 'wc-serial-numbers' ),
						'wrapper_class' => 'options_group',
						'desc_tip'      => true,
					)
				);
			}
			$stocks = wcsn_get_stocks_count();
			$stock  = isset( $stocks[ $post->ID ] ) ? $stocks[ $post->ID ] : 0;

			echo wp_kses_post(
				sprintf(
					'<p class="wcsn-key-source-based-field form-field options_group" data-source="custom_source"><label>%s</label><span class="description">%d %s</span></p>',
					__( 'Key source', 'wc-serial-numbers' ),
					$stock,
					_n( 'key available.', 'keys available.', $stock, 'wc-serial-numbers' )
				)
			);

			if ( ! WCSN()->is_premium_active() ) {
				echo wp_kses_post(
					sprintf(
						'<p class="wc-serial-numbers-upgrade-box">%s <a href="%s" target="_blank" class="button">%s</a></p>',
						__( 'Want to sell keys for variable products?', 'wc-serial-numbers' ),
						'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro',
						__( 'Upgrade to Pro', 'wc-serial-numbers' ),
					)
				);
			}
			?>
		</div>
		<?php
	}

	/**
	 * Show promo box.
	 *
	 * @since 1.2.0
	 */
	public static function variable_product_content() {
		if ( ! WCSN()->is_premium_active() ) {
			echo wp_kses_post(
				sprintf(
					'<p class="wc-serial-numbers-upgrade-box">%s <a href="%s" target="_blank" class="button">%s</a></p>',
					__( 'The free version of Serial Numbers for WooCommerce does not support product variation.', 'wc-serial-numbers' ),
					'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro',
					__( 'Upgrade to Pro', 'wc-serial-numbers' )
				)
			);
		}
	}

	/**
	 * since 1.0.0
	 */
	public static function product_save_data() {
		global $post;
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {
			return;
		}

		// Must have manage woocommerce user capability role to save this data.
		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			WCSN()->add_notice( __( 'You do not have permission to save this data.', 'wc-serial-numbers' ), 'error' );
			return;
		}

		$status = isset( $_POST['_is_serial_number'] ) ? 'yes' : 'no';
		$source = isset( $_POST['_serial_key_source'] ) ? sanitize_text_field( wp_unslash( $_POST['_serial_key_source'] ) ) : 'custom_source';
		update_post_meta( $post->ID, '_is_serial_number', $status );
		update_post_meta( $post->ID, '_serial_key_source', $source );
		// save only if software licensing enabled.
		if ( wcsn_is_software_support_enabled() ) {
			$software_version = isset( $_POST['_software_version'] ) ? sanitize_text_field( wp_unslash( $_POST['_software_version'] ) ) : '';
			update_post_meta( $post->ID, '_software_version', $software_version );
		}

		do_action( 'wcsn_save_simple_product_meta', $post );
	}
}
