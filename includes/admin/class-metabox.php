<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_MetaBox {
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
		add_action( 'woocommerce_product_write_panel_tabs', array(
			$this,
			'wc_serial_numbers_product_write_panel_tab'
		) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'wc_serial_numbers_product_write_panel' ) );

		add_filter( 'woocommerce_process_product_meta', array( $this, 'wc_serial_numbers_product_save_data' ) );
	}

	function wc_serial_numbers_product_write_panel_tab() {
		?>
		<li class="serial_number_tab show_if_serial_number"><a
				href="#serial_number_data"><span><?php _e( 'Serial Numbers', 'wc-serial-numbers' ); ?></span></a></li>

		<?php
	}

	public function wc_serial_numbers_product_write_panel() {
		global $post, $woocommerce;
		?>
		<div id="serial_number_data" class="panel woocommerce_options_panel show_if_simple"
		     style="padding-bottom: 50px;display: none;">
			<?php
			woocommerce_wp_checkbox(
				array(
					'id'          => '_is_serial_number',
					'label'       => __( 'Serial Number', 'wc-serial-numbers' ),
					'description' => __( 'Sell serial number', 'wc-serial-numbers' ),

					'desc_tip' => true,
				)
			);

			woocommerce_wp_select( array(
				'id'          => '_serial_key_source',
				'label'       => __( 'Serial Key Source', 'wc-serial-numbers' ),
				'description' => __( 'Auto generated will automatically generate serial key & assign to order. Custom generated key will be used from available generated serial key.', 'wc-serial-numbers' ),
				'placeholder' => __( 'N/A', 'wc-serial-numbers' ),
				'desc_tip'    => true,
				'options'     => apply_filters( 'wcsn_serial_key_sources', array(
					'custom_source' => __( 'Manually Generated serial number', 'wc-serial-numbers' ),
				) ),
			) );

			if ( ! wc_serial_numbers()->is_pro_active() ) {
				echo sprintf( '<p>%s <a href="%s" target="_blank">%s</a></p>', __( 'Want serial number to be generated automatically and auto assign with order? Upgrade to Pro', 'wc-serial-numbers' ), 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro', __( 'Upgrade to Pro', 'wc-serial-numbers' ) );
			}

			woocommerce_wp_text_input(
				array(
					'id'          => '_serial_number_key_prefix',
					'label'       => __( 'Serial key prefix', 'wc-serial-numbers' ),
					'description' => __( 'Optional prefix for generated serial number.', 'wc-serial-numbers' ),
					'placeholder' => __( 'N/A', 'wc-serial-numbers' ),
					'desc_tip'    => true,
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => '_activation_limit',
					'label'       => __( 'Activation limit', 'wc-serial-numbers' ),
					'description' => __( 'Amount of activations possible per serial number. 0 means unlimited. If its not a software product ignore this.', 'wc-serial-numbers' ),
					'placeholder' => __( '0', 'wc-serial-numbers' ),
					'desc_tip'    => true,
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => '_validity',
					'label'       => __( 'Validity', 'wc-serial-numbers' ),
					'description' => __( 'The number validity in days.', 'wc-serial-numbers' ),
					'placeholder' => __( '0', 'wc-serial-numbers' ),
					'desc_tip'    => true,
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'          => '_quantity_serial_number',
					'label'       => __( 'Quantity of serial number', 'wc-serial-numbers' ),
					'description' => __( 'the amount of serial number to be delivered each purchase.', 'wc-serial-numbers' ),
					'placeholder' => __( '1', 'wc-serial-numbers' ),
					'desc_tip'    => true,
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'          => '_software_version',
					'label'       => __( 'Software Version', 'wc-serial-numbers' ),
					'description' => __( 'Version number for the software. If its not a software product ignore this.', 'wc-serial-numbers' ),
					'placeholder' => __( 'e.g. 1.0', 'wc-serial-numbers' ),
					'desc_tip'    => true,
				)
			);

			echo sprintf(
				'<p class="form-field"><label>%s</label><span class="description">%d %s</span></p>',
				__('Available', 'wc-serial-numbers'),
				wcsn_get_serial_numbers( [ 'product_id' => $post->ID, 'status' => 'new' ], true ),
				__('Serial Number available for sale', 'wc-serial-numbers')
			);
			?>
		</div>
		<?php
	}

	public function wc_serial_numbers_product_save_data() {
		global $post;

		if ( ! empty( $_POST['_is_serial_number'] ) ) {
			update_post_meta( $post->ID, '_is_serial_number', 'yes' );
		} else {
			update_post_meta( $post->ID, '_is_serial_number', 'no' );
		}


		update_post_meta( $post->ID, '_quantity_serial_number', ! empty( $_POST['_quantity_serial_number'] ) ? intval( $_POST['_quantity_serial_number'] ) : 1 );
		update_post_meta( $post->ID, '_serial_key_source', ! empty( $_POST['_serial_key_source'] ) ? sanitize_key( $_POST['_serial_key_source'] ) : 'custom_source' );
		update_post_meta( $post->ID, '_serial_number_key_prefix', ! empty( $_POST['_serial_number_key_prefix'] ) ? sanitize_text_field( $_POST['_serial_number_key_prefix'] ) : '' );
		update_post_meta( $post->ID, '_activation_limit', ! empty( $_POST['_activation_limit'] ) ? intval( $_POST['_activation_limit'] ) : '0' );
		update_post_meta( $post->ID, '_validity', ! empty( $_POST['_validity'] ) ? intval( $_POST['_validity'] ) : '0' );
		update_post_meta( $post->ID, '_software_version', ! empty( $_POST['_software_version'] ) ? sanitize_text_field( $_POST['_software_version'] ) : '' );
		do_action( 'wc_serial_numbers_save_simple_product_meta', $post );
	}


}

WC_Serial_Numbers_MetaBox::instance();
