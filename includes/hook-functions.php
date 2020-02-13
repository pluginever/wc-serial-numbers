<?php
defined( 'ABSPATH' ) || exit();

function wcsn_maybe_hide_software_related_columns($columns){
	if(wcsn_software_disabled()){
		unset($columns['activation_limit']);
		unset($columns['activation_count']);
		unset($columns['validity']);
	}
	return $columns;
}
add_filter('serial_numbers_serials_table_columns', 'wcsn_maybe_hide_software_related_columns');
