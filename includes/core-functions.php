<?php
//function prefix wc_serial_numbers

/*
 * Get Plugin directory templates part
 * */

function wsn_get_template_part($template_name, $wsnp = false)
{

	$template_dir = $wsnp ? WPWSNP_TEMPLATES_DIR : WPWSN_TEMPLATES_DIR;

	return include $template_dir . '/' . $template_name . '.php';
}


/*
 * Register Serial Numbers Post Type
 *
 * @since 1.0.0
 *
 * @return mixed
 * */

add_action('init', 'wsn_register_posttypes');

function wsn_register_posttypes()
{
	register_post_type('wsn_serial_number', array(
		'labels'              => 'Serial Numbers',
		'hierarchical'        => false,
		'supports'            => array('title'),
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
	));
}

/*
 * Redirect the user with custom message
 * */

function wsn_redirect_with_message($url, $code, $type = 'success', $args = array())
{
	$redirect_url = add_query_arg(wp_parse_args($args, array(
		'feedback' => $type,
		'code'     => $code,
	)), $url);
	wp_redirect($redirect_url);
	exit();
}

function wsn_get_feedback_message($code)
{
	switch ($code) {
		case 'empty_serial_number':
			return __('The Serial Number is empty. Please enter a serial number and try again', 'wc-serial-numbers');
			break;
		case 'empty_product':
			return __('The product is empty. Please select a product and try again', 'wc-serial-numbers');
			break;
	}
}

add_filter('woocommerce_product_data_tabs', 'wsn_serial_number_tab');
add_action('woocommerce_product_data_panels', 'wsn_serial_number_tab_panel');

/**
 * Serial number tab
 *
 * @param $product_data_tabs
 *
 * @return mixed
 */
function wsn_serial_number_tab($product_data_tabs)
{

	$product_data_tabs['serial_numbers'] = array(
		'label'  => __('Serial Numbers', 'serial-numbers'),
		'target' => 'serial_numbers_data',
		'class'  => 'ever-serial_numbers_tab hide_if_external hide_if_grouped',
	);

	return $product_data_tabs;
}

/**
 * Serial number tab panel
 */
function wsn_serial_number_tab_panel()
{
	include WPWSN_TEMPLATES_DIR . '/product-serial-number-tab.php';
}

function wsn_get_products($args = []){

	$args = array_merge($args, array(
		'limit' => -1,
	));

	return wc_get_products( $args );
}

/**
 * Get serial number posts
 *
 * @param $args
 *
 * @return array
 */
function wsn_get_serial_numbers($args)
{

	$args = wp_parse_args($args, [
		'post_type'      => 'wsn_serial_number',
		'posts_per_page' => -1,
		'meta_key'       => '',
		'meta_value'     => '',
		'order_by'       => 'date',
		'order'          => 'DESC',
	]);

	return get_posts($args);
}

/**
 * Get the remain usage for serial number
 *
 * @param $serial_number_id
 */

function wsn_used_deliver_times($serial_number_id)
{

	$deliver_times        = get_post_meta($serial_number_id, 'deliver_times', true);
	$remain_deliver_times = get_post_meta($serial_number_id, 'remain_deliver_times', true);

	return $remain_deliver_times = ($deliver_times - $remain_deliver_times);
}


/**
 * Return saved setting options
 *
 * @param $key
 * @param string $default
 * @param string $section
 *
 * @return string
 */
function wsn_get_settings($key, $default = '', $section = '')
{
	$option = get_option($section, []);

	return !empty($option[$key]) ? $option[$key] : $default;
}

/**
 * get order customer details
 *
 * @since 1.0.0
 *
 * @param $key
 * @param $order
 *
 * @return mixed
 */

function wsn_get_customer_detail($key, $order)
{
	return $order->data['billing'][$key];
}


/**
 * Check is Pro active
 *
 * @since 1.0.0
 *
 * @return boolean
 */
function wsn_is_wsnp()
{
	return apply_filters('is_wsnp', false);
}

/**
 * add disabled attribute if if Pro is not active
 *
 * @since 1.0.0
 *
 * @return string
 */

function wsn_disabled()
{
	return wsn_is_wsnp() ? '' : 'disabled';
}

/**
 * add ever-disabled class if if wsn is not wsnp
 *
 * @since 1.0.0
 *
 * @return string
 */
function wsn_class_disabled()
{
	return wsn_is_wsnp() ? '' : 'ever-disabled';
}



