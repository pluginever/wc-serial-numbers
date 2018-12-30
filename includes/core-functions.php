<?php
//function prefix wc_serial_numbers

/*
 * Get Plugin directory templates part
 * */

function wsn_get_template_part($template_name){
	return include WPWSN_TEMPLATES_DIR.'/'.$template_name.'.php';
}


/*
 * Register Serial Numbers Post Type
 * */

add_action( 'init', 'wsn_register_posttypes' );

function wsn_register_posttypes(){
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
		'capabilities' => array(
			'create_posts' => 'do_not_allow', // false < WP 4.5, credit @Ewout
		),
		'map_meta_cap' => true,
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

function wsn_get_feedback_message($code){
	switch ($code){
		case 'empty_product':
			return __('The product is empty. Please select a product and try again', 'wc-serial-numbers');
			break;
		case 'empty_usage_limit':
			return __('The Usage Limit is empty. Please select a Limit and try again', 'wc-serial-numbers');
			break;
	}
}
