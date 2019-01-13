<?php
//function prefix wc_serial_number_pro


/**
 * Active the Pro version
 *
 * @since 1.0.0
 *
 * @return boolean
 */
add_filter('is_wsnp', function ($status) {
	return true;
});


/**
 * Add generator table to the generator page
 *
 * @since 1.0.0
 *
 * @return string
 */

add_filter('generate_serial_number', function () {
	include WPWSNP_TEMPLATES_DIR . '/generate-serial-number.php';
});

/*
 * Register Serial Numbers Post Type
 *
 * @since 1.0.0
 *
 * @return mixed
 * */

add_action('init', 'wsnp_register_posttypes');

function wsnp_register_posttypes() {

	register_post_type('wsnp_generator_rule', array(
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
	));

	register_post_type('wsnp_notification', array(
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
	));

}


/**
 * Get Serial number Generator rules
 *
 * @param $args
 *
 * @return array|object|mixed
 */
function wsnp_get_generator_rules($args) {

	$args = wp_parse_args($args, [
		'post_type'      => 'wsnp_generator_rule',
		'posts_per_page' => -1,
		'meta_key'       => '',
		'meta_value'     => '',
		'order_by'       => 'date',
		'order'          => 'DESC',
	]);

	return get_posts($args);
}

/**
 * Update serial number notification posts when a serial number enable or disable for any product
 *
 * @since  1.0.0
 *
 * @param $product_id
 *
 * @retun void
 */

function wsn_update_notification_on_enable_disable($product_id, $status) {

	$numbers = wsn_get_available_numbers($product_id);

	$is_exists = get_page_by_title($product_id, OBJECT, 'wsnp_notification');

	if ($is_exists) {
		wp_update_post(array(
			'ID'             => $is_exists->ID,
			'comment_status' => $status,
		));

	}

	return;

}

add_action('wsn_update_notification_on_enable_disable', 'wsn_update_notification_on_enable_disable', 10, 2);


/**
 * Update serial number notification posts when a order made or a serial number deleted
 *
 * @since  1.0.0
 *
 * @param $product_id
 *
 * @retun void
 */

function wsn_update_notification_on_order_delete($product_id) {

	$numbers = wsn_get_available_numbers($product_id);

	$show_number = wsn_get_settings('wsn_admin_bar_notification_number', '', 'wsn_notification_settings');

	$count_number = count($numbers);

	if ($count_number >= $show_number) {
		return;
	}

	$is_exists = get_page_by_title($product_id, OBJECT, 'wsnp_notification');

	if ($is_exists) {
		wp_update_post(array(
			'ID'             => $is_exists->ID,
			'post_content'   => $count_number,
			'post_status'    => 'publish',
			'comment_status' => 'enable',
		));

	}

	return;

}

add_action('wsn_update_notification_on_order_delete', 'wsn_update_notification_on_order_delete');


/**
 * Update serial number notification posts when a new order added or order edited
 *
 * @param $product_id
 *
 * @return void
 */

function wsn_update_notification_on_add_edit($product_id) {


	$show_number = wsn_get_settings('wsn_admin_bar_notification_number', 5, 'wsn_notification_settings');

	$numbers = wsn_get_available_numbers($product_id);

	$count_number = count($numbers);

	$is_exists = get_page_by_title($product_id, OBJECT, 'wsnp_notification');

	if ($count_number >= $show_number) {

		if ($is_exists) {
			wp_update_post(array(
				'ID'             => $is_exists->ID,
				'post_content'   => $count_number,
				'post_status'    => 'draft',
				'comment_status' => 'disable',
			));
		}

		return;
	}

	if ($is_exists) {
		wp_update_post(array(
			'ID'             => $is_exists->ID,
			'post_content'   => $count_number,
			'post_status'    => 'publish',
			'comment_status' => 'enable',
		));

		return;
	}

	wp_insert_post(array(
		'post_type'      => 'wsnp_notification',
		'post_title'     => $product_id,
		'post_content'   => $count_number,
		'post_status'    => 'publish',
		'comment_status' => 'enable',
	));

	return;

}

add_action('wsn_update_notification_on_add_edit', 'wsn_update_notification_on_add_edit', 10, 2);

/**
 * Show admin bar notification count number
 *
 * @since  1.0.0
 *
 * @return false|string
 */

add_filter('wsn_admin_bar_notification', function () {

	$count = count($posts = get_posts([
		'post_type'      => 'wsnp_notification',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'comment_status' => 'enable'
	]));

	$show_notification = wsn_get_settings('wsn_admin_bar_notification', 'on', 'wsn_notification_settings');

	if ($show_notification == 'on' and $count > 0) {
		return '<span class="wsn_admin_bar_notification"></span>';
	}

	return false;

});

/**
 * Sho admin bar notification list
 *
 * @since 1.0.0
 *
 * @param $html
 *
 * @return false|string
 */

function wsn_admin_bar_notification_list($html) {

	$show_notification = wsn_get_settings('wsn_admin_bar_notification', 'on', 'wsn_notification_settings');

	if ($show_notification != 'on') {
		return false;
	}

	if (empty(get_post_type())) {
		global $post;
	}

	$posts = get_posts([
		'post_type'      => 'wsnp_notification',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'comment_status' => 'enable'
	]);


	if (!empty($posts)) {

		$message = '';

		ob_start();

		echo '<span class="ever-notification"><span class="alert">' . sprintf('%02d', count($posts)) . '</span></span> <ul class="ever-notification-list alert">';

		foreach ($posts as $post) {

			setup_postdata($post);


			$name  = '<strong>' . get_the_title(get_the_title($post->ID)) . '</strong>';
			$count = '<strong>' . (int)get_the_content() . '</strong>';

			$msg = __('Please add serial numbers for ', 'wc-serial-numbers') . $name . ', ' . $count . __(' Serial number left', 'wc-serial-numbers');

			$message .= '<tr><td>'.$msg.'</td>';

			echo '<li>'.$msg.'</li>';


		}

		wp_reset_postdata();

		echo '</ul>'; //End the list

		$html = ob_get_clean();

		//Send email notification if serial number stock low
		$message = '<table>'.$message.'</table>';
		do_action('wsn_send_email_notification', $message);

	}

	return $html;
}

add_filter('wsn_admin_bar_notification_list', 'wsn_admin_bar_notification_list');

/**
 * Send email notification when serial number stock low
 *
 * @since 1.0.0
 *
 * @param $message
 */

function wsn_send_email_notification($message) {

	$is_on_email = wsn_get_settings('wsn_admin_bar_notification_send_email', '', 'wsn_notification_settings');

	if ($is_on_email != 'on' or (time() - (60 * 60 * 24)) < get_option('wsn_last_sent_notification_email_date', '0')) {
		return;
	}

	global $woocommerce;

	$to      = wsn_get_settings('wsn_admin_bar_notification_email', '', 'wsn_notification_settings');
	$subject = __('Serial Numbers stock running low', 'wc-serial-numbers');

	$headers = apply_filters('woocommerce_email_headers', '', 'rewards_message');

	$heading = __('Please add more serial number for the following items', 'wc-serial-numbers');

	$mailer = $woocommerce->mailer();

	$message = $mailer->wrap_message($heading, $message);

	$mailer->send($to, $subject, $message, $headers, array());

	update_option('wsn_last_sent_notification_email_date', time());


}

add_action('wsn_send_email_notification', 'wsn_send_email_notification');
