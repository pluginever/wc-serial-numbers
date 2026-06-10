<?php
/**
 * Missing dependencies notice.
 *
 * @since   2.4.0
 * @package WooCommerceSerialNumbers\Admin\Views
 */

defined( 'ABSPATH' ) || exit;

$wcsn_notice = sprintf(
/* translators: 1: plugin name 2: WooCommerce */
	__( '%1$s requires %2$s to be installed and active.', 'wc-serial-numbers' ),
	'<strong>' . esc_html( WCSN()->get( 'name' ) ) . '</strong>',
	'<strong>' . esc_html__( 'WooCommerce', 'wc-serial-numbers' ) . '</strong>'
);

echo '<p>' . wp_kses_post( $wcsn_notice ) . '</p>';
