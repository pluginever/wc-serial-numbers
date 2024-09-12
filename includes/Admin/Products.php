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
		add_action( 'wc_serial_numbers_product_options', array( __CLASS__, 'render_enable_sale_keys_options' ) );
		add_action( 'wc_serial_numbers_product_options', array( __CLASS__, 'render_source_options' ) );
		add_action( 'wc_serial_numbers_product_options', array( __CLASS__, 'render_key_options' ) );
		add_action( 'wc_serial_numbers_product_options', array( __CLASS__, 'render_software_options' ) );
		add_action( 'wc_serial_numbers_product_options', array( __CLASS__, 'render_pro_notice_options' ) );
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

			/*._serial_key_source_field label {*/
			/*	margin: 0 !important;*/
			/*	width: 100% !important;*/
			/*}*/

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
		global $post;
		$product = wc_get_product( $post->ID );

		include __DIR__ . '/views/products/product-options.php';
	}

	/**
	 * Render enable sale keys options.
	 *
	 * @param \WC_Product $product product object.
	 *
	 * @since 3.0.0
	 */
	public static function render_enable_sale_keys_options( $product ) {
		if ( strpos( $product->get_type(), 'variable' ) !== false ) {
			return;
		}

		include __DIR__ . '/views/products/product-selling-keys-options.php';
	}

	/**
	 * Render source options.
	 *
	 * @param \WC_Product $product product object.
	 *
	 * @since 3.0.0
	 */
	public static function render_source_options( $product ) {
		if ( strpos( $product->get_type(), 'variable' ) !== false ) {
			return;
		}

		include __DIR__ . '/views/products/product-source-options.php';
	}

	/**
	 * Render key options.
	 *
	 * @param \WC_Product $product product object.
	 *
	 * @since 3.0.0
	 */
	public static function render_key_options( $product ) {
		if ( strpos( $product->get_type(), 'variable' ) !== false ) {
			return;
		}

		include __DIR__ . '/views/products/product-key-options.php';
	}

	/**
	 * Render software options.
	 *
	 * @param \WC_Product $product product object.
	 *
	 * @since 3.0.0
	 */
	public static function render_software_options( $product ) {
		if ( strpos( $product->get_type(), 'variable' ) !== false || ! wcsn_is_software_support_enabled() ) {
			return;
		}

		include __DIR__ . '/views/products/product-software-options.php';
	}

	/**
	 * Render pro notice options.
	 *
	 * @param \WC_Product $product product object.
	 *
	 * @since 3.0.0
	 */
	public static function render_pro_notice_options( $product ) {
		// if product is variable, do not show the key options. Also, if pro version is active, do not show the notice.
		if ( strpos( $product->get_type(), 'variable' ) !== false || WCSN()->is_premium_active() ) {
			return;
		}

		include __DIR__ . '/views/products/product-pro-notice-options.php';
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

		// Must have WC Serial Numbers manager role to access this endpoint.
		if ( ! current_user_can( wcsn_get_manager_role() ) ) {
			return;
		}

		$status = isset( $_POST['_is_serial_number'] ) ? 'yes' : 'no';
		$source = isset( $_POST['_serial_key_source'] ) ? sanitize_text_field( wp_unslash( $_POST['_serial_key_source'] ) ) : 'automatic';
		update_post_meta( $post->ID, '_is_serial_number', $status );
		update_post_meta( $post->ID, '_serial_key_source', $source );

		// if source is automatic then get the generator id.
		if ( 'automatic' === $source ) {
			$generator_id = isset( $_POST['_generator_id'] ) ? absint( wp_unslash( $_POST['_generator_id'] ) ) : 0;
			$sequential   = isset( $_POST['_wcsn_is_sequential'] ) ? 'yes' : 'no';
			update_post_meta( $post->ID, '_generator_id', $generator_id );
			update_post_meta( $post->ID, '_wcsn_is_sequential', $sequential );
		}

		// save only if software licensing enabled.
		if ( wcsn_is_software_support_enabled() ) {
			$software_version = isset( $_POST['_software_version'] ) ? sanitize_text_field( wp_unslash( $_POST['_software_version'] ) ) : '';
			update_post_meta( $post->ID, '_software_version', $software_version );
		}

		do_action( 'wcsn_save_simple_product_meta', $post );
	}


	/**
	 * Display serial numbers in order item meta.
	 *
	 * @param int            $o_item_id order item id.
	 * @param \WC_Order_Item $o_item order item object.
	 * @param \WC_Product    $product product object.
	 *
	 * @since 1.1.6
	 *
	 * @return void
	 */
	public function order_itemmeta( $o_item_id, $o_item, $product ) {
		global $post;
		if ( ! is_object( $post ) || ! isset( $post->ID ) ) {
			return;
		}

		$order = wc_get_order( $post->ID );

		// bail for no order.
		if ( ! $order ) {
			return;
		}

		if ( 'completed' !== $order->get_status( 'edit' ) ) {
			return;
		}

		// if this is not product then no need to process.
		if ( empty( $product ) ) {
			return;
		}

		if ( 'yes' !== get_post_meta( $product->get_id(), '_is_serial_number', true ) ) {
			return;
		}

		$items = wcsn_get_keys(
			array(
				'order_id'   => $post->ID,
				'product_id' => $product->get_id(),
			)
		);

		if ( empty( $items ) && $order ) {
			echo wp_kses_post(
				sprintf(
					'<div class="wcsn-missing-serial-number">%s</div>',
					__( 'Order missing serial numbers for this item.', 'wc-serial-numbers' )
				)
			);
			return;
		}

		$url = admin_url( 'admin.php?page=wc-serial-numbers' );
		printf(
			'<br/><a href="%s">%s&rarr;</a>',
			esc_url(
				add_query_arg(
					array(
						'order_id'   => $post->ID,
						'product_id' => $product->get_id(),
					),
					$url
				)
			),
			esc_html__( 'Serial Numbers', 'wc-serial-numbers' )
		);

		$url = admin_url( 'admin.php?page=wc-serial-numbers' );

		$li = '';

		foreach ( $items as $item ) {
			$li .= sprintf(
				'<li><a href="%s">&rarr;</a>&nbsp;%s</li>',
				add_query_arg(
					array(
						'edit' => $item->id,
					),
					$url
				),
				wcsn_decrypt_key( $item->serial_key )
			);
		}
		echo wp_kses_post(
			sprintf(
				'<ul>%s</ul>',
				$li
			)
		);
	}
}
