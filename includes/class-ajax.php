<?php

namespace Pluginever\WCSerialNumberPro;

class Ajax {

	/**
	 * Ajax constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	function __construct() {
		add_action('wp_ajax_load_variations', [$this, 'load_variations']);
		add_action('wp_ajax_wsn_generate_numbers', [$this, 'generate_numbers']);

	}

	/**
	 * Load available variations for a product and append it to product variation
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	function load_variations() {

		$post_id = $_REQUEST['post_id'];

		$product = wc_get_product($post_id);

		$variations = $product->get_children();

		if (!empty($variations)) {

			ob_start();

			echo '<option value="">' . __('Main Product', 'wc-serial-numbers') . '</option>';

			foreach ($variations as $variation) { ?>
				<option value="<?php echo $variation ?>"><?php echo get_the_title($variation) ?></option>
			<?php }

			$html = ob_get_clean();


		} else {
			$html = '<option value="">' . __('Main Product', 'wc-serial-numbers') . '</option>';
		}

		wp_send_json_success([
			'html' => $html
		]);

	}

	/**
	 * Generate serial numbers automatically following the generator rule via AJAX
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */

	function generate_numbers() {

		$rule_id = $_REQUEST['rule_id'];
		$limit   = $_REQUEST['limit'];

		$product       = get_post_meta($rule_id, 'product', true);
		$variation     = get_post_meta($rule_id, 'variation', true);
		$prefix        = get_post_meta($rule_id, 'prefix', true);
		$chunks_number = get_post_meta($rule_id, 'chunks_number', true);
		$chunk_length  = get_post_meta($rule_id, 'chunk_length', true);
		$suffix        = get_post_meta($rule_id, 'suffix', true);
		$max_instance  = get_post_meta($rule_id, 'max_instance', true);
		$validity      = get_post_meta($rule_id, 'validity', true);
		$validity_type = get_post_meta($rule_id, 'validity_type', true);

		$tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$max    = strlen($tokens) - 1;

		for ($i = 0; $i < $limit; $i++) {

			$serial_number = '';

			for ($j = 0; $j < $chunks_number; $j++) {

				for ($k = 0; $k < $chunk_length; $k++) {
					$serial_number .= $tokens[rand(0, $max)];
				}

				$serial_number .= '-';

			}

			$serial_number = $prefix . rtrim($serial_number, '-') . $suffix;

			$is_exists = get_page_by_title($serial_number, OBJECT, 'wsn_serial_number');

			if ($is_exists) {
				continue;
			}

			$post_id = wp_insert_post(array(
				'post_title'  => $serial_number,
				'post_type'   => 'wsn_serial_number',
				'post_status' => 'publish',
				'meta_input'  => array(
					'product'       => $product,
					'variation'     => $variation,
					'max_instance'  => $max_instance,
					'deliver_times' => $max_instance,
					'used'          => 0,
					'validity_type' => $validity_type,
					'validity'      => $validity,
				),
			));

		}

		wp_send_json_success(array(
			'response' => 1,
		));

	}

}
