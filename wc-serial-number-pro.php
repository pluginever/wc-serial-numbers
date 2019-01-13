<?php
/**
 * Plugin Name: WC Serial Number Pro
 * Plugin URI:  https://www.pluginever.com
 * Description: The Best WordPress Plugin ever made!
 * Version:     1.0.0
 * Author:      pluginever
 * Author URI:  https://www.pluginever.com
 * Donate link: https://www.pluginever.com
 * License:     GPLv2+
 * Text Domain: wc-serial-number-pro
 * Domain Path: /i18n/languages/
 */

/**
 * Copyright (c) 2019 pluginever (email : support@pluginever.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Add admin notice if wc-serial-numbers is not active
 */

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

if (!is_plugin_active('wc-serial-numbers/wc-serial-numbers.php')) {

	function wsn_activate_notice() {
		?>

		<div class="notice notice-error is-dismissible">
			<p><?php _e('Please, Activate WC Serial Numbers first, to make workable WC Serial Numbers PRO.', 'wc-serial-numbers'); ?></p>
		</div>

		<?php
	}

	add_action('admin_notices', 'wsn_activate_notice');

	return;
}


/**
 * Main initiation class
 *
 * @since 1.0.0
 */

/**
 * Main WCSerialNumberPro Class.
 *
 * @class WCSerialNumberPro
 */
final class WCSerialNumberPro {
    /**
     * WCSerialNumberPro version.
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * Minimum PHP version required
     *
     * @var string
     */
    private $min_php = '5.6.0';

    /**
     * The single instance of the class.
     *
     * @var WCSerialNumberPro
     * @since 1.0.0
     */
    protected static $instance = null;


    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = array();

    /**
     * Main WCSerialNumberPro Instance.
     *
     * Ensures only one instance of WCSerialNumberPro is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @return false|WCSerialNumberPro - Main instance.
     */
    public static function instance() {


        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->setup();
        }

        return self::$instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'wc-serial-number-pro' ), '1.0.0' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wc-serial-number-pro' ), '2.1' );
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset( $prop ) {
        return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
    }

    /**
     * EverProjects Constructor.
     */
    public function setup() {
        $this->check_environment();
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
        $this->plugin_init();
        do_action( 'wc_serial_number_pro_loaded' );
    }

    /**
     * Ensure theme and server variable compatibility
     */
    public function check_environment() {
        if ( version_compare( PHP_VERSION, $this->min_php, '<=' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );

            wp_die( "Unsupported PHP version Min required PHP Version:{$this->min_php}" );
        }
    }

    /**
     * Define EverProjects Constants.
     *
     * @since 1.0.0
     * @return void
     */
    private function define_constants() {
        //$upload_dir = wp_upload_dir( null, false );
        define( 'WPWSNP_VERSION', $this->version );
        define( 'WPWSNP_FILE', __FILE__ );
        define( 'WPWSNP_PATH', dirname( WPWSNP_FILE ) );
        define( 'WPWSNP_INCLUDES', WPWSNP_PATH . '/includes' );
        define( 'WPWSNP_URL', plugins_url( '', WPWSNP_FILE ) );
        define( 'WPWSNP_ASSETS_URL', WPWSNP_URL . '/assets' );
        define( 'WPWSNP_TEMPLATES_DIR', WPWSNP_PATH . '/templates' );
    }


    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined( 'DOING_AJAX' );
            case 'cron':
                return defined( 'DOING_CRON' );
            case 'frontend':
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! defined( 'REST_REQUEST' );
        }
    }


    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes() {
        //core includes
		include_once WPWSNP_INCLUDES . '/core-functions.php';
		include_once WPWSNP_INCLUDES . '/class-install.php';


		//admin includes
		if ( $this->is_request( 'admin' ) ) {
			include_once WPWSNP_INCLUDES . '/admin/class-admin.php';
		}

		//frontend includes
		if ( $this->is_request( 'frontend' ) ) {
			include_once WPWSNP_INCLUDES . '/class-frontend.php';
		}

    }

    /**
     * Hook into actions and filters.
     *
     * @since 2.3
     */
    private function init_hooks() {
        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );

        //add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
    }

    /**
     * Initialize plugin for localization
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function localization_setup() {
        load_plugin_textdomain( 'wc-serial-number-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Plugin action links
     *
     * @param  array $links
     *
     * @return array
     */
    public function plugin_action_links( $links ) {
        //$links[] = '<a href="' . admin_url( 'admin.php?page=' ) . '">' . __( 'Settings', '' ) . '</a>';
        return $links;
    }

    public function plugin_init() {

    }

    /**
     * Get the plugin url.
     *
     * @return string
     */
    public function plugin_url() {
        return untrailingslashit( plugins_url( '/', WPWSNP_FILE ) );
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( WPWSNP_FILE ) );
    }

    /**
     * Get the template path.
     *
     * @return string
     */
    public function template_path() {
        return WPWSNP_TEMPLATES_DIR;
    }

}

function wc_serial_number_pro(){
    return WCSerialNumberPro::instance();
}

//fire off the plugin
wc_serial_number_pro();

register_deactivation_hook(__FILE__, 'wsnp_deactivation');

function wsnp_deactivation() {
	wp_clear_scheduled_hook('wsn_send_email_notification');
}



