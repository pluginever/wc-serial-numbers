<?php

if ( ! class_exists( 'WP_Background_Process', false ) ) {
    include_once WC_ABSPATH . '/includes/abstracts/class-wc-background-process.php';
}

class WCSN_Automatic_Notification extends WC_Background_Process {
    protected $action = 'wcsn_automatic_notification';

    protected function task( $product_id ) {
        $notification = wcsn_update_notification_list( false, $product_id );
		return false;
    }
    
    protected function complete() {
		parent::complete();
	}
}