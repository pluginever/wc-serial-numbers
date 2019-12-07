<?php
defined( 'ABSPATH' ) || exit();

function wc_serial_numbers_admin_bar_notification_styles() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	?>
	<style>
		#wp-admin-bar-wsn-wc-serial-numbers .wsn_admin_bar_notification {
			padding-right: 25px
		}

		#wp-admin-bar-wsn-wc-serial-numbers .ever-notification {
			position: absolute;
			right: 3px;
			top: 0
		}

		#wp-admin-bar-wsn-wc-serial-numbers .ever-notification > .alert {
			background: #fff;
			padding: 0 5px 0 3px;
			border-radius: 5px;
			color: red;
			cursor: pointer
		}

		#wp-admin-bar-wsn-wc-serial-numbers .ever-notification:hover + .ever-notification-list {
			display: -webkit-box;
			display: -webkit-flex;
			display: flex
		}

		#wp-admin-bar-wsn-wc-serial-numbers .ever-notification-list {
			position: fixed;
			color: #f0fafe;
			background: #333;
			display: none;
			-webkit-box-orient: vertical;
			-webkit-box-direction: normal;
			-webkit-flex-direction: column;
			flex-direction: column;
			z-index: 999999;
			margin: -1px 0 0 -10px;
			max-height: 100%;
			overflow-y: scroll
		}

		#wp-admin-bar-wsn-wc-serial-numbers .ever-notification-list:hover {
			display: -webkit-box;
			display: -webkit-flex;
			display: flex
		}

		#wp-admin-bar-wsn-wc-serial-numbers .ever-notification-list.alert > li {
			border-left: 5px solid red;
			padding: 0 15px 0 10px;
			margin: 5px 0;
			font-size: 14px
		}

		#wp-admin-bar-wsn-wc-serial-numbers .ever-notification-list.alert > li > a {
			display: inline;
			padding: 0
		}
	</style>

<?php }

add_action( 'admin_head', 'wc_serial_numbers_admin_bar_notification_styles' );
add_action( 'wp_head', 'wc_serial_numbers_admin_bar_notification_styles' );
