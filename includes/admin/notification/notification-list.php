<?php

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '<span class="ever-notification"><span class="alert">' . sprintf( '%02d', count( $ids ) ) . '</span></span><ul class="ever-notification-list alert">';

$message = '';

foreach ( $ids as $id ) {

	$id = intval($id->ID);

	$count = get_post_field('post_content', $id );

	$title = get_the_title( $id );

	//Check if the assigned product is published for the serial number
	if ( 'publish' != get_post_status( $title ) ) {

		if ( current_user_can( 'delete_posts' ) ) {
			wp_delete_post( $id );
		}

	}

	$name    = sprintf( '<a href="%s">%s</a>', get_edit_post_link( $title ), get_the_title( $title ) );
	$msg     = sprintf( __( 'Please add serial numbers for %s , %d Serial number left', 'wc-serial-numbers' ), $name, $count );
	$message .= '<tr><td>' . $msg . '</td></tr>';

	echo '<li>' . $msg . '</li>';

}

echo '</ul>'; //End the list
