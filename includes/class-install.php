<?php
namespace Pluginever\WCSerialNumbers;

class Install {
    /**
     * Install constructor.
     */
    public function __construct() {
        add_action( 'init', array( __CLASS__, 'install' ) );
    }

    public static function install() {

        if ( ! is_blog_installed() ) {
            return;
        }

        // Check if we are not already running this routine.
        if ( 'yes' === get_transient( 'wc_serial_numberss_installing' ) ) {
            return;
        }

        self::create_options();

        delete_transient( 'wc_serial_numberss_installing' );
    }

    /**
     * Save option data
     */
    public static function create_options() {
        //save db version
        update_option( 'wpcp_version', WPWSN_VERSION );

        //save install date
        update_option( 'wc_serial_numbers_install_date', current_time( 'timestamp' ) );
    }

}

new Install();
