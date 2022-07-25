<?php

namespace PluginEver\WooCommerceSerialNumbers\Admin;

// don't call the file directly.
use PluginEver\WooCommerceSerialNumbers\Generators;
use PluginEver\WooCommerceSerialNumbers\Serial_Keys;

defined( 'ABSPATH' ) || exit();

/**
 * Class Meta Boxes.
 *
 * @since   1.0.0
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Meta_Boxes {

	/**
	 * Construct Meta_Boxes.
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_metaboxes' ) );
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_write_panel' ) );
		add_filter( 'woocommerce_process_product_meta', array( __CLASS__, 'product_save_data' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'variable_product_content' ), 10, 3 ); //phpcs:ignore
	}

	/**
	 * Register order metaboxes.
	 *
	 * @since 1.2.5
	 */
	public static function register_metaboxes() {
		add_meta_box( 'order-serial-numbers', __( 'Serial Numbers', 'wc-serial-numbers' ), array( __CLASS__, 'order_metabox' ), 'shop_order', 'advanced', 'high' ); //phpcs:ignore
	}

	/**
	 * Add new tabs on product data tabs for serial numbers.
	 *
	 * @param array $tabs Product data tabs.
	 *
	 * @return array
	 * @since 1.0.0
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
	 * Add options to wc_serial_numbers tabs.
	 *
	 * @since 1.0.0
	 */
	public static function product_write_panel() {
		global $post, $woocommerce;
		?>
		<div id="wc_serial_numbers_data" class="panel woocommerce_options_panel show_if_simple" style="padding-bottom: 50px;display: none;">
			<?php
			woocommerce_wp_checkbox(
				array(
					'id'            => '_is_serial_number',
					'label'         => __( 'Sell serial numbers', 'wc-serial-numbers' ),
					'description'   => __( 'Enable this if you are selling serial numbers for this product.', 'wc-serial-numbers' ), //phpcs:ignore
					'value'         => get_post_meta( $post->ID, '_is_serial_number', true ),
					'wrapper_class' => 'options_group',
					'desc_tip'      => true,
				)
			);
			$delivery_quantity = (int) get_post_meta( $post->ID, '_delivery_quantity', true );
			woocommerce_wp_text_input( apply_filters( 'wc_serial_numbers_delivery_quantity_field_args',
				array(
					'id'                => '_delivery_quantity',
					'label'             => __( 'Delivery quantity', 'wc-serial-numbers' ),
					'description'       => __( 'Number of serial key(s) will be delivered per item. Available in PRO.', 'wc-serial-numbers' ), //phpcs:ignore
					'value'             => empty( $delivery_quantity ) ? 1 : $delivery_quantity,
					'type'              => 'number',
					'wrapper_class'     => 'options_group',
					'desc_tip'          => true,
					'custom_attributes' => array(
						'disabled' => 'disabled',
					),
				)
			) );

			$source = get_post_meta( $post->ID, '_serial_key_source', true );
			woocommerce_wp_select( array(
				'id'          => "_serial_key_source",
				'name'        => "_serial_key_source",
				'class'       => "serial_key_source",
				'label'       => __( 'Serial key generator', 'wc-serial-numbers' ),
				'description' => __( 'Manual option will pre-load the manually generated serial numbers. Automatic option will create serial numbers automatically based on the assigned generator rule and will be attached with order', 'wc-serial-numbers' ), //phpcs:ignore
				'desc_tip'    => true,
				'options'     => array(
					'custom_source'  => __( "Manual", 'wc-serial-numbers' ),
					'generator_rule' => __( "Automatic", 'wc-serial-numbers' ),
				),
				'value'       => $source,
			) );


			$generator_id = get_post_meta( $post->ID, '_generator_id', true );
			$style        = 'generator_rule' === $source ? 'display:block' : 'display:none';
			?>
			<div class="wc-serial-numbers-key-source-settings options_group" data-source="generator_rule" style="<?php echo $style; ?>">
				<?php
				woocommerce_wp_select(
					array(
						'id'          => '_generator_id',
						'label'       => __( 'Generator rule', 'wc-serial-numbers' ),
						'description' => __( 'Select generator source that will be used to generate serial numbers for the product.', 'wc-serial-numbers' ), //phpcs:ignore
						'options'     => self::get_generators(),
						'desc_tip'    => true,
						'value'       => $generator_id
					)
				);

				?>
			</div>
			<?php

			do_action( 'wc_serial_numbers_simple_product_metabox', $post );

			if ( 'yes' !== get_option( 'wc_serial_numbers_disable_software_support' ) ) {
				woocommerce_wp_text_input(
					array(
						'id'            => '_software_version',
						'label'         => __( 'Software version', 'wc-serial-numbers' ),
						'description'   => __( 'Version number for the software. Ignore if it\'s not a software product.', 'wc-serial-numbers' ), //phpcs:ignore
						'placeholder'   => __( 'e.g. 1.0', 'wc-serial-numbers' ),
						'wrapper_class' => 'options_group',
						'desc_tip'      => true,
					)
				);
			}


			if ( 'custom_source' == $source ) {
				$available_serial_numbers = Serial_Keys::query( array_merge( array(), [ 'status__in' => 'available' ] ), true ); //phpcs:ignore

				echo sprintf(
					'<p class="form-field options_group"><label>%s</label><span class="description">%d %s</span></p>',
					__( 'Available', 'wc-serial-numbers' ),
					$available_serial_numbers,
					__( 'serial number' . ( ( 1 == $available_serial_numbers || 0 == $available_serial_numbers ) ? '' : 's' ) . ' available for sale', 'wc-serial-numbers' ) //phpcs:ignore
				);
			}
			?>
		</div>
		<?php

	}

	/**
	 * Save product serial_numbers data.
	 *
	 * @since 1.0.0
	 */
	public static function product_save_data() {
		global $post;
		$status       = isset( $_POST['_is_serial_number'] ) ? 'yes' : 'no';
		$source       = isset( $_POST['_serial_key_source'] ) ? sanitize_text_field( $_POST['_serial_key_source'] ) : 'custom_source'; //phpcs:ignore
		$generator_id = isset( $_POST['_generator_id'] ) ? intval( $_POST['_generator_id'] ) : 1;
		update_post_meta( $post->ID, '_is_serial_number', $status );
		update_post_meta( $post->ID, '_serial_key_source', $source );
		update_post_meta( $post->ID, '_generator_id', $generator_id );
		// Save only if software licensing enabled.
		if ( 'yes' != get_option( 'wc_serial_numbers_disable_software_support' ) ) {
			update_post_meta( $post->ID, '_software_version', ! empty( $_POST['_software_version'] ) ? sanitize_text_field( $_POST['_software_version'] ) : '' ); //phpcs:ignore
		}

		do_action( 'wcsn_save_simple_product_meta', $post );
	}

	/**
	 * Show promo box for pro versions.
	 *
	 * @param array $variation_data variations_data
	 * @param array $variation variations
	 * @param array $loop loop
	 *
	 * @since 1.2.0
	 */
	public static function variable_product_content( $loop, $variation_data, $variation ) {
		if ( ! is_plugin_active( 'wc-serial-numbers-pro/wc-serial-numbers-pro.php' ) ) {
			echo sprintf( '<p class="wc-serial-numbers-upgrade-box">%s <a href="%s" target="_blank" class="button">%s</a></p>', __( 'WooCommerce Serial Number Free version does not support product variation.', 'wc-serial-numbers' ), 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=product_page_license_area&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro', __( 'Upgrade to Pro', 'wc-serial-numbers' ) ); //phpcs:ignore
		}

	}

	/**
	 * Render order metaboxes which show all the metaboxes.
	 *
	 * @param \WP_Post $post order post.
	 *
	 * @return bool
	 * @since 1.2.6
	 */
	public static function order_metabox( $post ) {
		echo 'order has serial numbers';

		//todo need to implement order serial numbers.

		return true;
	}

	/**
	 * Get generators.
	 *
	 * @return array
	 * @since 1.3.1
	 */
	public static function get_generators() {
		$all_generators = Generators::query( array( 'return' => 'array' ), false );
		$generators     = array();
		if ( is_array( $all_generators ) && count( $all_generators ) ) {
			foreach ( $all_generators as $generator ) {
				$generators[ $generator->get_id() ] = $generator->get_pattern();
			}
		}

		return $generators;
	}
}

new Meta_Boxes();
