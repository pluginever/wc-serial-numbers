<?php

namespace Pluginever\WCSerialNumbers;

class Ajax {
	function __construct() {
		add_action( 'wp_ajax_add_serial_number', [ $this, 'wsn_add_serial_number' ] );
		error_log( 'aa' );
	}

	function wsn_add_serial_number() {
		error_log( print_r( $_REQUEST, true ) );
		$serial_number = $_REQUEST['serial_number'];
		$product       = $_REQUEST['product'];
		$usage_limit   = $_REQUEST['usage_limit'];
		$expires_on    = $_REQUEST['expires_on'];

		$post_id = wp_insert_post( [
			'post_title'  => $serial_number,
			'post_type'   => 'serial_number',
			'post_status' => 'publish',
		] );

		update_post_meta( $post_id, 'product', $product );
		update_post_meta( $post_id, 'usage_limit', $usage_limit );
		update_post_meta( $post_id, 'expires_on', $expires_on );

		$posts = get_posts( [ 'post_type' => 'serial_number', 'meta_key' => 'product', 'meta_value' => $product ] );

		$html = '';
		foreach ( $posts as $post ) {
			setup_postdata( $post );
			$usage_limit = get_post_meta( $post->ID, 'usage_limit', true );
			$expires_on  = get_post_meta( $post->ID, 'expires_on', true );
			$html        .= '
			<tr>
				<td>' . get_the_title( $post->ID ) . '</td>
				<td>' . $usage_limit . '</td>
				<td>' . $expires_on . '</td>
			</tr>';
		}
		wp_reset_postdata();

		wp_send_json( [
			'posts' => $html
		] );
	}
}
