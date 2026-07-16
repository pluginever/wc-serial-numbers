<?php

namespace WooCommerceSerialNumbers\B8\Models\Traits;

use WooCommerceSerialNumbers\B8\Models\Relations\BelongsTo;
use WooCommerceSerialNumbers\B8\Models\Relations\BelongsToMany;
use WooCommerceSerialNumbers\B8\Models\Relations\HasMany;
use WooCommerceSerialNumbers\B8\Models\Relations\HasOne;
use WooCommerceSerialNumbers\B8\Models\Relations\HasOneThrough;
use WooCommerceSerialNumbers\B8\Models\Relations\MorphMany;
use WooCommerceSerialNumbers\B8\Models\Relations\MorphTo;
use WooCommerceSerialNumbers\B8\Models\Relations\MorphToMany;
use WooCommerceSerialNumbers\B8\Models\Relations\Relation;
defined('ABSPATH') || exit;
/**
 * Relations trait.
 *
 * Provides relationship management functionality for models.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
trait RelationsTrait
{
    /**
     * Relations data.
     *
     * @since 1.0.0
     * @var array<string, mixed>
     */
    protected $relations = array();
    /**
     * Has one relation.
     *
     * @since 1.0.0
     * @param class-string<\WooCommerceSerialNumbers\B8\Models\Model> $related     Related model class name.
     * @param string|null                    $foreign_key Optional. For example 'product_id' in the related model.
     * @param string|null                    $local_key   Optional. For example 'id' in the parent model.
     * @return HasOne
     */
    protected function has_one($related, ?string $foreign_key = null, ?string $local_key = null): HasOne
    {
        $instance = new $related();
        $foreign_key = $foreign_key ?? $this->get_object_type() . '_' . $this->get_primary_key();
        $local_key = $local_key ?? $this->get_primary_key();
        return new HasOne($this, $instance, $foreign_key, $local_key);
    }
    /**
     * Has many relation.
     *
     * @since 1.0.0
     * @param class-string<\WooCommerceSerialNumbers\B8\Models\Model> $related     Related model class name.
     * @param string|null                    $foreign_key Optional. For example 'product_id' in the related model.
     * @param string|null                    $local_key   Optional. For example 'id' in the parent model.
     * @return HasMany
     */
    protected function has_many($related, ?string $foreign_key = null, ?string $local_key = null): HasMany
    {
        $instance = new $related();
        $foreign_key = $foreign_key ?? $this->get_object_type() . '_' . $this->get_primary_key();
        $local_key = $local_key ?? $this->get_primary_key();
        return new HasMany($this, $instance, $foreign_key, $local_key);
    }
    /**
     * Belongs to relation.
     *
     * @since 1.0.0
     * @param class-string<\WooCommerceSerialNumbers\B8\Models\Model> $related     Related model class name.
     * @param string|null                    $foreign_key Optional. For example 'order_id' in the parent model.
     * @param string|null                    $parent_key  Optional. For example 'id' in the related model.
     * @return BelongsTo
     */
    protected function belongs_to($related, ?string $foreign_key = null, ?string $parent_key = null): BelongsTo
    {
        $instance = new $related();
        $foreign_key = $foreign_key ?? $instance->get_object_type() . '_' . $instance->get_primary_key();
        $parent_key = $parent_key ?? $instance->get_primary_key();
        return new BelongsTo($instance, $this, $foreign_key, $parent_key);
    }
    /**
     * Belongs to many relation, supporting both array storage and pivot tables.
     *
     * @since 1.0.0
     * @param class-string<\WooCommerceSerialNumbers\B8\Models\Model> $related           Related model class name.
     * @param string|null                    $foreign_key       The column storing serialized IDs or the pivot table name.
     * @param string|null                    $related_key       Related model key.
     * @param string|null                    $foreign_pivot_key Foreign key in the pivot table (null for array storage).
     * @param string|null                    $related_pivot_key Related key in the pivot table (null for array storage).
     * @param string|null                    $parent_key        Parent key (for pivot tables).
     * @return BelongsToMany
     */
    protected function belongs_to_many($related, ?string $foreign_key = null, ?string $related_key = null, ?string $foreign_pivot_key = null, ?string $related_pivot_key = null, ?string $parent_key = null): BelongsToMany
    {
        $instance = new $related();
        if (null !== $foreign_pivot_key && null !== $related_pivot_key) {
            $table = $foreign_key ?? $this->get_object_type() . '_' . $instance->get_object_type();
            $parent_key = $parent_key ?? $this->get_primary_key();
            $related_key = $related_key ?? $instance->get_primary_key();
            return new BelongsToMany($this, $instance, $table, $related_key, $foreign_pivot_key, $related_pivot_key, $parent_key);
        }
        $foreign_key = $foreign_key ?? $instance->get_object_type() . '_ids';
        $related_key = $related_key ?? $instance->get_primary_key();
        return new BelongsToMany($this, $instance, $foreign_key, $related_key);
    }
    /**
     * Morph to relation.
     *
     * @since 1.0.0
     * @param string|null $morph_type Optional. Morph type column name.
     * @param string|null $morph_id   Optional. Morph id column name.
     * @param string|null $owner_key  Optional. Owner key of the related model.
     * @return MorphTo
     */
    protected function morph_to(?string $morph_type = null, ?string $morph_id = null, ?string $owner_key = null): MorphTo
    {
        $morph_type = $morph_type ?? 'morphable_type';
        $morph_id = $morph_id ?? 'morphable_id';
        $owner_key = $owner_key ?? 'id';
        return new MorphTo($this, $morph_type, $morph_id, $owner_key);
    }
    /**
     * Morph many relation.
     *
     * @since 1.0.0
     * @param class-string<\WooCommerceSerialNumbers\B8\Models\Model> $related    Related model class name.
     * @param string|null                    $morph_type Optional. Morph type column name.
     * @param string|null                    $morph_id   Optional. Morph id column name.
     * @param string|null                    $local_key  Optional. Local key of the parent model.
     * @return MorphMany
     */
    protected function morph_many($related, ?string $morph_type = null, ?string $morph_id = null, ?string $local_key = null): MorphMany
    {
        $instance = new $related();
        $morph_type = $morph_type ?? 'morphable_type';
        $morph_id = $morph_id ?? 'morphable_id';
        $local_key = $local_key ?? $this->get_primary_key();
        return new MorphMany($this, $instance, $morph_type, $morph_id, $local_key);
    }
    /**
     * Morph to many relation (polymorphic many-to-many).
     *
     * @since 1.0.0
     * @param class-string<\WooCommerceSerialNumbers\B8\Models\Model> $related           Related model class name.
     * @param string                         $pivot_table       Pivot table name without prefix.
     * @param string                         $morph_name        Morph name prefix (e.g., 'taggable').
     * @param string|null                    $related_pivot_key Foreign key to the related model in the pivot.
     * @param string|null                    $local_key         Local key on the parent model.
     * @param string|null                    $related_key       Key on the related model.
     * @return MorphToMany
     */
    protected function morph_to_many($related, string $pivot_table, string $morph_name, ?string $related_pivot_key = null, ?string $local_key = null, ?string $related_key = null): MorphToMany
    {
        $instance = new $related();
        $related_pivot_key = $related_pivot_key ?? $instance->get_object_type() . '_' . $instance->get_primary_key();
        $local_key = $local_key ?? $this->get_primary_key();
        $related_key = $related_key ?? $instance->get_primary_key();
        return new MorphToMany($this, $instance, $pivot_table, $morph_name, $related_pivot_key, $local_key, $related_key);
    }
    /**
     * Has one through relation.
     *
     * @since 1.0.0
     * @param class-string<\WooCommerceSerialNumbers\B8\Models\Model> $related          Related model class name.
     * @param class-string<\WooCommerceSerialNumbers\B8\Models\Model> $through          Intermediate model class name.
     * @param string|null                    $first_key        Optional. First key on the relationship.
     * @param string|null                    $second_key       Optional. Second key on the relationship.
     * @param string|null                    $local_key        Optional. Local key on the parent model.
     * @param string|null                    $second_local_key Optional. Second local key on the intermediate model.
     * @return HasOneThrough
     */
    protected function has_one_through($related, $through, ?string $first_key = null, ?string $second_key = null, ?string $local_key = null, ?string $second_local_key = null): HasOneThrough
    {
        $related_instance = new $related();
        $through_instance = new $through();
        $first_key = $first_key ?? $this->get_object_type() . '_' . $this->get_primary_key();
        $second_key = $second_key ?? $related_instance->get_primary_key();
        $local_key = $local_key ?? $this->get_primary_key();
        $second_local_key = $second_local_key ?? $through_instance->get_primary_key();
        return new HasOneThrough($this, $related_instance, $through_instance, $first_key, $second_key, $local_key, $second_local_key);
    }
    /**
     * Get all the loaded relations for the instance.
     *
     * @since 1.0.0
     * @return array<string, mixed>
     */
    public function get_relations(): array
    {
        return $this->relations;
    }
    /**
     * Set the entire relations array on the model.
     *
     * @since 1.0.0
     * @param array<string, mixed> $relations The relations array.
     * @return $this
     */
    public function set_relations(array $relations): self
    {
        $this->relations = $relations;
        return $this;
    }
    /**
     * Get the given relationship value.
     *
     * @since 1.0.0
     * @param string $key The relation key.
     * @return mixed
     */
    public function get_relation(string $key)
    {
        return $this->relations[$key] ?? null;
    }
    /**
     * Set the given relationship on the model.
     *
     * @since 1.0.0
     * @param string         $relation The relation name.
     * @param mixed|callable $value    The value to set.
     * @return $this
     */
    public function set_relation(string $relation, $value): self
    {
        if (is_callable($value)) {
            $this->relations[$relation] = $value($this->get_relation($relation));
        } else {
            $this->relations[$relation] = $value;
        }
        return $this;
    }
    /**
     * Unset the given relation on the model.
     *
     * @since 1.0.0
     * @param string $relation The relation name.
     * @return $this
     */
    public function unset_relation(string $relation): self
    {
        unset($this->relations[$relation]);
        return $this;
    }
    /**
     * Determine if the given relation is loaded.
     *
     * @since 1.0.0
     * @param string $key The relation key.
     * @return bool
     */
    public function relation_loaded(string $key): bool
    {
        return array_key_exists($key, $this->relations);
    }
    /**
     * Determine if the given key is a relation method on the model.
     *
     * @since 1.0.0
     * @param string $key The key.
     * @return bool
     */
    public function has_relation(string $key): bool
    {
        return !method_exists($this, "get_{$key}_attribute") && method_exists($this, $key) && is_a($this->{$key}(), Relation::class);
    }
    /**
     * Get a relationship value, loading it if necessary.
     *
     * @since 1.0.0
     * @param string $key The relation key.
     * @return mixed The relation value, or null when the key is not a relation.
     */
    public function get_relation_value(string $key)
    {
        if ($this->relation_loaded($key)) {
            return $this->relations[$key];
        }
        if (!$this->has_relation($key)) {
            return null;
        }
        $relation = $this->{$key}();
        if (!$relation instanceof Relation) {
            return null;
        }
        $results = $relation->get();
        $this->set_relation($key, $results);
        return $results;
    }
    /**
     * Load the given relationships for the model.
     *
     * @since 1.0.0
     * @param array<int, string>|string $relations The relations to load.
     * @return $this
     */
    public function load_relations($relations): self
    {
        $args = func_get_args();
        if (is_string($args[0])) {
            $relations = $args;
        } elseif (!is_array($args[0])) {
            $relations = array($args[0]);
        } else {
            $relations = $args[0];
        }
        foreach ($relations as $relation) {
            if (!$this->relation_loaded($relation)) {
                $this->get_relation_value($relation);
            }
        }
        return $this;
    }
}