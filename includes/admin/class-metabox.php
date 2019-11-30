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
		add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'product_data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'product_write_panel' ) );
		add_filter( 'woocommerce_process_product_meta', array( $this, 'product_save_data' ) );
		add_action( 'admin_head', array( $this, 'print_style' ) );

	}

	/**
	 * Register metaboxes
	 * since 1.2.0
	 */
	public function register_metaboxes() {
		add_meta_box( 'wcsn-order-serial-keys', __( 'Serial Numbers', 'wc-serial-numbers' ), array(
			$this,
			'order_serial_numbers_metabox'
		), 'shop_order', 'normal' );
		//add_meta_box( 'wcsn-order-activations', __( 'Serial Numbers Activations', 'wc-serial-numbers' ), 'wcsn_activations_metabox', 'shop_order', 'normal', 'high' );
	}

	public function order_serial_numbers_metabox( $order ) {
		wcsn_get_views( 'order-details.php', [ 'order' => $order ] );
//		$serial_numbers = wcsn_get_order_serial_numbers( $order->ID );
//
//		if ( ! empty( $serial_numbers ) ):?>
<!--			<table class="widefat fixed" cellspacing="0">-->
<!--				<thead>-->
<!--				<tr>-->
<!--					<th>--><?php //esc_html_e( 'Serial Key', 'wc-serial-numbers' ); ?><!--</th>-->
<!--					<th>--><?php //esc_html_e( 'Product', 'wc-serial-numbers' ); ?><!--</th>-->
<!--					<th>--><?php //esc_html_e( 'Status', 'wc-serial-numbers' ); ?><!--</th>-->
<!--					<th>--><?php //esc_html_e( 'Validity', 'wc-serial-numbers' ); ?><!--</th>-->
<!--				</tr>-->
<!--				</thead>-->
<!---->
<!--				<tbody>-->
<!--				--><?php
//				$i = 0;
//				foreach ( $serial_numbers as $serial_number ) : ?>
<!--					<tr class="--><?php //if ( $i % 2 == 0 ) {
//						echo 'alternate';
//					} ?><!--">-->
<!--						<td class="price column-key">-->
<!--							--><?php //echo $serial_number->serial_key; ?>
<!--						</td>-->
<!---->
<!--						<td class="name column-name">-->
<!--							--><?php
//							echo sprintf( '<a href="%s" target="_blank">#%d - %s</a>', get_edit_post_link( $serial_number->product_id ), $serial_number->product_id, get_the_title( $serial_number->product_id ) );
//							?>
<!--						</td>-->
<!--						<td class="price column-key">-->
<!--							--><?php //echo $serial_number->status; ?>
<!--						</td>-->
<!--						<td class="price column-key">-->
<!--							--><?php //echo $serial_number->validity; ?>
<!--						</td>-->
<!--					</tr>-->
<!--					--><?php
//					$i ++;
//				endforeach;
//				?>
<!--				</tbody>-->
<!--			</table>-->
<!--		--><?php //endif;
	}


	/**
	 * product
	 * since 1.0.0
	 */
	function product_data_tab( $tabs ) {
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
	public function product_write_panel() {
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

			if ( ! wc_serial_numbers()->is_pro_active() ) {
				echo sprintf( '<p>%s <a href="%s" target="_blank">%s</a></p>', __( 'Want serial number to be generated automatically and auto assign with order? Upgrade to Pro', 'wc-serial-numbers' ), 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro', __( 'Upgrade to Pro', 'wc-serial-numbers' ) );
			}

			if ( 'on' !== wcsn_get_settings( 'enable_api' ) ) {
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
				wcsn_get_serial_numbers( [ 'product_id' => $post->ID, 'status' => 'new' ], true ),
				__( 'Serial Number available for sale', 'wc-serial-numbers' )
			);
			?>
		</div>
		<?php
	}

	/**
	 * since 1.0.0
	 */
	public function product_save_data() {
		global $post;

		if ( ! empty( $_POST['_is_serial_number'] ) ) {
			update_post_meta( $post->ID, '_is_serial_number', 'yes' );
		} else {
			update_post_meta( $post->ID, '_is_serial_number', 'no' );
		}

		update_post_meta( $post->ID, '_software_version', ! empty( $_POST['_software_version'] ) ? sanitize_text_field( $_POST['_software_version'] ) : '' );
		do_action( 'wc_serial_numbers_save_simple_product_meta', $post );
	}


	public function print_style() {
		echo sprintf(
			'<style>#woocommerce-product-data ul.wc-tabs li.wc_serial_numbers_options a:before { font-family: %s; content: "%s"; }</style>',
			'dashicons',
			'\f112'
		);
	}


}

WC_Serial_Numbers_MetaBox::instance();
