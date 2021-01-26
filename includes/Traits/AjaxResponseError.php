<?php

namespace WCSerialNumbers\Traits;

use Exception;
use WP_Error;
use WCSerialNumbers\Exceptions\WCSNException;

trait AjaxResponseError {

    /**
     * Send Ajax error response
     *
     * @since 1.2.8
     *
     * @param \Exception $e
     * @param string     $default_message
     *
     * @return void
     */
    protected static function send_response_error( Exception $e, $default_message = '' ) {
        if ( $e instanceof WCSNException ) {
            $error_code = $e->get_error_code();

            if ( $error_code instanceof WP_Error ) {
                wp_send_json_error( $error_code, 400 );
            }

            wp_send_json_error( $e->get_message(), $e->get_status_code() );
        }

        $default_message = $default_message ? $default_message : __( 'Something went wrong', 'wc-serial-numbers' );
        wp_send_json_error( $default_message, 422 );
    }
}
