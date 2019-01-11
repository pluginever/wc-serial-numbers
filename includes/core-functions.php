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
		'meta_key'       => '',
		'meta_value'     => '',
		'order_by'       => 'date',
		'order'          => 'DESC',
	] );

	return get_posts( $args );
}

/**
 * Update serial number notification posts when a order made or a serial number deleted
 *
 * @since  1.0.0
 *
 * @param $product_id
 *
 * @retun void
 */

function wsn_update_notification_on_order_delete( $product_id ) {

	$numbers = wsn_get_available_numbers( $product_id );

	$show_number = wsn_get_settings( 'wsn_admin_bar_notification_number', '', 'wsn_notification_settings' );

	$count_number = count( $numbers );

	if ( $count_number >= $show_number ) {
		return;
	}

	$is_exists = get_page_by_title( $product_id, OBJECT, 'wsnp_notification' );

	if ( $is_exists ) {
		wp_update_post( array(
			'ID'           => $is_exists->ID,
			'post_content' => $count_number,
			'post_status'  => 'publish',
		) );

	}

	return;

}

add_action( 'wsn_update_notification_on_order_delete', 'wsn_update_notification_on_order_delete', 10, 2 );


/**
 * Update serial number notification posts when a new order added or order edited
 *
 * @param $product_id
 *
 * @return void
 */

function wsn_update_notification_on_add_edit( $product_id ) {

	$show_number = wsn_get_settings( 'wsn_admin_bar_notification_number', 5, 'wsn_notification_settings' );

	$numbers = wsn_get_available_numbers( $product_id );

	$count_number = count( $numbers );

	$is_exists = get_page_by_title( $product_id, OBJECT, 'wsnp_notification' );

	if ( $count_number >= $show_number ) {

		if ( $is_exists ) {
			wp_update_post( array(
				'ID'           => $is_exists->ID,
				'post_content' => $count_number,
				'post_status'  => 'draft',
			) );

			return;
		}

		return;
	}


	if ( $is_exists ) {
		wp_update_post( array(
			'ID'           => $is_exists->ID,
			'post_content' => $count_number,
			'post_status'  => 'publish',
		) );

		return;
	}

	wp_insert_post( array(
		'post_type'    => 'wsnp_notification',
		'post_title'   => $product_id,
		'post_content' => $count_number,
		'post_status'  => 'publish',
	) );

	return;

}

add_action( 'wsn_update_notification_on_add_edit', 'wsn_update_notification_on_add_edit', 10, 2 );


add_filter( 'wsn_admin_bar_notification', function () {
	return '<span class="wsn_admin_bar_notification"></span>';
} );

add_filter( 'wsn_admin_bar_notification_list', 'wsn_admin_bar_notification_list' );


function wsn_admin_bar_notification_list( $html ) {

	if ( empty( get_post_type() ) ) {
		global $post;
	}

	$posts = get_posts( [ 'post_type' => 'wsnp_notification', 'posts_per_page' => - 1, 'post_status' => 'publish' ] );


	if ( ! empty( $posts ) ) {

		ob_start();

		echo '<span class="ever-notification"><span class="alert">'.sprintf('%02d',count($posts)).'</span></span> <ul class="ever-notification-list alert">';

		foreach ( $posts as $post ) {

			setup_postdata( $post );

			?>

			<li><?php
				$name  = get_the_title( get_the_title( $post->ID ) );
				$count = (int) get_the_content();
				echo __( 'Please add license keys for ', 'wc-serial-numbers' ) . $name . ', ' . $count . __( ' License Key left', 'wc-serial-numbers' ); ?></li>

			<?php
		}

		wp_reset_postdata();

		echo '</ul>'; //End the list

		$html = ob_get_clean();

	}

	return $html;
}
