<?php

namespace WooCommerceSerialNumbers\B8\Models\Relations;

use WooCommerceSerialNumbers\B8\Models\Model;
defined('ABSPATH') || exit;
/**
 * MorphMany relation class.
 *
 * Handles polymorphic one-to-many relationships between models.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class MorphMany extends Relation
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
     * The local key of the parent model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $local_key;
    /**
     * Create a new morph many relationship instance.
     *
     * @since 1.0.0
     * @param Model  $_parent    The parent model instance.
     * @param Model  $related    The related model instance.
     * @param string $morph_type The type column name.
     * @param string $morph_id   The ID column name.
     * @param string $local_key  The local key.
     */
    public function __construct($_parent, $related, $morph_type, $morph_id, $local_key)
    {
        $this->morph_type = $morph_type;
        $this->morph_id = $morph_id;
        $this->local_key = $local_key;
        parent::__construct($_parent, $related);
    }
    /**
     * Get the results of the relationship.
     *
     * @since 1.0.0
     * @return array<int, \WooCommerceSerialNumbers\B8\Models\Model>
     */
    public function get(): array
    {
        $parent_key_value = $this->parent->get($this->local_key);
        if (empty($parent_key_value)) {
            return array();
        }
        $results = $this->query->get();
        return is_array($results) ? $results : array();
    }
    /**
     * Set the base constraints on the relation query.
     *
     * @since 1.0.0
     * @return void
     */
    protected function set_query_constraints(): void
    {
        $this->query->where($this->morph_type, '=', $this->parent->get_object_type());
        $this->query->where($this->morph_id, '=', $this->parent->get($this->local_key));
        $this->query->where($this->morph_id, 'IS NOT NULL', null);
    }
    /**
     * Apply constraints for model creation.
     *
     * @param Model $model The model being created.
     *
     * @since 1.0.0
     * @return Model The modified model.
     */
    protected function set_foreign_attributes($model): Model
    {
        $model->set($this->morph_type, $this->parent->get_object_type());
        $model->set($this->morph_id, $this->parent->get($this->local_key));
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
        $parent_ids = wp_list_pluck($models, $this->local_key);
        $parent_ids = array_filter($parent_ids);
        if (empty($parent_ids) || empty($models)) {
            return;
        }
        $parent_type = $models[0]->get_object_type();
        $results = $this->related->new_query()->where($this->morph_id, 'IN', array_unique($parent_ids))->where($this->morph_type, '=', $parent_type)->get();
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
            $parent_id = $result->get($this->morph_id);
            if (!isset($dictionary[$parent_id])) {
                $dictionary[$parent_id] = array();
            }
            $dictionary[$parent_id][] = $result;
        }
        foreach ($models as $model) {
            $parent_id = $model->get($this->local_key);
            if (isset($dictionary[$parent_id])) {
                $model->set_relation($relation, $dictionary[$parent_id]);
            }
        }
        return $models;
    }
}