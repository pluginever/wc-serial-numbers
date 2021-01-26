<?php

namespace WCSerialNumbers\Upgrade;

use Exception;
use WCSerialNumbers\Exceptions\WCSNException;
use WCSerialNumbers\Traits\AjaxResponseError;

class AdminNotice {

    use AjaxResponseError;

    /**
     * Show admin notice to upgrade database
     *
     * @since 1.2.8
     *
     * @return void
     */
    public static function show_notice() {
        if ( ! current_user_can( 'update_plugins' ) || wc_serial_numbers()->upgrades->has_ongoing_process() ) {
            return;
        }

        if ( ! wc_serial_numbers()->upgrades->is_upgrade_required() ) {
            /**
             * Fires when upgrade is not required
             *
             * @since 1.2.8
             */
            do_action( 'wcsn_upgrade_is_not_required' );
            return;
        }

		require_once dirname( __FILE__ ) . '/upgrade-notice.php';

		$css_url = wc_serial_numbers()->plugin_url() . '/assets/css';
		$js_url  = wc_serial_numbers()->plugin_url() . '/assets/js';

        wp_enqueue_style( 'wcsn-upgrade', $css_url . '/wcsn-upgrade.css', [], WC_SERIAL_NUMBER_PLUGIN_VERSION );
		wp_enqueue_script( 'wcsn-upgrade', $js_url . '/wcsn-upgrade.js', [ 'jquery' ], WC_SERIAL_NUMBER_PLUGIN_VERSION, true );

		wp_localize_script( 'wcsn-upgrade', 'wcsn_upgrader', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wcsn_upgrader' ),
		] );
    }

    /**
     * Ajax handler method to initiate upgrade process
     *
     * @since 1.2.8
     *
     * @return void
     */
    public static function do_upgrade() {
        check_ajax_referer( 'wcsn_upgrader' );

        try {
            if ( ! current_user_can( 'update_plugins' ) ) {
                throw new WCSNException( 'wcsn_ajax_upgrade_error', __( 'You are not authorize to perform this operation.', 'wc-serial-numbers' ), 403 );
            }

            if ( wc_serial_numbers()->upgrades->has_ongoing_process() ) {
                throw new WCSNException( 'wcsn_ajax_upgrade_error', __( 'There is an upgrading process going on.', 'wc-serial-numbers' ), 400 );
            }

            if ( ! wc_serial_numbers()->upgrades->is_upgrade_required() ) {
                throw new WCSNException( 'wcsn_ajax_upgrade_error', __( 'Update is not required.', 'wc-serial-numbers' ), 400 );
            }

            wc_serial_numbers()->upgrades->do_upgrade();

            wp_send_json_success( [ 'success' => true ], 201 );
        } catch ( Exception $e ) {
            self::send_response_error( $e );
        }
    }
}
