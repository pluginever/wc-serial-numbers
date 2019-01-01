<?php

namespace Pluginever\WCSerialNumbers;

class Ajax {
	function __construct() {
		add_action( 'wp_ajax_add_serial_number', [ $this, 'wsn_add_serial_number' ] );
		add_action( 'wp_ajax_enable_serial_number', [ $this, 'wsn_enable_serial_number' ] );
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
			$html     = ob_get_clean();
			$response = array( 'posts' => $html );
		} else {
			$response = array( 'empty_serial' => true );
		}
		wp_send_json_success( $response );
	}

	function wsn_enable_serial_number() {
		$product              = $_REQUEST['product'];
		$enable_serial_number = $_REQUEST['enable_serial_number'];

		update_post_meta( $product, 'enable_serial_number', $enable_serial_number );

		wp_send_json_success(
			[
				'enable_serial_number' => $enable_serial_number
			]
		);
	}
}
