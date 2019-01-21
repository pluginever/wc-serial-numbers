<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/*
 * Get Plugin directory templates part
 *
 * @since 1.0.0
 *
 * @return template file path
 * */

function wsn_get_template_part( $template_name, $wsnp = false ) {

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

add_action( 'init', 'wsn_register_posttypes' );

function wsn_register_posttypes() {
	register_post_type( 'wsn_serial_number', array(
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
 * Redirect the user with custom message on form validation
 *
 * @since 1.0.0
 * */

function wsn_redirect_with_message( $url, $code, $type = 'success', $args = array() ) {

	$redirect_url = add_query_arg( wp_parse_args( $args, array(
		'feedback' => $type,
		'code'     => $code,
	) ), $url );
	wp_redirect( $redirect_url );
	exit();

}

/**
 * Get feedback message for form validation
 *
 * @param $code
 *
 * @return string|mixed
 */

function wsn_get_feedback_message( $code ) {

	switch ( $code ) {
		case 'empty_serial_number':
			return __( 'The Serial Number is empty. Please enter a serial number and try again', 'wc-serial-numbers' );
			break;
		case 'empty_product':
			return __( 'The product is empty. Please select a product and try again', 'wc-serial-numbers' );
			break;
	}

	return false;

}

add_filter( 'woocommerce_product_data_tabs', 'wsn_serial_number_tab' );
add_action( 'woocommerce_product_data_panels', 'wsn_serial_number_tab_panel' );

/**
 * Serial number tab
 *
 * @param $product_data_tabs
 *
 * @return mixed
 */
function wsn_serial_number_tab( $product_data_tabs ) {

	$product_data_tabs['serial_numbers'] = array(
		'label'  => __( 'Serial Numbers', 'wc-serial-numbers' ),
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
 *
 * @return array|stdClass
 */

function wsn_get_products( $args = [] ) {

	$args = array_merge( $args, array(
		'limit' => - 1,
	) );

	return wc_get_products( $args );
}

/**
 * Get serial number posts
 *
 * @since 1.0.0
 *
 * @param $args
 *
 * @return array|int
 */
function wsn_get_serial_numbers( $args, $count = false ) {

	$args = wp_parse_args( $args, array(
		'post_type'      => 'wsn_serial_number',
		'posts_per_page' => - 1,
		'meta_key'       => '',
		'meta_value'     => '',
		'meta_query'     => array(),
		'order_by'       => 'date',
		'order'          => 'ASC',
	) );

	$result = new WP_Query( $args );
	wp_reset_query();

	if ( $count ) {
		return $result->found_posts;
	}

	return $result->get_posts();
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
function wsn_get_settings( $key, $default = '', $section = '' ) {

	$option = get_option( $section, [] );

	return ! empty( $option[ $key ] ) ? $option[ $key ] : $default;
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

function wsn_get_customer_detail( $key, $order ) {

	if ( ! is_object( $order ) ) {
		return false;
	}

	return $order->get_data()['billing'][ $key ];
}


/**
 * Check is Pro active
 *
 * @since 1.0.0
 *
 * @return boolean
 */
function wsn_is_wsnp() {
	return apply_filters( 'is_wsnp', false );
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

function wsn_check_status_to_send( $order ) {

	$order_status   = $order->get_data()['status'];
	$status_to_show = wsn_get_settings( 'wsn_send_serial_number', 'completed', 'wsn_delivery_settings' );

	return $order_status == $status_to_show ? true : false;

}

/**
 * Check is the order status, is the settings saved order status for showing license
 *
 * @param $order
 *
 * @return bool
 */

function wsn_check_status_to_revoke( $order ) {

	$order_status = $order->get_data()['status'];

	$status_to_show = wsn_get_settings( 'wsn_revoke_serial_number', array(
		'cancelled',
		'refunded',
		'failed'
	), 'wsn_delivery_settings' );

	return in_array( $order_status, (array) $status_to_show ) ? true : false;

}


/**
 * Get all available serial numbers for a product
 *
 * @param $product_id
 *
 * @return array
 */

function wsn_get_available_numbers( $product_id ) {

	$serial_numbers = wsn_get_serial_numbers( [
		'meta_key'   => 'product',
		'meta_value' => $product_id,
	] );

	$numbers = array();

	foreach ( $serial_numbers as $serial_number ) {

		$deliver_times = get_post_meta( $serial_number->ID, 'deliver_times', true );
		$used          = get_post_meta( $serial_number->ID, 'used', true );
		$validity_type = get_post_meta( $serial_number->ID, 'validity_type', true );
		$validity      = get_post_meta( $serial_number->ID, 'validity', true );

		if ( $deliver_times <= $used || ! empty( wsn_is_serial_valid( $validity, $validity_type ) ) ) {
			continue;
		}

		$numbers[] = $serial_number->ID;

	}

	return $numbers;

}

/**
 * Serial numbers Table filter
 *
 * @param $html
 * @param $page
 */

function wsn_extra_table_nav( $html, $page ) {

	$serialnumber = ! empty( $_REQUEST['filter-serialnumber'] ) ? sanitize_key( $_REQUEST['filter-serialnumber'] ) : '';
	$product      = ! empty( $_REQUEST['filter-product'] ) ? intval( $_REQUEST['filter-product'] ) : '';

	?>

	<div class="ever-inline ever-table-filter <?php echo $page ?>">

		<label class="ever-label"><?php _e( 'Filter:', 'wc-serial-numbers' ) ?> </label>

		<?php if ( ! empty( $page ) && $page === 'serial-numbers' ) { ?>

			<input type="text" id="filter-serialnumber" name="filter-serialnumber" class="ever-field-inline" placeholder="Serial number" value="<?php echo $serialnumber ?>">

		<?php } ?>

		<select name="filter-product" id="filter-product" class="ever-select  ever-field-inline">
			<option value=""><?php _e( 'Choose a product', 'wc-serial-numbers' ) ?></option>
			<?php

			$query_args = array();

			if ( ! wsn_is_wsnp() ) {
				$query_args = array(
					'type' => 'simple',
				);
			}

			$posts = wsn_get_products( $query_args );

			foreach ( $posts as $post ) {

				setup_postdata( $post );

				$selected = $post->get_id() == $product ? 'selected' : '';

				echo '<option value="' . $post->get_id() . '" ' . $selected . '>' . $post->get_id() . ' - ' . get_the_title( $post->get_id() ) . '</option>';
			}

			?>

		</select>

		<div class="ever-helper"> ? <span class="text">

				<?php _e( '1. Enter a part of the serial number in the serial number box,  don\'t  need the whole number.', 'wc-serial-numbers' ); ?>

				<?php if ( $page == 'serial-numbers' ) { ?>
					<hr><?php _e( '2. Choose a product for filtering only the product.', 'wc-serial-numbers' ); ?><?php } ?>

			</span>
		</div>

		<input type="submit" name="wsn-filter-table-<?php echo $page ?>" id="wsn-filter-table" class="button button-primary" value="Filter">

		<button class="button ever-inline">
			<a href="<?php echo $page == 'serial-numbers' ? WPWSN_SERIAL_INDEX_PAGE : WPWSN_GENERATE_SERIAL_PAGE ?>" class="wsn-button"><?php _e( 'Clear filter', 'wc-serial-numbers' ) ?></a>
		</button>

	</div>

	<?php
}

add_filter( 'wsn_extra_table_nav', 'wsn_extra_table_nav', 10, 2 );


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

function wsn_send_serial_number_email( $order_id, $old_status, $new_status, $order ) {

	global $woocommerce;

	$serial_numbers = get_post_meta( $order_id, 'serial_numbers', true );

	if ( empty( $serial_numbers ) ) {
		return;
	}

	if ( wsn_check_status_to_send( $order ) ) {

		$customer_email = wsn_get_customer_detail( 'email', $order );

		$to = $customer_email;

		$subject = __( 'Serial Number for  for Order #', 'wc-serial-numbers' ) . $order_id;

		$headers = apply_filters( 'woocommerce_email_headers', '', 'rewards_message' );

		$heading = __( 'Serial Number for for Order #', 'wc-serial-numbers' ) . $order_id;

		$mailer = $woocommerce->mailer();

		ob_start();
		include WPWSN_TEMPLATES_DIR . '/order-details-serial-number.php';
		$html = ob_get_clean();


		$message = $mailer->wrap_message( $heading, $html );

		$mailer->send( $to, $subject, $message, $headers, array() );
	}

}


add_action( 'woocommerce_order_status_changed', 'wsn_send_serial_number_email', 10, 4 );


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

function wsn_revoke_serial_number( $order_id, $old_status, $new_status, $order ) {

	$serial_numbers = get_post_meta( $order_id, 'serial_numbers', true );

	if ( empty( $serial_numbers ) ) {
		return;
	}

	if ( wsn_check_status_to_revoke( $order ) ) {

		foreach ( $serial_numbers as $serial_number_id ) {

			$used = get_post_meta( $serial_number_id, 'used', true );

			update_post_meta( $serial_number_id, 'used', $used - 1 );
			update_post_meta( $serial_number_id, 'order', '' );
		}
	}

	return;

}


add_action( 'woocommerce_order_status_changed', 'wsn_revoke_serial_number', 10, 4 );


/**
 * ============================
 * Notifications
 * ============================
 */

/**
 * Update serial number notification posts when a serial number enable or disable for any product
 *
 * @since  1.0.0
 *
 * @param $product_id
 *
 * @retun void
 */

function wsn_update_notification_on_enable_disable( $product_id, $status ) {

	$numbers = wsn_get_available_numbers( $product_id );

	$is_exists = get_page_by_title( $product_id, OBJECT, 'wsnp_notification' );

	if ( $is_exists ) {
		wp_update_post( array(
			'ID'             => $is_exists->ID,
			'comment_status' => $status,
		) );

	}

	return;

}

add_action( 'wsn_update_notification_on_enable_disable', 'wsn_update_notification_on_enable_disable', 10, 2 );


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
			'ID'             => $is_exists->ID,
			'post_content'   => $count_number,
			'post_status'    => 'publish',
			'comment_status' => 'enable',
		) );

	}

	return;

}

add_action( 'wsn_update_notification_on_order_delete', 'wsn_update_notification_on_order_delete' );


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
				'ID'             => $is_exists->ID,
				'post_content'   => $count_number,
				'post_status'    => 'draft',
				'comment_status' => 'disable',
			) );
		}

		return;
	}

	if ( $is_exists ) {
		wp_update_post( array(
			'ID'             => $is_exists->ID,
			'post_content'   => $count_number,
			'post_status'    => 'publish',
			'comment_status' => 'enable',
		) );

		return;
	}

	wp_insert_post( array(
		'post_type'      => 'wsnp_notification',
		'post_title'     => $product_id,
		'post_content'   => $count_number,
		'post_status'    => 'publish',
		'comment_status' => 'enable',
	) );

	return;

}

add_action( 'wsn_update_notification_on_add_edit', 'wsn_update_notification_on_add_edit', 10, 2 );

/**
 * Show admin bar notification count number
 *
 * @since  1.0.0
 *
 * @return false|string
 */

add_filter( 'wsn_admin_bar_notification', function () {

	$count = count( $posts = get_posts( [
		'post_type'      => 'wsnp_notification',
		'posts_per_page' => - 1,
		'post_status'    => 'publish',
		'comment_status' => 'enable'
	] ) );

	$show_notification = wsn_get_settings( 'wsn_admin_bar_notification', 'on', 'wsn_notification_settings' );

	if ( $show_notification == 'on' and $count > 0 ) {
		return '<span class="wsn_admin_bar_notification"></span>';
	}

	return false;

} );

/**
 * Sho admin bar notification list
 *
 * @since 1.0.0
 *
 * @param $html
 *
 * @return false|string
 */

function wsn_admin_bar_notification_list( $html ) {

	$show_notification        = wsn_get_settings( 'wsn_admin_bar_notification', 'on', 'wsn_notification_settings' );
	$show_notification_number = wsn_get_settings( 'wsn_admin_bar_notification_number', '5', 'wsn_notification_settings' );

	if ( $show_notification != 'on' ) {
		return false;
	}

	if ( empty( get_post_type() ) ) {
		global $post;
	}

	$posts = get_posts( [
		'post_type'      => 'wsnp_notification',
		'posts_per_page' => 20,
		'post_status'    => 'publish',
		'comment_status' => 'enable'
	] );

	if ( ! empty( $posts ) ) {

		$message = '';

		ob_start();

		echo '<span class="ever-notification"><span class="alert">' . sprintf( '%02d', count( $posts ) ) . '</span></span> <ul class="ever-notification-list alert">';

		foreach ( $posts as $post ) {

			setup_postdata( $post );

			$count = (int) get_the_content( $post->ID );
			$title = get_the_title( $post->ID );

			if( get_post_status($title) != 'publish' ) {

				if(current_user_can('delete_posts')){
					wp_delete_post($post->ID);

				}

			}

				$name    = '<a href="' . get_edit_post_link( $title ) . '">' . get_the_title( $title ) . '</a>';
				$count   = '<strong>' . $count . '</strong>';
				$msg     = __( 'Please add serial numbers for ', 'wc-serial-numbers' ) . $name . ', ' . $count . __( ' Serial number left', 'wc-serial-numbers' );
				$message .= '<tr><td>' . $msg . '</td></tr>';

				echo '<li>' . $msg . '</li>';

		}

		wp_reset_postdata();

		echo '</ul>'; //End the list

		$html = ob_get_clean();

		$is_on_email = wsn_get_settings( 'wsn_admin_bar_notification_send_email', '', 'wsn_notification_settings' );

		if ( $is_on_email == 'on' ) {
			wp_schedule_event( time(), 'daily', 'wsn_send_email_notification', array( $message ) );
		}

	}

	return $html;
}

add_filter( 'wsn_admin_bar_notification_list', 'wsn_admin_bar_notification_list' );


/**
 * Display serial number details on order edit page
 *
 * @since 1.0.0
 *
 * @param $item_id
 * @param $item
 * @param $product
 */

function wsn_woocommerce_after_order_itemmeta( $item_id, $item, $product ) {

	$serial_numbers = get_post_meta( $item->get_data()['order_id'], 'serial_numbers', true )[ $product->get_id() ];

	if ( empty( $serial_numbers ) ) {
		return;
	}

	foreach ( $serial_numbers as $serial_number_id ) {

		$serial_number = get_the_title( $serial_number_id );
		$max_instance  = get_post_meta( $serial_number_id, 'max_instance', true );
		$validity_type = get_post_meta( $serial_number_id, 'validity_type', true );
		$validity      = get_post_meta( $serial_number_id, 'validity', true );

		?>

		<div class="wc-order-item-sku">
			<strong><?php _e( 'Serial Number', 'wc-serial-numbers' ) ?>:</strong> <?php echo $serial_number; ?>
			<br>
			<?php
			if ( ! empty( $max_instance ) && $max_instance > 0 ) {
				echo sprintf( __( 'Can be used: %d times', 'wc-serial-numbers' ), $max_instance );
			}
			?>

			<?php

			if ( ! empty( $validity_type ) && ! empty( $validity ) && in_array( $validity_type, array(
					'days',
					'date'
				) ) ) {

				echo '<br>';

				if ( $validity_type == 'days' ) {
					echo sprintf( __( 'Validity: %d (Days)', 'wc-serial-numbers' ), $validity );
				} elseif ( $validity_type == 'date' ) {
					echo sprintf( __( 'Validity: until %s', 'wc-serial-numbers' ), $validity );
				}

			}

			?>

		</div>

	<?php }
}

add_action( 'woocommerce_after_order_itemmeta', 'wsn_woocommerce_after_order_itemmeta', 10, 3 );


/**
 * Check if the validity of a serial number is expired or not
 *
 * @since 1.0.0
 *
 * @param $validity
 * @param $validity_type
 * @param $purchased_on
 *
 * @return string - null on if validity available, expired on expired
 */

function wsn_is_serial_valid( $validity, $validity_type, $purchased_on = '' ) {

	if ( $validity_type == 'days' && ! empty( $validity ) && ! empty( $purchased_on ) ) {

		$datediff = time() - strtotime( $purchased_on );

		$datediff = round( $datediff / ( 60 * 60 * 24 ) );

		if ( $datediff > $validity ) {

			$valid_msg = __( 'Expired', 'wc-serial-numbers' );

			return $valid_msg;
		}

	} elseif ( $validity_type == 'date' && ! empty( $validity ) ) {

		if ( time() > strtotime( $validity ) ) {
			$valid_msg = __( 'Expired', 'wc-serial-numbers' );

			return $valid_msg;
		}

	}

	return '';

}


function wsn_check_validity_date() {

	$serial_numbers = wsn_get_serial_numbers( array() );

	foreach ( $serial_numbers as $serial_number ) {

		$product_id    = get_post_meta( $serial_number->ID, 'product', true );
		$validity_type = get_post_meta( $serial_number->ID, 'validity_type', true );
		$validity      = get_post_meta( $serial_number->ID, 'validity', true );

		if ( ! empty( wsn_is_serial_valid( $validity, $validity_type ) ) ) {
			wsn_update_notification_on_add_edit( $product_id );
		}

	}
}

add_action( 'wsn_check_validity_date', 'wsn_check_validity_date' );

if ( ! wp_next_scheduled( 'wsn_check_validity_date' ) ) {
	wp_schedule_event( time(), 'daily', 'wsn_check_validity_date' );
}
