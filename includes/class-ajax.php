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

	function __construct()
	{
		add_action('wp_ajax_load_variations', [$this, 'load_variations']);

	}

	function load_variations(){

		$post_id = $_REQUEST['post_id'];

		$product = wc_get_product($post_id);

		$variations = $product->get_children();

		if(!empty($variations)){

			ob_start();

			echo '<option value="">'.__('Main Product', 'wc-serial-numbers').'</option>';

			foreach ($variations as $variation){ ?>
				<option value="<?php echo $variation ?>"><?php echo get_the_title($variation) ?></option>
			<?php }

			$html = ob_get_clean();


		}else{
			$html = '<option value="">'.__('Main Product', 'wc-serial-numbers').'</option>';
		}

		wp_send_json_success([
			'html' => $html
		]);

	}

}
