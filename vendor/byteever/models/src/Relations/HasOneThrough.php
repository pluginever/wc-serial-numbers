<?php

namespace WooCommerceSerialNumbers\B8\Models\Relations;

use WooCommerceSerialNumbers\B8\Models\Model;
defined('ABSPATH') || exit;
/**
 * HasOneThrough relation class.
 *
 * Handles one-to-one relationships through an intermediate model.
 *
 * This relationship allows a parent model to access a related model through
 * an intermediate table/model, creating a chain: Parent → Intermediate → Related.
 *
 * @example
 * // Contact → Task → Deal relationship
 * // A contact can access their primary deal through tasks
 *
 * // Database Schema:
 * // wp_test_contacts: id (primary key)
 * // wp_test_tasks: id (primary key), related_contact_id (foreign key to contacts.id), related_deal_id (foreign key to deals.id)
 * // wp_test_deals: id (primary key)
 *
 * // Usage:
 * // $contact->primary_deal_through_tasks() returns the deal through tasks
 * //
 * // Generated SQL:
 * // SELECT deals.* FROM wp_test_deals deals
 * // INNER JOIN wp_test_tasks tasks ON tasks.related_deal_id = deals.id
 * // WHERE tasks.related_contact_id = {contact_id}
 * // LIMIT 1
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class HasOneThrough extends Relation
{
    /**
     * The intermediate model instance.
     *
     * @since 1.0.0
     * @var Model
     */
    protected $through;
    /**
     * The foreign key in the intermediate table that references the parent model.
     * Example: In Contact → Task → Deal, this would be 'related_contact_id' in tasks table.
     *
     * @since 1.0.0
     * @var string
     */
    protected $first_key;
    /**
     * The foreign key in the intermediate table that references the related model.
     * Example: In Contact → Task → Deal, this would be 'related_deal_id' in tasks table.
     *
     * @since 1.0.0
     * @var string
     */
    protected $second_key;
    /**
     * The primary key of the parent model.
     * Example: In Contact → Task → Deal, this would be 'id' in contacts table.
     *
     * @since 1.0.0
     * @var string
     */
    protected $local_key;
    /**
     * The primary key of the related model.
     * Example: In Contact → Task → Deal, this would be 'id' in deals table.
     *
     * @since 1.0.0
     * @var string
     */
    protected $second_local_key;
    /**
     * Intermediate table mapping of parent key to related key.
     *
     * Populated during load() for use by match(), because hydrate()
     * drops intermediate table columns from JOIN query results.
     *
     * @since 1.0.0
     * @var array<int, mixed>
     */
    protected $intermediate_mapping = array();
    /**
     * Create a new has one through relationship instance.
     *
     * @since 1.0.0
     * @param Model  $_parent The parent model instance (e.g., Contact).
     * @param Model  $related The related model instance (e.g., Deal).
     * @param Model  $through The intermediate model instance (e.g., Task).
     * @param string $first_key The foreign key in intermediate table that references parent model (e.g., 'related_contact_id' in tasks table).
     * @param string $second_key The foreign key in intermediate table that references related model (e.g., 'related_deal_id' in tasks table).
     * @param string $local_key The primary key of the parent model (e.g., 'id' in contacts table).
     * @param string $second_local_key The primary key of the related model (e.g., 'id' in deals table).
     */
    public function __construct($_parent, $related, $through, $first_key, $second_key, $local_key, $second_local_key)
    {
        $this->through = $through;
        $this->first_key = $first_key;
        $this->second_key = $second_key;
        $this->local_key = $local_key;
        $this->second_local_key = $second_local_key;
        parent::__construct($_parent, $related);
    }
    /**
     * Set the base constraints on the relation query.
     *
     * Builds the JOIN between the intermediate table and the related table,
     * then adds the WHERE clause to filter by the parent model's key.
     * The JOIN connects the foreign key in the intermediate table to the primary key in the related table.
     * The WHERE clause connects the foreign key in the intermediate table to the parent model's primary key.
     *
     * @since 1.0.0
     * @return void
     */
    protected function set_query_constraints(): void
    {
        $through_table = $this->through->get_table();
        $related_table = $this->related->get_table();
        $this->query->join($through_table, $this->qualify_column($this->second_key, $through_table), '=', $this->qualify_column($this->second_local_key, $related_table))->where($this->qualify_column($this->first_key, $this->through->get_table()), '=', $this->parent->get($this->local_key));
    }
    /**
     * Get the results of the relationship.
     *
     * @since 1.0.0
     * @return Model|object|null
     */
    public function get()
    {
        $parent_key_value = $this->parent->get($this->local_key);
        if (empty($parent_key_value)) {
            return null;
        }
        return $this->query->limit(1)->first();
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
        global $wpdb;
        $parent_keys = wp_list_pluck($models, $this->local_key);
        $parent_keys = array_filter($parent_keys);
        if (empty($parent_keys)) {
            return;
        }
        $unique_keys = array_unique($parent_keys);
        $through_table = $this->through->get_table();
        // Pre-fetch parent → related key mapping from intermediate table.
        // Hydration drops intermediate table columns from JOIN results,
        // so match() uses this mapping instead.
        $prefixed_table = $wpdb->prefix . $through_table;
        $placeholders = implode(',', array_fill(0, count($unique_keys), '%d'));
        $intermediates = $wpdb->get_results($wpdb->prepare(
            "SELECT `{$this->first_key}`, `{$this->second_key}` FROM `{$prefixed_table}` WHERE `{$this->first_key}` IN ({$placeholders})",
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
            $unique_keys
        ));
        if (empty($intermediates)) {
            return;
        }
        $this->intermediate_mapping = array();
        $related_keys = array();
        foreach ($intermediates as $row) {
            $this->intermediate_mapping[$row->{$this->first_key}] = $row->{$this->second_key};
            $related_keys[] = $row->{$this->second_key};
        }
        // Fetch related models.
        $results = $this->related->new_query()->where($this->second_local_key, 'IN', array_unique($related_keys))->get();
        $this->match($models, $results, $relation);
    }
    /**
     * Match the eagerly loaded results to their parents.
     *
     * Uses the intermediate mapping built by load() to pair
     * related models with their parent models.
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
            $dictionary[$result->get($this->second_local_key)] = $result;
        }
        foreach ($models as $model) {
            $parent_key = $model->get($this->local_key);
            $related_key = $this->intermediate_mapping[$parent_key] ?? null;
            if (null !== $related_key && isset($dictionary[$related_key])) {
                $model->set_relation($relation, $dictionary[$related_key]);
            }
        }
        return $models;
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
        return $model;
    }
}