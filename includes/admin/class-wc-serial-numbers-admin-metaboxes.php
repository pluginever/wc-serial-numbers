<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Admin_MetaBoxes {

	/**
	 * WC_Serial_Numbers_Admin_MetaBoxes constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_write_panel' ) );
		add_filter( 'woocommerce_process_product_meta', array( __CLASS__, 'product_save_data' ) );
		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'order_itemmeta' ), 10, 3 );
		add_action( 'admin_head', array( __CLASS__, 'print_style' ) );
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
					'description' => __( 'Enable this if you are selling serial numbers for this product.', 'wc-serial-numbers' ),
					'value'       => get_post_meta( $post->ID, '_is_serial_number', true ),
					'desc_tip'    => true,
				)
			);

			$delivery_quantity = (int) get_post_meta( $post->ID, '_delivery_quantity', true );
			woocommerce_wp_text_input( apply_filters( 'wc_serial_numbers_delivery_quantity_field_args', array(
				'id'                => '_delivery_quantity',
				'label'             => __( 'Delivery quantity', 'wc-serial-numbers' ),
				'description'       => __( 'The amount of serial key will be delivered upon purchase', 'wc-serial-numbers' ),
				'value'             => empty( $delivery_quantity ) ? 1 : $delivery_quantity,
				'type'              => 'number',
				'desc_tip'          => true,
				'custom_attributes' => array(
					'disabled' => 'disabled'
				),
			) ) );

			do_action( 'serial_numbers_product_metabox', $post );

			if ( ! wc_serial_numbers()->is_pro_active() ) {
				echo sprintf( '<p>%s <a href="%s" target="_blank">%s</a></p>', __( 'Want serial number to be generated automatically and auto assign with order? Upgrade to Pro', 'wc-serial-numbers' ), 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro', __( 'Upgrade to Pro', 'wc-serial-numbers' ) );
			}

			if ( wc_serial_numbers()->api_enabled() ) {
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
				wc_serial_numbers_get_items( [
					'product_id' => $post->ID,
					'status'     => 'available'
				], true ),
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
		$status = isset( $_POST['_is_serial_number'] ) ? 'yes' : 'no';
		update_post_meta( $post->ID, '_is_serial_number', $status );
		update_post_meta( $post->ID, '_delivery_quantity', empty( $_POST['_delivery_quantity'] ) ? 1 : intval( $_POST['_delivery_quantity'] ) );

		//save only if software licensing enabled
		if ( wc_serial_numbers()->api_enabled() ) {
			update_post_meta( $post->ID, '_software_version', ! empty( $_POST['_software_version'] ) ? sanitize_text_field( $_POST['_software_version'] ) : '' );
		}

		do_action( 'wcsn_save_simple_product_meta', $post );
	}


	/**
	 *
	 * @param $o_item_id
	 * @param $o_item
	 * @param $product
	 *
	 * @since 1.1.6
	 */
	public function order_itemmeta( $o_item_id, $o_item, $product ) {
		global $post;
		$order = wc_get_order( $post->ID );
		if ( 'completed' !== $order->get_status( 'edit' ) ) {
			return '';
		}

		$is_serial_product = 'yes' == get_post_meta( $product->get_id(), '_is_serial_number', true );
		if ( ! $is_serial_product ) {
			return false;
		}

		$items = wc_serial_numbers_get_items( [
			'order_id'   => $post->ID,
			'product_id' => $product->get_id(),
		] );


		if ( empty( $items ) && $order ) {
			echo sprintf( '<div class="wcsn-missing-serial-number">%s</div>', __( 'Order missing serial numbers for this item.', 'wc-serial-numbers' ) );

			return true;
		}


		$url = admin_url( 'admin.php?page=wc-serial-numbers' );
		echo sprintf( '<br/><a href="%s">%s&rarr;</a>', add_query_arg( [
			'order_id'   => $post->ID,
			'product_id' => $product->get_id()
		], $url ), __( 'Serial Numbers', 'wc-serial-numbers' ) );

		$url = admin_url( 'admin.php?page=wc-serial-numbers' );

		$li = '';

		foreach ( $items as $item ) {
			$li .= sprintf( '<li><a href="%s">&rarr;</a>&nbsp;%s</li>', add_query_arg( [
				'action' => 'edit',
				'id'     => $item->id
			], $url ), wc_serial_numbers_decrypt_key( $item->serial_key ) );
		}

		echo sprintf( '<ul>%s</ul>', $li );
	}

	/**
	 * Print style
	 *
	 * @since 1.0.0
	 */
	public static function print_style() {
		echo sprintf(
			'<style>#woocommerce-product-data ul.wc-tabs li.wc_serial_numbers_options a:before { font-family: %s; content: "%s"; }</style>',
			'dashicons',
			'\f112'
		);

		echo sprintf( '<style>.wcsn-missing-serial-number{%s}</style>', 'display: inline-block;background: #d62828;color: #fff;padding: 2px 5px;border-radius: 3px;margin-top: 10px;' );

	}

}

new WC_Serial_Numbers_Admin_MetaBoxes();
