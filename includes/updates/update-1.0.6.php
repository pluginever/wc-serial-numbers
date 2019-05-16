<?php
function wcsn_update_1_0_6() {
    WC_Serial_Numbers_Install::activate();
    $serial_numbers = wcsn_get_serial_numbers( array(
        'number' => -1,
    ) );
    foreach( $serial_numbers as $serial_number ) {
        $serial_number = (array) $serial_number;

        if ( ! empty( $serial_number['serial_key'] ) ) {
            $serial_number['serial_key'] = wcsn_encrypt( $serial_number['serial_key'] );
        }

        wc_serial_numbers()->serial_number->update( $serial_number['id'], $serial_number );
    }
}

wcsn_update_1_0_6();