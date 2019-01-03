<?php
//function prefix wc_serial_numbers

/*
 * Get Plugin directory templates part
 * */

function wsn_get_template_part( $template_name ) {
	return include WPWSN_TEMPLATES_DIR . '/' . $template_name . '.php';
}


/*
 * Register Serial Numbers Post Type
 * */

add_action( 'init', 'wsn_register_posttypes' );

function wsn_register_posttypes() {
	register_post_type( 'serial_number', array(
		'labels'              => 'Serial Numbers',
		'hierarchical'        => false,
		'supports'            => array( 'title' ),
		'public'              => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'query_var'           => false,
		'can_export'          => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'capabilities'        => array(
			'create_posts' => 'do_not_allow', // false < WP 4.5, credit @Ewout
		),
		'map_meta_cap'        => true,
	) );
}

/*
 * Redirect the user with custom message
 * */

function wsn_redirect_with_message( $url, $code, $type = 'success', $args = array() ) {
	$redirect_url = add_query_arg( wp_parse_args( $args, array(
		'feedback' => $type,
		'code'     => $code,
	) ), $url );
	wp_redirect( $redirect_url );
	exit();
}

function wsn_get_feedback_message( $code ) {
	switch ( $code ) {
		case 'empty_serial_number':
			return __( 'The Serial Number is empty. Please enter a serial number and try again', 'wc-serial-numbers' );
			break;
		case 'empty_product':
			return __( 'The product is empty. Please select a product and try again', 'wc-serial-numbers' );
			break;
	}
}

add_filter( 'woocommerce_product_data_tabs', 'wsn_serial_number_tab' );
add_action( 'woocommerce_product_data_panels', 'wsn_serial_number_tab_panel' );

/**
 * Serial number tab
 * @param $product_data_tabs
 *
 * @return mixed
 */
function wsn_serial_number_tab( $product_data_tabs ) {

	$product_data_tabs['serial_numbers'] = array(
		'label'  => __( 'Serial Numbers', 'serial-numbers' ),
		'target' => 'serial_numbers_data',
		'class'  => 'hide_if_external hide_if_grouped',
	);

	return $product_data_tabs;
}

/**
 * Serial number tab panel
 */
function wsn_serial_number_tab_panel() {
	include WPWSN_TEMPLATES_DIR . '/product-serial-number-tab.php';
}

/**
 * Get serial number posts
 *
 * @param $args
 *
 * @return array
 */
function wsn_get_serial_numbers( $args ) {

	$args = wp_parse_args( $args, [
		'post_type'      => 'serial_number',
		'posts_per_page' => - 1,
		'meta_key'       => '',
		'meta_value'     => '',
		'order_by'       => 'date',
		'order'          => 'DESC',
	] );

	return get_posts( $args );
}

/**
 * Get the remain usage for serial number
 *
 * @param $serial_number_id
 */

function wsn_remain_usage( $serial_number_id ) {

	$deliver_times  = get_post_meta( $serial_number_id, 'deliver_times', true );
	$remain_deliver_times = get_post_meta( $serial_number_id, 'remain_deliver_times', true );

	return $remain_deliver_times = $deliver_times - $remain_deliver_times;
}


