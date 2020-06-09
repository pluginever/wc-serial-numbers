<?php
function wcsn_update_1_0_8() {
	$delivery_settings                          = get_option( 'wsn_delivery_settings' );
	$delivery_settings['heading_text']          = empty( $delivery_settings['wsnp_email_label'] ) ? 'Serial Numbers' : $delivery_settings['wsnp_email_label'];
	$delivery_settings['table_column_heading']  = empty( $delivery_settings['wsnp_email_tabel_label'] ) ? 'Serial Number' : $delivery_settings['wsnp_email_tabel_label'];
	$delivery_settings['serial_key_label']      = empty( $delivery_settings['wsnp_email_serial_key_email_label'] ) ? 'Serial Key' : $delivery_settings['wsnp_email_serial_key_email_label'];
	$delivery_settings['serial_email_label']    = empty( $delivery_settings['wsnp_email_serial_key_label'] ) ? 'Serial Email' : $delivery_settings['wsnp_email_serial_key_label'];
	$delivery_settings['show_validity']         = empty( $delivery_settings['wsnp_show_validity_on_email'] ) ? 'yes' : $delivery_settings['wsnp_show_validity_on_email'];
	$delivery_settings['show_activation_limit'] = empty( $delivery_settings['wsnp_show_activation_limit_on_email'] ) ? 'yes' : $delivery_settings['wsnp_show_activation_limit_on_email'];

	update_option( 'wsn_delivery_settings', $delivery_settings );
}

wcsn_update_1_0_8();
