<?php
//function prefix wc_serial_numbers

/*
 * Get Plugin directory templates part
 *
 * @since 1.0.0
 *
 * @return template file path
 * */

function wsn_get_template_part($template_name, $wsnp = false) {

	$template_dir = $wsnp ? WPWSNP_TEMPLATES_DIR : WPWSN_TEMPLATES_DIR;

	return include $template_dir . '/' . $template_name . '.php';
}


/*
 * Register Serial Numbers Post Type
 *
 * @since 1.0.0
 *
 * @return void
 * */

add_action('init', 'wsn_register_posttypes');

function wsn_register_posttypes() {
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
 * Redirect the user with custom message on form validation
 *
 * @since 1.0.0
 * */

function wsn_redirect_with_message($url, $code, $type = 'success', $args = array()) {

	$redirect_url = add_query_arg(wp_parse_args($args, array(
		'feedback' => $type,
		'code'     => $code,
	)), $url);
	wp_redirect($redirect_url);
	exit();

}

/**
 * Get feedback message for form validation
 *
 * @param $code
 * @return string|mixed
 */

function wsn_get_feedback_message($code) {

	switch ($code) {
		case 'empty_serial_number':
			return __('The Serial Number is empty. Please enter a serial number and try again', 'wc-serial-numbers');
			break;
		case 'empty_product':
			return __('The product is empty. Please select a product and try again', 'wc-serial-numbers');
			break;
	}

	return false;

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
function wsn_serial_number_tab($product_data_tabs) {

	$product_data_tabs['serial_numbers'] = array(
		'label'  => __('Serial Numbers', 'wc-serial-numbers'),
		'target' => 'serial_numbers_data',
		'class'  => 'ever-serial_numbers_tab hide_if_external hide_if_grouped',
	);

	return $product_data_tabs;
}

/**
 * Serial number tab panel content
 *
 * @since 1.0.0
 *
 */
function wsn_serial_number_tab_panel() {
	include WPWSN_TEMPLATES_DIR . '/product-serial-number-tab.php';
}

/**
 * Get all woocommerce products
 *
 * @param array $args
 * @return array|stdClass
 */

function wsn_get_products($args = []) {

	$args = array_merge($args, array(
		'limit' => -1,
	));

	return wc_get_products($args);
}

/**
 * Get serial number posts
 *
 * @since 1.0.0
 *
 * @param $args
 *
 * @return array
 */
function wsn_get_serial_numbers($args) {

	$args = wp_parse_args($args, [
		'post_type'      => 'wsn_serial_number',
		'posts_per_page' => -1,
		'meta_key'       => '',
		'meta_value'     => '',
		'meta_query'     => array(),
		'order_by'       => 'date',
		'order'          => 'ASC',
	]);

	return get_posts($args);
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
function wsn_get_settings($key, $default = '', $section = '') {

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

function wsn_get_customer_detail($key, $order) {

	if (!is_object($order)) {
		return false;
	}

	return $order->get_data()['billing'][$key];
}


/**
 * Check is Pro active
 *
 * @since 1.0.0
 *
 * @return boolean
 */
function wsn_is_wsnp() {
	return apply_filters('is_wsnp', false);
}

/**
 * add disabled attribute if if Pro is not active
 *
 * @since 1.0.0
 *
 * @return string
 */

function wsn_disabled() {
	return wsn_is_wsnp() ? '' : 'disabled';
}

/**
 * add ever-disabled class if if wsn is not wsnp
 *
 * @since 1.0.0
 *
 * @return string
 */
function wsn_class_disabled() {
	return wsn_is_wsnp() ? '' : 'ever-disabled';
}

/**
 * Check is the order status, is the settings saved order status for showing license
 *
 * @param $order
 *
 * @return bool
 */

function wsn_check_status_to_send($order) {

	$order_status   = $order->get_data()['status'];
	$status_to_show = wsn_get_settings('wsn_send_serial_number', '', 'wsn_delivery_settings');

	return $order_status == $status_to_show ? true : false;

}

/**
 * Check is the order status, is the settings saved order status for showing license
 *
 * @param $order
 *
 * @return bool
 */

function wsn_check_status_to_revoke($order) {

	$order_status   = $order->get_data()['status'];
	$status_to_show = wsn_get_settings('wsn_revoke_serial_number', '', 'wsn_delivery_settings');

	return $order_status == $status_to_show ? true : false;

}


/**
 * Get all available serial numbers for a product
 *
 * @param $product_id
 *
 * @return array
 */

function wsn_get_available_numbers($product_id) {

	$serial_numbers = wsn_get_serial_numbers([
		'meta_key'   => 'product',
		'meta_value' => $product_id,
	]);

	$numbers = [];

	foreach ($serial_numbers as $serial_number) {

		$deliver_times = get_post_meta($serial_number->ID, 'deliver_times', true);
		$used          = get_post_meta($serial_number->ID, 'used', true);

		if ($deliver_times <= $used) {
			continue;
		}

		$numbers[] = $serial_number->ID;

	}

	return $numbers;

}


function wsn_extra_table_nav($html, $page) {

	$serialnumber = !empty($_REQUEST['serialnumber']) ? esc_attr($_REQUEST['serialnumber']) : '';
	$product      = !empty($_REQUEST['product']) ? esc_attr($_REQUEST['product']) : '';

	?>

	<div class="ever-inline ever-table-filter <?php echo $page ?>">

		<label class="ever-label">Filter: </label>

		<?php if ($page == 'serial-numbers') { ?>

			<input type="text" id="filter-serialnumber" name="filter-serialnumber" placeholder="Serial number" value="<?php echo $serialnumber ?>">

		<?php } ?>

		<select name="filter-product" id="filter-product" class="ever-select  ever-field-inline">
			<option value=""><?php _e('Choose a product', 'wc-serial-numbers') ?></option>
			<?php

			$query_args = array();

			if (!wsn_is_wsnp()) {
				$query_args = array(
					'type' => 'simple',
				);
			}

			$posts = wsn_get_products($query_args);

			foreach ($posts as $post) {

				setup_postdata($post);

				$selected = $post->get_id() == $product ? 'selected' : '';
				echo '<option value="' . $post->get_id() . '" ' . $selected . '>' . $post->get_id() . ' - ' . get_the_title($post->get_id()) . '</option>';
			}

			?>
		</select>

		<div class="ever-helper"> ? <span class="text">
				 1. <?php _e('Enter a part of the serial number in the serial number box,  don\'t  need the whole number.', 'wc-serial-numbers'); ?>
				<?php if ($page == 'serial-numbers') { ?>
					<hr>2. <?php _e('Choose a product for filtering only the product.', 'wc-serial-numbers'); ?><?php } ?>

			</span>
		</div>

		<input type="submit" name="wsn-filter-table-<?php echo $page ?>" id="wsn-filter-table" class="button button-primary" value="filter">
	</div>

	<?php
}

add_filter('wsn_extra_table_nav', 'wsn_extra_table_nav', 10, 2);


/**
 * Send serial number via email if order status match with the settings order status to send the email
 *
 * @since 1.0.0
 *
 * @param $order_id
 * @param $old_status
 * @param $new_status
 * @param $order
 */

function wsn_send_serial_number_email($order_id, $old_status, $new_status, $order) {

	global $woocommerce;

	$serial_numbers = get_post_meta($order_id, 'serial_numbers', true);

	if (empty($serial_numbers)) {
		return;
	}

	if (wsn_check_status_to_send($order)) {

		$customer_email = wsn_get_customer_detail('email', $order);

		$to      = $customer_email;

		$subject = __('Serial Number for  for Order #', 'wc-serial-numbers') . $order_id;

		$headers = apply_filters('woocommerce_email_headers', '', 'rewards_message');

		$heading = __('Serial Number for for Order #', 'wc-serial-numbers') . $order_id;

		$mailer = $woocommerce->mailer();

		ob_start();
		include WPWSN_TEMPLATES_DIR . '/order-details-serial-number.php';
		$html = ob_get_clean();


		$message = $mailer->wrap_message($heading, $html);

		$mailer->send($to, $subject, $message, $headers, array());
	}

}


add_action('woocommerce_order_status_changed', 'wsn_send_serial_number_email', 10, 4);


/**
 * Revoke the serial number from the order and make it as before
 *
 * @since 1.0.0
 *
 * @param $order_id
 * @param $old_status
 * @param $new_status
 * @param $order
 */

function wsn_revoke_serial_number($order_id, $old_status, $new_status, $order) {

	$serial_numbers = get_post_meta($order_id, 'serial_numbers', true);

	if (empty($serial_numbers)) {
		return;
	}

	if (wsn_check_status_to_revoke($order)) {
		foreach ($serial_numbers as $serial_number_id){
			$used = get_post_meta($serial_number_id, 'used', true);

			update_post_meta($serial_number_id, 'used', $used -1 );
			update_post_meta($serial_number_id, 'order', '' );
		}
	}

	return;

}


add_action('woocommerce_order_status_changed', 'wsn_revoke_serial_number', 10, 4);







