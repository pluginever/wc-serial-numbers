<?php

namespace WooCommerceSerialNumbers\Admin;

defined( 'ABSPATH' ) || exit;

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
}
