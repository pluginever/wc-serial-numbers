<?php

namespace WooCommerceSerialNumbers\B8\Models\Relations;

use WooCommerceSerialNumbers\B8\Models\Model;
defined('ABSPATH') || exit;
/**
 * HasOne relation class.
 *
 * Handles one-to-one relationships between models.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class HasOne extends Relation
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
     * Create a new has one relationship instance.
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
     * @return mixed
     */
    public function get()
    {
        if (empty($this->parent->get($this->local_key))) {
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
        $this->query->where($this->foreign_key, '=', $this->parent->get($this->local_key));
        $this->query->where($this->foreign_key, 'IS NOT NULL', null);
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
        $model->set($this->foreign_key, $this->parent->get($this->local_key));
        return $model;
    }
    /**
     * Eager load the relation for given models.
     *
     * @since 1.0.0
     * @param array<int, \WooCommerceSerialNumbers\B8\Models\Model> $models   The array of parent models.
     * @param string                       $relation The relation name.
     * @return void
     */
    public function load($models, $relation): void
    {
        $local_keys = wp_list_pluck($models, $this->local_key);
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
            $dictionary[$result->get($this->foreign_key)] = $result;
        }
        foreach ($models as $model) {
            $key = $model->get($this->local_key);
            if (isset($dictionary[$key])) {
                $model->set_relation($relation, $dictionary[$key]);
            }
        }
        return $models;
    }
    /**
     * Create a new instance of the related model.
     *
     * @since 1.0.0
     * @param array<string, mixed> $attributes The attributes for the new model.
     * @return Model
     */
    public function make($attributes = array()): Model
    {
        $model = $this->get();
        if (empty($model)) {
            $model = $this->related->make($attributes);
            $attributes = array_merge($model->get_attributes(), $attributes);
        }
        return parent::make($attributes);
    }
}