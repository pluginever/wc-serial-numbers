<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_AJAX {

	/**
	 * WC_Serial_Numbers_AJAX constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_wc_serial_numbers_search_products', array( $this, 'search_products' ) );
		add_action( 'wp_ajax_wc_serial_numbers_decrypt_key', array( $this, 'decrypt_key' ) );
		add_action( 'wp_ajax_wcsn_json_search_products_and_variations', array( $this, 'json_search_products_and_variations' ) );
		add_action( 'wp_ajax_wcsn_validate_add_order_items', array( $this, 'validate_add_order_items' ) );
		add_action( 'wp_ajax_wcsn_get_order_metabox_table_items', array( $this, 'get_order_metabox_table_items' ) );
		add_action( 'wp_ajax_woocommerce_remove_order_item', array( $this, 'remove_order_item' ), 9 );
	}

	/**
	 * Search products.
	 *
	 * @since 1.1.6
	 */
	public function search_products() {
		$this->verify_nonce( 'wc_serial_numbers_admin_js_nonce', 'nonce' );
		$this->check_permission();
		$search = isset( $_REQUEST['search'] ) ? sanitize_text_field( $_REQUEST['search'] ) : '';
		$page   = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$types  = apply_filters( 'wc_serial_numbers_product_types', array( 'product' ) );
		global $wpdb;
		$query = WC_Serial_Numbers_Query::init()->table( 'posts' )
		                                ->where( 'post_status', 'publish' )
		                                ->whereRaw( 'post_type IN ("' . implode( '","', $types ) . '")' )
		                                ->whereRaw( "ID NOT IN  (SELECT DISTINCT post_parent FROM {$wpdb->posts} WHERE post_type='product_variation') " )
		                                ->search( sanitize_text_field( $search ), array( 'post_title' ) )
		                                ->page( $page );
		$more  = false;
		if ( $query->count() > ( 20 * $page ) ) {
			$more = true;
		}
		$product_ids = $query->column( 0 );
		$results     = array();
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				continue;
			}

			$text = sprintf(
				'(#%1$s) %2$s',
				$product->get_id(),
				strip_tags( $product->get_formatted_name() )
			);

			$results[] = array(
				'id'   => $product->get_id(),
				'text' => $text
			);
		}
		wp_send_json(
			array(
				'page'       => $page,
				'results'    => $results,
				'pagination' => array(
					'more' => $more
				)
			)
		);
	}

	/**
	 * Decrypt key
	 * @since 1.2.0
	 */
	public function decrypt_key() {
		$this->verify_nonce( 'wc_serial_numbers_decrypt_key', 'nonce' );
		$this->check_permission();
		$serial_id = isset( $_REQUEST['serial_id'] ) ? sanitize_text_field( $_REQUEST['serial_id'] ) : '';
		if ( empty( $serial_id ) ) {
			$this->send_error( [ 'message' => __( 'Could not detect the serial number to decrypt', 'wc-serial-numbers' ) ] );
		}

		$serial_number = wc_serial_numbers_get_serial_number( $serial_id );
		if ( empty( $serial_number ) ) {
			$this->send_error( [ 'message' => __( 'Could not find the serial number to decrypt', 'wc-serial-numbers' ) ] );
		}

		$this->send_success( [ 'key' => wc_serial_numbers_decrypt_key( $serial_number->serial_key ) ] );

	}

	/**
	 * Search product that has serial number attached
	 *
	 * @since 1.2.8
	 *
	 * @return void
	 */
	public function json_search_products_and_variations() {
		if ( isset( $_GET['wcsn_product_only'] ) ) {
			add_filter( 'user_has_cap', array( $this, 'filter_serial_number_products' ), 10, 3 );
		}

		WC_AJAX::json_search_products_and_variations();
	}

	/**
	 * Filter user cap to exclude products that has no serial number
	 *
	 * @since 1.2.8
	 *
	 * @see WP_User::has_cap()
	 *
	 * @param bool[]   $allcaps Array of key/value pairs where keys represent a capability name
	 *                          and boolean values represent whether the user has that capability.
	 * @param string[] $caps    Required primitive capabilities for the requested capability.
	 * @param array    $args {
	 *     Arguments that accompany the requested capability check.
	 *
	 *     @type string    $0 Requested capability.
	 *     @type int       $1 Concerned user ID.
	 *     @type mixed  ...$2 Optional second and further parameters, typically object ID.
	 * }
	 *
	 * @return bool[]
	 */
	public function filter_serial_number_products( $allcaps, $caps, $args ) {
		if ( ! empty( $args[0] ) && 'read_product' === $args[0] && ! empty( $args[2] ) ) {
			$has_serial_number = 'yes' === get_post_meta( $args[2], '_is_serial_number', true );

			if ( ! $has_serial_number ) {
				$allcaps = [];
			}
		}

		return $allcaps;
	}

	/**
	 * Validate order items
	 *
	 * @since 1.2.8
	 *
	 * @return void
	 */
	public function validate_add_order_items() {
		check_ajax_referer( 'order-item', 'security' );

		if ( ! isset( $_POST['items'] ) ) {
			self::send_error( __( 'Error: No item id provided.', 'wc-serial-numbers' ) );
		}

		if ( ! is_array( $_POST['items'] ) ) {
			self::send_error( __( 'Error: Invalid item id.', 'wc-serial-numbers' ) );
		}

		$items = wp_unslash( $_POST['items'] );

		foreach( $items as $id => $qty ) {
			$is_valid = wcsn_validate_cart_item( $id, $qty );

			if ( is_wp_error( $is_valid ) ) {
				self::send_error( $is_valid->get_error_message() );
			}
		}

		$this->send_success( __( 'Items are valid.', 'wc-serial-numbers' ) );
	}

	/**
	 * Order metabox table items
	 *
	 * @since 1.2.8
	 *
	 * @return void
	 */
	public function get_order_metabox_table_items() {
		check_ajax_referer( 'order-item', 'security' );

		$get_data = wp_unslash( $_GET );

		if ( ! isset( $get_data['order_id'] ) ) {
			self::send_error( __( 'Error: No order id provided.', 'wc-serial-numbers' ) );
		}

		$order = wc_get_order( absint( $get_data['order_id'] ) );

		if ( ! $order instanceof WC_Order ) {
			self::send_error( __( 'Error: Invalid order id.', 'wc-serial-numbers' ) );
		}

		WC_Serial_Numbers_Handler::maybe_assign_serial_numbers( $order->get_id() );
		$serial_numbers = WC_Serial_Numbers_Query::init()->from( 'serial_numbers' )->where( 'order_id', intval( $order->get_id() ) )->get();
		$columns = wc_serial_numbers_get_order_table_columns();
		$col_span = count( $columns ) + 1;

		ob_start();
		require_once WC_SERIAL_NUMBER_PLUGIN_INC_DIR . '/admin/views/order-metabox-items.php';
		$tbody = ob_get_clean();

		$this->send_success( array( 'tbody' => $tbody ) );
	}

	/**
	 * Hook an action before delete an order item
	 *
	 * @since 1.2.8
	 *
	 * @return void
	 */
	public function remove_order_item() {
		add_action( 'woocommerce_before_delete_order_item', array( $this, 'remove_serial_number_from_order' ) );
	}

	/**
	 * Remove serial numbers from order
	 *
	 * @since 1.2.8
	 *
	 * @param int $item_id
	 *
	 * @return void
	 */
	public function remove_serial_number_from_order( $item_id ) {
		$data_store = WC_Data_Store::load( 'order-item' );
		$order_id = $data_store->get_order_id_by_order_item_id( $item_id );
		WC_Serial_Numbers_Handler::revoke_serial_numbers( $order_id );
	}

	/**
	 * Check permission
	 *
	 * since 1.0.0
	 */
	public function check_permission() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			self::send_error( __( 'Error: You are not allowed to do this.', 'wc-serial-numbers' ) );
		}
	}

	/**
	 * Verify nonce request
	 * since 1.0.0
	 *
	 * @param $action
	 */
	public function verify_nonce( $action, $field = '_wpnonce' ) {
		if ( ! isset( $_REQUEST[ $field ] ) || ! wp_verify_nonce( $_REQUEST[ $field ], $action ) ) {
			self::send_error( __( 'Error: Nonce verification failed', 'wc-serial-numbers' ) );
		}
	}

	/**
	 * Wrapper function for sending success response
	 * since 1.0.0
	 *
	 * @param null $data
	 */
	public function send_success( $data = null ) {
		wp_send_json_success( $data );
	}

	/**
	 * Wrapper function for sending error
	 * since 1.0.0
	 *
	 * @param null $data
	 */
	public static function send_error( $data = null ) {
		wp_send_json_error( $data );
	}
}

new WC_Serial_Numbers_AJAX();
