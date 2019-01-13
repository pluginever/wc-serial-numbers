<?php
/**
 * Plugin Name: WC Serial Numbers
 * Plugin URI:  https://www.pluginever.com
 * Description: The Best WordPress Plugin ever made!
 * Version:     1.0.0
 * Author:      pluginever
 * Author URI:  https://www.pluginever.com
 * Donate link: https://www.pluginever.com
 * License:     GPLv2+
 * Text Domain: wc-serial-numbers
 * Domain Path: /i18n/languages/
 */

/**
 * Copyright (c) 2018 pluginever (email : support@pluginever.com)
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
if (!defined('ABSPATH'))
	exit;
/**
 * Main initiation class
 *
 * @since 1.0.0
 */

/**
 * Main WCSerialNumbers Class.
 *
 * @class WCSerialNumbers
 */
final class WCSerialNumbers {
	/**
	 * The single instance of the class.
	 *
	 * @var WCSerialNumbers
	 * @since 1.0.0
	 */
	protected static $instance = null;
	/**
	 * WCSerialNumbers version.
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
	 * Holds various class instances
	 *
	 * @var array
	 */
	private $container = array();


	/**
	 * Main WCSerialNumbers Instance.
	 *
	 * Ensures only one instance of WCSerialNumbers is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return boolean|WCSerialNumbers - Main instance.
	 */
	public static function instance() {

		if (!class_exists('WooCommerce')) {

			/**
			 * Add admin notice if WooCommerce is not active
			 */

			function wsn_woocommerce_activate_notice() {
				?>

				<div class="notice notice-error is-dismissible">
					<p><?php _e('Please, Activate WooCommerce first, to make workable WC Serial Numbers.', 'wc-serial-numbers'); ?></p>
				</div>

				<?php
			}

			add_action('admin_notices', 'wsn_woocommerce_activate_notice');

			return false;
		}

		if (is_null(self::$instance)) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
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
		do_action('wc_serial_numbers_loaded');
	}

	/**
	 * Ensure theme and server variable compatibility
	 */
	public function check_environment() {
		if (version_compare(PHP_VERSION, $this->min_php, '<=')) {
			deactivate_plugins(plugin_basename(__FILE__));

			wp_die("Unsupported PHP version Min required PHP Version:{$this->min_php}");
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
		define('WPWSN_VERSION', $this->version);
		define('WPWSN_FILE', __FILE__);
		define('WPWSN_PATH', dirname(WPWSN_FILE));
		define('WPWSN_INCLUDES', WPWSN_PATH . '/includes');
		define('WPWSN_URL', plugins_url('', WPWSN_FILE));
		define('WPWSN_ASSETS_URL', WPWSN_URL . '/assets');
		define('WPWSN_TEMPLATES_DIR', WPWSN_PATH . '/templates');

		define('WPWSN_SERIAL_INDEX_PAGE', admin_url('admin.php?page=serial-numbers'));
		define('WPWSN_ADD_SERIAL_PAGE', admin_url('admin.php?page=add-serial-number&type=manual'));
		define('WPWSN_GENERATE_SERIAL_PAGE', admin_url('admin.php?page=add-serial-number&type=automate'));
		define('WPWSN_SETTINGS_PAGE', admin_url('admin.php?page=wc_serial_numbers-settings'));
		define('WPWSN_ADD_GENERATE_RULE', admin_url('admin.php?page=add-generator-rule'));
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		//core includes
		include_once WPWSN_INCLUDES . '/core-functions.php';
		include_once WPWSN_INCLUDES . '/class-install.php';

		//admin includes
		if ($this->is_request('admin')) {
			include_once WPWSN_INCLUDES . '/admin/class-admin.php';
		}

		//frontend includes
		if ($this->is_request('frontend')) {
			include_once WPWSN_INCLUDES . '/class-frontend.php';
		}

	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 *
	 * @return bool
	 */
	private function is_request($type) {
		switch ($type) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined('DOING_AJAX');
			case 'cron':
				return defined('DOING_CRON');
			case 'frontend':
				return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON') && !defined('REST_REQUEST');
		}
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 2.3
	 */
	private function init_hooks() {
		// Localize our plugin
		add_action('init', array($this, 'localization_setup'));

		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
	}

	public function plugin_init() {

	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		_doing_it_wrong(__FUNCTION__, __('Cloning is forbidden.', 'wc-serial-numbers'), '1.0.0');
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		_doing_it_wrong(__FUNCTION__, __('Unserializing instances of this class is forbidden.', 'wc-serial-numbers'), '2.1');
	}

	/**
	 * Magic getter to bypass referencing plugin.
	 *
	 * @param $prop
	 *
	 * @return mixed
	 */
	public function __get($prop) {
		if (array_key_exists($prop, $this->container)) {
			return $this->container[$prop];
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
	public function __isset($prop) {
		return isset($this->{$prop}) || isset($this->container[$prop]);
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function localization_setup() {
		load_plugin_textdomain('wc-serial-numbers', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}

	/**
	 * Plugin action links
	 *
	 * @param  array $links
	 *
	 * @return array
	 */
	public function plugin_action_links($links) {
		$links[] = '<a href="' . admin_url('admin.php?page=wc_serial_numbers-settings') . '">' . __('Settings', 'wc-serial-numbers') . '</a>';
		return $links;
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit(plugins_url('/', WPWSN_FILE));
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit(plugin_dir_path(WPWSN_FILE));
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return WPWSN_TEMPLATES_DIR;
	}

}

function wc_serial_numbers() {
	return WCSerialNumbers::instance();
}

//fire off the plugin
wc_serial_numbers();
