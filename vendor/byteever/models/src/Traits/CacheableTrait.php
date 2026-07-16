<?php

namespace WooCommerceSerialNumbers\B8\Models\Traits;

defined('ABSPATH') || exit;
/**
 * Cacheable trait.
 *
 * Provides caching functionality for models.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
trait CacheableTrait
{
    /**
     * Caching status flag.
     *
     * @since 1.0.0
     * @var bool
     */
    protected bool $caching_status = true;
    /**
     * Set caching status.
     *
     * @since 1.0.0
     * @param bool $status Caching status.
     * @return void
     */
    public function set_caching_status(bool $status): void
    {
        $this->caching_status = $status;
    }
    /**
     * Get caching status.
     *
     * @since 1.0.0
     * @return bool
     */
    public function get_caching_status(): bool
    {
        return $this->caching_status;
    }
    /**
     * Get the cache group.
     *
     * @since 1.0.0
     * @return string The cache group name.
     */
    public function get_cache_group(): string
    {
        return $this->cache_group;
    }
    /**
     * Set the cache group.
     *
     * @since 1.0.0
     * @param string $group The cache group name.
     * @return void
     */
    public function set_cache_group(string $group): void
    {
        $this->cache_group = $group;
    }
    /**
     * Retrieve cached data using a cache key.
     *
     * @since 1.0.0
     * @param string|int $key The cache key.
     * @return mixed The cached data, or false when not found or caching is disabled.
     */
    public function get_cache($key)
    {
        if (!$this->caching_status) {
            return false;
        }
        return wp_cache_get($key, $this->cache_group);
    }
    /**
     * Store data in cache using a cache key.
     *
     * @since 1.0.0
     * @param string|int $key  The cache key.
     * @param mixed      $data The data to cache.
     * @return bool True on success, false on failure or when caching is disabled.
     */
    public function set_cache($key, $data): bool
    {
        if (!$this->caching_status) {
            return false;
        }
        return wp_cache_set($key, $data, $this->cache_group);
    }
    /**
     * Remove cached data using a cache key.
     *
     * @since 1.0.0
     * @param string|int $key The cache key.
     * @return void
     */
    public function delete_cache($key): void
    {
        if (!$this->caching_status) {
            return;
        }
        wp_cache_delete($key, $this->cache_group);
    }
    /**
     * Clear all cached data and update the last changed timestamp for the cache group.
     *
     * @since 1.0.0
     * @return void
     */
    public function flush_cache(): void
    {
        if (!$this->caching_status) {
            return;
        }
        wp_cache_flush_group($this->cache_group);
        wp_cache_set_last_changed($this->cache_group);
        /**
         * Fires after the cache has been flushed.
         *
         * @since 1.0.0
         *
         * @param static $model Model object.
         */
        $this->do_action('cache_flushed', $this);
    }
}