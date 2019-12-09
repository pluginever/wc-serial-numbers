<?php
require 'wp-blog-header.php';

if ( ! current_user_can( 'manage_options' ) ) die();


// API variables, please override
$base_url    = 'http://wcserialnumbers.test';
$email       = 'hello@manik.com';
$product_id  = '83';
$license_key = 'johndoe';
$instance    = 'http://example2.com';

$request = ( isset( $_GET['request'] ) ) ? $_GET['request'] : '';

// Fire away!
function execute_request( $args ) {
	global $base_url;
	$target_url = add_query_arg( $args, $base_url );
	$data       = wp_remote_get( $target_url );
	var_dump( $data['body'] );
}

$links = array(
	'check'        => 'Check request',
	'activation'   => 'Activation request',
	'deactivation' => 'Deactivation',
	'version_check'      => 'Version Check',
);

foreach ( $links as $key => $value ) {
	echo '<a href="' . add_query_arg( 'request', $key ) . '">' . $value . '</a> | ';
}

// Valid check request
if ( $request == 'check' ) {
	$args = array(
		'wc-api'     => 'serial-numbers-api',
		'request'    => 'check',
		'email'      => $email,
		'serial_key' => $license_key,
		'product_id' => $product_id
	);

	echo '<b>Valid check request:</b><br />';
	execute_request( $args );
}

// Valid activation request
if ( $request == 'activation' ) {
	$args = array(
		'wc-api'     => 'serial-numbers-api',
		'request'    => 'activate',
		'email'      => $email,
		'serial_key' => $license_key,
		'product_id' => $product_id,
		'instance'   => $instance
	);

	echo '<b>Valid activation request:</b><br />';
	execute_request( $args );
}


// Valid deactivation reset request
if ( $request == 'deactivation' ) {
	$args = array(
		'wc-api'     => 'serial-numbers-api',
		'request'    => 'deactivate',
		'email'      => $email,
		'serial_key' => $license_key,
		'product_id' => $product_id,
		'instance'   => $instance,
	);

	echo '<b>Valid deactivation request:</b><br />';
	execute_request( $args );
}

// Version check
if ( $request == 'version_check' ) {
	$args = array(
		'wc-api'     => 'serial-numbers-api',
		'request'    => 'version_check',
		'email'      => $email,
		'serial_key' => $license_key,
		'product_id' => $product_id,
		'instance'   => $instance,
	);

	echo '<b>Valid Version check request:</b><br />';
	execute_request( $args );
}
