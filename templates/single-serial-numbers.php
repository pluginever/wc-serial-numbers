<?php

include WPWSN_INCLUDES . '/class-single-serial-list-table.php';

$serial_list = new Pluginever\WCSerialNumbers\Single_List_Table();

$serial_list->prepare_items();

?>
<h3><?php _e( 'Available license number for this product:', 'wc-serial-numbers' ) ?> </h3>
<?php
$serial_list->display();
