<?php

namespace WooCommerceSerialNumbers\B8\Models\Relations;

use WooCommerceSerialNumbers\B8\Models\Model;
defined('ABSPATH') || exit;
/**
 * MorphTo relation class.
 *
 * Handles polymorphic inverse relationships between models.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class MorphTo extends Relation
{
    /**
     * The type column name.
     *
     * @since 1.0.0
     * @var string
     */
    protected $morph_type;
    /**
     * The ID column name.
     *
     * @since 1.0.0
     * @var string
     */
    protected $morph_id;
    /**
     * The owner key on the related model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $owner_key;
    /**
     * Create a new morph to relationship instance.
     *
     * @since 1.0.0
     * @param Model  $child      The child model instance.
     * @param string $morph_type The type column name.
     * @param string $morph_id   The ID column name.
     * @param string $owner_key  The owner key.
     */
    public function __construct($child, $morph_type, $morph_id, $owner_key)
    {
        $this->morph_type = $morph_type;
        $this->morph_id = $morph_id;
        $this->owner_key = $owner_key;
        $morph_type_value = $child->get($morph_type);
        $related_model = null;
        if (!empty($morph_type_value) && class_exists($morph_type_value) && is_subclass_of($morph_type_value, Model::class)) {
            $related_model = new $morph_type_value();
        }
        parent::__construct($child, $related_model);
    }
    /**
     * Get the results of the relationship.
     *
     * @since 1.0.0
     * @return Model|object|null
     */
    public function get()
    {
        if (!$this->related) {
            return null;
        }
        if (empty($this->parent->get($this->morph_id))) {
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
        if (!$this->related) {
            return;
        }
        $morph_id_value = $this->parent->get($this->morph_id);
        if (empty($morph_id_value)) {
            $this->query->where($this->owner_key, '=', null);
            return;
        }
        $this->query->where($this->owner_key, '=', $morph_id_value);
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
     * Load the relation for given models.
     *
     * @since 1.0.0
     * @param array<int, \WooCommerceSerialNumbers\B8\Models\Model> $models   The array of parent models.
     * @param string                       $relation The relation name.
     * @return void
     */
    public function load($models, $relation): void
    {
        $grouped_keys = array();
        foreach ($models as $model) {
            $morph_type = $model->get($this->morph_type);
            $morph_id = $model->get($this->morph_id);
            if (!empty($morph_type) && !empty($morph_id)) {
                if (!isset($grouped_keys[$morph_type])) {
                    $grouped_keys[$morph_type] = array();
                }
                $grouped_keys[$morph_type][] = $morph_id;
            }
        }
        if (empty($grouped_keys)) {
            return;
        }
        $all_results = array();
        foreach ($grouped_keys as $morph_type => $ids) {
            if (class_exists($morph_type) && is_subclass_of($morph_type, Model::class)) {
                $morph_model = new $morph_type();
                $results = $morph_model->new_query()->where($this->owner_key, 'IN', array_unique($ids))->get();
                if (!empty($results)) {
                    $all_results = array_merge($all_results, $results);
                }
            }
        }
        $this->match($models, $all_results, $relation);
    }
    /**
     * Match the eagerly loaded results to their parents.
     *
     * @since 1.0.0
     * @param array<int, \WooCommerceSerialNumbers\B8\Models\Model> $models   The array of parent models.
     * @param array<int, mixed>            $results  The array of related results.
     * @param string                       $relation The relation name.
     * @return array<int, \WooCommerceSerialNumbers\B8\Models\Model> The parent models.
     */
    protected function match($models, $results, $relation): array
    {
        $dictionary = array();
        foreach ($results as $result) {
            $dictionary[$result->get_object_type()][$result->get($this->owner_key)] = $result;
        }
        foreach ($models as $model) {
            $morph_type = $model->get($this->morph_type);
            $morph_id = $model->get($this->morph_id);
            if (isset($dictionary[$morph_type][$morph_id])) {
                $model->set_relation($relation, $dictionary[$morph_type][$morph_id]);
            }
        }
        return $models;
    }
    /**
     * Attach a model to the parent.
     *
     * @since 1.0.0
     * @param Model|int $id The model instance or ID to attach.
     * @return Model The parent model.
     */
    public function attach($id): Model
    {
        if (is_int($id)) {
            if (!$this->related) {
                return $this->parent;
            }
            $model = $this->related->find($id);
            if (!$model) {
                return $this->parent;
            }
        } else {
            $model = $id;
        }
        $this->parent->set($this->morph_type, $model->get_object_type());
        $this->parent->set($this->morph_id, $model->get($this->owner_key));
        return $this->parent;
    }
    /**
     * Detach a model from the parent.
     *
     * @since 1.0.0
     * @param Model|int|null $id The model instance or ID to detach.
     * @return Model The parent model.
     */
    public function detach($id = null): Model
    {
        $this->parent->set($this->morph_type, null);
        $this->parent->set($this->morph_id, null);
        return $this->parent;
    }
}