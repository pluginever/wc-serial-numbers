<?php

namespace Pluginever\WCSerialNumbers;

class Ajax {
	function __construct() {
		add_action( 'wp_ajax_add_serial_number', [ $this, 'wsn_add_serial_number' ] );
		add_action( 'wp_ajax_enable_serial_number', [ $this, 'wsn_enable_serial_number' ] );
		add_action( 'wp_ajax_load_tab_data', [ $this, 'wsn_load_tab_data' ] );
	}

	function wsn_add_serial_number() {

		$serial_number = $_REQUEST['serial_number'];
		$product       = $_REQUEST['product'];
		$usage_limit   = $_REQUEST['usage_limit'];
		$expires_on    = $_REQUEST['expires_on'];

		if ( ! empty( $serial_number ) ) {

			$post_id = wp_insert_post( [
				'post_title'  => $serial_number,
				'post_type'   => 'serial_number',
				'post_status' => 'publish',
			] );

			update_post_meta( $post_id, 'product', $product );
			update_post_meta( $post_id, 'usage_limit', $usage_limit );
			update_post_meta( $post_id, 'expires_on', $expires_on );

			$posts = get_posts( [
				'post_type'      => 'serial_number',
				'meta_key'       => 'product',
				'meta_value'     => $product,
				'posts_per_page' => - 1
			] );

			ob_start();
			foreach ( $posts as $post ) {
				setup_postdata( $post );
				$usage_limit = get_post_meta( $post->ID, 'usage_limit', true );
				$expires_on  = get_post_meta( $post->ID, 'expires_on', true );
				echo '
			<tr>
				<td>' . get_the_title( $post->ID ) . '</td>
				<td>' . $usage_limit . '</td>
				<td>' . $expires_on . '</td>
			</tr>';
			}
			$html = ob_get_clean();

			$response = array( 'posts' => $html );
		} else {
			$response = array( 'empty_serial' => true );
		}

		wp_send_json_success( $response );
	}

	function wsn_enable_serial_number() {

		$post_id = $_REQUEST['post_id'];

		$is_serial_number_enabled = $_REQUEST['enable_serial_number'];

		error_log($is_serial_number_enabled);
		//die();

		update_post_meta( $post_id, 'enable_serial_number', $is_serial_number_enabled  );

		if($is_serial_number_enabled == 'enable'){

			set_query_var( 'is_product_tab', $post_id );

			ob_start();

			include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';

			echo '<h3 style="margin-bottom: -30px;">Available license number for this product:</h3>';

			require WPWSN_TEMPLATES_DIR . '/serial-numbers-page.php';

			require WPWSN_TEMPLATES_DIR . '/add-serial-number.php';

			$html = ob_get_clean();
		}else{
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

	function wsn_load_tab_data() {

		$post_id = $_REQUEST['post_id'];

		set_query_var( 'is_product_tab', $post_id );

		$is_serial_number_enabled = get_post_meta( $post_id, 'enable_serial_number', true );

		//error_log(print_r($is_serial_number_enabled));
		//die();

		if ( $is_serial_number_enabled == 'enable') {
			ob_start();
			include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';

			echo '<h3 style="margin-bottom: -30px;">Available license number for this product:</h3>';

			require WPWSN_TEMPLATES_DIR . '/serial-numbers-page.php';

			require WPWSN_TEMPLATES_DIR . '/add-serial-number.php';

			$html = ob_get_clean();
		} else {
			ob_start();
			include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';
			$html = ob_get_clean();
		}

		wp_send_json_success( [
			'html' => $html,
		] );
	}
}
