<?php
//function prefix wc_serial_numbers

/*
 * Get Plugin directory templates part
 * */

function wsn_get_template_part($template_name){
	return include WPWSN_TEMPLATES_DIR.'/'.$template_name.'.php';
}


/*
 * Register Serial Numbers Post Type
 * */

add_action( 'init', 'wsn_register_posttypes' );

function wsn_register_posttypes(){
	register_post_type( 'serial_number', array(
		'labels'              => 'Serial Numbers',
		'hierarchical'        => false,
		'supports'            => array( 'title' ),
		'public'              => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'query_var'           => false,
		'can_export'          => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'capabilities' => array(
			'create_posts' => 'do_not_allow', // false < WP 4.5, credit @Ewout
		),
		'map_meta_cap' => true,
	) );
}
