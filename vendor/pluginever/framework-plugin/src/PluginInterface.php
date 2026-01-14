<?php

namespace WooCommerceSerialNumbers\Lib;

defined('ABSPATH') || exit;
/**
 * Describes a plugin instance.
 *
 * @since 1.0.0
 * @version 1.1.2
 * @author  Sultan Nasir Uddin <sultan@byteever.com>
 * @package \Lib
 * @subpackage Lib/Plugin
 */
interface PluginInterface
{
    /**
     * Plugin absolute file path.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_file();
    /**
     * Gets the (hopefully) semantic version of the plugin.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_version();
    /**
     * Plugin slug.
     *
     * @since  1.0.0
     * @return string plugin slug
     */
    public function get_slug();
    /**
     * Get the plugin prefix.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_prefix();
    /**
     * Get the 'basename' for the plugin (e.g. my-plugin/my-plugin.php).
     *
     * @since  1.0.0
     * @return string The plugin basename.
     */
    public function get_basename();
    /**
     * Gets the name of the plugin.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_name();
    /**
     * Get the documentation URI for this plugin.
     *
     * @since  1.0.0
     * @return string (URI)
     */
    public function get_docs_url();
    /**
     * Get the support URI for this plugin.
     *
     * @since  1.0.0
     * @return string (URI)
     */
    public function get_support_url();
    /**
     * Get the review URI for this plugin.
     *
     * @since  1.0.0
     * @return string (URI)
     */
    public function get_review_url();
    /**
     * Get plugin settings url.
     *
     * @since  1.0.0
     * @return string (URI)
     */
    public function get_settings_url();
    /**
     * Get plugin store url.
     *
     * @since 1.0.0
     * @return string (URI)
     */
    public function get_store_url();
    /**
     * Get plugin api url.
     *
     * @since 1.0.0
     * @return string (URI)
     */
    public function get_api_url();
    /**
     * Get premium plugin url.
     *
     * @since 1.0.0
     * @return string (URI)
     */
    public function get_premium_url();
    /**
     * Get premium plugin basename.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_premium_basename();
    /**
     * Has premium plugin.
     *
     * @since 1.0.0
     * @return bool
     */
    public function has_premium();
    /**
     * Is premium plugin active.
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_premium_active();
    /**
     * Gets the plugin text domain.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_text_domain();
    /**
     * Gets the plugin domain path.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_domain_path();
    /**
     * Gets the plugin language directory.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_lang_path();
    /**
     *
     * Get the plugin dir path.
     *
     * @param string $path Optional. Path relative to the plugin dir path.
     *
     * @since 1.0.2
     * @return string
     */
    public function get_dir_path($path = '');
    /**
     * Get the plugin dir url.
     *
     * @param string $path Optional. Path relative to the plugin dir url.
     *
     * @since 1.0.2
     * @return string
     */
    public function get_dir_url($path = '');
    /**
     * Get template path.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_template_path();
    /**
     * Get assets path.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_assets_path();
    /**
     * Get assets url.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_assets_url();
    /**
     * Get meta links.
     *
     * @since 1.0.0
     * @return array
     */
    public function get_meta_links();
    /**
     * Get action links.
     *
     * @since 1.0.0
     * @return array
     */
    public function get_action_links();
    /**
     * Get plugin database version.
     *
     * @since 1.0.0
     * @return string (version)
     */
    public function get_db_version();
    /**
     * Update plugin database version.
     *
     * @param string $version Version.
     *
     * @since 1.0.0
     * @return void
     */
    public function update_db_version($version = null);
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
    public function register_script($handle, $src, $deps = array(), $in_footer = false);
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
    public function register_style($handle, $src, $deps = array(), $media = 'all');
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
    public function enqueue_script($handle, $src, $deps = array(), $in_footer = false);
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
    public function enqueue_style($handle, $src, $deps = array(), $media = 'all');
    /**
     * Check if the plugin is active.
     *
     * @param string $plugin The plugin slug or basename.
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_plugin_active($plugin);
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
    public function log($message, $level = 'debug', $data = array());
}