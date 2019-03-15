<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tracker class
 */
class WCSN_Tracker extends \Pluginever_Insights {

	public function __construct() {

		$notice = __( 'Want to help make <strong>WooCommerce Serial Numbers</strong> even more awesome? Allow PluginEver to collect non-sensitive diagnostic data and usage information.', 'wc-serial-numbers' );

		parent::__construct( 'wc-serial-numbers', 'WooCommerce Serial Numbers', WC_SERIAL_NUMBERS_FILE, $notice );
	}

	/**
	 * Get the extra data
	 *
	 * @return array
	 */
	protected function get_extra_data() {
		$data = array(
			'wc_products'    => $this->get_post_count( 'product' ),
			'serial_numbers' => wcsn_get_serial_numbers( [ 'number' => - 1 ], true ),
			'is_pro'         => wc_serial_numbers()->is_pro_installed() ? 'yes' : 'no',
		);

		return $data;
	}


	/**
	 * Explain the user which data we collect
	 *
	 * @return array
	 */
	protected function data_we_collect() {
		$data = array(
			'Server environment details (php, mysql, server, WordPress versions)',
			'Number of WC Products in your site',
			'Site language',
			'Number of active and inactive plugins',
			'Site name and url',
			'Your name and email address',
		);

		return $data;
	}


}

new WCSN_Tracker();
