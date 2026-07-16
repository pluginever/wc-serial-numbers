<?php

namespace WooCommerceSerialNumbers\B8\Models\Relations;

use WooCommerceSerialNumbers\B8\Models\Model;
defined('ABSPATH') || exit;
/**
 * BelongsTo relation class.
 *
 * Handles one-to-many inverse relationships between models.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class BelongsTo extends Relation
{
    /**
     * The foreign key of the child model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $foreign_key;
    /**
     * The associated key on the parent model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $owner_key;
    /**
     * Create a new belongs to relationship instance.
     *
     * @since 1.0.0
     * @param Model  $related     The related model instance.
     * @param Model  $child       The child model instance.
     * @param string $foreign_key The foreign key.
     * @param string $owner_key   The owner key.
     */
    public function __construct($related, $child, $foreign_key, $owner_key)
    {
        $this->foreign_key = $foreign_key;
        $this->owner_key = $owner_key;
        parent::__construct($child, $related);
    }
    /**
     * Get the results of the relationship.
     *
     * @since 1.0.0
     * @return Model|object|null
     */
    public function get()
    {
        if (empty($this->parent->get($this->foreign_key))) {
            return null;
        }
        return $this->query->first();
    }
    /**
     * Set the base constraints on the relation query.
     *
     * @since 1.0.0
     * @return void
     */
    protected function set_query_constraints(): void
    {
        $this->query->where($this->owner_key, '=', $this->parent->get($this->foreign_key));
        $this->query->where($this->owner_key, 'IS NOT NULL', null);
        $this->query->limit(1);
    }
    /**
     * Apply constraints for model creation.
     *
     * @since 1.0.0
     * @param Model $model The model being created.
     * @return Model The modified model.
     */
    protected function set_foreign_attributes($model): Model
    {
        return $model;
    }
    /**
     * Create a new related model and save it to the database.
     *
     * @since 1.0.0
     * @param array<string, mixed> $attributes The attributes for the new model.
     * @return Model|\WP_Error
     */
    public function insert($attributes = array())
    {
        $model = parent::insert($attributes);
        if (is_wp_error($model)) {
            return $model;
        }
        $this->attach($model);
        return $model;
    }
    /**
     * Load the relation for given models.
     *
     * @since 1.0.0
     * @param array<int, \WooCommerceSerialNumbers\B8\Models\Model> $models   The array of parent models.
     * @param string                       $relation The relation name.
     * @return void
     */
    public function load($models, $relation): void
    {
        $foreign_keys = wp_list_pluck($models, $this->foreign_key);
        $foreign_keys = array_filter($foreign_keys);
        if (empty($foreign_keys)) {
            return;
        }
        $results = $this->related->new_query()->where($this->owner_key, 'IN', array_unique($foreign_keys))->get();
        $this->match($models, $results, $relation);
    }
    /**
     * Match the eagerly loaded results to their parents.
     *
     * @since 1.0.0
     * @param array<int, \WooCommerceSerialNumbers\B8\Models\Model> $models   The array of parent models.
     * @param array<int, mixed>            $results  The array of related results.
     * @param string                       $relation The relation name.
     * @return array<int, \WooCommerceSerialNumbers\B8\Models\Model>
     */
    protected function match($models, $results, $relation): array
    {
        $dictionary = array();
        foreach ($results as $result) {
            $dictionary[$result->get($this->owner_key)] = $result;
        }
        foreach ($models as $model) {
            $key = $model->get($this->foreign_key);
            if (isset($dictionary[$key])) {
                $model->set_relation($relation, $dictionary[$key]);
            }
        }
        return $models;
    }
    /**
     * Save a model and set the relationship constraints.
     *
     * @since 1.0.0
     * @param Model $model The model to save.
     * @return Model|\WP_Error
     */
    public function save($model)
    {
        $result = parent::save($model);
        if (is_wp_error($result)) {
            return $result;
        }
        $this->attach($result);
        return $result;
    }
    /**
     * Attach a model to the parent.
     *
     * @since 1.0.0
     * @param Model|int $id The model instance or ID to attach.
     * @return Model
     */
    public function attach($id): Model
    {
        $owner_key = $id instanceof Model ? $id->get($this->owner_key) : $id;
        $this->parent->set($this->foreign_key, $owner_key);
        if ($id instanceof Model) {
            $this->parent->set_relation($this->get_relation_name(), $id);
        } else {
            $this->parent->unset_relation($this->get_relation_name());
        }
        return $this->parent;
    }
    /**
     * Detach a model from the parent.
     *
     * @since 1.0.0
     * @param Model|int|null $id The model instance or ID to detach.
     * @return Model
     */
    public function detach($id = null): Model
    {
        $this->parent->set($this->foreign_key, null);
        $this->parent->unset_relation($this->get_relation_name());
        return $this->parent;
    }
}