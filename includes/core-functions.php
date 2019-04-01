<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * get settings options
 *
 * @param        $key
 * @param string $default
 * @param string $section
 *
 * @return string|array
 */
function wcsn_get_settings( $key, $default = '', $section = '' ) {

	$option = get_option( $section, [] );

	return ! empty( $option[ $key ] ) ? $option[ $key ] : $default;
}

/**
 * Get serial numbers
 * since 1.0.0
 *
 * @param array $args
 * @param bool  $count
 *
 * @return array|null|int
 */
function wcsn_get_serial_numbers( $args = array(), $count = false ) {
	global $wpdb;
	$args = wp_parse_args( $args, array(
		'number'      => 20,
		'offset'      => 0,
		'search'      => '',
		'status'      => '',
		'orderby'     => 'id',
		'order'       => 'ASC',
		'expire_date' => current_time( 'mysql' ),
	) );

	if ( $args['number'] < 1 ) {
		$args['number'] = 999999999;
	}

	$where = ' WHERE 1=1 ';
	$join  = '';

	// Specific id
	if ( ! empty( $args['id'] ) ) {

		if ( is_array( $args['id'] ) ) {
			$ids = implode( ',', array_map( 'intval', $args['id'] ) );
		} else {
			$ids = intval( $args['id'] );
		}

		$where .= " AND `id` IN( {$ids} ) ";
	}

	//Specific order id
	if ( ! empty( $args['order_id'] ) ) {

		if ( is_array( $args['order_id'] ) ) {
			$order_ids = implode( ',', array_map( 'intval', $args['order_id'] ) );
		} else {
			$order_ids = intval( $args['order_id'] );
		}

		$where .= " AND `order_id` IN( {$order_ids} ) ";
	}

	// Specific product id
	if ( ! empty( $args['product_id'] ) ) {

		if ( is_array( $args['product_id'] ) ) {
			$product_ids = implode( ',', array_map( 'intval', $args['product_id'] ) );
		} else {
			$product_ids = intval( $args['product_id'] );
		}

		$where .= " AND `product_id` IN( {$product_ids} ) ";
	}

	//specific email
	if ( ! empty( $args['activation_email'] ) ) {
		$activation_email = sanitize_email( $args['activation_email'] );
		$where            .= " AND `activation_email` = '{$activation_email}' ";
	}

	//specific serial key
	if ( ! empty( $args['serial_key'] ) ) {
		$serial_key = sanitize_textarea_field( $args['serial_key'] );
		$where      .= " AND `serial_key` = '{$serial_key}' ";
	}


	// Specific status
	if ( ! empty( $args['status'] ) ) {

		$status = sanitize_key( $args['status'] );
		$where  .= " AND `status` = '{$status}' ";
	}

	// check expire date
	//	if ( ! empty( $args['expire_date'] ) ) {
	//		$expire_date = sanitize_textarea_field( $args['expire_date'] );
	//		$where       .= " AND ( `expire_date` = '0000-00-00 00:00:00' OR `expire_date` >= '{$expire_date}')";
	//	}

	//$join  .= " LEFT JOIN {$wpdb->posts} wc_order ON wc_order.ID = serial.order_id";
	//$where .= " AND wc_order.post_type='shop_order' ";

	if ( ! empty( $args['search'] ) ) {
		$where .= " AND ( `serial_key` LIKE '%%" . esc_sql( $args['search'] ) . "%%' OR `activation_email` LIKE '%%" . esc_sql( $args['search'] ) . "%%')";
	}

	$args['orderby'] = esc_sql( $args['orderby'] );
	$args['order']   = esc_sql( $args['order'] );

	//if count
	if ( $count ) {
		return $wpdb->get_var( "select count(serial.id) from {$wpdb->prefix}wcsn_serial_numbers serial $join $where" );
	}

	$sql = "SELECT * from {$wpdb->prefix}wcsn_serial_numbers serial $join $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;";

	return $wpdb->get_results( $wpdb->prepare( $sql, absint( $args['offset'] ), absint( $args['number'] ) ) );
}


/**
 * Get available serial number statuses
 *
 * since 1.0.0
 *
 * @return array
 */
function wcsn_get_serial_statuses() {
	return array(
		'new'      => __( 'New', 'wc-serial-numbers' ),
		'pending'  => __( 'Pending', 'wc-serial-numbers' ),
		'refunded' => __( 'Refunded', 'wc-serial-numbers' ),
		'rejected' => __( 'Rejected', 'wc-serial-numbers' ),
		'expired'  => __( 'Expired', 'wc-serial-numbers' ),
		'active'   => __( 'Active', 'wc-serial-numbers' ),
	);
}

/**
 * Get a list of all wc products
 *
 * @since 1.0.0
 * @return array
 */
function wcsn_get_product_list( $only_enabled = false ) {
	$list      = [];
	$post_args = array(
		'post_type' => 'product',
		'nopaging'  => true,
		'orderby'   => 'ID',
		'order'     => 'ASC',
	);
	if ( $only_enabled ) {
		$post_args['meta_key']   = '_is_serial_number';
		$post_args['meta_value'] = 'yes';
	}
	$posts           = get_posts( $post_args );
	$products        = array_map( 'wc_get_product', $posts );
	$supported_types = apply_filters( 'wcsn_supported_product_types', array( 'simple' ) );
	foreach ( $products as $product ) {
		if ( in_array( $product->get_type(), $supported_types ) ) {
			if ( 'simple' == $product->get_type() ) {
				$title                      = $product->get_title();
				$title                      .= "(#{$product->get_id()} {$product->get_sku()} ";
				$title                      .= $product->get_type() == 'variation' ? ', Variation' : '';
				$title                      .= ')';
				$list[ $product->get_id() ] = $title;
			} elseif ( 'variable' == $product->get_type() ) {
				$args_get_children = array(
					'post_type'      => array( 'product_variation', 'product' ),
					'posts_per_page' => - 1,
					'order'          => 'ASC',
					'orderby'        => 'title',
					'post_parent'    => $product->get_id()
				);
				if ( $only_enabled ) {
					$args_get_children['meta_key']   = '_is_serial_number';
					$args_get_children['meta_value'] = 'yes';
				}
				$children_products = get_children( $args_get_children );
				if ( ! empty( $children_products ) ) {
					foreach ( $children_products as $child ) {
						$sku                = get_post_meta( $child->ID, '_sku', true );
						$title              = get_the_title( $child->ID );
						$title              .= "(#{$child->ID} {$sku} ";
						$title              .= 'Variation';
						$title              .= ')';
						$list[ $child->ID ] = $title;
					}
				}
			}
		}
	}
	krsort( $list );

	return $list;
}

/**
 * List of features of pro plugin
 *
 * @since 1.0.0
 * @return array
 */
function wcsn_get_pro_features() {
	$features = array(
		'Sell serial numbers or license keys for variable products either its a physical or digital product.',
		'Enables you to define your own pattern to generate serial numbers Like Serial-############## ',
		'You can even include date in serial numbers like Serial-{y}{m}{d}############',
		'You can create random or sequential numbers depending on your needs.',
		'If you do not like to generate serial numbers your own, then set the option it will generate automatically and assign to order depending on how you set the rule.',
		'Manage serial numbers directly from the order management page.You can edit, assign new, delete from there.',
		'Create unlimited serial number generator rules with your custom serial numbers patterns.',
		'Bulk generation of  serial numbers with a single click.',
		'Bulk Serial numbers/License keys import from CSV',
		'Serial numbers export in CSV format',
		'Sell license keys, any kind of gift cards, physical products that include a serial number or license key, digital software with access keys, username & password, tickets, lotteries, pin codes almost any kind of secret number based products.',
		'Dedicated customer support.',
	);

	return $features;
}


/**
 * get remaining activation
 *
 * @since 1.0.0
 *
 * @param $serial_id
 *
 * @return int|mixed
 */
function wcsn_get_remaining_activation( $serial_id, $context = 'edit' ) {
	global $wpdb;

	$serial_id = (int) $serial_id;

	if ( ! $serial_id ) {
		return 0;
	}

	$activation_limit = $wpdb->get_var( $wpdb->prepare( "SELECT activation_limit FROM {$wpdb->prefix}wcsn_serial_numbers WHERE id = %s;", $serial_id ) );

	if ( null == $activation_limit || 0 == $activation_limit ) {
		return $context == 'edit' ? 999999999 : __( 'Unlimited', 'wc-serial-numbers' );
	}

	$active_activations = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}wcsn_activations WHERE serial_id = %s AND active = 1;", $serial_id ) );
	$remaining          = max( 0, $activation_limit - $active_activations );

	return $context == 'edit' ? $remaining : $remaining > 9999 ? __( 'Unlimited', 'wc-serial-numbers' ) : $remaining;
}

/**
 * activate
 * since 1.0.0
 *
 * @param        $serial_id
 * @param string $instance
 * @param string $platform
 *
 * @return bool|false|int
 */
function wcsn_activate_serial_key( $serial_id, $instance = '', $platform = '' ) {
	global $wpdb;
	$activation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcsn_activations WHERE serial_id=%d AND instance=%s", $serial_id, $instance ) );
	if ( $activation ) {
		$sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}wcsn_activations SET active=%d WHERE serial_id=%d AND id=%d", 1, $serial_id, $activation->id );

		return $wpdb->query( $sql );
	} else {
		$date_time = current_time( 'mysql' );
		$wpdb->insert( "{$wpdb->prefix}wcsn_activations",
			array(
				'serial_id'       => $serial_id,
				'instance'        => $instance,
				'active'          => '1',
				'platform'        => $platform,
				'activation_time' => $date_time
			)
		);

		return $wpdb->insert_id;
	}

	return false;
}


/**
 * Get activation_id of given license serial_id and instance.
 *
 * @since 1.0.0
 *
 * @param $serial_id
 * @param $instance
 *
 * @return null|string
 */
function wcsn_get_active_activations( $serial_id, $instance, $status = '1' ) {
	global $wpdb;
	$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcsn_activations WHERE serial_id = %s AND instance = %s AND active=%d", $serial_id, $instance, $status );

	return $wpdb->get_row( $sql );
}

/**
 * deactivate serial key
 *
 * since 1.0.0
 *
 * @param $serial_id
 * @param $instance
 *
 * @return false|int
 */
function wcsn_deactivate_serial_key( $serial_id, $instance ) {
	global $wpdb;
	$sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}wcsn_activations SET active=%s WHERE serial_id=%d AND instance=%s", '0', $serial_id, $instance );

	return $wpdb->query( $sql );
}

/**
 * Assign order
 *
 * @since 1.0.0
 *
 * @param        $serial_id
 * @param        $order_id
 * @param string $status
 */
function wcsn_serial_number_assign_order( $serial_id, $order_id, $status = null ) {
	$order = wc_get_order( $order_id );

	wc_serial_numbers()->serial_number->update( $serial_id, array(
		'order_id'         => $order->get_id(),
		'activation_email' => $order->get_billing_email( 'edit' ),
		'status'           => $order->get_status( 'edit' ) == 'completed' ? 'active' : 'pending',
		'order_date'       => current_time( 'mysql' )
	) );
}

/**
 * get expiration date
 *
 * since 1.0.0
 *
 * @param $serial
 *
 * @return string
 */
function wcsn_get_serial_expiration_date( $serial ) {
	if ( empty( $serial->validity ) ) {
		return __( 'Never Expire', 'wc-serial-numbers' );
	}

	return date( 'Y-m-d', strtotime( $serial->order_date . ' + ' . $serial->validity . ' Day ' ) );
}

/**
 * Get notifications
 *
 * @since 1.0.0
 *
 * @param array $args
 * @param bool  $count
 *
 * @return array|int|null|object
 */

function wcsn_get_notifications( $args = array(), $count = false ) {
	global $wpdb;

	$args = wp_parse_args( $args, array(
		'post_type'      => 'wcsn_notification',
		'post_status'    => 'publish',
		'comment_status' => 'enable',
		'limit'          => 10,
	) );

	$where = " WHERE `post_type` = '{$args['post_type']}' AND `post_status` = '{$args['post_status']}' AND `comment_status` = '{$args['comment_status']}' ";
	$limit = $args['limit'];


	if ( $count ) {
		$total = $wpdb->get_col( "SELECT ID FROM $wpdb->posts $where LIMIT $limit;" );

		return count( $total );
	}

	$sql = $wpdb->prepare( "SELECT ID FROM $wpdb->posts $where LIMIT %d ;", $limit );

	return $wpdb->get_results( $sql );

}

/**
 * Checks if serial numbers enabled for this
 * product
 *
 * @since 1.0.3
 *
 * @param $product_id
 *
 * @return bool
 */
function wcsn_is_serial_number_enabled( $product_id ) {
	return 'yes' === get_post_meta( $product_id, '_is_serial_number', true );
}

/**
 * Get variable product enabled serial numbers
 *
 * @since 1.0.3
 *
 * @param $product
 *
 * @return array
 */
function wcsn_get_product_variations( $product ) {
	if ( is_numeric( $product ) ) {
		$product = new WC_Product( $product );
	}
	if ( $product->is_type( 'simple' ) ) {
		return array();
	}

	$args          = array(
		'post_type'   => 'product_variation',
		'post_status' => array( 'publish' ),
		'numberposts' => - 1,
		'orderby'     => 'menu_order',
		'order'       => 'asc',
		'post_parent' => $product->get_id()
	);
	$variations    = get_posts( $args );
	$variation_ids = wp_list_pluck( $variations, 'ID' );
	foreach ( $variation_ids as $key => $variation_id ) {
		if ( ! wcsn_is_serial_number_enabled( $variation_id ) ) {
			unset( $variation_ids[ $key ] );
		}
	}

	return $variation_ids;
}

/**
 *
 * since 1.0.3
 * @param $product_id
 *
 * @return bool
 */
function wcsn_is_key_source_automatic($product_id){
	return 'auto_generated' === get_post_meta( $product_id, '_serial_key_source', true );
}
