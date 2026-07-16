<?php

namespace WooCommerceSerialNumbers\B8\Models;

defined('ABSPATH') || exit;
/**
 * Term model class.
 *
 * Handles taxonomy terms and their relationships.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class Term extends Model
{
    /**
     * The table associated with the model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $table = 'terms';
    /**
     * The primary key for the model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $primary_key = 'term_id';
    /**
     * Meta type declaration for the object.
     *
     * @since 1.0.0
     * @var string
     */
    protected $meta_type = 'term';
    /**
     * The table columns of the model.
     *
     * @since 1.0.0
     * @var array<int, string>
     */
    protected $columns = array('term_id', 'name', 'slug', 'term_group');
    /**
     * The model's attributes.
     *
     * @since 1.0.0
     * @var array<string, mixed>
     */
    protected $attributes = array();
    /**
     * Bootstrap the model's attributes and settings.
     *
     * @return void
     */
    protected function initialize()
    {
        parent::initialize();
        $this->aliases = array_merge($this->aliases, array('id' => 'term_id', 'term' => 'name'));
        $this->searchable = array_merge($this->searchable, array('name', 'slug'));
        $this->set_casts(array('term_id' => 'integer', 'term_group' => 'integer'));
        $this->set_rules(array('name' => 'required', 'slug' => 'required'));
    }
}