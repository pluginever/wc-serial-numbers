<?php

namespace WooCommerceSerialNumbers\B8\Models\Relations;

use WooCommerceSerialNumbers\B8\Models\Model;
defined('ABSPATH') || exit;
/**
 * MorphToMany relation class.
 *
 * Handles polymorphic many-to-many relationships using pivot tables.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class MorphToMany extends Relation
{
    /**
     * The pivot table name (without prefix).
     *
     * @since 1.0.0
     * @var string
     */
    protected $pivot_table;
    /**
     * The morph type column name.
     *
     * @since 1.0.0
     * @var string
     */
    protected $morph_type;
    /**
     * The morph ID column name.
     *
     * @since 1.0.0
     * @var string
     */
    protected $morph_id;
    /**
     * The foreign key in pivot pointing to related model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $related_pivot_key;
    /**
     * The parent key for the relationship.
     *
     * @since 1.0.0
     * @var string
     */
    protected $parent_key;
    /**
     * The key on the related model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $related_key;
    /**
     * Create a new morph to many relationship instance.
     *
     * @since 1.0.0
     * @param Model  $_parent           The parent model instance.
     * @param Model  $related           The related model instance.
     * @param string $pivot_table       The pivot table name without prefix.
     * @param string $morph_name        The morph name prefix.
     * @param string $related_pivot_key The foreign key to related model in pivot.
     * @param string $parent_key        The parent key on parent model.
     * @param string $related_key       The key on related model.
     */
    public function __construct($_parent, $related, $pivot_table, $morph_name, $related_pivot_key, $parent_key, $related_key)
    {
        $this->pivot_table = $pivot_table;
        $this->morph_type = $morph_name . '_type';
        $this->morph_id = $morph_name . '_id';
        $this->related_pivot_key = $related_pivot_key;
        $this->parent_key = $parent_key;
        $this->related_key = $related_key;
        parent::__construct($_parent, $related);
    }
    /**
     * Get the results of the relationship.
     *
     * @since 1.0.0
     * @return array<int, \WooCommerceSerialNumbers\B8\Models\Model> The related models.
     */
    public function get(): array
    {
        $parent_key_value = $this->parent->get($this->parent_key);
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
        $this->build_pivot_join();
        $pivot_table = $this->pivot_table;
        $this->query->where($this->qualify_column($this->morph_type, $pivot_table), '=', $this->parent->get_object_type());
        $this->query->where($this->qualify_column($this->morph_id, $pivot_table), '=', $this->parent->get($this->parent_key));
        $this->query->where($this->qualify_column($this->morph_id, $pivot_table), 'IS NOT NULL', null);
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
     * Delete the relationship (detach all).
     *
     * For polymorphic many-to-many relations, delete() removes the pivot rows
     * without deleting the related models, since they may be shared with other
     * parents.
     *
     * @since 1.0.0
     * @return void
     */
    public function delete()
    {
        $this->detach(null);
    }
    /**
     * Create a new related model and attach it.
     *
     * @since 1.0.0
     * @param array<string, mixed> $attributes The attributes for the new model.
     * @return Model|\WP_Error The created model, or a WP_Error on failure.
     */
    public function insert($attributes = array())
    {
        $model = $this->make($attributes);
        $result = $model->save();
        if (is_wp_error($result)) {
            return $result;
        }
        $this->attach($model);
        return $model;
    }
    /**
     * Save a model and attach it to the parent.
     *
     * @since 1.0.0
     * @param Model $model The model to save and attach.
     * @return Model|\WP_Error The saved model, or a WP_Error on failure.
     */
    public function save($model)
    {
        $result = $model->save();
        if (is_wp_error($result)) {
            return $result;
        }
        $this->attach($model);
        return $model;
    }
    /**
     * Load the relation for given models (eager loading).
     *
     * @since 1.0.0
     * @param array<int, \WooCommerceSerialNumbers\B8\Models\Model> $models   The array of parent models.
     * @param string                       $relation The relation name.
     * @return void
     */
    public function load($models, $relation): void
    {
        global $wpdb;
        if (empty($models)) {
            return;
        }
        $parent_ids = wp_list_pluck($models, $this->parent_key);
        $parent_ids = array_filter(array_unique($parent_ids));
        if (empty($parent_ids)) {
            return;
        }
        $parent_type = $models[0]->get_object_type();
        $table_name = $wpdb->prefix . $this->pivot_table;
        $placeholders = implode(',', array_fill(0, count($parent_ids), '%d'));
        $pivots = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM `{$table_name}` WHERE `{$this->morph_type}` = %s AND `{$this->morph_id}` IN ({$placeholders})",
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            array_merge(array($parent_type), $parent_ids)
        ));
        if (empty($pivots)) {
            foreach ($models as $model) {
                $model->set_relation($relation, array());
            }
            return;
        }
        $related_ids = array_unique(wp_list_pluck($pivots, $this->related_pivot_key));
        $results = $this->related->new_query()->where($this->related_key, 'IN', $related_ids)->get();
        $related_by_id = array();
        foreach ($results as $result) {
            $related_by_id[$result->get($this->related_key)] = $result;
        }
        $dictionary = array();
        foreach ($pivots as $pivot) {
            $pid = $pivot->{$this->morph_id};
            $rid = $pivot->{$this->related_pivot_key};
            if (isset($related_by_id[$rid])) {
                $dictionary[$pid][] = $related_by_id[$rid];
            }
        }
        foreach ($models as $model) {
            $model->set_relation($relation, $dictionary[$model->get($this->parent_key)] ?? array());
        }
    }
    /**
     * Match the eagerly loaded results to their parents.
     *
     * Not used - load() handles matching directly.
     *
     * @since 1.0.0
     * @param array<int, \WooCommerceSerialNumbers\B8\Models\Model> $models   The array of parent models.
     * @param array<int, mixed>            $results  The array of related results.
     * @param string                       $relation The relation name.
     * @return array<int, \WooCommerceSerialNumbers\B8\Models\Model> The parent models.
     */
    protected function match($models, $results, $relation): array
    {
        return $models;
    }
    /**
     * Attach models to the parent.
     *
     * @since 1.0.0
     * @param Model|int|array<int, \WooCommerceSerialNumbers\B8\Models\Model|int> $ids The model instance(s) or ID(s) to attach.
     * @return Model The parent model.
     */
    public function attach($ids): Model
    {
        $ids = is_array($ids) ? $ids : array($ids);
        foreach ($ids as $id) {
            $this->attach_to_pivot($id);
        }
        $this->related->flush_cache();
        return $this->parent;
    }
    /**
     * Detach models from the parent.
     *
     * @since 1.0.0
     * @param Model|int|array<int, \WooCommerceSerialNumbers\B8\Models\Model|int>|null $ids The model instance(s) or ID(s) to detach.
     * @return Model The parent model.
     */
    public function detach($ids = null): Model
    {
        $this->detach_from_pivot($ids);
        $this->related->flush_cache();
        return $this->parent;
    }
    /**
     * Sync the relationship with a list of IDs.
     *
     * @since 1.0.0
     * @param array<int, int> $ids The IDs to sync.
     * @return array<string, array<int, int>> Array with attached and detached IDs.
     */
    public function sync($ids): array
    {
        $ids = array_map('intval', $ids);
        $current = $this->get_current_pivot_ids();
        $attach_ids = array_diff($ids, $current);
        $detach_ids = array_diff($current, $ids);
        foreach ($attach_ids as $id) {
            $this->attach_to_pivot($id);
        }
        if (!empty($detach_ids)) {
            $this->detach_from_pivot($detach_ids);
        }
        $this->related->flush_cache();
        return array('attached' => array_values($attach_ids), 'detached' => array_values($detach_ids));
    }
    /**
     * Toggle the attachment status of given IDs.
     *
     * @since 1.0.0
     * @param array<int, int>|int $ids The IDs to toggle.
     * @return array<string, array<int, int>> Array with attached and detached IDs.
     */
    public function toggle($ids): array
    {
        $ids = is_array($ids) ? $ids : array($ids);
        $ids = array_map('intval', $ids);
        $current = $this->get_current_pivot_ids();
        $attached = array();
        $detached = array();
        foreach ($ids as $id) {
            if (in_array($id, $current, true)) {
                $this->detach_from_pivot($id);
                $detached[] = $id;
            } else {
                $this->attach_to_pivot($id);
                $attached[] = $id;
            }
        }
        $this->related->flush_cache();
        return array('attached' => $attached, 'detached' => $detached);
    }
    /**
     * Build the pivot join clause.
     *
     * @since 1.0.0
     * @return void
     */
    protected function build_pivot_join(): void
    {
        $pivot_table = $this->pivot_table;
        $related_table = $this->related->get_table();
        $this->query->join($pivot_table, $this->qualify_column($this->related_pivot_key, $pivot_table), '=', $this->qualify_column($this->related_key, $related_table));
    }
    /**
     * Attach to pivot table.
     *
     * @param Model|int $id The model instance or ID.
     *
     * @since 1.0.0
     * @return void
     */
    protected function attach_to_pivot($id): void
    {
        global $wpdb;
        $related_id = $id instanceof Model ? $id->get($this->related_key) : $id;
        $parent_id = $this->parent->get($this->parent_key);
        if (empty($related_id) || empty($parent_id)) {
            return;
        }
        $wpdb->insert($wpdb->prefix . $this->pivot_table, array($this->related_pivot_key => $related_id, $this->morph_id => $parent_id, $this->morph_type => $this->parent->get_object_type()));
    }
    /**
     * Detach from pivot table.
     *
     * @since 1.0.0
     * @param Model|int|array<int, \WooCommerceSerialNumbers\B8\Models\Model|int>|null $ids The model instances or IDs to detach.
     * @return void
     */
    protected function detach_from_pivot($ids = null): void
    {
        global $wpdb;
        $parent_id = $this->parent->get($this->parent_key);
        $parent_type = $this->parent->get_object_type();
        $table_name = $wpdb->prefix . $this->pivot_table;
        if (is_null($ids)) {
            $wpdb->delete($table_name, array($this->morph_id => $parent_id, $this->morph_type => $parent_type));
            return;
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        foreach ($ids as $id) {
            $related_id = $id instanceof Model ? $id->get($this->related_key) : $id;
            $wpdb->delete($table_name, array($this->related_pivot_key => $related_id, $this->morph_id => $parent_id, $this->morph_type => $parent_type));
        }
    }
    /**
     * Get current pivot table IDs for the parent.
     *
     * @since 1.0.0
     * @return array<int, int> The related model IDs currently in the pivot table.
     */
    protected function get_current_pivot_ids(): array
    {
        global $wpdb;
        $parent_id = $this->parent->get($this->parent_key);
        $parent_type = $this->parent->get_object_type();
        $table_name = $wpdb->prefix . $this->pivot_table;
        $results = $wpdb->get_col($wpdb->prepare(
            "SELECT `{$this->related_pivot_key}` FROM `{$table_name}` WHERE `{$this->morph_id}` = %d AND `{$this->morph_type}` = %s",
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $parent_id,
            $parent_type
        ));
        return empty($results) ? array() : array_map('intval', $results);
    }
}