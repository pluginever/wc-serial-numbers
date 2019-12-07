<?php
defined( 'ABSPATH' ) || exit();

/**
 * Register Post types
 *
 * @since 1.0.0
 */

function wcsn_register_post_types() {
	register_post_type( 'wcsn_notification', array(
		'labels'              => false,
		'hierarchical'        => false,
		'supports'            => false,
		'public'              => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'query_var'           => false,
		'can_export'          => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'capabilities'        => array(
			'create_posts' => 'do_not_allow',
		),
		'map_meta_cap'        => true,
	) );
}

add_action( 'init', 'wcsn_register_post_types' );
