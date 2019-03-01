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
		'number'  => 20,
		'offset'  => 0,
		'search'  => '',
		'status'  => '',
		'orderby' => 'id',
		'order'   => 'ASC',
	) );


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

	//$join  .= " LEFT JOIN {$wpdb->posts} wc_order ON wc_order.ID = serial.order_id";
	//$where .= " AND wc_order.post_type='shop_order' ";


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
		'used'     => __( 'Used', 'wc-serial-numbers' ),
	);
}

/**
 * Get a list of all wc products
 *
 * @since 1.0.0
 * @return array
 */
function wcsn_get_product_list() {
	$list = [];

	$products        = array_map( 'wc_get_product', get_posts( [ 'post_type' => 'product', 'nopaging' => true ] ) );
	$supported_types = apply_filters( 'wcsn_supported_product_types', array( 'simple', 'variable' ) );
	foreach ( $products as $product ) {
		if ( in_array( $product->get_type(), $supported_types ) ) {
			if ( 'simple' == $product->get_type() ) {
				$list[ $product->get_id() ] = $product->get_title() . ' (#' . $product->get_id() . ' ' . $product->get_sku() . ')';
			} elseif ( 'variable' == $product->get_type() ) {
				$args_get_children = array(
					'post_type'      => array( 'product_variation', 'product' ),
					'posts_per_page' => - 1,
					'order'          => 'ASC',
					'orderby'        => 'title',
					'post_parent'    => $product->get_id()
				);
				$children_products = get_children( $args_get_children );
				if ( ! empty( $children_products ) ) :
					foreach ( $children_products as $child ) :
						$sku                = get_post_meta( $child->ID, '_sku', true );
						$list[ $child->ID ] = $child->post_title . ' (#' . $child->ID . ' ' . $sku . ')';;
					endforeach;

				endif;
			}
		}
	}

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
		'Create license keys and directly assign them to products.',
		'You will be able to set how many times you want to sell that same license key.',
		'It will also enable you to select the maximum instances for a license key. (How many times/devices the user will be able to use that key).',
		'Setting license validity is another powerful option. There are two different options to set the validity. Restriction by days and date are those two different license expiration options for your complete control.',
		'Notifications will keep you informed when your generated licenses are about to finish.',
		'Set the minimum license number to trigger a notification.',
		'You can also choose to get an email notification.',
		'Generate and deliver the license keys in image format.',
		'Automatically generate license keys to populate your license pool.',
		'The settings section offers ample customization options for automatic serial number generation.'
	);

	return $features;
}
