<?php

defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_MetaBoxes {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.0.0
	 */
	private static $instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @return self Main instance.
	 * @since  1.0.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * WC_Serial_Numbers_MetaBox constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_write_panel' ) );
		add_filter( 'woocommerce_process_product_meta', array( __CLASS__, 'product_save_data' ) );
		add_action( 'admin_head', array( __CLASS__, 'print_style' ) );

	}

	/**
	 * Register metaboxes
	 * since 1.2.0
	 */
	public function register_metaboxes() {
		add_meta_box( 'wcsn-ordered-serial-numbers', __( 'Serial Numbers', 'wc-serial-numbers' ), array(
			__CLASS__,
			'ordered_serials'
		), 'shop_order', 'normal', 'default' );
	}

	/**
	 * product
	 * since 1.0.0
	 */
	public static function product_data_tab( $tabs ) {
		$tabs['wc_serial_numbers'] = array(
			'label'    => __( 'Serial Numbers', 'wc-serial-numbers' ),
			'target'   => 'wc_serial_numbers_data',
			'class'    => array( 'show_if_simple' ),
			'priority' => 11
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
					'id'          => '_is_serial_number',
					'label'       => __( 'Serial Number', 'wc-serial-numbers' ),
					'description' => __( 'Enable this if you are selling Sell serial number for this product.', 'wc-serial-numbers' ),
					'value'       => get_post_meta( $post->ID, '_is_serial_number', true ),
					'desc_tip'    => true,
				)
			);

			$_delivery_quantity = get_post_meta( $post->ID, '_delivery_quantity', true );
			woocommerce_wp_text_input(
				array(
					'id'          => '_delivery_quantity ',
					'label'       => __( 'Delivery quantity', 'wc-serial-numbers' ),
					'description' => __( 'The amount of serial key will be delivered upon purchase', 'wc-serial-numbers' ),
					'value'       => empty( $_delivery_quantity ) ? 1 : $_delivery_quantity,
					'type'        => 'number',
					'desc_tip'    => true,
				)
			);

			do_action( 'serial_numbers_product_metabox', $post );

			if ( ! wc_serial_numbers()->is_pro_active() ) {
				echo sprintf( '<p>%s <a href="%s" target="_blank">%s</a></p>', __( 'Want serial number to be generated automatically and auto assign with order? Upgrade to Pro', 'wc-serial-numbers' ), 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro', __( 'Upgrade to Pro', 'wc-serial-numbers' ) );
			}
			if ( ! wc_serial_numbers_software_disabled() ) {
				woocommerce_wp_text_input(
					array(
						'id'          => '_software_version',
						'label'       => __( 'Software Version', 'wc-serial-numbers' ),
						'description' => __( 'Version number for the software. If its not a software product ignore this.', 'wc-serial-numbers' ),
						'placeholder' => __( 'e.g. 1.0', 'wc-serial-numbers' ),
						'desc_tip'    => true,
					)
				);
			}

			echo sprintf(
				'<p class="form-field"><label>%s</label><span class="description">%d %s</span></p>',
				__( 'Available', 'wc-serial-numbers' ),
				wc_serial_numbers_get_serial_numbers( [ 'product_id' => $post->ID, 'status' => 'available' ], true ),
				__( 'Serial Number available for sale', 'wc-serial-numbers' )
			);
			?>
		</div>
		<?php
	}

	/**
	 * since 1.0.0
	 */
	public static function product_save_data() {
		global $post;

		if ( ! empty( $_POST['_is_serial_number'] ) ) {
			update_post_meta( $post->ID, '_is_serial_number', 'yes' );
		} else {
			update_post_meta( $post->ID, '_is_serial_number', 'no' );
		}

		update_post_meta( $post->ID, '_delivery_quantity', ! empty( $_POST['_delivery_quantity'] ) ? intval( $_POST['_delivery_quantity'] ) : '1' );
		if ( ! wc_serial_numbers_software_disabled() ) {
			update_post_meta( $post->ID, '_software_version', ! empty( $_POST['_software_version'] ) ? sanitize_text_field( $_POST['_software_version'] ) : '' );
		}
		do_action( 'wc_serial_numbers_save_simple_product_meta', $post );
	}

	/**
	 * since 1.0.0
	 *
	 * @param $order
	 *
	 * @return bool
	 */
	public static function ordered_serials( $order ) {
		$serial_numbers = get_post_meta( $order->ID, 'wc_serial_numbers_products', true );
		if ( false == $serial_numbers || empty( $serial_numbers ) ) {
			_e( 'No serial number enabled products associated with the order', 'wc-serial-numbers' );

			return false;
		}

		wc_serial_numbers_get_views( 'ordered-serial-numbers.php', [
			'serial_numbers' => $serial_numbers,
			'order_id'       => $order->ID
		] );
	}

	public static function print_style() {
		echo sprintf(
			'<style>#woocommerce-product-data ul.wc-tabs li.wc_serial_numbers_options a:before { font-family: %s; content: "%s"; }</style>',
			'dashicons',
			'\f112'
		);
	}
}

WC_Serial_Numbers_MetaBoxes::instance();
