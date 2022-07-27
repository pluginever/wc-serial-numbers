<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class My_Account
 *
 * Handles my_accounts serial keys options.
 *
 * @since  1.3.1
 * @package PluginEver\WooCommerceSerialNumbers
 */
class My_Account {

	/**
	 * My_Account constructor
	 *
	 * @since 1.3.1
	 */
	public function __construct() {
		add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'my_account_custom_query_vars' ) );
		add_filter( 'woocommerce_endpoint_wc-serial-numbers_title', array( __CLASS__, 'my_account_custom_endpoint_title' ) );
		add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'wc_serial_numbers_add_menu_items' ) );
		add_action( 'woocommerce_account_wc-serial-numbers_endpoint', array( __CLASS__, 'wc_serial_numbers_endpoint_content' ) );
	}

	/**
	 * Adds endpoint into query vars.
	 *
	 * @param array $query_vars Query Vars.
	 *
	 * @retun  array
	 * @since 1.3.1
	 */
	public static function my_account_custom_query_vars( $query_vars ) {
		$query_vars['wc-serial-numbers'] = 'wc-serial-numbers';

		return $query_vars;
	}

	/**
	 * Page title on serial numbers page.
	 *
	 * @return string
	 * @since 1.3.1
	 */
	public static function my_account_custom_endpoint_title() {
		return apply_filters( 'wc_serial_numbers_account_menu_title', __( 'Serial Numbers', 'wc-serial-numbers' ) );
	}

	/**
	 * Add Serial numbers menu-item in my-account section
	 *
	 * @param array $menu_items Menu Items.
	 *
	 * @return array $menu_items
	 * @since 1.3.1
	 */
	public static function wc_serial_numbers_add_menu_items( $menu_items ) {
		// insert after account details
		$menu_item_key   = 'wc-serial-numbers';
		$menu_item_value = apply_filters( 'wc_serial_numbers_account_menu_title', __( 'Serial Numbers', 'wc-serial-numbers' ) );

		$add_before_index = array_search( 'edit-account', array_keys( $menu_items ), true );
		if ( false === $add_before_index ) {
			$menu_items[ $menu_item_key ] = $menu_item_value;
		} else {
			$add_before_index ++;
			$menu_items = array_merge( array_slice( $menu_items, 0, intval( $add_before_index ) ), array( $menu_item_key => $menu_item_value ), array_slice( $menu_items, $add_before_index ) );
		}

		return $menu_items;
	}

	/**
	 * Serial numbers end point content.
	 *
	 * @since 1.2.8
	 */
	public static function wc_serial_numbers_endpoint_content() {
		wc_print_notices();
		$current_user_id = get_current_user_id();
		$customer_orders = get_posts(
			array(
				'posts_per_page' => - 1,
				'post_type'      => 'shop_order',
				'post_status'    => 'wc-completed',
				'meta_query'     => array(
					array(
						'key'     => '_customer_user',
						'value'   => $current_user_id,
						'compare' => '=',
					),
				),
			)
		);

		$serial_numbers_data = array();
		if ( is_array( $customer_orders ) && count( $customer_orders ) ) {
			foreach ( $customer_orders as $order ) {
				$single_order = wc_get_order( $order->ID );

				// check if the order has attached serial numbers
				//$total_ordered_serial_numbers = wc_serial_numbers_order_has_serial_numbers( $single_order );
				$total_ordered_serial_numbers = 1;
				if ( empty( $total_ordered_serial_numbers ) ) {
					continue;
				}

				$serial_numbers = Serial_Keys::query( array( 'order_id' => $single_order->get_id() ) );
				if ( is_array( $serial_numbers ) && count( $serial_numbers ) ) {
					foreach ( $serial_numbers as $serial_number ) {
						$serial_numbers_data[] = array(
							'serial_id'        => $serial_number->id,
							'serial_key'       => $serial_number->serial_key,
							'product_id'       => $serial_number->product_id,
							'activation_limit' => $serial_number->activation_limit,
							'order_id'         => $serial_number->order_id,
							'expire_date'      => $serial_number->expire_date,
							'order_date'       => $serial_number->order_date,
							'validity'         => $serial_number->validity,
						);
					}
				}
			}
		} else { ?>
			<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
				<a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"><?php esc_html_e( 'Browse products', 'woocommerce' ); ?></a>
				<?php esc_html_e( apply_filters( 'wc_serial_numbers_account_empty_message', __( 'No serial numbers has been added yet.', 'wc-serial-numbers' ) ) ); ?>
			</div>
			<?php
		}

		if ( is_array( $serial_numbers_data ) && count( $serial_numbers_data ) ) {
			$columns = apply_filters(
				'wc_serial_numbers_account_table_columns',
				array(
					'product'    => __( 'Product', 'wc-serial-numbers' ),
					'serial_key' => __( 'Serial Key', 'wc-serial-numbers' ),
				)
			);
			?>
			<table class="woocommerce-table woocommerce-table--order-serial_numbers shop_table shop_table_responsive order_details" cellspacing="0" cellpadding="6" border="1">
			<thead>
			<tr>
				<?php
				foreach ( $columns as $key => $label ) {
					echo sprintf( '<th class="td %s" scope="col" style="text-align:left;">%s</th>', sanitize_html_class( $key ), $label );
				}
				?>
			</tr>
			</thead>
			<tbody>
			<?php
			echo '<tr>';
			foreach ( $columns as $key => $label ) {
				echo sprintf( '<td class="td %s" style="text-align:left;">', $key );
				switch ( $key ) {
					case 'product':
						echo sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( get_the_permalink( 13 ) ), get_the_title( 13 ) );
						break;
					case 'serial_key':
						?>
						<ul class="wc-item-meta">
							<li>
								<strong class="wc-item-meta-label">Serial Numbers:</strong>
								<span>Test</span>
							</li>
							<li>
								<strong class="wc-item-meta-label">Activation Limit:</strong>
								<span>Unlimited</span>
							</li>
							<li>
								<strong class="wc-item-meta-label">Expire Date:</strong>
								<span>2022-08-31</span>
							</li>
							<li>
								<strong class="wc-item-meta-label">Order ID:</strong>
								<span><?php echo sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( wc_get_endpoint_url( 'view-order', 60, wc_get_page_permalink( 'myaccount' ) ) ), '#' . 60 );?></span>
							</li>
						</ul>
						<?php
						break;
						default:
							$order_id = 60;
							do_action( 'wc_serial_numbers_my_account_table_cell_content', $key, $serial_number, $order_id );
							break;

				}
				?>

				<?php
			}
			echo '</tr>';
//			foreach ( $serial_numbers_data as $serial_number ) {
//				$order_id = $serial_number['order_id'];
//				echo '<tr>';
//				foreach ( $columns as $key => $label ) {
//					echo sprintf( '<td class="td %s" style="text-align:left;">', $key );
//					switch ( $key ) {
//						case 'order':
//							echo sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( wc_get_endpoint_url( 'view-order', $order_id, wc_get_page_permalink( 'myaccount' ) ) ), '#' . $order_id );
//							break;
//						case 'serial_key':
//							echo wc_serial_numbers_decrypt_key( $serial_number['serial_key'] );
//							break;
//						case 'product':
//							echo sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( get_the_permalink( $serial_number['product_id'] ) ), get_the_title( $serial_number['product_id'] ) );
//							break;
//						case 'activation_limit':
//							if ( empty( $serial_number['activation_limit'] ) ) {
//								echo __( 'Unlimited', 'wc-serial-numbers' );
//							} else {
//								echo $serial_number['activation_limit'];
//							}
//							break;
//						case 'expire_date':
//							if ( empty( $serial_number['validity'] ) ) {
//								echo __( 'Lifetime', 'wc-serial-numbers' );
//							} else {
//								echo date( 'Y-m-d', strtotime( $serial_number['order_date'] . ' + ' . $serial_number['validity'] . ' Day ' ) );
//							}
//							break;
//						default:
//							do_action( 'wc_serial_numbers_my_account_table_cell_content', $key, $serial_number, $order_id );
//					}
//					echo '</td>';
//				}
//				echo '</tr>';
//			}
			echo '</table>';
		} else {
			?>
			<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
				<a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"><?php esc_html_e( 'Browse products', 'woocommerce' ); ?></a>
				<?php esc_html_e( apply_filters( 'wc_serial_numbers_account_empty_message', __( 'No serial numbers has been added yet.', 'wc-serial-numbers' ) ) ); ?>
			</div>
			<?php
		}
	}


}

new My_Account();