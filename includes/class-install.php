<?php
namespace Pluginever\WCSerialNumberPro;

class Install {
    /**
     * Install constructor.
     */
    public function __construct() {
//        add_action( 'init', array( __CLASS__, 'install' ) );
//        add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
    }

    public static function install() {

        if ( ! is_blog_installed() ) {
            return;
        }

        // Check if we are not already running this routine.
        if ( 'yes' === get_transient( 'wc_serial_number_pros_installing' ) ) {
            return;
        }

        self::create_options();
        self::create_tables();
        self::create_roles();
        self::create_cron_jobs();
        
        delete_transient( 'wc_serial_number_pros_installing' );
    }

    /**
     * Save option data
     */
    public static function create_options() {
        //save db version
        update_option( 'wpcp_version', WPWSNP_VERSION );

        //save install date
        update_option( 'wc_serial_number_pros_install_date', current_time( 'timestamp' ) );
    }

    private static function create_tables() {
        global $wpdb;
        $collate = '';
        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty( $wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }
            if ( ! empty( $wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }
        $table_schema = [
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}table` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                  UNIQUE (url)
            ) $collate;",
        ];
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }
    }

    /**
     * Create roles and capabilities.
     */
    private static function create_roles() {
        global $wp_roles;

        if ( ! class_exists( 'WP_Roles' ) ) {
            return;
        }

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new \WP_Roles();
        }

        // Customer role.
        add_role(
            'userrole',
            __( 'User Role', 'wc-serial-number-pro' ),
            self::get_caps( 'userrole' )
        );

        //add all new caps to admin
        $admin_capabilities = self::get_caps( 'administrator' );

        foreach ( $admin_capabilities as $cap ) {
            $wp_roles->add_cap( 'administrator', $cap );
        }
    }

    /**
     * @param $role
     *
     * @return array
     */
    private static function get_caps( $role ) {
        $caps = [
            'userrole'      => [],
            'administrator' => [],
        ];

        return $caps[ $role ];
    }

    /**
     * Add more cron schedules.
     *
     * @param  array $schedules List of WP scheduled cron jobs.
     *
     * @return array
     */
    public static function cron_schedules( $schedules ) {
        $schedules['monthly'] = array(
            'interval' => 2635200,
            'display'  => __( 'Monthly', 'wc-serial-number-pro' ),
        );

        return $schedules;
    }

    /**
     * Create cron jobs (clear them first).
     */
    private static function create_cron_jobs() {
        wp_clear_scheduled_hook( 'wc_serial_number_pro_daily_cron' );
        wp_schedule_event( time(), 'daily', 'wc_serial_number_pro_daily_cron' );
    }


}

new Install();