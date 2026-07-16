<?php

namespace WooCommerceSerialNumbers\B8\Models;

defined('ABSPATH') || exit;
/**
 * Post model class.
 *
 * Handles WordPress post data and operations.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class Post extends Model
{
    /**
     * The table associated with the model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $table = 'posts';
    /**
     * Post type.
     *
     * @since 1.0.0
     * @var string
     */
    protected $post_type = 'post';
    /**
     * Meta type declaration for the object.
     *
     * @since 1.0.0
     * @var string
     */
    protected $meta_type = 'post';
    /**
     * The primary key for the model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $primary_key = 'ID';
    /**
     * The table columns of the model.
     *
     * @since 1.0.0
     * @var array<int, string>
     */
    protected $columns = array('ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count');
    /**
     * The model's attributes.
     *
     * @example ['name' => 'Jane Smith', 'email' => 'jane@example.com']
     * @example ['title' => 'Account Manager', 'salary' => 75000]
     *
     * @since 1.0.0
     * @var array<string, mixed>
     */
    protected $attributes = array();
    /**
     * The name of the "created at" column.
     *
     * @since 1.0.0
     * @var string
     */
    const CREATED_AT = 'post_date';
    /**
     * The name of the "updated at" column.
     *
     * @since 1.0.0
     * @var string
     */
    const UPDATED_AT = 'post_modified';
    /**
     * The name of the "created_by" column.
     *
     * @since 1.0.0
     * @var string
     */
    const CREATED_BY = 'post_author';
    /**
     * Initialize the model instance.
     *
     * @since 1.0.0
     * @return void
     */
    protected function initialize()
    {
        parent::initialize();
        $this->attributes = array_merge($this->attributes, array('post_type' => $this->post_type, 'post_status' => 'publish', 'comment_status' => 'open', 'ping_status' => 'open'));
        $this->aliases = array_merge($this->aliases, array('id' => 'ID', 'created_by' => 'post_author', 'created_at' => 'post_date', 'updated_at' => 'post_modified', 'date' => 'post_date', 'date_gmt' => 'post_date_gmt', 'content' => 'post_content', 'title' => 'post_title', 'excerpt' => 'post_excerpt', 'status' => 'post_status'));
        $this->query_vars = array_merge($this->query_vars, array('post_type' => $this->post_type));
        $this->searchable = array_merge($this->searchable, array('post_title', 'post_content', 'post_excerpt'));
        $this->set_casts(array('ID' => 'int', 'post_author' => 'int', 'post_parent' => 'int', 'menu_order' => 'int', 'comment_count' => 'int', 'post_date' => 'datetime', 'post_date_gmt' => 'datetime', 'post_modified' => 'datetime', 'post_modified_gmt' => 'datetime'));
        $this->set_rules(array('post_title' => 'required'));
    }
    /**
     * Post belongs to a user (author).
     *
     * @return \WooCommerceSerialNumbers\B8\Models\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongs_to(User::class, 'post_author');
    }
    /**
     * Post belongs to a user (author) - alias method.
     *
     * @return \WooCommerceSerialNumbers\B8\Models\Relations\BelongsTo
     */
    public function author()
    {
        return $this->user();
    }
}