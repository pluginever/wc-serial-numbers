<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
use PluginEver\WooCommerceSerialNumbers\Generators;
use PluginEver\WooCommerceSerialNumbers\Helper;
use PluginEver\WooCommerceSerialNumbers\Product;

defined( 'ABSPATH' ) || exit();

/**
 * Admin Product Admin Class
 *
 * @since       #.#.#
 *
 * @since   1.0.0
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Admin_Product {
	/**
	 * Construct Admin_Product.
	 *
	 * @since  #.#.#
	 * @return void
	 */
	public function __construct() {
		// Make sure API products in the trash can be restored.
		// add_filter( 'post_row_actions', array( $this, 'api_row_actions' ), 10, 2 );
		// Remove the "Delete Permanently" bulk action on the Edit Products screen.
		// add_filter( 'bulk_actions-edit-product', array( $this, 'api_bulk_actions' ), 10 );
		// Do not allow API products to be automatically purged on the 'wp_scheduled_delete' hook.
		// add_action( 'wp_scheduled_delete', array( $this, 'prevent_scheduled_deletion' ), 9 );
		// Trash variations instead of deleting them to prevent headaches from deleted products.

		add_action( 'woocommerce_product_options_product_type', array( __CLASS__, 'type_options' ) );
		add_filter( 'product_type_options', array( __CLASS__, 'product_type_options' ) );
		add_action( 'woocommerce_product_write_panel_tabs', array( __CLASS__, 'product_write_panel_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'data_panel' ) );


		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_product' ) );

		if ( Helper::is_software_support_enabled() ) {
			add_filter( 'wc_serial_numbers_product_admin_fields', [ __CLASS__, 'add_software_fields' ] );
		}

	}

	/**
	 * Render the API checkbox.
	 *
	 * @since #.#.#
	 */
	public static function type_options() {
		global $post;
		if ( Helper::is_valid_product_type( $post ) ) {
			woocommerce_wp_checkbox(
				array(
					'id'            => '_is_serial_numbers',
					'wrapper_class' => 'display_serial_numbers_checkbox',
					'label'         => esc_html__( 'Sell Serial Numbers', 'wc-serial-numbers' ),
					'description'   => esc_html__( 'Enable this if you are selling serial numbers for this product.', 'wc-serial-numbers' ),
					'value'         => Helper::is_serial_product( $post->ID ) ? 'yes' : 'no',
				)
			);
		}
	}

	/**
	 * Add a serial number checkbox to the product edit screen.
	 *
	 * @since #.#.#
	 *
	 * @param array $options Edit options.
	 *
	 * @return mixed
	 */
	public static function product_type_options( $options ) {
		global $post;
		if ( Helper::is_valid_product_type( $post->ID ) ) {
			$options['is_serial_numbers'] = array(
				'id'            => '_is_serial_numbers',
				'wrapper_class' => 'display_serial_numbers_checkbox',
				'label'         => esc_html__( 'Sell Serial Numbers', 'wc-serial-numbers' ),
				'description'   => esc_html__( 'Enable this if you are selling serial numbers for this product.', 'wc-serial-numbers' ),
			);
		}

		return $options;
	}

	/**
	 * adds a new tab to the product interface
	 */
	public static function product_write_panel_tab() {
		global $post;
		if ( Helper::is_valid_product_type( $post->ID ) ) {
			?>
			<li class="show_if_serial_numbers wc-serial-numbers-tab" style="display: none;">
				<a href="#serial_numbers_data">
					<span><?php esc_html_e( 'Serial Numbers', 'wc-serial-numbers' ); ?></span>
				</a>
			</li>
			<?php
		}
	}

	/**
	 * adds the panel to the product interface
	 */
	public static function data_panel() {
		global $post;
		if ( Helper::is_valid_product_type( $post->ID ) ) {
			?>
			<div id="serial_numbers_data" class="panel woocommerce_options_panel" style="display: none;">
				<div class="options_group show_if_variable" style="padding:2em">
					<strong class="attribute_name">
						<?php esc_html_e( 'All data below is copied to all variations, unless override per variation.', 'wc-serial-numbers' ); ?>
					</strong>
				</div>

				<?php
				$fields = apply_filters(
					'wc_serial_numbers_product_admin_fields',
					array(
						array(
							'id'   => 'common_fields',
							'type' => 'start_group',
						),
						array(
							'id'          => '_serial_numbers_key_source',
							'name'        => '_serial_numbers_key_source',
							'class'       => 'serial_numbers_key_source short',
							'label'       => esc_html__( 'Serial key source', 'wc-serial-numbers' ),
							'callback'    => 'woocommerce_wp_select',
							'description' => __( 'Manual option will pre-load the manually generated serial numbers. Automatic option will create serial numbers automatically based on the assigned generator rule and will be attached with order', 'wc-serial-numbers' ),
							'desc_tip'    => true,
							'default'     => 'stock',
							'options'     => Helper::get_key_sources(),
						),
						array(
							'id'            => '_serial_numbers_generator_id',
							'label'         => __( 'Generator rule', 'wc-serial-numbers' ),
							'description'   => __( 'Select generator source that will be used to generate serial numbers for the product.', 'wc-serial-numbers' ), //phpcs:ignore
							'options'       => self::get_generators(),
							'callback'      => 'woocommerce_wp_select',
							'wrapper_class' => 'show_if_serial_numbers_key_source_is_generator',
							'desc_tip'      => true,
							'class'         => 'serial-numbers-generator-select short',
						),
						array(
							'id'   => 'common_fields',
							'type' => 'end_group',
						),
					),
					$post->ID,
					$post
				);

				foreach ( $fields as $field ) {
					$field = wp_parse_args(
						$field,
						[
							'type'     => '',
							'callback' => '',
						]
					);
					if ( 'start_group' === $field['type'] ) {
						echo '<div class="options_group">';
					} elseif ( 'end_group' === $field['type'] ) {
						echo '</div>';
					} elseif ( ! empty( $field['callback'] ) && is_callable( $field['callback'] ) ) {
						if ( isset( $field['default'] ) && ! empty( $field['default'] ) && ! empty( $field['id'] ) ) {
							$meta           = get_post_meta( $post->ID, $field['id'], true );
							$field['value'] = empty( $meta ) ? $field['default'] : $meta;
						}
						call_user_func( $field['callback'], $field );
					}
				}
				?>
			</div>
			<?php
		}
	}

	/**
	 * Add software support fields.
	 *
	 * @param array $fields Meta fields.
	 *
	 * @since #.#.#
	 * @return array
	 */
	public static function add_software_fields( $fields ) {
		$software_fields = [
			array(
				'id'   => 'software_fields',
				'type' => 'start_group',
			),
			array(
				'id'          => '_serial_numbers_activation_limit',
				'label'       => __( 'Activation limit', 'wc-serial-numbers' ),
				'description' => esc_html__( 'Limits the number of activations. Default is unlimited when left empty.', 'wc-serial-numbers' ),
				'placeholder' => __( 'e.g. 1.0', 'wc-serial-numbers' ),
				'type'        => 'number',
				'callback'    => 'woocommerce_wp_text_input',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_serial_numbers_validity',
				'label'       => __( 'Valid for', 'wc-serial-numbers' ),
				'description' => __( 'Number of days it will be valid after purchase. Default is never expire when left empty.', 'wc-serial-numbers' ),
				'placeholder' => __( 'Never expire', 'wc-serial-numbers' ),
				'type'        => 'number',
				'callback'    => 'woocommerce_wp_text_input',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_serial_numbers_software_version',
				'label'       => __( 'Software version', 'wc-serial-numbers' ),
				'description' => __( 'Version number for the software.', 'wc-serial-numbers' ),
				'placeholder' => __( 'e.g. 1.0', 'wc-serial-numbers' ),
				'type'        => 'text',
				'callback'    => 'woocommerce_wp_text_input',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_serial_numbers_software_author',
				'label'       => esc_html__( 'Author', 'wc-serial-numbers' ),
				'description' => esc_html__( 'The author of the software.', 'wc-serial-numbers' ),
				'placeholder' => esc_html__( 'PluginEver LLC', 'wc-serial-numbers' ),
				'class'       => '',
				'type'        => 'text',
				'callback'    => 'woocommerce_wp_text_input',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_serial_numbers_software_last_updated',
				'label'       => esc_html__( 'Last updated', 'wc-serial-numbers' ),
				'description' => esc_html__( 'When the software was last updated.', 'wc-serial-numbers' ),
				'placeholder' => esc_html__( 'YYYY-MM-DD', 'wc-serial-numbers' ),
				'class'       => 'serial_numbers_date_field',
				'type'        => 'text',
				'callback'    => 'woocommerce_wp_text_input',
				'desc_tip'    => true,
			),
			array(
				'id'          => '_serial_numbers_software_upgrade_notice',
				'label'       => esc_html__( 'Upgrade notice', 'wc-serial-numbers' ),
				'description' => esc_html__( 'A notice displayed when an update is available.', 'wc-serial-numbers' ),
				'placeholder' => esc_html__( 'Optional', 'wc-serial-numbers' ),
				'class'       => '',
				'type'        => 'text',
				'callback'    => 'woocommerce_wp_text_input',
				'desc_tip'    => true,
			),
			array(
				'id'   => 'software_fields',
				'type' => 'end_group',
			),
		];

		return array_merge( $fields, $software_fields );
	}

	/**
	 * Save data for Simple product.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public static function save_product( $post_id ) {
		if ( Helper::is_valid_product_type( $post_id ) ) {
			$is_serial    = filter_input( INPUT_POST, '_is_serial_numbers', FILTER_SANITIZE_STRING );
			$source       = filter_input( INPUT_POST, '_serial_numbers_key_source', FILTER_SANITIZE_STRING );
			$generator_id = filter_input( INPUT_POST, '_serial_numbers_generator_id', FILTER_SANITIZE_NUMBER_INT );
			$patern       = filter_input( INPUT_POST, '_serial_numbers_pattern', FILTER_SANITIZE_STRING );
			// if generator id is not set but source is set as generator then fall back to stock.
			if ( 'on' === $is_serial && empty( $generator_id ) && 'generator' === $source ) {
				$source = 'pre_generated';
			}

			$common_metas = [
				'_is_serial_numbers'           => 'on' === $is_serial ? 'yes' : '',
				'_serial_numbers_key_source'   => $source,
				'_serial_numbers_generator_id' => $generator_id,
				'_serial_numbers_pattern'      => $patern,
			];

			foreach ( $common_metas as $common_key => $common_value ) {
				if ( empty( $common_value ) ) {
					delete_post_meta( $post_id, $common_key );
				} else {
					update_post_meta( $post_id, $common_key, wc_clean( $common_value ) );
				}
			}

			if ( Helper::is_software_support_enabled() ) {
				$software_fields = array(
					'_serial_numbers_software_version',
					'_serial_numbers_software_author',
					'_serial_numbers_software_last_updated',
					'_serial_numbers_software_upgrade_notice',
				);
				foreach ( $software_fields as $key => $software_field ) {
					$value = filter_input( INPUT_POST, $software_field, FILTER_SANITIZE_STRING );
					if ( empty( $value ) ) {
						delete_post_meta( $post_id, $software_field );
					} else {
						update_post_meta( $post_id, $software_field, wc_clean( $value ) );
					}
				}
			}

			$software_number_fields = array(
				'_serial_numbers_activation_limit',
				'_serial_numbers_validity',
			);
			foreach ( $software_number_fields as $key => $software_number_field ) {
				$value = filter_input( INPUT_POST, $software_number_field, FILTER_SANITIZE_NUMBER_INT );

				if ( empty( $value ) ) {
					delete_post_meta( $post_id, $software_number_field );
				} else {
					update_post_meta( $post_id, $software_number_field, absint( $value ) );
				}
			}
		}

		do_action( 'wc_serial_numbers_save_product', $post_id, $_POST );
	}


	/**
	 * Get generators.
	 *
	 * @since 1.3.1
	 * @return array
	 */
	public static function get_generators() {
		$all_generators = Generators::query(
			array(
				'fields'   => [ 'pattern', 'id' ],
				'per_page' => - 1,
			)
		);
		$generators     = array();
		if ( is_array( $all_generators ) && count( $all_generators ) ) {
			$generators = wp_list_pluck( $generators, 'pattern', 'id' );
		}

		return $generators;
	}
}

new Admin_Product();
