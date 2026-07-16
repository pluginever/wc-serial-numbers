<?php

namespace WooCommerceSerialNumbers\B8\Models;

use ArrayAccess;
use WooCommerceSerialNumbers\B8\Models\Traits\RelationsTrait;
use WooCommerceSerialNumbers\B8\Models\Traits\AttributesTrait;
use WooCommerceSerialNumbers\B8\Models\Traits\CacheableTrait;
use WooCommerceSerialNumbers\B8\Models\Traits\HookableTrait;
use WooCommerceSerialNumbers\B8\Models\Utilities\StringUtil;
defined('ABSPATH') || exit;
/**
 * Abstract Model class.
 *
 * Provides a foundation for database model implementations.
 *
 * @since 1.0.0
 * @package \B8\Models
 *
 * @implements ArrayAccess<string, mixed>
 */
abstract class Model implements ArrayAccess
{
    use AttributesTrait;
    use CacheableTrait;
    use HookableTrait;
    use RelationsTrait;
    /**
     * The table associated with the model.
     *
     * @example 'accounts', 'transactions'
     *
     * @since 1.0.0
     * @var string
     */
    protected $table;
    /**
     * The primary key for the model.
     *
     * @example 'id', 'account_id'
     *
     * @since 1.0.0
     * @var string
     */
    protected $primary_key = 'id';
    /**
     * The table columns of the model.
     *
     * @example ['id', 'name', 'email', 'created_at']
     *
     * @since 1.0.0
     * @var array<int, string>
     */
    protected $columns = array();
    /**
     * Default query variables passed to the Query class.
     *
     * @example ['orderby' => 'created_at', 'order' => 'DESC']
     * @example ['status' => 'active', 'per_page' => 25]
     *
     * @since 1.0.0
     * @var array<string, mixed>
     */
    protected $query_vars = array();
    /**
     * The searchable attributes.
     *
     * @example ['name', 'email', 'description']
     *
     * @since 1.0.0
     * @var array<int, string>
     */
    protected $searchable = array();
    /**
     * Attributes that have transition effects when changed.
     *
     * @example ['status', 'stage']
     *
     * @since 1.0.0
     * @var array<int, string>
     */
    protected $transitionable = array();
    /**
     * Object type for hooks.
     *
     * @since 1.0.0
     * @var string
     */
    protected $object_type;
    /**
     * Meta type declaration for the object.
     *
     * @example 'account', 'transaction' or false
     *
     * @since 1.0.0
     * @var string|false
     */
    protected $meta_type = false;
    /**
     * The cache group for the object type.
     *
     * @since 1.0.0
     * @var string
     */
    protected $cache_group;
    /**
     * Hook prefix for the model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $hook_prefix;
    /**
     * The name of the "created at" column.
     *
     * @since 1.0.0
     * @var string
     */
    const CREATED_AT = 'created_at';
    /**
     * The name of the "updated at" column.
     *
     * @since 1.0.0
     * @var string
     */
    const UPDATED_AT = 'updated_at';
    /**
     * The name of the "created_by" column.
     *
     * @since 1.0.0
     * @var string|false
     */
    const CREATED_BY = false;
    /**
     * Static instances cache for performance.
     *
     * @example ['Account' => [...], 'Transaction' => [...]]
     *
     * @since 1.0.0
     * @var array<string, array<string, mixed>>
     */
    private static $instances = array();
    /**
     * Find an object by column or create a new instance.
     *
     * @since 1.0.0
     *
     * @param mixed                                $attributes Attributes to set.
     * @param array<int|string, mixed>|string|null $search     Search column name or additional values.
     *
     * @return static The model instance.
     *
     * @example make(5) → Search by id=5, create empty if not found
     * @example make(['id' => 5, 'name' => 'John']) → Search by id, create with name if not found
     * @example make('user@mail.com', 'email') → Search by email, create with email if not found
     * @example make(['name' => 'John'], 'email,phone') → Search by email+phone, create if not found
     * @example make(['phone' => '123'], ['email' => 'x']) → Search by email, merge both if not found
     */
    public static function make($attributes = null, $search = null)
    {
        $model = new static();
        $primary_key = $model->get_primary_key();
        if ($attributes instanceof static && $attributes->exists()) {
            $attributes = $attributes->get_attributes();
        } elseif (is_object($attributes)) {
            $attributes = get_object_vars($attributes);
        } elseif (is_scalar($attributes)) {
            $column = is_string($search) ? trim($search) : $primary_key;
            $attributes = array($column => $attributes);
        } else {
            $attributes = is_array($attributes) ? $attributes : array();
        }
        if (is_array($search)) {
            $is_assoc = !wp_is_numeric_array($search);
            $where = $is_assoc ? $search : wp_array_slice_assoc($attributes, $search);
            $attributes = $is_assoc ? array_merge($attributes, $search) : $attributes;
        } elseif (is_string($search) && !empty($search)) {
            $where = wp_array_slice_assoc($attributes, wp_parse_list($search));
        } elseif (!empty($attributes[$primary_key])) {
            $where = array($primary_key => $attributes[$primary_key]);
        } else {
            $where = array();
        }
        $where = array_filter($where, function ($v) {
            return !is_null($v);
        });
        if (!empty($where)) {
            $found = static::find($where);
            if ($found) {
                return $found->fill($attributes);
            }
            if (array_key_exists($primary_key, $where)) {
                unset($attributes[$primary_key]);
            }
        }
        return new static($attributes);
    }
    /**
     * Find an object by its primary key or query.
     *
     * @since 1.0.0
     *
     * @param mixed $id The ID or query to search by.
     *
     * @return static|null The model instance, or null if not found.
     */
    public static function find($id)
    {
        return static::query()->find($id);
    }
    /**
     * Create or update an object.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed>|static          $data     Item data.
     * @param array<int|string, mixed>|string|null $search   Search column name or additional values.
     * @param bool                                 $wp_error Whether to return a WP_Error on failure.
     *
     * @return static|false|\WP_Error The model instance on success, false on failure.
     */
    public static function insert($data, $search = null, $wp_error = true)
    {
        $item = static::make($data, $search);
        $result = $item->save();
        if (is_wp_error($result)) {
            return $wp_error ? $result : false;
        }
        return $result;
    }
    /**
     * Query the database for items or count.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed>|null $args The query arguments.
     *
     * @return ($args is null ? Query : array<int, mixed>|int) Query object, or the items/count when args are given.
     */
    public static function query($args = null)
    {
        return (new static())->new_query($args);
    }
    /**
     * Get the total number of items in the database.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed>|null $args The query arguments.
     *
     * @return int
     */
    public static function count($args = null)
    {
        $args = wp_parse_args(array('count' => true), (array) $args);
        return (int) static::query($args);
    }
    /**
     * Create a new model instance.
     *
     * @param mixed $attributes The attributes to fill the model with.
     */
    public function __construct($attributes = array())
    {
        $this->initialize();
        $this->fill($attributes);
        $this->sync_original();
    }
    /**
     * Destroy the object.
     *
     * @since 1.0.0
     */
    public function __destruct()
    {
        if (is_array($this->attributes)) {
            $this->attributes = array_fill_keys(array_keys($this->attributes), null);
        }
    }
    /**
     * Only store the object primary key to avoid serializing the data object instance.
     *
     * @since 1.0.0
     * @return array
     */
    public function __sleep()
    {
        return array('attributes');
    }
    /**
     * Re-run the constructor with the object primary key.
     *
     * If the object no longer exists, remove the ID.
     *
     * @since 1.0.0
     * @return void
     */
    public function __wakeup()
    {
        try {
            $this->__construct($this->get_key_value());
        } catch (\Exception $e) {
            $this->set($this->primary_key, null);
        }
    }
    /**
     * When the object is cloned, make sure meta is duplicated correctly.
     *
     * @since 1.0.0
     * @return $this
     */
    public function __clone()
    {
        $this->set($this->get_primary_key(), null);
        $this->exists = false;
        $this->original = $this->attributes;
        return $this;
    }
    /**
     * Magic method to get the value of a property.
     *
     * @since 1.0.0
     *
     * @param string $key The name of the property.
     *
     * @return mixed
     */
    public function &__get(string $key)
    {
        $value = $this->get($key);
        return $value;
    }
    /**
     * Magic method to set the value of an attribute.
     *
     * @since 1.0.0
     *
     * @param string $key The name of the attribute.
     * @param mixed  $value The value of the attribute.
     *
     * @return void
     */
    public function __set(string $key, $value)
    {
        $this->set($key, $value);
    }
    /**
     * Magic method to check if an attribute is set.
     *
     * @since 1.0.0
     *
     * @param string $key The name of the attribute.
     *
     * @return bool
     */
    public function __isset(string $key)
    {
        return !is_null($this->get($key));
    }
    /**
     * Magic method to unset an attribute.
     *
     * @since 1.0.0
     *
     * @param string $key The name of the attribute.
     *
     * @return void
     */
    public function __unset(string $key)
    {
        $this->set($key, null);
    }
    /**
     * Initialize the model instance.
     *
     * Manages caching and property assignment for model instances.
     * Checks for cached instances and calls bootstrap if needed.
     *
     * @since 1.0.0
     * @return void
     */
    protected function initialize()
    {
        $class = static::class;
        $exclude = array('original', 'relations');
        if (!isset(self::$instances[$class])) {
            $original = get_object_vars($this);
            $this->bootstrap();
            /**
             * Fires when bootstrapping a model for meta key registration.
             *
             * Allows external code to register meta keys for the model.
             *
             * @since 1.0.0
             *
             * @param static $model The model instance.
             */
            $this->do_action('init', $this);
            foreach (get_object_vars($this) as $prop => $value) {
                if (!in_array($prop, $exclude, true) && (!array_key_exists($prop, $original) || $original[$prop] !== $value)) {
                    self::$instances[$class][$prop] = $value;
                }
            }
        }
        if (isset(self::$instances[$class])) {
            foreach (self::$instances[$class] as $prop => $value) {
                if (property_exists($this, $prop) && !empty($prop)) {
                    $this->{$prop} = $value;
                }
            }
        }
        $this->attributes = array_merge(array_fill_keys($this->columns, null), $this->attributes);
    }
    /**
     * Bootstrap the model's attributes and settings.
     *
     * @return void
     */
    protected function bootstrap()
    {
        global $wpdb;
        $class_name = basename(str_replace('\\', '/', static::class));
        if (empty($this->object_type)) {
            $this->object_type = strtolower($class_name);
        }
        if (empty($this->cache_group)) {
            $this->cache_group = StringUtil::pluralize($this->object_type);
        }
        if (empty($this->hook_prefix)) {
            $this->hook_prefix = strtolower($class_name);
        }
        if (empty($this->table)) {
            $this->table = StringUtil::snake(StringUtil::pluralize($class_name));
        }
        $this->table = sanitize_text_field($this->table);
        if (empty($this->columns)) {
            $this->columns = wp_list_pluck(
                $wpdb->get_results("DESCRIBE {$wpdb->prefix}{$this->table}"),
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is from model property.
                'Field'
            );
        }
        if (false !== $this->meta_type) {
            $this->meta_type = StringUtil::singularize($this->table);
        }
        // If meta type is set and meta table doesn't exist, add it to wpdb.
        if (false !== $this->meta_type && !_get_meta_table($this->meta_type)) {
            $meta_table = $this->meta_type . 'meta';
            $wpdb->tables[] = $meta_table;
            $wpdb->{$meta_table} = $wpdb->prefix . $meta_table;
            // get registered meta keys for this meta type.
            foreach (get_registered_meta_keys($this->object_type) as $meta_key => $meta) {
                $this->metadata[$meta_key] = $meta['default'] ?? null;
            }
        }
        // Ensure primary key is in columns.
        if (!in_array($this->primary_key, $this->columns, true)) {
            $this->columns[] = $this->primary_key;
        }
        if (!isset($this->casts[$this->primary_key])) {
            $this->casts[$this->primary_key] = 'int';
        }
        // Set created_by column if the model tracks the creator.
        if (static::CREATED_BY) {
            if (!in_array(static::CREATED_BY, $this->columns, true)) {
                $this->columns[] = static::CREATED_BY;
            }
            if (!isset($this->casts[static::CREATED_BY])) {
                $this->casts[static::CREATED_BY] = 'int';
            }
        }
        // Set timestamp columns if the model uses timestamps.
        if ($this->timestamps) {
            if (!in_array(static::CREATED_AT, $this->columns, true)) {
                $this->columns[] = static::CREATED_AT;
            }
            if (!in_array(static::UPDATED_AT, $this->columns, true)) {
                $this->columns[] = static::UPDATED_AT;
            }
            if (!isset($this->casts[static::CREATED_AT])) {
                $this->casts[static::CREATED_AT] = 'datetime';
            }
            if (!isset($this->casts[static::UPDATED_AT])) {
                $this->casts[static::UPDATED_AT] = 'datetime';
            }
        }
    }
    /**
     * Get an attribute, relation, or metadata value.
     *
     * @since 1.0.0
     *
     * @param string $key The name of the attribute, relation, or metadata key.
     * @param mixed  $fallback Optional fallback value if the key is not found.
     *
     * @return mixed|void The value of the attribute, or the fallback if not found.
     */
    public function get(string $key, $fallback = null)
    {
        $getter = 'get_' . $key . '_attribute';
        if (method_exists($this, $getter)) {
            // might have aliased getters.
            return $this->{$getter}();
        }
        $key = $this->resolve_alias($key);
        $getter = 'get_' . $key . '_attribute';
        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        if (array_key_exists($key, $this->metadata)) {
            return $this->metadata[$key];
        }
        // Here we will determine if the model base class itself contains this given key
        // since we don't want to treat any of those methods as relationships because
        // they are all intended as helper methods and none of these are relations.
        if (method_exists(self::class, $key)) {
            return;
        }
        if (!$this->has_relation($key)) {
            return $fallback;
        }
        return $this->get_relation_value($key);
    }
    /**
     * Set an attribute, or metadata value.
     *
     * @since 1.0.0
     *
     * @param string $key The name of the attribute or metadata key.
     * @param mixed  $value The value to set.
     *
     * @return static
     */
    public function set(string $key, $value)
    {
        if (empty($key)) {
            return $this;
        }
        $setter = 'set_' . $key . '_attribute';
        if (method_exists($this, $setter)) {
            // might have aliased setters.
            $this->{$setter}($value);
            return $this;
        }
        $key = $this->resolve_alias($key);
        $setter = 'set_' . $key . '_attribute';
        if (method_exists($this, $setter)) {
            $this->{$setter}($value);
            return $this;
        }
        $value = $this->cast($key, $value);
        if (array_key_exists($key, $this->attributes)) {
            $this->attributes[$key] = $value;
            return $this;
        }
        if (array_key_exists($key, $this->metadata)) {
            $this->metadata[$key] = $value;
            return $this;
        }
        // Here we will determine if the model base class itself contains this given key
        // since we don't want to treat any of those methods as relationships because
        // they are all intended as helper methods and none of these are relations.
        if (method_exists(self::class, $key)) {
            return $this;
        }
        $this->attributes[$key] = $value;
        return $this;
    }
    /**
     * Fill the model with an array of attributes.
     *
     * @param mixed $attributes The attributes to fill the model with.
     *
     * @return static
     */
    public function fill($attributes)
    {
        if (is_object($attributes)) {
            $attributes = get_object_vars($attributes);
        }
        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $this->set($key, $value);
            }
        }
        return $this;
    }
    /**
     * Hydrate the model with attributes without triggering mutators.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed> $attributes The attributes to set.
     * @param bool                 $sync       Whether to sync original attributes. Default false.
     *
     * @return static
     */
    public function hydrate(array $attributes, bool $sync = false): self
    {
        foreach ($attributes as $key => $value) {
            $key = $this->resolve_alias($key);
            $value = $this->cast($key, $value);
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            } elseif (array_key_exists($key, $this->metadata)) {
                $this->metadata[$key] = $value;
            }
        }
        if ($sync) {
            $this->sync_original();
            $this->exists = !empty($this->get_key_value());
        }
        return $this;
    }
    /**
     * Convert the model instance to an array.
     *
     * @since 1.0.0
     * @return array<string, mixed>
     */
    public function to_array()
    {
        $attributes = $this->get_attributes();
        $appends = $this->get_appends();
        $hidden = $this->get_hidden();
        $relations = $this->get_relations();
        $data = array_diff_key($attributes, array_flip($hidden));
        $data = $this->to_recursive_array($data);
        foreach ($appends as $key) {
            $value = $this->get($key);
            $data[$key] = $this->to_recursive_array($value);
        }
        foreach ($relations as $key => $relation) {
            $rel_value = $this->get_relation($key);
            $data[$key] = $this->to_recursive_array($rel_value);
        }
        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | CRUD Methods
    |--------------------------------------------------------------------------
    | This section contains methods for creating, reading, updating, and deleting
    | objects in the database.
    |--------------------------------------------------------------------------
    */
    /**
     * Create an item in the database.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed> $data Data to be inserted.
     *
     * @return \WP_Error|int The ID of the inserted item, or WP_Error on failure.
     */
    protected function perform_create($data)
    {
        global $wpdb;
        $data = wp_unslash($data);
        // maybe_serialize( null ) would store the literal string 'N;' instead of SQL NULL.
        $data = array_map(static fn($value) => is_null($value) ? null : maybe_serialize($value), $data);
        if (false === $wpdb->insert($wpdb->prefix . $this->table, $data)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is from model property.
            return new \WP_Error('db_insert_error', sprintf('Could not insert item into the database error %s', esc_html($wpdb->last_error)));
        }
        return $wpdb->insert_id;
    }
    /**
     * Update an item in the database.
     *
     * @since 1.0.0
     *
     * @param int                  $id   ID of the item to be updated.
     * @param array<string, mixed> $data Data to be updated.
     *
     * @return \WP_Error|bool True on success, or WP_Error on failure.
     * @global \wpdb $wpdb WordPress database abstraction object.
     */
    protected function perform_update($id, $data)
    {
        global $wpdb;
        $data = wp_unslash($data);
        // maybe_serialize( null ) would store the literal string 'N;' instead of SQL NULL.
        $data = array_map(static fn($value) => is_null($value) ? null : maybe_serialize($value), $data);
        if (false === $wpdb->update($wpdb->prefix . $this->table, $data, array($this->primary_key => $id))) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is from model property.
            return new \WP_Error('db_update_error', sprintf('Could not update item in the database error %s', esc_html($wpdb->last_error)));
        }
        return true;
    }
    /**
     * Delete an item from the database.
     *
     * @since 1.0.0
     *
     * @param int $id ID of the item to be deleted.
     *
     * @return \WP_Error|bool True on success, or WP_Error on failure.
     * @global \wpdb $wpdb WordPress database abstraction object.
     */
    protected function perform_delete($id)
    {
        global $wpdb;
        if (false === $wpdb->delete($wpdb->prefix . $this->table, array($this->primary_key => $id))) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is from model property.
            return new \WP_Error('db_delete_error', sprintf('Could not delete item from the database error %s', esc_html($wpdb->last_error)));
        }
        return true;
    }
    /**
     * Save the object to the database.
     *
     * @since 1.0.0
     * @return \WP_Error|static WP_Error on failure, or the object on success.
     */
    public function save()
    {
        /**
         * Filters the validation result before saving.
         *
         * Return a WP_Error to prevent saving. Return null to allow saving.
         *
         * @since 1.0.0
         *
         * @param \WP_Error|null $result The validation result.
         * @param static         $model  Model object.
         */
        $valid = $this->apply_filters('validate_save', $this->validate(), $this);
        if (null !== $valid) {
            return $valid;
        }
        $this->set_author();
        $this->set_timestamps();
        /**
         * Fires before saving an item to the database.
         *
         * @since 1.0.0
         *
         * @param static $model Model object.
         */
        $this->do_action('pre_save', $this);
        foreach ($this->get_transitionable() as $transitionable) {
            $old_value = array_key_exists($transitionable, $this->original) ? $this->original[$transitionable] : null;
            $new_value = $this->get($transitionable);
            if ($old_value !== $new_value) {
                /**
                 * Fires before a transitionable property changes.
                 *
                 * The dynamic portion of the hook name, `$transitionable`, refers to the property name.
                 *
                 * @since 1.0.0
                 *
                 * @param static $model     Model object.
                 * @param mixed  $new_value New value.
                 * @param mixed  $old_value Old value.
                 */
                $this->do_action("pre_{$transitionable}_transition", $this, $new_value, $old_value);
                /**
                 * Fires before a transitionable property changes to a specific value.
                 *
                 * The dynamic portions of the hook name, `$transitionable` and `$new_value`,
                 * refer to the property name and its new value.
                 *
                 * @since 1.0.0
                 *
                 * @param static $model     Model object.
                 * @param mixed  $old_value Old value.
                 */
                $this->do_action("pre_{$transitionable}_{$new_value}", $this, $old_value);
                // Only fire the from-to hook when both values are meaningful.
                if (null !== $old_value && '' !== (string) $old_value) {
                    /**
                     * Fires before a specific transition between two values.
                     *
                     * The dynamic portions of the hook name, `$transitionable`, `$old_value`,
                     * and `$new_value`, refer to the property name and its old/new values.
                     *
                     * @since 1.0.0
                     *
                     * @param static $model Model object.
                     */
                    $this->do_action("pre_{$transitionable}_{$old_value}_to_{$new_value}", $this);
                }
            }
        }
        $data = array();
        if (!$this->exists()) {
            $data = wp_array_slice_assoc($this->get_db_attributes(), $this->get_columns());
            foreach ($data as $key => $value) {
                /**
                 * Filters a single field value before saving to the database.
                 *
                 * The dynamic portion of the hook name, `$key`, refers to the column name.
                 *
                 * @since 1.0.0
                 *
                 * @param mixed  $value The field value.
                 * @param static $model Model object.
                 */
                $data[$key] = $this->apply_filters("pre_{$key}", $value, $this);
            }
            /**
             * Fires before an item is inserted in the database.
             *
             * @since 1.0.0
             *
             * @param static $model Model object.
             * @param array  $data  Data to be inserted.
             */
            $this->do_action('pre_insert', $this, $data);
            /**
             * Filters all data before inserting into the database.
             *
             * @since 1.0.0
             *
             * @param array  $data  Data to be inserted.
             * @param static $model Model object.
             */
            $data = $this->apply_filters('pre_insert_data', $data, $this);
            $insert_id = $this->perform_create($data);
            if (is_wp_error($insert_id)) {
                return $insert_id;
            }
            $this->set($this->primary_key, $insert_id);
            $this->exists = true;
            $data[$this->primary_key] = $insert_id;
        } elseif (!empty(array_intersect_key($this->get_changes(), array_flip($this->get_columns())))) {
            $updates = array_intersect_key($this->get_db_attributes(), $this->get_changes());
            foreach ($updates as $key => $value) {
                /**
                 * Filters a single field value before saving to the database.
                 *
                 * The dynamic portion of the hook name, `$key`, refers to the column name.
                 *
                 * @since 1.0.0
                 *
                 * @param mixed  $value The field value.
                 * @param static $model Model object.
                 */
                $updates[$key] = $this->apply_filters("pre_{$key}", $value, $this);
            }
            /**
             * Fires before an item is updated in the database.
             *
             * @since 1.0.0
             *
             * @param static $model   Model object.
             * @param array  $updates Changed data to be updated.
             */
            $this->do_action('pre_update', $this, $updates);
            /**
             * Filters all changed data before updating in the database.
             *
             * @since 1.0.0
             *
             * @param array  $updates Changed data to be updated.
             * @param static $model   Model object.
             */
            $updates = $this->apply_filters('pre_update_data', $updates, $this);
            $data = array_intersect_key($updates, array_flip($this->get_columns()));
            // using wp_array_slice_assoc will ignore null values.
            unset($data[$this->primary_key]);
            $return = $this->perform_update($this->get_key_value(), $data);
            if (is_wp_error($return)) {
                return $return;
            }
        }
        // Complete all DB writes and invalidate stale cache.
        $this->save_metadata();
        $this->flush_cache();
        // Post-write hooks: fire after cache is fresh so listeners get current data.
        if (isset($insert_id)) {
            /**
             * Fires after an item is inserted in the database.
             *
             * @since 1.0.0
             *
             * @param static $model Model object.
             * @param array  $data  Data inserted.
             */
            $this->do_action('inserted', $this, $data);
        } elseif (isset($updates)) {
            /**
             * Fires after an item is updated in the database.
             *
             * @since 1.0.0
             *
             * @param static $model   Model object.
             * @param array  $updates Data updated.
             */
            $this->do_action('updated', $this, $updates);
        }
        // Transition effects.
        foreach ($this->get_transitionable() as $transitionable) {
            $old_value = array_key_exists($transitionable, $this->original) ? $this->original[$transitionable] : null;
            $new_value = $this->get($transitionable);
            if ($this->is_dirty($transitionable)) {
                /**
                 * Fires after a transitionable property has changed.
                 *
                 * The dynamic portion of the hook name, `$transitionable`, refers to the property name.
                 *
                 * @since 1.0.0
                 *
                 * @param static $model     Model object.
                 * @param mixed  $new_value New value.
                 * @param mixed  $old_value Old value.
                 */
                $this->do_action("{$transitionable}_transition", $this, $new_value, $old_value);
                /**
                 * Fires after a transitionable property has changed to a specific value.
                 *
                 * The dynamic portions of the hook name, `$transitionable` and `$new_value`,
                 * refer to the property name and its new value.
                 *
                 * @since 1.0.0
                 *
                 * @param static $model     Model object.
                 * @param mixed  $old_value Old value.
                 */
                $this->do_action("{$transitionable}_{$new_value}", $this, $old_value);
                // Only fire the from-to hook when both values are meaningful.
                if (null !== $old_value && '' !== (string) $old_value) {
                    /**
                     * Fires after a specific transition between two values.
                     *
                     * The dynamic portions of the hook name, `$transitionable`, `$old_value`,
                     * and `$new_value`, refer to the property name and its old/new values.
                     *
                     * @since 1.0.0
                     *
                     * @param static $model Model object.
                     */
                    $this->do_action("{$transitionable}_{$old_value}_to_{$new_value}", $this);
                }
            }
        }
        // Capture the persisted changes before syncing so the model reports a clean state to listeners.
        $changes = $this->get_changes();
        $this->sync_original();
        /**
         * Fires after an item is saved.
         *
         * @since 1.0.0
         *
         * @param static               $model   Model object.
         * @param array<string, mixed> $changes Attribute changes persisted by this save.
         */
        $this->do_action('saved', $this, $changes);
        // Save dirty loaded relations then clear the cache so next access re-queries.
        foreach ($this->relations as $rel_name => $rel_value) {
            if (!method_exists($this, $rel_name)) {
                continue;
            }
            $children = is_array($rel_value) ? $rel_value : array($rel_value);
            $relation = $this->{$rel_name}();
            foreach ($children as $child) {
                if ($child instanceof self && ($child->is_dirty() || !$child->exists())) {
                    $relation->save($child);
                }
            }
            $this->unset_relation($rel_name);
        }
        return $this;
    }
    /**
     * Delete the object from the database.
     *
     * @since 1.0.0
     * @return true|\WP_Error True on success, WP_Error on failure.
     */
    public function delete()
    {
        if (!$this->exists()) {
            return new \WP_Error('invalid_object', 'Cannot delete an object that does not exist.');
        }
        /**
         * Filters the validation result before deleting.
         *
         * Return a WP_Error to prevent deletion. Return null to allow deletion.
         *
         * @since 1.0.0
         *
         * @param \WP_Error|null $result The validation result.
         * @param static         $model  Model object.
         */
        $valid = $this->apply_filters('validate_delete', null, $this);
        if (null !== $valid) {
            return $valid;
        }
        $data = $this->to_array();
        /**
         * Fires immediately before an item is deleted from the database.
         *
         * @since 1.0.0
         *
         * @param static $model Model object.
         * @param array  $data Model data array.
         */
        $this->do_action('pre_delete', $this, $data);
        $return = $this->perform_delete($this->get_key_value());
        if (is_wp_error($return)) {
            return $return;
        }
        // Finish all DB cleanup and flush cache before firing hooks.
        $this->delete_metadata();
        $this->flush_cache();
        /**
         * Fires after an item is deleted from the database.
         *
         * @since 1.0.0
         *
         * @param static $model Model object.
         * @param array  $data Model data array.
         */
        $this->do_action('deleted', $this, $data);
        $this->exists = false;
        return true;
    }
    /**
     * Loads the metadata.
     *
     * @since 1.0.0
     * @return static
     */
    public function load_metadata()
    {
        if ($this->get_meta_type() && $this->exists()) {
            $raw_meta = get_metadata((string) $this->meta_type, $this->get_key_value());
            $metadata = array();
            foreach ($raw_meta as $key => $value) {
                $value = is_array($value) ? $value[0] : $value;
                $value = maybe_unserialize($value);
                $metadata[$key] = $value;
            }
            /**
             * Filters all metadata after reading from the database.
             *
             * @since 1.0.0
             *
             * @param array  $metadata Array of metadata for the given object.
             * @param static $model    Model object.
             */
            $metadata = $this->apply_filters('get_metadata', $metadata, $this);
            foreach ($metadata as $key => $value) {
                $value = $this->cast($key, $value);
                $this->metadata[$key] = $value;
            }
        }
        return $this;
    }
    /**
     * Saves the metadata.
     *
     * @since 1.0.0
     * @return $this
     */
    public function save_metadata()
    {
        if ($this->get_meta_type() && $this->exists()) {
            $metadata = array_intersect_key($this->get_db_attributes(), array_flip(array_keys($this->metadata)));
            foreach ($metadata as $key => $value) {
                if (is_null($value)) {
                    delete_metadata($this->get_meta_type(), $this->get_key_value(), $key);
                } else {
                    update_metadata($this->get_meta_type(), $this->get_key_value(), $key, $value);
                }
            }
        }
        return $this;
    }
    /**
     * Deletes the metadata.
     *
     * @since 1.0.0
     * @return static
     */
    public function delete_metadata()
    {
        global $wpdb;
        if ($this->get_meta_type() && $this->exists()) {
            $meta_table = $this->get_meta_type() . 'meta';
            $field_name = $this->get_meta_type() . '_id';
            $wpdb->delete($wpdb->prefix . $meta_table, array($field_name => $this->get_key_value()));
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Meta table name is from model property.
            $this->metadata = array();
            wp_cache_delete($this->get_key_value(), $this->get_meta_type() . '_meta');
        }
        return $this;
    }
    /**
     * Determine if the object exists in the database.
     *
     * @since 1.0.0
     * @return bool
     */
    public function exists(): bool
    {
        return $this->exists;
    }
    /**
     * Reload the object from the database.
     *
     * @since 1.0.0
     * @return static|null The model instance, or null if it no longer exists.
     */
    public function reload()
    {
        if (!$this->exists()) {
            return $this;
        }
        // unset cached data.
        wp_cache_delete($this->get_key_value(), $this->get_cache_group());
        return static::find($this->get_key_value());
    }
    /**
     * Get the query object for the model.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed>|null $args Optional. The query arguments.
     *
     * @return ($args is null ? Query : array<int, mixed>|int) Query object, or the items/count when args are given.
     */
    public function new_query($args = null)
    {
        $query = new Query($this);
        if (!is_null($args)) {
            return $query->set_var($args)->get();
        }
        return $query;
    }
    /*
    |--------------------------------------------------------------------------
    | Getter and Setter Methods
    |--------------------------------------------------------------------------
    | This section contains methods for getting and setting model properties.
    |--------------------------------------------------------------------------
    */
    /**
     * Get the table associated with the model.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_table()
    {
        return $this->table;
    }
    /**
     * Get the primary key for the model.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_primary_key()
    {
        return $this->primary_key;
    }
    /**
     * Get the object type for the model.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_object_type()
    {
        return $this->object_type;
    }
    /**
     * Get the value of the primary key.
     *
     * @since 1.0.0
     * @return mixed
     */
    public function get_key_value()
    {
        return $this->get($this->primary_key);
    }
    /**
     * Get the table columns of the model.
     *
     * @since 1.0.0
     * @return array<int, string>
     */
    public function get_columns()
    {
        return $this->columns;
    }
    /**
     * Get default query variables passed to Query.
     *
     * @since 1.0.0
     * @return array<string, mixed>
     */
    public function get_query_vars()
    {
        return $this->query_vars;
    }
    /**
     * Set default query variables passed to Query.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed> $vars    The query variables to set.
     * @param bool                 $replace Whether to replace existing query vars or merge. Default false.
     *
     * @return $this
     */
    public function set_query_vars($vars, $replace = false)
    {
        $this->query_vars = $replace ? $vars : array_merge($this->query_vars, $vars);
        return $this;
    }
    /**
     * Get the searchable properties.
     *
     * @since 1.0.0
     * @return array<int, string>
     */
    public function get_searchable()
    {
        return $this->searchable;
    }
    /**
     * Set the searchable properties.
     *
     * @since 1.0.0
     *
     * @param array<int, string> $attrs   The properties to set as searchable.
     * @param bool               $replace Whether to replace existing searchable properties or merge. Default false (merge).
     *
     * @return $this
     */
    public function set_searchable($attrs, $replace = false)
    {
        $this->searchable = $replace ? $attrs : array_merge($this->searchable, $attrs);
        return $this;
    }
    /**
     * Get the transitionable properties.
     *
     * @since 1.0.0
     * @return array<int, string>
     */
    public function get_transitionable()
    {
        return $this->transitionable;
    }
    /**
     * Set the transitionable properties.
     *
     * @since 1.0.0
     *
     * @param array<int, string> $attrs   The properties to set as transitionable.
     * @param bool               $replace Whether to replace existing transitionable properties or merge. Default false (merge).
     *
     * @return $this
     */
    public function set_transitionable($attrs, $replace = false)
    {
        $this->transitionable = $replace ? $attrs : array_merge($this->transitionable, $attrs);
        return $this;
    }
    /**
     * Get metadata type.
     *
     * @since 1.0.0
     * @return string|false
     */
    public function get_meta_type()
    {
        return $this->meta_type;
    }
    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    | This section contains all helper methods that support the model's functionality.
    |--------------------------------------------------------------------------
    */
    /**
     * Get a new instance of the model.
     *
     * @since 1.0.0
     *
     * @param mixed $attributes The model attributes.
     *
     * @return static
     */
    public function new_instance($attributes = null)
    {
        return new static($attributes);
    }
    /**
     * Get the value at a given offset.
     *
     * @since 1.0.0
     *
     * @param string $offset The key to get.
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get((string) $offset);
    }
    /**
     * Set the value at a given offset.
     *
     * @since 1.0.0
     *
     * @param string $offset The key to set.
     * @param mixed  $value  The value to set.
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        $this->set((string) $offset, $value);
    }
    /**
     * Remove the value at a given offset.
     *
     * @since 1.0.0
     *
     * @param string $offset The key to remove.
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        $this->set((string) $offset, null);
    }
    /**
     * Whether an offset exists.
     *
     * @since 1.0.0
     *
     * @param mixed $offset The key to check.
     *
     * @return bool True if the offset exists, false otherwise.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return $this->offsetGet($offset) !== null;
    }
}