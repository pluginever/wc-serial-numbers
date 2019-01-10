<?php

namespace Pluginever\WCSerialNumbers;


class Ajax {
	/**
	 * Ajax constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	function __construct() {
		add_action('wp_ajax_add_serial_number', [$this, 'add_serial_number']);
		add_action('wp_ajax_enable_serial_number', [$this, 'enable_serial_number']);
		add_action('wp_ajax_load_tab_data', [$this, 'load_tab_data']);
	}

	/**
	 * Add serial number from product edit tab via Ajax Request
	 *
	 * @since 1.0.0
	 *
	 */

	function add_serial_number() {

		$product       = intval($_REQUEST['product']);
		$serial_number = sanitize_text_field($_REQUEST['serial_number']);
		$image_license = esc_url($_REQUEST['image_license']);
		$deliver_times = intval($_REQUEST['deliver_times']);
		$max_instance  = intval($_REQUEST['max_instance']);
		$validity      = esc_attr($_REQUEST['validity']);

		if (empty($serial_number) && empty($image_license)) {

			$response = array('empty_serial' => '<div class="notice notice-error is-dismissible"><p><strong>' . __('Please enter a valid serial number', 'wc-serial-numbers') . '</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>');

		} else {

			$meta_input = array(
				'product'       => $product,
				'image_license' => $image_license,
				'deliver_times' => $deliver_times,
				'max_instance'  => $max_instance,
				'validity'      => $validity,
			);

			$post_id = wp_insert_post([
				'post_title'  => $serial_number,
				'post_type'   => 'wsn_serial_number',
				'post_status' => 'publish',
				'meta_input'  => $meta_input,
			]);


			$is_serial_number_enabled = 'enable';

			set_query_var('is_product_tab', $product);

			ob_start();

			include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';

			echo '<h3 style="margin-bottom: -30px;">'._e('Available license number for this product:','wc-serial-numbers').'</h3>';

			require WPWSN_TEMPLATES_DIR . '/serial-numbers-page.php';

			require WPWSN_TEMPLATES_DIR . '/add-serial-number-page.php';

			$html = ob_get_clean();

			$response = array('html' => $html);

		}

		wp_send_json_success($response);
	}

	/**
	 * Enable number from product edit tab via Ajax Request
	 *
	 * @since 1.0.0
	 *
	 * @return string|void
	 */
	function enable_serial_number() {

		$post_id = intval($_REQUEST['post_id']);

		$is_serial_number_enabled = sanitize_text_field($_REQUEST['enable_serial_number']);


		update_post_meta($post_id, 'enable_serial_number', $is_serial_number_enabled);

		if ($is_serial_number_enabled == 'enable') {

			set_query_var('is_product_tab', $post_id);

			ob_start();

			include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';

			echo '<h3 style="margin-bottom: -30px;">'._e('Available license number for this product:','wc-serial-numbers').'</h3>';

			require WPWSN_TEMPLATES_DIR . '/serial-numbers-page.php';

			require WPWSN_TEMPLATES_DIR . '/add-serial-number-page.php';

			$html = ob_get_clean();
		} else {
			ob_start();
			include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';
			$html = ob_get_clean();
		}

		wp_send_json_success(
			[
				'html' => $html
			]
		);
	}


	/**
	 * Load product edit tab panel after clicking serial number tab
	 *
	 * @since 1.0.0
	 *
	 */
	function load_tab_data() {

		$post_id = intval($_REQUEST['post_id']);

		set_query_var('is_product_tab', $post_id);

		$is_serial_number_enabled = get_post_meta($post_id, 'enable_serial_number', true);


		if ($is_serial_number_enabled == 'enable') {
			ob_start();
			include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';

			echo '<h3 style="margin-bottom: -30px;">'._e('Available license number for this product:','wc-serial-numbers').'</h3>';

			require WPWSN_TEMPLATES_DIR . '/serial-numbers-page.php';

			require WPWSN_TEMPLATES_DIR . '/add-serial-number-page.php';

			$html = ob_get_clean();
		} else {
			ob_start();
			include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';
			$html = ob_get_clean();
		}

		wp_send_json_success([
			'html' => $html,
		]);
	}
}
