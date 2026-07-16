<?php

namespace WooCommerceSerialNumbers\B8\Models;

defined('ABSPATH') || exit;
/**
 * Page model class.
 *
 * Manages WordPress page content and metadata.
 *
 * @since 1.0.0
 * @package \B8\Models
 */
class Page extends Post
{
    /**
     * Post type.
     *
     * @since 1.0.0
     * @var string
     */
    protected $post_type = 'page';
}