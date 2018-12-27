<?php
//function prefix wc_serial_numbers

/*
 * Get Plugin directory templates part
 * */

function wsn_get_template_part($template_name){
	return include WPWSN_TEMPLATES_DIR.'/'.$template_name.'.php';
}

