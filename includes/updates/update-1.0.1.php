<?php
function wcsn_update_1_0_1() {
	WCSN_Install::activate();

	$serial_numbers = get_posts( array(
		'post_type' => 'wsn_serial_number',
		'nopaging'  => true,
	) );

	foreach ( $serial_numbers as $post ) {
		$validity = get_post_meta( $post->ID, 'validity', true );
		$order    = get_post_meta( $post->ID, 'order', true );
		$data     = array(
			'serial_key'       => $post->post_title,
			'license_image'    => ! empty( $_POST['license_image'] ) ? sanitize_text_field( $_POST['license_image'] ) : '',
			'product_id'       => get_post_meta( $post->ID, 'product', true ),
			'activation_limit' => get_post_meta( $post->ID, 'max_instance', true ),
			'validity'         => is_numeric( $validity ) ? $validity : 0,
			'expire_date'      => is_string( $validity ) && ( strtotime( $validity ) > strtotime( '2019-01-01' ) ) ? date( 'Y-m-d', strtotime( $validity ) ) : 0,
			'status'           => empty( intval( $order ) ) ? 'new' : 'active',
			'order_id'         => intval( $order ),
		);
		wcsn()->serial_number->insert( $data );
	}
}

wcsn_update_1_0_1();
