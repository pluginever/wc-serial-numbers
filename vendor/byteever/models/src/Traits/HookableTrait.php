<?php

namespace WooCommerceSerialNumbers\B8\Models\Traits;

use WooCommerceSerialNumbers\B8\Models\Utilities\StringUtil;
defined('ABSPATH') || exit;
/**
 * Hookable trait.
 *
 * Provides WordPress hook integration for models.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
trait HookableTrait
{
    /**
     * Hook status flag.
     *
     * @since 1.0.0
     * @var bool
     */
    protected bool $hook_status = true;
    /**
     * Set hook status.
     *
     * @since 1.0.0
     * @param bool $status Hook status.
     * @return void
     */
    public function set_hook_status(bool $status): void
    {
        $this->hook_status = $status;
    }
    /**
     * Get the hook prefix used for this model.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_hook_prefix(): string
    {
        return $this->hook_prefix;
    }
    /**
     * Get the full hook name with prefix and object type.
     *
     * @since 1.0.0
     * @param string $hook The hook name suffix.
     * @return string The full hook name, in the form {prefix}_{object_type}_{hook}.
     */
    public function get_hook_name(string $hook): string
    {
        return StringUtil::join(array($this->hook_prefix, $this->object_type, $hook), '_');
    }
    /**
     * Trigger an action with the automatic hook prefix.
     *
     * @since 1.0.0
     * @param string $hook    The hook name.
     * @param mixed  ...$args Arguments to pass.
     * @return void
     */
    public function do_action(string $hook, ...$args): void
    {
        if (!$this->hook_status || empty($this->hook_prefix) || empty($hook)) {
            return;
        }
        do_action($this->get_hook_name($hook), ...$args);
    }
    /**
     * Apply filters with the automatic hook prefix.
     *
     * @since 1.0.0
     * @param string $hook    The filter name.
     * @param mixed  $value   The value to filter.
     * @param mixed  ...$args Additional arguments.
     * @return mixed The filtered value.
     */
    public function apply_filters(string $hook, $value, ...$args)
    {
        if (!$this->hook_status || empty($this->hook_prefix) || empty($hook)) {
            return $value;
        }
        return apply_filters($this->get_hook_name($hook), $value, ...$args);
    }
    /**
     * Add a callback to a prefixed action hook.
     *
     * @since 1.0.0
     * @param string   $hook          The hook name (without prefix).
     * @param callable $callback      The callback to execute.
     * @param int      $priority      Hook priority. Default 10.
     * @param int      $accepted_args Number of arguments. Default 1.
     * @return bool
     */
    public function on_action(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): bool
    {
        return add_action($this->get_hook_name($hook), $callback, $priority, $accepted_args);
    }
    /**
     * Add a callback to a prefixed filter hook.
     *
     * @since 1.0.0
     * @param string   $hook          The hook name (without prefix).
     * @param callable $callback      The callback to execute.
     * @param int      $priority      Hook priority. Default 10.
     * @param int      $accepted_args Number of arguments. Default 1.
     * @return bool
     */
    public function on_filter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): bool
    {
        return add_filter($this->get_hook_name($hook), $callback, $priority, $accepted_args);
    }
}