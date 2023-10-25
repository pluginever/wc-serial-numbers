<?php
/**
 * Template functions.
 *
 * @since 1.4.6
 * @package WooCommerceSerialNumbers/Functions
 */

use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * A wrapper function for wc_get_template.
 *
 * @param string $template_name Template name.
 * @param array  $args Arguments. (default: array).
 *
 * @since 1.4.6
 * @return void
 */
function wcsn_get_template( $template_name, $args = array() ) {
	$template_name = apply_filters( 'wcsn_get_template', $template_name, $args );
	wc_get_template( $template_name, $args, 'wc-serial-numbers/', WCSN()->get_template_path() );
}

/**
 * output key properties.
 *
 * @param Key  $key Key object.
 * @param bool $output Echo or return.
 *
 * @since 1.4.9
 * @return void|string Return html if $output is false.
 */
function wcsn_display_key_html( $key, $output = true ) {
	$text_align  = is_rtl() ? 'right' : 'left';
	$margin_side = is_rtl() ? 'left' : 'right';

	$properties = array(
		'key'              => array(
			'label'    => __( 'Key', 'wc-serial-numbers' ),
			'value'    => '<code>' . $key->get_key() . '</code>',
			'priority' => 10,
		),
		'activation_email' => array(
			'label'    => __( 'Activation Email', 'wc-serial-numbers' ),
			'value'    => $key->get_customer_email(),
			'priority' => 20,
		),
		'activation_limit' => array(
			'label'    => __( 'Activation Limit', 'wc-serial-numbers' ),
			'value'    => ! empty( $key->get_activation_limit() ) ? number_format_i18n( $key->get_activation_limit() ) : __( 'None', 'wc-serial-numbers' ),
			'priority' => 30,
		),
		'activation_count' => array(
			'label'    => __( 'Activation Count', 'wc-serial-numbers' ),
			'value'    => ! empty( $key->get_activation_count() ) ? number_format_i18n( $key->get_activation_count() ) : __( 'None', 'wc-serial-numbers' ),
			'priority' => 40,
		),
		'expire_date'      => array(
			'label'    => __( 'Expire Date', 'wc-serial-numbers' ),
			'value'    => ! empty( $key->get_expire_date() ) ? $key->get_expire_date() : __( 'Lifetime', 'wc-serial-numbers' ),
			'priority' => 50,
		),
	);

	$status = $key->get_status();
	if ( 'sold' === $status ) {
		$status = '<span style="color: #5b841b;">' . __( 'Active', 'wc-serial-numbers' ) . '</span>';
	} elseif ( 'expired' === $status ) {
		$status = '<span style="color: #a00;">' . __( 'Expired', 'wc-serial-numbers' ) . '</span>';
	} else {
		$status = '&nbsp;';
	}

	$properties['status'] = array(
		'label'    => __( 'Status', 'wc-serial-numbers' ),
		'value'    => $status,
		'priority' => 60,
	);

	/**
	 * Filter key properties.
	 *
	 * @param array $props Key properties.
	 * @param Key $key Key object.
	 *
	 * @since 1.4.9
	 */
	$properties = apply_filters( 'wc_serial_numbers_display_key_props', $properties, $key );

	usort(
		$properties,
		function ( $a, $b ) {
			return $a['priority'] - $b['priority'];
		}
	);

	ob_start();

	?>
	<ul class="wcsn-key-props" style="list-style: none;padding-left:0;padding-right:0;margin-left:0;margin-right:0;">
		<?php foreach ( $properties as $prop => $prop_data ) : ?>
			<li class="wcsn-key-prop wcsn-key-prop-<?php echo esc_attr( $prop ); ?>" style="margin-bottom: 0.5em;">
				<?php if ( ! empty( $prop_data['label'] ) ) : ?>
					<strong style="float: <?php echo esc_attr( $text_align ); ?>;margin-<?php echo esc_attr( $margin_side ); ?>: 0.5em;clear: both;"><?php echo wp_kses_post( $prop_data['label'] ); ?>:</strong>
				<?php endif; ?>
				<?php
				if ( ! empty( $prop_data['value'] ) ) {
					echo wp_kses_post( $prop_data['value'] );
				} else {
					echo '&mdash;';
				}
				?>
			</li>
		<?php endforeach; ?>
		<?php
		/**
		 * Display key properties.
		 *
		 * @param Key $key Key object.
		 * @param array $properties Key properties.
		 *
		 * @since 1.4.9
		 */
		do_action( 'wc_serial_numbers_display_key_props_items', $key, $properties );
		?>
	</ul>
	<?php
	$html = ob_get_clean();

	/**
	 * Filter key properties html.
	 *
	 * @param string $html Key properties html.
	 * @param Key $key Key object.
	 *
	 * @since 1.4.9
	 */
	$html = apply_filters( 'wc_serial_numbers_display_key_props_html', $html, $key );

	if ( $output ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $html;
	}
}

/**
 * Display order keys.
 *
 * @param \WC_Order $order The order object.
 *
 * @since 1.4.6
 * @return void
 */
function wcsn_display_order_keys( $order ) {
	$line_items = wcsn_get_order_line_items_data( $order->get_id() );
	if ( empty( $line_items ) ) {
		return;
	}

	?>
	<section class="woocommerce-order-details wcsn-order-details" style="margin-bottom: 2em;">

		<?php
		/**
		 * Fires before displaying order keys.
		 *
		 * @param \WC_Order $order The order object.
		 * @param array $line_items The line items data.
		 *
		 * @since 1.4.6
		 */
		do_action( 'wc_serial_numbers_before_display_order_keys', $order, $line_items );
		?>

		<?php
		/**
		 * Fires to display order keys.
		 *
		 * @param \WC_Order $order The order object.
		 * @param array $line_items The line items data.
		 *
		 * @since 1.4.6
		 */
		do_action( 'wc_serial_numbers_display_order_keys', $order, $line_items );
		?>

		<?php
		/**
		 * Fires after displaying order keys.
		 *
		 * @param \WC_Order $order The order object.
		 * @param array $line_items The line items data.
		 *
		 * @since 1.4.6
		 */
		do_action( 'wc_serial_numbers_after_display_order_keys', $order, $line_items );
		?>

	</section>
	<?php
}

/**
 * Display order keys title.
 *
 * @param \WC_Order $order The order object.
 * @param array     $line_items The line items data.
 *
 * @since 1.4.6
 * @return void
 */
function wcsn_display_order_keys_title( $order, $line_items ) {
	/**
	 * Filters the order keys table heading.
	 *
	 * @param string $title The table heading.
	 * @param \WC_Order $order The order object.
	 * @param array $line_items The line items data.
	 *
	 * @since 1.4.6
	 */
	$title = apply_filters( 'wc_serial_numbers_order_table_heading', esc_html__( 'Serial Numbers', 'wc-serial-numbers' ), $order, $line_items );

	echo '<h2 class="woocommerce-column__title">' . esc_html( $title ) . '</h2>';
}


/**
 * Display order keys table.
 *
 * @param \WC_Order $order The order object.
 * @param array     $line_items The line items data.
 *
 * @since 1.4.6
 * @return void
 */
function wcsn_display_order_keys_table( $order, $line_items ) {
	foreach ( $line_items as $line_item ) {
		/**
		 * Filters the query arguments for getting keys.
		 *
		 * @param array $args The query arguments.
		 * @param \WC_Order $order The order object.
		 * @param array $line_item The line item data.
		 *
		 * @since 1.4.6
		 */
		$args = apply_filters(
			'wc_serial_numbers_display_order_keys_table_query_args',
			array(
				'order_id'   => $order->get_id(),
				'product_id' => $line_item['product_id'],
				'status__in' => array( 'sold', 'expired' ),
				'limit'      => - 1,
			)
		);

		$keys = wcsn_get_keys( $args );
		?>
		<table class="woocommerce-table woocommerce-table--order-details shop_table order_details wcsn-order-table" style="width: 100%;" cellspacing="0" cellpadding="6">
			<thead>
			<tr>
				<th class="td" scope="col"> <?php printf( '<a href="%s">%s</a>', esc_url( get_permalink( $line_item['product_id'] ) ), esc_html( get_the_title( $line_item['product_id'] ) ) ); ?>
			</tr>
			</thead>
			<tbody>
			<?php if ( ! empty( $keys ) ) : ?>
				<?php foreach ( $keys as $key ) : ?>
					<tr>
						<td class="td" scope="col">
							<?php
							/**
							 * Fires before displaying key properties.
							 *
							 * @param Key $key The key object.
							 * @param \WC_Order $order The order object.
							 *
							 * @since 1.4.6
							 */
							do_action( 'wc_serial_numbers_before_display_key_props', $key, $order );
							?>

							<?php wcsn_display_key_html( $key ); ?>

							<?php
							/**
							 * Fires after displaying key properties.
							 *
							 * @param Key $key The key object.
							 * @param \WC_Order $order The order object.
							 *
							 * @since 1.4.6
							 */
							do_action( 'wc_serial_numbers_after_display_key_props', $key, $order );
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td class="td" scope="col"><?php printf( '<p>%s</p>', esc_html( apply_filters( 'wc_serial_numbers_pending_notice', __( 'Order is waiting for serial numbers to be assigned.', 'wc-serial-numbers' ) ) ) ); ?></td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}
}
