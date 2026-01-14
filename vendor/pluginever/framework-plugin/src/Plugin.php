<?php

namespace WooCommerceSerialNumbers\Lib;

defined('ABSPATH') || exit;
/**
 * Template for encapsulating some of the most often required abilities of a plugin instance.
 *
 * @since   1.0.0
 * @version 1.1.2
 * @author  Sultan Nasir Uddin <sultan@byteever.com>
 * @package \Lib
 * @subpackage Lib/Plugin
 */
abstract class Plugin implements PluginInterface
{
    /**
     * The plugin data store.
     *
     * @since 1.0.0
     * @var array
     */
    protected $data = array('api_url' => 'https://pluginever.com', 'store_url' => 'https://pluginever.com', 'notices' => array());
    /**
     * The plugin services.
     *
     * @since 1.0.0
     * @var Container
     */
    public $services;
    /**
     * The single instance of the class.
     *
     * @since 1.0.0
     * @var self
     */
    public static $instance;
    /**
     * Gets the single instance of the class.
     * This method is used to create a new instance of the class.
     *
     * @param string|array $data The plugin data.
     *
     * @since 1.0.0
     * @return static
     */
    final public static function create($data = null)
    {
        if (is_null(static::$instance)) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            $called_class = get_called_class();
            if (!is_array($data)) {
                $file = $data;
                $data = array();
                $data['file'] = $file;
            }
            $file = $data['file'];
            $plugin_data = get_plugin_data($file, false, false);
            $plugin_data = array_change_key_case($plugin_data, CASE_LOWER);
            $plugin_data = array_merge($plugin_data, $data);
            static::$instance = new $called_class($plugin_data);
        }
        return static::$instance;
    }
    /**
     * Gets the instance of the class.
     *
     * @since 1.0.0
     * @depecated 1.0.5
     *
     * @return static
     */
    final public static function get_instance()
    {
        _doing_it_wrong(__FUNCTION__, 'Use static::create() instead.', '1.0.5');
        return static::instance();
    }
    /**
     * Gets the instance of the class.
     *
     * @since 1.0.0
     *
     * @return static
     */
    final public static function instance()
    {
        if (null === static::$instance) {
            _doing_it_wrong(__FUNCTION__, 'Plugin instance called before initiating the instance.', '1.0.0');
        }
        return static::$instance;
    }
    /**
     * Plugin constructor.
     *
     * @param array $data The plugin data.
     *
     * @since 1.0.0
     */
    protected function __construct($data)
    {
        $this->services = new Container();
        // Only set the data keys that are not already set.
        $this->data = array_merge($this->data, $data);
        // If the slug is not set, then set it.
        if (!isset($this->data['slug'])) {
            $this->data['slug'] = basename($this->data['file'], '.php');
        }
        // If the version is not set, then set it.
        if (!isset($this->data['version'])) {
            $this->data['version'] = '1.0.0';
        }
        // If the prefix is not set, then set it.
        if (!isset($this->data['prefix'])) {
            $this->data['prefix'] = str_replace('-', '_', $this->data['slug']);
        }
        // Register hooks.
        add_action('init', array($this, 'load_plugin_textdomain'));
        if (is_admin()) {
            add_filter('wp_redirect', array($this, 'save_notices'), 1);
            add_action('init', array($this, 'load_notices'), 1);
            add_action('admin_notices', array($this, 'display_admin_notices'));
            add_action('admin_footer', array($this, 'display_admin_notices'));
            add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);
            add_filter('plugin_action_links_' . $this->get_basename(), array($this, 'plugin_action_links'));
        }
    }
    /**
     * Prevents cloning.
     *
     * @since 1.0.0
     * @return void
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, 'Cloning is forbidden.', '1.0.0');
    }
    /**
     * Prevents unserializing.
     *
     * @since 1.0.0
     * @return void
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, 'Unserializing instances of this class is forbidden.', '1.0.0');
    }
    /**
     * Magic method to get the plugin data.
     *
     * @param string $key The data key.
     *
     * @since 1.0.0
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        } elseif (isset($this->services[$key])) {
            return $this->services[$key];
        }
        return null;
    }
    /**
     * Magic method to set the plugin data.
     *
     * @param string $key The data key.
     * @param mixed  $value The data value.
     *
     * @since 1.0.0
     * @return void
     */
    public function __set($key, $value)
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = $value;
        } elseif (!isset($this->services[$key])) {
            $this->services[$key] = $value;
        }
    }
    /**
     * Magic method to check if a property is set.
     *
     * @param string $key The data key.
     *
     * @since 1.0.0
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]) || isset($this->services[$key]);
    }
    /**
     * Save messages to the database.
     *
     * @param string $location the URL to redirect to.
     *
     * @since 1.0.0
     * @return string the URL to redirect to.
     */
    public function save_notices($location)
    {
        if (!empty($this->data['notices']) && is_array($this->data['notices'])) {
            $nonce = wp_create_nonce($this->get_prefix() . '_notices');
            update_option($this->get_prefix() . '_notices', $this->data['notices']);
            $location = add_query_arg('notice', $nonce, $location);
        }
        return $location;
    }
    /**
     * Load messages from the database.
     *
     * @since 1.0.0
     * @return void
     */
    public function load_notices()
    {
        $notice = isset($_GET['notice']) ? sanitize_key($_GET['notice']) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!empty($notice) && wp_verify_nonce($notice, $this->get_prefix() . '_notices')) {
            $notices = get_option($this->get_prefix() . '_notices', array());
            if (!empty($notices) && is_array($notices)) {
                foreach ($notices as $notice) {
                    $this->add_notice($notice['message'], $notice['type']);
                }
                update_option($this->get_prefix() . '_notices', array());
            }
        }
    }
    /**
     * Render admin notices.
     *
     * @since 1.0.0
     * @return void
     */
    public function display_admin_notices()
    {
        foreach ($this->data['notices'] as $notice) {
            ?>
			<div class="notice notice-<?php 
            echo esc_attr($notice['type']);
            ?> is-dismissible">
				<p><?php 
            echo wp_kses_post($notice['message']);
            ?></p>
			</div>
			<?php 
        }
        $this->data['notices'] = array();
    }
    /**
     * Add plugin meta links.
     *
     * @param array  $links Plugin meta links.
     * @param string $file Plugin file.
     *
     * @since 1.0.0
     * @return array
     */
    public function plugin_row_meta($links, $file)
    {
        if ($file !== $this->get_basename()) {
            return $links;
        }
        foreach ($this->get_meta_links() as $key => $link) {
            $links[$key] = sprintf('<a href="%1$s" target="_blank">%2$s</a>', esc_url($link['url']), esc_html($link['label']));
        }
        return $links;
    }
    /**
     * Add plugin action links.
     *
     * @param array $links Plugin action links.
     *
     * @since 1.0.0
     * @return array
     */
    public function plugin_action_links($links)
    {
        $actions = array();
        foreach ($this->get_action_links() as $key => $link) {
            $actions[$key] = sprintf('<a href="%1$s">%2$s</a>', esc_url($link['url']), wp_kses_post($link['label']));
        }
        // add the actions to beginning of the links.
        $links = array_merge($actions, $links);
        if ($this->has_premium() && !$this->is_premium_active()) {
            // Add UTM parameters to the URL.
            $pro_link = add_query_arg(array('utm_source' => 'plugins-page', 'utm_medium' => 'plugin-action-link', 'utm_campaign' => 'plugins-page', 'utm_term' => 'go-pro', 'utm_id' => $this->get_slug()), $this->get_premium_url());
            $links['go_pro'] = sprintf('<a href="%1$s" target="_blank" style="color: #39b54a; font-weight: bold;">%2$s</a>', esc_url($pro_link), esc_html__('Go Pro', 'wc-serial-numbers'));
        }
        return $links;
    }
    /**
     * Register plugin textdomain.
     *
     * @since 1.0.0
     * @return void
     */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain($this->get_text_domain(), false, $this->get_lang_path());
    }
    /*
    |--------------------------------------------------------------------------
    | PLUGIN DATA
    |--------------------------------------------------------------------------
    |
    | Methods to get plugin data.
    |
    */
    /**
     * Gets the plugin data.
     *
     * @param string $key The data key.
     *
     * @since 1.0.0
     *
     * @return mixed
     */
    public function get_data($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
    /**
     * Gets the plugin file.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_file()
    {
        return $this->get_data('file');
    }
    /**
     * Gets the plugin version.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_version()
    {
        return $this->get_data('version');
    }
    /**
     * Gets the plugin slug.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_slug()
    {
        return $this->get_data('slug');
    }
    /**
     * Get the plugin prefix.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_prefix()
    {
        return $this->get_data('prefix');
    }
    /**
     * Get the 'basename' for the plugin (e.g. my-plugin/my-plugin.php).
     *
     * @since  1.0.0
     * @return string The plugin basename.
     */
    public function get_basename()
    {
        return plugin_basename($this->get_file());
    }
    /**
     * Gets the plugin name.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_name()
    {
        return $this->get_data('name');
    }
    /**
     * Get the documentation URI for this plugin.
     *
     * @since  1.0.0
     * @return string (URI)
     */
    public function get_docs_url()
    {
        return $this->get_data('docs_url');
    }
    /**
     * Get the support URI for this plugin.
     *
     * @since  1.0.0
     * @return string (URI)
     */
    public function get_support_url()
    {
        return $this->get_data('support_url');
    }
    /**
     * Get the review URI for this plugin.
     *
     * @since  1.0.0
     * @return string (URI)
     */
    public function get_review_url()
    {
        return $this->get_data('review_url');
    }
    /**
     * Get plugin settings url.
     *
     * @since  1.0.0
     * @return string (URI)
     */
    public function get_settings_url()
    {
        return $this->get_data('settings_url');
    }
    /**
     * Get plugin store url.
     *
     * @since 1.0.0
     * @return string (URI)
     */
    public function get_store_url()
    {
        return $this->get_data('store_url');
    }
    /**
     * Get plugin api url.
     *
     * @since 1.0.0
     * @return string (URI)
     */
    public function get_api_url()
    {
        return $this->get_data('api_url');
    }
    /**
     * Get premium plugin url.
     *
     * @since 1.0.0
     * @return string (URI)
     */
    public function get_premium_url()
    {
        return $this->get_data('premium_url');
    }
    /**
     * Get premium plugin basename.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_premium_basename()
    {
        $basename = $this->get_data('premium_basename');
        if (!empty($basename) && false === strpos($basename, '/')) {
            $basename = $basename . '/' . $basename . '.php';
        }
        return $basename;
    }
    /**
     * Has premium plugin.
     *
     * @since 1.0.0
     * @return bool
     */
    public function has_premium()
    {
        return $this->get_premium_basename() && $this->get_premium_url();
    }
    /**
     * Is premium plugin active.
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_premium_active()
    {
        return $this->has_premium() && $this->is_plugin_active($this->get_premium_basename());
    }
    /**
     * Gets the plugin text domain.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_text_domain()
    {
        return $this->get_data('textdomain');
    }
    /**
     * Gets the plugin domain path.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_domain_path()
    {
        return $this->get_data('domainpath');
    }
    /**
     * Gets the plugin language directory.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_lang_path()
    {
        // generate language directory path.
        $lang_dir = $this->get_slug() . rtrim($this->get_domain_path(), '/');
        // return language directory path.
        return $lang_dir;
    }
    /**
     *
     * Get the plugin dir path.
     *
     * @param string $path Optional. Path relative to the plugin dir path.
     *
     * @since 1.0.2
     * @return string
     */
    public function get_dir_path($path = '')
    {
        $dir_path = plugin_dir_path($this->get_file());
        if (!empty($path)) {
            $dir_path = trailingslashit($dir_path) . ltrim($path, '/');
        }
        return $dir_path;
    }
    /**
     * Get the plugin dir url.
     *
     * @param string $path Optional. Path relative to the plugin dir url.
     *
     * @since 1.0.2
     * @return string
     */
    public function get_dir_url($path = '')
    {
        $dir_url = plugin_dir_url($this->get_file());
        if (!empty($path)) {
            $dir_url = trailingslashit($dir_url) . ltrim($path, '/');
        }
        return $dir_url;
    }
    /**
     * Gets the plugin path.
     *
     * @since 1.0.0
     * @deprecated 1.0.2
     *
     * @return string
     */
    public function get_path()
    {
        return $this->get_dir_path();
    }
    /**
     * Gets the plugin url.
     *
     * @since 1.0.0
     * @deprecated 1.0.2
     *
     * @return string
     */
    public function get_url()
    {
        return $this->get_dir_url();
    }
    /**
     * Get template path.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_template_path()
    {
        return $this->get_dir_path('templates/');
    }
    /**
     * Get assets path.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_assets_path()
    {
        return $this->get_dir_path('assets/dist/');
    }
    /**
     * Get assets url.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_assets_url()
    {
        return $this->get_dir_url('assets/dist/');
    }
    /**
     * Get meta links.
     *
     * @since 1.0.0
     * @return array
     */
    public function get_meta_links()
    {
        $links = array();
        if (!empty($this->get_docs_url())) {
            $links['docs'] = array('label' => __('Documentation', 'wc-serial-numbers'), 'url' => $this->get_docs_url());
        }
        if (!empty($this->get_support_url())) {
            $links['support'] = array('label' => __('Support', 'wc-serial-numbers'), 'url' => $this->get_support_url());
        }
        if (!empty($this->get_review_url())) {
            $links['review'] = array('label' => __('Review', 'wc-serial-numbers'), 'url' => $this->get_review_url());
        }
        $links['plugins'] = array('label' => __('More Plugins', 'wc-serial-numbers'), 'url' => $this->get_store_url());
        return $links;
    }
    /**
     * Get action links.
     *
     * @since 1.0.0
     * @return array
     */
    public function get_action_links()
    {
        $links = array();
        if (!empty($this->get_settings_url())) {
            $links['settings'] = array('label' => __('Settings', 'wc-serial-numbers'), 'url' => $this->get_settings_url());
        }
        return $links;
    }
    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    |
    | This section is for helper methods.
    |
    */
    /**
     * Define constant if not already set.
     *
     * @param string      $name  Constant name.
     * @param string|bool $value Constant value.
     * @since 1.0.6
     * @return void
     */
    protected function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }
    /**
     * Get plugin database version.
     *
     * @since 1.0.0
     * @return string (version)
     */
    public function get_db_version()
    {
        return get_option($this->get_prefix() . '_version', null);
    }
    /**
     * Update plugin database version.
     *
     * @param string $version Version.
     * @param bool   $update  Whether to update or not.
     *
     * @since 1.0.0
     * @return void
     */
    public function update_db_version($version = null, $update = true)
    {
        if (empty($version)) {
            $version = $this->get_version();
        }
        if ($update) {
            update_option($this->get_prefix() . '_version', $version);
            return;
        }
        add_option($this->get_prefix() . '_version', $version);
    }
    /**
     * Add message to the list of messages.
     *
     * @param string $notice Message to be added.
     * @param string $type Message type. Default 'success'.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_notice($notice, $type = 'success')
    {
        if (empty($notice) && !in_array($type, array('success', 'info', 'warning', 'error'), true)) {
            $type = 'success';
        }
        $this->data['notices'][] = array('type' => $type, 'message' => $notice);
    }
    /**
     * Enqueue scripts helper.
     *
     * @param string $handle Name of the script. Should be unique.
     * @param string $src Relative path to the script from the plugin's assets directory.
     * @param array  $deps An array of registered script handles this script depends on. Default empty array.
     * @param bool   $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
     *
     * @since 1.0.0
     * @return void
     */
    public function register_script($handle, $src, $deps = array(), $in_footer = false)
    {
        // check if $src is relative or absolute.
        if (!preg_match('/^(http|https):\/\//', $src)) {
            $url = $this->get_assets_url() . ltrim($src);
            $path = $this->get_assets_path() . ltrim($src);
        } else {
            $url = $src;
            $path = str_replace($this->get_dir_url(), $this->get_dir_path(), $src);
        }
        $php_file = str_replace('.js', '.asset.php', $path);
        $asset = $php_file && file_exists($php_file) ? require $php_file : array('dependencies' => array(), 'version' => $this->get_version());
        $deps = array_merge($asset['dependencies'], $deps);
        $ver = $asset['version'];
        wp_register_script($handle, $url, $deps, $ver, $in_footer);
        if (array_intersect($deps, array('react', 'react-dom'))) {
            // add text domain to the script.
            $text_domain = $this->get_data('text_domain');
            $domain_path = $this->get_data('domain_path');
            wp_set_script_translations($handle, $text_domain, dirname($this->get_basename()) . $domain_path);
        }
    }
    /**
     * Enqueue styles helper.
     *
     * @param string $handle Name of the stylesheet. Should be unique.
     * @param string $src Relative path to the stylesheet from the plugin's assets directory.
     * @param array  $deps An array of registered stylesheet handles this stylesheet depends on. Default empty array.
     * @param string $media The media for which this stylesheet has been defined. Default 'all'.
     *
     * @since 1.0.0
     * @return void
     */
    public function register_style($handle, $src, $deps = array(), $media = 'all')
    {
        if (!preg_match('/^(http|https):\/\//', $src)) {
            $url = $this->get_assets_url() . ltrim($src);
            $path = $this->get_assets_path() . ltrim($src);
        } else {
            $url = $src;
            $path = str_replace($this->get_dir_url(), $this->get_dir_url(), $src);
        }
        $php_file = str_replace('.css', '.asset.php', $path);
        $asset = $php_file && file_exists($php_file) ? require $php_file : array('dependencies' => array(), 'version' => $this->get_version());
        $deps = array_merge($asset['dependencies'], $deps);
        $ver = $asset['version'];
        wp_register_style($handle, $url, $deps, $ver, $media);
    }
    /**
     * Enqueue scripts helper.
     *
     * @param string $handle Name of the script. Should be unique.
     * @param string $src Relative path to the script from the plugin's assets directory.
     * @param array  $deps An array of registered script handles this script depends on. Default empty array.
     * @param bool   $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_script($handle, $src, $deps = array(), $in_footer = false)
    {
        $this->register_script($handle, $src, $deps, $in_footer);
        wp_enqueue_script($handle);
    }
    /**
     * Enqueue styles helper.
     *
     * @param string $handle Name of the stylesheet. Should be unique.
     * @param string $src Relative path to the stylesheet from the plugin's assets directory.
     * @param array  $deps An array of registered stylesheet handles this stylesheet depends on. Default empty array.
     * @param string $media The media for which this stylesheet has been defined. Default 'all'.
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_style($handle, $src, $deps = array(), $media = 'all')
    {
        $this->register_style($handle, $src, $deps, $media);
        wp_enqueue_style($handle);
    }
    /**
     * Check if the plugin is active.
     *
     * @param string $plugin The plugin slug or basename.
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_plugin_active($plugin)
    {
        // Check if the $plugin is a basename or a slug. If it's a slug, convert it to a basename.
        if (false === strpos($plugin, '/')) {
            $plugin = $plugin . '/' . $plugin . '.php';
        }
        $active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array($plugin, $active_plugins, true) || array_key_exists($plugin, $active_plugins);
    }
    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     *
     * @since  1.1.0
     * @return bool
     */
    protected function is_request($type)
    {
        switch ($type) {
            case 'admin':
                return is_admin() || defined('WP_CLI') && WP_CLI;
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
            case 'rest':
                return defined('REST_REQUEST');
        }
        return false;
    }
    /**
     * Log an error.
     *
     * Description of levels:
     * 'emergency': System is unusable.
     * 'alert': Action must be taken immediately.
     * 'critical': Critical conditions.
     * 'error': Error conditions.
     * 'warning': Warning conditions.
     * 'notice': Normal but significant condition.
     * 'info': Informational messages.
     * 'debug': Debug-level messages.
     *
     * @param mixed  $message The error message.
     * @param string $level The error level.
     * @param array  $data Optional. Data to log.
     *
     * @return void
     */
    public function log($message, $level = 'debug', $data = array())
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        if (is_object($message) || is_array($message)) {
            $message = print_r($message, true);
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
        } elseif (is_bool($message)) {
            $message = $message ? 'true' : 'false';
        } elseif (is_null($message)) {
            $message = 'null';
        } else {
            $message = (string) $message;
        }
        $line = sprintf('[%s] %s', strtoupper($level), $message);
        if (!empty($data)) {
            $line .= ' ' . wp_json_encode($data);
        }
        error_log($line);
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
    }
}