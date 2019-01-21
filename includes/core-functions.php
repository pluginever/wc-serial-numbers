<?php
//function prefix wc_serial_number_pro


/**
 * Active the Pro version
 *
 * @since 1.0.0
 *
 * @return boolean
 */
add_filter( 'is_wsnp', function ( $status ) {
	return true;
} );


/**
 * Add generator table to the generator page
 *
 * @since 1.0.0
 *
 * @return string
 */

add_filter( 'generate_serial_number', function () {
	include WPWSNP_TEMPLATES_DIR . '/generate-serial-number.php';
} );

/*
 * Register Serial Numbers Post Type
 *
 * @since 1.0.0
 *
 * @return mixed
 * */

add_action( 'init', 'wsnp_register_posttypes' );

function wsnp_register_posttypes() {

	register_post_type( 'wsnp_generator_rule', array(
		'labels'              => false,
		'hierarchical'        => false,
		'supports'            => false,
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

	register_post_type( 'wsnp_notification', array(
		'labels'              => false,
		'hierarchical'        => false,
		'supports'            => false,
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


/**
 * Get Serial number Generator rules
 *
 * @param $args
 *
 * @return array|object|mixed
 */
function wsnp_get_generator_rules( $args ) {

	$args = wp_parse_args( $args, [
		'post_type'      => 'wsnp_generator_rule',
		'posts_per_page' => - 1,
		's'              => '',
		'meta_key'       => 'product',
		'meta_value'     => '',
		'order_by'       => 'date',
		'order'          => 'DESC',
	] );

	return get_posts( $args );
}

/**
 * Send email notification when serial number stock low
 *
 * @since 1.0.0
 *
 * @param $message
 */

function wsn_send_email_notification( $message ) {

	global $woocommerce;

	$to      = wsn_get_settings( 'wsn_admin_bar_notification_email', '', 'wsn_notification_settings' );
	$subject = __( 'Serial Numbers stock running low', 'wc-serial-number-pro' );

	$headers = apply_filters( 'woocommerce_email_headers', '', 'rewards_message' );

	$heading = __( 'Please add more serial number for the following items', 'wc-serial-number-pro' );

	$mailer = $woocommerce->mailer();

	$message = $mailer->wrap_message( $heading, $message );

	$mailer->send( $to, $subject, $message, $headers, array() );

}

add_action( 'wsn_send_email_notification', 'wsn_send_email_notification' );



function wsnp_add_settings_field($sections){
	$sections[] = array(
		'id'    => 'wsn_serial_generator_settings',
		'title' => __('Serial Numbers Generator', 'wc-serial-number-pro')
	);

	return $sections;
}

add_filter('wc_serial_numbers_settings_sections', 'wsnp_add_settings_field');
