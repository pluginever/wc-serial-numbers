<?php

namespace WooCommerceSerialNumbers\B8\Models\Relations;

use WooCommerceSerialNumbers\B8\Models\Model;
defined('ABSPATH') || exit;
/**
 * BelongsToMany relation class.
 *
 * Handles many-to-many relationships using either array storage or pivot tables.
 * Array storage for small relations (5-10 items), pivot tables for larger ones.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class BelongsToMany extends Relation
{
    /**
     * The foreign key column that stores serialized IDs (array storage).
     *
     * @since 1.0.0
     * @var string|null
     */
    protected $foreign_key;
    /**
     * The related key on the related model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $related_key;
    /**
     * Whether to use pivot table instead of array storage.
     *
     * @since 1.0.0
     * @var bool
     */
    protected $use_pivot_table = false;
    /**
     * The intermediate table for pivot relations.
     *
     * @since 1.0.0
     * @var string|null
     */
    protected $pivot_table = null;
    /**
     * The foreign key of the parent model in pivot table.
     *
     * @since 1.0.0
     * @var string|null
     */
    protected $foreign_pivot_key = null;
    /**
     * The associated key of the relation in pivot table.
     *
     * @since 1.0.0
     * @var string|null
     */
    protected $related_pivot_key = null;
    /**
     * The parent key for the relationship.
     *
     * @since 1.0.0
     * @var string
     */
    protected $parent_key;
    /**
     * Create a new belongs to many relationship instance.
     *
     * @since 1.0.0
     * @param Model       $_parent The parent model instance.
     * @param Model       $related The related model instance.
     * @param string      $foreign_key The foreign key for array storage or pivot table name.
     * @param string|null $related_key The related key.
     * @param string|null $foreign_pivot_key The foreign pivot key (pivot mode only).
     * @param string|null $related_pivot_key The related pivot key (pivot mode only).
     * @param string|null $parent_key The parent key.
     */
    public function __construct($_parent, $related, $foreign_key, $related_key = null, $foreign_pivot_key = null, $related_pivot_key = null, $parent_key = null)
    {
        $this->related_key = $related_key ?? $related->get_primary_key();
        $this->parent_key = $parent_key ?? $_parent->get_primary_key();
        if (!empty($foreign_pivot_key) && !empty($related_pivot_key)) {
            $this->use_pivot_table = true;
            $this->pivot_table = $foreign_key;
            $this->foreign_pivot_key = $foreign_pivot_key;
            $this->related_pivot_key = $related_pivot_key;
            $this->foreign_key = null;
        } else {
            $this->use_pivot_table = false;
            $this->foreign_key = $foreign_key;
            $this->pivot_table = null;
        }
        parent::__construct($_parent, $related);
    }
    /**
     * Get the results of the relationship.
     *
     * @since 1.0.0
     * @return array<int, Model>
     */
    public function get(): array
    {
        return $this->use_pivot_table ? $this->get_pivot_results() : $this->get_array_results();
    }
    /**
     * Set the base constraints on the relation query.
     *
     * @since 1.0.0
     * @return void
     */
    protected function set_query_constraints(): void
    {
        if ($this->use_pivot_table) {
            $this->set_pivot_constraints();
        } else {
            $this->set_array_constraints();
        }
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
     * For many-to-many relations, delete() removes the association (pivot rows
     * or array entries) without deleting the related models, since they may be
     * shared with other parents.
     *
     * @since 1.0.0
     * @return void
     */
    public function delete()
    {
        $this->detach(null);
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
        return $this->related->new_instance($attributes);
    }
    /**
     * Create a new related model and save it to the database, then attach it.
     *
     * @since 1.0.0
     * @param array<string, mixed> $attributes The attributes for the new model.
     * @return Model|\WP_Error
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
     * @return Model|\WP_Error
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
     * Load the relation for given models.
     *
     * @since 1.0.0
     * @param array<int, Model> $models The array of parent models.
     * @param string            $relation The relation name.
     * @return void
     */
    public function load($models, $relation): void
    {
        if (empty($models)) {
            return;
        }
        if ($this->use_pivot_table) {
            $this->load_pivot($models, $relation);
            return;
        }
        $results = $this->get_array_eager_results($models);
        $this->match_array_results($models, $results, $relation);
    }
    /**
     * Load pivot table results for given models.
     *
     * Queries the pivot table directly to get parent-to-related mappings,
     * then fetches related models and builds the dictionary.
     *
     * @since 1.0.0
     * @param array<int, Model> $models The array of parent models.
     * @param string            $relation The relation name.
     * @return void
     */
    protected function load_pivot($models, $relation): void
    {
        global $wpdb;
        $parent_keys = wp_list_pluck($models, $this->parent_key);
        $parent_keys = array_filter(array_unique($parent_keys));
        if (empty($parent_keys)) {
            return;
        }
        $table_name = $wpdb->prefix . $this->pivot_table;
        $placeholders = implode(',', array_fill(0, count($parent_keys), '%d'));
        $pivots = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM `{$table_name}` WHERE `{$this->foreign_pivot_key}` IN ({$placeholders})",
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
            $parent_keys
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
            $pid = $pivot->{$this->foreign_pivot_key};
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
     * @since 1.0.0
     * @param array<int, Model> $models The array of parent models.
     * @param array<int, Model> $results The array of related results.
     * @param string            $relation The relation name.
     * @return array<int, Model>
     */
    protected function match($models, $results, $relation): array
    {
        return $this->match_array_results($models, $results, $relation);
    }
    /**
     * Attach a model to the parent.
     *
     * @since 1.0.0
     * @param Model|int            $id The model instance or ID to attach.
     * @param array<string, mixed> $pivot_attributes Additional pivot attributes (pivot only).
     * @return Model
     */
    public function attach($id, $pivot_attributes = array()): Model
    {
        if ($this->use_pivot_table) {
            $this->attach_to_pivot($id, $pivot_attributes);
            $this->related->flush_cache();
        } else {
            $this->attach_to_array($id);
        }
        return $this->parent;
    }
    /**
     * Detach a model from the parent.
     *
     * @since 1.0.0
     * @param Model|int|array<int, Model|int>|null $ids The model instances or IDs to detach.
     * @return Model
     */
    public function detach($ids = null): Model
    {
        if ($this->use_pivot_table) {
            $this->detach_from_pivot($ids);
            $this->related->flush_cache();
        } else {
            $this->detach_from_array($ids);
        }
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
        if ($this->use_pivot_table) {
            return $this->sync_pivot($ids);
        } else {
            return $this->sync_array($ids);
        }
    }
    /**
     * Toggle the attachment status of given IDs.
     *
     * @since 1.0.0
     * @param array<int, int>|int|string $ids The IDs to toggle.
     * @return array<string, array<int, int>> Array with attached and detached IDs.
     */
    public function toggle($ids): array
    {
        $ids = is_array($ids) ? $ids : wp_parse_id_list($ids);
        if ($this->use_pivot_table) {
            return $this->toggle_pivot($ids);
        } else {
            return $this->toggle_array($ids);
        }
    }
    /**
     * Get array storage results.
     *
     * @since 1.0.0
     * @return array<int, Model>
     */
    protected function get_array_results(): array
    {
        $parent_key_value = $this->parent->get($this->foreign_key);
        if (empty($parent_key_value)) {
            return array();
        }
        $results = $this->query->get();
        return is_array($results) ? $results : array();
    }
    /**
     * Get pivot table results.
     *
     * @since 1.0.0
     * @return array<int, Model>
     */
    protected function get_pivot_results(): array
    {
        $parent_key_value = $this->parent->get($this->parent_key);
        if (empty($parent_key_value)) {
            return array();
        }
        $results = $this->query->get();
        return is_array($results) ? $results : array();
    }
    /**
     * Set array storage constraints.
     *
     * @since 1.0.0
     * @return void
     */
    protected function set_array_constraints()
    {
        $this->query->where($this->related_key, 'IN', $this->parent->get($this->foreign_key));
        $this->query->where($this->related_key, 'IS NOT NULL', null);
    }
    /**
     * Set pivot table constraints.
     *
     * @since 1.0.0
     * @return void
     */
    protected function set_pivot_constraints()
    {
        $this->build_pivot_join();
        $this->query->where($this->qualify_column($this->foreign_pivot_key, $this->pivot_table), '=', $this->parent->get($this->parent_key));
        $this->query->where($this->qualify_column($this->foreign_pivot_key, $this->pivot_table), 'IS NOT NULL', null);
    }
    /**
     * Build the pivot join clause.
     *
     * @since 1.0.0
     * @return void
     */
    protected function build_pivot_join()
    {
        $pivot_table = $this->pivot_table;
        $related_table = $this->related->get_table();
        $this->query->join($pivot_table, $this->qualify_column($this->related_pivot_key, $pivot_table), '=', $this->qualify_column($this->related_key, $related_table));
    }
    /**
     * Get array storage eager results.
     *
     * @since 1.0.0
     * @param array<int, Model> $models The array of parent models.
     * @return array<int, Model>
     */
    protected function get_array_eager_results($models): array
    {
        $related_ids = array();
        foreach ($models as $model) {
            $ids = $model->get($this->foreign_key);
            if (!empty($ids)) {
                $ids = is_array($ids) ? $ids : wp_parse_id_list($ids);
                $related_ids = array_merge($related_ids, $ids);
            }
        }
        $related_ids = array_filter(array_unique($related_ids));
        if (empty($related_ids)) {
            return array();
        }
        return $this->related->new_query()->where($this->related_key, 'IN', $related_ids)->get();
    }
    /**
     * Match array storage results.
     *
     * @since 1.0.0
     * @param array<int, Model> $models The array of parent models.
     * @param array<int, Model> $results The array of related results.
     * @param string            $relation The relation name.
     * @return array<int, Model>
     */
    protected function match_array_results($models, $results, $relation): array
    {
        $dictionary = array();
        foreach ($results as $result) {
            $key = $result->get($this->related_key);
            $dictionary[$key] = $result;
        }
        foreach ($models as $model) {
            $ids = $model->get($this->foreign_key);
            $matched = array();
            $ids = is_array($ids) ? $ids : wp_parse_id_list($ids);
            foreach ($ids as $id) {
                if (isset($dictionary[$id])) {
                    $matched[] = $dictionary[$id];
                }
            }
            $model->set_relation($relation, $matched);
        }
        return $models;
    }
    /**
     * Attach to array storage.
     *
     * @since 1.0.0
     * @param Model|int $id The model instance or ID.
     * @return void
     */
    protected function attach_to_array($id)
    {
        if ($id instanceof Model) {
            if (!$id->exists()) {
                return;
            }
            $id = $id->get($this->related_key);
        }
        if (empty($id)) {
            return;
        }
        $current_ids = $this->parent->get($this->foreign_key);
        $current_ids = is_array($current_ids) ? $current_ids : wp_parse_id_list($current_ids);
        if (!in_array($id, $current_ids, true)) {
            $current_ids[] = $id;
            $this->parent->set($this->foreign_key, $current_ids);
        }
    }
    /**
     * Attach to pivot table.
     *
     * @since 1.0.0
     * @param Model|int            $id The model instance or ID.
     * @param array<string, mixed> $attributes Additional pivot attributes.
     * @return void
     */
    protected function attach_to_pivot($id, $attributes = array())
    {
        global $wpdb;
        $id = $id instanceof Model ? $id->get($this->related_key) : $id;
        $record = array_merge(array($this->foreign_pivot_key => $this->parent->get($this->parent_key), $this->related_pivot_key => $id), $attributes);
        $wpdb->insert($wpdb->prefix . $this->pivot_table, $record);
    }
    /**
     * Detach from array storage.
     *
     * @since 1.0.0
     * @param Model|int|array<int, Model|int>|null $ids The model instances or IDs to detach.
     * @return void
     */
    protected function detach_from_array($ids = null)
    {
        $current_ids = $this->parent->get($this->foreign_key);
        $current_ids = is_array($current_ids) ? $current_ids : wp_parse_id_list($current_ids);
        if (is_null($ids)) {
            $this->parent->set($this->foreign_key, array());
            return;
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $detach_ids = array();
        foreach ($ids as $id) {
            $detach_ids[] = $id instanceof Model ? $id->get($this->related_key) : $id;
        }
        $new_ids = array_diff($current_ids, $detach_ids);
        $this->parent->set($this->foreign_key, array_values($new_ids));
    }
    /**
     * Detach from pivot table.
     *
     * @since 1.0.0
     * @param Model|int|array<int, Model|int>|null $ids The model instances or IDs to detach.
     * @return void
     */
    protected function detach_from_pivot($ids = null)
    {
        global $wpdb;
        $parent_id = $this->parent->get($this->parent_key);
        if (is_null($ids)) {
            $wpdb->delete($wpdb->prefix . $this->pivot_table, array($this->foreign_pivot_key => $parent_id));
            return;
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $related_ids = array();
        foreach ($ids as $id) {
            $related_ids[] = $id instanceof Model ? $id->get($this->related_key) : $id;
        }
        $table_name = $wpdb->prefix . $this->pivot_table;
        foreach ($related_ids as $related_id) {
            $wpdb->delete($table_name, array($this->foreign_pivot_key => $parent_id, $this->related_pivot_key => $related_id));
        }
    }
    /**
     * Sync array storage.
     *
     * @since 1.0.0
     * @param array<int, int> $ids The IDs to sync.
     * @return array<string, array<int, int>>
     */
    protected function sync_array($ids): array
    {
        $current_ids = $this->parent->get($this->foreign_key);
        $current_ids = is_array($current_ids) ? $current_ids : wp_parse_id_list($current_ids);
        $attach_ids = array_diff($ids, $current_ids);
        $detach_ids = array_diff($current_ids, $ids);
        $this->parent->set($this->foreign_key, $ids);
        return array('attached' => $attach_ids, 'detached' => $detach_ids);
    }
    /**
     * Sync pivot table.
     *
     * @since 1.0.0
     * @param array<int, int> $ids The IDs to sync.
     * @return array<string, array<int, int>>
     */
    protected function sync_pivot($ids): array
    {
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
        return array('attached' => $attach_ids, 'detached' => $detach_ids);
    }
    /**
     * Toggle array storage.
     *
     * @since 1.0.0
     * @param array<int, int> $ids The IDs to toggle.
     * @return array<string, array<int, int>>
     */
    protected function toggle_array($ids): array
    {
        $current_ids = $this->parent->get($this->foreign_key);
        $current_ids = is_array($current_ids) ? $current_ids : wp_parse_id_list($current_ids);
        $attached = array();
        $detached = array();
        foreach ($ids as $id) {
            if (in_array($id, $current_ids, true)) {
                $current_ids = array_diff($current_ids, array($id));
                $detached[] = $id;
            } else {
                $current_ids[] = $id;
                $attached[] = $id;
            }
        }
        $this->parent->set($this->foreign_key, array_values($current_ids));
        return array('attached' => $attached, 'detached' => $detached);
    }
    /**
     * Toggle pivot table.
     *
     * @since 1.0.0
     * @param array<int, int> $ids The IDs to toggle.
     * @return array<string, array<int, int>>
     */
    protected function toggle_pivot($ids): array
    {
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
     * Get current pivot table IDs.
     *
     * @since 1.0.0
     * @return array<int, int>
     */
    protected function get_current_pivot_ids(): array
    {
        global $wpdb;
        $parent_id = $this->parent->get($this->parent_key);
        $table_name = $wpdb->prefix . $this->pivot_table;
        $results = $wpdb->get_col($wpdb->prepare(
            "SELECT `{$this->related_pivot_key}` FROM `{$table_name}` WHERE `{$this->foreign_pivot_key}` = %s",
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $parent_id
        ));
        return empty($results) ? array() : array_map('intval', $results);
    }
}