<?php

namespace WooCommerceSerialNumbers\B8\Models\Traits;

use WooCommerceSerialNumbers\B8\Models\Utilities\DateUtil;
use WooCommerceSerialNumbers\B8\Models\Utilities\DateTime;
use WooCommerceSerialNumbers\B8\Models\Utilities\StringUtil;
defined('ABSPATH') || exit;
/**
 * Attributes trait.
 *
 * Provides attribute management functionality for models.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
trait AttributesTrait
{
    /**
     * The model's attributes.
     *
     * @since 1.0.0
     * @var array<string, mixed>
     */
    protected $attributes = array();
    /**
     * Metadata for the object.
     *
     * @since 1.0.0
     * @var array<string, mixed>
     */
    protected $metadata = array();
    /**
     * The original attribute values.
     *
     * @since 1.0.0
     * @var array<string, mixed>
     */
    protected $original = array();
    /**
     * The attributes that should be cast.
     *
     * @example ['is_active' => 'boolean', 'created_at' => 'datetime']
     * @example ['amount' => 'decimal:2', 'status' => 'string']
     *
     * @since 1.0.0
     * @var array<string, string>
     */
    protected $casts = array();
    /**
     * Validation rules for the model attributes.
     *
     * @example ['email' => 'required|email|unique', 'age' => 'integer|min:18']
     * @example ['name' => 'required|string|max:255', 'amount' => 'numeric|min:0']
     *
     * @since 1.0.0
     * @var array<string, string>
     */
    protected $rules = array();
    /**
     * The attributes that have aliases.
     *
     * @example ['full_name' => 'name', 'email_address' => 'email']
     * @example ['created' => 'created_at', 'modified' => 'updated_at']
     *
     * @since 1.0.0
     * @var array<string, string>
     */
    protected $aliases = array();
    /**
     * The accessors to append to the model's array form.
     *
     * @example ['full_name', 'avatar_url']
     * @example ['display_name', 'formatted_date']
     *
     * @since 1.0.0
     * @var array<int, string>
     */
    protected $appends = array();
    /**
     * The attributes that should be hidden for serialization.
     *
     * @example ['password', 'api_token']
     * @example ['secret_key', 'internal_notes']
     *
     * @since 1.0.0
     * @var array<int, string>
     */
    protected $hidden = array();
    /**
     * Indicates if the model should be timestamped.
     *
     * @example true, false
     *
     * @since 1.0.0
     * @var bool
     */
    protected $timestamps = false;
    /**
     * Indicates if the model exists in the database.
     *
     * @since 1.0.0
     * @var bool
     */
    protected bool $exists = false;
    /**
     * Get the model's attributes.
     *
     * @since 1.0.0
     * @return array<string, mixed>
     */
    public function get_attributes(): array
    {
        return array_merge($this->metadata, $this->attributes);
    }
    /**
     * Get metadata attributes.
     *
     * @since 1.0.0
     * @return array<string, mixed>
     */
    public function get_metadata(): array
    {
        return $this->metadata;
    }
    /**
     * Register a metadata attribute.
     *
     * @since 1.0.0
     *
     * @param string               $key  The meta key.
     * @param array<string, mixed> $args {
     *     Optional. Registration arguments.
     *
     *     @type mixed  $default Default value.
     *     @type string $cast    Cast type (string, integer, number, boolean, array, object).
     *     @type string $rules   Validation rules (e.g., 'required|email').
     *     @type bool   $public  If true, appends to output. If false, hidden. Default true.
     * }
     *
     * @return static
     */
    public function register_meta(string $key, array $args = array()): self
    {
        if (in_array($key, $this->columns, true) || array_key_exists($key, $this->metadata)) {
            return $this;
        }
        $args = wp_parse_args($args, array('default' => null, 'cast' => null, 'rules' => null, 'public' => true));
        if ($args['cast']) {
            $this->set_casts(array($key => $args['cast']));
        }
        if ($args['rules']) {
            $this->set_rules(array($key => $args['rules']));
        }
        if ($args['public']) {
            $this->appends[] = $key;
        } else {
            $this->hidden[] = $key;
        }
        $this->metadata[$key] = $this->cast($key, $args['default']);
        return $this;
    }
    /**
     * Get a meta attribute by key.
     *
     * @since 1.0.0
     * @param string $key      The meta key.
     * @param mixed  $fallback The fallback value if the key does not exist.
     * @return mixed
     */
    public function get_meta($key, $fallback = null)
    {
        return array_key_exists($key, $this->metadata) ? $this->metadata[$key] : $fallback;
    }
    /**
     * Set a metadata attribute by key.
     *
     * @since 1.0.0
     * @param string $key   The meta key.
     * @param mixed  $value The value to set.
     * @return static
     */
    public function set_meta($key, $value): self
    {
        $this->metadata[$key] = $this->cast($key, $value);
        return $this;
    }
    /**
     * Get database-ready attributes with scalar values.
     *
     * Converts objects and DateTime instances to their scalar representations
     * suitable for database storage.
     *
     * @since 1.0.0
     * @return array<string, mixed>
     */
    public function get_db_attributes(): array
    {
        $attributes = $this->get_attributes();
        return $this->to_recursive_array($attributes);
    }
    /**
     * Get the original data of the model.
     *
     * @since 1.0.0
     *
     * @param string|null $key      The name of the attribute.
     * @param mixed       $fallback The fallback value if the attribute is not found.
     *
     * @return mixed
     */
    public function get_original($key = null, $fallback = null)
    {
        if (!is_null($key)) {
            return array_key_exists($key, $this->original) ? $this->original[$key] : $fallback;
        }
        return $this->original;
    }
    /**
     * Apply changes to the model attributes.
     *
     * @since 1.0.0
     * @return static
     */
    public function sync_original(): self
    {
        $this->original = array_merge($this->metadata, $this->attributes);
        return $this;
    }
    /**
     * Get the props that have been changed since the last sync.
     *
     * @since 1.0.0
     * @return array<string, mixed>
     */
    public function get_changes(): array
    {
        $changed = array();
        foreach (array_keys($this->get_attributes()) as $key) {
            if ($this->is_dirty($key)) {
                $changed[] = $key;
            }
        }
        return array_intersect_key($this->get_attributes(), array_flip($changed));
    }
    /**
     * Determine if the model or given prop has been modified.
     *
     * @since 1.0.0
     *
     * @param string|null $key The prop key.
     *
     * @return bool
     */
    public function is_dirty($key = null): bool
    {
        if (is_null($key)) {
            return count($this->get_changes()) > 0;
        }
        if (!array_key_exists($key, $this->original)) {
            return true;
        }
        $current = array_key_exists($key, $this->get_attributes()) ? $this->get_attributes()[$key] : null;
        $original = array_key_exists($key, $this->original) ? $this->original[$key] : null;
        if ($current === $original) {
            return false;
        } elseif (is_null($current)) {
            return true;
        } elseif (is_numeric($current) && is_numeric($original) && strcmp((string) $current, (string) $original) !== 0) {
            return true;
        }
        return $this->cast($key, $current) !== $this->cast($key, $original);
    }
    /**
     * Get the casts for the model.
     *
     * @since 1.0.0
     * @return array<string, string>
     */
    public function get_casts(): array
    {
        return $this->casts;
    }
    /**
     * Set the casts for the model.
     *
     * @since 1.0.0
     *
     * @param array<string, string> $casts   The casts to set.
     * @param bool                  $replace Whether to replace existing casts or merge. Default false (merge).
     *
     * @return static
     */
    public function set_casts($casts, $replace = false): self
    {
        foreach ($casts as $key => $value) {
            $new = array_filter(explode('|', (string) $value));
            $existing = $replace ? array() : array_filter(explode('|', $this->casts[$key] ?? ''));
            $this->casts[$key] = implode('|', array_unique(array_merge($existing, $new)));
        }
        return $this;
    }
    /**
     * Get the validation rules for the model.
     *
     * @since 1.0.0
     * @return array<string, string>
     */
    public function get_rules(): array
    {
        return $this->rules;
    }
    /**
     * Set the rules for the model.
     *
     * @since 1.0.0
     *
     * @param array<string, string> $rules   The rules to set.
     * @param bool                  $replace Whether to replace existing rules or merge. Default false (merge).
     *
     * @return static
     */
    public function set_rules($rules, $replace = false): self
    {
        foreach ($rules as $key => $value) {
            $new = array_filter(explode('|', (string) $value));
            $existing = $replace ? array() : array_filter(explode('|', $this->rules[$key] ?? ''));
            $this->rules[$key] = implode('|', array_unique(array_merge($existing, $new)));
        }
        return $this;
    }
    /**
     * Get the attributes that have aliases.
     *
     * @since 1.0.0
     * @return array<string, string>
     */
    public function get_aliases(): array
    {
        return $this->aliases;
    }
    /**
     * Set the attributes that have aliases.
     *
     * @since 1.0.0
     *
     * @param array<string, string> $aliases The aliases to set.
     * @param bool                  $replace Whether to replace existing aliases or merge. Default false (merge).
     *
     * @return static
     */
    public function set_aliases($aliases, $replace = false): self
    {
        $this->aliases = $replace ? $aliases : array_merge($this->aliases, $aliases);
        return $this;
    }
    /**
     * Get the attribute name for the alias.
     *
     * @since 1.0.0
     *
     * @param string|array<string, mixed> $alias The alias.
     *
     * @return ($alias is array ? array<string, mixed> : string) The attribute name or array of attribute names.
     */
    public function resolve_alias($alias)
    {
        if (is_array($alias)) {
            $unaliased = array();
            foreach ($alias as $column => $value) {
                $unaliased[$this->resolve_alias((string) $column)] = $value;
            }
            return $unaliased;
        }
        return (string) ($this->aliases[$alias] ?? $alias);
    }
    /**
     * Get the accessors to append to the model's array form.
     *
     * @since 1.0.0
     * @return array<int, string>
     */
    public function get_appends(): array
    {
        return $this->appends;
    }
    /**
     * Set the accessors to append to the model's array form.
     *
     * @since 1.0.0
     *
     * @param array<int, string> $appends The accessors to set.
     * @param bool               $replace Whether to replace existing appends or merge. Default false (merge).
     *
     * @return static
     */
    public function set_appends($appends, $replace = false): self
    {
        $this->appends = $replace ? $appends : array_merge($this->appends, $appends);
        return $this;
    }
    /**
     * Get the attributes that should be hidden for serialization.
     *
     * @since 1.0.0
     * @return array<int, string>
     */
    public function get_hidden(): array
    {
        return $this->hidden;
    }
    /**
     * Set the attributes that should be hidden for serialization.
     *
     * @since 1.0.0
     *
     * @param array<int, string> $hidden  The hidden attributes to set.
     * @param bool               $replace Whether to replace existing hidden attributes or merge. Default false (merge).
     *
     * @return static
     */
    public function set_hidden($hidden, $replace = false): self
    {
        $this->hidden = $replace ? $hidden : array_merge($this->hidden, $hidden);
        return $this;
    }
    /**
     * Cast an attribute to a native PHP type.
     *
     * @since 1.0.0
     *
     * @param string|array<string, mixed>      $key   The attribute key.
     * @param mixed                            $value The value to cast.
     * @param string|array<string, mixed>|null $rule  The validation rule to apply (optional).
     *
     * @return mixed
     */
    public function cast($key, $value = null, $rule = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $key[$k] = $this->cast($k, $v, $rule);
            }
            return $key;
        }
        if (is_null($value)) {
            return $value;
        }
        // Use a cast_{key}_attribute method when one is defined.
        $method = 'cast_' . $key . '_attribute';
        if (method_exists($this, $method)) {
            return $this->{$method}($value);
        }
        $rule = empty($rule) && array_key_exists($key, $this->casts) ? $this->casts[$key] : $rule;
        $rule_items = is_string($rule) ? explode('|', $rule) : (is_array($rule) ? $rule : array());
        foreach ($rule_items as $rule_item) {
            $parameters = array();
            if ($rule_item && str_contains($rule_item, ':')) {
                $parts = explode(':', $rule_item, 2);
                $rule_item = $parts[0];
                $params_string = $parts[1];
                $parameters = array_map('trim', explode(',', $params_string));
            }
            switch ($rule_item) {
                case 'int':
                case 'integer':
                    $value = (int) $value;
                    break;
                case 'real':
                case 'float':
                case 'double':
                case 'number':
                    $value = (float) $value;
                    break;
                case 'bool':
                case 'boolean':
                    $value = (bool) filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'tinyint':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                    break;
                case 'object':
                case 'json':
                    $value = (object) maybe_unserialize($value);
                    break;
                case 'array':
                    $value = (array) maybe_unserialize($value);
                    break;
                case 'date':
                    $value = DateUtil::is_valid((string) $value, 'Y-m-d') ? $value : null;
                    break;
                case 'time':
                    $value = DateUtil::is_valid((string) $value, 'H:i:s') ? $value : null;
                    break;
                case 'datetime':
                    if ($value instanceof \DateTimeInterface) {
                        break;
                    }
                    $value = DateUtil::is_valid((string) $value, 'Y-m-d H:i:s') ? DateTime::createFromFormat('Y-m-d H:i:s', $value) : null;
                    break;
                case 'textarea':
                    $value = sanitize_textarea_field($value);
                    break;
                case 'email':
                    $value = strtolower(sanitize_email($value));
                    break;
                case 'url':
                case 'sanitize_url':
                    $value = esc_url_raw($value);
                    break;
                case 'key':
                case 'sanitize_key':
                    $value = sanitize_key($value);
                    break;
                case 'filename':
                    $value = sanitize_file_name($value);
                    break;
                case 'slug':
                    $value = sanitize_title_with_dashes($value);
                    break;
                case 'title':
                case 'sanitize_title':
                    $value = sanitize_title($value);
                    break;
                case 'strip_tags':
                    $value = wp_strip_all_tags($value);
                    break;
                case 'trim':
                    $value = trim((string) $value);
                    break;
                case 'lower':
                case 'lowercase':
                    $value = strtolower((string) $value);
                    break;
                case 'upper':
                case 'uppercase':
                    $value = strtoupper((string) $value);
                    break;
                case 'ucfirst':
                    $value = ucfirst(strtolower((string) $value));
                    break;
                case 'ucwords':
                case 'title_case':
                    $value = ucwords(strtolower((string) $value));
                    break;
                case 'esc_html':
                    $value = esc_html($value);
                    break;
                case 'attr':
                    $value = esc_attr($value);
                    break;
                case 'html':
                    $value = wp_kses_post($value);
                    break;
                case 'alpha':
                    $value = preg_replace('/[^a-zA-Z]/', '', (string) $value);
                    break;
                case 'alpha_num':
                    $value = preg_replace('/[^a-zA-Z0-9]/', '', (string) $value);
                    break;
                case 'alpha_dash':
                    $value = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $value);
                    break;
                case 'numeric':
                    $value = preg_replace('/[^0-9.-]/', '', (string) $value);
                    break;
                case 'collapse_ws':
                    $value = trim((string) $value);
                    $value = preg_replace('/\s+/', ' ', $value);
                    break;
                case 'limit':
                    $value = StringUtil::limit_string_value((string) $value, $parameters);
                    break;
                case 'pad':
                    $value = StringUtil::pad_string_value((string) $value, $parameters);
                    break;
                case 'id_list':
                    $value = wp_parse_id_list($value);
                    $value = implode(',', $value);
                    break;
                case 'list':
                    $value = wp_parse_list($value);
                    $value = implode(',', $value);
                    break;
                case 'hash':
                    $algo = $parameters[0] ?? 'sha256';
                    $salt = $parameters[1] ?? '';
                    $value = hash($algo, $salt . (string) $value);
                    break;
                case 'text':
                default:
                    if (is_callable($rule_item)) {
                        $value = call_user_func($rule_item, $value);
                    } elseif (is_array($value)) {
                        $value = map_deep($value, 'sanitize_text_field');
                    } else {
                        $value = is_scalar($value) ? sanitize_text_field((string) $value) : $value;
                    }
                    break;
            }
        }
        return $value;
    }
    /**
     * Validate the model's attributes against the defined rules.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed>|null $attributes The attributes to validate. If null, validates all attributes.
     *
     * @return \WP_Error|null A WP_Error if validation fails, or null if validation passes.
     */
    public function validate($attributes = null): ?\WP_Error
    {
        if (is_null($attributes)) {
            $attributes = $this->get_attributes();
        }
        $errors = array();
        foreach ($this->rules as $attribute => $rules) {
            $value = array_key_exists($attribute, $attributes) ? $attributes[$attribute] : null;
            $rules = is_string($rules) ? explode('|', $rules) : (is_array($rules) ? $rules : array());
            $method = 'validate_' . $attribute . '_attribute';
            if (method_exists($this, $method)) {
                $result = $this->{$method}($value, $rules);
                if (is_wp_error($result)) {
                    $errors = array_merge($errors, $result->get_error_messages());
                }
                continue;
            }
            $is_required = in_array('required', $rules, true);
            if (!$is_required && $this->is_empty_value($value)) {
                continue;
            }
            foreach ($rules as $rule) {
                $parameters = array();
                if (str_contains($rule, ':')) {
                    $parts = explode(':', $rule, 2);
                    $rule_name = $parts[0];
                    $params_string = $parts[1];
                    $parameters = array_map('trim', explode(',', $params_string));
                } else {
                    $rule_name = $rule;
                }
                if (!$this->validate_value($rule_name, $value, $parameters)) {
                    $attribute_name = preg_replace('/[^a-zA-Z0-9 ]+/', ' ', $attribute);
                    $attribute_name = preg_replace('/\s+/', ' ', trim((string) $attribute_name));
                    $messages = $this->get_validation_messages();
                    $replacements = array_merge(array($attribute_name), $parameters);
                    $replacements = array_filter($replacements, 'is_scalar');
                    $message = $messages[$rule_name] ?? 'The %s is invalid.';
                    $errors[] = vsprintf($message, $replacements);
                }
            }
        }
        if (empty($errors)) {
            return null;
        }
        $error = new \WP_Error();
        foreach ($errors as $err) {
            $error->add('validation_error', $err);
        }
        return $error;
    }
    /**
     * Validate a value using common model validation rules.
     *
     * @since 1.0.0
     * @param string             $rule       The validation rule name.
     * @param mixed              $value      The value to validate.
     * @param array<int, string> $parameters Rule parameters.
     * @return bool True if validation passes, false otherwise.
     */
    protected function validate_value($rule, $value, $parameters = array()): bool
    {
        switch ($rule) {
            case 'required':
                $result = !$this->is_empty_value($value);
                break;
            case 'string':
                $result = is_string($value);
                break;
            case 'integer':
            case 'int':
                $result = filter_var($value, FILTER_VALIDATE_INT) !== false;
                break;
            case 'numeric':
                $result = is_numeric($value);
                break;
            case 'boolean':
            case 'bool':
                $result = is_bool($value) || in_array($value, array(0, 1, '0', '1', 'true', 'false', 'on', 'off', 'yes', 'no'), true);
                break;
            case 'email':
                $result = false !== is_email($value);
                break;
            case 'url':
                $result = filter_var($value, FILTER_VALIDATE_URL) !== false;
                break;
            case 'date':
                $result = DateUtil::is_valid($value, 'Y-m-d');
                break;
            case 'datetime':
                $result = DateUtil::is_valid($value, 'Y-m-d H:i:s');
                break;
            case 'min':
                $result = $this->validate_min_value($value, $parameters);
                break;
            case 'max':
                $result = $this->validate_max_value($value, $parameters);
                break;
            case 'between':
                $result = isset($parameters[0], $parameters[1]) && $this->validate_min_value($value, array($parameters[0])) && $this->validate_max_value($value, array($parameters[1]));
                break;
            case 'in':
                $result = in_array($value, $parameters, true);
                break;
            case 'not_in':
                $result = !in_array($value, $parameters, true);
                break;
            case 'same':
                $result = isset($parameters[0]) ? $this->get($parameters[0]) === $value : false;
                break;
            case 'different':
                $result = isset($parameters[0]) ? $this->get($parameters[0]) !== $value : true;
                break;
            case 'unique':
                $result = $this->validate_unique_value($value, $parameters);
                break;
            case 'exists':
                $result = $this->validate_exists_value($value, $parameters);
                break;
            default:
                $result = true;
                break;
        }
        return $result;
    }
    /**
     * Check if a value is considered empty for validation.
     *
     * @since 1.0.0
     * @param mixed $value Value to check.
     * @return bool True if the value is empty.
     */
    protected function is_empty_value($value): bool
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        if (null === $value || '' === $value || is_array($value) && empty($value)) {
            return true;
        }
        return false;
    }
    /**
     * Validate that a record is unique in the database table.
     *
     * Supports optional scope fields to check uniqueness within a subset of records.
     * Scope values are read from the model's current attributes.
     *
     * Usage:
     * - `unique:field`               — globally unique in model's table.
     * - `unique:field,scope1`        — unique where scope1 matches model's value.
     * - `unique:field,scope1,scope2` — unique where scope1 AND scope2 match.
     *
     * @since 1.0.0
     * @param mixed              $value  Value to check.
     * @param array<int, string> $fields The field to check followed by optional scope fields.
     * @return bool True if unique.
     */
    protected function validate_unique_value($value, $fields): bool
    {
        global $wpdb;
        if (empty($fields)) {
            return false;
        }
        $table = $this->get_table();
        $primary_key = $this->get_primary_key();
        $field = sanitize_text_field($fields[0]);
        $where = $wpdb->prepare("`{$field}` = %s", $value);
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Field name is sanitized above.
        for ($i = 1, $count = count($fields); $i < $count; $i++) {
            $scope = sanitize_text_field($fields[$i]);
            $where .= $wpdb->prepare(" AND `{$scope}` = %s", $this->get($scope));
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Field name is sanitized above.
        }
        if ($this->exists()) {
            $where .= $wpdb->prepare(" AND `{$primary_key}` != %s", $this->get_key_value());
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Primary key is a model property.
        }
        $query = "SELECT COUNT(*) FROM `{$wpdb->prefix}{$table}` WHERE {$where}";
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Built from prepared clauses above.
        $count = $wpdb->get_var($query);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query built from prepared clauses above.
        return 0 === (int) $count;
    }
    /**
     * Validate that a record exists in the database table.
     *
     * @since 1.0.0
     * @param mixed              $value      Value to check.
     * @param array<int, string> $parameters Parameters [column] or [table, column].
     * @return bool True if it exists.
     */
    protected function validate_exists_value($value, $parameters): bool
    {
        global $wpdb;
        if (count($parameters) < 1 || empty($value)) {
            return false;
        }
        if (count($parameters) === 1) {
            $table = $this->get_table();
            $column = sanitize_text_field($parameters[0]);
        } else {
            $table = sanitize_text_field($parameters[0]);
            $column = sanitize_text_field($parameters[1]);
        }
        $value = sanitize_text_field($value);
        $query = $wpdb->prepare("SELECT COUNT(*) FROM `{$wpdb->prefix}{$table}` WHERE `{$column}` = %s", $value);
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table and column names are sanitized.
        $count = $wpdb->get_var($query);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query prepared above.
        return 0 < $count;
    }
    /**
     * Validate if a value meets minimum requirements.
     *
     * @since 1.0.0
     * @param mixed              $value      The value to validate.
     * @param array<int, string> $parameters Rule parameters.
     * @return bool True if validation passes, false otherwise.
     */
    protected function validate_min_value($value, $parameters): bool
    {
        if (!isset($parameters[0])) {
            return false;
        }
        $min = (float) $parameters[0];
        if (is_string($value)) {
            return $min <= mb_strlen($value);
        }
        if (is_numeric($value)) {
            return $min <= (float) $value;
        }
        if (is_array($value)) {
            return $min <= count($value);
        }
        return false;
    }
    /**
     * Validate if a value meets maximum requirements.
     *
     * @since 1.0.0
     * @param mixed              $value      The value to validate.
     * @param array<int, string> $parameters Rule parameters.
     * @return bool True if validation passes, false otherwise.
     */
    protected function validate_max_value($value, $parameters): bool
    {
        if (!isset($parameters[0])) {
            return false;
        }
        $max = (float) $parameters[0];
        if (is_string($value)) {
            return $max >= mb_strlen($value);
        }
        if (is_numeric($value)) {
            return $max >= (float) $value;
        }
        if (is_array($value)) {
            return $max >= count($value);
        }
        return false;
    }
    /**
     * Get validation error messages for the supported rules.
     *
     * @since 1.0.0
     * @return array<string, string> Array of validation messages.
     */
    protected function get_validation_messages(): array
    {
        return array('required' => 'The %s is required.', 'string' => 'The %s must be a string.', 'integer' => 'The %s must be an integer.', 'int' => 'The %s must be an integer.', 'numeric' => 'The %s must be a number.', 'boolean' => 'The %s must be true or false.', 'bool' => 'The %s must be true or false.', 'email' => 'The %s must be a valid email address.', 'url' => 'The %s must be a valid URL.', 'date' => 'The %s is not a valid date.', 'datetime' => 'The %s is not a valid datetime.', 'min' => 'The %s must be at least %s.', 'max' => 'The %s may not be greater than %s.', 'between' => 'The %s must be between %s and %s.', 'in' => 'The selected %s is invalid.', 'not_in' => 'The selected %s is invalid.', 'same' => 'The %s and %s must match.', 'different' => 'The %s and %s must be different.', 'unique' => 'The %s has already been taken.', 'exists' => 'The selected %s does not exist.');
    }
    /**
     * Set the author of the model if applicable.
     *
     * @since 1.0.0
     * @return void
     */
    protected function set_author(): void
    {
        if (static::CREATED_BY && !$this->exists() && empty($this->get(static::CREATED_BY))) {
            $user_id = get_current_user_id();
            if ($user_id) {
                $this->set(static::CREATED_BY, $user_id);
            }
        }
    }
    /**
     * Update the creation and update timestamps.
     *
     * @since 1.0.0
     * @return void
     */
    protected function set_timestamps(): void
    {
        if ($this->timestamps) {
            $now = current_time('mysql', true);
            $created_at = $this->get(static::CREATED_AT);
            // Set created_at if creating and not set already.
            if (!$this->exists() && empty($created_at)) {
                $this->set(static::CREATED_AT, $now);
                $this->set(static::UPDATED_AT, null);
            }
            // Set updated_at if updating and not set already.
            if ($this->exists() && $this->get_changes()) {
                $this->set(static::UPDATED_AT, $now);
            }
        }
    }
    /**
     * Convert a value to an array recursively.
     *
     * @since 1.0.0
     * @param mixed $value The value to convert.
     * @return mixed
     */
    protected function to_recursive_array($value)
    {
        if (is_null($value) || is_scalar($value)) {
            return $value;
        }
        if (is_object($value)) {
            if ($value instanceof \DateTime) {
                return $value->format('Y-m-d H:i:s');
            }
            if (method_exists($value, 'to_array')) {
                return $value->to_array();
            }
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }
            return get_object_vars($value);
        }
        if (is_array($value)) {
            $result = array();
            foreach ($value as $key => $item) {
                $result[$key] = $this->to_recursive_array($item);
            }
            return $result;
        }
        return $value;
    }
}