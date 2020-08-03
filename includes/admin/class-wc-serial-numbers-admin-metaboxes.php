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
		add_action( 'woocommerce_product_after_variable_attributes', array(
			__CLASS__,
			'variable_product_content'
		), 10, 3 );
		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'order_itemmeta' ), 10, 3 );
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
					'id'            => '_is_serial_number',
					'label'         => __( 'Sell Serial Numbers', 'wc-serial-numbers' ),
					'description'   => __( 'Enable this if you are selling serial numbers for this product.', 'wc-serial-numbers' ),
					'value'         => get_post_meta( $post->ID, '_is_serial_number', true ),
					'wrapper_class' => 'options_group',
					'desc_tip'      => true,
				)
			);

			$delivery_quantity = (int) get_post_meta( $post->ID, '_delivery_quantity', true );
			woocommerce_wp_text_input( apply_filters( 'wc_serial_numbers_delivery_quantity_field_args', array(
				'id'                => '_delivery_quantity',
				'label'             => __( 'Delivery quantity', 'wc-serial-numbers' ),
				'description'       => __( 'The number of serial key will be delivered per item. Available in PRO.', 'wc-serial-numbers' ),
				'value'             => empty( $delivery_quantity ) ? 1 : $delivery_quantity,
				'type'              => 'number',
				'wrapper_class'     => 'options_group',
				'desc_tip'          => true,
				'custom_attributes' => array(
					'disabled' => 'disabled'
				),
			) ) );

			$source  = get_post_meta( $post->ID, '_serial_key_source', true );
			$sources = wc_serial_numbers_get_key_sources();
			woocommerce_wp_radio( array(
				'id'            => "_serial_key_source",
				'name'          => "_serial_key_source",
				'class'         => "serial_key_source",
				'label'         => __( 'Serial Key Source', 'wc-serial-numbers' ),
				'value'         => empty( $source ) ? 'custom_source' : $source,
				'wrapper_class' => 'options_group',
				'options'       => $sources,
			) );

			foreach ( array_keys( $sources ) as $key_source ) {
				do_action( 'wc_serial_numbers_source_settings_' . $key_source, $post->ID );
				do_action( 'wc_serial_numbers_source_settings', $key_source, $post->ID );
			}


			do_action( 'wc_serial_numbers_simple_product_metabox', $post );

			if ( ! wc_serial_numbers_software_support_disabled() ) {
				woocommerce_wp_text_input(
					array(
						'id'            => '_software_version',
						'label'         => __( 'Software Version', 'wc-serial-numbers' ),
						'description'   => __( 'Version number for the software. If its not a software product ignore this.', 'wc-serial-numbers' ),
						'placeholder'   => __( 'e.g. 1.0', 'wc-serial-numbers' ),
						'wrapper_class' => 'options_group',
						'desc_tip'      => true,
					)
				);
			}
			if ( 'custom_source' == $source ) {
				echo sprintf(
					'<p class="form-field options_group"><label>%s</label><span class="description">%d %s</span></p>',
					__( 'Available', 'wc-serial-numbers' ),
					WC_Serial_Numbers_Query::init()->table( 'serial_numbers' )->where( [
						'product_id' => $post->ID,
						'status'     => 'available'
					] )->count(),
					__( 'Serial Number available for sale', 'wc-serial-numbers' )
				);
			}
			if ( ! wc_serial_numbers()->is_pro_active() ) {
				echo sprintf( '<p class="wc-serial-numbers-upgrade-box">%s <a href="%s" target="_blank" class="button">%s</a></p>', __( 'Want serial number to be generated automatically and auto assign with order and many more?', 'wc-serial-numbers' ), 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro', __( 'Upgrade to Pro', 'wc-serial-numbers' ) );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Show promo box.
	 *
	 * @param $loop
	 * @param $variation_data
	 * @param $variation
	 *
	 * @since 1.2.0
	 */
	public static function variable_product_content( $loop, $variation_data, $variation ) {
		if ( ! wc_serial_numbers()->is_pro_active() ) {
			echo sprintf( '<p class="wc-serial-numbers-upgrade-box">%s <a href="%s" target="_blank" class="button">%s</a></p>', __( 'WooCommerce Serial Number Free version does not support product variation.', 'wc-serial-numbers' ), 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro', __( 'Upgrade to Pro', 'wc-serial-numbers' ) );
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
		//save only if software licensing enabled
		if ( ! wc_serial_numbers_software_support_disabled() ) {
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
	 * @return bool|string
	 * @since 1.1.6
	 */
	public function order_itemmeta( $o_item_id, $o_item, $product ) {
		global $post;
		if ( ! is_object( $post ) || ! isset( $post->ID ) ) {
			return false;
		}

		$order = wc_get_order( $post->ID );

		// bail for no order
		if ( ! $order ) {
			return false;
		}

		if ( 'completed' !== $order->get_status( 'edit' ) ) {
			return '';
		}

		$is_serial_product = 'yes' == get_post_meta($post->ID, '_is_serial_number', true );

		if ( ! $is_serial_product ) {
			return false;
		}

		$items = WC_Serial_Numbers_Query::init()->from( 'serial_numbers' )->where( [
			'order_id'   => $post->ID,
			'product_id' => $product->get_id(),
		] )->get();

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


}

new WC_Serial_Numbers_Admin_MetaBoxes();
