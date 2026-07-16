<?php

namespace WooCommerceSerialNumbers\B8\Models\Relations;

use WooCommerceSerialNumbers\B8\Models\Model;
defined('ABSPATH') || exit;
/**
 * HasMany relation class.
 *
 * Handles one-to-many relationships between models.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class HasMany extends Relation
{
    /**
     * The foreign key of the parent model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $foreign_key;
    /**
     * The local key of the parent model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $local_key;
    /**
     * Create a new has many relationship instance.
     *
     * @since 1.0.0
     * @param Model  $_parent     The parent model instance.
     * @param Model  $related     The related model instance.
     * @param string $foreign_key The foreign key.
     * @param string $local_key   The local key.
     */
    public function __construct($_parent, $related, $foreign_key, $local_key)
    {
        $this->foreign_key = $foreign_key;
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
        $this->query->where($this->foreign_key, '=', $this->parent->get($this->local_key));
        $this->query->where($this->foreign_key, 'IS NOT NULL', null);
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
        $model->set($this->foreign_key, $this->parent->get($this->local_key));
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
        $local_keys = wp_list_pluck($models, $this->local_key);
        $local_keys = array_filter($local_keys);
        if (empty($local_keys)) {
            return;
        }
        $results = $this->related->new_query()->where($this->foreign_key, 'IN', array_unique($local_keys))->get();
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
            $key = $result->get($this->foreign_key);
            if (!isset($dictionary[$key])) {
                $dictionary[$key] = array();
            }
            $dictionary[$key][] = $result;
        }
        foreach ($models as $model) {
            $key = $model->get($this->local_key);
            if (isset($dictionary[$key])) {
                $model->set_relation($relation, $dictionary[$key]);
            }
        }
        return $models;
    }
}