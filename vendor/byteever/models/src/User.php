<?php

namespace WooCommerceSerialNumbers\B8\Models;

defined('ABSPATH') || exit;
/**
 * User model class.
 *
 * Manages user accounts and authentication data.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $table = 'users';
    /**
     * The primary key for the model.
     *
     * @since 1.0.0
     * @var string
     */
    protected $primary_key = 'ID';
    /**
     * Meta type declaration for the object.
     *
     * @since 1.0.0
     * @var string
     */
    protected $meta_type = 'user';
    /**
     * The model's attributes.
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
    const CREATED_AT = 'user_registered';
    /**
     * Initialize the model instance.
     *
     * @since 1.0.0
     * @return void
     */
    protected function initialize()
    {
        parent::initialize();
        $this->aliases = array_merge($this->aliases, array('id' => 'ID', 'login' => 'user_login', 'nicename' => 'user_nicename', 'email' => 'user_email', 'url' => 'user_url', 'created_at' => 'user_registered', 'registered' => 'user_registered', 'status' => 'user_status', 'display_name' => 'display_name'));
        $this->searchable = array_merge($this->searchable, array('user_login', 'user_email', 'display_name'));
        $this->set_casts(array('ID' => 'integer', 'user_registered' => 'datetime', 'user_status' => 'integer'));
        $this->set_rules(array('user_login' => 'required', 'user_email' => 'required|email'));
    }
    /**
     * User has many posts.
     *
     * @return \WooCommerceSerialNumbers\B8\Models\Relations\HasMany
     */
    public function posts()
    {
        return $this->has_many(Post::class, 'post_author');
    }
}