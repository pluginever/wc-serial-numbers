<?php

namespace PluginEver\SerialNumbers\Admin;

use PluginEver\SerialNumbers\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Class Reports.
 *
 * @since   1.0.0
 * @package PluginEver\SerialNumbers\Admin
 */
class Reports extends Component {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'wc_serial_numbers_reports_tab_stock', array( $this, 'stock_tab' ) );
	}

	/**
	 * Output reports page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_page() {
		wp_verify_nonce( '_nonce' );
		$tabs = array(
			'stock' => __( 'Stock', 'wc-serial-numbers' ),
		);

		$tabs        = apply_filters( 'wc_serial_numbers_reports_tabs', $tabs );
		$tab_ids     = array_keys( $tabs );
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : reset( $tab_ids );
		$page        = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		$this->app->template->view(
			'admin.reports',
			array(
				'tabs'        => $tabs,
				'current_tab' => $current_tab,
				'page'        => $page,
			)
		);
	}

	/**
	 * Stock section.
	 *
	 * @since 1.4.6
	 * @return void
	 */
	public function stock_tab() {
		$this->app->template->view( 'admin.list-stock' );
	}
}
