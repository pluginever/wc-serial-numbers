<?php
//function prefix wc_serial_number_pro


/**
 * Active the Pro version=
 */
add_filter('is_wsnp', function ($status){
	return true;
});


add_filter('generate_serial_number', function (){
	include WPWSNP_TEMPLATES_DIR.'/generate-serial-number.php';
});
