<?php

namespace WooCommerceSerialNumbers\B8\Models\Relations;

use WooCommerceSerialNumbers\B8\Models\Model;
use WooCommerceSerialNumbers\B8\Models\Query;
use WooCommerceSerialNumbers\B8\Models\Utilities\StringUtil;
defined('ABSPATH') || exit;
/**
 * Base relation class.
 *
 * Provides foundation for model relationship handling.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
abstract class Relation
{
    /**
     * The parent model instance.
     *
     * @since 1.0.0
     * @var Model
     */
    protected $parent;
    /**
     * The related model instance.
     *
     * @since 1.0.0
     * @var Model|null
     */
    protected $related;
    /**
     * The query builder instance.
     *
     * @since 1.0.0
     * @var Query|null
     */
    protected $query;
    /**
     * Create a new relation instance.
     *
     * @since 1.0.0
     * @param Model      $_parent The parent model instance.
     * @param Model|null $related The related model instance.
     */
    public function __construct($_parent, $related)
    {
        $this->parent = $_parent;
        $this->related = $related;
        $this->query = $related ? $related->new_query() : null;
        $this->set_query_constraints();
    }
    /**
     * Handle dynamic method calls to the relationship.
     *
     * @since 1.0.0
     * @param string            $method The method name.
     * @param array<int, mixed> $args  The method arguments.
     * @return mixed
     * @throws \BadMethodCallException If the method does not exist.
     */
    public function __call($method, $args)
    {
        if (method_exists($this->query, $method)) {
            $result = $this->query->{$method}(...$args);
            if ($result === $this->query) {
                return $this;
            }
            return $result;
        }
        throw new \BadMethodCallException(esc_html(sprintf('Call to undefined method %s::%s()', static::class, $method)));
    }
    /**
     * Get the results of the relationship.
     *
     * @since 1.0.0
     * @return mixed
     */
    abstract public function get();
    /**
     * Set the base constraints on the relation query.
     *
     * @since 1.0.0
     * @return void
     */
    abstract protected function set_query_constraints(): void;
    /**
     * Apply constraints for model creation.
     *
     * @since 1.0.0
     * @param Model $model The model being created.
     * @return Model The modified model.
     */
    abstract protected function set_foreign_attributes($model): Model;
    /**
     * Eager load the relation for given models.
     *
     * @since 1.0.0
     * @param array<int, Model> $models   The array of parent models.
     * @param string            $relation The relation name.
     * @return void
     */
    abstract public function load($models, $relation): void;
    /**
     * Match the eagerly loaded results to their parents.
     *
     * @since 1.0.0
     * @param array<int, Model> $models   The array of parent models.
     * @param array<int, mixed> $results  The array of related results.
     * @param string            $relation The relation name.
     * @return array<int, Model> The parent models with the relation set.
     */
    abstract protected function match($models, $results, $relation): array;
    /**
     * Determine if any related models exist.
     *
     * @since 1.0.0
     * @return bool
     */
    public function exists(): bool
    {
        return $this->query->count() > 0;
    }
    /**
     * Get the count of related models.
     *
     * @since 1.0.0
     * @return int
     */
    public function count(): int
    {
        $this->query->set_var('count', true);
        $count = $this->query->get();
        $this->query->set_var('count', false);
        return $count;
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
        $model = $this->related->make($attributes);
        return $this->set_foreign_attributes($model);
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
        $model = $this->make($attributes);
        return $this->save($model);
    }
    /**
     * Save a new model and set the relationship constraints.
     *
     * @since 1.0.0
     * @param Model $model The model to save.
     * @return Model|\WP_Error
     */
    public function save($model)
    {
        $model = $this->set_foreign_attributes($model);
        $result = $model->save();
        if (is_wp_error($result)) {
            return $result;
        }
        return $model;
    }
    /**
     * Delete the related model.
     *
     * @since 1.0.0
     * @return void
     */
    public function delete()
    {
        $items = $this->get();
        $items = is_array($items) ? $items : array($items);
        foreach ($items as $item) {
            if (is_subclass_of($item, Model::class) && $item->exists()) {
                $item->delete();
            }
        }
    }
    /**
     * Create multiple related models and save them to the database.
     *
     * @since 1.0.0
     * @param array<int, array<string, mixed>> $records The array of attribute arrays for the new models.
     * @return array<int, Model>|\WP_Error Array of created models or WP_Error on failure.
     */
    public function insert_many($records)
    {
        $models = array();
        foreach ($records as $attributes) {
            $model = $this->insert($attributes);
            if (is_wp_error($model)) {
                return $model;
            }
            $models[] = $model;
        }
        return $models;
    }
    /**
     * Save multiple models and set the relationship constraints.
     *
     * @since 1.0.0
     * @param array<int, Model> $models The array of models to save.
     * @return array<int, Model>|\WP_Error Array of saved models or WP_Error on failure.
     */
    public function save_many($models)
    {
        $saved = array();
        foreach ($models as $model) {
            $result = $this->save($model);
            if (is_wp_error($result)) {
                return $result;
            }
            $saved[] = $result;
        }
        return $saved;
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
        // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
        _doing_it_wrong(__METHOD__, 'Method not supported by this relation type', '1.0.0');
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
        // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
        _doing_it_wrong(__METHOD__, 'Method not supported by this relation type', '1.0.0');
        return $this->parent;
    }
    /**
     * Sync the intermediate tables with a list of IDs or models.
     *
     * @since 1.0.0
     * @param array<int, int> $ids The IDs to sync.
     * @return array<string, mixed>
     */
    public function sync($ids): array
    {
        // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
        _doing_it_wrong(__METHOD__, 'Method not supported by this relation type', '1.0.0');
        return array('attached' => array(), 'detached' => array(), 'updated' => array());
    }
    /**
     * Toggle the intermediate tables with a list of IDs or models.
     *
     * @since 1.0.0
     * @param array<int, int>|int $ids The IDs to toggle.
     * @return array<string, mixed>
     */
    public function toggle($ids): array
    {
        // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
        _doing_it_wrong(__METHOD__, 'Method not supported by this relation type', '1.0.0');
        return array('attached' => array(), 'detached' => array());
    }
    /**
     * Qualify a column name with the table name.
     *
     * @since 1.0.0
     *
     * @param string      $column Column name.
     * @param string|null $table Optional table name. Defaults to related model's table.
     *
     * @return string Qualified column name.
     */
    protected function qualify_column($column, $table = null): string
    {
        if (empty($table)) {
            $table = $this->related->get_table();
        }
        return str_contains($column, '.') ? $column : $table . '.' . $column;
    }
    /**
     * Get the name of the relationship.
     *
     * @since 1.0.0
     * @return string
     */
    protected function get_relation_name(): string
    {
        $name = $this->related->get_object_type();
        return StringUtil::singularize($name);
    }
}